<?php

namespace App\Console\Commands;

use App\Jobs\SendEventNotification;
use App\Services\EventNotificationService;
use Illuminate\Console\Command;

class SendEventNotificationTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:notify-test {eventId : 活動 ID} {userId : 接收者使用者 ID} {--type=created : created|updated|cancelled} {--sync : 同步執行（不走佇列）} {--force : 強制忽略重複通知限制}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '測試活動通知（指定接收者）';

    /**
     * Execute the console command.
     */
    public function handle(EventNotificationService $service)
    {
        $eventId = (int) $this->argument('eventId');
        $userId = (int) $this->argument('userId');
        $type = (string) $this->option('type');

        if (!in_array($type, ['created', 'updated', 'cancelled'], true)) {
            $this->error('type 只允許 created / updated / cancelled');
            return 1;
        }

        $force = (bool) $this->option('force');

        if ($this->option('sync')) {
            $service->notifyById($eventId, $type, $userId, $force);
            $this->info('測試通知已送出');
            return 0;
        }

        SendEventNotification::dispatch($eventId, $type, $userId, $force);
        $this->info('測試通知已加入佇列');
        return 0;
    }
}
