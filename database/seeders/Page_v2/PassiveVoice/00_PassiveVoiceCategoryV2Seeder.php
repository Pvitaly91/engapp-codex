<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceCategoryV2Seeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'passive-voice';
    }

    protected function description(): array
    {
        return [
            'title' => 'Passive Voice — Пасивний стан',
            'subtitle_html' => '<p><strong>Пасивний стан (Passive Voice)</strong> — це граматична форма, яка використовується, коли важливіша не дія, а об\'єкт, на який ця дія спрямована. У цьому розділі ти вивчиш утворення пасивного стану в різних часах, коли та як його використовувати.</p>',
            'subtitle_text' => 'Пасивний стан (Passive Voice) в англійській мові: утворення, використання в різних часах, типові випадки вживання.',
            'locale' => 'uk',
            'blocks' => [
                // Hero block with V3 JSON structure
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–C1',
                        'intro' => 'У цьому розділі ти опануєш <strong>Passive Voice (пасивний стан)</strong> — граматичну конструкцію, яка використовується, коли на першому місці стоїть об\'єкт дії, а не той, хто її виконує.',
                        'rules' => [
                            [
                                'label' => 'Утворення',
                                'color' => 'emerald',
                                'text' => 'Пасив утворюється за формулою: <strong>be + V3</strong> (дієприкметник минулого часу):',
                                'example' => 'The book is written by the author.',
                            ],
                            [
                                'label' => 'Основні часи',
                                'color' => 'blue',
                                'text' => 'Пасив існує у <strong>всіх основних часах</strong> — змінюється форма "be":',
                                'example' => 'Present: is made / Past: was made / Future: will be made',
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'rose',
                                'text' => 'Вживаємо пасив, коли <strong>виконавець невідомий, неважливий</strong> або очевидний:',
                                'example' => 'English is spoken all over the world.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid block
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні компоненти Passive Voice',
                        'intro' => 'Пасивний стан складається з двох частин:',
                        'items' => [
                            [
                                'label' => 'be',
                                'title' => 'Форма дієслова "be"',
                                'subtitle' => 'Змінюється залежно від часу: is/are, was/were, will be, has been...',
                            ],
                            [
                                'label' => 'V3',
                                'title' => 'Past Participle (V3)',
                                'subtitle' => 'Третя форма дієслова: written, made, taken, built',
                            ],
                            [
                                'label' => 'by',
                                'title' => 'Вказівка виконавця (by)',
                                'subtitle' => 'Опціонально: by the author, by students, by my friend',
                            ],
                            [
                                'label' => 'Приклад',
                                'title' => 'Повна структура',
                                'subtitle' => 'The house was built by my grandfather in 1950.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels block
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Пасив у різних часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'Форма: <strong>am/is/are + V3</strong>. Використовується для загальних фактів і звичних дій.',
                                'examples' => [
                                    ['en' => 'The office is cleaned every day.', 'ua' => 'Офіс прибирають щодня.'],
                                    ['en' => 'English is spoken here.', 'ua' => 'Тут говорять англійською.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => 'Форма: <strong>was/were + V3</strong>. Використовується для завершених дій у минулому.',
                                'examples' => [
                                    ['en' => 'The book was published in 1995.', 'ua' => 'Книгу опублікували у 1995 році.'],
                                    ['en' => 'The letters were sent yesterday.', 'ua' => 'Листи відправили вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'rose',
                                'description' => 'Форма: <strong>will be + V3</strong>. Використовується для майбутніх дій.',
                                'examples' => [
                                    ['en' => 'The project will be completed next week.', 'ua' => 'Проект буде завершено наступного тижня.'],
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'purple',
                                'description' => 'Форма: <strong>has/have been + V3</strong>. Для дій, що мають зв\'язок з теперішнім.',
                                'examples' => [
                                    ['en' => 'The work has been finished.', 'ua' => 'Роботу завершено.'],
                                    ['en' => 'The documents have been signed.', 'ua' => 'Документи підписано.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list block
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати пасив?',
                        'items' => [
                            'Коли <strong>виконавець дії невідомий</strong>: <em>My car was stolen</em> (хто вкрав — невідомо).',
                            'Коли виконавець <strong>неважливий або очевидний</strong>: <em>The bridge was built in 2005</em> (будівельники).',
                            'У <strong>науковій та офіційній мові</strong>: <em>The experiment was conducted...</em>',
                            'Для <strong>акценту на об\'єкті</strong>, а не на виконавці: <em>The Mona Lisa was painted by Leonardo da Vinci</em>.',
                            'У <strong>новинах та інструкціях</strong>: <em>The product should be stored in a cool place</em>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Additional usage panel
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Активний vs Пасивний стан',
                        'sections' => [
                            [
                                'label' => 'Active Voice',
                                'color' => 'emerald',
                                'description' => 'Підмет виконує дію. Фокус на <strong>виконавці</strong>.',
                                'examples' => [
                                    ['en' => 'Shakespeare wrote Hamlet.', 'ua' => 'Шекспір написав Гамлета.'],
                                    ['en' => 'The chef prepares the food.', 'ua' => 'Шеф-кухар готує їжу.'],
                                ],
                            ],
                            [
                                'label' => 'Passive Voice',
                                'color' => 'blue',
                                'description' => 'Підмет зазнає дії. Фокус на <strong>об\'єкті</strong> або результаті.',
                                'examples' => [
                                    ['en' => 'Hamlet was written by Shakespeare.', 'ua' => 'Гамлет був написаний Шекспіром.'],
                                    ['en' => 'The food is prepared by the chef.', 'ua' => 'Їжа готується шеф-кухарем.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'passive-voice',
            'title' => 'Пасивний стан (Passive Voice)',
            'language' => 'uk',
        ];
    }
}
