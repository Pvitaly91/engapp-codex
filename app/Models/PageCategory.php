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
        'type',
        'parent_id',
        'seeder',
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
            ->whereNull('page_id')
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
     * Check if this category has any children (uses loaded relation if available).
     */
    public function hasChildren(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->isNotEmpty();
        }
        return $this->children()->exists();
    }

    /**
     * Check if a given category is a descendant of this category.
     * Uses already-loaded children relationship for efficiency.
     *
     * @param PageCategory|null $category The category to check
     * @param int $maxDepth Maximum depth to traverse (default 10)
     * @return bool
     */
    public function hasDescendant(?PageCategory $category, int $maxDepth = 10): bool
    {
        if ($maxDepth <= 0 || !$category || !$this->relationLoaded('children') || $this->children->isEmpty()) {
            return false;
        }

        foreach ($this->children as $child) {
            if ($child->id === $category->id) {
                return true;
            }
            if ($child->hasDescendant($category, $maxDepth - 1)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this category is an ancestor of a given category.
     *
     * @param PageCategory|null $category The category to check
     * @return bool
     */
    public function isAncestorOf(?PageCategory $category): bool
    {
        return $this->hasDescendant($category);
    }
}
