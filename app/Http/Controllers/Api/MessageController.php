<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        $latestIds = Message::where(function ($q) use ($userId) {
                $q->where('from_user_id', $userId)
                  ->orWhere('to_user_id', $userId);
            })
            ->selectRaw('MAX(id) as id')
            ->groupBy(DB::raw('LEAST(from_user_id, to_user_id), GREATEST(from_user_id, to_user_id)'))
            ->pluck('id');

        $conversations = Message::whereIn('id', $latestIds)
            ->with(['sender', 'receiver', 'player'])
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

        $query = Message::where(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $otherUserId)->where('to_user_id', $userId);
        });

        if ($request->after_id) {
            $messages = $query->where('id', '>', $request->after_id)
                ->with(['sender', 'player'])
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $messages = $query->with(['sender', 'player'])
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

        $message = Message::create([
            'from_user_id' => $user->id,
            'to_user_id' => $toUserId,
            'to_player_id' => $playerId,
            'content' => $request->content,
        ]);

        $message->load(['sender', 'player']);

        // Send LINE Notification if receiver exists and has line_user_id
        // 使用節流機制：同一發送者對同一接收者在短時間內只發送一次通知
        try {
            $receiver = \App\Models\User::find($toUserId);
            
            if ($receiver && $receiver->line_user_id) {
                $receiverSettings = $receiver->settings ?? [];
                $wantsNotify = $receiverSettings['notify_line'] ?? true;

                if ($wantsNotify) {
                    // 節流 key：改為單向 (發送者 -> 接收者)，確保回覆時對方能收到通知
                    $throttleKey = 'line_notify_from_' . $user->id . '_to_' . $toUserId;
                    $throttleMinutes = 1; // 縮短為 1 分鐘，提升即時感
                    
                    // 檢查是否在節流時間內
                    if (!\Illuminate\Support\Facades\Cache::has($throttleKey)) {
                        // 設置節流標記
                        \Illuminate\Support\Facades\Cache::put($throttleKey, true, now()->addMinutes($throttleMinutes));
                        
                        $lineService = new \App\Services\LineNotifyService();
                        $senderName = $user->name ?: '一位球友';
                        $senderAvatar = $user->line_picture_url;
                        $shortContent = \Illuminate\Support\Str::limit($request->content, 100);
                        
                        // Construct Sender Box (Avatar + Name)
                        $senderBoxContents = [];
                        if ($senderAvatar) {
                            $avatarUrl = str_starts_with($senderAvatar, 'http') ? $senderAvatar : asset($senderAvatar);
                            $senderBoxContents[] = [
                                "type" => "image",
                                "url" => $avatarUrl,
                                "size" => "xxs",
                                "aspectMode" => "cover",
                                "aspectRatio" => "1:1",
                                "gravity" => "center",
                                "flex" => 0
                            ];
                        }
                        $senderBoxContents[] = [
                            "type" => "text",
                            "text" => $senderName,
                            "weight" => "bold",
                            "size" => "sm",
                            "gravity" => "center",
                            "flex" => 1,
                            "margin" => "md"
                        ];

                        // Flex Message Structure (Premium Card with Avatar)
                        $flexContents = [
                            "type" => "bubble",
                            "header" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "🎾 收到約打邀約",
                                        "weight" => "bold",
                                        "color" => "#FFFFFF",
                                        "size" => "md"
                                    ]
                                ],
                                "backgroundColor" => "#2563EB",
                                "paddingAll" => "md"
                            ],
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "box",
                                        "layout" => "horizontal",
                                        "contents" => $senderBoxContents,
                                        "alignItems" => "center"
                                    ],
                                    [
                                        "type" => "separator",
                                        "margin" => "lg"
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => $shortContent,
                                        "wrap" => true,
                                        "size" => "xs",
                                        "color" => "#64748B",
                                        "margin" => "lg"
                                    ]
                                ],
                                "paddingAll" => "lg"
                            ],
                            "footer" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "button",
                                        "action" => [
                                            "type" => "uri",
                                            "label" => "立即查看訊息",
                                            "uri" => "https://lovetennis.tw/messages"
                                        ],
                                        "style" => "primary",
                                        "color" => "#2563EB",
                                        "height" => "sm"
                                    ]
                                ],
                                "paddingAll" => "md"
                            ]
                        ];

                        $lineService->sendFlexMessage($receiver->line_user_id, "🎾 您收到來自 {$senderName} 的約打邀約信", $flexContents);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LINE Notification Error: ' . $e->getMessage(), [
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

        $count = Message::forUser($user->id)->unread()->count();

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
}
