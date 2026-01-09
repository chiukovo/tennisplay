<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Player;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of conversations (latest message per user pair).
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch all messages involving the user
        $messages = Message::where(function ($q) use ($userId) {
            $q->where('from_user_id', $userId)
              ->orWhere('to_user_id', $userId);
        })
        ->with(['sender', 'receiver', 'player'])
        ->orderBy('created_at', 'desc')
        ->get();

        // Group by the "other" user and take the first (latest) one
        $conversations = $messages->groupBy(function ($message) use ($userId) {
            return $message->from_user_id === $userId ? ($message->receiver->uid ?? $message->to_user_id) : ($message->sender->uid ?? $message->from_user_id);
        })->map(function ($msgs) {
            $latest = $msgs->first();
            // Count unread for this conversation
            $unreadCount = $msgs->where('to_user_id', request()->user()->id)->whereNull('read_at')->count();
            $latest->unread_count = $unreadCount;
            return $latest;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Get chat history with a specific user.
     */
    public function chat(Request $request, $uid)
    {
        $userId = $request->user()->id;
        
        // Find other user by uid or id
        $otherUser = is_numeric($uid) 
            ? \App\Models\User::findOrFail($uid)
            : \App\Models\User::where('uid', $uid)->firstOrFail();
        
        $otherUserId = $otherUser->id;

        $query = Message::where(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('from_user_id', $otherUserId)->where('to_user_id', $userId);
        });

        if ($request->after_id) {
            // Polling: Get messages after specific ID
            $messages = $query->where('id', '>', $request->after_id)
                ->with(['sender', 'player'])
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            // Initial Load / Pagination: Get latest messages
            $messages = $query->with(['sender', 'player'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 50);
        }

        // Mark all received messages as read
        Message::where('from_user_id', $otherUserId)
            ->where('to_user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

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
            'to_user_id' => 'nullable|exists:users,id',
            'to_user_uid' => 'nullable|exists:users,uid',
            'to_player_id' => 'nullable|exists:players,id',
            'content' => 'required|string|max:2000',
        ]);

        $toUserId = $request->to_user_id;
        $toUserUid = $request->to_user_uid;
        $playerId = $request->to_player_id;

        // If UID is provided, find the ID
        if ($toUserUid && !$toUserId) {
            $toUserId = \App\Models\User::where('uid', $toUserUid)->value('id');
        }

        // If player ID is provided, infer user ID
        if ($playerId && !$toUserId) {
            $player = Player::findOrFail($playerId);
            $toUserId = $player->user_id;
        }

        $message = Message::create([
            'from_user_id' => $request->user()->id,
            'to_user_id' => $toUserId,
            'to_player_id' => $playerId,
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
     * Get sent messages (Deprecated in favor of chat view, but kept for compatibility).
     */
    public function sent(Request $request)
    {
        return $this->index($request);
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
