<?php

namespace App\Console\Commands;

use App\Jobs\SendEventNotification;
use Illuminate\Console\Command;

class ResendEventNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:notify-resend {eventId : 活動 ID} {--type=created : created|updated|cancelled} {--sync : 同步執行（不走佇列）}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重新發送活動通知（依原本規則推播）';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = (int) $this->argument('eventId');
        $type = (string) $this->option('type');

        if (!in_array($type, ['created', 'updated', 'cancelled'], true)) {
            $this->error('type 只允許 created / updated / cancelled');
            return 1;
        }

        if ($this->option('sync')) {
            // 仍走服務層，但不指定收件者，強制略過重複限制
            app('App\\Services\\EventNotificationService')->notifyById($eventId, $type, null, true);
            $this->info('活動通知已重新送出');
            return 0;
        }

        // 走佇列並強制略過重複限制
        SendEventNotification::dispatch($eventId, $type, null, true);
        $this->info('活動通知已加入佇列重新送出');
        return 0;
    }
}
