<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

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
        'uid',
        'gender',
        'region',
        'bio',
        'line_user_id',
        'line_picture_url',
        'settings',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uid)) {
                // Generate unique UID
                do {
                    $uid = 'u' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
                } while (static::where('uid', $uid)->exists());
                $user->uid = $uid;
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'line_user_id',
    ];

    /**
     * Get the primary player card.
     */
    public function player()
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Get the users following this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    /**
     * Get the users this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
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
     * Get events organized by this user.
     */
    public function organizedEvents()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get event participations.
     */
    public function eventParticipations()
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCountAttribute()
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }

    /**
     * Get LINE picture URL attribute.
     */
    public function getLinePictureUrlAttribute($value)
    {
        if ($value && Str::startsWith($value, '/storage/')) {
            return asset(ltrim($value, '/'));
        }
        return $value;
    }
}

