<?php

namespace App\Services;

use App\Models\MobileDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $serverKey;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.key');
    }

    /**
     * Send notification to a specific user's devices
     */
    public function notifyUser($userId, $title, $body, $data = [])
    {
        $tokens = MobileDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return false;
        }

        return $this->send($tokens, $title, $body, $data);
    }

    /**
     * Send notification to multiple tokens
     */
    public function send(array $tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) {
            return false;
        }

        if (!$this->serverKey) {
            Log::warning('FCM server key not configured.');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $this->serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
            ],
            'data' => $data,
            'priority' => 'high',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('FCM Notification failed: ' . $response->body());
        return false;
    }
}
