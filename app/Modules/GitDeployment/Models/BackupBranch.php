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
    ];

    protected $casts = [
        'pushed_at' => 'datetime',
    ];
}
