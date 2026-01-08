<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                        'photo' => $p ? $p->photo_url : null,
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

        $comment = EventComment::create([
            'event_id' => $event->id,
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
