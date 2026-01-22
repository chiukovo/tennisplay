<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Services\LineNotifyService;
use App\Services\PushNotificationService;
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

        $senderName = $actor ? ($actor->name ?: '球友') : '球友';
        $senderAvatar = $actor ? $actor->line_picture_url : null;
        $avatarUrl = $senderAvatar ? (str_starts_with($senderAvatar, 'http') ? $senderAvatar : asset($senderAvatar)) : null;

        $text = sprintf(
            "💬 有人留言了\n活動：%s\n來自：%s\n內容：%s\n👉 %s",
            $event->title ?: '網球活動',
            $senderName,
            Str::limit($this->content, 80),
            url('/events/' . $event->id)
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
                        'text' => $event->title ?: '網球活動',
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
                            'label' => '查看留言',
                            'uri' => url('/events/' . $event->id)
                        ],
                        'style' => 'primary',
                        'color' => '#2563EB',
                        'height' => 'sm'
                    ]
                ],
                'paddingAll' => 'md'
            ]
        ];

        if ($wantsLine) {
            // 使用 Queue 非同步發送
            (new LineNotifyService())->dispatchFlexMessage($organizer->id, $organizer->line_user_id, $text, $flexContents);
        }

        $wantsEventPush = $settings['notify_event'] ?? true;
        if ($wantsEventPush) {
            $pushTitle = '💬 新留言通知';
            $pushBody = sprintf('%s：%s', $senderName, Str::limit($this->content, 60));
            (new PushNotificationService())->sendToUserIds([$organizer->id], $pushTitle, $pushBody, [
                'event_id' => (string) $event->id,
                'type' => 'comment',
                'url' => url('/events/' . $event->id),
            ]);
        }
    }
}
