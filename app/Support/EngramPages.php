<?php

namespace App\Support;

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
            ->map(fn (array $page) => new Fluent($page))
            ->values();
    }

    /**
     * Find a static page by its slug.
     */
    public static function find(string $slug): ?Fluent
    {
        $page = config("engram.pages.$slug");

        return $page ? new Fluent($page) : null;
    }
}
