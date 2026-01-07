<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    /**
     * Logout user and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => '已登出',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    /**
     * Redirect to LINE OAuth2 authorization page.
     */
    public function lineLogin(Request $request)
    {
        $state = Str::random(32);
        session(['line_oauth_state' => $state]);

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line.client_id'),
            'redirect_uri' => config('services.line.redirect'),
            'state' => $state,
            'scope' => 'profile openid',
        ]);

        return redirect('https://access.line.me/oauth2/v2.1/authorize?' . $params);
    }

    /**
     * Handle LINE OAuth2 callback.
     */
    public function lineCallback(Request $request)
    {
        // Verify state to prevent CSRF
        if ($request->state !== session('line_oauth_state')) {
            return redirect('/auth?error=' . urlencode('驗證失敗，請重試'));
        }

        // Check for error from LINE
        if ($request->has('error')) {
            Log::error('LINE OAuth error: ' . $request->error_description);
            return redirect('/auth?error=' . urlencode('LINE 登入失敗：' . $request->error_description));
        }

        try {
            // Exchange code for access token
            $tokenResponse = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.line.redirect'),
                'client_id' => config('services.line.client_id'),
                'client_secret' => config('services.line.client_secret'),
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('LINE token exchange failed: ' . $tokenResponse->body());
                return redirect('/auth?error=' . urlencode('無法取得 LINE 授權'));
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];

            // Get user profile from LINE
            $profileResponse = Http::withToken($accessToken)
                ->get('https://api.line.me/v2/profile');

            if (!$profileResponse->successful()) {
                Log::error('LINE profile fetch failed: ' . $profileResponse->body());
                return redirect('/auth?error=' . urlencode('無法取得 LINE 用戶資料'));
            }

            $lineUser = $profileResponse->json();
            $lineUserId = $lineUser['userId'];
            $lineName = $lineUser['displayName'];
            $linePicture = $lineUser['pictureUrl'] ?? null;

            // Download and save avatar locally if it exists
            $localAvatarPath = null;
            if ($linePicture) {
                try {
                    $avatarResponse = Http::get($linePicture);
                    if ($avatarResponse->successful()) {
                        $extension = 'jpg'; // LINE avatars are usually jpg
                        $filename = 'avatar_' . $lineUserId . '_' . time() . '.' . $extension;
                        $path = 'avatars/' . $filename;
                        
                        // Ensure directory exists
                        if (!Storage::disk('public')->exists('avatars')) {
                            Storage::disk('public')->makeDirectory('avatars');
                        }

                        // Save to storage/app/public/avatars
                        Storage::disk('public')->put($path, $avatarResponse->body());
                        $localAvatarPath = '/storage/' . $path;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to download LINE avatar: ' . $e->getMessage());
                    // Fallback to original URL if download fails
                    $localAvatarPath = $linePicture;
                }
            }

            // Find or create user
            $user = User::where('line_user_id', $lineUserId)->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'line_user_id' => $lineUserId,
                    'name' => $lineName,
                    'line_picture_url' => $localAvatarPath,
                ]);
            } else {
                // Update existing user's LINE info
                // If we have a new local avatar, we might want to delete the old one
                $oldRawPath = $user->getRawOriginal('line_picture_url');
                if ($localAvatarPath && $oldRawPath && Str::startsWith($oldRawPath, '/storage/avatars/')) {
                    $oldPath = str_replace('/storage/', '', $oldRawPath);
                    Storage::disk('public')->delete($oldPath);
                }

                $user->update([
                    'name' => $lineName,
                    'line_picture_url' => $localAvatarPath ?: $user->line_picture_url,
                ]);
            }

            // Create Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Redirect back to frontend with token
            return redirect('/?line_token=' . $token . '&line_user=' . urlencode(json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'line_picture_url' => $user->line_picture_url,
            ])));

        } catch (\Exception $e) {
            Log::error('LINE login error: ' . $e->getMessage());
            return redirect('/auth?error=' . urlencode('LINE 登入發生錯誤，請稍後再試'));
        }
    }
}

