<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Services\LineNotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendEventCommentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $eventId;
    public $actorId;
    public $content;

    public function __construct(int $eventId, int $actorId, string $content)
    {
        $this->eventId = $eventId;
        $this->actorId = $actorId;
        $this->content = $content;
    }

    public function handle()
    {
        $event = Event::with('user')->find($this->eventId);
        $actor = User::find($this->actorId);

        if (!$event || !$event->user) {
            return;
        }

        $organizer = $event->user;
        if (!$organizer->line_user_id || ($actor && $organizer->id === $actor->id)) {
            return;
        }

        $settings = $organizer->settings ?? [];
        $wantsLine = $settings['notify_line'] ?? true;
        if (!$wantsLine) {
            return;
        }

        $senderName = $actor ? ($actor->name ?: '球友') : '球友';
        $text = sprintf(
            "💬 有人留言了\n活動：%s\n來自：%s\n內容：%s\n👉 %s",
            $event->title ?: '網球活動',
            $senderName,
            Str::limit($this->content, 80),
            url('/events/' . $event->id)
        );

        (new LineNotifyService())->sendTextMessage($organizer->line_user_id, $text);
    }
}
