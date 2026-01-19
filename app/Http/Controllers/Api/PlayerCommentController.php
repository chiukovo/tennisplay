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
                    'rating' => $comment->rating,
                    'at' => $comment->created_at->toISOString(),
                    'user_id' => $comment->user_id,
                    'user' => [
                        'name' => ($p = $comment->user->player) ? $p->name : $comment->user->name,
                        'line_picture_url' => $comment->user->line_picture_url,
                        'uid' => $comment->user->uid,
                    ],
                    'reply' => $comment->reply,
                    'replied_at' => $comment->replied_at ? $comment->replied_at->toISOString() : null,
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
            'content' => 'nullable|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Require at least one field
        if (!$request->content && !$request->rating) {
            return response()->json(['message' => '請輸入內容或評分'], 422);
        }
 
        $player = Player::findOrFail($playerId);
        $actor = $request->user('sanctum') ?: $request->user();
        if (!$actor) {
            $actor = Auth::guard('sanctum')->user() ?: Auth::user();
        }
        if (!$actor) {
            return response()->json(['message' => '未授權'], 401);
        }

        // Check self-rating
        if ($request->rating && $player->user_id === $actor->id) {
            return response()->json(['message' => '不能評價自己'], 403);
        }

        // Check if already rated
        if ($request->rating) {
            $hasRated = PlayerComment::where('player_id', $player->id)
                ->where('user_id', $actor->id)
                ->whereNotNull('rating')
                ->exists();
            if ($hasRated) {
                return response()->json(['message' => '您已經評價過此球友'], 409);
            }
        }

        // 阻擋重複點擊 (5秒內相同內容)
        $lockKey = 'lock_player_comment_' . $actor->id . '_' . md5($playerId . $request->content);
        if (!Cache::add($lockKey, true, 5)) {
            return response()->json(['message' => '提交太快，請稍候再試'], 429);
        }

        $comment = PlayerComment::create([
            'player_id' => $player->id,
            'user_id' => $actor->id,
            'content' => $request->content ?? '',
            'rating' => $request->rating,
        ]);
        SendPlayerCommentNotification::dispatch($player->id, $actor->id, $comment->id, $request->content, $request->rating);

        // Refresh player to get updated stats
        $player->refresh();

        return response()->json([
            'message' => '留言成功',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->content,
                'rating' => $comment->rating,
                'at' => $comment->created_at->toISOString(),
                'user_id' => $comment->user_id,
                'user' => [
                    'name' => ($actor && $actor->player) ? $actor->player->name : $actor->name,
                    'line_picture_url' => $actor->line_picture_url,
                    'uid' => $actor->uid,
                ],
            ],
            'player_stats' => [
                'average_rating' => $player->average_rating,
                'ratings_count' => $player->ratings_count,
            ],
        ]);
    }

    /**
     * Update an existing comment.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Require at least one field
        if (!$request->content && !$request->rating) {
            return response()->json(['message' => '請輸入內容或評分'], 422);
        }

        $comment = PlayerComment::findOrFail($id);
        $actor = $request->user('sanctum') ?: $request->user();
        
        if (!$actor || $comment->user_id !== $actor->id) {
            return response()->json(['message' => '無權修改'], 403);
        }

        $comment->update([
            'content' => $request->content ?? '',
            'rating' => $request->rating,
        ]);

        // Refresh player stats
        $player = $comment->player;
        $player->refresh();

        return response()->json([
            'message' => '更新成功',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->content,
                'rating' => $comment->rating,
                'at' => $comment->created_at->toISOString(),
                'updated_at' => $comment->updated_at->toISOString(),
                'user_id' => $actor->id,
                'user' => [
                    'name' => ($p = $actor->player) ? $p->name : $actor->name,
                    'line_picture_url' => $actor->line_picture_url,
                    'uid' => $actor->uid,
                ],
            ],
            'player_stats' => [
                'average_rating' => $player->average_rating,
                'ratings_count' => $player->ratings_count,
            ]
        ]);
    }

    /**
     * Reply to a comment (Owner only).
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $comment = PlayerComment::findOrFail($id);
        $player = $comment->player;
        $actor = $request->user('sanctum') ?: $request->user();

        // Check if actor is the owner of the player card
        if (!$actor || $player->user_id !== $actor->id) {
            return response()->json(['message' => '只有版主可以回覆'], 403);
        }

        // Cannot reply to own comment
        if ($comment->user_id === $actor->id) {
            return response()->json(['message' => '不能回覆自己的留言'], 403);
        }

        $comment->update([
            'reply' => $request->reply,
            'replied_at' => now(),
        ]);

        return response()->json([
            'message' => '回覆成功',
            'comment' => [
                'id' => $comment->id,
                'reply' => $comment->reply,
                'replied_at' => $comment->replied_at->toISOString(),
            ]
        ]);
    }

    /**
     * Delete a reply (Owner only).
     */
    public function destroyReply(Request $request, $id)
    {
        $comment = PlayerComment::findOrFail($id);
        $player = $comment->player;
        $actor = $request->user('sanctum') ?: $request->user();

        // Check if actor is the owner of the player card
        if (!$actor || $player->user_id !== $actor->id) {
            return response()->json(['message' => '只有版主可以刪除回覆'], 403);
        }

        $comment->update([
            'reply' => null,
            'replied_at' => null,
        ]);

        return response()->json([
            'message' => '回覆已刪除',
        ]);
    }

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
        $playerId = $comment->player_id;
        $comment->delete();

        $player = Player::find($playerId);
        
        return response()->json([
            'message' => '留言已刪除',
            'comment_id' => $id,
            'player_stats' => [
                'average_rating' => $player->average_rating,
                'ratings_count' => $player->ratings_count,
            ],
        ]);

 
    }

}
