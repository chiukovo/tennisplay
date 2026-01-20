<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MobileDevice;
use Illuminate\Support\Facades\Auth;

class MobileController extends Controller
{
    public function registerToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|string',
            'model' => 'nullable|string',
            'os_version' => 'nullable|string',
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $device = MobileDevice::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
                'model' => $request->model,
                'os_version' => $request->os_version,
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Token registered successfully',
            'device' => $device
        ]);
    }
}
