<?php

namespace Database\Seeders\Page_V3\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Support\TextBlock\TextBlockUuidGenerator;
use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder as BaseGrammarPageSeeder;

abstract class GrammarPageSeeder extends BaseGrammarPageSeeder
{
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
            $hasCategoryInName = str_contains(class_basename(static::class), 'Category');

            $category = $hasCategoryInName
                ? PageCategory::updateOrCreate(
                    ['slug' => $categoryConfig['slug']],
                    [
                        'title' => $categoryConfig['title'] ?? $categoryConfig['slug'],
                        'language' => $language,
                        'seeder' => static::class,
                    ]
                )
                : PageCategory::firstOrCreate(
                    ['slug' => $categoryConfig['slug']],
                    [
                        'title' => $categoryConfig['title'] ?? $categoryConfig['slug'],
                        'language' => $language,
                        'type' => $categoryConfig['type'] ?? 'theory',
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
}
