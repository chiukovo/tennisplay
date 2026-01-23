<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$seo = [
    'title' => 'LoveTennis | 專業網球約打媒合與球友卡社群',
    'description' => 'LoveTennis 是全台最專業的網球約打平台，提供職業級球友卡製作、透明約打費用與安全站內信媒合系統。',
    'canonical' => 'https://lovetennis.tw',
    'og_image' => '/img/og-default.jpg',
];

$html = app('view')->make('index', ['seo' => $seo])->render();

$target = __DIR__ . '/../public/index.html';
file_put_contents($target, $html);

fwrite(STDOUT, "Exported SPA to {$target}\n");
