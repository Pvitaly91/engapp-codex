<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractiveTest extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
