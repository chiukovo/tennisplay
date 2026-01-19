<?php

namespace App\Jobs;

use App\Models\LineNotificationLog;
use App\Services\LineNotifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendLineNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大重試次數
     */
    public $tries = 3;

    /**
     * 重試間隔 (秒)
     */
    public $backoff = [30, 60, 120];

    /**
     * 任務超時時間 (秒)
     */
    public $timeout = 30;

    /**
     * 唯一任務 ID (用於防重複)
     */
    public $uniqueFor = 300; // 5 分鐘內相同任務視為重複

    protected int $userId;
    protected string $lineUserId;
    protected string $type;
    protected string $altText;
    /** @var array|string */
    protected $content;
    protected string $uniqueKey;

    /**
     * Create a new job instance.
     *
     * @param int $userId 接收者的 user_id
     * @param string $lineUserId 接收者的 LINE User ID
     * @param string $type 訊息類型: 'text' 或 'flex'
     * @param string $altText 替代文字 (用於通知預覽)
     * @param array|string $content 訊息內容 (text 為字串, flex 為陣列)
     */
    public function __construct(int $userId, string $lineUserId, string $type, string $altText, $content)
    {
        $this->userId = $userId;
        $this->lineUserId = $lineUserId;
        $this->type = $type;
        $this->altText = $altText;
        $this->content = $content;
        // 產生唯一 key 用於防重複發送
        $contentHash = md5(is_array($content) ? json_encode($content) : $content);
        $this->uniqueKey = "line_job_{$userId}_{$lineUserId}_{$contentHash}";
    }

    /**
     * Laravel 唯一任務識別 (防止重複派發)
     */
    public function uniqueId(): string
    {
        return $this->uniqueKey;
    }

    /**
     * Execute the job.
     */
    public function handle(LineNotifyService $lineService): void
    {
        // 防重複發送：檢查此通知是否已成功發送過
        $sentKey = $this->uniqueKey . '_sent';
        if (Cache::has($sentKey)) {
            Log::info('LINE notification already sent, skipping', [
                'user_id' => $this->userId,
                'unique_key' => $this->uniqueKey,
            ]);
            return;
        }

        // 第一次執行時建立日誌，重試時更新現有日誌
        $logKey = $this->uniqueKey . '_log_id';
        $logId = Cache::get($logKey);

        if ($logId) {
            $log = LineNotificationLog::find($logId);
        }

        if (!isset($log) || !$log) {
            $log = LineNotificationLog::create([
                'user_id' => $this->userId,
                'line_user_id' => $this->lineUserId,
                'type' => $this->type,
                'alt_text' => $this->altText,
                'content' => is_array($this->content) ? json_encode($this->content, JSON_UNESCAPED_UNICODE) : $this->content,
                'status' => 'pending',
                'attempts' => $this->attempts(),
            ]);
            // 快取 log ID 供重試使用 (10 分鐘)
            Cache::put($logKey, $log->id, now()->addMinutes(10));
        }

        try {
            $success = false;

            if ($this->type === 'text') {
                $success = $lineService->sendTextMessage($this->lineUserId, $this->content);
            } elseif ($this->type === 'flex') {
                $success = $lineService->sendFlexMessage($this->lineUserId, $this->altText, $this->content);
            }

            if ($success) {
                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'attempts' => $this->attempts(),
                ]);

                // 標記為已發送，防止重複 (5 分鐘內)
                Cache::put($sentKey, true, now()->addMinutes(5));
                Cache::forget($logKey);

                Log::info('LINE notification sent successfully', [
                    'user_id' => $this->userId,
                    'type' => $this->type,
                ]);
            } else {
                throw new \Exception('LINE API returned failure');
            }
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);

            Log::error('LINE notification failed', [
                'user_id' => $this->userId,
                'type' => $this->type,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // 重新拋出例外以觸發重試
            throw $e;
        }
    }

    /**
     * 任務最終失敗時的處理
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('LINE notification permanently failed', [
            'user_id' => $this->userId,
            'line_user_id' => $this->lineUserId,
            'type' => $this->type,
            'error' => $exception->getMessage(),
        ]);

        // 更新日誌為永久失敗
        $logKey = $this->uniqueKey . '_log_id';
        $logId = Cache::get($logKey);

        if ($logId) {
            $log = LineNotificationLog::find($logId);
            if ($log) {
                $log->update([
                    'status' => 'permanently_failed',
                    'error_message' => $exception->getMessage(),
                ]);
            }
            Cache::forget($logKey);
        }
    }
}
