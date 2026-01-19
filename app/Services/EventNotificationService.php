<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EventNotificationService
{
    /**
     * Notify by event id.
     */
    public function notifyById(int $eventId, string $type, ?int $targetUserId = null, bool $force = false): void
    {
        $event = Event::find($eventId);
        if (!$event) {
            return;
        }
        $this->notify($event, $type, $targetUserId, $force);
    }

    /**
     * Notify users about event changes.
     */
    public function notify(Event $event, string $type, ?int $targetUserId = null, bool $force = false): void
    {
        $event->refresh();

        switch ($type) {
            case 'created':
                $cacheKey = 'event_notify_created_' . $event->id;
                $ttl = now()->addDays(30);
                break;
            case 'updated':
                $stamp = optional($event->updated_at)->timestamp ?: time();
                $cacheKey = 'event_notify_updated_' . $event->id . '_' . $stamp;
                $ttl = now()->addDays(7);
                break;
            case 'cancelled':
                $cacheKey = 'event_notify_cancelled_' . $event->id;
                $ttl = now()->addDays(30);
                break;
            default:
                $cacheKey = 'event_notify_misc_' . $event->id . '_' . time();
                $ttl = now()->addDays(1);
                break;
        }

        if (!$force && !Cache::add($cacheKey, true, $ttl)) {
            return;
        }

        $this->sendEventNotification($event, $type, $targetUserId);
    }

    /**
     * Build recipients and send LINE notifications.
     */
    protected function sendEventNotification(Event $event, string $type, ?int $targetUserId = null): void
    {
        try {
            $event->loadMissing(['user']);

            $lineService = new LineNotifyService();
            $url = url('/events/' . $event->id);
            $dateText = optional($event->event_date)->format('m/d H:i');

            switch ($type) {
                case 'created':
                    $titlePrefix = '🎾 新活動建立';
                    break;
                case 'updated':
                    $titlePrefix = '🔔 活動已更新';
                    break;
                case 'cancelled':
                    $titlePrefix = '⚠️ 活動已取消';
                    break;
                default:
                    $titlePrefix = '🎾 活動通知';
                    break;
            }

            $text = sprintf(
                "%s：%s\n📅 %s\n📍 %s\n💰 $%d/人\n👉 %s",
                $titlePrefix,
                $event->title,
                $dateText ?: '時間待確認',
                $event->location ?: '地點待確認',
                (int) $event->fee,
                $url
            );

            $organizerName = $event->user ? ($event->user->name ?: '主辦人') : '主辦人';
            $organizerAvatar = $event->user ? $event->user->line_picture_url : null;
            $avatarUrl = $organizerAvatar ? (str_starts_with($organizerAvatar, 'http') ? $organizerAvatar : asset($organizerAvatar)) : null;

            $flexContents = [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $titlePrefix,
                            'weight' => 'bold',
                            'color' => '#FFFFFF',
                            'size' => 'md'
                        ],
                        [
                            'type' => 'text',
                            'text' => $event->title ?: '活動通知',
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
                                            'text' => '主辦人',
                                            'size' => 'xs',
                                            'color' => '#94A3B8'
                                        ],
                                        [
                                            'type' => 'text',
                                            'text' => $organizerName,
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
                            'type' => 'box',
                            'layout' => 'vertical',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => "📅 時間：" . ($dateText ?: '時間待確認'),
                                    'size' => 'sm',
                                    'wrap' => true,
                                    'color' => '#334155'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "📍 地點：" . ($event->location ?: '地點待確認'),
                                    'size' => 'sm',
                                    'wrap' => true,
                                    'color' => '#334155'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "💰 費用：$" . (int) $event->fee . "/人",
                                    'size' => 'sm',
                                    'wrap' => true,
                                    'color' => '#334155'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => "🏟️ 地區：" . ($event->region ?: '未設定'),
                                    'size' => 'sm',
                                    'wrap' => true,
                                    'color' => '#334155'
                                ]
                            ]
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
                                'label' => '立即查看活動',
                                'uri' => $url
                            ],
                            'style' => 'primary',
                            'color' => '#2563EB',
                            'height' => 'sm'
                        ]
                    ],
                    'paddingAll' => 'md'
                ]
            ];

            $recipientIds = $targetUserId
                ? collect([$targetUserId])
                : $this->getEventNotifyRecipientIds($event, $type);
            if ($recipientIds->isEmpty()) {
                return;
            }

            User::whereIn('id', $recipientIds)
                ->whereNotNull('line_user_id')
                ->chunk(200, function ($users) use ($lineService, $text, $flexContents, $event, $type) {
                    foreach ($users as $u) {
                        $settings = $u->settings ?? [];
                        $wantsLine = $settings['notify_line'] ?? true;
                        $wantsEvent = $settings['notify_event'] ?? true;
                        if (!$wantsLine || !$wantsEvent) {
                            continue;
                        }
                        // 使用 Queue 非同步發送，不阻塞當前 Job
                        $lineService->dispatchFlexMessage($u->id, $u->line_user_id, $text, $flexContents);
                        
                        Log::channel('notify')->info('event_notify_dispatched', [
                            'event_id' => $event->id,
                            'type' => $type,
                            'user_id' => $u->id,
                        ]);
                    }
                });
        } catch (\Throwable $e) {
            Log::error('Event notification error: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'type' => $type,
            ]);
        }
    }

    /**
     * Get recipient user IDs for event notifications.
     * - created: region users + followers + participants (excluding organizer)
     * - updated/cancelled: followers + participants (excluding organizer)
     */
    protected function getEventNotifyRecipientIds(Event $event, string $type)
    {
        $participantIds = EventParticipant::confirmed()
            ->where('event_id', $event->id)
            ->pluck('user_id');

        $followerIds = Follow::where('following_id', $event->user_id)
            ->pluck('follower_id');

        $ids = $participantIds->merge($followerIds);

        if ($type === 'created') {
            $region = $event->region;
            if ($region) {
                $regionIds = User::query()
                    ->where(function ($q) use ($region) {
                        $q->where('region', $region)
                          ->orWhere('settings->default_region', $region);
                    })
                    ->pluck('id');
                $ids = $ids->merge($regionIds);
            }
        }

        return $ids->filter(function ($id) use ($event) {
                return $id && $id !== $event->user_id;
            })
            ->unique()
            ->values();
    }
}
