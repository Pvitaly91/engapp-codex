<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentOperationLock extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lock_key',
        'owner_token',
        'operation_kind',
        'trigger_source',
        'domains',
        'content_operation_run_id',
        'operator_user_id',
        'acquired_at',
        'heartbeat_at',
        'expires_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'domains' => 'array',
        'content_operation_run_id' => 'integer',
        'operator_user_id' => 'integer',
        'acquired_at' => 'datetime',
        'heartbeat_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];
}
