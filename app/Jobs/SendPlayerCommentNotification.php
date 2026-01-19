<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\PlayerComment;
use App\Models\User;
use App\Services\LineNotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SendPlayerCommentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $playerId;
    public $actorId;
    public $content;
    public $commentId;
    public $rating;

    public function __construct(int $playerId, int $actorId, int $commentId, ?string $content, ?int $rating = null)
    {
        $this->playerId = $playerId;
        $this->actorId = $actorId;
        $this->commentId = $commentId;
        $this->content = $content;
        $this->rating = $rating;
    }

    public function handle()
    {
        $player = Player::with('user')->find($this->playerId);
        $actor = User::find($this->actorId);

        if (!$player || !$player->user) {
            return;
        }

        $owner = $player->user;
        $recipientIds = PlayerComment::where('player_id', $player->id)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if ($owner) {
            $recipientIds[] = $owner->id;
        }

        $recipientIds = array_values(array_unique(array_filter($recipientIds, function ($id) {
            return (int)$id !== (int)$this->actorId;
        })));

        if (empty($recipientIds)) {
            return;
        }

        $senderName = $actor ? ($actor->name ?: '球友') : '球友';
        $senderAvatar = $actor ? $actor->line_picture_url : null;
        $avatarUrl = $senderAvatar ? (str_starts_with($senderAvatar, 'http') ? $senderAvatar : asset($senderAvatar)) : null;

        // Construct message content
        $messageBody = "";
        if ($this->rating) {
            $messageBody .= "⭐ 獲得 " . $this->rating . " 顆星評價\n";
        }
        if ($this->content) {
            $messageBody .= "留言內容：" . Str::limit($this->content, 80);
        } else if (!$this->rating) {
            $messageBody .= "（無內容）";
        }

        $text = sprintf(
            "💬 有人留言了\n球友卡：%s\n來自：%s\n%s\n👉 %s",
            $player->name ?: '球友卡',
            $senderName,
            $messageBody,
            url('/profile/' . ($owner->uid ?? $owner->id))
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
                    // Rating Display
                    $this->rating ? [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => '⭐ 獲得 ' . $this->rating . ' 顆星評價',
                                'weight' => 'bold',
                                'color' => '#F59E0B',
                                'size' => 'sm'
                            ]
                        ],
                        'margin' => 'md'
                    ] : null,
                    // Content Display
                    $this->content ? [
                        'type' => 'text',
                        'text' => $this->content,
                        'size' => 'sm',
                        'wrap' => true,
                        'color' => '#334155',
                        'margin' => 'sm'
                    ] : null
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
                            'uri' => url('/profile/' . ($owner->uid ?? $owner->id))
                        ],
                        'style' => 'primary',
                        'color' => '#2563EB',
                        'height' => 'sm'
                    ]
                ],
                'paddingAll' => 'md'
            ]
        ];

        $lineService = new LineNotifyService();
        foreach ($recipientIds as $userId) {
            $recipient = User::find($userId);
            if (!$recipient || !$recipient->line_user_id) {
                continue;
            }
            $settings = $recipient->settings ?? [];
            $wantsLine = $settings['notify_line'] ?? true;
            if (!$wantsLine) {
                continue;
            }

            $cacheKey = 'player_comment_notify_' . $this->commentId . '_' . $recipient->id;
            if (!Cache::add($cacheKey, true, now()->addDay())) {
                continue;
            }
            // 使用 Queue 非同步發送
            $lineService->dispatchFlexMessage($recipient->id, $recipient->line_user_id, $text, $flexContents);
        }
    }
}
