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
        $senderAvatar = $actor ? $actor->line_picture_url : null;
        $avatarUrl = $senderAvatar ? (str_starts_with($senderAvatar, 'http') ? $senderAvatar : asset($senderAvatar)) : null;

        $text = sprintf(
            "💬 有人留言了\n球友卡：%s\n來自：%s\n內容：%s\n👉 %s",
            $player->name ?: '球友卡',
            $senderName,
            Str::limit($this->content, 80),
            url('/profile/' . $owner->uid)
        );

        $flexContents = [
            'type' => 'bubble',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '💬 新留言通知',
                        'weight' => 'bold',
                        'color' => '#FFFFFF',
                        'size' => 'md'
                    ],
                    [
                        'type' => 'text',
                        'text' => $player->name ?: '球友卡',
                        'weight' => 'bold',
                        'color' => '#FFFFFF',
                        'size' => 'lg',
                        'margin' => 'sm',
                        'wrap' => true
                    ]
                ],
                'backgroundColor' => '#2563EB',
                'paddingAll' => 'md'
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'spacing' => 'md',
                'contents' => array_values(array_filter([
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => array_values(array_filter([
                            $avatarUrl ? [
                                'type' => 'image',
                                'url' => $avatarUrl,
                                'size' => 'sm',
                                'aspectMode' => 'cover',
                                'aspectRatio' => '1:1',
                                'gravity' => 'center'
                            ] : null,
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => '留言者',
                                        'size' => 'xs',
                                        'color' => '#94A3B8'
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $senderName,
                                        'size' => 'sm',
                                        'weight' => 'bold',
                                        'color' => '#0F172A'
                                    ]
                                ],
                                'margin' => 'md'
                            ]
                        ]))
                    ],
                    [
                        'type' => 'separator',
                        'margin' => 'md'
                    ],
                    [
                        'type' => 'text',
                        'text' => '留言內容：' . Str::limit($this->content, 120),
                        'size' => 'sm',
                        'wrap' => true,
                        'color' => '#334155'
                    ]
                ]))
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => '查看主頁',
                            'uri' => url('/profile/' . $owner->uid)
                        ],
                        'style' => 'primary',
                        'color' => '#2563EB',
                        'height' => 'sm'
                    ]
                ],
                'paddingAll' => 'md'
            ]
        ];

        (new LineNotifyService())->sendFlexMessage($owner->line_user_id, $text, $flexContents);
    }
}
