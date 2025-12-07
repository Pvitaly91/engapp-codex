<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

abstract class PageCategoryDescriptionSeeder extends Seeder
{
    abstract protected function slug(): string;

    abstract protected function description(): array;

    protected function category(): array
    {
        return [];
    }

    protected function cleanupSeederClasses(): array
    {
        return [static::class];
    }

    public function previewCategorySlug(): string
    {
        return $this->slug();
    }

    public function run(): void
    {
        $slug = $this->slug();
        $description = $this->description();
      

        $categoryConfig = [
            'slug' => $slug,
            'title' => $description['title'] ?? $slug,
            'language' => $description['locale'] ?? 'uk',
        ];

        $language = $categoryConfig['language']
            ?? $categoryConfig['locale']
            ?? $description['locale']
            ?? 'uk';

        $categoryData = $this->category();
       

        $category = PageCategory::updateOrCreate(
            ['slug' => (isset($categoryData['slug'])) ? $categoryData['slug']  : $slug],
            [
                'title' => (isset($categoryData['title'])) ? $categoryData['title']  : $description['title'],
                'language' => (isset($categoryData['language'])) ? $categoryData['language']  :$categoryConfig['language'],
                'seeder' => static::class,
            ]
        );

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $locale = $description['locale'] ?? $language;

        if (! empty($description['subtitle_html'])) {
            TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $description['subtitle_html'],
                'seeder' => static::class,
            ]);
        }

        foreach ($description['blocks'] ?? [] as $index => $block) {
            // Support both legacy 'box' type and new V3-style types
            $blockType = $block['type'] ?? 'box';
            
            TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $block['locale'] ?? $locale,
                'type' => $blockType,
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'seeder' => static::class,
            ]);
        }
    }
}
