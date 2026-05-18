<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ListeningHistory Model
 * Tracks user listening progress per episode.
 */
class ListeningHistory extends Model
{
    protected $table = 'listening_history';

    protected $fillable = [
        'user_id', 'episode_id', 'progress_seconds', 'completed', 'listened_at',
    ];

    protected $casts = [
        'completed'   => 'boolean',
        'listened_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /** Progress as a percentage */
    public function getProgressPercentAttribute(): int
    {
        $duration = $this->episode->duration ?? 1;
        return min(100, (int) round(($this->progress_seconds / $duration) * 100));
    }
}
