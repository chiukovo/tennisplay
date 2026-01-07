<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * LINE Login only - no email/password needed.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'line_user_id',
        'line_picture_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'line_user_id',
    ];

    /**
     * Get the player cards owned by the user.
     */
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    /**
     * Get the messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user_id');
    }

    /**
     * Get the messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user_id');
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCountAttribute()
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }
}

