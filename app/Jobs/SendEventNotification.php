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

    /**
     * Create a new job instance.
     */
    public function __construct(int $eventId, string $type)
    {
        $this->eventId = $eventId;
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(EventNotificationService $service)
    {
        $service->notifyById($this->eventId, $this->type);
    }
}
