<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'photo',
        'region',
        'level',
        'gender',
        'handed',
        'backhand',
        'intro',
        'fee',
        'signature',
        'theme',
        'photo_x',
        'photo_y',
        'photo_scale',
        'sig_x',
        'sig_y',
        'sig_scale',
        'sig_rotate',
        'is_active',
        'is_verified',
    ];

    protected $casts = [
        'photo_x' => 'float',
        'photo_y' => 'float',
        'photo_scale' => 'float',
        'sig_x' => 'float',
        'sig_y' => 'float',
        'sig_scale' => 'float',
        'sig_rotate' => 'float',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected $appends = ['photo_url', 'signature_url'];

    /**
     * Get the user that owns the player card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages sent to this player.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'to_player_id');
    }

    /**
     * Scope for active players only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by region.
     */
    public function scopeInRegion($query, $region)
    {
        if ($region && $region !== '全部') {
            return $query->where('region', $region);
        }
        return $query;
    }

    /**
     * Scope for filtering by level.
     */
    public function scopeAtLevel($query, $level)
    {
        if ($level) {
            return $query->where('level', $level);
        }
        return $query;
    }

    /**
     * Scope for search.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('intro', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Get photo URL attribute.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo && !str_starts_with($this->photo, 'http') && !str_starts_with($this->photo, 'data:')) {
            return asset('storage/' . $this->photo);
        }
        return $this->photo;
    }

    /**
     * Get signature URL attribute.
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature && !str_starts_with($this->signature, 'http') && !str_starts_with($this->signature, 'data:')) {
            return asset('storage/' . $this->signature);
        }
        return $this->signature;
    }
}
