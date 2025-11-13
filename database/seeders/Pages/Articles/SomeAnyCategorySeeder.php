<?php

namespace Database\Seeders\Pages\Articles;

use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use Database\Seeders\Pages\Articles\Concerns\SomeAnyContent;

class SomeAnyCategorySeeder extends Seeder
{
    use SomeAnyContent;

    public function run(): void
    {
        $config = $this->someAnyContent();
        $locale = $config['locale'] ?? 'uk';

        $category = PageCategory::updateOrCreate(
            ['slug' => 'some-any'],
            [
                'title' => $config['title'],
                'language' => $locale,
            ]
        );

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->where('seeder', static::class)
            ->delete();

        if (! empty($config['subtitle_html'])) {
            TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
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
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'box',
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
