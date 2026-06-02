<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

class PassiveVoicePresentContinuousTheorySeeder extends PassiveVoiceTensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-present-continuous';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Continuous Passive — Теперішній тривалий пасив',
            'subtitle_html' => '<p><strong>Present Continuous Passive</strong> використовується для опису дій, що <strong>відбуваються прямо зараз</strong> або <strong>тимчасових процесів</strong> у пасивному стані. Формула: <strong>am/is/are + being + V3</strong>.</p>',
            'subtitle_text' => 'Present Continuous Passive (Теперішній тривалий пасив): формула am/is/are + being + V3, опис дій що відбуваються зараз, практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Continuous Passive',
                'Теперішній тривалий пасив',
                'am is are being',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Present Continuous Passive</strong> (Теперішній тривалий пасив): як утворювати цю форму, коли використовувати та в чому відмінність від Present Simple Passive.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + am/is/are + being + V3</strong>:',
                                'example' => 'The house is being painted right now.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + am/is/are + not + being + V3</strong>:',
                                'example' => 'The report is not being written now.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Am/Is/Are + Subject + being + V3?</strong>:',
                                'example' => 'Is the project being developed?',
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
                        'title' => '1. Структура Present Continuous Passive',
                        'intro' => 'Форма дієслова <strong>be</strong> залежить від підмета, а <strong>being</strong> залишається незмінним:',
                        'items' => [
                            [
                                'label' => 'I',
                                'title' => 'am being + V3',
                                'subtitle' => 'I am being interviewed now. — Мене зараз інтерв\'юють.',
                            ],
                            [
                                'label' => 'He/She/It',
                                'title' => 'is being + V3',
                                'subtitle' => 'The car is being repaired. — Машину ремонтують.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'are being + V3',
                                'subtitle' => 'The documents are being checked. — Документи перевіряються.',
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
                                'description' => 'Порядок слів: <strong>Subject + am/is/are + being + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted at the moment.', 'ua' => 'Будинок зараз фарбується.'],
                                    ['en' => 'The documents are being prepared right now.', 'ua' => 'Документи готуються прямо зараз.'],
                                    ['en' => 'I am being trained for the new position.', 'ua' => 'Мене навчають для нової посади.'],
                                    ['en' => 'The road is being repaired this week.', 'ua' => 'Дорогу ремонтують цього тижня.'],
                                ],
                            ],
                            [
                                'label' => 'Тимчасові процеси',
                                'color' => 'sky',
                                'description' => 'Для дій, що <strong>тривають певний період часу</strong>:',
                                'examples' => [
                                    ['en' => 'A new system is being installed this month.', 'ua' => 'Нова система встановлюється цього місяця.'],
                                    ['en' => 'The building is being renovated.', 'ua' => 'Будівля ремонтується (зараз, тимчасово).'],
                                    ['en' => 'New software is being tested at the moment.', 'ua' => 'Нове програмне забезпечення зараз тестується.'],
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
                        'title' => '3. Коли використовувати Present Continuous Passive?',
                        'sections' => [
                            [
                                'label' => 'Дії прямо зараз',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>відбуваються в момент мовлення</strong>:',
                                'examples' => [
                                    ['en' => 'The dinner is being cooked right now.', 'ua' => 'Вечеря готується прямо зараз.'],
                                    ['en' => 'The meeting is being held at the moment.', 'ua' => 'Зустріч проводиться в даний момент.'],
                                    ['en' => 'Your order is being processed.', 'ua' => 'Ваше замовлення обробляється.'],
                                ],
                            ],
                            [
                                'label' => 'Тимчасові ситуації',
                                'color' => 'blue',
                                'description' => 'Для дій, що <strong>тривають певний період</strong>, але не постійно:',
                                'examples' => [
                                    ['en' => 'The bridge is being constructed this year.', 'ua' => 'Міст будується цього року.'],
                                    ['en' => 'New employees are being trained this month.', 'ua' => 'Нові працівники навчаються цього місяця.'],
                                    ['en' => 'The project is being developed at present.', 'ua' => 'Проєкт зараз розробляється.'],
                                ],
                            ],
                            [
                                'label' => 'Зміни та тенденції',
                                'color' => 'amber',
                                'description' => 'Для опису <strong>поточних змін</strong>:',
                                'examples' => [
                                    ['en' => 'More and more trees are being planted.', 'ua' => 'Все більше дерев висаджується.'],
                                    ['en' => 'The old system is being replaced gradually.', 'ua' => 'Стара система поступово замінюється.'],
                                    ['en' => 'Electric cars are being promoted heavily.', 'ua' => 'Електромобілі активно просуваються.'],
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
                                'description' => 'Додаємо <strong>not</strong> після am/is/are: <strong>Subject + am/is/are + not + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report is not being written at the moment.', 'ua' => 'Звіт зараз не пишеться.'],
                                    ['en' => 'The rooms are not being cleaned today.', 'ua' => 'Кімнати сьогодні не прибираються.'],
                                    ['en' => 'I am not being considered for the job.', 'ua' => 'Мене не розглядають на цю посаду.'],
                                    ['en' => 'The problem is not being addressed.', 'ua' => 'Проблема не вирішується.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>isn\'t being / aren\'t being</strong>',
                                'examples' => [
                                    ['en' => "The car isn't being repaired today.", 'ua' => 'Машина сьогодні не ремонтується.'],
                                    ['en' => "The packages aren't being delivered now.", 'ua' => 'Посилки зараз не доставляються.'],
                                    ['en' => "The issue isn't being investigated.", 'ua' => 'Питання не розслідується.'],
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
                                'description' => '<strong>Am/Is/Are</strong> виходить на перше місце: <strong>Am/Is/Are + Subject + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is the house being painted?', 'ua' => 'Будинок фарбується?'],
                                    ['en' => 'Are the documents being checked?', 'ua' => 'Документи перевіряються?'],
                                    ['en' => 'Am I being tested?', 'ua' => 'Мене тестують?'],
                                    ['en' => 'Is the project being developed?', 'ua' => 'Проєкт розробляється?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + am/is/are + Subject + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'What is being built there?', 'ua' => 'Що там будується?'],
                                    ['en' => 'Why is the road being closed?', 'ua' => 'Чому дорога перекривається?'],
                                    ['en' => 'Where are the tests being conducted?', 'ua' => 'Де проводяться тести?'],
                                    ['en' => 'How is the problem being solved?', 'ua' => 'Як вирішується проблема?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + am/is/are (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Is the car being repaired? — Yes, it is. / No, it isn\'t.', 'ua' => 'Машина ремонтується? — Так. / Ні.'],
                                    ['en' => 'Are they being interviewed? — Yes, they are. / No, they aren\'t.', 'ua' => 'Їх інтерв\'юють? — Так. / Ні.'],
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
                        'title' => '6. Порівняння Active vs Passive у Present Continuous',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They are painting the house now.',
                                'ua' => 'Вони зараз фарбують будинок.',
                                'note' => '→ Passive: The house is being painted now.',
                            ],
                            [
                                'en' => 'Active: The manager is checking the documents.',
                                'ua' => 'Менеджер перевіряє документи.',
                                'note' => '→ Passive: The documents are being checked.',
                            ],
                            [
                                'en' => 'Active: Workers are repairing the road.',
                                'ua' => 'Робітники ремонтують дорогу.',
                                'note' => '→ Passive: The road is being repaired.',
                            ],
                            [
                                'en' => 'Active: Someone is interviewing me.',
                                'ua' => 'Хтось мене інтерв\'юває.',
                                'note' => '→ Passive: I am being interviewed.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (now, at the moment, currently) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - Present Simple vs Present Continuous Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Present Simple Passive vs Present Continuous Passive',
                        'intro' => 'Порівняння двох форм — регулярні дії vs дії прямо зараз:',
                        'rows' => [
                            [
                                'en' => 'The house is cleaned every week.',
                                'ua' => 'Будинок прибирається щотижня.',
                                'note' => 'vs: The house is being cleaned now. — Будинок прибирається зараз.',
                            ],
                            [
                                'en' => 'Cars are made in Germany.',
                                'ua' => 'Машини виробляються в Німеччині.',
                                'note' => 'vs: A car is being made for me. — Машина зараз виготовляється для мене.',
                            ],
                            [
                                'en' => 'The report is prepared monthly.',
                                'ua' => 'Звіт готується щомісяця.',
                                'note' => 'vs: The report is being prepared now. — Звіт зараз готується.',
                            ],
                        ],
                        'warning' => '📌 Present Simple = регулярно, завжди. Present Continuous = зараз, у цей момент.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Маркери часу для Present Continuous Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Present Continuous:',
                        'items' => [
                            [
                                'label' => 'Зараз',
                                'title' => 'now, right now, at the moment, currently',
                                'subtitle' => 'The report is being written right now.',
                            ],
                            [
                                'label' => 'Сьогодні',
                                'title' => 'today, this morning, this afternoon',
                                'subtitle' => 'The meeting is being held this afternoon.',
                            ],
                            [
                                'label' => 'Цей період',
                                'title' => 'this week, this month, this year',
                                'subtitle' => 'A new bridge is being built this year.',
                            ],
                            [
                                'label' => 'Тимчасово',
                                'title' => 'at present, for the time being, temporarily',
                                'subtitle' => 'The office is being renovated at present.',
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
                                'wrong' => 'The house is painted now.',
                                'right' => '✅ The house is being painted now.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'The report being is written.',
                                'right' => '✅ The report is being written.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання been замість being.',
                                'wrong' => 'The car is been repaired now.',
                                'right' => '✅ The car is being repaired now.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Плутанина Present Simple та Continuous.',
                                'wrong' => 'The house is being cleaned every day.',
                                'right' => '✅ The house is cleaned every day. (регулярно)',
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
                            'Present Continuous Passive: <strong>am/is/are + being + V3</strong>.',
                            'Вибір be: <strong>I → am</strong>, <strong>He/She/It → is</strong>, <strong>You/We/They → are</strong>.',
                            '<strong>being</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>am/is/are + not + being + V3</strong>.',
                            'Питання: <strong>Am/Is/Are + Subject + being + V3?</strong>',
                            'Використовується для: <strong>дій прямо зараз</strong>, <strong>тимчасових процесів</strong>, <strong>поточних змін</strong>.',
                            'Маркери часу: now, right now, at the moment, currently, this week тощо.',
                            'Не плутай з Present Simple Passive (регулярні дії)!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
