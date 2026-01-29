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
            'subtitle_html' => '<p><strong>Пасивний стан (Passive Voice)</strong> — це граматична конструкція, у якій підмет речення отримує дію, а не виконує її. Використовується, коли фокус на дії або її результаті, а не на виконавці.</p>',
            'subtitle_text' => 'Пасивний стан англійської мови: структура, правила утворення та використання у різних часах.',
            'locale' => 'uk',
            'blocks' => [
                // Hero block with V3 JSON structure
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B2',
                        'intro' => 'У цьому розділі ти опануєш <strong>пасивний стан</strong> англійської мови: як його утворювати, коли використовувати та як він змінюється у різних часах.',
                        'rules' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>Object + be + Past Participle</strong>:',
                                'example' => 'The cake is made by my mother.',
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'blue',
                                'text' => 'Коли виконавець <strong>невідомий, неважливий</strong> або очевидний:',
                                'example' => 'The letter was sent yesterday.',
                            ],
                            [
                                'label' => 'Агент (by)',
                                'color' => 'rose',
                                'text' => 'Виконавець вказується через <strong>by + agent</strong> (необов\'язково):',
                                'example' => 'The book was written by J.K. Rowling.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid block
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Порівняння Active vs Passive',
                        'intro' => 'У активному стані підмет виконує дію, у пасивному — отримує її:',
                        'items' => [
                            [
                                'label' => 'Active',
                                'title' => 'Активний стан',
                                'subtitle' => 'The chef cooks the meal. — Шеф готує їжу.',
                            ],
                            [
                                'label' => 'Passive',
                                'title' => 'Пасивний стан',
                                'subtitle' => 'The meal is cooked by the chef. — Їжа готується шефом.',
                            ],
                            [
                                'label' => 'Focus',
                                'title' => 'Фокус уваги',
                                'subtitle' => 'Active — на виконавці, Passive — на дії/результаті.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Вказівка виконавця',
                                'subtitle' => 'by + agent — необов\'язково, якщо виконавець очевидний.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels block
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Passive Voice у різних часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>am/is/are + Past Participle</strong>',
                                'examples' => [
                                    ['en' => 'The cake is made every day.', 'ua' => 'Торт випікається щодня.'],
                                    ['en' => 'Letters are sent by post.', 'ua' => 'Листи надсилаються поштою.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => 'Структура: <strong>was/were + Past Participle</strong>',
                                'examples' => [
                                    ['en' => 'The window was broken yesterday.', 'ua' => 'Вікно було розбите вчора.'],
                                    ['en' => 'The cars were stolen last night.', 'ua' => 'Машини були вкрадені вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'rose',
                                'description' => 'Структура: <strong>has/have been + Past Participle</strong>',
                                'examples' => [
                                    ['en' => 'The work has been done.', 'ua' => 'Робота виконана.'],
                                    ['en' => 'The tickets have been sold.', 'ua' => 'Квитки продані.'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'amber',
                                'description' => 'Структура: <strong>will be + Past Participle</strong>',
                                'examples' => [
                                    ['en' => 'The project will be finished tomorrow.', 'ua' => 'Проєкт буде завершений завтра.'],
                                    ['en' => 'The results will be announced next week.', 'ua' => 'Результати буде оголошено наступного тижня.'],
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
                        'title' => '3. Ключові правила',
                        'items' => [
                            'Базова структура: <strong>Object + be (у потрібному часі) + Past Participle</strong>.',
                            'Тільки <strong>перехідні дієслова</strong> (з додатком) можуть утворювати пасивний стан.',
                            'Виконавець (агент) вказується через <strong>by</strong>, але часто опускається.',
                            'У модальних конструкціях: <strong>modal + be + Past Participle</strong> (The task must be done).',
                            'Пасивний стан часто використовується у <strong>науковому та офіційному</strong> стилях.',
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
            'title' => 'Пасивний стан',
            'language' => 'uk',
        ];
    }
}
