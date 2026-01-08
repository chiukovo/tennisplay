<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// SEO Metadata based on routes
$seoData = [
    'home' => [
        'title' => 'LoveTennis | 全台最專業的網球約打媒合與球友卡社群',
        'description' => 'LoveTennis 是全台領先的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。',
        'og_image' => '/img/og-home.jpg'
    ],
    'list' => [
        'title' => '球友大廳 | 發現您的最佳網球夥伴 | LoveTennis',
        'description' => '瀏覽全台網球球友，依據地區與 NTRP 等級篩選最適合您的網球戰友。即刻發送約打邀請！',
    ],
    'create' => [
        'title' => '建立球友卡 | 展現您的網球風格 | LoveTennis',
        'description' => '30秒快速建立您的專屬數位球友卡。上傳專業照、設定等級，開啟您的網球社交第一步。',
    ],
    'messages' => [
        'title' => '我的訊息 | 網球約打邀請管理 | LoveTennis',
        'description' => '管理您的約打邀請與球友訊息，安全聯繫潛在夥伴。',
    ],
    'events' => [
        'title' => '揪球開團 | 搜尋全台網球場次 | LoveTennis',
        'description' => '即時搜尋附近的網球團體與練習場次。不管是新手練習還是專業切磋，這裡都有適合您的球局。',
    ],
    'auth' => [
        'title' => '登入/註冊 | 加入 LoveTennis 社群',
        'description' => '使用 LINE 或電子郵件快速加入 LoveTennis，開啟專業網球社交體驗。',
    ],
    'mycards' => [
        'title' => '我的球友卡 | 管理個人檔案 | LoveTennis',
        'description' => '編輯與管理您的網球特色與約打設定，保持個人檔案最優化。',
    ],
    'settings' => [
        'title' => '帳號設置 | 個性化您的網球體驗 | LoveTennis',
        'description' => '調整預設地區與帳號偏好，享受最直覺的網球媒合服務。',
    ]
];

Route::get('/', function () use ($seoData) {
    $initialPlayers = \App\Models\Player::active()->latest()->take(8)->get();
    $initialEvents = \App\Models\Event::where('event_date', '>=', now()->format('Y-m-d H:i:s'))->latest()->take(5)->get();
    return view('index', [
        'seo' => $seoData['home'],
        'initialPlayers' => $initialPlayers,
        'initialEvents' => $initialEvents
    ]);
});

Route::get('/list', function () use ($seoData) {
    $initialPlayers = \App\Models\Player::active()->latest()->take(20)->get();
    return view('index', [
        'seo' => $seoData['list'],
        'initialPlayers' => $initialPlayers
    ]);
});

Route::get('/create', function () use ($seoData) {
    return view('index', ['seo' => $seoData['create']]);
});

Route::get('/messages', function () use ($seoData) {
    return view('index', ['seo' => $seoData['messages']]);
});

Route::get('/auth', function () use ($seoData) {
    return view('index', ['seo' => $seoData['auth']]);
});

Route::get('/mycards', function () use ($seoData) {
    return view('index', ['seo' => $seoData['mycards']]);
});

Route::get('/events', function () use ($seoData) {
    $initialEvents = \App\Models\Event::where('event_date', '>=', now()->format('Y-m-d H:i:s'))->latest()->take(20)->get();
    return view('index', [
        'seo' => $seoData['events'],
        'initialEvents' => $initialEvents
    ]);
});

Route::get('/create-event', function () use ($seoData) {
    return view('index', ['seo' => $seoData['events']]);
});

Route::get('/settings', function () use ($seoData) {
    return view('index', ['seo' => $seoData['settings']]);
});

// LINE Login Routes
Route::get('/auth/line', [AuthController::class, 'lineLogin'])->name('line.login');
Route::get('/auth/line/callback', [AuthController::class, 'lineCallback'])->name('line.callback');

