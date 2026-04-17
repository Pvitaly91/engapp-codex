<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'text', 'type', 'seeder', 'page_category_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }

    public function textBlocks(): HasMany
    {
        return $this->hasMany(TextBlock::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function theoryVariants(): MorphMany
    {
        return $this->morphMany(TheoryVariant::class, 'variantable');
    }

    public function theoryVariantSelections(): MorphMany
    {
        return $this->morphMany(TheoryVariantSelection::class, 'variantable');
    }

    public function scopeForType($query, ?string $type)
    {
        return $type === null
            ? $query->whereNull('type')
            : $query->where('type', $type);
    }
}
