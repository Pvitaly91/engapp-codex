<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteTreeItem extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'variant_id', 'title', 'linked_page_title', 'linked_page_url', 'level', 'is_checked', 'sort_order'];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(SiteTreeVariant::class, 'variant_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SiteTreeItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SiteTreeItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    public function scopeForVariant($query, $variantId)
    {
        return $query->where('variant_id', $variantId);
    }
}
