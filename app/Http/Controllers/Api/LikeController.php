<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Like a player card.
     */
    public function like(Request $request, $playerId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $player = Player::findOrFail($playerId);

        // Check if already liked
        $exists = Like::where('user_id', $user->id)
            ->where('player_id', $playerId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => '您已經按過讚了'], 200);
        }

        Like::create([
            'user_id' => $user->id,
            'player_id' => $playerId,
        ]);

        return response()->json([
            'message' => '已按讚',
            'likes_count' => $player->likes()->count(),
        ]);
    }

    /**
     * Unlike a player card.
     */
    public function unlike(Request $request, $playerId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $player = Player::findOrFail($playerId);

        Like::where('user_id', $user->id)
            ->where('player_id', $playerId)
            ->delete();

        return response()->json([
            'message' => '已取消按讚',
            'likes_count' => $player->likes()->count(),
        ]);
    }

    /**
     * Check if liked a player card.
     */
    public function status($playerId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['is_liked' => false]);

        $isLiked = Like::where('user_id', $user->id)
            ->where('player_id', $playerId)
            ->exists();

        return response()->json(['is_liked' => $isLiked]);
    }
}
