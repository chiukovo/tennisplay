<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'player_id',
        'user_id',
        'content',
    ];

    /**
     * Get the player card this comment belongs to.
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
