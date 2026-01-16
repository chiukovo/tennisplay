<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerComment;
use App\Jobs\SendPlayerCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


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
                        'line_picture_url' => $comment->user->line_picture_url,
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
        $actor = $request->user('sanctum') ?: $request->user();
        if (!$actor) {
            $actor = Auth::guard('sanctum')->user() ?: Auth::user();
        }
        if (!$actor) {
            return response()->json(['message' => '未授權'], 401);
        }

        // 阻擋重複點擊 (5秒內相同內容)
        $lockKey = 'lock_player_comment_' . $actor->id . '_' . md5($playerId . $request->content);
        if (!Cache::add($lockKey, true, 5)) {
            return response()->json(['message' => '提交太快，請稍候再試'], 429);
        }

        $comment = PlayerComment::create([
            'player_id' => $player->id,
            'user_id' => $actor->id,
            'content' => $request->content,
        ]);
        SendPlayerCommentNotification::dispatch($player->id, $actor->id, $request->content);

        return response()->json([
            'message' => '留言成功',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->content,
                'at' => $comment->created_at->toISOString(),
                'user_id' => $comment->user_id,
                'user' => [
                    'name' => ($actor && $actor->player) ? $actor->player->name : $actor->name,
                    'line_picture_url' => $actor->line_picture_url,
                    'uid' => $actor->uid,
                ],
            ],
        ]);


    }


    /**
     * Delete a comment.
     */
    public function destroy(Request $request, $id)
    {
        $comment = PlayerComment::findOrFail($id);
        $actor = $request->user('sanctum') ?: $request->user();
        Log::info('PlayerComment destroy resolved actor', ['actor' => $actor ? $actor->id : null, 'comment_user' => $comment->user_id]);
        if (!$actor) {
            $actor = Auth::guard('sanctum')->user() ?: Auth::user();
            Log::info('PlayerComment destroy fallback actor', ['actor' => $actor ? $actor->id : null, 'comment_user' => $comment->user_id]);
        }
        if (!$actor) {
            Log::warning('PlayerComment destroy unauthorized', ['comment_user' => $comment->user_id]);
            return response()->json(['message' => '未授權'], 401);
        }
        if ($comment->user_id != $actor->id) {
            Log::warning('PlayerComment destroy forbidden', ['actor' => $actor->id, 'comment_user' => $comment->user_id]);
            return response()->json(['message' => '無權限刪除此留言'], 403);
        }
 
        Log::info('PlayerComment destroy deleting', ['actor' => $actor->id, 'comment_id' => $comment->id]);
        $comment->delete();
 
        return response()->json([
            'message' => '留言已刪除',
            'comment_id' => $id,
        ]);

 
    }

}
