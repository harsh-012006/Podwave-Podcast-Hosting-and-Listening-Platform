<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rating Model
 * Star ratings (1–5) per user per podcast.
 */
class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = ['user_id', 'podcast_id', 'rating'];

    protected $casts = ['rating' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }
}
