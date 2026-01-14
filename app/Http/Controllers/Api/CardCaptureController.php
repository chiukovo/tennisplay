<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class CardCaptureController extends Controller
{
    /**
     * Generate a high-fidelity player card image using Puppeteer.
     * 
     * @param int $id Player ID
     * @return \Illuminate\Http\Response
     */
    public function capture($id)
    {
        try {
            $player = Player::with('user')->findOrFail($id);
            
            // Generate the internal render URL
            $renderUrl = route('card.render', ['id' => $player->id]);
            
            // Configure Browsershot
            $browsershot = Browsershot::url($renderUrl)
                ->windowSize(450, 684)
                ->deviceScaleFactor(2) // Retina quality (900x1368 output)
                ->waitUntilNetworkIdle()
                ->timeout(30);
            
            // Configure paths for Windows/Linux
            $this->configureBrowserPaths($browsershot);
            
            // Take screenshot
            $screenshot = $browsershot->screenshot();
            
            // Return as base64 JSON response for frontend consumption
            $base64 = base64_encode($screenshot);
            
            return response()->json([
                'success' => true,
                'image' => 'data:image/png;base64,' . $base64,
                'filename' => 'player-card-' . ($player->name ?? 'tennis') . '.png'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Card capture failed: ' . $e->getMessage(), [
                'player_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '圖片生成失敗，請稍後再試',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Configure browser paths for different operating systems.
     */
    private function configureBrowserPaths(Browsershot $browsershot): void
    {
        // Get npm path for Puppeteer
        $npmPath = base_path('node_modules');
        $browsershot->setNodeModulePath($npmPath);
        
        // Windows-specific Chrome path detection
        if (PHP_OS_FAMILY === 'Windows') {
            $possibleChromePaths = [
                // Puppeteer's bundled Chrome (preferred)
                $npmPath . '\\puppeteer\\.local-chromium\\win64-*\\chrome-win\\chrome.exe',
                $npmPath . '\\puppeteer\\chrome-headless-shell\\win64\\chrome-headless-shell.exe',
                // Local Chrome installations
                'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
                'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
                getenv('LOCALAPPDATA') . '\\Google\\Chrome\\Application\\chrome.exe',
            ];
            
            // Find Puppeteer's cache directory (new location)
            $puppeteerCacheDir = getenv('LOCALAPPDATA') . '\\puppeteer\\chrome';
            if (is_dir($puppeteerCacheDir)) {
                $dirs = glob($puppeteerCacheDir . '\\win64-*', GLOB_ONLYDIR);
                if (!empty($dirs)) {
                    rsort($dirs); // Get latest version
                    $chromeExe = $dirs[0] . '\\chrome-win64\\chrome.exe';
                    if (file_exists($chromeExe)) {
                        $browsershot->setChromePath($chromeExe);
                        return;
                    }
                }
            }
            
            // Fallback: check possible paths
            foreach ($possibleChromePaths as $chromePath) {
                // Handle glob patterns
                if (strpos($chromePath, '*') !== false) {
                    $matches = glob($chromePath);
                    if (!empty($matches)) {
                        $browsershot->setChromePath($matches[0]);
                        return;
                    }
                } elseif (file_exists($chromePath)) {
                    $browsershot->setChromePath($chromePath);
                    return;
                }
            }
        }
        
        // For Linux/Mac, Puppeteer should handle this automatically
    }
}
