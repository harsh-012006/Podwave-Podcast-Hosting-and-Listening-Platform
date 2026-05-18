<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Podcast extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'podcasts';

    protected $fillable = [
        'user_id', 'category_id', 'genre_id', 'title', 'slug',
        'description', 'thumbnail', 'language', 'tags', 'status',
        'is_explicit', 'is_featured', 'total_plays', 'total_subscribers',
        'rating_average', 'rating_count', 'rss_feed',
    ];

    protected $casts = [
        'tags'         => 'array',
        'is_explicit'  => 'boolean',
        'is_featured'  => 'boolean',
        'rating_average' => 'float',
    ];

    // ============================================================
    // BOOT — auto-generate slug
    // ============================================================

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($podcast) {
            if (empty($podcast->slug)) {
                $podcast->slug = Str::slug($podcast->title) . '-' . Str::random(5);
            }
        });
    }

    // ============================================================
    // ACCESSORS
    // ============================================================

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            // Check if file exists in storage/public first
            if (Storage::disk('public')->exists($this->thumbnail)) {
                return Storage::url($this->thumbnail);
            }
            // Fall back to public folder
            return asset($this->thumbnail);
        }
        return asset('images/default-podcast.png');
    }

    public function getTagsListAttribute(): string
    {
        return is_array($this->tags) ? implode(', ', $this->tags) : '';
    }

    public function getEpisodeCountAttribute(): int
    {
        return $this->episodes()->published()->count();
    }

    public function getFormattedPlaysAttribute(): string
    {
        $plays = $this->total_plays;
        if ($plays >= 1_000_000) return round($plays / 1_000_000, 1) . 'M';
        if ($plays >= 1_000)     return round($plays / 1_000, 1) . 'K';
        return (string) $plays;
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('episode_number');
    }

    /** Polymorphic likes */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    /** Users who favorited this podcast */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /** Ratings */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending($query, int $days = 7)
    {
        return $query->withCount(['episodes as recent_plays' => function ($q) use ($days) {
            $q->whereDate('created_at', '>=', now()->subDays($days));
        }])->orderByDesc('total_plays');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByGenre($query, $genreId)
    {
        return $query->where('genre_id', $genreId);
    }

    // ============================================================
    // HELPERS
    // ============================================================

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /** Increment play count and update total */
    public function incrementPlays(): void
    {
        $this->increment('total_plays');
    }

    /** Recalculate rating average */
    public function recalculateRating(): void
    {
        $avg = $this->ratings()->avg('rating');
        $count = $this->ratings()->count();
        $this->update([
            'rating_average' => round($avg, 1),
            'rating_count'   => $count,
        ]);
    }
}
