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
        'description' => 'LoveTennis 是全台領先的網球約打、網球約球平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統，一起打網球更安心。',
        'og_image' => '/img/og-default.jpg'
    ],
    'list' => [
        'title' => '找球友 | 發現您的最佳網球夥伴 | LoveTennis',
        'description' => '瀏覽全台網球球友，依據地區與 NTRP 等級篩選最適合您的網球戰友。網球約打、網球約球一鍵發送邀請！',
    ],
    'create' => [
        'title' => '建立球友卡 | 展現您的網球風格 | LoveTennis',
        'description' => '30秒快速建立您的專屬數位球友卡。上傳專業照、設定等級，讓網球約打、網球約球更順利。',
    ],
    'messages' => [
        'title' => '我的訊息 | 網球約打邀請管理 | LoveTennis',
        'description' => '管理您的網球約打、網球約球邀請與球友訊息，安全聯繫潛在夥伴，一起打網球更有效率。',
    ],
    'events' => [
        'title' => '揪球開團 | 搜尋全台網球場次 | LoveTennis',
        'description' => '即時搜尋附近的網球團體與練習場次，網球約打、網球約球、一起打網球都能快速找到合適場次。',
    ],
    'auth' => [
        'title' => '登入/註冊 | 加入 LoveTennis 社群',
        'description' => '使用 LINE 或電子郵件快速加入 LoveTennis，開啟專業網球社交體驗，網球約打更簡單。',
    ],
    'mycards' => [
        'title' => '我的球友卡 | 管理個人檔案 | LoveTennis',
        'description' => '編輯與管理您的網球特色與約打設定，讓網球約球、一起打網球更精準。',
    ],
    'settings' => [
        'title' => '帳號設置 | 個性化您的網球體驗 | LoveTennis',
        'description' => '調整預設地區與帳號偏好，享受最直覺的網球媒合服務，網球約打更貼近需求。',
    ],
    'privacy' => [
        'title' => '隱私權政策 | LoveTennis',
        'description' => '了解 LoveTennis 如何保護您的個人資料與使用規範，安心網球約打。',
    ],
    'instant-play' => [
        'title' => '現在想打 | 即時揪球聊天室 | LoveTennis',
        'description' => '即時發布揪球訊息，找到現在就能上場的網球球友。分區聊天室，媒合更快速！',
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

Route::get('/events/{id}', function ($id) use ($seoData) {
    $event = \App\Models\Event::with('player')->find($id);
    $seo = $seoData['events'];

    if ($event) {
        $seo = [
            'title' => $event->title . ' | LoveTennis',
            'description' => $event->notes ?: '查看此網球活動詳情與報名資訊',
            'og_image' => $event->player && $event->player->photo ? $event->player->photo_url : ($seoData['events']['og_image'] ?? '/img/og-default.jpg'),
            'type' => 'event',
            'start_date' => optional($event->event_date)->toAtomString(),
            'end_date' => optional($event->end_date)->toAtomString(),
            'location' => $event->location,
            'address' => $event->address,
            'canonical' => url('/events/' . $event->id)
        ];
    }

    return view('index', [
        'seo' => $seo,
        'initialEvents' => isset($event) && $event ? [$event] : []
    ]);
});

Route::get('/create-event', function () use ($seoData) {
    return view('index', ['seo' => $seoData['events']]);
});


Route::get('/settings', function () use ($seoData) {
    return view('index', ['seo' => $seoData['settings']]);
});

Route::get('/privacy', function () use ($seoData) {
    return view('index', ['seo' => $seoData['privacy']]);
});

Route::get('/instant-play', function () use ($seoData) {
    return view('index', ['seo' => $seoData['instant-play']]);
});

Route::get('/profile/{uid}', function ($uid) use ($seoData) {
    return view('index', [
        'seo' => [
            'title' => '個人主頁 | LoveTennis',
            'description' => '查看球友的個人資料與球友卡'
        ]
    ]);
});

// Internal card render route (for Browsershot capture)
Route::get('/internal/card-render/{id}', function ($id) {
    $player = \App\Models\Player::with('user')->findOrFail($id);
    return view('pages.card-capture', ['player' => $player]);
})->name('card.render');

// SEO helpers

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml');
    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/sitemap.xml', function () {
    $staticUrls = [
        url('/'),
        url('/list'),
        url('/events'),
        url('/create'),
        url('/messages'),
        url('/auth'),
        url('/mycards'),
        url('/create-event'),
        url('/settings'),
        url('/privacy'),
        url('/instant-play'),
    ];

    $eventUrls = \App\Models\Event::select('id', 'updated_at')
        ->orderBy('updated_at', 'desc')
        ->take(100)
        ->get()
        ->map(function ($event) {
            return [
                'loc' => url('/events/' . $event->id),
                'lastmod' => optional($event->updated_at)->toAtomString(),
            ];
        });

    $playerUrls = \App\Models\Player::active()
        ->with('user:id,uid')
        ->select('id', 'user_id', 'updated_at')
        ->orderBy('updated_at', 'desc')
        ->take(100)
        ->get()
        ->map(function ($player) {
            return [
                'loc' => url('/profile/' . $player->user->uid),
                'lastmod' => optional($player->updated_at)->toAtomString(),
            ];
        });

    $xml = view('sitemap', [
        'staticUrls' => $staticUrls,
        'eventUrls' => $eventUrls,
        'playerUrls' => $playerUrls,
    ])->render();

    return response($xml, 200)->header('Content-Type', 'application/xml');
});

// LINE Login Routes
Route::get('/auth/line', [AuthController::class, 'lineLogin'])->name('line.login');
Route::get('/auth/line/callback', [AuthController::class, 'lineCallback'])->name('line.callback');

// Dev Login Routes (local environment only)
if (app()->environment('local')) {
    Route::get('/dev-login', [\App\Http\Controllers\Api\DevAuthController::class, 'showLoginForm'])->name('dev.login');
    Route::post('/dev-login', [\App\Http\Controllers\Api\DevAuthController::class, 'devLogin'])->name('dev.login.submit');
}
