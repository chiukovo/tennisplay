<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerCommentController extends Controller
{
    /**
     * Get comments for a specific player.
     */
    public function index($playerId)
    {
        $comments = PlayerComment::with('user.player')
            ->where('player_id', $playerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'text' => $comment->content,
                    'at' => $comment->created_at->toISOString(),
                    'user_id' => $comment->user_id,
                    'user' => [
                        'name' => ($p = $comment->user->player) ? $p->name : $comment->user->name,
                        'photo' => $p ? $p->photo_url : null,
                        'uid' => $comment->user->uid,
                    ],
                ];
            });

        return response()->json($comments);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, $playerId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $player = Player::findOrFail($playerId);

        $comment = PlayerComment::create([
            'player_id' => $player->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => '留言成功',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->content,
                'at' => $comment->created_at->toISOString(),
                'user_id' => $comment->user_id,
                'user' => [
                    'name' => ($userPlayer = Auth::user()->player) ? $userPlayer->name : Auth::user()->name,
                    'photo' => $userPlayer ? $userPlayer->photo_url : null,
                    'uid' => Auth::user()->uid,
                ],
            ]
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy($id)
    {
        $comment = PlayerComment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '無權限刪除此留言'], 403);
        }

        $comment->delete();

        return response()->json(['message' => '留言已刪除']);
    }
}
