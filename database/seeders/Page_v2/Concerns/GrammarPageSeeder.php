<?php

namespace Database\Seeders\Page_v2\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use App\Support\TextBlock\TextBlockUuidGenerator;

/**
 * Base class for grammar theory page seeders with BLOCK-FIRST TAGGING support.
 *
 * BLOCK-FIRST TAGGING Principle:
 * 1. Each TextBlock has DETAILED tags (specific to that block's content)
 * 2. Page has AGGREGATE tags: union(all block tags) + page anchor tags
 * 3. base_tags provide controlled inheritance to blocks (not automatic page->block sync)
 *
 * Page config structure:
 * [
 *   'title' => '...',
 *   'tags' => [...],              // Page anchor tags (short, 2-8 tags)
 *   'base_tags' => [...],         // Tags inherited by blocks (controlled inheritance)
 *   'blocks' => [
 *     [
 *       'type' => '...',
 *       'body' => '...',
 *       'tags' => [...],              // Block-specific detailed tags
 *       'inherit_base_tags' => true,  // Default true - inherit base_tags
 *     ],
 *   ]
 * ]
 *
 * Backward compatibility:
 * - If base_tags is not defined, page['tags'] are used as base_tags for blocks
 * - If block has no tags, it inherits base_tags (or page.tags for backward compat)
 */
abstract class GrammarPageSeeder extends Seeder
{
    /**
     * Cache for Tag::firstOrCreate to avoid N+1 queries.
     *
     * @var array<string, int>
     */
    protected array $tagCache = [];

    abstract protected function slug(): string;

    abstract protected function page(): array;

    protected function type(): ?string
    {
        return null;
    }

    protected function category(): ?array
    {
        return null;
    }

    protected function cleanupSeederClasses(): array
    {
        return [static::class];
    }

    public function run(): void
    {
        $slug = $this->slug();
        $config = $this->page();
        $type = $this->type();

        $categoryConfig = $config['category'] ?? $this->category();
        $categoryId = null;
        $categorySlug = null;

        if ($categoryConfig && ! empty($categoryConfig['slug'])) {
            $language = $categoryConfig['language']
                ?? $categoryConfig['locale']
                ?? ($config['locale'] ?? 'uk');

            $isPageV2Seeder = str_contains(static::class, 'Database\\Seeders\\Page_v2\\');
            $hasCategoryInName = str_contains(class_basename(static::class), 'Category');

            $category = $isPageV2Seeder && ! $hasCategoryInName
                ? PageCategory::where('slug', $categoryConfig['slug'])->first()
                : PageCategory::updateOrCreate(
                    ['slug' => $categoryConfig['slug']],
                    [
                        'title' => $categoryConfig['title'] ?? $categoryConfig['slug'],
                        'language' => $language,
                        'seeder' => static::class,
                    ]
                );

            $categoryId = $category?->id;
            $categorySlug = $categoryConfig['slug'];
        }

        $matchAttributes = ['slug' => $slug];

        if (! is_null($type)) {
            $matchAttributes['type'] = $type;
        }

        $page = Page::updateOrCreate(
            $matchAttributes,
            [
                'title' => $config['title'],
                'text' => $config['subtitle_text'] ?? null,
                'type' => $type,
                'seeder' => static::class,
                'page_category_id' => $categoryId,
            ]
        );

        TextBlock::where('page_id', $page->id)
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $pageAnchorTags = $config['tags'] ?? [];

        if ($categorySlug && ! in_array($categorySlug, $pageAnchorTags, true)) {
            $pageAnchorTags[] = $categorySlug;
        }

        $baseTags = $config['base_tags'] ?? $pageAnchorTags;
        $baseTagIds = $this->resolveTagIds($baseTags);

        $aggregatedBlockTagIds = [];
        $blockIndex = 0;
        $createdTextBlocks = [];

        if (! empty($config['subtitle_html'])) {
            $subtitleUuid = $config['subtitle_uuid']
                ?? TextBlockUuidGenerator::generateWithKey(static::class, 'subtitle');

            $subtitleConfig = [
                'tags' => $config['subtitle_tags'] ?? [],
                'inherit_base_tags' => $config['subtitle_inherit_base_tags'] ?? true,
            ];

            $textBlock = TextBlock::create([
                'uuid' => $subtitleUuid,
                'page_id' => $page->id,
                'page_category_id' => $categoryId,
                'locale' => $config['locale'] ?? 'uk',
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $config['subtitle_html'],
                'level' => $config['subtitle_level'] ?? null,
                'seeder' => static::class,
            ]);

            $createdTextBlocks[] = ['block' => $textBlock, 'config' => $subtitleConfig];
            $blockIndex++;
        }

        foreach ($config['blocks'] ?? [] as $index => $block) {
            $uuid = $block['uuid']
                ?? (isset($block['uuid_key'])
                    ? TextBlockUuidGenerator::generateWithKey(static::class, $block['uuid_key'])
                    : TextBlockUuidGenerator::generate(static::class, $blockIndex));

            $textBlock = TextBlock::create([
                'uuid' => $uuid,
                'page_id' => $page->id,
                'page_category_id' => $categoryId,
                'locale' => $config['locale'] ?? 'uk',
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'],
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'level' => $block['level'] ?? null,
                'seeder' => static::class,
            ]);

            $createdTextBlocks[] = ['block' => $textBlock, 'config' => $block];
            $blockIndex++;
        }

        foreach ($createdTextBlocks as $item) {
            $textBlock = $item['block'];
            $blockConfig = $item['config'];

            $inheritBaseTags = $blockConfig['inherit_base_tags']
                ?? $blockConfig['inherit_tags']
                ?? true;

            $blockTagIds = [];

            if ($inheritBaseTags) {
                $blockTagIds = $baseTagIds;
            }

            if (! empty($blockConfig['tags'])) {
                $blockSpecificTagIds = $this->resolveTagIds($blockConfig['tags']);
                $blockTagIds = array_unique(array_merge($blockTagIds, $blockSpecificTagIds));
            }

            $textBlock->tags()->sync($blockTagIds);
            $aggregatedBlockTagIds = array_unique(array_merge($aggregatedBlockTagIds, $blockTagIds));
        }

        $pageAnchorTagIds = $this->resolveTagIds($pageAnchorTags);
        $pageTagIds = array_unique(array_merge($pageAnchorTagIds, $aggregatedBlockTagIds));
        $page->tags()->sync($pageTagIds);
    }

    /**
     * Resolve tag names to IDs with caching to avoid N+1 queries.
     *
     * @param  array<int, string>  $tagNames
     * @return array<int, int>
     */
    protected function resolveTagIds(array $tagNames): array
    {
        if (empty($tagNames)) {
            return [];
        }

        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (! is_string($tagName) || trim($tagName) === '') {
                continue;
            }

            $normalizedName = trim($tagName);
            $cacheKey = mb_strtolower($normalizedName);

            if (! isset($this->tagCache[$cacheKey])) {
                $tag = Tag::firstOrCreate(['name' => $normalizedName]);
                $this->tagCache[$cacheKey] = $tag->id;
            }

            $tagIds[] = $this->tagCache[$cacheKey];
        }

        return array_unique($tagIds);
    }
}
