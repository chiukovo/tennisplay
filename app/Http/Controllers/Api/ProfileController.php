<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Like;
use App\Models\UserBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Get profile data for a user.
     */
    public function show($uid)
    {
        // Support both uid and numeric id for backwards compatibility
        $user = is_numeric($uid) 
            ? User::with(['player' => fn($q) => $q->withCount(['likes', 'comments'])])->findOrFail($uid)
            : User::with(['player' => fn($q) => $q->withCount(['likes', 'comments'])])->where('uid', $uid)->firstOrFail();
        
        $me = Auth::guard('sanctum')->user();

        // Stats
        $stats = [
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'likes_count' => $user->player ? $user->player->likes_count : 0,
            'events_count' => $user->organizedEvents()->count(),
        ];

        if ($me && $user->player) {
            \App\Models\Player::hydrateSocialStatus($user->player, $me);
        }

        // Status for current user (kept for backward compatibility or profile specific buttons)
        $status = [
            'is_following' => $me ? \App\Models\Follow::where('follower_id', $me->id)->where('following_id', $user->id)->exists() : false,
            'is_liked' => ($me && $user->player) ? \App\Models\Like::where('user_id', $me->id)->where('player_id', $user->player->id)->exists() : false,
            'is_me' => $me ? (string)$me->id === (string)$user->id : false,
            'is_blocked' => $me ? UserBlock::where('blocker_id', $me->id)->where('blocked_id', $user->id)->exists() : false,
            'is_blocked_by' => $me ? UserBlock::where('blocker_id', $user->id)->where('blocked_id', $me->id)->exists() : false,
        ];

        return response()->json([
            'user' => $user,
            'stats' => $stats,
            'status' => $status,
        ]);
    }

    /**
     * Update basic profile info.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'region' => 'nullable|string',
            'bio' => 'nullable|string|max:1000',
            'level' => 'nullable|string',
            'handed' => 'nullable|string',
            'backhand' => 'nullable|string',
            'intro' => 'nullable|string',
            'fee' => 'nullable|string',
        ]);

        $user->update([
            'name' => $data['name'],
            'gender' => $data['gender'],
            'region' => $data['region'],
            'bio' => $data['bio'],
        ]);

        // Sync to player card if it exists
        if ($user->player) {
            $user->player->update([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'region' => $data['region'],
                'level' => $data['level'] ?? $user->player->level,
                'handed' => $data['handed'] ?? $user->player->handed,
                'backhand' => $data['backhand'] ?? $user->player->backhand,
                'intro' => $data['intro'] ?? $user->player->intro,
                'fee' => $data['fee'] ?? $user->player->fee,
            ]);
        }

        return response()->json([
            'message' => '個人資料已更新',
            'user' => $user->fresh(['player']),
        ]);
    }

    /**
     * Get events for a user's profile.
     */
    public function events(Request $request, $uid)
    {
        $type = $request->get('type', 'active'); // active or past
        
        // Support both uid and numeric id
        $user = is_numeric($uid) 
            ? User::findOrFail($uid)
            : User::where('uid', $uid)->firstOrFail();

        $query = Event::with(['player', 'user', 'confirmedParticipants.player'])
            ->where('user_id', $user->id);

        if ($type === 'active') {
            $query->upcoming()
                  ->whereIn('status', ['open', 'full'])
                  ->orderBy('event_date', 'asc');
        } else {
            $query->where(function($q) {
                $q->where(function($sq) {
                    $sq->whereNotNull('end_date')
                       ->where('end_date', '<=', now());
                })->orWhere(function($sq) {
                    $sq->whereNull('end_date')
                       ->where('event_date', '<=', now());
                })->orWhereIn('status', ['completed', 'cancelled', 'closed']);
            })->orderBy('event_date', 'desc');
        }

        $events = $query->paginate(10);

        return response()->json($events);
    }
}
