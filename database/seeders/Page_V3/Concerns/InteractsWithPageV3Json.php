<?php

namespace Database\Seeders\Page_V3\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use RuntimeException;

trait InteractsWithPageV3Json
{
    protected ?array $pageV3SourceDefinition = null;

    abstract protected function sourcePath(): string;

    protected function sourceEntityType(): string
    {
        return 'page';
    }

    protected function sourceSlug(): ?string
    {
        return null;
    }

    protected function resolvedSlugFromSource(): string
    {
        $slug = trim((string) ($this->sourceDefinition()['slug'] ?? ''));

        if ($slug === '') {
            throw new RuntimeException(sprintf('Page_V3 source [%s] must define slug.', $this->sourcePath()));
        }

        return $slug;
    }

    protected function resolvedPageTypeFromSource(): ?string
    {
        $type = trim((string) ($this->sourceDefinition()['type'] ?? ''));

        if ($type === '' || $this->normalizePageV3EntityType($type) === 'category') {
            return 'theory';
        }

        return $type;
    }

    protected function resolvedCategoryFromSource(): ?array
    {
        $definition = $this->sourceDefinition();
        $entityType = $this->detectPageV3EntityType($definition);

        if ($entityType === 'page') {
            $category = $definition['category'] ?? null;

            return is_array($category)
                ? $this->normalizePageV3CategoryConfig($category)
                : null;
        }

        return $this->normalizePageV3CategoryConfig([
            'slug' => $definition['slug'] ?? null,
            'title' => $definition['title'] ?? null,
            'language' => $definition['language'] ?? $definition['locale'] ?? 'uk',
            'type' => $definition['type'] ?? 'theory',
            'parent_slug' => $definition['parent_slug'] ?? $definition['parent_category'] ?? null,
            'parent_id' => $definition['parent_id'] ?? null,
            'tags' => $definition['tags'] ?? [],
            'base_tags' => $definition['base_tags'] ?? [],
        ]);
    }

    protected function resolvedPageConfigFromSource(): array
    {
        $definition = $this->sourceDefinition();
        $config = [
            'title' => trim((string) ($definition['title'] ?? $this->resolvedSlugFromSource())),
            'subtitle_html' => $definition['subtitle_html'] ?? null,
            'subtitle_text' => $definition['subtitle_text'] ?? null,
            'locale' => $this->normalizePageV3Locale((string) ($definition['locale'] ?? 'uk')),
            'blocks' => $this->normalizePageV3Blocks($definition['blocks'] ?? []),
        ];

        foreach (['subtitle_level', 'subtitle_uuid', 'subtitle_uuid_key', 'subtitle_inherit_base_tags'] as $key) {
            if (array_key_exists($key, $definition)) {
                $config[$key] = $definition[$key];
            }
        }

        foreach (['tags', 'base_tags', 'subtitle_tags'] as $key) {
            if (array_key_exists($key, $definition)) {
                $config[$key] = $this->normalizePageV3StringList($definition[$key]);
            }
        }

        $category = $this->resolvedCategoryFromSource();

        if ($category !== null) {
            $config['category'] = $category;
        }

        return $config;
    }

    protected function resolvedCategoryDescriptionFromSource(): array
    {
        $definition = $this->sourceDefinition();
        $description = [
            'title' => trim((string) ($definition['title'] ?? $this->resolvedSlugFromSource())),
            'subtitle_html' => $definition['subtitle_html'] ?? null,
            'subtitle_text' => $definition['subtitle_text'] ?? null,
            'locale' => $this->normalizePageV3Locale((string) ($definition['locale'] ?? $definition['language'] ?? 'uk')),
            'blocks' => $this->normalizePageV3Blocks($definition['blocks'] ?? []),
        ];

        foreach (['subtitle_level', 'subtitle_uuid', 'subtitle_uuid_key', 'subtitle_inherit_base_tags'] as $key) {
            if (array_key_exists($key, $definition)) {
                $description[$key] = $definition[$key];
            }
        }

        foreach (['tags', 'base_tags', 'subtitle_tags'] as $key) {
            if (array_key_exists($key, $definition)) {
                $description[$key] = $this->normalizePageV3StringList($definition[$key]);
            }
        }

        return $description;
    }

