<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\LineNotifyService;
use Illuminate\Console\Command;

class SendBroadcastNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:broadcast 
                            {--user= : 指定用戶 ID（測試用，不指定則發送給所有用戶）}
                            {--title= : 標題}
                            {--message= : 訊息內容}
                            {--url= : 按鈕連結（選填）}
                            {--button= : 按鈕文字（選填，預設「查看詳情」）}
                            {--dry-run : 只顯示會發送的用戶，不實際發送}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '向所有用戶或指定用戶發送 LINE 廣播通知（用於發佈更新資訊）';

    /**
     * Execute the console command.
     */
    public function handle(LineNotifyService $service)
    {
        $userId = $this->option('user');
        $title = $this->option('title');
        $message = $this->option('message');
        $url = $this->option('url') ?? config('app.url');
        $buttonText = $this->option('button') ?? '查看詳情';
        $isDryRun = $this->option('dry-run');

        // 互動式輸入
        if (empty($title)) {
            $title = $this->ask('請輸入標題', '🎾 LoveTennis 系統更新');
        }
        if (empty($message)) {
            $message = $this->ask('請輸入訊息內容');
        }

        if (empty($message)) {
            $this->error('訊息內容不可為空');
            return 1;
        }

        // 取得目標用戶
        $query = User::whereNotNull('line_user_id')->where('line_user_id', '!=', '');
        
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('沒有找到符合條件的用戶');
            return 1;
        }

        $this->info("準備發送通知給 {$users->count()} 位用戶");
        $this->newLine();
        
        // 預覽訊息
        $this->info('═══════════════════════════════════════');
        $this->info('📢 訊息預覽');
        $this->info('═══════════════════════════════════════');
        $this->line("標題：{$title}");
        $this->line("內容：{$message}");
        $this->line("連結：{$url}");
        $this->line("按鈕：{$buttonText}");
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if ($isDryRun) {
            $this->info('[Dry Run] 以下用戶將收到通知：');
            foreach ($users as $user) {
                $this->line(" - ID:{$user->id} | {$user->name} | LINE:{$user->line_user_id}");
            }
            return 0;
        }

        if (!$this->confirm('確定發送？', false)) {
            $this->info('已取消');
            return 0;
        }

        // 建立 Flex Message
        $flexContents = $this->buildFlexMessage($title, $message, $url, $buttonText);
        $altText = "📢 {$title}";

        $successCount = 0;
        $failCount = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $result = $service->sendFlexMessage($user->line_user_id, $altText, $flexContents);
            
            if ($result) {
                $successCount++;
            } else {
                $failCount++;
                $this->newLine();
                $this->warn("發送失敗：{$user->name} ({$user->id})");
            }
            
            $bar->advance();
            
            // 避免過快發送被 LINE 限流
            usleep(100000); // 0.1 秒
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("發送完成！成功：{$successCount}，失敗：{$failCount}");
        
        return $failCount > 0 ? 1 : 0;
    }

    /**
     * 建立 Flex Message 結構
     */
    protected function buildFlexMessage(string $title, string $message, string $url, string $buttonText): array
    {
        return [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            [
                                'type' => 'image',
                                'url' => config('app.url') . '/img/logo.png',
                                'size' => 'xxs',
                                'aspectMode' => 'cover',
                                'aspectRatio' => '1:1',
                                'flex' => 0,
                            ],
                            [
                                'type' => 'text',
                                'text' => 'LoveTennis',
                                'weight' => 'bold',
                                'size' => 'sm',
                                'color' => '#1e40af',
                                'margin' => 'sm',
                                'flex' => 1,
                                'gravity' => 'center',
                            ],
                        ],
                        'alignItems' => 'center',
                    ],
                ],
                'paddingAll' => 'lg',
                'backgroundColor' => '#f8fafc',
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $title,
                        'weight' => 'bold',
                        'size' => 'lg',
                        'wrap' => true,
                        'color' => '#0f172a',
                    ],
                    [
                        'type' => 'separator',
                        'margin' => 'lg',
                    ],
                    [
                        'type' => 'text',
                        'text' => $message,
                        'size' => 'sm',
                        'color' => '#475569',
                        'wrap' => true,
                        'margin' => 'lg',
                    ],
                ],
                'paddingAll' => 'xl',
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => $buttonText,
                            'uri' => $url,
                        ],
                        'style' => 'primary',
                        'color' => '#2563eb',
                        'height' => 'sm',
                    ],
                ],
                'paddingAll' => 'lg',
                'backgroundColor' => '#f1f5f9',
            ],
            'styles' => [
                'header' => [
                    'separator' => false,
                ],
            ],
        ];
    }
}
