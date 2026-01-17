<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstantGlobalStatsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $active_count;
    public $display_count;
    public $avatars;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $activeCount, array $avatars)
    {
        $this->active_count = $activeCount;
        $this->display_count = $activeCount;
        $this->avatars = $avatars;
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
            new \Illuminate\Broadcasting\Channel('instant-public')
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    // public function broadcastAs()
    // {
    //     return 'global.stats.updated';
    // }
}
