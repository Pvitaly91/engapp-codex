<?php

namespace App\Support\Database;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

abstract class JsonPageSeeder extends Seeder
{
    /**
     * @var array<string, int>
     */
    protected array $tagCache = [];

    final public function run(): void
    {
        $definition = $this->loadDefinitionFromFile($this->definitionPath());
        $seederClass = $this->resolveSeederClassName($definition);

        $this->seedDefinition($definition, $seederClass);
        app(JsonPageLocalizationManager::class)->syncDefinitionLocalizations(
            $definition,
            $this->definitionPath(),
            $seederClass
        );
    }

    abstract protected function definitionPath(): string;

    public function resolvedDefinitionPath(): string
    {
        return $this->definitionPath();
    }

    protected function seederDirectory(): string
    {
        $reflection = new \ReflectionClass($this);
        $fileName = $reflection->getFileName();

        if (! is_string($fileName) || $fileName === '') {
            throw new RuntimeException(sprintf(
                'Unable to resolve file path for seeder [%s].',
                static::class
            ));
        }

        return dirname($fileName);
    }

    protected function seederAssetPath(string $relativePath = ''): string
    {
        $directory = rtrim($this->seederDirectory(), DIRECTORY_SEPARATOR);
        $relativePath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);

        if ($relativePath === '') {
            return $directory;
        }

