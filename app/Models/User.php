<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'google_id',
        'role',
        'avatar',
        'bio',
        'website',
        'twitter',
        'instagram',
        'is_banned',
        'ban_reason',
        'banned_at',
        'subscriber_count',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at'         => 'datetime',
            'password'          => 'hashed',
            'is_banned'         => 'boolean',
        ];
    }

    // ============================================================
    // ROLE HELPERS
    // ============================================================

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCreator(): bool
    {
        return $this->role === 'creator';
    }

    public function isListener(): bool
    {
        return $this->role === 'listener';
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    // ============================================================
    // AVATAR HELPER
    // ============================================================

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::url($this->avatar);
        }
        // Generate a letter-based avatar URL as fallback
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) .
               '&background=8B5CF6&color=fff&size=128';
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /** Podcasts this user created (creator role) */
    public function podcasts(): HasMany
    {
        return $this->hasMany(Podcast::class);
    }

    /** Episodes created through podcasts */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'user_id');
    }

    /** Comments this user wrote */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Creators this user subscribes to */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscriptions', 'subscriber_id', 'creator_id')
                    ->withTimestamps();
    }

    /** Subscribers who follow this creator */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscriptions', 'creator_id', 'subscriber_id')
                    ->withTimestamps();
    }

    /** Podcasts this user has favorited */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Podcast::class, 'favorites')->withTimestamps();
    }

    /** Listening history records */
    public function listeningHistory(): HasMany
    {
        return $this->hasMany(ListeningHistory::class)->latest('listened_at');
    }

    /** Recently played episodes (distinct, ordered) */
    public function recentlyPlayed()
    {
        return $this->listeningHistory()
                    ->with('episode.podcast')
                    ->latest('listened_at')
                    ->take(20);
    }

    /** Polymorphic likes (podcasts or episodes) */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /** Ratings */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    // ============================================================
    // SUBSCRIPTION HELPERS
    // ============================================================

    /** Check if this user is subscribed to a creator */
    public function isSubscribedTo(User $creator): bool
    {
        return $this->subscriptions()->where('creator_id', $creator->id)->exists();
    }

    /** Check if a podcast is favorited */
    public function hasFavorited(Podcast $podcast): bool
    {
        return $this->favorites()->where('podcast_id', $podcast->id)->exists();
    }

    /** Get subscriber count */
    public function getSubscriberCountAttribute(): int
    {
        return $this->subscribers()->count();
    }

    /** Get total podcast plays for this creator */
    public function getTotalPlaysAttribute(): int
    {
        return $this->podcasts()->sum('total_plays');
    }
}
