<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class BasicGrammarCategoryV2Seeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'basic-grammar';
    }

    protected function description(): array
    {
        return [
            'title' => 'Basic Grammar — Базова граматика',
            'subtitle_html' => '<p><strong>Базова граматика</strong> — це фундамент англійської мови. У цьому розділі ти вивчиш основні правила побудови речень: порядок слів, питання та заперечення, прислівники та обставини.</p>',
            'subtitle_text' => 'Базова граматика англійської мови: порядок слів, питання, заперечення, прислівники та просунуті структури.',
            'locale' => 'uk',
            'blocks' => [
                // Hero block with V3 JSON structure
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цьому розділі ти опануєш <strong>базову граматику</strong> англійської мови: порядок слів у реченні, питальні та заперечні структури, прислівники та обставини.',
                        'rules' => [
                            [
                                'label' => 'Порядок слів',
                                'color' => 'emerald',
                                'text' => 'Базова структура <strong>S–V–O</strong> — підмет, дієслово, додаток:',
                                'example' => 'She reads books every day.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => 'Допоміжне дієслово <strong>do/does/did</strong> перед підметом:',
                                'example' => 'Do you like pizza?',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => 'Використовуємо <strong>do/does/did + not</strong>:',
                                'example' => "I don't like apples.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid block
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні елементи речення',
                        'intro' => 'Англійське речення будується за чіткою структурою:',
                        'items' => [
                            [
                                'label' => 'Subject',
                                'title' => 'Підмет (S)',
                                'subtitle' => 'Хто виконує дію: I, you, she, Tom',
                            ],
                            [
                                'label' => 'Verb',
                                'title' => 'Дієслово (V)',
                                'subtitle' => 'Дія або стан: read, eat, is, have',
                            ],
                            [
                                'label' => 'Object',
                                'title' => 'Додаток (O)',
                                'subtitle' => 'На кого/що спрямовано дію: book, coffee',
                            ],
                            [
                                'label' => 'Adverbials',
                                'title' => 'Обставини',
                                'subtitle' => 'Коли, де, як: yesterday, at home, quickly',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels block
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Типи речень',
                        'sections' => [
                            [
                                'label' => 'Стверджувальні',
                                'color' => 'emerald',
                                'description' => 'Базова структура S–V–O без додаткових елементів.',
                                'examples' => [
                                    ['en' => 'She reads books.', 'ua' => 'Вона читає книги.'],
                                    ['en' => 'They play football.', 'ua' => 'Вони грають у футбол.'],
                                ],
                            ],
                            [
                                'label' => 'Питальні',
                                'color' => 'blue',
                                'description' => 'Допоміжне дієслово <strong>do/does/did</strong> на початку.',
                                'examples' => [
                                    ['en' => 'Do you like pizza?', 'ua' => 'Тобі подобається піца?'],
                                    ['en' => 'Does she work here?', 'ua' => 'Вона тут працює?'],
                                ],
                            ],
                            [
                                'label' => 'Заперечні',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після допоміжного дієслова.',
                                'examples' => [
                                    ['en' => "I don't like coffee.", 'ua' => 'Я не люблю каву.'],
                                    ['en' => "She doesn't work here.", 'ua' => 'Вона тут не працює.'],
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
                            'Базова структура: <strong>S + V + O</strong> (підмет + дієслово + додаток).',
                            'Прислівники частотності (<em>always, often, never</em>) — <strong>перед основним дієсловом</strong>.',
                            'Обставини часу та місця — зазвичай <strong>в кінці</strong> речення.',
                            'Порядок обставин: <strong>Manner → Place → Time</strong>.',
                            'У питаннях допоміжне дієслово <strong>перед підметом</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'basic-grammar',
            'title' => 'Базова граматика',
            'language' => 'uk',
        ];
    }
}
