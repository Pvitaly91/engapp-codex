<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceBasicsWhatIsPassiveTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-what-is-passive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive Voice — Що це і навіщо?',
            'subtitle_html' => '<p><strong>Passive Voice</strong> (пасивний стан) — це граматична конструкція, де фокус на <strong>дії або її результаті</strong>, а не на виконавці. У пасиві object активного речення стає subject.</p>',
            'subtitle_text' => 'Вступ до пасивного стану: що таке пасив, коли і навіщо його використовувати, порівняння з активним станом.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-basics',
                'title' => 'Passive Voice: База — Основи пасивного стану',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Active vs Passive',
                'Introduction',
                'A2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти дізнаєшся, <strong>що таке пасивний стан</strong>, чим він відрізняється від активного, і в яких випадках його використовують.',
                        'rules' => [
                            [
                                'label' => 'Active',
                                'color' => 'emerald',
                                'text' => '<strong>Активний стан</strong> — підмет виконує дію:',
                                'example' => 'Tom writes a letter.',
                            ],
                            [
                                'label' => 'Passive',
                                'color' => 'blue',
                                'text' => '<strong>Пасивний стан</strong> — підмет отримує дію:',
                                'example' => 'A letter is written by Tom.',
                            ],
                            [
                                'label' => 'Коли?',
                                'color' => 'rose',
                                'text' => 'Коли виконавець <strong>невідомий, неважливий</strong> або очевидний:',
                                'example' => 'The window was broken.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке Active і Passive?',
                        'intro' => 'В англійській мові речення може бути у двох станах:',
                        'items' => [
                            [
                                'label' => 'Active Voice',
                                'title' => 'Активний стан',
                                'subtitle' => 'Підмет (subject) виконує дію. Фокус на виконавці.',
                            ],
                            [
                                'label' => 'Passive Voice',
                                'title' => 'Пасивний стан',
                                'subtitle' => 'Підмет отримує дію. Фокус на дії або результаті.',
                            ],
                            [
                                'label' => 'Трансформація',
                                'title' => 'Object → Subject',
                                'subtitle' => 'Додаток (object) активного речення стає підметом у пасиві.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Виконавець (by)',
                                'subtitle' => 'Можна вказати виконавця через "by", але часто опускають.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Порівняння Active vs Passive',
                        'sections' => [
                            [
                                'label' => 'Active Voice',
                                'color' => 'emerald',
                                'description' => 'Підмет <strong>виконує</strong> дію. Структура: <strong>Subject + Verb + Object</strong>',
                                'examples' => [
                                    ['en' => 'Tom writes a letter.', 'ua' => 'Том пише листа. (Том — виконавець)'],
                                    ['en' => 'She cleaned the room.', 'ua' => 'Вона прибрала кімнату.'],
                                    ['en' => 'They build houses.', 'ua' => 'Вони будують будинки.'],
                                ],
                            ],
                            [
                                'label' => 'Passive Voice',
                                'color' => 'blue',
                                'description' => 'Підмет <strong>отримує</strong> дію. Структура: <strong>Subject + be + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'A letter is written by Tom.', 'ua' => 'Лист написаний Томом. (Фокус на листі)'],
                                    ['en' => 'The room was cleaned.', 'ua' => 'Кімната була прибрана.'],
                                    ['en' => 'Houses are built here.', 'ua' => 'Тут будуються будинки.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати Passive Voice?',
                        'sections' => [
                            [
                                'label' => '1. Виконавець невідомий',
                                'color' => 'emerald',
                                'description' => 'Ми не знаємо, хто виконав дію:',
                                'examples' => [
                                    ['en' => 'My car was stolen.', 'ua' => 'Мою машину вкрали. (Не знаємо, хто)'],
                                    ['en' => 'The window was broken.', 'ua' => 'Вікно було розбите.'],
                                ],
                            ],
                            [
                                'label' => '2. Виконавець неважливий',
                                'color' => 'sky',
                                'description' => 'Нам байдуже, хто виконує дію. Важливий результат:',
                                'examples' => [
                                    ['en' => 'English is spoken here.', 'ua' => 'Тут розмовляють англійською.'],
                                    ['en' => 'The building was constructed in 1990.', 'ua' => 'Будівлю було зведено в 1990 році.'],
                                ],
                            ],
                            [
                                'label' => '3. Виконавець очевидний',
                                'color' => 'amber',
                                'description' => 'Зрозуміло, хто виконує дію, тому його не називаємо:',
                                'examples' => [
                                    ['en' => 'He was arrested.', 'ua' => 'Його заарештували. (поліція — очевидно)'],
                                    ['en' => 'The letter was delivered.', 'ua' => 'Листа доставили. (пошта — очевидно)'],
                                ],
                            ],
                            [
                                'label' => '4. Формальний/науковий стиль',
                                'color' => 'rose',
                                'description' => 'У наукових текстах та офіційних документах пасив звучить об\'єктивніше:',
                                'examples' => [
                                    ['en' => 'The experiment was conducted.', 'ua' => 'Експеримент було проведено.'],
                                    ['en' => 'It is believed that...', 'ua' => 'Вважається, що...'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Трансформація Active → Passive',
                        'intro' => 'Як перетворити речення з активного стану в пасивний:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom writes a letter.',
                                'ua' => '(Subject: Tom, Object: a letter)',
                                'note' => '→ Passive: A letter is written by Tom.',
                            ],
                            [
                                'en' => 'Active: She cleaned the room.',
                                'ua' => '(Subject: She, Object: the room)',
                                'note' => '→ Passive: The room was cleaned (by her).',
                            ],
                            [
                                'en' => 'Active: They build houses.',
                                'ua' => '(Subject: They, Object: houses)',
                                'note' => '→ Passive: Houses are built.',
                            ],
                        ],
                        'warning' => '📌 <strong>Object</strong> активного речення стає <strong>Subject</strong> пасивного!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Короткий конспект',
                        'items' => [
                            '<strong>Active Voice</strong>: підмет виконує дію → Tom writes a letter.',
                            '<strong>Passive Voice</strong>: підмет отримує дію → A letter is written.',
                            '<strong>Object → Subject</strong>: додаток активного речення стає підметом пасивного.',
                            '<strong>Коли використовувати</strong>: виконавець невідомий, неважливий, очевидний, або для формального стилю.',
                            '<strong>Agent (by)</strong>: вказує на виконавця, але часто опускається.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з основ пасивного стану',
                        'items' => [
                            [
                                'label' => 'Що таке Passive Voice? (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Form: be + V3 (Past Participle)',
                                'current' => false,
                            ],
                            [
                                'label' => 'Present Simple Passive',
                                'current' => false,
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
