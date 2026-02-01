<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceV2InfinitiveGerundCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2-infinitive-gerund';
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
            'title' => 'Інфінітив та герундій у пасиві — Passive Infinitive and Gerund',
            'subtitle_html' => '<p><strong>Інфінітив та герундій у пасиві (Passive Infinitive and Gerund)</strong> — це вивчення пасивних форм інфінітива (to be done, to have been done) та герундія (being done, having been done) у англійській мові.</p>',
            'subtitle_text' => 'Пасивні форми інфінітива та герундія: to be done, to have been done, being done, having been done.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Infinitive',
                'Інфінітив',
                'Gerund',
                'Герундій',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1–B2',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш <strong>пасивні форми інфінітива та герундія</strong>: як їх утворювати та використовувати у реченнях.',
                        'rules' => [
                            [
                                'label' => 'Пасивний інфінітив',
                                'color' => 'emerald',
                                'text' => '<strong>to be + V3</strong> або <strong>to have been + V3</strong>:',
                                'example' => 'The problem needs to be solved.',
                            ],
                            [
                                'label' => 'Пасивний герундій',
                                'color' => 'blue',
                                'text' => '<strong>being + V3</strong> або <strong>having been + V3</strong>:',
                                'example' => 'He enjoys being praised.',
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'rose',
                                'text' => 'Коли підмет <strong>отримує дію</strong>, а не виконує її:',
                                'example' => 'She wants to be invited (not: to invite).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Simple Passive Infinitive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Простий пасивний інфінітив (Simple Passive Infinitive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>to be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The problem needs to be solved.', 'ua' => 'Проблему потрібно вирішити.'],
                                    ['en' => 'She wants to be invited to the party.', 'ua' => 'Вона хоче бути запрошеною на вечірку.'],
                                    ['en' => 'The report has to be finished by Friday.', 'ua' => 'Звіт має бути завершений до п\'ятниці.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Після дієслів, які <strong>вимагають інфінітив</strong>: need, want, expect, seem, appear, etc.',
                                'examples' => [
                                    ['en' => 'I expect to be promoted next year.', 'ua' => 'Я очікую, що мене підвищать наступного року.'],
                                    ['en' => 'The car needs to be repaired.', 'ua' => 'Машину потрібно відремонтувати.'],
                                    ['en' => 'He seems to be liked by everyone.', 'ua' => 'Здається, він подобається всім.'],
                                ],
                            ],
                            [
                                'label' => 'Після модальних',
                                'color' => 'blue',
                                'description' => 'З модальними дієсловами: <strong>modal + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The task must be done today.', 'ua' => 'Завдання має бути виконано сьогодні.'],
                                    ['en' => 'This can be fixed easily.', 'ua' => 'Це можна легко виправити.'],
                                    ['en' => 'The meeting should be postponed.', 'ua' => 'Зустріч слід відкласти.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Perfect Passive Infinitive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Доконаний пасивний інфінітив (Perfect Passive Infinitive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>to have been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The report seems to have been lost.', 'ua' => 'Звіт, схоже, загубили.'],
                                    ['en' => 'She claims to have been invited.', 'ua' => 'Вона стверджує, що її запросили.'],
                                    ['en' => 'The work appears to have been completed.', 'ua' => 'Робота, здається, завершена.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>відбулися до моменту мовлення</strong> або раніше.',
                                'examples' => [
                                    ['en' => 'He is lucky to have been chosen.', 'ua' => 'Йому пощастило, що його обрали.'],
                                    ['en' => 'The building seems to have been damaged.', 'ua' => 'Будівля, схоже, пошкоджена.'],
                                    ['en' => 'They are believed to have been warned.', 'ua' => 'Вважається, що їх попередили.'],
                                ],
                            ],
                            [
                                'label' => 'Після модальних',
                                'color' => 'rose',
                                'description' => 'З модальними дієсловами: <strong>modal + have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The work should have been finished yesterday.', 'ua' => 'Робота мала бути завершена вчора.'],
                                    ['en' => 'It might have been stolen.', 'ua' => 'Його, можливо, вкрали.'],
                                    ['en' => 'The email must have been sent already.', 'ua' => 'Листа, напевно, вже надіслали.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Simple Passive Gerund
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Простий пасивний герундій (Simple Passive Gerund)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'He enjoys being praised by his boss.', 'ua' => 'Йому подобається, коли його хвалить його бос.'],
                                    ['en' => 'I don\'t like being told what to do.', 'ua' => 'Мені не подобається, коли мені кажуть, що робити.'],
                                    ['en' => 'She remembered being asked about it.', 'ua' => 'Вона пам\'ятала, як її питали про це.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Після дієслів, які <strong>вимагають герундій</strong>: enjoy, avoid, mind, remember, etc.',
                                'examples' => [
                                    ['en' => 'I hate being interrupted during meetings.', 'ua' => 'Я ненавиджу, коли мене перебивають під час зустрічей.'],
                                    ['en' => 'He avoided being seen by his ex.', 'ua' => 'Він уникав того, щоб його побачила його колишня.'],
                                    ['en' => 'She minds being criticized in public.', 'ua' => 'Їй неприємно, коли її критикують публічно.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'blue',
                                'description' => 'Після прийменників використовуємо <strong>being + V3</strong>',
                                'examples' => [
                                    ['en' => 'I\'m tired of being ignored.', 'ua' => 'Я втомився від того, що мене ігнорують.'],
                                    ['en' => 'She insisted on being heard.', 'ua' => 'Вона наполягала на тому, щоб її вислухали.'],
                                    ['en' => 'He is afraid of being punished.', 'ua' => 'Він боїться бути покараним.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Perfect Passive Gerund
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Доконаний пасивний герундій (Perfect Passive Gerund)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>having been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'After having been warned, he left immediately.', 'ua' => 'Після того, як його попередили, він негайно пішов.'],
                                    ['en' => 'She regrets having been misinformed.', 'ua' => 'Вона шкодує, що її дезінформували.'],
                                    ['en' => 'He denies having been involved.', 'ua' => 'Він заперечує, що був залучений.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>відбулися раніше</strong> за головну дію.',
                                'examples' => [
                                    ['en' => 'I remember having been told about it before.', 'ua' => 'Я пам\'ятаю, що мені про це казали раніше.'],
                                    ['en' => 'After having been trained, they started working.', 'ua' => 'Після того, як їх навчили, вони почали працювати.'],
                                    ['en' => 'He is proud of having been chosen.', 'ua' => 'Він пишається тим, що його обрали.'],
                                ],
                            ],
                            [
                                'label' => 'Perfect vs Simple',
                                'color' => 'rose',
                                'description' => '<strong>Perfect</strong> — дія відбулася раніше. <strong>Simple</strong> — одночасно.',
                                'examples' => [
                                    ['en' => 'I remember being asked (тоді). → I remember having been asked (раніше).', 'ua' => 'Я пам\'ятаю, як мене питали (тоді) vs раніше.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Порівняння Active vs Passive Infinitive/Gerund',
                        'intro' => 'Коли використовувати активну або пасивну форму:',
                        'rows' => [
                            [
                                'en' => 'Active: I want to invite her.',
                                'ua' => 'Активна: Я хочу запросити її.',
                                'note' => '→ Passive: I want to be invited. (Мене запрошують)',
                            ],
                            [
                                'en' => 'Active: He enjoys praising others.',
                                'ua' => 'Активна: Йому подобається хвалити інших.',
                                'note' => '→ Passive: He enjoys being praised. (Його хвалять)',
                            ],
                            [
                                'en' => 'Active: She needs to finish the report.',
                                'ua' => 'Активна: Їй потрібно завершити звіт.',
                                'note' => '→ Passive: The report needs to be finished. (Звіт треба завершити)',
                            ],
                            [
                                'en' => 'Active: I remember asking him.',
                                'ua' => 'Активна: Я пам\'ятаю, як питав його.',
                                'note' => '→ Passive: I remember being asked. (Мене питали)',
                            ],
                        ],
                        'warning' => '📌 Вибір між Active і Passive залежить від того, хто виконує дію.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Common verbs with passive infinitive/gerund
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Дієслова з пасивним інфінітивом та герундієм',
                        'intro' => 'Найпоширеніші дієслова, які можуть використовуватись з пасивними формами:',
                        'items' => [
                            [
                                'label' => 'З інфінітивом',
                                'title' => 'want, need, expect, seem',
                                'subtitle' => 'She wants to be invited. He needs to be helped.',
                            ],
                            [
                                'label' => 'З герундієм',
                                'title' => 'enjoy, avoid, mind, remember',
                                'subtitle' => 'I enjoy being praised. She avoided being seen.',
                            ],
                            [
                                'label' => 'Після модальних',
                                'title' => 'must, should, can, may',
                                'subtitle' => 'It must be done. This can be fixed.',
                            ],
                            [
                                'label' => 'Після прийменників',
                                'title' => 'of, about, for, from',
                                'subtitle' => 'I\'m tired of being ignored. He insisted on being heard.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Examples with different structures
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Приклади різних структур',
                        'sections' => [
                            [
                                'label' => 'З дієсловами почуттів',
                                'color' => 'emerald',
                                'description' => 'like, love, hate, enjoy + <strong>being + V3</strong>',
                                'examples' => [
                                    ['en' => 'I love being surprised.', 'ua' => 'Мені подобаються сюрпризи.'],
                                    ['en' => 'She hates being criticized.', 'ua' => 'Вона ненавидить, коли її критикують.'],
                                    ['en' => 'They enjoy being treated well.', 'ua' => 'Їм подобається, коли з ними добре поводяться.'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловами намірів',
                                'color' => 'blue',
                                'description' => 'want, expect, hope + <strong>to be + V3</strong>',
                                'examples' => [
                                    ['en' => 'I expect to be promoted soon.', 'ua' => 'Я очікую, що мене незабаром підвищать.'],
                                    ['en' => 'She hopes to be selected.', 'ua' => 'Вона сподівається, що її оберуть.'],
                                    ['en' => 'We want to be informed.', 'ua' => 'Ми хочемо бути поінформованими.'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловами сприйняття',
                                'color' => 'rose',
                                'description' => 'seem, appear + <strong>to be/have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'He seems to be liked by everyone.', 'ua' => 'Здається, він подобається всім.'],
                                    ['en' => 'The house appears to have been abandoned.', 'ua' => 'Будинок, здається, покинутий.'],
                                    ['en' => 'She seems to be trusted.', 'ua' => 'Здається, їй довіряють.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Common mistakes
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання активу замість пасиву.',
                                'wrong' => '❌ I want to invite (коли мене запрошують).',
                                'right' => '✅ I want to be invited.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск be в інфінітиві.',
                                'wrong' => '❌ The problem needs solved.',
                                'right' => '✅ The problem needs to be solved.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'orange',
                                'title' => 'Пропуск being у герундії.',
                                'wrong' => '❌ He enjoys praised.',
                                'right' => '✅ He enjoys being praised.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'sky',
                                'title' => 'Неправильна структура Perfect.',
                                'wrong' => '❌ She seems to been chosen.',
                                'right' => '✅ She seems to have been chosen.',
                            ],
                            [
                                'label' => 'Помилка 5',
                                'color' => 'violet',
                                'title' => 'Неправильна структура після модальних.',
                                'wrong' => '❌ It must to be done.',
                                'right' => '✅ It must be done.',
                            ],
                            [
                                'label' => 'Помилка 6',
                                'color' => 'blue',
                                'title' => 'Використання to після герундія.',
                                'wrong' => '❌ I avoid to be seen.',
                                'right' => '✅ I avoid being seen.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Простий пасивний інфінітив:</strong> to be + V3 (to be done). (B1)',
                            '<strong>Доконаний пасивний інфінітив:</strong> to have been + V3 (to have been done). (B2)',
                            '<strong>Простий пасивний герундій:</strong> being + V3 (being done). (B1)',
                            '<strong>Доконаний пасивний герундій:</strong> having been + V3 (having been done). (B2)',
                            'З <strong>модальними</strong> дієсловами: modal + be + V3 (без to). (B1)',
                            'Після дієслів, що вимагають <strong>інфінітив</strong>: want, need, expect, seem. (B1)',
                            'Після дієслів, що вимагають <strong>герундій</strong>: enjoy, avoid, mind, remember. (B1)',
                            'Після <strong>прийменників</strong> використовуємо герундій: being + V3. (B1)',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
