<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceCategoryV2Seeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '13';
    }

    protected function description(): array
    {
        return [
            'title' => 'Passive Voice — Пасивний стан',
            'subtitle_html' => '<p><strong>Passive Voice</strong> допомагає зосередитися на дії та результаті, а не на виконавці. У цьому розділі ти вивчиш, як утворювати пасив у різних часах, коли використовувати пасивні конструкції та як уникати типових помилок.</p>',
            'subtitle_text' => 'Passive Voice в англійській мові: утворення пасиву в різних часах, коли він доречний, як змінюється порядок слів та роль виконавця дії.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–C1',
                        'intro' => 'У цьому розділі ти опануєш <strong>Passive Voice</strong>: як перетворювати активні речення на пасивні, які часи найчастіше використовуються та коли пасив звучить природно.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => 'Пасив утворюється за формулою <strong>be + V3</strong> (Past Participle):',
                                'example' => 'The report is prepared every week.',
                            ],
                            [
                                'label' => 'Час',
                                'color' => 'blue',
                                'text' => 'Час визначається формою <strong>to be</strong>:',
                                'example' => 'The bridge was built in 1990.',
                            ],
                            [
                                'label' => 'Виконавець',
                                'color' => 'rose',
                                'text' => 'Виконавець дії не обов’язковий, але може бути після <strong>by</strong>:',
                                'example' => 'The song was written by Adele.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Як утворити Passive Voice',
                        'intro' => 'Основні кроки перетворення активного речення на пасивне:',
                        'items' => [
                            [
                                'label' => 'Object → Subject',
                                'title' => 'Об’єкт стає підметом',
                                'subtitle' => 'The company built a bridge. → The bridge...',
                            ],
                            [
                                'label' => 'Be + V3',
                                'title' => 'Дієслово у пасиві',
                                'subtitle' => 'to be у потрібному часі + Past Participle (V3)',
                            ],
                            [
                                'label' => 'Agent (optional)',
                                'title' => 'Виконавець дії',
                                'subtitle' => 'Додаємо by, якщо важливо: by the company',
                            ],
                            [
                                'label' => 'Focus',
                                'title' => 'Фокус на результаті',
                                'subtitle' => 'Пасив використовують, коли дія важливіша за виконавця',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Найуживаніші часи у пасиві',
                        'sections' => [
                            [
                                'label' => 'Present Simple Passive',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + V3</strong>. Для фактів і регулярних дій.',
                                'examples' => [
                                    ['en' => 'Coffee is served here.', 'ua' => 'Тут подають каву.'],
                                    ['en' => 'The room is cleaned every day.', 'ua' => 'Кімнату прибирають щодня.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'color' => 'blue',
                                'description' => 'Формула: <strong>was/were + V3</strong>. Для завершених подій у минулому.',
                                'examples' => [
                                    ['en' => 'The castle was built in 1790.', 'ua' => 'Замок збудували у 1790 році.'],
                                    ['en' => 'The tickets were sold out quickly.', 'ua' => 'Квитки швидко розпродали.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect Passive',
                                'color' => 'rose',
                                'description' => 'Формула: <strong>has/have been + V3</strong>. Для результату, що важливий зараз.',
                                'examples' => [
                                    ['en' => 'The report has been sent.', 'ua' => 'Звіт уже надіслано.'],
                                    ['en' => 'All tasks have been completed.', 'ua' => 'Усі завдання виконано.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати пасив',
                        'items' => [
                            'Коли виконавець дії невідомий або неважливий: <strong>The window was broken.</strong>',
                            'Коли фокус на результаті: <strong>The project has been completed.</strong>',
                            'У формальному стилі, звітах та інструкціях: <strong>Payments are processed within 24 hours.</strong>',
                            'Якщо потрібно приховати або не підкреслювати виконавця: <strong>The decision was made.</strong>',
                            'З конструкцією <strong>by</strong>, якщо важливий виконавець: <strong>The painting was created by Monet.</strong>',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => '13',
            'title' => 'Пасивний стан',
            'language' => 'uk',
        ];
    }
}
