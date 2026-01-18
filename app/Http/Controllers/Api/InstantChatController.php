<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InstantRoom;
use App\Models\InstantMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Redis;

class InstantChatController extends Controller
{
    public function getRooms()
    {
        $rooms = InstantRoom::orderBy('sort_order')->get();

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
        // Only show messages from the past 48 hours for relevance
        $messages = $room->messages()
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'line_picture_url', 'uid')->with('player:user_id,level');
            }])
            ->where('created_at', '>=', Carbon::now()->subHours(48))
            ->latest()
            ->limit(50)
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
            $q->with('player:user_id,level');
        }]);

        if ($message->user && $message->user->player) {
            $message->user->level = $message->user->player->level;
        }

        // Broadcast the message via WebSocket
        broadcast(new \App\Events\InstantMessageSent($message))->toOthers();

        // Trigger a room stats sync to update the lobby card preview
        $this->syncRoomStats($room);

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
                $q->select('id', 'name', 'line_picture_url', 'uid')->with('player:user_id,level');
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
}
