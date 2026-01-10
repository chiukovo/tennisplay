<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function follow(Request $request, $uid)
    {
        $follower = Auth::user();
        if (!$follower) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $following = is_numeric($uid) ? User::findOrFail($uid) : User::where('uid', $uid)->firstOrFail();
        $userId = $following->id;

        if ($follower->id == $userId) {
            return response()->json(['error' => '您不能追蹤自己'], 400);
        }

        // Check if already following
        $exists = Follow::where('follower_id', $follower->id)
            ->where('following_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => '您已經追蹤過此球友'], 200);
        }

        Follow::create([
            'follower_id' => $follower->id,
            'following_id' => $userId,
        ]);

        return response()->json([
            'message' => '已追蹤',
            'followers_count' => $following->followers()->count(),
        ]);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(Request $request, $uid)
    {
        $follower = Auth::user();
        if (!$follower) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $following = is_numeric($uid) ? User::findOrFail($uid) : User::where('uid', $uid)->firstOrFail();
        $userId = $following->id;

        Follow::where('follower_id', $follower->id)
            ->where('following_id', $userId)
            ->delete();

        return response()->json([
            'message' => '已取消追蹤',
            'followers_count' => $following->followers()->count(),
        ]);
    }

    /**
     * Check if following a user.
     */
    public function status($uid)
    {
        $me = Auth::user();
        if (!$me) return response()->json(['is_following' => false]);

        $following = is_numeric($uid) ? User::findOrFail($uid) : User::where('uid', $uid)->firstOrFail();
        $userId = $following->id;

        $isFollowing = Follow::where('follower_id', $me->id)
            ->where('following_id', $userId)
            ->exists();

        return response()->json(['is_following' => $isFollowing]);
    }

    /**
     * Get users followed by a specific user.
     */
    public function following($uid)
    {
        $user = is_numeric($uid) ? User::findOrFail($uid) : User::where('uid', $uid)->firstOrFail();
        $me = Auth::user();
        
        $following = $user->following()->with('player')->get()->map(function($u) use ($me) {
            return [
                'uid' => $u->uid,
                'name' => $u->player ? $u->player->name : $u->name,
                'photo' => $u->player ? $u->player->photo_url : $u->line_picture_url,
                'region' => $u->player ? $u->player->region : $u->region,
                'level' => $u->player ? $u->player->level : null,
                'is_mutual' => $me ? $u->following()->where('following_id', $me->id)->exists() : false,
            ];
        });

        return response()->json($following);
    }

    /**
     * Get users following a specific user.
     */
    public function followers($uid)
    {
        $user = is_numeric($uid) ? User::findOrFail($uid) : User::where('uid', $uid)->firstOrFail();
        $me = Auth::user();
        
        $followers = $user->followers()->with('player')->get()->map(function($u) use ($me) {
            return [
                'uid' => $u->uid,
                'name' => $u->player ? $u->player->name : $u->name,
                'photo' => $u->player ? $u->player->photo_url : $u->line_picture_url,
                'region' => $u->player ? $u->player->region : $u->region,
                'level' => $u->player ? $u->player->level : null,
                'is_mutual' => $me ? $u->followers()->where('follower_id', $me->id)->exists() : false,
            ];
        });

        return response()->json($followers);
    }
}
