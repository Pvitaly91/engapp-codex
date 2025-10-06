<?php

namespace App\Support;

use App\Models\PageBlock;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class EngramPages
{
    /**
     * Retrieve all configured static pages.
     */
    public static function all(): Collection
    {
        return collect(config('engram.pages', []))
            ->map(function (array $page) {
                return new Fluent($page + [
                    'template' => $page['template'] ?? 'grammar-card',
                ]);
            })
            ->values();
    }

    /**
     * Find a static page by its slug.
     */
    public static function find(string $slug): ?Fluent
    {
        $page = config("engram.pages.$slug");

        if (! $page) {
            return null;
        }

        $blocks = PageBlock::forPage($slug)->get();

        if ($blocks->isEmpty() && ($fallback = config('app.fallback_locale'))) {
            $blocks = PageBlock::forPage($slug, $fallback)->get();
        }

        return new Fluent($page + [
            'template' => $page['template'] ?? 'grammar-card',
            'blocks' => $blocks,
        ]);
    }
}
