<?php

namespace App\Events;

use App\Models\InstantRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstantRoomStatsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room_slug;
    public $active_count;
    public $active_avatars;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $roomSlug, int $activeCount, array $activeAvatars)
    {
        $this->room_slug = $roomSlug;
        $this->active_count = $activeCount;
        $this->active_avatars = $activeAvatars;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PresenceChannel('instant-lobby'),
            new Channel('instant-public')
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    // public function broadcastAs()
    // {
    //     return 'room.stats.updated';
    // }
}
