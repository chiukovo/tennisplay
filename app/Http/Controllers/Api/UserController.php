<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Update user settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.default_region' => 'nullable|string',
        ]);

        $settings = $user->settings ?? [];
        $settings = array_merge($settings, $validated['settings']);
        
        $user->settings = $settings;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => '設置已更新'
        ]);
    }
}
