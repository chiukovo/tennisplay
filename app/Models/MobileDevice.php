<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'model',
        'os_version',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
