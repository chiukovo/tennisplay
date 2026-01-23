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
        'is_coach',
        'coach_price_min',
        'coach_price_max',
        'coach_price_note',
        'coach_methods',
        'coach_locations',
        'coach_tags',
        'coach_certs',
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
        'is_coach' => 'boolean',
        'coach_price_min' => 'integer',
        'coach_price_max' => 'integer',
    ];

    protected $appends = ['photo_url', 'signature_url', 'likes_count', 'comments_count', 'user_uid', 'average_rating', 'ratings_count'];

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
     * 優先使用 withCount 的結果，避免 N+1 查詢問題
     */
    public function getLikesCountAttribute()
    {
        // 優先使用 withCount 載入的結果
        if (array_key_exists('likes_count', $this->attributes)) {
            return $this->attributes['likes_count'];
        }
        return $this->likes()->count();
    }

    /**
     * Get comments count.
     * 優先使用 withCount 的結果，避免 N+1 查詢問題
     */
    public function getCommentsCountAttribute()
    {
        // 優先使用 withCount 載入的結果
        if (array_key_exists('comments_count', $this->attributes)) {
            return $this->attributes['comments_count'];
        }
        return $this->comments()->count();
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute()
    {
        // 優先使用 withAvg 載入的結果 (如果有用 withAvg('comments', 'rating'))
        if (array_key_exists('comments_avg_rating', $this->attributes)) {
            return round($this->attributes['comments_avg_rating'], 1);
        }
        // Fallback to query if not eager loaded
        $avg = $this->comments()->whereNotNull('rating')->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    /**
     * Get ratings count.
     */
    public function getRatingsCountAttribute()
    {
        return $this->comments()->whereNotNull('rating')->count();
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
                  ->orWhere('intro', 'like', "%{$search}%")
                  ->orWhere('coach_tags', 'like', "%{$search}%")
                  ->orWhere('coach_methods', 'like', "%{$search}%")
                  ->orWhere('coach_locations', 'like', "%{$search}%")
                  ->orWhere('coach_certs', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    /**
     * Scope for coach filter.
     */
    public function scopeIsCoach($query, $isCoach)
    {
        if ($isCoach) {
            return $query->where('is_coach', true);
        }
        return $query;
    }

    /**
     * Scope for coach price range.
     */
    public function scopeCoachPriceBetween($query, $min, $max)
    {
        if ($min !== null && $min !== '') {
            $query->whereRaw('COALESCE(coach_price_max, coach_price_min) >= ?', [$min]);
        }
        if ($max !== null && $max !== '') {
            $query->whereRaw('COALESCE(coach_price_min, coach_price_max) <= ?', [$max]);
        }
        return $query;
    }

    /**
     * Scope for coach methods.
     */
    public function scopeCoachMethod($query, $method)
    {
        if ($method && !in_array($method, ['全部', 'all'])) {
            return $query->whereRaw('FIND_IN_SET(?, coach_methods) > 0', [$method]);
        }
        return $query;
    }

    /**
     * Scope for coach tags.
     */
    public function scopeCoachTag($query, $tag)
    {
        if ($tag) {
            return $query->whereRaw('FIND_IN_SET(?, coach_tags) > 0', [$tag]);
        }
        return $query;
    }

    /**
     * Scope for coach locations.
     */
    public function scopeCoachLocation($query, $location)
    {
        if ($location) {
            return $query->whereRaw('FIND_IN_SET(?, coach_locations) > 0', [$location]);
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
