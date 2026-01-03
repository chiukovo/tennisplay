<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Player;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of messages for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $messages = Message::forUser($user->id)
            ->with(['sender', 'player'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'to_player_id' => 'required|exists:players,id',
            'content' => 'required|string|max:2000',
        ]);

        $player = Player::findOrFail($request->to_player_id);

        $message = Message::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $player->user_id,
            'to_player_id' => $player->id,
            'content' => $request->content,
        ]);

        $message->load(['sender', 'player']);

        return response()->json([
            'success' => true,
            'message' => '訊息已發送',
            'data' => $message,
        ], 201);
    }

    /**
     * Display the specified message.
     */
    public function show(Request $request, $id)
    {
        $message = Message::with(['sender', 'player'])
            ->findOrFail($id);

        // Only allow sender or receiver to view
        $user = $request->user();
        if ($message->from_user_id !== $user->id && $message->to_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限查看此訊息',
            ], 403);
        }

        // Mark as read if receiver is viewing
        if ($message->to_user_id === $user->id) {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    /**
     * Mark message as read.
     */
    public function markRead(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        // Only receiver can mark as read
        if ($message->to_user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限操作',
            ], 403);
        }

        $message->markAsRead();

        return response()->json([
            'success' => true,
            'message' => '已標記為已讀',
        ]);
    }

    /**
     * Get unread message count.
     */
    public function unreadCount(Request $request)
    {
        $count = Message::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Get sent messages.
     */
    public function sent(Request $request)
    {
        $messages = Message::fromUser($request->user()->id)
            ->with(['receiver', 'player'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Delete a message.
     */
    public function destroy(Request $request, $id)
    {
        $message = Message::findOrFail($id);

        // Only sender or receiver can delete
        $user = $request->user();
        if ($message->from_user_id !== $user->id && $message->to_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限刪除此訊息',
            ], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => '訊息已刪除',
        ]);
    }
}
