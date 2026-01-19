<?php

namespace App\Services;

/**
 * LINE Flex Message 建構器
 * 用於建立約打訊息的 Flex Message 卡片
 */
class LineFlexMessageBuilder
{
    /**
     * 建立約打邀約通知的 Flex Message
     *
     * @param string $senderName 發送者名稱
     * @param string|null $senderAvatar 發送者頭像 URL
     * @param string $content 訊息內容
     * @return array Flex Message 內容
     */
    public static function buildMatchInviteMessage(string $senderName, ?string $senderAvatar, string $content): array
    {
        $shortContent = \Illuminate\Support\Str::limit($content, 100);

        // Construct Sender Box (Avatar + Name)
        $senderBoxContents = [];

        if ($senderAvatar) {
            $avatarUrl = str_starts_with($senderAvatar, 'http') ? $senderAvatar : asset($senderAvatar);
            $senderBoxContents[] = [
                "type" => "image",
                "url" => $avatarUrl,
                "size" => "xxs",
                "aspectMode" => "cover",
                "aspectRatio" => "1:1",
                "gravity" => "center",
                "flex" => 0
            ];
        }

        $senderBoxContents[] = [
            "type" => "text",
            "text" => $senderName,
            "weight" => "bold",
            "size" => "sm",
            "gravity" => "center",
            "flex" => 1,
            "margin" => "md"
        ];

        // Flex Message Structure (Premium Card with Avatar)
        return [
            "type" => "bubble",
            "header" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "🎾 收到約打邀約",
                        "weight" => "bold",
                        "color" => "#FFFFFF",
                        "size" => "md"
                    ]
                ],
                "backgroundColor" => "#2563EB",
                "paddingAll" => "md"
            ],
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "box",
                        "layout" => "horizontal",
                        "contents" => $senderBoxContents,
                        "alignItems" => "center"
                    ],
                    [
                        "type" => "separator",
                        "margin" => "lg"
                    ],
                    [
                        "type" => "text",
                        "text" => $shortContent,
                        "wrap" => true,
                        "size" => "xs",
                        "color" => "#64748B",
                        "margin" => "lg"
                    ]
                ],
                "paddingAll" => "lg"
            ],
            "footer" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "uri",
                            "label" => "立即查看訊息",
                            "uri" => "https://lovetennis.tw/messages"
                        ],
                        "style" => "primary",
                        "color" => "#2563EB",
                        "height" => "sm"
                    ]
                ],
                "paddingAll" => "md"
            ]
        ];
    }

    /**
     * 建立即時聊天室通知的 Flex Message
     *
     * @param string $roomName 聊天室名稱
     * @return array Flex Message 內容
     */
    public static function buildInstantChatNotification(string $roomName): array
    {
        return [
            "type" => "bubble",
            "header" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "🎾 即時聊天室",
                        "weight" => "bold",
                        "color" => "#FFFFFF",
                        "size" => "md"
                    ]
                ],
                "backgroundColor" => "#06C755", // 使用 LINE 綠色系
                "paddingAll" => "md"
            ],
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "「{$roomName}」有新訊息！",
                        "weight" => "bold",
                        "size" => "sm",
                        "color" => "#1E293B"
                    ],
                    [
                        "type" => "text",
                        "text" => "目前有多位球友在線等待，點擊下方按鈕加入揪球！",
                        "wrap" => true,
                        "size" => "xs",
                        "color" => "#64748B",
                        "margin" => "md"
                    ]
                ],
                "paddingAll" => "lg"
            ],
            "footer" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "button",
                        "action" => [
                            "type" => "uri",
                            "label" => "進入聊天室",
                            "uri" => config('app.url') . "/instant-play"
                        ],
                        "style" => "primary",
                        "color" => "#06C755",
                        "height" => "sm"
                    ]
                ],
                "paddingAll" => "md"
            ]
        ];
    }
}
