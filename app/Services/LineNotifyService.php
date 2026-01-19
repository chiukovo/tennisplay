<?php

namespace App\Services;

use App\Jobs\SendLineNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotifyService
{
    protected $channelAccessToken;

    /**
     * HTTP 請求超時時間 (秒)
     */
    protected int $timeout = 10;

    /**
     * HTTP 重試次數
     */
    protected int $httpRetries = 2;

    /**
     * HTTP 重試間隔 (毫秒)
     */
    protected int $httpRetryDelay = 500;

    public function __construct()
    {
        $this->channelAccessToken = config('services.line.message_token');
    }

    /**
     * 取得配置好的 HTTP Client (含重試機制)
     */
    protected function getHttpClient()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->channelAccessToken,
        ])
        ->timeout($this->timeout)
        ->retry($this->httpRetries, $this->httpRetryDelay, function ($exception, $request) {
            // 只對暫時性錯誤重試 (網路錯誤、5xx 錯誤)
            if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                return true;
            }
            if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                return $exception->response->status() >= 500;
            }
            return false;
        });
    }

    /**
     * Send a text message to a user (同步).
     *
     * @param string $lineUserId
     * @param string $text
     * @return bool
     */
    public function sendTextMessage($lineUserId, $text)
    {
        if (empty($this->channelAccessToken)) {
            Log::channel('notify')->warning('LINE Channel Access Token is not set.');
            return false;
        }

        try {
            $response = $this->getHttpClient()->post('https://api.line.me/v2/bot/message/push', [
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

            Log::channel('notify')->error('LINE Message Push Failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::channel('notify')->error('LINE Message Push Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a Flex Message (Card) to a user (同步).
     *
     * @param string $lineUserId
     * @param string $altText
     * @param array $flexContents
     * @return bool
     */
    public function sendFlexMessage($lineUserId, $altText, $flexContents)
    {
        if (empty($this->channelAccessToken)) {
            Log::channel('notify')->warning('LINE Channel Access Token is not set.');
            return false;
        }

        try {
            $response = $this->getHttpClient()->post('https://api.line.me/v2/bot/message/push', [
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

            Log::channel('notify')->error('LINE Flex Message Push Failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::channel('notify')->error('LINE Flex Message Push Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 派發文字訊息到 Queue (非同步).
     *
     * @param int $userId 接收者的 user_id
     * @param string $lineUserId 接收者的 LINE User ID
     * @param string $text 訊息內容
     * @return void
     */
    public function dispatchTextMessage(int $userId, string $lineUserId, string $text): void
    {
        SendLineNotification::dispatch(
            $userId,
            $lineUserId,
            'text',
            $text,
            $text
        );
    }

    /**
     * 派發 Flex 訊息到 Queue (非同步).
     *
     * @param int $userId 接收者的 user_id
     * @param string $lineUserId 接收者的 LINE User ID
     * @param string $altText 替代文字
     * @param array $flexContents Flex Message 內容
     * @return void
     */
    public function dispatchFlexMessage(int $userId, string $lineUserId, string $altText, array $flexContents): void
    {
        SendLineNotification::dispatch(
            $userId,
            $lineUserId,
            'flex',
            $altText,
            $flexContents
        );
    }
}
