<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected string $scope = 'https://www.googleapis.com/auth/firebase.messaging';

    public function sendToUserIds(array $userIds, string $title, string $body, array $data = []): void
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->all();
        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return;
        }

        $accessToken = $this->getAccessToken();
        $projectId = config('services.firebase.project_id');
        if (!$accessToken || !$projectId) {
            return;
        }

        $endpoint = sprintf('https://fcm.googleapis.com/v1/projects/%s/messages:send', $projectId);
        $payloadData = $this->normalizeData($data);

        foreach ($tokens as $token) {
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $payloadData,
                ],
            ];

            try {
                $response = Http::withToken($accessToken)->post($endpoint, $message);
                if (!$response->successful()) {
                    Log::error('FCM push failed', [
                        'token' => $token,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('FCM push exception', [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function getAccessToken(): ?string
    {
        $projectId = config('services.firebase.project_id');
        $clientEmail = config('services.firebase.client_email');
        $privateKey = config('services.firebase.private_key');

        if (!$projectId || !$clientEmail || !$privateKey) {
            Log::warning('Firebase config missing for push notifications');
            return null;
        }

        return Cache::remember('firebase_access_token', now()->addMinutes(55), function () use ($clientEmail, $privateKey) {
            $jwt = $this->buildJwt($clientEmail, $privateKey);
            if (!$jwt) {
                return null;
            }

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('Firebase token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('access_token');
        });
    }

    protected function buildJwt(string $clientEmail, string $privateKey): ?string
    {
        $privateKey = str_replace('\\n', "\n", $privateKey);

        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $clientEmail,
            'sub' => $clientEmail,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => $this->scope,
        ]));

        $signingInput = $header . '.' . $payload;

        $signature = '';
        $success = openssl_sign($signingInput, $signature, $privateKey, 'sha256');
        if (!$success) {
            Log::error('Firebase JWT sign failed');
            return null;
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function normalizeData(array $data): array
    {
        $normalized = [];
        foreach ($data as $key => $value) {
            $normalized[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }
        return $normalized;
    }
}