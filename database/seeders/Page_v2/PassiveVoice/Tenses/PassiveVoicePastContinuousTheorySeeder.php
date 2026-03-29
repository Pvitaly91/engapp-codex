<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

class PassiveVoicePastContinuousTheorySeeder extends PassiveVoiceTensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-past-continuous';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Continuous Passive — Минулий тривалий пасив',
            'subtitle_html' => '<p><strong>Past Continuous Passive</strong> використовується для опису дій, що <strong>тривали в певний момент у минулому</strong> або <strong>відбувалися паралельно з іншими діями</strong>. Формула: <strong>was/were + being + V3</strong>.</p>',
            'subtitle_text' => 'Past Continuous Passive (Минулий тривалий пасив): формула was/were + being + V3, опис тривалих дій у минулому.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Past Continuous Passive',
                'Минулий тривалий пасив',
                'was were being',
                'B1',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти детально вивчиш <strong>Past Continuous Passive</strong> (Минулий тривалий пасив): як утворювати цю форму, коли використовувати та в чому відмінність від Past Simple Passive.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + was/were + being + V3</strong>:',
                                'example' => 'The house was being painted when I arrived.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + was/were + not + being + V3</strong>:',
                                'example' => 'The report was not being written at that time.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Was/Were + Subject + being + V3?</strong>:',
                                'example' => 'Was the project being developed?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - структура
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Структура Past Continuous Passive',
                        'intro' => 'Форма дієслова <strong>be</strong> залежить від підмета, а <strong>being</strong> залишається незмінним:',
                        'items' => [
                            [
                                'label' => 'I/He/She/It',
                                'title' => 'was being + V3',
                                'subtitle' => 'The car was being repaired. — Машину ремонтували.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'were being + V3',
                                'subtitle' => 'The documents were being checked. — Документи перевіряли.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'was being + V3',
                                'subtitle' => 'The house was being built. — Будинок будували.',
                            ],
                            [
                                'label' => 'Ключ',
                                'title' => 'being — незмінна частина',
                                'subtitle' => 'being завжди стоїть перед V3.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - ствердження
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Стверджувальні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Порядок слів: <strong>Subject + was/were + being + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The house was being painted when I arrived.', 'ua' => 'Будинок фарбували, коли я прийшов.'],
                                    ['en' => 'The documents were being prepared all morning.', 'ua' => 'Документи готували весь ранок.'],
                                    ['en' => 'The car was being repaired at 5 PM.', 'ua' => 'Машину ремонтували о 17:00.'],
                                    ['en' => 'The rooms were being cleaned by the staff.', 'ua' => 'Кімнати прибирав персонал.'],
                                ],
                            ],
                            [
                                'label' => 'Тривала дія в момент минулого',
                                'color' => 'sky',
                                'description' => 'Для дій, що <strong>тривали в певний момент</strong>:',
                                'examples' => [
                                    ['en' => 'At 3 PM, the meeting was being held.', 'ua' => 'О 15:00 проводилася зустріч.'],
                                    ['en' => 'The road was being repaired all day.', 'ua' => 'Дорогу ремонтували весь день.'],
                                    ['en' => 'The project was being discussed when he called.', 'ua' => 'Проєкт обговорювали, коли він подзвонив.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати Past Continuous Passive?',
                        'sections' => [
                            [
                                'label' => 'Дія в момент минулого',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>тривали в певний момент у минулому</strong>:',
                                'examples' => [
                                    ['en' => 'At 8 AM, breakfast was being served.', 'ua' => 'О 8 ранку подавали сніданок.'],
                                    ['en' => 'The building was being renovated last summer.', 'ua' => 'Будівлю ремонтували минулого літа.'],
                                    ['en' => 'The reports were being prepared yesterday evening.', 'ua' => 'Звіти готували вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'Паралельні дії',
                                'color' => 'blue',
                                'description' => 'Коли одна дія <strong>перервала іншу тривалу</strong>:',
                                'examples' => [
                                    ['en' => 'The car was being washed when it started to rain.', 'ua' => 'Машину мили, коли почався дощ.'],
                                    ['en' => 'The dinner was being cooked when the guests arrived.', 'ua' => 'Вечерю готували, коли приїхали гості.'],
                                    ['en' => 'The document was being printed when the power went out.', 'ua' => 'Документ друкували, коли вимкнули світло.'],
                                ],
                            ],
                            [
                                'label' => 'Тривалість процесу',
                                'color' => 'amber',
                                'description' => 'Для підкреслення <strong>тривалості процесу</strong>:',
                                'examples' => [
                                    ['en' => 'The house was being built for two years.', 'ua' => 'Будинок будували два роки.'],
                                    ['en' => 'The project was being developed throughout the year.', 'ua' => 'Проєкт розробляли протягом року.'],
                                    ['en' => 'The investigation was being conducted all week.', 'ua' => 'Розслідування проводили весь тиждень.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Заперечні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після was/were: <strong>Subject + was/were + not + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report was not being written at that time.', 'ua' => 'Звіт не писали в той час.'],
                                    ['en' => 'The rooms were not being cleaned yesterday.', 'ua' => 'Кімнати не прибирали вчора.'],
                                    ['en' => 'The problem was not being addressed.', 'ua' => 'Проблему не вирішували.'],
                                    ['en' => 'The orders were not being processed.', 'ua' => 'Замовлення не обробляли.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>wasn\'t being / weren\'t being</strong>',
                                'examples' => [
                                    ['en' => "The car wasn't being repaired yesterday.", 'ua' => 'Машину вчора не ремонтували.'],
                                    ['en' => "The packages weren't being delivered.", 'ua' => 'Посилки не доставляли.'],
                                    ['en' => "The issue wasn't being investigated.", 'ua' => 'Питання не розслідували.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Питальні речення',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were</strong> виходить на перше місце: <strong>Was/Were + Subject + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the house being painted?', 'ua' => 'Будинок фарбували?'],
                                    ['en' => 'Were the documents being checked?', 'ua' => 'Документи перевіряли?'],
                                    ['en' => 'Was the project being developed?', 'ua' => 'Проєкт розробляли?'],
                                    ['en' => 'Were they being interviewed?', 'ua' => 'Їх інтерв\'юювали?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + was/were + Subject + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'What was being built there?', 'ua' => 'Що там будували?'],
                                    ['en' => 'Why was the road being closed?', 'ua' => 'Чому дорогу перекривали?'],
                                    ['en' => 'Where were the tests being conducted?', 'ua' => 'Де проводили тести?'],
                                    ['en' => 'How was the problem being solved?', 'ua' => 'Як вирішували проблему?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + was/were (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Was the car being repaired? — Yes, it was. / No, it wasn\'t.', 'ua' => 'Машину ремонтували? — Так. / Ні.'],
                                    ['en' => 'Were they being interviewed? — Yes, they were. / No, they weren\'t.', 'ua' => 'Їх інтерв\'юювали? — Так. / Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Порівняння Active vs Passive у Past Continuous',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They were painting the house.',
                                'ua' => 'Вони фарбували будинок.',
                                'note' => '→ Passive: The house was being painted.',
                            ],
                            [
                                'en' => 'Active: The manager was checking the documents.',
                                'ua' => 'Менеджер перевіряв документи.',
                                'note' => '→ Passive: The documents were being checked.',
                            ],
                            [
                                'en' => 'Active: Workers were repairing the road.',
                                'ua' => 'Робітники ремонтували дорогу.',
                                'note' => '→ Passive: The road was being repaired.',
                            ],
                            [
                                'en' => 'Active: Someone was interviewing me.',
                                'ua' => 'Хтось мене інтерв\'ював.',
                                'note' => '→ Passive: I was being interviewed.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (when, while, at that time) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - Past Simple vs Past Continuous Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Past Simple Passive vs Past Continuous Passive',
                        'intro' => 'Порівняння двох форм — завершена дія vs тривала дія:',
                        'rows' => [
                            [
                                'en' => 'The house was built in 1990.',
                                'ua' => 'Будинок побудували у 1990 році.',
                                'note' => 'vs: The house was being built when I visited. — Будинок будували, коли я приїхав.',
                            ],
                            [
                                'en' => 'The car was repaired yesterday.',
                                'ua' => 'Машину відремонтували вчора.',
                                'note' => 'vs: The car was being repaired at 5 PM. — Машину ремонтували о 17:00.',
                            ],
                            [
                                'en' => 'The report was written.',
                                'ua' => 'Звіт написали.',
                                'note' => 'vs: The report was being written all day. — Звіт писали весь день.',
                            ],
                        ],
                        'warning' => '📌 Past Simple = факт, завершена дія. Past Continuous = процес, тривала дія.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Маркери часу для Past Continuous Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Past Continuous:',
                        'items' => [
                            [
                                'label' => 'Момент',
                                'title' => 'at 5 PM, at that time, at noon',
                                'subtitle' => 'The report was being written at 5 PM.',
                            ],
                            [
                                'label' => 'Переривання',
                                'title' => 'when, while, as',
                                'subtitle' => 'The house was being painted when it started to rain.',
                            ],
                            [
                                'label' => 'Тривалість',
                                'title' => 'all day, all morning, the whole week',
                                'subtitle' => 'The road was being repaired all week.',
                            ],
                            [
                                'label' => 'Паралельність',
                                'title' => 'while, at the same time',
                                'subtitle' => 'The dinner was being cooked while we waited.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск being.',
                                'wrong' => 'The house was painted when I arrived.',
                                'right' => '✅ The house was being painted when I arrived.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'The report being was written.',
                                'right' => '✅ The report was being written.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання been замість being.',
                                'wrong' => 'The car was been repaired.',
                                'right' => '✅ The car was being repaired.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Плутанина Past Simple та Continuous.',
                                'wrong' => 'The house was being built in 1990. (якщо факт)',
                                'right' => '✅ The house was built in 1990. (факт)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            'Past Continuous Passive: <strong>was/were + being + V3</strong>.',
                            'Вибір be: <strong>I/He/She/It → was</strong>, <strong>You/We/They → were</strong>.',
                            '<strong>being</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>was/were + not + being + V3</strong>.',
                            'Питання: <strong>Was/Were + Subject + being + V3?</strong>',
                            'Використовується для: <strong>тривалих дій у минулому</strong>, <strong>паралельних дій</strong>.',
                            'Маркери часу: at 5 PM, when, while, all day тощо.',
                            'Не плутай з Past Simple Passive (завершена дія)!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