    protected function sourceDefinition(): array
    {
        if ($this->pageV3SourceDefinition !== null) {
            return $this->pageV3SourceDefinition;
        }

        $path = $this->sourcePath();

        if (! File::exists($path)) {
            throw new RuntimeException(sprintf('Page_V3 source not found: %s', $path));
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException(sprintf('Invalid Page_V3 source: %s', $path));
        }

        $definition = $this->flattenPageV3Payload($decoded);
        $expectedEntityType = $this->normalizePageV3EntityType($this->sourceEntityType());
        $actualEntityType = $this->detectPageV3EntityType($definition);

        if ($actualEntityType !== $expectedEntityType) {
            throw new RuntimeException(sprintf(
                'Page_V3 source [%s] resolved to [%s], expected [%s].',
                $path,
                $actualEntityType,
                $expectedEntityType
            ));
        }

        $expectedSlug = trim((string) $this->sourceSlug());
        $actualSlug = trim((string) ($definition['slug'] ?? ''));

        if ($expectedSlug !== '' && $actualSlug !== $expectedSlug) {
            throw new RuntimeException(sprintf(
                'Page_V3 source [%s] resolved slug [%s], expected [%s].',
                $path,
                $actualSlug,
                $expectedSlug
            ));
        }

        return $this->pageV3SourceDefinition = $definition;
    }

    private function flattenPageV3Payload(array $payload): array
    {
        if (is_array($payload['items'] ?? null)) {
            return $this->flattenPageV3Payload($this->selectPageV3SeedPackItem($payload));
        }

        if (is_array($payload['page'] ?? null) || is_array($payload['description'] ?? null)) {
            return $this->flattenWrappedPageV3Definition($payload);
        }

        return $payload;
    }

    private function selectPageV3SeedPackItem(array $payload): array
    {
        $expectedEntityType = $this->normalizePageV3EntityType($this->sourceEntityType());
        $expectedSlug = trim((string) $this->sourceSlug());

        foreach ($payload['items'] as $item) {
            if (! is_array($item) || ! is_array($item['data'] ?? null)) {
                continue;
            }

            $itemSlug = trim((string) ($item['slug'] ?? $item['data']['slug'] ?? ''));
            $itemEntityType = $this->detectPageV3EntityType($item['data']);

            if ($itemEntityType !== $expectedEntityType) {
                continue;
            }

            if ($expectedSlug !== '' && $itemSlug !== $expectedSlug) {
                continue;
            }

            return $item['data'];
        }

        throw new RuntimeException(sprintf(
            'Seed pack [%s] does not contain [%s] item with slug [%s].',
            $this->sourcePath(),
            $expectedEntityType,
            $expectedSlug !== '' ? $expectedSlug : '*'
        ));
    }

