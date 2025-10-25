<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use Database\Seeders\Pages\GrammarPagesSeeder;

abstract class GrammarPageSeeder extends Seeder
{
    abstract protected function slug(): string;

    abstract protected function page(): array;

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

        $categoryConfig = $config['category'] ?? $this->category();
        $categoryId = null;

        if ($categoryConfig && ! empty($categoryConfig['slug'])) {
            $language = $categoryConfig['language']
                ?? $categoryConfig['locale']
                ?? ($config['locale'] ?? 'uk');

            $category = PageCategory::updateOrCreate(
                ['slug' => $categoryConfig['slug']],
                [
                    'title' => $categoryConfig['title'] ?? $categoryConfig['slug'],
                    'language' => $language,
                ]
            );

            $categoryId = $category->id;
        }

        $page = Page::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $config['title'],
                'text' => $config['subtitle_text'] ?? null,
                'seeder' => static::class,
                'page_category_id' => $categoryId,
            ]
        );

        TextBlock::where('page_id', $page->id)
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();
 
        if (!empty($config['subtitle_html'])) {
            TextBlock::create([
                'page_id' => $page->id,
                'locale' => $config['locale'] ?? 'uk',
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $config['subtitle_html'],
                'seeder' => static::class,
            ]);
        }

        foreach ($config['blocks'] ?? [] as $index => $block) {
            TextBlock::create([
                'page_id' => $page->id,
                'locale' => $config['locale'] ?? 'uk',
                'type' => 'box',
                'column' => $block['column'],
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'seeder' => static::class,
            ]);
        }
    }
}
