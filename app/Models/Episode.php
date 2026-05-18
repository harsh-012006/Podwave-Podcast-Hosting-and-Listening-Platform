<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Episode extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'episodes';

    protected $fillable = [
        'podcast_id', 'title', 'slug', 'description', 'audio_file',
        'thumbnail', 'duration', 'season_number', 'episode_number',
        'episode_type', 'status', 'release_date', 'is_explicit',
        'play_count', 'show_notes', 'chapters', 'transcript',
    ];

    protected $casts = [
        'release_date' => 'datetime',
        'is_explicit'  => 'boolean',
        'chapters'     => 'array',
    ];

    // ============================================================
    // BOOT
    // ============================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($episode) {
            if (empty($episode->slug)) {
                $episode->slug = Str::slug($episode->title) . '-' . Str::random(5);
            }
        });

        // When an episode play is incremented, sync to podcast
        static::saved(function ($episode) {
            if ($episode->isDirty('play_count')) {
                $episode->podcast->increment('total_plays');
            }
        });
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getAudioUrlAttribute(): string
    {
        if ($this->audio_file && Storage::disk('public')->exists($this->audio_file)) {
            return Storage::url($this->audio_file);
        }
        return '';
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail && Storage::disk('public')->exists($this->thumbnail)) {
            return Storage::url($this->thumbnail);
        }
        // Fall back to podcast thumbnail
        return $this->podcast->thumbnail_url ?? asset('images/default-episode.png');
    }

    public function getFormattedDurationAttribute(): string
    {
        $seconds = $this->duration;
        if ($seconds <= 0) return '0:00';
        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;
        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }

    public function getFormattedPlaysAttribute(): string
    {
        $plays = $this->play_count;
        if ($plays >= 1_000_000) return round($plays / 1_000_000, 1) . 'M';
        if ($plays >= 1_000)     return round($plays / 1_000, 1) . 'K';
        return (string) $plays;
    }

    public function getEpisodeLabelAttribute(): string
    {
        $parts = [];
        if ($this->season_number)  $parts[] = 'S' . str_pad($this->season_number, 2, '0', STR_PAD_LEFT);
        if ($this->episode_number) $parts[] = 'E' . str_pad($this->episode_number, 2, '0', STR_PAD_LEFT);
        return implode('·', $parts) ?: 'Episode';
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->with('replies.user')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function listeningHistory(): HasMany
    {
        return $this->hasMany(ListeningHistory::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where(function ($q) {
                         $q->whereNull('release_date')
                           ->orWhere('release_date', '<=', now());
                     });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('release_date');
    }

    public function scopeTrending($query)
    {
        return $query->orderByDesc('play_count');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function getLikeCountAttribute(): int
    {
        return $this->likes()->count();
    }

    public function getCommentCountAttribute(): int
    {
        return $this->allComments()->count();
    }

    /** Track or update a user's listen progress */
    public function trackProgress(int $userId, int $progressSeconds): void
    {
        ListeningHistory::updateOrCreate(
            ['user_id' => $userId, 'episode_id' => $this->id],
            [
                'progress_seconds' => $progressSeconds,
                'completed'        => $progressSeconds >= ($this->duration * 0.9),
                'listened_at'      => now(),
            ]
        );
    }

    /** Increment play count */
    public function incrementPlays(): void
    {
        $this->increment('play_count');
    }
}
