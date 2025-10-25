<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageCategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'language',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
