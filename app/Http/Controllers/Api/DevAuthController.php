<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Development Authentication Controller
 * 
 * This controller is ONLY for local development and testing.
 * It allows quick login without LINE OAuth for testing purposes.
 */
class DevAuthController extends Controller
{
    /**
     * Show the dev login form.
     * Only available in local environment.
     */
    public function showLoginForm()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        return view('pages.dev-login');
    }

    /**
     * Handle dev login - login or create user by line_id.
     * Only available in local environment.
     */
    public function devLogin(Request $request)
    {
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is only available in local environment',
            ], 403);
        }

        $request->validate([
            'line_id' => 'required|string|min:1|max:100',
        ]);

        $lineId = $request->input('line_id');

        // Find existing user or create new one
        $user = User::where('line_user_id', $lineId)->first();

        if (!$user) {
            // Create new user with the provided line_id
            $user = User::create([
                'line_user_id' => $lineId,
                'name' => 'Dev User ' . Str::substr($lineId, 0, 8),
                'line_picture_url' => null,
            ]);
        }

        // Create Sanctum token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Return loading view (same as LINE callback)
        return view('pages.auth-loading', [
            'token' => $token,
            'user' => urlencode(json_encode([
                'uid' => $user->uid,
                'name' => $user->name,
                'line_picture_url' => $user->line_picture_url,
                'gender' => $user->gender,
                'region' => $user->region,
                'level' => $user->level ?? null,
                'role' => $user->role ?? null,
                'id' => $user->id
            ]))
        ]);
    }
}
