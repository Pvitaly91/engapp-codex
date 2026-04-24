<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentSyncState extends Model
{
    protected $fillable = [
        'domain',
        'last_successful_ref',
        'last_successful_applied_at',
        'last_attempted_base_ref',
        'last_attempted_head_ref',
        'last_attempted_status',
        'last_attempted_at',
        'last_attempt_meta',
    ];

    protected $casts = [
        'last_successful_applied_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'last_attempt_meta' => 'array',
    ];
}
