<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventComment;
use App\Jobs\SendEventCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EventCommentController extends Controller
{
    /**
     * Get comments for a specific event.
     */
    public function index($eventId)
    {
        $comments = EventComment::with('user.player')
            ->where('event_id', $eventId)
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
    public function store(Request $request, $eventId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $event = Event::findOrFail($eventId);
        $userId = Auth::id();

        // 阻擋重複點擊 (5秒內相同內容)
        $lockKey = 'lock_event_comment_' . $userId . '_' . md5($eventId . $request->content);
        if (!Cache::add($lockKey, true, 5)) {
            return response()->json(['message' => '提交太快，請稍候再試'], 429);
        }

        $comment = EventComment::create([
            'event_id' => $event->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);
        SendEventCommentNotification::dispatch($event->id, Auth::id(), $request->content);

        return response()->json([
            'message' => '留言成功',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->content,
                'at' => $comment->created_at->toISOString(),
                'user_id' => $comment->user_id,
                'user' => [
                    'name' => ($userPlayer = Auth::user()->player) ? $userPlayer->name : Auth::user()->name,
                    'line_picture_url' => Auth::user()->line_picture_url,
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
        $comment = EventComment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => '無權限刪除此留言'], 403);
        }

        $comment->delete();

        return response()->json(['message' => '留言已刪除']);
    }
}
