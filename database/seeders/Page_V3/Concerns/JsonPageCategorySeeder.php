<?php

namespace Database\Seeders\Page_V3\Concerns;

use App\Models\PageCategory;
use App\Models\TextBlock;

abstract class JsonPageCategorySeeder extends PageCategoryDescriptionSeeder
{
    use InteractsWithPageV3Json;

    protected function sourceEntityType(): string
    {
        return 'category';
    }

    protected function slug(): string
    {
        return $this->resolvedSlugFromSource();
    }

    protected function description(): array
    {
        return $this->resolvedCategoryDescriptionFromSource();
    }

    protected function category(): array
    {
        return $this->resolvedCategoryFromSource() ?? [
            'slug' => $this->slug(),
            'title' => $this->slug(),
            'language' => 'uk',
            'type' => 'theory',
        ];
    }

    public function run(): void
    {
        $slug = $this->slug();
        $description = $this->description();
        $categoryData = $this->category();
        $categorySlug = trim((string) ($categoryData['slug'] ?? $slug));
        $locale = $this->normalizeLocale((string) ($description['locale'] ?? $categoryData['language'] ?? 'uk'));

        $category = PageCategory::updateOrCreate(
            ['slug' => $categorySlug],
            [
                'title' => $categoryData['title'] ?? ($description['title'] ?? $categorySlug),
                'language' => $this->normalizeLocale((string) ($categoryData['language'] ?? $locale)),
                'type' => $categoryData['type'] ?? 'theory',
                'parent_id' => $this->resolveParentCategoryId($categoryData),
                'seeder' => static::class,
            ]
        );

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $categoryAnchorTags = $this->normalizeStringList(
            $description['tags']
            ?? $categoryData['tags']
            ?? []
        );
        $baseTags = $this->normalizeStringList($description['base_tags'] ?? $categoryAnchorTags);
        $baseTagIds = $this->resolveTagIds($baseTags);
        $aggregatedBlockTagIds = [];
        $createdBlocks = [];
        $blockIndex = 0;

        if (! empty($description['subtitle_html'])) {
            $subtitleConfig = [
                'uuid' => $description['subtitle_uuid'] ?? null,
                'uuid_key' => $description['subtitle_uuid_key'] ?? 'subtitle',
                'tags' => $description['subtitle_tags'] ?? [],
                'inherit_base_tags' => $description['subtitle_inherit_base_tags'] ?? true,
            ];

            $subtitleBlock = TextBlock::create([
                'uuid' => $this->resolveBlockUuid($subtitleConfig, $blockIndex),
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $description['subtitle_html'],
                'level' => $description['subtitle_level'] ?? null,
                'seeder' => static::class,
            ]);

            $createdBlocks[] = ['block' => $subtitleBlock, 'config' => $subtitleConfig];
            $blockIndex++;
        }

        foreach ($description['blocks'] ?? [] as $index => $block) {
            $textBlock = TextBlock::create([
                'uuid' => $this->resolveBlockUuid($block, $blockIndex),
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
                'seeder' => static::class,
            ]);

            $createdBlocks[] = ['block' => $textBlock, 'config' => $block];
            $blockIndex++;
        }

        foreach ($createdBlocks as $item) {
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

            $textBlock->tags()->sync($blockTagIds);
            $aggregatedBlockTagIds = array_values(array_unique(array_merge($aggregatedBlockTagIds, $blockTagIds)));
        }

        $category->tags()->sync(array_values(array_unique(array_merge(
            $aggregatedBlockTagIds,
            $this->resolveTagIds($categoryAnchorTags)
        ))));
    }
}
