<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceV2TensesCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2-tenses';
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

        // Find parent category
        $parentCategory = PageCategory::where('slug', 'passive-voice-v2')->first();

        $category = PageCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $description['title'],
                'language' => $description['locale'],
                'type' => 'theory',
                'parent_id' => $parentCategory?->id,
                'seeder' => static::class,
            ]
        );

        // Sync tags if provided
        if (! empty($description['tags'])) {
            $tagIds = [];
            foreach ($description['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $category->tags()->sync($tagIds);
        }

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $locale = $description['locale'];

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
                'level' => 'A2',
                'seeder' => static::class,
            ]);
        }

        foreach ($description['blocks'] ?? [] as $index => $block) {
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
                'level' => $block['level'] ?? null,
                'seeder' => static::class,
            ]);
        }
    }

    protected function description(): array
    {
        return [
            'title' => 'Пасив у різних часах',
            'subtitle_html' => '<p><strong>Пасив у різних часах</strong> — огляд пасивного стану у всіх основних часах англійської мови. Детальна інформація про кожен час доступна на окремих сторінках.</p>',
            'subtitle_text' => 'Пасив у 9 основних часах: Present/Past/Future Simple, Continuous, Perfect.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Tenses',
                'A2',
                'B1',
                'Theory',
            ],
            'blocks' => [
                // Hero block - коротка інформація
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цьому розділі ти вивчиш <strong>пасивний стан у всіх основних часах</strong> англійської мови.',
                        'rules' => [
                            [
                                'label' => 'Simple',
                                'color' => 'emerald',
                                'text' => '<strong>be + V3</strong>:',
                                'example' => 'is written / was written / will be written',
                            ],
                            [
                                'label' => 'Continuous',
                                'color' => 'blue',
                                'text' => '<strong>be + being + V3</strong>:',
                                'example' => 'is being written / was being written',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'rose',
                                'text' => '<strong>have + been + V3</strong>:',
                                'example' => 'has been written / had been written',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list - огляд часів
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => 'Теми розділу',
                        'items' => [
                            '<strong>Present Simple Passive</strong> — am/is/are + V3',
                            '<strong>Present Continuous Passive</strong> — am/is/are + being + V3',
                            '<strong>Present Perfect Passive</strong> — has/have + been + V3',
                            '<strong>Past Simple Passive</strong> — was/were + V3',
                            '<strong>Past Continuous Passive</strong> — was/were + being + V3',
                            '<strong>Past Perfect Passive</strong> — had + been + V3',
                            '<strong>Future Simple Passive</strong> — will be + V3',
                            '<strong>Future Continuous Passive</strong> — will be + being + V3',
                            '<strong>Future Perfect Passive</strong> — will have + been + V3',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
