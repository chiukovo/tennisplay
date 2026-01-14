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
                ->omitBackground()
                ->waitUntilNetworkIdle()
                ->delay(500)
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
        // Get npm path from config or use default
        $npmPath = config('browsershot.node_modules_path', base_path('node_modules'));
        $browsershot->setNodeModulePath($npmPath);
        
        // Use explicit config values if set (highest priority)
        if ($nodeBinary = config('browsershot.node_binary')) {
            $browsershot->setNodeBinary($nodeBinary);
        }
        
        if ($npmBinary = config('browsershot.npm_binary')) {
            $browsershot->setNpmBinary($npmBinary);
        }
        
        if ($chromeBinary = config('browsershot.chrome_binary')) {
            $browsershot->setChromePath($chromeBinary);
        }
        
        // If config values are set, we're done
        if (config('browsershot.node_binary') && config('browsershot.chrome_binary')) {
            $browsershot->setIncludePath(false);
            return;
        }
        
        // Auto-detect paths based on OS
        if (PHP_OS_FAMILY === 'Windows') {
            $this->configureWindowsPaths($browsershot, $npmPath);
            return;
        }
        
        // Linux/Mac configuration
        $this->configureLinuxPaths($browsershot, $npmPath);
    }

    
    /**
     * Configure paths for Windows systems.
     */
    private function configureWindowsPaths(Browsershot $browsershot, string $npmPath): void
    {
        $possibleChromePaths = [
            $npmPath . '\\puppeteer\\.local-chromium\\win64-*\\chrome-win\\chrome.exe',
            $npmPath . '\\puppeteer\\chrome-headless-shell\\win64\\chrome-headless-shell.exe',
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            getenv('LOCALAPPDATA') . '\\Google\\Chrome\\Application\\chrome.exe',
        ];
        
        // Find Puppeteer's cache directory (new location)
        $puppeteerCacheDir = getenv('LOCALAPPDATA') . '\\puppeteer\\chrome';
        if (is_dir($puppeteerCacheDir)) {
            $dirs = glob($puppeteerCacheDir . '\\win64-*', GLOB_ONLYDIR);
            if (!empty($dirs)) {
                rsort($dirs);
                $chromeExe = $dirs[0] . '\\chrome-win64\\chrome.exe';
                if (file_exists($chromeExe)) {
                    $browsershot->setChromePath($chromeExe);
                    return;
                }
            }
        }
        
        // Fallback: check possible paths
        foreach ($possibleChromePaths as $chromePath) {
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
    
    /**
     * Configure paths for Linux/Mac systems.
     */
    private function configureLinuxPaths(Browsershot $browsershot, string $npmPath): void
    {
        // Common Node.js binary locations on Linux/CentOS
        $possibleNodePaths = [
            '/usr/bin/node',
            '/usr/local/bin/node',
            '/opt/nodejs/bin/node',
            // nvm paths (common user installations)
            getenv('HOME') . '/.nvm/versions/node/*/bin/node',
            '/root/.nvm/versions/node/*/bin/node',
            // n (node version manager)
            '/usr/local/n/versions/node/*/bin/node',
        ];
        
        $possibleNpmPaths = [
            '/usr/bin/npm',
            '/usr/local/bin/npm',
            '/opt/nodejs/bin/npm',
            getenv('HOME') . '/.nvm/versions/node/*/bin/npm',
            '/root/.nvm/versions/node/*/bin/npm',
        ];
        
        // Find and set Node.js binary
        $nodePath = $this->findExecutable($possibleNodePaths);
        if ($nodePath) {
            $browsershot->setNodeBinary($nodePath);
        }
        
        // Find and set NPM binary
        $npmBinaryPath = $this->findExecutable($possibleNpmPaths);
        if ($npmBinaryPath) {
            $browsershot->setNpmBinary($npmBinaryPath);
        }
        
        // Common Chrome/Chromium paths on Linux
        // Priority: Direct binary paths first (not wrapper scripts)
        $possibleChromePaths = [
            // Puppeteer's downloaded Chrome (highest priority)
            getenv('HOME') . '/.cache/puppeteer/chrome/linux-*/chrome-linux64/chrome',
            '/root/.cache/puppeteer/chrome/linux-*/chrome-linux64/chrome',
            $npmPath . '/puppeteer/.local-chromium/linux-*/chrome-linux/chrome',
            // Direct Chromium binary paths (not wrapper scripts)
            '/usr/lib64/chromium-browser/chromium-browser',
            '/usr/lib/chromium-browser/chromium-browser',
            '/usr/lib/chromium/chromium',
            '/opt/chromium/chrome',
            // Google Chrome direct paths
            '/opt/google/chrome/chrome',
            '/opt/google/chrome/google-chrome',
            // System wrapper scripts (last resort - requires PATH)
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/snap/bin/chromium',
        ];
        
        $chromePath = $this->findExecutable($possibleChromePaths);
        if ($chromePath) {
            $browsershot->setChromePath($chromePath);
        }
        
        // Note: We do NOT set setIncludePath(false) because some Chromium installations
        // use wrapper scripts that require system PATH to work properly
    }

    
    /**
     * Find an executable from a list of possible paths (supports glob patterns).
     */
    private function findExecutable(array $paths): ?string
    {
        foreach ($paths as $path) {
            if (strpos($path, '*') !== false) {
                $matches = glob($path);
                if (!empty($matches)) {
                    rsort($matches); // Prefer newer versions
                    foreach ($matches as $match) {
                        if (is_executable($match)) {
                            return $match;
                        }
                    }
                }
            } elseif (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        return null;
    }
}

