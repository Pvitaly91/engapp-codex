<?php

namespace Database\Seeders\Page_V3\Concerns;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder as BasePageCategoryDescriptionSeeder;

abstract class PageCategoryDescriptionSeeder extends BasePageCategoryDescriptionSeeder
{
    /**
     * @var array<string, int>
     */
    protected array $tagCache = [];

    protected function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($this->normalizeStringList($tagNames) as $tagName) {
            $cacheKey = mb_strtolower($tagName);

            if (! isset($this->tagCache[$cacheKey])) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $this->tagCache[$cacheKey] = $tag->id;
            }

            $tagIds[] = $this->tagCache[$cacheKey];
        }

        return array_values(array_unique($tagIds));
    }

    protected function normalizeStringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            $clean = trim((string) $item);

            if ($clean === '') {
                continue;
            }

            $normalized[] = preg_replace('/\s+/', ' ', $clean);
        }

        return array_values(array_unique($normalized));
    }

    protected function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === 'ua') {
            return 'uk';
        }

        return $normalized !== '' ? $normalized : 'uk';
    }

    protected function resolveBlockUuid(array $block, int $blockIndex): string
    {
        $explicitUuid = trim((string) ($block['uuid'] ?? ''));

        if ($explicitUuid !== '') {
            return $explicitUuid;
        }

        $uuidKey = trim((string) ($block['uuid_key'] ?? ''));

        if ($uuidKey !== '') {
            return TextBlockUuidGenerator::generateWithKey(static::class, $uuidKey);
        }

        return TextBlockUuidGenerator::generate(static::class, $blockIndex);
    }

    protected function resolveParentCategoryId(array $payload): ?int
    {
        if (! empty($payload['parent_id'])) {
            return (int) $payload['parent_id'];
        }

        $parentSlug = trim((string) ($payload['parent_slug'] ?? $payload['parent_category'] ?? ''));

        if ($parentSlug === '') {
            return null;
        }

        return PageCategory::query()
            ->where('slug', $parentSlug)
            ->value('id');
    }
}
