<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Jobs\SendEventNotification;
use App\Models\EventParticipant;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Get list of events with optional filters.
     */
    public function index(Request $request)
    {
        $query = Event::with(['player', 'user', 'confirmedParticipants.player'])
            ->upcoming()
            ->orderBy('event_date', 'asc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['open', 'full']);
        }

        // Filter by region
        if ($request->has('region')) {
            $query->inRegion($request->region);
        }

        // Filter by match type
        if ($request->has('match_type') && $request->match_type !== 'all') {
            $query->where('match_type', $request->match_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            if ($request->has('end_date') && $request->end_date) {
                // Range
                $query->whereDate('event_date', '>=', $request->start_date)
                      ->whereDate('event_date', '<=', $request->end_date);
            } else {
                // Only start
                $query->whereDate('event_date', '>=', $request->start_date);
            }
        } elseif ($request->has('date') && $request->date) {
            // Legacy single date support
            $query->whereDate('event_date', $request->date);
        }

        // Filter by time period
        if ($request->has('time_period') && $request->time_period !== 'all') {
            $query->where(function($q) use ($request) {
                switch($request->time_period) {
                    case 'morning': $q->whereRaw('HOUR(event_date) >= 6 AND HOUR(event_date) < 12'); break;
                    case 'afternoon': $q->whereRaw('HOUR(event_date) >= 12 AND HOUR(event_date) < 18'); break;
                    case 'evening': $q->whereRaw('HOUR(event_date) >= 18 AND HOUR(event_date) < 24'); break;
                    case 'late-night': $q->whereRaw('HOUR(event_date) >= 0 AND HOUR(event_date) < 6'); break;
                }
            });
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate($request->get('per_page', 12));

        $user = Auth::guard('sanctum')->user();
        if ($user) {
            // Hydrate player social status
            $players = $events->getCollection()->pluck('player')->filter()->concat(
                $events->getCollection()->flatMap(fn($e) => $e->confirmedParticipants->pluck('player'))
            )->unique('id');
            Player::hydrateSocialStatus($players, $user);

            // Hydrate event participation status
            $joinedEventIds = EventParticipant::where('user_id', $user->id)
                ->where('status', 'confirmed')
                ->whereIn('event_id', $events->getCollection()->pluck('id'))
                ->pluck('event_id')
                ->toArray();
            
            foreach ($events->getCollection() as $event) {
                $event->has_joined = in_array($event->id, $joinedEventIds);
                $event->is_organizer = $event->user_id === $user->id;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get a single event by ID.
     */
    public function show($id)
    {
        $event = Event::with(['player', 'user', 'confirmedParticipants.player'])
            ->findOrFail($id);

        // Check if current user has joined
        $userId = Auth::id();
        $user = Auth::guard('sanctum')->user();
        $event->has_joined = $userId ? $event->hasParticipant($userId) : false;
        $event->is_organizer = $userId ? $event->user_id === $userId : false;

        if ($user) {
            $players = collect([$event->player])->concat($event->confirmedParticipants->pluck('player'))->filter()->unique('id');
            Player::hydrateSocialStatus($players, $user);
        }

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
    }

    /**
     * Create a new event.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        // Get user's player card
        $player = Player::where('user_id', $user->id)->first();
        if (!$player) {
            return response()->json(['error' => '請先建立球員卡'], 400);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'event_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:event_date',
            'location' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'fee' => 'required|integer|min:0',
            'max_participants' => 'required|integer|min:0|max:99', // 0 means unlimited
            'match_type' => 'required|in:all,singles,doubles,mixed',
            'gender' => 'nullable|in:all,male,female',
            'region' => 'required|string',
            'level_min' => 'nullable|string',
            'level_max' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ], [
            'title.required' => '請輸入活動主題',
            'event_date.required' => '請選擇開始時間',
            'event_date.after' => '開始時間必須是未來的時間',
            'end_date.after' => '結束時間必須晚於開始時間',
            'location.required' => '請輸入球場名稱',
            'fee.required' => '請輸入每人費用',
            'fee.integer' => '每人費用格式不正確',
            'max_participants.required' => '請選擇招募人數',
            'match_type.required' => '請選擇賽制類型',
            'region.required' => '請選擇活動地區',
            'notes.max' => '備註文字過長，請縮短內容',
        ]);

        $eventData = array_merge(
            [
                'user_id' => $user->id,
                'player_id' => $player->id,
                'status' => 'open',
            ],
            $validated
        );

        $event = Event::create($eventData);

        // Organizer automatically joins the event
        EventParticipant::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'player_id' => $player->id,
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        SendEventNotification::dispatch($event->id, 'created');

        return response()->json([
            'success' => true,
            'message' => '活動建立成功',
            'event' => $event->load(['player', 'confirmedParticipants.player']),
        ], 201);
    }

    /**
     * Update an event.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);

        if ($event->user_id !== $user->id) {
            return response()->json(['error' => '只有主辦人可以修改活動'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'event_date' => 'sometimes|date|after:now',
            'end_date' => 'nullable|date|after:event_date',
            'location' => 'sometimes|string|max:100',
            'address' => 'nullable|string|max:255',
            'fee' => 'sometimes|integer|min:0',
            'max_participants' => 'sometimes|integer|min:0|max:99',
            'match_type' => 'sometimes|in:all,singles,doubles,mixed',
            'gender' => 'nullable|in:all,male,female',
            'region' => 'sometimes|string',
            'level_min' => 'nullable|string',
            'level_max' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'status' => 'sometimes|in:open,closed,cancelled',
        ], [
            'event_date.after' => '開始時間必須是未來的時間',
            'end_date.after' => '結束時間必須晚於開始時間',
            'fee.integer' => '每人費用格式不正確',
            'notes.max' => '備註文字過長，請縮短內容',
        ]);

        $event->update($validated);

        SendEventNotification::dispatch($event->id, 'updated');

        return response()->json([
            'success' => true,
            'message' => '活動已更新',
            'event' => $event->fresh(['player', 'confirmedParticipants.player']),
        ]);
    }

    /**
     * Delete/cancel an event.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);

        if ($event->user_id !== $user->id) {
            return response()->json(['error' => '只有主辦人可以取消活動'], 403);
        }

        $event->update(['status' => 'cancelled']);

        SendEventNotification::dispatch($event->id, 'cancelled');

        return response()->json([
            'success' => true,
            'message' => '活動已取消'
        ]);
    }

    /**
     * Join an event.
     */
    public function join($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $player = Player::where('user_id', $user->id)->first();
        if (!$player) {
            return response()->json(['error' => '請先建立球員卡才能報名'], 400);
        }

        $event = Event::findOrFail($id);

        // Check if event is open
        if ($event->status !== 'open') {
            return response()->json(['error' => '此活動目前無法報名'], 400);
        }

        // Check if already full
        if ($event->is_full) {
            return response()->json(['error' => '活動已額滿'], 400);
        }

        // Check if already joined
        if ($event->hasParticipant($user->id)) {
            return response()->json(['error' => '您已報名此活動'], 400);
        }

        // Check level requirements (numeric compare if possible)
        $playerLevel = is_numeric($player->level) ? (float) $player->level : $player->level;
        $levelMin = is_numeric($event->level_min) ? (float) $event->level_min : $event->level_min;
        $levelMax = is_numeric($event->level_max) ? (float) $event->level_max : $event->level_max;

        if ($levelMin !== null && $levelMin !== '' && is_numeric($levelMin) && is_numeric($playerLevel) && $playerLevel < $levelMin) {
            return response()->json(['error' => '您的程度低於此活動要求'], 400);
        }
        if ($levelMax !== null && $levelMax !== '' && is_numeric($levelMax) && is_numeric($playerLevel) && $playerLevel > $levelMax) {
            return response()->json(['error' => '您的程度高於此活動要求'], 400);
        }


        // Create or update participation (handling re-joining cancelled events)
        EventParticipant::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'player_id' => $player->id,
                'status' => 'confirmed',
                'registered_at' => now(),
                'cancelled_at' => null,
            ]
        );

        // Update event status if full
        $event->refresh();
        if ($event->is_full) {
            $event->update(['status' => 'full']);
        }

        $event->has_joined = true;
        $event->is_organizer = $event->user_id === $user->id;

        return response()->json([
            'success' => true,
            'message' => '報名成功！',
            'event' => $event->load(['player', 'confirmedParticipants.player']),
        ]);
    }

    /**
     * Leave an event.
     */
    public function leave($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $event = Event::findOrFail($id);

        // Can't leave if you're the organizer
        if ($event->user_id === $user->id) {
            return response()->json(['error' => '主辦人無法取消報名，請直接取消活動'], 400);
        }

        $participant = EventParticipant::where('event_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->first();

        if (!$participant) {
            return response()->json(['error' => '您尚未報名此活動'], 400);
        }

        $participant->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Reopen event if it was full
        if ($event->status === 'full') {
            $event->update(['status' => 'open']);
        }

        $event->has_joined = false;
        $event->is_organizer = $event->user_id === $user->id;

        return response()->json([
            'success' => true,
            'message' => '已取消報名',
            'event' => $event->fresh(['player', 'confirmedParticipants.player']),
        ]);
    }

    /**
     * Get events organized by current user.
     */
    public function myOrganized()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $events = Event::with(['player', 'user', 'confirmedParticipants.player'])
            ->where('user_id', $user->id)
            ->orderBy('event_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get events joined by current user.
     */
    public function myJoined()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => '請先登入'], 401);
        }

        $eventIds = EventParticipant::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->pluck('event_id');

        $events = Event::with(['player', 'user', 'confirmedParticipants.player'])
            ->whereIn('id', $eventIds)
            ->orderBy('event_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Get LINE share data for an event.
     */
    public function share($id)
    {
        $event = Event::with('player')->findOrFail($id);

        $shareData = [
            'title' => $event->title,
            'text' => sprintf(
                "🎾 %s\n📅 %s\n📍 %s\n💰 $%d/人\n👥 剩餘 %d 位\n\n立即報名 👇",
                $event->title,
                $event->event_date->format('m/d (D) H:i'),
                $event->location,
                $event->fee,
                $event->spots_left
            ),
            'url' => url("/events/{$event->id}"),
        ];

        return response()->json([
            'success' => true,
            'data' => $shareData,
        ]);
    }

}
