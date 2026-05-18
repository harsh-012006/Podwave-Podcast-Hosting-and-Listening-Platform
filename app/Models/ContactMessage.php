<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ContactMessage Model
 */
class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = ['name', 'email', 'subject', 'message', 'is_read', 'read_at'];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];
}
