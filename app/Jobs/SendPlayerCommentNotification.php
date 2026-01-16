<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\User;
use App\Services\LineNotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendPlayerCommentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $playerId;
    public $actorId;
    public $content;

    public function __construct(int $playerId, int $actorId, string $content)
    {
        $this->playerId = $playerId;
        $this->actorId = $actorId;
        $this->content = $content;
    }

    public function handle()
    {
        $player = Player::with('user')->find($this->playerId);
        $actor = User::find($this->actorId);

        if (!$player || !$player->user) {
            return;
        }

        $owner = $player->user;
        if (!$owner->line_user_id || ($actor && $owner->id === $actor->id)) {
            return;
        }

        $settings = $owner->settings ?? [];
        $wantsLine = $settings['notify_line'] ?? true;
        if (!$wantsLine) {
            return;
        }

        $senderName = $actor ? ($actor->name ?: '球友') : '球友';
        $text = sprintf(
            "💬 有人留言了\n球友卡：%s\n來自：%s\n內容：%s\n👉 %s",
            $player->name ?: '球友卡',
            $senderName,
            Str::limit($this->content, 80),
            url('/profile/' . $owner->uid)
        );

        (new LineNotifyService())->sendTextMessage($owner->line_user_id, $text);
    }
}
