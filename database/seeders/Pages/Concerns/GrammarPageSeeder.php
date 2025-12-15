<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Database\Seeders\Pages\GrammarPagesSeeder;

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
        return [static::class, GrammarPagesSeeder::class];
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

        // BLOCK-FIRST TAGGING: Resolve page anchor tags and base_tags
        $pageAnchorTags = $config['tags'] ?? [];

        // Add category slug as identifier tag if not already present
        if ($categorySlug && ! in_array($categorySlug, $pageAnchorTags, true)) {
            $pageAnchorTags[] = $categorySlug;
        }

        // Determine base_tags for block inheritance
        // Backward compatibility: if base_tags not defined, use page.tags as base_tags
        $baseTags = $config['base_tags'] ?? $pageAnchorTags;
        $baseTagIds = $this->resolveTagIds($baseTags);

        // Collect all block tag IDs for page aggregation
        $aggregatedBlockTagIds = [];

        // Block index counter for UUID generation (starts from 0)
        $blockIndex = 0;
        $createdTextBlocks = [];

        // Handle subtitle block with BLOCK-FIRST tagging support
        if (! empty($config['subtitle_html'])) {
            $subtitleUuid = $config['subtitle_uuid']
                ?? TextBlockUuidGenerator::generateWithKey(static::class, 'subtitle');

            // Subtitle config from page level or empty
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

        // BLOCK-FIRST TAGGING: Sync tags to each TextBlock and aggregate for page
        foreach ($createdTextBlocks as $item) {
            $textBlock = $item['block'];
            $blockConfig = $item['config'];

            // Determine if this block inherits base_tags
            // Support both 'inherit_base_tags' (new) and 'inherit_tags' (legacy) keys
            $inheritBaseTags = $blockConfig['inherit_base_tags']
                ?? $blockConfig['inherit_tags']
                ?? true;

            // Calculate block tag IDs
            $blockTagIds = [];

            if ($inheritBaseTags) {
                // Start with base_tags (controlled inheritance)
                $blockTagIds = $baseTagIds;
            }

            // Add block-specific tags if defined
            if (! empty($blockConfig['tags'])) {
                $blockSpecificTagIds = $this->resolveTagIds($blockConfig['tags']);
                $blockTagIds = array_unique(array_merge($blockTagIds, $blockSpecificTagIds));
            }

            // Sync tags to block
            if (! empty($blockTagIds)) {
                $textBlock->tags()->sync($blockTagIds);
            }

            // Aggregate block tags for page (BLOCK-FIRST: page = union of all blocks)
            $aggregatedBlockTagIds = array_unique(array_merge($aggregatedBlockTagIds, $blockTagIds));
        }

        // BLOCK-FIRST TAGGING: Page tags = union(all block tags) + page anchor tags
        $pageAnchorTagIds = $this->resolveTagIds($pageAnchorTags);
        $finalPageTagIds = array_unique(array_merge($aggregatedBlockTagIds, $pageAnchorTagIds));

        if (! empty($finalPageTagIds)) {
            $page->tags()->sync($finalPageTagIds);
        }
    }

    /**
     * Resolve tag names to tag IDs with caching and normalization.
     *
     * @param  array<string>  $tagNames
     * @return array<int>
     */
    protected function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            // Normalize tag name: trim whitespace and collapse multiple spaces
            $normalizedName = preg_replace('/\s+/', ' ', trim($tagName));

            if (empty($normalizedName)) {
                continue;
            }

            if (isset($this->tagCache[$normalizedName])) {
                $tagIds[] = $this->tagCache[$normalizedName];
            } else {
                $tag = Tag::firstOrCreate(['name' => $normalizedName]);
                $this->tagCache[$normalizedName] = $tag->id;
                $tagIds[] = $tag->id;
            }
        }

        return $tagIds;
    }
}
