<?php

namespace App\Jobs;

use App\Services\EventNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEventNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventId;
    public $type;
    public $targetUserId;
    public $force;

    /**
     * Create a new job instance.
     */
    public function __construct(int $eventId, string $type, ?int $targetUserId = null, bool $force = false)
    {
        $this->eventId = $eventId;
        $this->type = $type;
        $this->targetUserId = $targetUserId;
        $this->force = $force;
    }

    /**
     * Execute the job.
     */
    public function handle(EventNotificationService $service)
    {
        $service->notifyById($this->eventId, $this->type, $this->targetUserId, $this->force);
    }
}
