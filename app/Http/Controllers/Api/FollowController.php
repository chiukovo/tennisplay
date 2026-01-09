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
}
