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
            'redirect_uri' => route('line.callback'),
            'state' => $state,
            'scope' => 'profile openid',
            'bot_prompt' => 'normal',
            'prompt' => 'consent', // Force consent screen to help with some mobile issues
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
            session()->forget('line_oauth_state');
            return redirect('/auth?error=' . urlencode('驗證失敗，請重試'));
        }
        session()->forget('line_oauth_state');

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
                'redirect_uri' => route('line.callback'),
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
                    $avatarResponse = Http::timeout(5)->get($linePicture);
                    $contentType = $avatarResponse->header('Content-Type');
                    $isImage = $contentType && str_starts_with($contentType, 'image/');
                    $maxBytes = 2 * 1024 * 1024; // 2MB limit

                    if ($avatarResponse->successful() && $isImage && strlen($avatarResponse->body()) <= $maxBytes) {
                        $extension = 'jpg'; // LINE avatars are usually jpg
                        $filename = 'avatar_' . $lineUserId . '.' . $extension;
                        $path = 'avatars/' . $filename;
                        
                        // Ensure directory exists
                        if (!Storage::disk('public')->exists('avatars')) {
                            Storage::disk('public')->makeDirectory('avatars');
                        }

                        // Save to storage/app/public/avatars (overwrites existing file)
                        Storage::disk('public')->put($path, $avatarResponse->body());
                        
                        // Add version query string to bust cache
                        $avatarVersion = time();
                        $localAvatarPath = '/storage/' . $path . '?v=' . $avatarVersion;
                    } else {
                        $localAvatarPath = $linePicture;
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
                // Avatar file is now overwritten with the same filename, no need to delete old file

                $updateData = [
                    'line_picture_url' => $localAvatarPath ?: $user->line_picture_url,
                ];
                if (empty($user->name)) {
                    $updateData['name'] = $lineName;
                }
                $user->update($updateData);
            }

            // Create Sanctum token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Return loading view instead of redirecting directly
            // This allows the frontend to save token cleanly and provides a better UX
            return view('pages.auth-loading', [
                'token' => $token, 
                'user' => urlencode(json_encode([
                    'uid' => $user->uid,
                    'name' => $user->name,
                    'line_picture_url' => $user->line_picture_url,
                    'gender' => $user->gender,
                    'region' => $user->region,
                    'level' => $user->level,
                    'role' => $user->role,
                    'id' => $user->id
                ]))
            ]);


        } catch (\Exception $e) {
            Log::error('LINE login error: ' . $e->getMessage());
            return redirect('/auth?error=' . urlencode('LINE 登入發生錯誤，請稍後再試'));
        }
    }
    /**
     * Handle LINE Webhook.
     */
    public function handleWebhook(Request $request)
    {
        // LINE sends a POST request with events. 
        // We just need to return 200 to acknowledge.
        // You can add signature verification here for security.
        
        Log::info('LINE Webhook received', ['payload' => $request->all()]);
        
        return response('OK', 200);
    }
}

