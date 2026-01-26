<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceBasicsPastSimpleTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-past-simple';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Simple Passive — Пасивний стан минулого часу',
            'subtitle_html' => '<p><strong>Past Simple Passive</strong> використовується для опису завершених дій у минулому в пасивному стані. Формула: <strong>was/were + V3</strong>.</p>',
            'subtitle_text' => 'Past Simple Passive: формула was/were + V3, ствердження, заперечення, питання та практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-basics',
                'title' => 'База — Основи пасивного стану',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Past Simple Passive',
                'was were',
                'A2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти вивчиш <strong>Past Simple Passive</strong>: як утворювати ствердження, заперечення та питання у пасивному стані минулого часу.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>was/were + V3</strong>:',
                                'example' => 'The house was built in 1990.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>was/were + not + V3</strong>:',
                                'example' => 'The letter was not sent.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Was/Were + S + V3?</strong>:',
                                'example' => 'Was the house built in 1990?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Вибір was/were',
                        'intro' => 'Дієслово <strong>be</strong> в минулому часі:',
                        'items' => [
                            [
                                'label' => 'I/He/She/It',
                                'title' => 'was',
                                'subtitle' => 'I was invited. The house was built.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'were',
                                'subtitle' => 'You were told. They were arrested.',
                            ],
                            [
                                'label' => 'Singular nouns',
                                'title' => 'was',
                                'subtitle' => 'The car was stolen.',
                            ],
                            [
                                'label' => 'Plural nouns',
                                'title' => 'were',
                                'subtitle' => 'The thieves were caught.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Стверджувальні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Порядок: <strong>Subject + was/were + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The thieves were caught yesterday.', 'ua' => 'Злодіїв зловили вчора.'],
                                    ['en' => 'I was invited to the party.', 'ua' => 'Мене запросили на вечірку.'],
                                ],
                            ],
                            [
                                'label' => 'Коли використовувати?',
                                'color' => 'sky',
                                'description' => 'Для <strong>завершених дій у минулому</strong>:',
                                'examples' => [
                                    ['en' => 'America was discovered in 1492.', 'ua' => 'Америку відкрили в 1492 році. (історичний факт)'],
                                    ['en' => 'The letter was sent last week.', 'ua' => 'Листа надіслали минулого тижня.'],
                                    ['en' => 'The window was broken during the storm.', 'ua' => 'Вікно розбилося під час бурі.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Заперечні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після was/were: <strong>Subject + was/were + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email was not sent.', 'ua' => 'Електронний лист не був надісланий.'],
                                    ['en' => 'The documents were not signed.', 'ua' => 'Документи не були підписані.'],
                                    ['en' => 'I was not informed about the meeting.', 'ua' => 'Мене не повідомили про зустріч.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові використовують <strong>wasn\'t / weren\'t</strong>:',
                                'examples' => [
                                    ['en' => "The door wasn't locked.", 'ua' => 'Двері не були замкнені.'],
                                    ['en' => "The rooms weren't cleaned yesterday.", 'ua' => 'Кімнати не прибирали вчора.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Питальні речення',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were</strong> виходить на перше місце: <strong>Was/Were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                    ['en' => 'Were the documents signed?', 'ua' => 'Документи були підписані?'],
                                    ['en' => 'Was she invited to the party?', 'ua' => 'Її запросили на вечірку?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + was/were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'When was the house built?', 'ua' => 'Коли був побудований будинок?'],
                                    ['en' => 'Where was the car found?', 'ua' => 'Де знайшли машину?'],
                                    ['en' => 'Why were they arrested?', 'ua' => 'Чому їх заарештували?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + was/were (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Was the letter sent? — Yes, it was. / No, it wasn\'t.', 'ua' => 'Листа надіслали? — Так. / Ні.'],
                                    ['en' => 'Were they informed? — Yes, they were. / No, they weren\'t.', 'ua' => 'Їх повідомили? — Так. / Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Active vs Passive: Past Simple',
                        'intro' => 'Порівняння активного та пасивного стану в минулому:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom wrote the letter yesterday.',
                                'ua' => 'Том написав листа вчора.',
                                'note' => '→ Passive: The letter was written yesterday.',
                            ],
                            [
                                'en' => 'Active: They built this house in 1990.',
                                'ua' => 'Вони побудували цей будинок у 1990.',
                                'note' => '→ Passive: This house was built in 1990.',
                            ],
                            [
                                'en' => 'Active: Someone stole my bike.',
                                'ua' => 'Хтось вкрав мій велосипед.',
                                'note' => '→ Passive: My bike was stolen.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (yesterday, last week, in 1990) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Маркери часу для Past Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Типові маркери',
                                'color' => 'emerald',
                                'description' => 'Past Simple Passive часто використовується з маркерами <strong>минулого часу</strong>:',
                                'examples' => [
                                    ['en' => 'yesterday', 'ua' => 'The report was finished yesterday.'],
                                    ['en' => 'last week/month/year', 'ua' => 'The car was sold last week.'],
                                    ['en' => 'in 1990 / in the 19th century', 'ua' => 'The bridge was built in 1900.'],
                                    ['en' => 'ago (two days ago)', 'ua' => 'The email was sent two days ago.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний вибір was/were.',
                                'wrong' => 'The documents was signed.',
                                'right' => '✅ The documents were signed.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V2 замість V3.',
                                'wrong' => 'The house was builded in 1990.',
                                'right' => '✅ The house was built in 1990.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Пропуск be у питаннях.',
                                'wrong' => 'The letter sent yesterday?',
                                'right' => '✅ Was the letter sent yesterday?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Past Simple Passive: <strong>was/were + V3</strong>.',
                            'Вибір be: <strong>I/He/She/It + singular → was</strong>, <strong>You/We/They + plural → were</strong>.',
                            'Заперечення: <strong>was/were + not + V3</strong> (wasn\'t/weren\'t).',
                            'Питання: <strong>Was/Were + Subject + V3?</strong>',
                            'Використовується для <strong>завершених дій у минулому</strong>.',
                            'Маркери часу: yesterday, last week, ago, in + рік.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з основ пасивного стану',
                        'items' => [
                            [
                                'label' => 'Що таке Passive Voice?',
                                'current' => false,
                            ],
                            [
                                'label' => 'Form: be + V3',
                                'current' => false,
                            ],
                            [
                                'label' => 'Present Simple Passive',
                                'current' => false,
                            ],
                            [
                                'label' => 'Past Simple Passive (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