        return $directory . DIRECTORY_SEPARATOR . $relativePath;
    }

    protected function loadDefinitionFromFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("Page V3 definition not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid Page V3 definition: {$path}");
        }

        return $decoded;
    }

    protected function seedDefinition(array $definition, ?string $seederClass = null): void
    {
        $contentType = $this->resolveContentType($definition);
        $seederClass = filled($seederClass) ? $seederClass : $this->resolveSeederClassName($definition);

        if ($contentType === 'category') {
            $this->seedCategoryDefinition($definition, $seederClass);

            return;
        }

        $this->seedPageDefinition($definition, $seederClass);
    }

    protected function resolveSeederClassName(array $definition): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        return $configured !== '' ? $configured : static::class;
    }

    protected function resolveContentType(array $definition): string
    {
        return app(JsonPageDefinitionIndex::class)->resolveContentType($definition);
    }

    protected function resolveContentConfig(array $definition): array
    {
        return app(JsonPageDefinitionIndex::class)->resolveContentConfig($definition);
    }

    protected function seedPageDefinition(array $definition, string $seederClass): void
    {
        $config = $this->resolveContentConfig($definition);
        $slug = trim((string) ($definition['slug'] ?? ''));

        if ($slug === '') {
            throw new RuntimeException('Page V3 page definition must define slug.');
        }

        $title = trim((string) ($config['title'] ?? ''));

        if ($title === '') {
            throw new RuntimeException("Page V3 page definition [{$slug}] must define title.");
        }

        $locale = $this->normalizeLocale((string) ($config['locale'] ?? 'uk'));
        $type = $definition['type'] ?? ($config['type'] ?? null);
        $category = $this->resolveOrCreateCategory(
            $config['category'] ?? null,
            $locale,
            $seederClass,
            false
        );

        $matchAttributes = ['slug' => $slug];

        if ($type !== null) {
            $matchAttributes['type'] = $type;
        }

        $page = Page::updateOrCreate(
            $matchAttributes,
            [
                'title' => $title,
                'text' => $config['subtitle_text'] ?? null,
                'type' => $type,
                'seeder' => $seederClass,
                'page_category_id' => $category?->getKey(),
            ]
        );

        TextBlock::query()
            ->where('page_id', $page->getKey())
            ->where('seeder', $seederClass)
            ->delete();

        $pageAnchorTags = $this->normalizeStringList($config['tags'] ?? []);
        $categorySlug = $category?->slug ?? trim((string) Arr::get($config, 'category.slug', ''));
        $normalizedAnchorTagSlugs = array_values(array_filter(array_map(
            fn (string $tag): string => Str::slug($tag),
            $pageAnchorTags
        )));

        if ($categorySlug !== '' && ! in_array(Str::slug($categorySlug), $normalizedAnchorTagSlugs, true)) {
            $pageAnchorTags[] = $categorySlug;
        }

        $baseTags = $this->normalizeStringList($config['base_tags'] ?? $pageAnchorTags);
        $baseTagIds = $this->resolveTagIds($baseTags);
        $aggregatedBlockTagIds = [];
        $scope = $this->blockUuidScope($seederClass, $locale);
        $createdBlocks = [];
        $blockCounter = 0;

        if (! empty($config['subtitle_html'])) {
            $subtitleConfig = [
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'body' => $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'tags' => $config['subtitle_tags'] ?? [],
                'inherit_base_tags' => $config['subtitle_inherit_base_tags'] ?? true,
                'uuid_key' => $config['subtitle_uuid_key'] ?? 'subtitle',
            ];

            $textBlock = $this->upsertTextBlock($this->resolveBlockUuid($scope, $subtitleConfig, $blockCounter), [
                'page_id' => $page->getKey(),
                'page_category_id' => $category?->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'seeder' => $seederClass,
            ]);

            $createdBlocks[] = ['block' => $textBlock, 'config' => $subtitleConfig];
            $blockCounter++;
        }

        foreach (($config['blocks'] ?? []) as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $textBlock = $this->upsertTextBlock($this->resolveBlockUuid($scope, $block, $blockCounter), [
                'page_id' => $page->getKey(),
                'page_category_id' => $category?->getKey(),
                'locale' => $locale,
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'level' => $block['level'] ?? null,
                'seeder' => $seederClass,
            ]);

            $createdBlocks[] = ['block' => $textBlock, 'config' => $block];
            $blockCounter++;
        }

        foreach ($createdBlocks as $item) {
            /** @var TextBlock $textBlock */
            $textBlock = $item['block'];
            $blockConfig = $item['config'];
            $inheritBaseTags = $blockConfig['inherit_base_tags']
                ?? $blockConfig['inherit_tags']
                ?? true;

            $blockTagIds = $inheritBaseTags ? $baseTagIds : [];

            if (! empty($blockConfig['tags'])) {
                $blockTagIds = array_values(array_unique(array_merge(
                    $blockTagIds,
                    $this->resolveTagIds($blockConfig['tags'])
                )));
            }

            if ($blockTagIds !== []) {
                $textBlock->tags()->sync($blockTagIds);
            }

            $aggregatedBlockTagIds = array_values(array_unique(array_merge($aggregatedBlockTagIds, $blockTagIds)));
        }

        $pageTagIds = array_values(array_unique(array_merge(
            $aggregatedBlockTagIds,
            $this->resolveTagIds($pageAnchorTags)
        )));

        if ($pageTagIds !== []) {
            $page->tags()->sync($pageTagIds);
        }
    }

    protected function seedCategoryDefinition(array $definition, string $seederClass): void
    {
        $config = $this->resolveContentConfig($definition);
        $slug = trim((string) ($definition['slug'] ?? Arr::get($definition, 'category.slug', '')));

        if ($slug === '') {
            throw new RuntimeException('Page V3 category definition must define slug.');
        }

        $locale = $this->normalizeLocale((string) ($config['locale'] ?? Arr::get($definition, 'category.language', 'uk')));
        $categoryConfig = is_array($definition['category'] ?? null) ? $definition['category'] : [];
        $categoryConfig['slug'] = $slug;
        $categoryConfig['title'] = $categoryConfig['title'] ?? ($config['category_title'] ?? $config['title'] ?? $slug);
        $categoryConfig['language'] = $categoryConfig['language'] ?? $locale;
        $categoryConfig['type'] = $categoryConfig['type'] ?? ($definition['type'] ?? 'theory');
        $category = $this->resolveOrCreateCategory($categoryConfig, $locale, $seederClass, true);

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->where('seeder', $seederClass)
            ->delete();

        $categoryAnchorTags = $this->normalizeStringList(
            $config['tags']
            ?? $categoryConfig['tags']
            ?? []
        );
        $baseTags = $this->normalizeStringList($config['base_tags'] ?? $categoryAnchorTags);
        $baseTagIds = $this->resolveTagIds($baseTags);
        $aggregatedBlockTagIds = [];
        $scope = $this->blockUuidScope($seederClass, $locale);
        $createdBlocks = [];
        $blockCounter = 0;

        if (! empty($config['subtitle_html'])) {
            $subtitleConfig = [
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'body' => $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'tags' => $config['subtitle_tags'] ?? [],
                'inherit_base_tags' => $config['subtitle_inherit_base_tags'] ?? true,
                'uuid_key' => $config['subtitle_uuid_key'] ?? 'subtitle',
            ];

            $textBlock = $this->upsertTextBlock($this->resolveBlockUuid($scope, $subtitleConfig, $blockCounter), [
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'seeder' => $seederClass,
            ]);

            $createdBlocks[] = ['block' => $textBlock, 'config' => $subtitleConfig];
            $blockCounter++;
        }

        foreach (($config['blocks'] ?? []) as $index => $block) {
            if (! is_array($block)) {
                continue;
            }

            $textBlock = $this->upsertTextBlock($this->resolveBlockUuid($scope, $block, $blockCounter), [
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $this->normalizeLocale((string) ($block['locale'] ?? $locale)),
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'level' => $block['level'] ?? null,
                'seeder' => $seederClass,
            ]);

            $createdBlocks[] = ['block' => $textBlock, 'config' => $block];
            $blockCounter++;
        }

        foreach ($createdBlocks as $item) {
            /** @var TextBlock $textBlock */
            $textBlock = $item['block'];
            $blockConfig = $item['config'];
            $inheritBaseTags = $blockConfig['inherit_base_tags']
                ?? $blockConfig['inherit_tags']
                ?? true;

            $blockTagIds = $inheritBaseTags ? $baseTagIds : [];

            if (! empty($blockConfig['tags'])) {
                $blockTagIds = array_values(array_unique(array_merge(
                    $blockTagIds,
                    $this->resolveTagIds($blockConfig['tags'])
                )));
            }

            if ($blockTagIds !== []) {
                $textBlock->tags()->sync($blockTagIds);
            }

            $aggregatedBlockTagIds = array_values(array_unique(array_merge($aggregatedBlockTagIds, $blockTagIds)));
        }

        $categoryTagIds = array_values(array_unique(array_merge(
            $aggregatedBlockTagIds,
            $this->resolveTagIds($categoryAnchorTags)
        )));

        if ($categoryTagIds !== []) {
            $category->tags()->sync($categoryTagIds);
        }
    }

    protected function resolveOrCreateCategory(
        mixed $payload,
        string $locale,
        string $seederClass,
        bool $setSeeder = false,
    ): ?PageCategory {
        if (! is_array($payload)) {
            return null;
        }

        $slug = trim((string) ($payload['slug'] ?? ''));

        if ($slug === '') {
            return null;
        }

        $existingCategory = PageCategory::query()
            ->where('slug', $slug)
            ->first();
        $hasExplicitParent = array_key_exists('parent_id', $payload) || array_key_exists('parent_slug', $payload);
        $hasExplicitLanguage = array_key_exists('language', $payload) || array_key_exists('locale', $payload);
        $hasExplicitType = array_key_exists('type', $payload);
        $hasExplicitTitle = array_key_exists('title', $payload);
        $parentId = $hasExplicitParent
            ? $this->resolveCategoryParentId($payload)
            : $existingCategory?->parent_id;
        $language = $hasExplicitLanguage
            ? $this->normalizeLocale((string) ($payload['language'] ?? $payload['locale'] ?? $locale))
            : ($existingCategory?->language ?? $this->normalizeLocale($locale));
        $attributes = [
            'title' => $hasExplicitTitle
                ? ($payload['title'] ?? $slug)
                : ($existingCategory?->title ?? $slug),
            'language' => $language,
            'type' => $hasExplicitType
                ? ($payload['type'] ?? null)
                : $existingCategory?->type,
            'parent_id' => $parentId,
        ];

        if ($setSeeder) {
            $attributes['seeder'] = $seederClass;
        }

        return PageCategory::updateOrCreate(
            ['slug' => $slug],
            $attributes
        );
    }

    protected function resolveCategoryParentId(array $payload): ?int
    {
        if (! empty($payload['parent_id'])) {
            return (int) $payload['parent_id'];
        }

        $parentSlug = trim((string) ($payload['parent_slug'] ?? ''));

        if ($parentSlug === '') {
            return null;
        }

        return PageCategory::query()
            ->where('slug', $parentSlug)
            ->value('id');
    }

    protected function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($this->normalizeStringList($tagNames) as $tagName) {
            if (isset($this->tagCache[$tagName])) {
                $tagIds[] = $this->tagCache[$tagName];

                continue;
            }

            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $this->tagCache[$tagName] = $tag->id;
            $tagIds[] = $tag->id;
        }

        return array_values(array_unique($tagIds));
    }

    protected function syncTags(Model $model, array $tagNames): void
    {
        $tagIds = $this->resolveTagIds($tagNames);

        if ($tagIds === []) {
            return;
        }

        $model->tags()->sync($tagIds);
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

    protected function upsertTextBlock(string $uuid, array $attributes): TextBlock
    {
        return TextBlock::query()->updateOrCreate(
            ['uuid' => $uuid],
            $attributes + ['uuid' => $uuid]
        );
    }

    protected function resolveBlockUuid(string $scope, array $block, int $blockIndex): string
    {
        $explicit = trim((string) ($block['uuid'] ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        $uuidKey = trim((string) ($block['uuid_key'] ?? ''));

        if ($uuidKey !== '') {
            return TextBlockUuidGenerator::generateWithKey($scope, $uuidKey);
        }

        return TextBlockUuidGenerator::generate($scope, $blockIndex);
    }

    protected function blockUuidScope(string $seederClass, string $locale): string
    {
        return $seederClass . '::' . $this->normalizeLocale($locale);
    }
}
