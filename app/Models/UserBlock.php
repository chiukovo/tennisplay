<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    public static function isBlockedBetween($userId, $otherUserId): bool
    {
        return self::where(function ($q) use ($userId, $otherUserId) {
            $q->where('blocker_id', $userId)->where('blocked_id', $otherUserId);
        })->orWhere(function ($q) use ($userId, $otherUserId) {
            $q->where('blocker_id', $otherUserId)->where('blocked_id', $userId);
        })->exists();
    }
}
