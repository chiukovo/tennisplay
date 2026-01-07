<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlayerController extends Controller
{
    /**
     * Display a listing of players.
     */
    public function index(Request $request)
    {
        $query = Player::active()
            ->inRegion($request->region)
            ->atLevel($request->level)
            ->search($request->search);

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        $players = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    /**
     * Display a listing of players belonging to the authenticated user.
     */
    public function myCards(Request $request)
    {
        $players = Player::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $players,
        ]);
    }

    /**
     * Store a newly created player.
     */
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

        // Handle base64 photo
        if ($request->photo && Str::startsWith($request->photo, 'data:image')) {
            $data['photo'] = $this->saveBase64Image($request->photo, 'players/photos');
        } elseif ($request->photo) {
            $data['photo'] = $request->photo;
        }

        // Handle base64 signature
        if ($request->signature && Str::startsWith($request->signature, 'data:image')) {
            $data['signature'] = $this->saveBase64Image($request->signature, 'players/signatures');
        }

        // Associate with logged in user if available
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $player = Player::create($data);

        // Merging disabled as per user request to keep signatures dynamic
        /*
        if ($player->photo && $player->signature) {
            $mergedPath = $this->mergeSignature($player);
            if ($mergedPath) {
                $player->update([
                    'photo' => $mergedPath,
                ]);
            }
        }
        */

        return response()->json([
            'success' => true,
            'message' => '球友卡建立成功！',
            'data' => $player->fresh(),
        ], 201);
    }

    /**
     * Display the specified player.
     */
    public function show($id)
    {
        $player = Player::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $player,
        ]);
    }

    /**
     * Update the specified player.
     */
    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        // Check ownership
        if ($request->user() && $player->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限編輯此球友卡',
            ], 403);
        }

        $data = $request->only([
            'name', 'region', 'level', 'gender', 'handed', 'backhand',
            'intro', 'fee', 'theme', 'photo_x', 'photo_y', 'photo_scale',
            'sig_x', 'sig_y', 'sig_scale', 'sig_rotate', 'sig_width', 'sig_height', 'is_active'
        ]);

        // Handle base64 photo
        if ($request->photo && Str::startsWith($request->photo, 'data:image')) {
            // Delete old photo
            if ($player->photo && !Str::startsWith($player->photo, 'http')) {
                Storage::disk('public')->delete($player->photo);
            }
            $data['photo'] = $this->saveBase64Image($request->photo, 'players/photos');
        }

        // Handle base64 signature
        if ($request->signature && Str::startsWith($request->signature, 'data:image')) {
            if ($player->signature && !Str::startsWith($player->signature, 'http')) {
                Storage::disk('public')->delete($player->signature);
            }
            $data['signature'] = $this->saveBase64Image($request->signature, 'players/signatures');
        }

        $player->update($data);

        // Merging disabled as per user request to keep signatures dynamic
        /*
        if ($player->photo && $player->signature) {
            $mergedPath = $this->mergeSignature($player);
            if ($mergedPath) {
                $player->update([
                    'photo' => $mergedPath,
                    'signature' => null
                ]);
            }
        }
        */

        return response()->json([
            'success' => true,
            'message' => '球友卡已更新',
            'data' => $player->fresh(),
        ]);
    }

    /**
     * Merge signature into photo using GD.
     */
    private function mergeSignature($player)
    {
        try {
            $photoPath = storage_path('app/public/' . $player->photo);
            $sigPath = storage_path('app/public/' . $player->signature);

            if (!file_exists($photoPath) || !file_exists($sigPath)) return null;

            $photo = imagecreatefromstring(file_get_contents($photoPath));
            $sig = imagecreatefromstring(file_get_contents($sigPath));

            if (!$photo || !$sig) return null;

            // Get dimensions
            $pw = imagesx($photo);
            $ph = imagesy($photo);
            $sw = imagesx($sig);
            $sh = imagesy($sig);

            // Enable alpha blending
            imagealphablending($photo, true);
            imagesavealpha($photo, true);

            // Standard Card Aspect Ratio (2.5 / 3.8)
            $cardAspect = 2.5 / 3.8;
            
            // Calculate Virtual Card Dimensions based on Photo Width
            // Since photo is 'w-full' on the card, CardWidth = PhotoWidth
            $vcw = $pw;
            $vch = $vcw / $cardAspect;

            // Signature positioning (percentages of the card)
            $sigXPercent = ($player->sig_x ?? 50) / 100;
            $sigYPercent = ($player->sig_y ?? 50) / 100;

            // Map card percentages to photo pixels
            $targetX = $vcw * $sigXPercent;
            $targetY = $vch * $sigYPercent;

            // Scale signature
            // The signature image from frontend is card-sized (2.5:3.8)
            // We scale it to match the virtual card size, then apply user's scale
            $scale = $player->sig_scale ?? 1;
            $targetSW = $vcw * $scale;
            $targetSH = $vch * $scale;

            // Resize signature
            $resizedSig = imagecreatetruecolor($targetSW, $targetSH);
            imagealphablending($resizedSig, false);
            imagesavealpha($resizedSig, true);
            $transparent = imagecolorallocatealpha($resizedSig, 0, 0, 0, 127);
            imagefill($resizedSig, 0, 0, $transparent);
            imagecopyresampled($resizedSig, $sig, 0, 0, 0, 0, $targetSW, $targetSH, $sw, $sh);

            // Rotate signature (GD rotates counter-clockwise)
            $rotate = -($player->sig_rotate ?? 0);
            $rotatedSig = imagerotate($resizedSig, $rotate, imagecolorallocatealpha($resizedSig, 0, 0, 0, 127));
            
            $rsw = imagesx($rotatedSig);
            $rsh = imagesy($rotatedSig);

            // Draw onto photo
            // Center the rotated signature at (targetX, targetY)
            imagecopy($photo, $rotatedSig, $targetX - ($rsw / 2), $targetY - ($rsh / 2), 0, 0, $rsw, $rsh);

            // Save merged image
            // We save it as a new file to avoid overwriting the original photo if possible
            $filename = 'players/merged/' . Str::uuid() . '.png';
            $savePath = storage_path('app/public/' . $filename);
            
            if (!is_dir(dirname($savePath))) {
                mkdir(dirname($savePath), 0755, true);
            }

            imagepng($photo, $savePath);

            // Cleanup
            imagedestroy($photo);
            imagedestroy($sig);
            imagedestroy($resizedSig);
            imagedestroy($rotatedSig);

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Signature merge failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Remove the specified player.
     */
    public function destroy(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        // Check ownership
        if ($request->user() && $player->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => '無權限刪除此球友卡',
            ], 403);
        }

        // Delete associated files
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

    /**
     * Upload photo for player.
     */
    public function uploadPhoto(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Delete old photo
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

    /**
     * Save base64 image to storage.
     */
    private function saveBase64Image($base64, $folder)
    {
        // Extract the image data
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);
        $extension = $matches[1] ?? 'png';
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $path = $folder . '/' . $filename;

        // Save to storage
        Storage::disk('public')->put($path, $imageData);

        return $path;
    }
}
