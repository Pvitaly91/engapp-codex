<?php

namespace Database\Seeders\Pages\Concerns;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use Database\Seeders\Pages\GrammarPagesSeeder;

abstract class GrammarPageSeeder extends Seeder
{
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

        if ($categoryConfig && ! empty($categoryConfig['slug'])) {
            $language = $categoryConfig['language']
                ?? $categoryConfig['locale']
                ?? ($config['locale'] ?? 'uk');

            $category = PageCategory::updateOrCreate(
                ['slug' => $categoryConfig['slug']],
                [
                    'title' => $categoryConfig['title'] ?? $categoryConfig['slug'],
                    'language' => $language,
                    'seeder' => static::class,
                ]
            );

            $categoryId = $category->id;
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

        if (! empty($config['subtitle_html'])) {
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
                'type' => $block['type'] ?? 'box',
                'column' => $block['column'],
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'seeder' => static::class,
            ]);
        }

        // Attach tags if defined
        if (! empty($config['tags'])) {
            $tagIds = [];
            foreach ($config['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $page->tags()->sync($tagIds);
        }
    }
}
