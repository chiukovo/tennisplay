<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSafetyController extends Controller
{
    public function report(Request $request, $uid)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }

        $data = $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        $reported = is_numeric($uid)
            ? User::findOrFail($uid)
            : User::where('uid', $uid)->firstOrFail();

        if ((string)$reported->id === (string)$user->id) {
            return response()->json(['success' => false, 'message' => '無法檢舉自己'], 400);
        }

        UserReport::create([
            'reporter_id' => $user->id,
            'reported_id' => $reported->id,
            'message' => $data['message'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '已送出檢舉',
        ]);
    }

    public function block(Request $request, $uid)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }

        $blocked = is_numeric($uid)
            ? User::findOrFail($uid)
            : User::where('uid', $uid)->firstOrFail();

        if ((string)$blocked->id === (string)$user->id) {
            return response()->json(['success' => false, 'message' => '無法封鎖自己'], 400);
        }

        UserBlock::firstOrCreate([
            'blocker_id' => $user->id,
            'blocked_id' => $blocked->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => '已封鎖對方',
        ]);
    }

    public function unblock(Request $request, $uid)
    {
        $user = $request->user('sanctum') ?: Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => '未授權'], 401);
        }

        $blocked = is_numeric($uid)
            ? User::findOrFail($uid)
            : User::where('uid', $uid)->firstOrFail();

        UserBlock::where('blocker_id', $user->id)
            ->where('blocked_id', $blocked->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => '已解除封鎖',
        ]);
    }
}
