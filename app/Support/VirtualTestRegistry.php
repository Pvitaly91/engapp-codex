<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VirtualTestRegistry
{
    private const SESSION_KEY = 'virtual_saved_tests.slugs';

    private const CACHE_PREFIX = 'virtual_saved_test:';

    private const ACCESS_CACHE_PREFIX = 'theory_test_access:';

    public static function register(
        string $baseSlug,
        string $name,
        array $filters,
        ?string $description = null,
        ?int $totalQuestionsAvailable = null
    ): string {
        $slug = self::slugFor($baseSlug, $filters);
        $payload = [
            'name' => $name,
            'filters' => $filters,
            'description' => $description,
            'total_questions_available' => $totalQuestionsAvailable,
        ];

        Cache::put(self::cacheKey($slug), $payload, now()->addDays(14));

        self::rememberSlug($slug);

        return $slug;
    }

    public static function registerStatic(
        string $slug,
        string $name,
        array $filters,
        ?string $description = null,
        ?int $totalQuestionsAvailable = null
    ): string {
        $slug = collect(explode('/', trim($slug, '/')))
            ->map(fn (string $segment): string => Str::slug($segment))
            ->filter()
            ->implode('/');

        $payload = [
            'name' => $name,
            'filters' => $filters,
            'description' => $description,
            'total_questions_available' => $totalQuestionsAvailable,
        ];

        Cache::put(self::cacheKey($slug), $payload, now()->addDays(14));
        self::rememberSlug($slug);

        return $slug;
    }

    public static function rememberSlug(string $slug): void
    {
        Cache::put(self::accessCacheKey($slug), true, now()->addDays(14));

        if (! request()->hasSession()) {
            return;
        }

        $slugs = request()->session()->get(self::SESSION_KEY, []);
        $slugs = is_array($slugs) ? $slugs : [];
        $slugs[$slug] = true;

        request()->session()->put(self::SESSION_KEY, $slugs);
    }

    public static function resolve(string $slug): ?array
    {
        $payload = Cache::get(self::cacheKey($slug));

        return is_array($payload) ? $payload : null;
    }

    public static function has(string $slug): bool
    {
        if (Cache::has(self::cacheKey($slug)) || Cache::has(self::accessCacheKey($slug))) {
            return true;
        }

        if (! request()->hasSession()) {
            return false;
        }

        $slugs = request()->session()->get(self::SESSION_KEY, []);

        return is_array($slugs) && isset($slugs[$slug]);
    }

    public static function slugFor(string $baseSlug, array $filters): string
    {
        $baseSlug = Str::slug($baseSlug !== '' ? $baseSlug : 'theory-test');
        $hash = substr(hash('sha256', self::stableJson($filters)), 0, 12);

        return "{$baseSlug}-{$hash}";
    }

    private static function cacheKey(string $slug): string
    {
        return self::CACHE_PREFIX.$slug;
    }

    private static function accessCacheKey(string $slug): string
    {
        return self::ACCESS_CACHE_PREFIX.$slug;
    }

    private static function stableJson(array $value): string
    {
        $normalized = self::sortRecursively($value);

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    private static function sortRecursively(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::sortRecursively($item);
            }
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
