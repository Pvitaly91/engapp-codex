<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

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
                'seeder' => static::class,
            ]);
        }
    }

    protected function description(): array
    {
        return [
            'title' => 'Пасив у різних часах',
            'subtitle_html' => '<p><strong>Пасив у різних часах</strong> показує, як змінюється форма дієслова <em>to be</em> у пасиві та як будуються речення у Simple, Continuous і Perfect часах.</p>',
            'subtitle_text' => 'Пасивний стан у Present, Past, Future та їхніх Continuous і Perfect формах.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасив у різних часах',
                'Present Simple Passive',
                'Past Simple Passive',
                'Future Simple Passive',
                'Continuous Passive',
                'Perfect Passive',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2–B2',
                    'body' => json_encode([
                        'level' => 'A2–B2',
                        'intro' => 'Тут ти побачиш <strong>повну таблицю пасивних форм</strong> у різних часах, з формулами та прикладами.',
                        'rules' => [
                            [
                                'label' => 'Simple',
                                'color' => 'emerald',
                                'text' => 'Simple: <strong>am/is/are</strong>, <strong>was/were</strong>, <strong>will be</strong> + V3.',
                                'example' => 'The email is sent every day.',
                            ],
                            [
                                'label' => 'Continuous',
                                'color' => 'blue',
                                'text' => 'Continuous: <strong>am/is/are being</strong>, <strong>was/were being</strong> + V3.',
                                'example' => 'The bridge is being repaired now.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'rose',
                                'text' => 'Perfect: <strong>has/have been</strong>, <strong>had been</strong>, <strong>will have been</strong> + V3.',
                                'example' => 'The work has been finished.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2–B2',
                    'body' => json_encode([
                        'title' => 'Пасивні форми у всіх часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple Passive',
                                'color' => 'emerald',
                                'description' => 'am/is/are + V3',
                                'examples' => [
                                    ['en' => 'The reports are checked weekly.', 'ua' => 'Звіти перевіряються щотижня.'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous Passive',
                                'color' => 'blue',
                                'description' => 'am/is/are being + V3',
                                'examples' => [
                                    ['en' => 'The documents are being signed now.', 'ua' => 'Документи підписуються зараз.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect Passive',
                                'color' => 'rose',
                                'description' => 'has/have been + V3',
                                'examples' => [
                                    ['en' => 'The room has been cleaned.', 'ua' => 'Кімнату прибрано.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'color' => 'emerald',
                                'description' => 'was/were + V3',
                                'examples' => [
                                    ['en' => 'The contract was signed yesterday.', 'ua' => 'Контракт підписали вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous Passive',
                                'color' => 'blue',
                                'description' => 'was/were being + V3',
                                'examples' => [
                                    ['en' => 'The stage was being built at 5 p.m.', 'ua' => 'Сцену будували о 5-й вечора.'],
                                ],
                            ],
                            [
                                'label' => 'Past Perfect Passive',
                                'color' => 'rose',
                                'description' => 'had been + V3',
                                'examples' => [
                                    ['en' => 'The tickets had been sold before noon.', 'ua' => 'Квитки було продано до полудня.'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple Passive',
                                'color' => 'emerald',
                                'description' => 'will be + V3',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Future Continuous Passive',
                                'color' => 'blue',
                                'description' => 'will be being + V3',
                                'examples' => [
                                    ['en' => 'The system will be being updated at night.', 'ua' => 'Систему оновлюватимуть вночі.'],
                                ],
                            ],
                            [
                                'label' => 'Future Perfect Passive',
                                'color' => 'rose',
                                'description' => 'will have been + V3',
                                'examples' => [
                                    ['en' => 'The project will have been completed by Friday.', 'ua' => 'Проєкт буде завершено до п’ятниці.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2–B2',
                    'body' => json_encode([
                        'title' => 'Поради та типові маркери',
                        'items' => [
                            '<strong>Present Simple:</strong> every day, usually, always.',
                            '<strong>Present Continuous:</strong> now, at the moment, currently.',
                            '<strong>Present Perfect:</strong> already, just, yet, recently.',
                            '<strong>Past Simple:</strong> yesterday, last week, in 2010.',
                            '<strong>Past Continuous:</strong> while, at 5 p.m., when.',
                            '<strong>Past Perfect:</strong> before, by the time.',
                            '<strong>Future Simple:</strong> tomorrow, next week, soon.',
                            '<strong>Future Perfect:</strong> by + time (by Friday, by 2030).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
