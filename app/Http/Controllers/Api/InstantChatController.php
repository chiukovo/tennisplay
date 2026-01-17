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
            return $room;
        });

        return response()->json($rooms);
    }

    public function getMessages(InstantRoom $room)
    {
        $messages = $room->messages()
            ->with(['user' => function($q) {
                $q->select('id', 'name', 'line_picture_url', 'uid');
            }])
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

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

        $message->load('user');

        // Broadcast the message via WebSocket
        broadcast(new \App\Events\InstantMessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function getGlobalStats()
    {
        return response()->json($this->fetchGlobalStatsData());
    }

    /**
     * Trigger a broadcast update for a specific room's stats to the lobby.
     */
    public function syncRoomStats(InstantRoom $room)
    {
        // Pulse Signal Architecture: Just tell the frontend to REFRESH from API
        $payload = [
            'event' => 'stats.changed',
            'data' => ['type' => 'room', 'room_slug' => $room->slug],
            'socket' => null
        ];
        
        Redis::connection('echo')->publish('instant-public', json_encode($payload));
        
        return response()->json(['status' => 'synced']);
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
        $payload = [
            'event' => 'stats.changed',
            'data' => ['type' => 'global'],
            'socket' => null
        ];
        
        Redis::connection('echo')->publish('instant-public', json_encode($payload));

        return response()->json(['status' => 'synced']);
    }

    private function fetchRoomStatsData(InstantRoom $room)
    {
        $key = 'presence-instant-room.' . $room->slug . ':members';
        $json = Redis::connection('echo')->get($key);
        $members = $json ? json_decode($json, true) : [];
        
        $activeUsers = [];
        if (is_array($members)) {
            foreach ($members as $member) {
                $userData = $member['user_info'] ?? null;
                if ($userData) {
                    $userId = $userData['id'] ?? null;
                    if ($userId) {
                        // State Reconciliation: Only count if this is the user's LATEST authoritative room
                        $currentLocation = Redis::connection('echo')->get('user_location:' . $userId);
                        if ($currentLocation !== $room->slug) {
                            continue; // This is a ghost/shadow from a previous or concurrent connection
                        }
                    }
                    $activeUsers[] = $userData;
                }
            }
        }

        return [
            'active_count' => count($activeUsers),
            'active_avatars' => array_slice(array_reverse($activeUsers), 0, 3)
        ];
    }

    private function fetchGlobalStatsData()
    {
        $count = (int) Redis::connection('echo')->get('presence-instant-lobby:members_count');
        
        // Explicitly get member list for avatars
        $json = Redis::connection('echo')->get('presence-instant-lobby:members');
        $members = $json ? json_decode($json, true) : [];
        
        // ROBUSTNESS: If count key is missing or 0 but members list has people, use member count
        if ($count <= 0 && !empty($members)) {
            $count = count($members);
        }

        $avatars = [];
        if (is_array($members)) {
            foreach (array_slice($members, 0, 8) as $m) {
                if (isset($m['user_info'])) {
                    $avatars[] = ['avatar' => $m['user_info']['avatar'], 'uid' => $m['user_info']['uid']];
                }
            }
        }

        return [
            'active_count' => $count,
            'display_count' => $count,
            'avatars' => $avatars
        ];
    }
}
