<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SiteTreeVariant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'is_base'];

    protected $casts = [
        'is_base' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variant) {
            if (empty($variant->slug)) {
                $variant->slug = Str::slug($variant->name) . '-' . Str::random(6);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(SiteTreeItem::class, 'variant_id');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(SiteTreeItem::class, 'variant_id')->whereNull('parent_id')->orderBy('sort_order');
    }

    public static function getBase(): ?self
    {
        return static::where('is_base', true)->first();
    }
}
