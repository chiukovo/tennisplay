<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Get profile data for a user.
     */
    public function show($userId)
    {
        $user = User::with(['player'])->findOrFail($userId);
        $me = Auth::user();

        // Stats
        $stats = [
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'likes_count' => $user->player ? $user->player->likes_count : 0,
            'events_count' => $user->organizedEvents()->count(),
        ];

        // Status for current user
        $status = [
            'is_following' => $me ? Follow::where('follower_id', $me->id)->where('following_id', $userId)->exists() : false,
            'is_liked' => ($me && $user->player) ? Like::where('user_id', $me->id)->where('player_id', $user->player->id)->exists() : false,
            'is_me' => $me ? $me->id == $userId : false,
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
            'gender' => 'nullable|string|in:男,女',
            'region' => 'nullable|string',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($data);

        // Sync to player card if it exists
        if ($user->player) {
            $user->player->update([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'region' => $data['region'],
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
    public function events(Request $request, $userId)
    {
        $type = $request->get('type', 'active'); // active or past

        $query = Event::with(['player', 'confirmedParticipants.player'])
            ->where('user_id', $userId);

        if ($type === 'active') {
            $query->where('event_date', '>=', now())
                  ->whereIn('status', ['open', 'full'])
                  ->orderBy('event_date', 'asc');
        } else {
            $query->where(function($q) {
                $q->where('event_date', '<', now())
                  ->orWhereIn('status', ['completed', 'cancelled']);
            })->orderBy('event_date', 'desc');
        }

        $events = $query->paginate(10);

        return response()->json($events);
    }
}
