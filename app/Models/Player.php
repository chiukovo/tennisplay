<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Like;
use App\Models\Follow;

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
        'sig_width',
        'sig_height',
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
        'sig_width' => 'float',
        'sig_height' => 'float',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    protected $appends = ['photo_url', 'signature_url', 'likes_count', 'comments_count', 'user_uid'];

    /**
     * Get user UID.
     */
    public function getUserUidAttribute()
    {
        return $this->user ? $this->user->uid : null;
    }

    /**
     * Get the user that owns the player card.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the likes for this player card.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the comments for this player card.
     */
    public function comments()
    {
        return $this->hasMany(PlayerComment::class);
    }

    /**
     * Get likes count.
     */
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }

    /**
     * Get comments count.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
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
     * Supports comma-separated multi-region format (e.g., "台北市,新北市")
     */
    public function scopeInRegion($query, $region)
    {
        if ($region && !in_array($region, ['全部', 'all'])) {
            // Use FIND_IN_SET for exact match within comma-separated values
            return $query->whereRaw('FIND_IN_SET(?, region) > 0', [$region]);
        }
        return $query;
    }

    /**
     * Scope for filtering by level.
     */
    public function scopeAtLevel($query, $level)
    {
        if ($level && $level !== '全部') {
            return $query->where('level', $level);
        }
        return $query;
    }

    /**
     * Scope for filtering by level range.
     */
    public function scopeBetweenLevels($query, $min, $max)
    {
        if ($min && $min !== '全部') {
            $query->where('level', '>=', $min);
        }
        if ($max && $max !== '全部') {
            $query->where('level', '<=', $max);
        }
        return $query;
    }

    /**
     * Scope for filtering by gender.
     */
    public function scopeOfGender($query, $gender)
    {
        if ($gender && !in_array($gender, ['全部', 'all'])) {
            return $query->where('gender', $gender);
        }
        return $query;
    }

    /**
     * Scope for filtering by handedness.
     */
    public function scopeOfHanded($query, $handed)
    {
        if ($handed && !in_array($handed, ['全部', 'all'])) {
            return $query->where('handed', $handed);
        }
        return $query;
    }

    /**
     * Scope for filtering by backhand type.
     */
    public function scopeOfBackhand($query, $backhand)
    {
        if ($backhand && !in_array($backhand, ['全部', 'all'])) {
            return $query->where('backhand', $backhand);
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
        if ($this->photo && !Str::startsWith($this->photo, 'http') && !Str::startsWith($this->photo, 'data:')) {
            return asset('storage/' . $this->photo);
        }
        return $this->photo;
    }

    /**
     * Get signature URL attribute.
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature && !Str::startsWith($this->signature, 'http') && !Str::startsWith($this->signature, 'data:')) {
            return asset('storage/' . $this->signature);
        }
        return $this->signature;
    }

    /**
     * Hydrate social status (is_liked, is_following) for a specific user.
     */
    public static function hydrateSocialStatus($players, $user)
    {
        if (!$user) {
            if ($players instanceof \Illuminate\Support\Collection || is_array($players)) {
                foreach ($players as $player) {
                    $player->is_liked = false;
                    $player->is_following = false;
                }
            } else {
                $players->is_liked = false;
                $players->is_following = false;
            }
            return $players;
        }

        if ($players instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $playerCollection = $players->getCollection();
        } elseif ($players instanceof \Illuminate\Support\Collection) {
            $playerCollection = $players;
        } elseif (is_array($players)) {
            $playerCollection = collect($players);
        } else {
            $playerCollection = collect([$players]);
        }

        $playerIds = $playerCollection->pluck('id')->toArray();
        $playerUserIds = $playerCollection->pluck('user_id')->filter()->toArray();

        $likedPlayerIds = Like::where('user_id', $user->id)
            ->whereIn('player_id', $playerIds)
            ->pluck('player_id')
            ->toArray();

        $followedUserIds = Follow::where('follower_id', $user->id)
            ->whereIn('following_id', $playerUserIds)
            ->pluck('following_id')
            ->toArray();

        foreach ($playerCollection as $player) {
            $player->is_liked = in_array($player->id, $likedPlayerIds);
            $player->is_following = in_array($player->user_id, $followedUserIds);
        }

        return $players;
    }
}
