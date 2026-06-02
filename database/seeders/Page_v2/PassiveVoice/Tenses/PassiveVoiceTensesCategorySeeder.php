<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceTensesCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-tenses';
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
        $parentCategory = PageCategory::where('slug', 'passive-voice')->first();

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
            'subtitle_html' => '<p><strong>Пасив у різних часах (Passive Voice in Different Tenses)</strong> — це повний огляд пасивного стану у всіх основних часах англійської мови. Дієслово <strong>be</strong> змінюється відповідно до часу, а <strong>Past Participle (V3)</strong> залишається незмінним.</p>',
            'subtitle_text' => 'Пасив у 9 основних часах: Present/Past/Future Simple, Continuous, Perfect. Формули, приклади та порівняння.',
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
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цьому розділі ти вивчиш <strong>пасивний стан у всіх основних часах</strong> англійської мови: Present, Past та Future у формах Simple, Continuous та Perfect.',
                        'rules' => [
                            [
                                'label' => 'Принцип',
                                'color' => 'emerald',
                                'text' => 'Базова формула пасиву: <strong>be (у потрібному часі) + V3</strong>',
                                'example' => 'The letter is written. / was written. / will be written.',
                            ],
                            [
                                'label' => 'Continuous',
                                'color' => 'blue',
                                'text' => 'Для тривалих часів додаємо <strong>being</strong>:',
                                'example' => 'The house is being painted. / was being painted.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'rose',
                                'text' => 'Для завершених часів використовуємо <strong>have/had + been</strong>:',
                                'example' => 'The work has been done. / had been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Present tenses
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Present Tenses (Теперішні часи)',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => '<strong>am/is/are + V3</strong> — регулярні дії, факти',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійська розмовляється в багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every day.', 'ua' => 'Кімнати прибираються щодня.'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous',
                                'color' => 'blue',
                                'description' => '<strong>am/is/are + being + V3</strong> — дії зараз',
                                'examples' => [
                                    ['en' => 'The house is being painted now.', 'ua' => 'Будинок фарбується зараз.'],
                                    ['en' => 'The documents are being prepared.', 'ua' => 'Документи готуються.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'rose',
                                'description' => '<strong>has/have + been + V3</strong> — результат',
                                'examples' => [
                                    ['en' => 'The project has been completed.', 'ua' => 'Проєкт завершено.'],
                                    ['en' => 'All tickets have been sold.', 'ua' => 'Усі квитки продано.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Past tenses
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Past Tenses (Минулі часи)',
                        'sections' => [
                            [
                                'label' => 'Past Simple',
                                'color' => 'emerald',
                                'description' => '<strong>was/were + V3</strong> — дії в минулому',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The letters were sent yesterday.', 'ua' => 'Листи надіслали вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'blue',
                                'description' => '<strong>was/were + being + V3</strong> — тривала дія в минулому',
                                'examples' => [
                                    ['en' => 'The car was being repaired when I arrived.', 'ua' => 'Машину ремонтували, коли я прийшов.'],
                                    ['en' => 'The rooms were being cleaned all morning.', 'ua' => 'Кімнати прибиралися весь ранок.'],
                                ],
                            ],
                            [
                                'label' => 'Past Perfect',
                                'color' => 'rose',
                                'description' => '<strong>had + been + V3</strong> — дія до іншої минулої',
                                'examples' => [
                                    ['en' => 'The work had been finished before the deadline.', 'ua' => 'Роботу було закінчено до дедлайну.'],
                                    ['en' => 'The tickets had been sold out before we arrived.', 'ua' => 'Квитки були розпродані до нашого приїзду.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Future tenses
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Future Tenses (Майбутні часи)',
                        'sections' => [
                            [
                                'label' => 'Future Simple',
                                'color' => 'emerald',
                                'description' => '<strong>will be + V3</strong> — дії в майбутньому',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                    ['en' => 'You will be contacted soon.', 'ua' => 'З вами скоро зв\'яжуться.'],
                                ],
                            ],
                            [
                                'label' => 'Future Continuous',
                                'color' => 'blue',
                                'description' => '<strong>will be + being + V3</strong> — рідко використовується',
                                'examples' => [
                                    ['en' => 'The house will be being painted tomorrow.', 'ua' => 'Будинок буде фарбуватися завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Future Perfect',
                                'color' => 'rose',
                                'description' => '<strong>will have + been + V3</strong> — буде завершено до...',
                                'examples' => [
                                    ['en' => 'The work will have been completed by Friday.', 'ua' => 'Робота буде завершена до п\'ятниці.'],
                                    ['en' => 'All emails will have been sent by 6 PM.', 'ua' => 'Усі листи будуть надіслані до 18:00.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Зведена таблиця всіх часів',
                        'intro' => 'Формули пасивного стану в усіх часах:',
                        'rows' => [
                            [
                                'en' => 'Present Simple',
                                'ua' => 'am/is/are + V3',
                                'note' => 'The letter is written.',
                            ],
                            [
                                'en' => 'Present Continuous',
                                'ua' => 'am/is/are + being + V3',
                                'note' => 'The letter is being written.',
                            ],
                            [
                                'en' => 'Present Perfect',
                                'ua' => 'has/have + been + V3',
                                'note' => 'The letter has been written.',
                            ],
                            [
                                'en' => 'Past Simple',
                                'ua' => 'was/were + V3',
                                'note' => 'The letter was written.',
                            ],
                            [
                                'en' => 'Past Continuous',
                                'ua' => 'was/were + being + V3',
                                'note' => 'The letter was being written.',
                            ],
                            [
                                'en' => 'Past Perfect',
                                'ua' => 'had + been + V3',
                                'note' => 'The letter had been written.',
                            ],
                            [
                                'en' => 'Future Simple',
                                'ua' => 'will be + V3',
                                'note' => 'The letter will be written.',
                            ],
                            [
                                'en' => 'Future Perfect',
                                'ua' => 'will have + been + V3',
                                'note' => 'The letter will have been written.',
                            ],
                        ],
                        'warning' => '📌 Future Continuous Passive (will be being + V3) використовується дуже рідко!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Ключові правила',
                        'items' => [
                            'Базова формула: <strong>be (у потрібному часі) + V3</strong>.',
                            'Для Continuous часів додаємо <strong>being</strong> перед V3.',
                            'Для Perfect часів використовуємо <strong>been</strong> перед V3.',
                            'Дієслово <strong>be</strong> змінюється, <strong>V3</strong> — ні.',
                            '<strong>Perfect Continuous Passive</strong> (has been being done) теоретично існує, але практично не використовується.',
                            '<strong>Future Continuous Passive</strong> використовується дуже рідко.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
