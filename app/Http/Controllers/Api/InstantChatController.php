<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstantRoom;
use App\Models\InstantMessage;
use App\Models\User;
use App\Services\LineNotifyService;
use App\Services\LineFlexMessageBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class InstantChatController extends Controller
{
    public function getRooms()
    {
        // 記錄用戶已訪問過「想打」功能（用於推播判斷）
        if (Auth::check()) {
            $hasVisitedKey = "instant_visited:" . Auth::id();
            Redis::connection('echo')->setex($hasVisitedKey, 86400 * 365, '1'); // 保留1年
        }

        $rooms = InstantRoom::orderBy('sort_order')->get();
        $primaryRoom = $rooms->firstWhere('slug', 'all') ?? $rooms->first();
        $rooms = $primaryRoom ? collect([$primaryRoom]) : collect();

        $rooms->map(function($room) {
            $stats = $this->fetchRoomStatsData($room);
            $room->active_count = $stats['active_count'];
            $room->active_avatars = $stats['active_avatars'];
            
            // 3. HOT Logic: Active > 5 OR (Active > 2 AND Recent Message < 15min)
            $isRecent = false;
            $lastMessage = $room->messages()
                ->with(['user:id,name'])
                ->where('created_at', '>=', Carbon::now()->subMinutes(15))
                ->latest()
                ->first();

            if ($lastMessage) {
                $isRecent = true;
                $room->last_message = $lastMessage->content;
                $room->last_message_by = $lastMessage->user->name ?? null;
                $room->last_message_at = $lastMessage->created_at;
            } else {
                // Fallback to 24 hours for preview only
                $previewMessage = $room->messages()
                    ->with(['user:id,name'])
                    ->where('created_at', '>=', Carbon::now()->subHours(24))
                    ->latest()
                    ->first();
                if ($previewMessage) {
                    $room->last_message = $previewMessage->content;
                    $room->last_message_by = $previewMessage->user->name ?? null;
                    $room->last_message_at = $previewMessage->created_at;
                }
            }

            $room->is_hot = ($room->active_count >= 5) || ($room->active_count >= 2 && $isRecent);
            
            return $room;
        });

        return response()->json($rooms);
    }

    public function getMessages(InstantRoom $room)
    {
        // 顯示最近 50 條訊息，不再限制時間
        $messages = $room->messages()
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'line_picture_url', 'uid', 'region')->with('player:user_id,level');
            }])
            ->latest()
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        $messages->map(function($msg) {
            if ($msg->user && $msg->user->player) {
                $msg->user->level = $msg->user->player->level;
            }
            return $msg;
        });

        return response()->json($messages);
    }

    public function sendMessage(Request $request, InstantRoom $room)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = $room->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $message->load(['user' => function($q) {
            $q->select('id', 'name', 'line_picture_url', 'uid', 'region')->with('player:user_id,level');
        }]);

        if ($message->user && $message->user->player) {
            $message->user->level = $message->user->player->level;
        }

        // Broadcast the message via WebSocket
        broadcast(new \App\Events\InstantMessageSent($message))->toOthers();

        // Trigger a room stats sync to update the lobby card preview
        $this->syncRoomStats($room);

        // 發送 LINE 通知給離線的聊天室參與者
        $this->notifyOfflineParticipants($room, Auth::user());

        return response()->json($message);
    }

    public function getGlobalStats()
    {
        return response()->json($this->fetchGlobalStatsData());
    }

    /**
     * Get consolidated global data: Recent messages from all rooms + Active users + LFG users.
     */
    public function getGlobalData()
    {
        // 1. Fetch 10 most recent messages from ALL rooms (within 48 hours)
        $recentMessages = InstantMessage::with([
            'user' => function($q) {
                $q->select('id', 'name', 'line_picture_url', 'uid', 'region')->with('player:user_id,level');
            }, 
            'room:id,name,slug'
        ])
            ->where('created_at', '>=', Carbon::now()->subHours(6))
            ->latest()
            ->limit(10)
            ->get();

        $recentMessages->map(function($msg) {
            if ($msg->user && $msg->user->player) {
                $msg->user->level = $msg->user->player->level;
            }
            return $msg;
        });

        // 2. Fetch LFG (Looking For Group) users from Redis
        $lfgUsers = $this->getLfgUsers();

        // 3. Global Stats
        $stats = $this->fetchGlobalStatsData();

        return response()->json([
            'recent_messages' => $recentMessages,
            'lfg_users' => $lfgUsers,
            'global_stats' => $stats
        ]);
    }

    /**
     * Toggle "Looking For Group" status.
     */
    public function toggleLfg(Request $request)
    {
        $userId = Auth::id();
        $isLfg = $request->input('status', false);
        $remark = $request->input('remark');
        $key = 'instant_lfg_users';
        $userKey = "user_lfg:{$userId}";

        if ($isLfg) {
            // Store user info in a hash or set with 1 hour TTL
            $user = Auth::user();
            $player = $user->player; // Get NTRP Level
            
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->line_picture_url,
                'uid' => $user->uid,
                'level' => $player->level ?? '?',
                'region' => $user->region,
                'remark' => $remark,
                'timestamp' => now()->timestamp
            ];
            
            Redis::connection('echo')->hset($key, $userId, json_encode($userData));
            Redis::connection('echo')->setex($userKey, 120, '1'); // TTL 120 seconds (2 mins)
            
            // Pulse notify everyone
            $this->syncGlobalStats();
        } else {
            Redis::connection('echo')->hdel($key, $userId);
            Redis::connection('echo')->del($userKey);
            $this->syncGlobalStats();
        }

        return response()->json(['status' => 'success', 'is_lfg' => $isLfg]);
    }

    private function getLfgUsers()
    {
        $key = 'instant_lfg_users';
        $allLfg = Redis::connection('echo')->hgetall($key);
        $users = [];
        
        foreach ($allLfg as $userId => $data) {
            // Check if still valid (using TTL key as source of truth)
            if (Redis::connection('echo')->exists("user_lfg:{$userId}")) {
                $users[] = json_decode($data, true);
            } else {
                // Cleanup expired
                Redis::connection('echo')->hdel($key, $userId);
            }
        }
        
        // Sort by timestamp desc
        usort($users, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        
        return $users;
    }

    /**
     * Trigger a broadcast update for a specific room's stats to the lobby.
     */
    public function syncRoomStats(InstantRoom $room)
    {
        $stats = $this->fetchRoomStatsData($room);
        
        // Fetch latest preview message
        $lastMessage = $room->messages()
            ->with(['user:id,name'])
            ->latest()
            ->first();

        broadcast(new \App\Events\InstantRoomStatsUpdated(
            $room->slug,
            $stats['active_count'],
            $stats['active_avatars'],
            $lastMessage->content ?? null,
            $lastMessage->user->name ?? null,
            $lastMessage->created_at ?? null,
            $lastMessage->id ?? null
        ));
        
        return response()->json(['status' => 'synced', 'stats' => $stats]);
    }

    /**
     * Explicitly exit current room records to prevent ghosting.
     */
    /**
     * Explicitly exit current room records (Simple version).
     */
    public function exitRoom()
    {
        // Just trigger a global refresh pulse
        $this->syncGlobalStats();

        return response()->json(['status' => 'exited']);
    }

    /**
     * Trigger a broadcast update for global stats (Pulse).
     */
    public function syncGlobalStats()
    {
        $stats = $this->fetchGlobalStatsData();
        
        broadcast(new \App\Events\InstantGlobalStatsUpdated(
            $stats['active_count'],
            $stats['avatars']
        ));

        return response()->json(['status' => 'synced', 'stats' => $stats]);
    }

    private function fetchRoomStatsData(InstantRoom $room)
    {
        $key = 'presence-instant-room.' . $room->slug . ':members';
        $json = Redis::connection('echo')->get($key);
        $members = $json ? json_decode($json, true) : [];
        
        $uniqueUsers = [];
        $uniqueIds = [];
        if (is_array($members)) {
            foreach ($members as $member) {
                $userData = $member['user_info'] ?? null;
                if ($userData) {
                    $userId = $userData['id'] ?? null;
                    if (!$userId) continue;

                    // Deduplication: Only take the first connection for this user
                    if (in_array($userId, $uniqueIds)) continue;
                    
                    // State Reconciliation: Only count if this is the user's LATEST authoritative room
                    $currentLocation = Redis::connection('echo')->get('user_location:' . $userId);
                    if ($currentLocation !== $room->slug) {
                        continue; // This is a ghost/shadow from a previous or concurrent connection
                    }

                    $uniqueIds[] = $userId;
                    $uniqueUsers[] = $userData;
                }
            }
        }

        return [
            'active_count' => count($uniqueUsers),
            'active_avatars' => array_slice(array_reverse($uniqueUsers), 0, 3)
        ];
    }

    private function fetchGlobalStatsData()
    {
        // Explicitly get member list for avatars and unique count
        $json = Redis::connection('echo')->get('presence-instant-lobby:members');
        $members = $json ? json_decode($json, true) : [];
        
        $uniqueUsers = [];
        $uniqueIds = [];
        if (is_array($members)) {
            foreach ($members as $m) {
                if (isset($m['user_info'])) {
                    $uInfo = $m['user_info'];
                    $uid = $uInfo['uid'] ?? null;
                    if ($uid && !in_array($uid, $uniqueIds)) {
                        $uniqueIds[] = $uid;
                        $uniqueUsers[] = [
                            'avatar' => $uInfo['avatar'] ?? null, 
                            'uid' => $uid,
                            'name' => $uInfo['name'] ?? null,
                            'level' => $uInfo['level'] ?? null
                        ];
                    }
                }
            }
        }

        $count = count($uniqueUsers);

        return [
            'active_count' => $count,
            'display_count' => $count,
            'avatars' => array_slice($uniqueUsers, 0, 15) // Return up to 15 unique avatars
        ];
    }

    /**
     * 熱度觸發推播：當聊天室熱鬧時推播給潛在用戶
     * - 只在高熱度時才推播（5人以上在線，或15分鐘內8則訊息）
     * - 推播給最近3天有登入但未用過「想打」的用戶
     * - 每個用戶每天最多收到1次，時間限制10:00-22:00
     */
    private function notifyOfflineParticipants(InstantRoom $room, $sender)
    {
        if (!$sender) return;

        // 檢查是否達到熱度觸發條件
        $stats = $this->fetchRoomStatsData($room);
        $activeCount = $stats['active_count'] ?? 0;
        
        // 計算最近15分鐘的訊息數
        $recentMessageCount = $room->messages()
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();
        
        // 熱度觸發條件：同時在線≥5人 或 15分鐘內≥8則訊息
        $isHot = $activeCount >= 5 || $recentMessageCount >= 8;
        
        if (!$isHot) return; // 不夠熱鬧就不推播
        
        // 限制推播時間：10:00-22:00
        $hour = now()->hour;
        if ($hour < 10 || $hour >= 22) return;

        // 取得目前在線的用戶 ID
        $onlineUserIds = $this->getOnlineUserIds();

        // 找出潛在用戶：最近3天有活動（updated_at），但從未進入過「想打」
        $potentialUsers = User::whereNotNull('line_user_id')
            ->where('updated_at', '>=', now()->subDays(3))
            ->whereNotIn('id', $onlineUserIds)
            ->limit(50) // 限制最多50人避免過載
            ->get();

        $notificationsSent = 0;

        foreach ($potentialUsers as $user) {
            // 限制每次推播最多10人
            if ($notificationsSent >= 10) break;

            // 檢查是否曾進入過「想打」功能（Redis記錄）
            $hasVisitedKey = "instant_visited:{$user->id}";
            if (Redis::connection('echo')->exists($hasVisitedKey)) continue;

            // 每日推播限制：每個用戶每天最多1次
            $dailyThrottleKey = "instant_promo_daily:{$user->id}:" . now()->format('Y-m-d');
            if (Cache::has($dailyThrottleKey)) continue;
            Cache::put($dailyThrottleKey, true, now()->endOfDay());

            // 發送 LINE 通知
            LineNotifyService::dispatchFlexMessage(
                $user->id,
                $user->line_user_id,
                "🔥 熱！現在有 {$activeCount} 人在「想打」找球友！",
                LineFlexMessageBuilder::buildInstantChatNotification($room->name)
            );

            // 發送原生推播通知
            app(PushNotificationService::class)->notifyUser(
                $user->id,
                "🔥 現在很熱鬧！",
                "{$activeCount} 人正在「想打」聊天室找球友，立即加入！",
                ['room_slug' => $room->slug, 'type' => 'instant_promo']
            );

            $notificationsSent++;
        }
    }

    /**
     * 取得目前在 instant-lobby Presence Channel 的用戶 ID
     */
    private function getOnlineUserIds()
    {
        $json = Redis::connection('echo')->get('presence-instant-lobby:members');
        $members = $json ? json_decode($json, true) : [];
        
        $userIds = [];
        if (is_array($members)) {
            foreach ($members as $m) {
                if (isset($m['user_info']['id'])) {
                    $userIds[] = $m['user_info']['id'];
                }
            }
        }
        
        return array_unique($userIds);
    }
}
