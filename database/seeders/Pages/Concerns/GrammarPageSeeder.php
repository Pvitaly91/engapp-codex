<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Database\Seeders\Pages\GrammarPagesSeeder;

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

        // Resolve page-level tags (including category slug as anchor tag)
        $pageTags = $config['tags'] ?? [];
        if ($categorySlug && ! in_array($categorySlug, $pageTags, true)) {
            $pageTags[] = $categorySlug;
        }
        $pageTagIds = $this->resolveTagIds($pageTags);

        // Block index counter for UUID generation (starts from 0)
        $blockIndex = 0;
        $createdTextBlocks = [];

        if (! empty($config['subtitle_html'])) {
            // Generate UUID for subtitle block - use key 'subtitle' for clarity
            $subtitleUuid = $config['subtitle_uuid']
                ?? TextBlockUuidGenerator::generateWithKey(static::class, 'subtitle');

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

            // Subtitle block inherits page tags by default
            $createdTextBlocks[] = ['block' => $textBlock, 'config' => []];
            $blockIndex++;
        }

        foreach ($config['blocks'] ?? [] as $index => $block) {
            // Generate UUID for block:
            // 1. Use explicit 'uuid' from block if provided
            // 2. Use 'uuid_key' to generate deterministic UUID with custom key
            // 3. Fall back to index-based UUID generation
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

        // Attach tags to page
        if (! empty($pageTagIds)) {
            $page->tags()->sync($pageTagIds);
        }

        // Sync tags to each TextBlock
        foreach ($createdTextBlocks as $item) {
            $textBlock = $item['block'];
            $blockConfig = $item['config'];

            // Check if tag inheritance is disabled for this block
            $inheritTags = $blockConfig['inherit_tags'] ?? true;

            if ($inheritTags) {
                // Start with page tags
                $blockTagIds = $pageTagIds;

                // Add block-specific tags if defined
                if (! empty($blockConfig['tags'])) {
                    $blockSpecificTagIds = $this->resolveTagIds($blockConfig['tags']);
                    $blockTagIds = array_unique(array_merge($blockTagIds, $blockSpecificTagIds));
                }
            } else {
                // Only use block-specific tags (no inheritance)
                $blockTagIds = ! empty($blockConfig['tags'])
                    ? $this->resolveTagIds($blockConfig['tags'])
                    : [];
            }

            if (! empty($blockTagIds)) {
                $textBlock->tags()->sync($blockTagIds);
            }
        }
    }

    /**
     * Resolve tag names to tag IDs with caching.
     *
     * @param  array<string>  $tagNames
     * @return array<int>
     */
    protected function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (isset($this->tagCache[$tagName])) {
                $tagIds[] = $this->tagCache[$tagName];
            } else {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $this->tagCache[$tagName] = $tag->id;
                $tagIds[] = $tag->id;
            }
        }

        return $tagIds;
    }
}
