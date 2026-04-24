<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentOperationRun extends Model
{
    protected $fillable = [
        'replayed_from_run_id',
        'operation_kind',
        'trigger_source',
        'domains',
        'base_ref',
        'head_ref',
        'base_refs_by_domain',
        'dry_run',
        'strict',
        'with_release_check',
        'skip_release_check',
        'bootstrap_uninitialized',
        'status',
        'started_at',
        'finished_at',
        'operator_user_id',
        'summary',
        'payload_json_path',
        'report_path',
        'error_excerpt',
        'meta',
    ];

    protected $casts = [
        'domains' => 'array',
        'base_refs_by_domain' => 'array',
        'dry_run' => 'boolean',
        'strict' => 'boolean',
        'with_release_check' => 'boolean',
        'skip_release_check' => 'boolean',
        'bootstrap_uninitialized' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'summary' => 'array',
        'meta' => 'array',
    ];

    public function replayedFrom()
    {
        return $this->belongsTo(self::class, 'replayed_from_run_id');
    }

    public function replays()
    {
        return $this->hasMany(self::class, 'replayed_from_run_id');
    }
}
