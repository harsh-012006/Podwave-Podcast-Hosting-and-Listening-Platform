<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comment Model
 * Supports threaded comments (replies via parent_id).
 */
class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'episode_id', 'user_id', 'parent_id', 'body', 'is_flagged', 'is_approved',
    ];

    protected $casts = [
        'is_flagged'  => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('user')->latest();
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}
