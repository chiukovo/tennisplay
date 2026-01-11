<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $query = Player::with('user')->withCount(['likes', 'comments'])->active()
            ->inRegion($request->region)
            ->atLevel($request->level)
            ->search($request->search);

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        $players = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        Player::hydrateSocialStatus($players, $this->resolveUser($request));

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    public function show($id)
    {
        $player = Player::with('user')->withCount(['likes', 'comments'])->findOrFail($id);
        Player::hydrateSocialStatus($player, $this->resolveUser(request()));

        return response()->json([
            'success' => true,
            'data' => $player,
        ]);
    }

    public function myCards(Request $request)
    {
        $user = $this->resolveUser($request);
        $players = Player::with('user')
            ->withCount(['likes', 'comments'])
            ->where('user_id', $user ? $user->id : null)
            ->orderBy('created_at', 'desc')
            ->get();

        Player::hydrateSocialStatus($players, $user);

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string',
            'level' => 'required|string',
            'gender' => 'required|string',
            'handed' => 'required|string',
            'backhand' => 'string|nullable',
            'intro' => 'string|nullable',
            'fee' => 'string|nullable',
            'theme' => 'string|nullable',
        ]);

        $data = $request->only([
            'name', 'region', 'level', 'gender', 'handed', 'backhand',
            'intro', 'fee', 'theme', 'photo_x', 'photo_y', 'photo_scale',
            'sig_x', 'sig_y', 'sig_scale', 'sig_rotate', 'sig_width', 'sig_height'
        ]);

        if ($request->photo && Str::startsWith($request->photo, 'data:image')) {
            $data['photo'] = $this->saveBase64Image($request->photo, 'players/photos');
        } elseif ($request->photo && Str::startsWith($request->photo, 'http')) {
            $data['photo'] = $this->downloadRemoteImage($request->photo, 'players/photos');
        } elseif ($request->photo) {
            $data['photo'] = $request->photo;
        }

        if ($request->signature && Str::startsWith($request->signature, 'data:image')) {
            $data['signature'] = $this->saveBase64Image($request->signature, 'players/signatures');
        }

        $user = $this->resolveUser($request);
        if ($user) {
            $data['user_id'] = $user->id;
        }

        if (isset($data['user_id'])) {
            $player = Player::updateOrCreate(
                ['user_id' => $data['user_id']],
                $data
            );
        } else {
            $player = Player::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => '球友卡建立成功！',
            'data' => $player->fresh(),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $user = $this->resolveUser($request);
        if ($user && $player->user_id && $player->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限編輯此球友卡',
            ], 403);
        }

        if ($user && !$player->user_id) {
            $player->user_id = $user->id;
        }

        $data = $request->only([
            'name', 'region', 'level', 'gender', 'handed', 'backhand',
            'intro', 'fee', 'theme', 'photo_x', 'photo_y', 'photo_scale',
            'sig_x', 'sig_y', 'sig_scale', 'sig_rotate', 'sig_width', 'sig_height', 'is_active'
        ]);

        if ($request->photo && Str::startsWith($request->photo, 'data:image')) {
            if ($player->photo && !Str::startsWith($player->photo, 'http')) {
                Storage::disk('public')->delete($player->photo);
            }
            $data['photo'] = $this->saveBase64Image($request->photo, 'players/photos');
        } elseif ($request->photo && Str::startsWith($request->photo, 'http')) {
            if ($request->photo !== $player->photo) {
                $data['photo'] = $this->downloadRemoteImage($request->photo, 'players/photos');
            }
        }

        if ($request->signature && Str::startsWith($request->signature, 'data:image')) {
            if ($player->signature && !Str::startsWith($player->signature, 'http')) {
                Storage::disk('public')->delete($player->signature);
            }
            $data['signature'] = $this->saveBase64Image($request->signature, 'players/signatures');
        }

        $player->update($data);

        return response()->json([
            'success' => true,
            'message' => '球友卡已更新',
            'data' => $player->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $user = $this->resolveUser($request);
        if ($user && $player->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限刪除此球友卡',
            ], 403);
        }

        if ($player->photo && !Str::startsWith($player->photo, 'http')) {
            Storage::disk('public')->delete($player->photo);
        }
        if ($player->signature && !Str::startsWith($player->signature, 'http')) {
            Storage::disk('public')->delete($player->signature);
        }

        $player->delete();

        return response()->json([
            'success' => true,
            'message' => '球友卡已刪除',
        ]);
    }

    public function uploadPhoto(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($player->photo && !Str::startsWith($player->photo, 'http')) {
            Storage::disk('public')->delete($player->photo);
        }

        $path = $request->file('photo')->store('players/photos', 'public');
        $player->update(['photo' => $path]);

        return response()->json([
            'success' => true,
            'message' => '照片上傳成功',
            'data' => [
                'photo' => $player->photo_url,
            ],
        ]);
    }

    private function saveBase64Image($base64, $folder)
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);
        $extension = $matches[1] ?? 'png';
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));

        $filename = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    private function downloadRemoteImage($url, $folder)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get($url);
            if ($response->successful()) {
                $extension = 'jpg';
                $contentType = $response->header('Content-Type');
                if (str_contains($contentType, 'png')) $extension = 'png';
                elseif (str_contains($contentType, 'webp')) $extension = 'webp';
                elseif (str_contains($contentType, 'gif')) $extension = 'gif';

                $filename = Str::uuid() . '.' . $extension;
                $path = $folder . '/' . $filename;

                Storage::disk('public')->put($path, $response->body());
                return $path;
            }
        } catch (\Exception $e) {
            Log::error('Failed to download remote image: ' . $e->getMessage());
        }
        return $url;
    }

    private function resolveUser(Request $request)
    {
        $user = $request->user('sanctum') ?: $request->user();
        if (!$user) {
            $user = Auth::guard('sanctum')->user();
        }
        if (!$user) {
            $user = Auth::user();
        }
        return $user;
    }
}
