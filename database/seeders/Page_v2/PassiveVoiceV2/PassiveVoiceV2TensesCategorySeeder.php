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
            'subtitle_html' => '<p><strong>Пасив у різних часах (Passive Voice in Different Tenses)</strong> — огляд пасивного стану у всіх основних часах англійської мови: Present Simple, Present Continuous, Present Perfect, Past Simple, Past Continuous, Past Perfect, Future Simple, Future Continuous, Future Perfect.</p>',
            'subtitle_text' => 'Пасив у 9 основних часах: Present/Past/Future Simple, Continuous, Perfect. Структура, приклади та типові помилки.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Tenses',
                'Present Simple Passive',
                'Past Simple Passive',
                'Future Simple Passive',
                'Present Continuous Passive',
                'Past Continuous Passive',
                'Present Perfect Passive',
                'Past Perfect Passive',
                'Future Perfect Passive',
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
                        'intro' => 'У цій темі ти вивчиш <strong>пасивний стан у всіх основних часах</strong> англійської мови: від простих (Simple) до завершених (Perfect) та тривалих (Continuous).',
                        'rules' => [
                            [
                                'label' => 'Simple',
                                'color' => 'emerald',
                                'text' => '<strong>am/is/are/was/were/will be + V3</strong>:',
                                'example' => 'The letter is written. / The letter was written.',
                            ],
                            [
                                'label' => 'Continuous',
                                'color' => 'blue',
                                'text' => '<strong>am/is/are/was/were + being + V3</strong>:',
                                'example' => 'The house is being painted.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'rose',
                                'text' => '<strong>has/have/had/will have + been + V3</strong>:',
                                'example' => 'The work has been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Simple Passive - A2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Теперішній простий пасив (Present Simple Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійською розмовляють у багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every day.', 'ua' => 'Кімнати прибираються щодня.'],
                                    ['en' => 'I am paid monthly.', 'ua' => 'Мені платять щомісяця.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>am/is/are + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'These cars are not made in Japan.', 'ua' => 'Ці машини не виготовляються в Японії.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Am/Is/Are + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the letters sent daily?', 'ua' => 'Листи надсилаються щодня?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Continuous Passive - B1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Теперішній тривалий пасив (Present Continuous Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted right now.', 'ua' => 'Будинок фарбується прямо зараз.'],
                                    ['en' => 'The documents are being prepared.', 'ua' => 'Документи готуються.'],
                                    ['en' => 'I am being interviewed for a new position.', 'ua' => 'Мене інтерв\'юють на нову посаду.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>am/is/are + not + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The house is not being painted today.', 'ua' => 'Будинок сьогодні не фарбується.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Am/Is/Are + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is the project being worked on?', 'ua' => 'Над проєктом працюють?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Perfect Passive - B1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Теперішній доконаний пасив (Present Perfect Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>has/have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The project has been completed successfully.', 'ua' => 'Проєкт успішно завершено.'],
                                    ['en' => 'All tickets have been sold.', 'ua' => 'Усі квитки продано.'],
                                    ['en' => 'I have been promoted to manager!', 'ua' => 'Мене підвищили до менеджера!'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>has/have + not + been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email has not been sent yet.', 'ua' => 'Електронний лист ще не надіслано.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Has/Have + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Has the report been submitted?', 'ua' => 'Звіт був поданий?'],
                                    ['en' => 'Have you ever been interviewed?', 'ua' => 'Тебе коли-небудь інтерв\'ювали?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Simple Passive - A2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Минулий простий пасив (Past Simple Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The thieves were caught yesterday.', 'ua' => 'Злодіїв зловили вчора.'],
                                    ['en' => 'I was invited to the party.', 'ua' => 'Мене запросили на вечірку.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>was/were + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email was not sent.', 'ua' => 'Електронний лист не був надісланий.'],
                                    ['en' => 'We were not informed about it.', 'ua' => 'Нас не повідомили про це.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                    ['en' => 'Were the documents signed?', 'ua' => 'Документи були підписані?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Continuous Passive - B1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Минулий тривалий пасив (Past Continuous Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The car was being repaired when I arrived.', 'ua' => 'Машину ремонтували, коли я прийшов.'],
                                    ['en' => 'The rooms were being cleaned all morning.', 'ua' => 'Кімнати прибиралися весь ранок.'],
                                    ['en' => 'I was being examined by the doctor.', 'ua' => 'Мене оглядав лікар.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>was/were + not + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The issue was not being discussed then.', 'ua' => 'Питання не обговорювалося тоді.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Were the emails being sent?', 'ua' => 'Електронні листи надсилалися?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Perfect Passive - B1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Минулий доконаний пасив (Past Perfect Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>had + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work had been finished before the deadline.', 'ua' => 'Роботу було закінчено до дедлайну.'],
                                    ['en' => 'The tickets had been sold out before we arrived.', 'ua' => 'Квитки були розпродані до нашого приїзду.'],
                                    ['en' => 'The documents had been signed earlier.', 'ua' => 'Документи були підписані раніше.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>had + not + been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The work had not been completed by then.', 'ua' => 'Робота не була завершена до того часу.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Had + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Had the letter been sent?', 'ua' => 'Лист був надісланий?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Simple Passive - A2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Майбутній простий пасив (Future Simple Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                    ['en' => 'The meeting will be held next Monday.', 'ua' => 'Зустріч відбудеться наступного понеділка.'],
                                    ['en' => 'You will be contacted soon.', 'ua' => 'З вами скоро зв\'яжуться.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>will not (won\'t) + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершено вчасно.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the report be ready by Friday?', 'ua' => 'Звіт буде готовий до п\'ятниці?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Continuous Passive - B2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Майбутній тривалий пасив (Future Continuous Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + being + Past Participle (V3)</strong>. <em>Рідко використовується!</em>',
                                'examples' => [
                                    ['en' => 'The house will be being painted at this time tomorrow.', 'ua' => 'Будинок буде фарбуватися в цей час завтра.'],
                                    ['en' => 'The documents will be being reviewed all day.', 'ua' => 'Документи переглядатимуться весь день.'],
                                ],
                            ],
                            [
                                'label' => 'Примітка',
                                'color' => 'amber',
                                'description' => 'Ця форма <strong>використовується дуже рідко</strong> через незручну конструкцію.',
                                'examples' => [
                                    ['en' => 'Better: The house will be painted tomorrow.', 'ua' => 'Краще використовувати Future Simple Passive.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Perfect Passive - B2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Майбутній доконаний пасив (Future Perfect Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work will have been completed by next week.', 'ua' => 'Робота буде завершена до наступного тижня.'],
                                    ['en' => 'By tomorrow, the report will have been submitted.', 'ua' => 'До завтра звіт буде поданий.'],
                                    ['en' => 'By 6 PM, all emails will have been sent.', 'ua' => 'До 18:00 усі листи будуть надіслані.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>will not have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The project will not have been finished by then.', 'ua' => 'Проєкт не буде завершений до того часу.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Will + S + have been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the work have been done by Friday?', 'ua' => 'Робота буде зроблена до п\'ятниці?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - A2-B1
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '10. Зведена таблиця всіх часів у пасиві',
                        'intro' => 'Структури пасивного стану в усіх основних часах:',
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
                                'ua' => 'will + be + V3',
                                'note' => 'The letter will be written.',
                            ],
                            [
                                'en' => 'Future Continuous',
                                'ua' => 'will + be + being + V3 (рідко)',
                                'note' => 'The letter will be being written.',
                            ],
                            [
                                'en' => 'Future Perfect',
                                'ua' => 'will have + been + V3',
                                'note' => 'The letter will have been written.',
                            ],
                        ],
                        'warning' => '📌 Perfect Continuous часи (has been being done) <strong>не мають пасивної форми</strong>!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - A2
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '11. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => 'The letter written yesterday.',
                                'right' => '✅ The letter was written yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна форма be.',
                                'wrong' => 'The letters was sent.',
                                'right' => '✅ The letters were sent.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутанина з being у Continuous.',
                                'wrong' => 'The house is painted now.',
                                'right' => '✅ The house is being painted now.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Пропуск been у Perfect.',
                                'wrong' => 'The work has completed.',
                                'right' => '✅ The work has been completed.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list - A2
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '12. Короткий конспект',
                        'items' => [
                            '<strong>Simple Passive</strong>: be (у потрібному часі) + V3.',
                            '<strong>Continuous Passive</strong>: be + being + V3.',
                            '<strong>Perfect Passive</strong>: have/had + been + V3.',
                            '<strong>Future Passive</strong>: will be + V3 / will have been + V3.',
                            'Компонент <strong>be</strong> змінюється за часом, а <strong>V3</strong> залишається незмінним.',
                            '<strong>Perfect Continuous</strong> часи НЕ мають пасивної форми.',
                            'Future Continuous Passive використовується <strong>дуже рідко</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
