<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function textBlocks(): HasMany
    {
        return $this->hasMany(TextBlock::class, 'page_category_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
