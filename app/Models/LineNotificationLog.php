<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineNotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'line_user_id',
        'type',
        'alt_text',
        'content',
        'status',
        'attempts',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * 關聯的用戶
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: 只查詢失敗的通知
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'permanently_failed']);
    }

    /**
     * Scope: 只查詢成功的通知
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: 只查詢待處理的通知
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * 標記為已發送
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * 標記為失敗
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