    private function flattenWrappedPageV3Definition(array $payload): array
    {
        if (is_array($payload['description'] ?? null)) {
            $definition = $payload['description'];
            $definition['entity_type'] = 'category';
            $definition['slug'] = $payload['slug']
                ?? Arr::get($payload, 'category.slug')
                ?? ($definition['slug'] ?? null);
            $definition['title'] = $payload['title']
                ?? Arr::get($payload, 'category.title')
                ?? ($definition['title'] ?? null);
            $definition['language'] = Arr::get($payload, 'category.language')
                ?? ($definition['language'] ?? $definition['locale'] ?? 'uk');
            $definition['type'] = Arr::get($payload, 'category.type')
                ?? ($payload['type'] ?? $definition['type'] ?? 'theory');
            $definition['parent_slug'] = Arr::get($payload, 'category.parent_slug')
                ?? ($definition['parent_slug'] ?? $definition['parent_category'] ?? null);
            $definition['parent_id'] = Arr::get($payload, 'category.parent_id')
                ?? ($definition['parent_id'] ?? null);

            if (! array_key_exists('tags', $definition) && is_array(Arr::get($payload, 'category.tags'))) {
                $definition['tags'] = Arr::get($payload, 'category.tags');
            }

            if (! array_key_exists('base_tags', $definition) && is_array(Arr::get($payload, 'category.base_tags'))) {
                $definition['base_tags'] = Arr::get($payload, 'category.base_tags');
            }

            return $definition;
        }

        $definition = $payload['page'];
        $definition['slug'] = $payload['slug'] ?? ($definition['slug'] ?? null);
        $definition['type'] = $payload['type'] ?? ($definition['type'] ?? 'theory');

        if (! array_key_exists('category', $definition) && is_array($payload['category'] ?? null)) {
            $definition['category'] = $payload['category'];
        }

        return $definition;
    }

    private function detectPageV3EntityType(array $definition): string
    {
        $entityType = trim((string) ($definition['entity_type'] ?? ''));

        if ($entityType !== '') {
            return $this->normalizePageV3EntityType($entityType);
        }

        $type = strtolower(trim((string) ($definition['type'] ?? '')));

        if ($type === 'theory_category' || $type === 'category') {
            return 'category';
        }

        if (
            array_key_exists('language', $definition)
            || array_key_exists('parent_category', $definition)
            || array_key_exists('parent_slug', $definition)
        ) {
            return 'category';
        }

        return 'page';
    }

    private function normalizePageV3EntityType(string $entityType): string
    {
        $normalized = strtolower(trim($entityType));

        return in_array($normalized, ['category', 'theory_category', 'page_category'], true)
            ? 'category'
            : 'page';
    }

    private function normalizePageV3CategoryConfig(array $category): ?array
    {
        $slug = trim((string) ($category['slug'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $normalized = [
            'slug' => $slug,
            'title' => $category['title'] ?? $slug,
            'language' => $this->normalizePageV3Locale((string) ($category['language'] ?? $category['locale'] ?? 'uk')),
        ];

        $type = trim((string) ($category['type'] ?? ''));

        if ($type !== '') {
            $normalized['type'] = $this->normalizePageV3EntityType($type) === 'category' ? 'theory' : $type;
        }

        $parentSlug = trim((string) ($category['parent_slug'] ?? $category['parent_category'] ?? ''));

        if ($parentSlug !== '') {
            $normalized['parent_slug'] = $parentSlug;
        }

        if (array_key_exists('parent_id', $category) && $category['parent_id'] !== null && $category['parent_id'] !== '') {
            $normalized['parent_id'] = (int) $category['parent_id'];
        }

        foreach (['tags', 'base_tags'] as $key) {
            if (array_key_exists($key, $category)) {
                $normalized[$key] = $this->normalizePageV3StringList($category[$key]);
            }
        }

        return $normalized;
    }

    private function normalizePageV3Blocks(mixed $blocks): array
    {
        if (! is_array($blocks)) {
            return [];
        }

        $normalized = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $block['body'] = $this->normalizePageV3BlockBody($block['body'] ?? null);

            if (array_key_exists('tags', $block)) {
                $block['tags'] = $this->normalizePageV3StringList($block['tags']);
            }

            if (array_key_exists('locale', $block)) {
                $block['locale'] = $this->normalizePageV3Locale((string) $block['locale']);
            }

            $normalized[] = $block;
        }

        return $normalized;
    }

    private function normalizePageV3BlockBody(mixed $body): ?string
    {
        if ($body === null) {
            return null;
        }

        if (is_string($body)) {
            return $body;
        }

        return json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function normalizePageV3StringList(mixed $value): array
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

    private function normalizePageV3Locale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === 'ua') {
            return 'uk';
        }

        return $normalized !== '' ? $normalized : 'uk';
    }
}
