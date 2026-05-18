<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Like Model — polymorphic likes for podcasts and episodes.
 */
class Like extends Model
{
    protected $table = 'likes';

    protected $fillable = ['user_id', 'likeable_id', 'likeable_type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
