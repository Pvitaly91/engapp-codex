<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageCategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'language',
        'parent_id',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PageCategory::class, 'parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.) recursively.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if this category has any children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get the depth level of this category in the hierarchy.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        return $depth;
    }
}
