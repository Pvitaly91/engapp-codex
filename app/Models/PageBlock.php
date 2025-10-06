<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PageBlock extends Model
{
    protected $fillable = [
        'page_slug',
        'locale',
        'area',
        'type',
        'label',
        'content',
        'position',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function scopeForPage(Builder $query, string $slug, ?string $locale = null): Builder
    {
        $locale ??= app()->getLocale();

        return $query->where('page_slug', $slug)
            ->where('locale', $locale)
            ->orderBy('position');
    }
}
