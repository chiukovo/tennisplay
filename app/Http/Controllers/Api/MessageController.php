<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Player;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\PushNotificationService;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations (latest message per user pair).
     */
    public function index(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }
        $userId = $user->id;
        $blockedIds = $this->getBlockedUserIds($userId);

        $baseQuery = Message::where(function ($q) use ($userId) {
                $q->where('from_user_id', $userId)
                  ->orWhere('to_user_id', $userId);
            });

        if (!empty($blockedIds)) {
            $baseQuery->whereNotIn('from_user_id', $blockedIds)
                      ->whereNotIn('to_user_id', $blockedIds);
        }

        $latestIds = $baseQuery
            ->selectRaw('MAX(id) as id')
            ->groupBy(DB::raw('LEAST(from_user_id, to_user_id), GREATEST(from_user_id, to_user_id)'))
            ->pluck('id');

        $conversations = Message::whereIn('id', $latestIds)
            ->with(['sender.player', 'receiver.player', 'player'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($message) use ($userId) {
                $otherUserId = ($message->from_user_id == $userId) ? $message->to_user_id : $message->from_user_id;
                $message->unread_count = Message::where('from_user_id', $otherUserId)
                    ->where('to_user_id', $userId)
                    ->whereNull('read_at')
                    ->count();
                return $message;
            });

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Get chat history with a specific user.
     */
    public function chat(Request $request, $uid)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }
        $userId = $user->id;

        $otherUser = is_numeric($uid)
            ? \App\Models\User::findOrFail($uid)
            : \App\Models\User::where('uid', $uid)->firstOrFail();
        $otherUserId = $otherUser->id;

        if ($this->isBlockedBetween($userId, $otherUserId)) {
            return response()->json(['success' => false, 'message' => '已封鎖或被封鎖，無法查看私訊'], 403);
        }

        $query = Message::where(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $otherUserId)->where('to_user_id', $userId);
        });

        if ($request->after_id) {
            $messages = $query->where('id', '>', $request->after_id)
                ->with(['sender.player', 'player'])
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $messages = $query->with(['sender.player', 'player'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 50);
        }

        Message::where('from_user_id', $otherUserId)
            ->where('to_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'to_user_id' => 'nullable|exists:users,id',
            'to_user_uid' => 'nullable|exists:users,uid',
            'to_player_id' => 'nullable|exists:players,id',
            'content' => 'required|string|max:2000',
        ]);

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }

        $toUserId = $request->to_user_id;
        $toUserUid = $request->to_user_uid;
        $playerId = $request->to_player_id;

        if ($toUserUid && !$toUserId) {
            $toUserId = \App\Models\User::where('uid', $toUserUid)->value('id');
        }

        if ($playerId && !$toUserId) {
            $player = Player::findOrFail($playerId);
            $toUserId = $player->user_id;
        }

        if ($toUserId && $this->isBlockedBetween($user->id, $toUserId)) {
            return response()->json(['success' => false, 'message' => '已封鎖或被封鎖，無法傳送私訊'], 403);
        }

        // 阻擋重複點擊 (5秒內相同內容)
        $lockKey = 'lock_message_' . $user->id . '_' . md5($toUserId . $request->content);
        if (!Cache::add($lockKey, true, 5)) {
            return response()->json(['success' => false, 'message' => '提交太快，請稍候再試'], 429);
        }

        $message = Message::create([
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'to_player_id' => $playerId,
            'content' => $request->content,
        ]);

        $message->load(['sender', 'player']);

        // Send LINE Notification if receiver exists and has line_user_id
        // 使用節流機制：同一發送者對同一接收者在短時間內只發送一次通知
        // 使用 Queue 非同步發送，支援重試機制
        try {
            $receiver = \App\Models\User::find($toUserId);
            
            if ($receiver && $receiver->line_user_id) {
                $receiverSettings = $receiver->settings ?? [];
                $wantsNotify = $receiverSettings['notify_line'] ?? true;

                if ($wantsNotify) {
                    // 節流 key：單向 (發送者 -> 接收者)，確保回覆時對方能收到通知
                    $throttleKey = 'line_notify_from_' . $user->id . '_to_' . $toUserId;
                    $throttleSeconds = 30; // 縮短為 30 秒，提升即時感
                    
                    // 檢查是否在節流時間內
                    if (!Cache::has($throttleKey)) {
                        // 設置節流標記
                        Cache::put($throttleKey, true, now()->addSeconds($throttleSeconds));
                        
                        // 建構 Flex Message
                        $senderName = $user->name ?: '一位球友';
                        $senderAvatar = $user->line_picture_url;
                        $flexContents = \App\Services\LineFlexMessageBuilder::buildMatchInviteMessage(
                            $senderName,
                            $senderAvatar,
                            $request->content
                        );
                        
                        // 使用 Queue 非同步發送 (支援重試)
                        $lineService = new \App\Services\LineNotifyService();
                        $lineService->dispatchFlexMessage(
                            $receiver->id,
                            $receiver->line_user_id,
                            "🎾 您收到來自 {$senderName} 的約打邀約信",
                            $flexContents
                        );

                        // 發送原生推播通知 (Capacitor/FCM)
                        app(PushNotificationService::class)->notifyUser(
                            $receiver->id,
                            "🎾 您收到一封私訊邀約",
                            "來自 {$senderName}：「" . mb_substr($request->content, 0, 30) . "...」",
                            ['sender_uid' => $user->uid, 'type' => 'message']
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LINE Notification Dispatch Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'to_user_id' => $toUserId,
                'exception' => $e
            ]);
        }


        return response()->json([
            'success' => true,
            'message' => '訊息已發送',
            'data' => $message,
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Request $request, $id)
    {
        $message = Message::with(['sender', 'player'])->findOrFail($id);

        $user = $this->resolveUser($request);
        if (!$user || ($message->from_user_id !== $user->id && $message->to_user_id !== $user->id)) {
            return response()->json([
                'success' => false,
                'message' => '無權限查看此訊息',
            ], 403);
        }

        $otherUserId = $message->from_user_id == $user->id ? $message->to_user_id : $message->from_user_id;
        if ($this->isBlockedBetween($user->id, $otherUserId)) {
            return response()->json(['success' => false, 'message' => '已封鎖或被封鎖，無法查看私訊'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Mark message as read.
     */
    public function markRead(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        $user = $this->resolveUser($request);
        if (!$user || $message->to_user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限操作',
            ], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => '已標記為已讀',
        ]);

    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }
        $blockedIds = $this->getBlockedUserIds($user->id);
        $query = Message::forUser($user->id)->unread();
        if (!empty($blockedIds)) {
            $query->whereNotIn('from_user_id', $blockedIds);
        }
        $count = $query->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Get sent messages (deprecated in favor of chat view, kept for compatibility).
     */
    public function sent(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Delete a message.
     */
    public function destroy(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        $user = $this->resolveUser($request);
        if (!$user || ($message->from_user_id != $user->id && $message->to_user_id != $user->id)) {
            return response()->json([
                'success' => false,
                'message' => '無權限刪除此訊息',
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => '訊息已刪除',
        ]);
    }

    /**
     * Resolve current authenticated user checking request, Sanctum guards, then default auth.
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user('sanctum') ?: $request->user();
        if (!$user) {
            $user = Auth::guard('sanctum')->user();
        }
        if (!$user) {
            $user = Auth::user();
        }
        return $user;
    }

    private function getBlockedUserIds(int $userId): array
    {
        $blocked = UserBlock::where('blocker_id', $userId)->pluck('blocked_id')->toArray();
        $blockedBy = UserBlock::where('blocked_id', $userId)->pluck('blocker_id')->toArray();
        return array_values(array_unique(array_merge($blocked, $blockedBy)));
    }

    private function isBlockedBetween(int $userId, int $otherUserId): bool
    {
        return UserBlock::isBlockedBetween($userId, $otherUserId);
    }
}
