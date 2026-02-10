<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceV2CategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2';
    }

    protected function description(): array
    {
        return [
            'title' => 'Passive Voice V2 — Пасивний стан V2',
            'subtitle_html' => '<p><strong>Пасивний стан V2 (Passive Voice V2)</strong> — розширений курс з вивчення пасивного стану англійської мови. Охоплює всі часи, модальні дієслова, інфінітиви, герундії, спеціальні конструкції та стилістичні особливості пасиву.</p>',
            'subtitle_text' => 'Пасивний стан V2: повний курс від основ до просунутого рівня. Утворення пасиву у всіх часах, модальні дієслова, інфінітиви, герундії та типові помилки.',
            'locale' => 'uk',
            'blocks' => [
                // Hero block with V3 JSON structure
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2–C1',
                        'intro' => 'У цьому розділі ти повністю опануєш <strong>пасивний стан</strong> англійської мови: від базових правил утворення до складних конструкцій з інфінітивами, герундіями та модальними дієсловами.',
                        'rules' => [
                            [
                                'label' => 'Основи',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>Subject + be + Past Participle (V3)</strong>:',
                                'example' => 'The cake is baked by my mother.',
                            ],
                            [
                                'label' => 'Всі часи',
                                'color' => 'blue',
                                'text' => 'Пасив у <strong>9 основних часах</strong> англійської мови:',
                                'example' => 'Present/Past/Future Simple, Continuous, Perfect',
                            ],
                            [
                                'label' => 'Модальні',
                                'color' => 'rose',
                                'text' => 'Пасив з <strong>модальними дієсловами</strong>:',
                                'example' => 'This task must be done today.',
                            ],
                            [
                                'label' => 'Інфінітив & Герундій',
                                'color' => 'amber',
                                'text' => 'Пасивні форми <strong>інфінітива та герундія</strong>:',
                                'example' => 'to be done, being done, having been done',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid block - основні правила утворення
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Основні правила утворення пасиву',
                        'intro' => 'У пасивному стані підмет отримує дію, а не виконує її:',
                        'items' => [
                            [
                                'label' => 'Active',
                                'title' => 'Активний стан',
                                'subtitle' => 'Subject виконує дію: Tom writes letters.',
                            ],
                            [
                                'label' => 'Passive',
                                'title' => 'Пасивний стан',
                                'subtitle' => 'Subject отримує дію: Letters are written by Tom.',
                            ],
                            [
                                'label' => 'Формула',
                                'title' => 'be + V3',
                                'subtitle' => 'Дієслово be у потрібному часі + Past Participle.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Виконавець (by)',
                                'subtitle' => 'by + agent — вказується, якщо важливо: by Tom.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати пасив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Коли використовувати пасив?',
                        'sections' => [
                            [
                                'label' => 'Невідомий виконавець',
                                'color' => 'emerald',
                                'description' => 'Коли виконавець дії <strong>невідомий або неважливий</strong>:',
                                'examples' => [
                                    ['en' => 'My bike was stolen yesterday.', 'ua' => 'Мій велосипед вкрали вчора.'],
                                    ['en' => 'The window has been broken.', 'ua' => 'Вікно розбите.'],
                                ],
                            ],
                            [
                                'label' => 'Фокус на дії',
                                'color' => 'blue',
                                'description' => 'Коли важливіша <strong>сама дія або результат</strong>, а не хто її виконав:',
                                'examples' => [
                                    ['en' => 'The report was submitted on time.', 'ua' => 'Звіт був поданий вчасно.'],
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійська мова розмовляється в багатьох країнах.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'rose',
                                'description' => 'У <strong>науковому, офіційному та діловому</strong> стилі:',
                                'examples' => [
                                    ['en' => 'The experiment was conducted last month.', 'ua' => 'Експеримент було проведено минулого місяця.'],
                                    ['en' => 'Applicants will be contacted by email.', 'ua' => 'Із заявниками зв\'яжуться електронною поштою.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list block - структура розділу
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Структура розділу',
                        'items' => [
                            '<strong>Пасив у різних часах</strong>: Present/Past/Future Simple, Continuous, Perfect.',
                            '<strong>Заперечення та питання</strong>: утворення питальних та заперечних форм у пасиві.',
                            '<strong>Пасив з модальними дієсловами</strong>: can/could/must/should be done.',
                            '<strong>Інфінітив та герундій у пасиві</strong>: to be done, being done, having been done.',
                            '<strong>Get-пасив та безособовий пасив</strong>: особливі конструкції.',
                            '<strong>Каузатив (have/get something done)</strong>: коли хтось робить щось для тебе.',
                            '<strong>Пасив двооб\'єктних та фразових дієслів</strong>: особливості.',
                            '<strong>Обмеження, формальність та типові помилки</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-v2',
            'title' => 'Пасивний стан V2',
            'language' => 'uk',
        ];
    }
}
