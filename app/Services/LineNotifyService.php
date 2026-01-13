<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotifyService
{
    protected $channelAccessToken;

    public function __construct()
    {
        $this->channelAccessToken = config('services.line.message_token');
    }

    /**
     * Send a text message to a user.
     *
     * @param string $lineUserId
     * @param string $text
     * @return bool
     */
    public function sendTextMessage($lineUserId, $text)
    {
        if (empty($this->channelAccessToken)) {
            Log::warning('LINE Channel Access Token is not set.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->channelAccessToken,
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('LINE Message Push Failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('LINE Message Push Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a Flex Message (Card) to a user.
     *
     * @param string $lineUserId
     * @param string $altText
     * @param array $flexContents
     * @return bool
     */
    public function sendFlexMessage($lineUserId, $altText, $flexContents)
    {
        if (empty($this->channelAccessToken)) {
            Log::warning('LINE Channel Access Token is not set.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->channelAccessToken,
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'flex',
                        'altText' => $altText,
                        'contents' => $flexContents,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('LINE Flex Message Push Failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('LINE Flex Message Push Exception: ' . $e->getMessage());
            return false;
        }
    }
}
