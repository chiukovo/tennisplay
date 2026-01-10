<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'player_id',
        'region',
        'title',
        'event_date',
        'end_date',
        'location',
        'address',
        'fee',
        'max_participants',
        'match_type',
        'gender',
        'level_min',
        'level_max',
        'notes',
        'status',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'end_date' => 'datetime',
        'fee' => 'integer',
        'max_participants' => 'integer',
    ];

    protected $appends = ['participants_count', 'is_full', 'spots_left'];

    /**
     * Get the user (organizer) that created the event.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the player card of the organizer.
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the participants of this event.
     */
    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    /**
     * Get confirmed participants only.
     */
    public function confirmedParticipants()
    {
        return $this->hasMany(EventParticipant::class)->where('status', 'confirmed');
    }

    /**
     * Get the number of confirmed participants.
     */
    public function getParticipantsCountAttribute()
    {
        return $this->confirmedParticipants()->count();
    }

    /**
     * Check if the event is full (0 means unlimited).
     */
    public function getIsFullAttribute()
    {
        if ($this->max_participants == 0) return false; // Unlimited
        return $this->participants_count >= $this->max_participants;
    }

    /**
     * Get remaining spots (0 means unlimited).
     */
    public function getSpotsLeftAttribute()
    {
        if ($this->max_participants == 0) {
            return null; // Unlimited
        }
        return max(0, $this->max_participants - $this->participants_count);
    }


    /**
     * Scope for open events.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }

    /**
     * Get the comments for this event.
     */
    public function comments()
    {
        return $this->hasMany(EventComment::class);
    }

    /**
     * Check if a user has joined this event.
     */
    public function hasParticipant($userId)
    {
        return $this->confirmedParticipants()->where('user_id', $userId)->exists();
    }

    /**
     * Scope for filtering by region.
     */
    public function scopeInRegion($query, $region)
    {
        if ($region && $region !== '全部') {
            return $query->where(function($q) use ($region) {
                $q->where('region', $region)
                  ->orWhere('location', 'like', "%{$region}%")
                  ->orWhere('address', 'like', "%{$region}%");
            });
        }
        return $query;
    }

    /**
     * Get match type label in Chinese.
     */
    public function getMatchTypeLabelAttribute()
    {
        switch ($this->match_type) {
            case 'all': return '不限';
            case 'singles': return '單打';
            case 'doubles': return '雙打';
            case 'mixed': return '混雙';
            default: return '不限';
        }
    }

    /**
     * Get gender label in Chinese.
     */
    public function getGenderLabelAttribute()
    {
        switch ($this->gender) {
            case 'all': return '不限';
            case 'male': return '限男性';
            case 'female': return '限女性';
            default: return '不限';
        }
    }

    /**
     * Get status label in Chinese.
     */
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'open': return '招募中';
            case 'full': return '已額滿';
            case 'closed': return '已截止';
            case 'completed': return '已結束';
            case 'cancelled': return '已取消';
            default: return '招募中';
        }
    }
}
