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
            ->betweenLevels($request->level_min, $request->level_max)
            ->ofGender($request->gender)
            ->ofHanded($request->handed)
            ->ofBackhand($request->backhand)
            ->search($request->search);

        if ($request->level && !$request->level_min && !$request->level_max) {
            $query->atLevel($request->level);
        }

        $players = $query->orderBy('updated_at', 'desc')
            ->paginate($request->per_page ?? 20);

        Player::hydrateSocialStatus($players, $this->resolveUser($request));

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    public function random(Request $request)
    {
        $players = Player::with('user')
            ->withCount(['likes', 'comments'])
            ->active()
            ->inRandomOrder()
            ->take(10)
            ->get();

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
            'photo' => 'string|nullable',
            'signature' => 'string|nullable',
        ]);

        $maxPhotoBytes = 5 * 1024 * 1024; // 5MB
        if ($request->photo && Str::startsWith($request->photo, 'data:image') && strlen($request->photo) > $maxPhotoBytes) {
            return response()->json([
                'success' => false,
                'message' => '圖片過大，請不要超過 5 MB',
            ], 422);
        }

        if ($request->photo && strlen($request->photo) > 1024 * 1024) {
            Log::info('Large photo payload in store', [
                'user_id' => Auth::id(),
                'size' => strlen($request->photo)
            ]);
        }

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
            
            // Sync back to user
            $user->update([
                'name' => $data['name'],
                'gender' => $data['gender'],
                'region' => $data['region'],
            ]);
        } else {
            $player = Player::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => '球友卡建立成功！',
            'data' => $player->fresh(['user']),
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

        $maxPhotoBytes = 5 * 1024 * 1024; // 5MB
        if ($request->photo && Str::startsWith($request->photo, 'data:image') && strlen($request->photo) > $maxPhotoBytes) {
            return response()->json([
                'success' => false,
                'message' => '圖片過大，請不要超過 5 MB',
            ], 422);
        }

        if ($request->photo && strlen($request->photo) > 1024 * 1024) {
            Log::info('Large photo payload in update', [
                'user_id' => Auth::id(),
                'player_id' => $id,
                'size' => strlen($request->photo)
            ]);
        }

        if ($request->photo && Str::startsWith($request->photo, 'data:image')) {
            if ($player->photo && !Str::startsWith($player->photo, 'http')) {
                Storage::disk('public')->delete($player->photo);
            }
            $data['photo'] = $this->saveBase64Image($request->photo, 'players/photos');
        } elseif ($request->photo && Str::startsWith($request->photo, 'http')) {
            // Only download if it's NOT our own storage URL
            $storageUrl = asset('storage/');
            if (!Str::startsWith($request->photo, $storageUrl)) {
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

        // Sync back to user
        if ($user && $player->user_id == $user->id) {
            $user->update([
                'name' => $player->name,
                'gender' => $player->gender,
                'region' => $player->region,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '球友卡已更新',
            'data' => $player->fresh(['user']),
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

        $user = $this->resolveUser($request);
        if ($user && $player->user_id && $player->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限修改此照片',
            ], 403);
        }

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
        // 1. Validate Base64 Format and Extract MIME
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            throw new \Exception('無效的圖片格式');
        }
        
        $extension = strtolower($matches[1]);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('不支援的圖片類型（僅限 jpg, png, webp）');
        }

        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        
        // 2. Security Check: Validate actual image content
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);
        if (!Str::startsWith($mimeType, 'image/') || str_contains($mimeType, 'gif')) {
            throw new \Exception('非法的文件內容');
        }

        $filename = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    private function downloadRemoteImage($url, $folder)
    {
        try {
            // SSRF Protection: Parse URL and check host
            $host = parse_url($url, PHP_URL_HOST);
            if (!$host || $host === 'localhost' || $host === '127.0.0.1' || $host === '::1') {
                return $url;
            }

            // Block private IP ranges
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $url;
                }
            }

            $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                
                // Only allow specific non-gif image types
                $extension = null;
                if (str_contains($contentType, 'image/jpeg')) $extension = 'jpg';
                elseif (str_contains($contentType, 'image/png')) $extension = 'png';
                elseif (str_contains($contentType, 'image/webp')) $extension = 'webp';
                
                if (!$extension) return $url;

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
