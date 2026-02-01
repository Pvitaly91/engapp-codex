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
            'title' => 'Пасив у різних часах — Passive Voice in Different Tenses',
            'subtitle_html' => '<p><strong>Пасив у різних часах (Passive Voice in Different Tenses)</strong> — це детальне вивчення пасивного стану у всіх основних часах англійської мови: Present, Past та Future у формах Simple, Continuous та Perfect.</p>',
            'subtitle_text' => 'Пасивний стан у всіх часах англійської мови: від простих до складних форм (Simple, Continuous, Perfect).',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Tenses',
                'Часи',
                'A2',
                'B1',
                'B2',
                'C1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2–C1',
                    'body' => json_encode([
                        'level' => 'A2–C1',
                        'intro' => 'У цій темі ти детально вивчиш <strong>пасивний стан у всіх часах</strong> англійської мови: від базових Present Simple та Past Simple до складних Future Perfect та Continuous Passive.',
                        'rules' => [
                            [
                                'label' => 'Simple Passive',
                                'color' => 'emerald',
                                'text' => '<strong>am/is/are/was/were/will be + V3</strong>:',
                                'example' => 'The letter is sent daily.',
                            ],
                            [
                                'label' => 'Continuous Passive',
                                'color' => 'blue',
                                'text' => '<strong>am/is/are/was/were + being + V3</strong>:',
                                'example' => 'The house is being painted.',
                            ],
                            [
                                'label' => 'Perfect Passive',
                                'color' => 'rose',
                                'text' => '<strong>has/have/had/will have + been + V3</strong>:',
                                'example' => 'The work has been completed.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Simple Passive
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
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійською говорять у багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every day.', 'ua' => 'Кімнати прибираються щодня.'],
                                    ['en' => 'I am paid monthly.', 'ua' => 'Мені платять щомісяця.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для <strong>регулярних, повторюваних</strong> дій або фактів у пасивному стані.',
                                'examples' => [
                                    ['en' => 'The newspapers are delivered at 7 AM.', 'ua' => 'Газети доставляються о 7 ранку.'],
                                    ['en' => 'Coffee is grown in Brazil.', 'ua' => 'Каву вирощують у Бразилії.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>am/is/are + not + V3</strong>. Q: <strong>Am/Is/Are + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'Are the letters sent daily?', 'ua' => 'Листи надсилаються щодня?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Continuous Passive
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
                                    ['en' => 'The documents are being prepared at the moment.', 'ua' => 'Документи готуються в даний момент.'],
                                    ['en' => 'I am being interviewed for a new job.', 'ua' => 'Мене інтерв\'юють на нову роботу.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>відбуваються зараз</strong>, у процесі виконання.',
                                'examples' => [
                                    ['en' => 'The road is being repaired this week.', 'ua' => 'Дорогу ремонтують цього тижня.'],
                                    ['en' => 'New software is being installed.', 'ua' => 'Нове ПЗ встановлюється.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>am/is/are + not + being + V3</strong>. Q: <strong>Am/Is/Are + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The room is not being cleaned now.', 'ua' => 'Кімнату зараз не прибирають.'],
                                    ['en' => 'Is the project being finished?', 'ua' => 'Проєкт завершується?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Present Perfect Passive
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
                                    ['en' => 'The work has been completed.', 'ua' => 'Роботу завершено.'],
                                    ['en' => 'The tickets have been sold.', 'ua' => 'Квитки продано.'],
                                    ['en' => 'The decision has been made.', 'ua' => 'Рішення прийнято.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, що <strong>завершилися</strong>, але результат важливий зараз.',
                                'examples' => [
                                    ['en' => 'The email has been sent.', 'ua' => 'Листа надіслано (і тепер він у відправлених).'],
                                    ['en' => 'All the files have been checked.', 'ua' => 'Усі файли перевірено (і зараз все готово).'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>has/have + not + been + V3</strong>. Q: <strong>Has/Have + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The report has not been finished yet.', 'ua' => 'Звіт ще не завершено.'],
                                    ['en' => 'Have the documents been signed?', 'ua' => 'Документи підписано?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Simple Passive
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
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок побудований у 1990 році.'],
                                    ['en' => 'They were invited to the party.', 'ua' => 'Їх запросили на вечірку.'],
                                    ['en' => 'The letter was sent yesterday.', 'ua' => 'Листа надіслали вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, що <strong>відбулися у минулому</strong> у пасивному стані.',
                                'examples' => [
                                    ['en' => 'The book was written in 1985.', 'ua' => 'Книгу написали в 1985 році.'],
                                    ['en' => 'The thieves were caught last night.', 'ua' => 'Злодіїв зловили вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>was/were + not + V3</strong>. Q: <strong>Was/Were + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The email was not sent.', 'ua' => 'Листа не надіслали.'],
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Continuous Passive
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
                                    ['en' => 'The road was being repaired when I arrived.', 'ua' => 'Дорогу ремонтували, коли я прибув.'],
                                    ['en' => 'The documents were being prepared yesterday.', 'ua' => 'Документи готувалися вчора.'],
                                    ['en' => 'I was being followed.', 'ua' => 'За мною стежили.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>тривали у певний момент</strong> у минулому.',
                                'examples' => [
                                    ['en' => 'The house was being painted at 3 PM.', 'ua' => 'Будинок фарбували о 15:00.'],
                                    ['en' => 'While the car was being fixed, we waited.', 'ua' => 'Поки машину ремонтували, ми чекали.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>was/were + not + being + V3</strong>. Q: <strong>Was/Were + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The room was not being cleaned then.', 'ua' => 'Кімната тоді не прибиралася.'],
                                    ['en' => 'Were the files being checked?', 'ua' => 'Файли перевірялися?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Past Perfect Passive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Минулий доконаний пасив (Past Perfect Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>had + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The email had been sent before I called.', 'ua' => 'Листа вже надіслали до того, як я подзвонив.'],
                                    ['en' => 'The project had been completed by then.', 'ua' => 'Проєкт було завершено до того часу.'],
                                    ['en' => 'The documents had been signed earlier.', 'ua' => 'Документи підписали раніше.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, що <strong>завершилися до певного моменту</strong> у минулому.',
                                'examples' => [
                                    ['en' => 'The work had been done before the deadline.', 'ua' => 'Роботу виконали до дедлайну.'],
                                    ['en' => 'By 2020, the building had been demolished.', 'ua' => 'До 2020 року будівлю знесли.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>had + not + been + V3</strong>. Q: <strong>Had + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The report had not been reviewed yet.', 'ua' => 'Звіт ще не переглянули.'],
                                    ['en' => 'Had the letter been delivered?', 'ua' => 'Листа доставили?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Simple Passive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Майбутній простий пасив (Future Simple Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The report will be finished tomorrow.', 'ua' => 'Звіт буде завершено завтра.'],
                                    ['en' => 'You will be informed soon.', 'ua' => 'Вас повідомлять незабаром.'],
                                    ['en' => 'The results will be announced next week.', 'ua' => 'Результати оголосять наступного тижня.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>відбудуться у майбутньому</strong> у пасивному стані.',
                                'examples' => [
                                    ['en' => 'The meeting will be held on Friday.', 'ua' => 'Зустріч відбудеться в п\'ятницю.'],
                                    ['en' => 'New offices will be opened next year.', 'ua' => 'Нові офіси відкриють наступного року.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>will + not + be + V3</strong>. Q: <strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be delayed.', 'ua' => 'Проєкт не буде відкладено.'],
                                    ['en' => 'Will the package be delivered today?', 'ua' => 'Посилку доставлять сьогодні?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Continuous Passive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '8. Майбутній тривалий пасив (Future Continuous Passive)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + being + V3</strong> (рідко вживається)',
                                'examples' => [
                                    ['en' => 'The project will be being discussed at 3 PM tomorrow.', 'ua' => 'Проєкт обговорюватимуть завтра о 15:00.'],
                                    ['en' => 'The repairs will be being carried out next week.', 'ua' => 'Ремонт проводитимуть наступного тижня.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'amber',
                                'description' => '⚠️ <strong>Рідко використовується</strong>. Замість цього зазвичай використовують Future Simple Passive.',
                                'examples' => [
                                    ['en' => 'The house will be being painted (рідко) → The house will be painted (краще).', 'ua' => 'Будинок буде фарбуватися → Будинок пофарбують.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Future Perfect Passive
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
                                'description' => 'Формула: <strong>will + have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The task will have been completed by next Monday.', 'ua' => 'Завдання буде завершено до наступного понеділка.'],
                                    ['en' => 'The building will have been demolished by then.', 'ua' => 'Будівлю буде знесено до того часу.'],
                                    ['en' => 'All the documents will have been reviewed by tomorrow.', 'ua' => 'Усі документи буде переглянуто до завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для дій, які <strong>будуть завершені до певного моменту</strong> у майбутньому.',
                                'examples' => [
                                    ['en' => 'By next year, 100 homes will have been built.', 'ua' => 'До наступного року буде побудовано 100 будинків.'],
                                    ['en' => 'The work will have been finished by 5 PM.', 'ua' => 'Роботу буде завершено до 17:00.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'rose',
                                'description' => 'Neg: <strong>will + not + have + been + V3</strong>. Q: <strong>Will + S + have + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The report will not have been completed by Friday.', 'ua' => 'Звіт не буде завершено до п\'ятниці.'],
                                    ['en' => 'Will the project have been finished by then?', 'ua' => 'Проєкт буде завершено до того часу?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '10. Порівняльна таблиця часів у пасиві',
                        'intro' => 'Швидкий огляд усіх форм пасиву в різних часах:',
                        'rows' => [
                            [
                                'en' => 'Present Simple: am/is/are + V3',
                                'ua' => 'Теперішній простий',
                                'note' => 'The letter is sent. (A2)',
                            ],
                            [
                                'en' => 'Present Continuous: am/is/are + being + V3',
                                'ua' => 'Теперішній тривалий',
                                'note' => 'The house is being painted. (B1)',
                            ],
                            [
                                'en' => 'Present Perfect: has/have + been + V3',
                                'ua' => 'Теперішній доконаний',
                                'note' => 'The work has been done. (B1)',
                            ],
                            [
                                'en' => 'Past Simple: was/were + V3',
                                'ua' => 'Минулий простий',
                                'note' => 'The house was built. (A2)',
                            ],
                            [
                                'en' => 'Past Continuous: was/were + being + V3',
                                'ua' => 'Минулий тривалий',
                                'note' => 'The road was being repaired. (B1)',
                            ],
                            [
                                'en' => 'Past Perfect: had + been + V3',
                                'ua' => 'Минулий доконаний',
                                'note' => 'The email had been sent. (B2)',
                            ],
                            [
                                'en' => 'Future Simple: will + be + V3',
                                'ua' => 'Майбутній простий',
                                'note' => 'The report will be finished. (B1)',
                            ],
                            [
                                'en' => 'Future Continuous: will + be + being + V3',
                                'ua' => 'Майбутній тривалий',
                                'note' => 'Рідко використовується. (C1)',
                            ],
                            [
                                'en' => 'Future Perfect: will + have + been + V3',
                                'ua' => 'Майбутній доконаний',
                                'note' => 'The task will have been done. (B2)',
                            ],
                        ],
                        'warning' => '📌 Future Continuous Passive рідко використовується на практиці.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Common mistakes
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '11. Типові помилки з часами в пасиві',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск being у Continuous.',
                                'wrong' => '❌ The house is painted (зараз).',
                                'right' => '✅ The house is being painted.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок у Perfect.',
                                'wrong' => '❌ The work been has done.',
                                'right' => '✅ The work has been done.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'orange',
                                'title' => 'Пропуск been у Perfect.',
                                'wrong' => '❌ The report has completed.',
                                'right' => '✅ The report has been completed.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'sky',
                                'title' => 'Неправильне використання was/were.',
                                'wrong' => '❌ The letters was sent.',
                                'right' => '✅ The letters were sent.',
                            ],
                            [
                                'label' => 'Помилка 5',
                                'color' => 'violet',
                                'title' => 'Неправильний Future Perfect Passive.',
                                'wrong' => '❌ The work will been finished.',
                                'right' => '✅ The work will have been finished.',
                            ],
                            [
                                'label' => 'Помилка 6',
                                'color' => 'blue',
                                'title' => 'Зайвий had у Present Perfect.',
                                'wrong' => '❌ The letter has had been sent.',
                                'right' => '✅ The letter has been sent.',
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
                        'title' => '12. Короткий конспект',
                        'items' => [
                            '<strong>Simple Passive:</strong> am/is/are/was/were/will be + V3 (A2, B1).',
                            '<strong>Continuous Passive:</strong> am/is/are/was/were + being + V3 (B1).',
                            '<strong>Perfect Passive:</strong> has/have/had/will have + been + V3 (B1, B2).',
                            'Future Continuous Passive <strong>рідко використовується</strong> (C1).',
                            'Кожен час у пасиві утворюється за формулою: <strong>be (у потрібному часі) + V3</strong>.',
                            'Заперечення: <strong>додаємо not після першого допоміжного дієслова</strong>.',
                            'Питання: <strong>перше допоміжне дієслово виходить на початок</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
