<?php

namespace App\Modules\GitDeployment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchUsageHistory extends Model
{
    use HasFactory;

    protected $table = 'branch_usage_history';

    protected $fillable = [
        'branch_name',
        'action',
        'description',
        'paths',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'paths' => 'array',
    ];

    /**
     * Track usage of a branch
     */
    public static function trackUsage(string $branchName, string $action, ?string $description = null, ?array $paths = null): void
    {
        static::create([
            'branch_name' => $branchName,
            'action' => $action,
            'description' => $description,
            'paths' => $paths,
            'used_at' => now(),
        ]);
    }

    /**
     * Get recent branch usage
     */
    public static function getRecentUsage(int $limit = 10)
    {
        return static::orderByDesc('used_at')
            ->limit($limit)
            ->get();
    }
}
