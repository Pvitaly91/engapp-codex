<?php

namespace App\Modules\GitDeployment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'commit_hash',
        'pushed_at',
        'action',
        'description',
        'used_at',
    ];

    protected $casts = [
        'pushed_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Track usage of a branch
     */
    public static function trackUsage(string $branchName, string $action, ?string $description = null): void
    {
        $branch = static::firstOrNew(['name' => $branchName]);
        
        // Only set commit_hash if it's a new record and empty
        if (! $branch->exists && empty($branch->commit_hash)) {
            $branch->commit_hash = '';
        }
        
        $branch->action = $action;
        $branch->description = $description;
        $branch->used_at = now();
        $branch->save();
    }

    /**
     * Get recent branch usage
     */
    public static function getRecentUsage(int $limit = 10)
    {
        return static::whereNotNull('used_at')
            ->orderByDesc('used_at')
            ->limit($limit)
            ->get();
    }
}
