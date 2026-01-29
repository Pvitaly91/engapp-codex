<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceBasicsPresentSimpleTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-present-simple';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Simple Passive — Пасивний стан теперішнього часу',
            'subtitle_html' => '<p><strong>Present Simple Passive</strong> використовується для опису регулярних, звичних дій або загальних фактів у пасивному стані. Формула: <strong>am/is/are + V3</strong>.</p>',
            'subtitle_text' => 'Present Simple Passive: формула am/is/are + V3, ствердження, заперечення, питання та практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-basics',
                'title' => 'База — Основи пасивного стану',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Simple Passive',
                'am is are',
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
                        'intro' => 'У цій темі ти вивчиш <strong>Present Simple Passive</strong>: як утворювати ствердження, заперечення та питання у пасивному стані теперішнього часу.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>am/is/are + V3</strong>:',
                                'example' => 'The letter is written.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>am/is/are + not + V3</strong>:',
                                'example' => 'The letter is not written.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Am/Is/Are + S + V3?</strong>:',
                                'example' => 'Is the letter written?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Вибір am/is/are',
                        'intro' => 'Дієслово <strong>be</strong> залежить від підмета:',
                        'items' => [
                            [
                                'label' => 'I',
                                'title' => 'am',
                                'subtitle' => 'I am paid monthly.',
                            ],
                            [
                                'label' => 'He/She/It',
                                'title' => 'is',
                                'subtitle' => 'It is made in China.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'are',
                                'subtitle' => 'They are invited to the party.',
                            ],
                            [
                                'label' => 'Plural nouns',
                                'title' => 'are',
                                'subtitle' => 'Cars are produced here.',
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
                                'description' => 'Порядок: <strong>Subject + am/is/are + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійська розмовляється в багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every day.', 'ua' => 'Кімнати прибираються щодня.'],
                                    ['en' => 'I am paid at the end of each month.', 'ua' => 'Мені платять наприкінці кожного місяця.'],
                                ],
                            ],
                            [
                                'label' => 'Коли використовувати?',
                                'color' => 'sky',
                                'description' => 'Для <strong>регулярних дій</strong>, <strong>загальних фактів</strong> та <strong>звичок</strong>:',
                                'examples' => [
                                    ['en' => 'Coffee is grown in Brazil.', 'ua' => 'Каву вирощують у Бразилії. (загальний факт)'],
                                    ['en' => 'Newspapers are delivered every morning.', 'ua' => 'Газети доставляють щоранку. (регулярна дія)'],
                                    ['en' => 'This road is used by many cars.', 'ua' => 'Цією дорогою користуються багато машин.'],
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
                                'description' => 'Додаємо <strong>not</strong> після am/is/are: <strong>Subject + am/is/are + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'These cars are not made in Japan.', 'ua' => 'Ці машини не виробляються в Японії.'],
                                    ['en' => 'I am not invited to the meeting.', 'ua' => 'Мене не запросили на зустріч.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові використовують <strong>isn\'t / aren\'t</strong>:',
                                'examples' => [
                                    ['en' => "The window isn't opened.", 'ua' => 'Вікно не відкрите.'],
                                    ['en' => "The reports aren't finished yet.", 'ua' => 'Звіти ще не готові.'],
                                ],
                                'note' => 'Форма <strong>am not</strong> не скорочується: I am not (I\'m not).',
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
                                'description' => '<strong>Am/Is/Are</strong> виходить на перше місце: <strong>Am/Is/Are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the rooms cleaned daily?', 'ua' => 'Кімнати прибираються щодня?'],
                                    ['en' => 'Am I included in the list?', 'ua' => 'Мене включено до списку?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + am/is/are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Where is coffee grown?', 'ua' => 'Де вирощують каву?'],
                                    ['en' => 'How often are the rooms cleaned?', 'ua' => 'Як часто прибираються кімнати?'],
                                    ['en' => 'Why is this product so popular?', 'ua' => 'Чому цей продукт такий популярний?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + am/is/are (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Is the letter written? — Yes, it is. / No, it isn\'t.', 'ua' => 'Лист написаний? — Так. / Ні.'],
                                    ['en' => 'Are they invited? — Yes, they are. / No, they aren\'t.', 'ua' => 'Їх запросили? — Так. / Ні.'],
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
                        'title' => '5. Active vs Passive: Present Simple',
                        'intro' => 'Порівняння активного та пасивного стану:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom writes letters every day.',
                                'ua' => 'Том пише листи щодня.',
                                'note' => '→ Passive: Letters are written every day.',
                            ],
                            [
                                'en' => 'Active: They clean the rooms daily.',
                                'ua' => 'Вони прибирають кімнати щодня.',
                                'note' => '→ Passive: The rooms are cleaned daily.',
                            ],
                            [
                                'en' => 'Active: People speak English here.',
                                'ua' => 'Люди тут розмовляють англійською.',
                                'note' => '→ Passive: English is spoken here.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (every day, always, often) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний вибір is/are.',
                                'wrong' => 'The letters is sent every day.',
                                'right' => '✅ The letters are sent every day.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск be у питаннях.',
                                'wrong' => 'The room cleaned daily?',
                                'right' => '✅ Is the room cleaned daily?',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'English is speak here.',
                                'right' => '✅ English is spoken here.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Present Simple Passive: <strong>am/is/are + V3</strong>.',
                            'Вибір be: <strong>I → am</strong>, <strong>He/She/It → is</strong>, <strong>You/We/They → are</strong>.',
                            'Заперечення: <strong>am/is/are + not + V3</strong>.',
                            'Питання: <strong>Am/Is/Are + Subject + V3?</strong>',
                            'Використовується для <strong>регулярних дій</strong> та <strong>загальних фактів</strong>.',
                            'Маркери часу: every day, often, always, usually.',
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
                                'label' => 'Present Simple Passive (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
