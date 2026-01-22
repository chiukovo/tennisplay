<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Store or refresh a device token.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'platform' => 'required|in:ios,android',
            'token' => 'required|string|max:512',
            'last_seen' => 'nullable|date',
        ]);

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $user->id,
                'platform' => $validated['platform'],
                'last_seen' => $validated['last_seen'] ?? now(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $deviceToken,
        ]);
    }

    /**
     * Update an existing device token record.
     */
    public function update(Request $request, DeviceToken $deviceToken)
    {
        $user = $request->user();

        if ($deviceToken->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限',
            ], 403);
        }

        $validated = $request->validate([
            'platform' => 'nullable|in:ios,android',
            'token' => 'nullable|string|max:512',
            'last_seen' => 'nullable|date',
        ]);

        if (array_key_exists('token', $validated) && $validated['token']) {
            $conflict = DeviceToken::where('token', $validated['token'])
                ->where('id', '!=', $deviceToken->id)
                ->first();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token 已被使用',
                ], 422);
            }
        }

        $payload = [];
        if (array_key_exists('platform', $validated)) {
            $payload['platform'] = $validated['platform'];
        }
        if (array_key_exists('token', $validated)) {
            $payload['token'] = $validated['token'];
        }

        $payload['last_seen'] = $validated['last_seen'] ?? now();

        $deviceToken->fill($payload);
        $deviceToken->save();

        return response()->json([
            'success' => true,
            'data' => $deviceToken,
        ]);
    }
}