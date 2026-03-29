<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

class PassiveVoiceFutureContinuousTheorySeeder extends PassiveVoiceTensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-future-continuous';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Continuous Passive — Майбутній тривалий пасив',
            'subtitle_html' => '<p><strong>Future Continuous Passive</strong> використовується для опису дій, що <strong>триватимуть у певний момент у майбутньому</strong>. Ця форма <strong>використовується дуже рідко</strong> через незручну конструкцію. Формула: <strong>will be being + V3</strong>.</p>',
            'subtitle_text' => 'Future Continuous Passive (Майбутній тривалий пасив): формула will be being + V3, рідко вживана форма для тривалих дій у майбутньому.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Future Continuous Passive',
                'Майбутній тривалий пасив',
                'will be being',
                'B2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2',
                        'intro' => 'У цій темі ти детально вивчиш <strong>Future Continuous Passive</strong> (Майбутній тривалий пасив). <strong>Важливо:</strong> ця форма використовується дуже рідко в реальному спілкуванні через незручну конструкцію.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + will be being + V3</strong>:',
                                'example' => 'The house will be being painted at this time tomorrow.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + will not be being + V3</strong>:',
                                'example' => 'The report will not be being written at that moment.',
                            ],
                            [
                                'label' => '⚠️ Увага',
                                'color' => 'amber',
                                'text' => 'Ця форма <strong>майже не використовується</strong>!',
                                'example' => 'Краще: The house will be painted tomorrow.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - структура
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Структура Future Continuous Passive',
                        'intro' => 'Формула: <strong>will + be + being + V3</strong>. Ця структура незручна, тому рідко вживається:',
                        'items' => [
                            [
                                'label' => 'Всі особи',
                                'title' => 'will be being + V3',
                                'subtitle' => 'I/You/He/She/It/We/They will be being interviewed.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'will be being + V3',
                                'subtitle' => 'The car will be being repaired. — Машину будуть ремонтувати.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'will be being + V3',
                                'subtitle' => 'The documents will be being processed. — Документи будуть обробляти.',
                            ],
                            [
                                'label' => '⚠️ Рідко!',
                                'title' => 'Краще уникати',
                                'subtitle' => 'Зазвичай замінюють на Future Simple Passive.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - ствердження
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Стверджувальні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Порядок слів: <strong>Subject + will be being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The house will be being painted at this time tomorrow.', 'ua' => 'Будинок будуть фарбувати в цей час завтра.'],
                                    ['en' => 'The project will be being discussed at 3 PM.', 'ua' => 'Проєкт будуть обговорювати о 15:00.'],
                                    ['en' => 'The documents will be being reviewed all day.', 'ua' => 'Документи будуть переглядати весь день.'],
                                ],
                            ],
                            [
                                'label' => '⚠️ Практична порада',
                                'color' => 'amber',
                                'description' => '<strong>Краще використовувати Future Simple Passive або активний стан</strong>:',
                                'examples' => [
                                    ['en' => 'Instead: The house will be painted tomorrow.', 'ua' => 'Краще: Будинок пофарбують завтра.'],
                                    ['en' => 'Or active: They will be painting the house.', 'ua' => 'Або активний: Вони будуть фарбувати будинок.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Коли (теоретично) використовувати Future Continuous Passive?',
                        'sections' => [
                            [
                                'label' => 'Тривала дія в момент майбутнього',
                                'color' => 'emerald',
                                'description' => 'Для підкреслення, що дія <strong>триватиме в певний момент</strong>:',
                                'examples' => [
                                    ['en' => 'At 10 AM tomorrow, the meeting will be being held.', 'ua' => 'Завтра о 10 ранку буде проводитися зустріч.'],
                                    ['en' => 'This time next week, the building will be being renovated.', 'ua' => 'В цей час наступного тижня будівлю будуть ремонтувати.'],
                                ],
                            ],
                            [
                                'label' => 'Чому уникають цю форму?',
                                'color' => 'rose',
                                'description' => 'Причини, чому ця форма <strong>рідко використовується</strong>:',
                                'examples' => [
                                    ['en' => 'Too awkward: "will be being" sounds clumsy.', 'ua' => 'Занадто незручно: "will be being" звучить незграбно.'],
                                    ['en' => 'Confusing: Three verb forms in a row (be being done).', 'ua' => 'Заплутано: Три форми дієслова поспіль.'],
                                    ['en' => 'Easier alternatives exist.', 'ua' => 'Існують простіші альтернативи.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Заперечні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після will: <strong>Subject + will not be being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report will not be being written at that moment.', 'ua' => 'Звіт не будуть писати в той момент.'],
                                    ['en' => 'The car will not be being repaired tomorrow afternoon.', 'ua' => 'Машину не будуть ремонтувати завтра вдень.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочена форма',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>won\'t be being</strong> (ще більш незвично)',
                                'examples' => [
                                    ['en' => "The documents won't be being processed then.", 'ua' => 'Документи не будуть обробляти тоді.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Питальні речення',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Will + Subject + be being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the house be being painted at that time?', 'ua' => 'Будинок будуть фарбувати в той час?'],
                                    ['en' => 'Will the project be being discussed tomorrow?', 'ua' => 'Проєкт будуть обговорювати завтра?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + will + Subject + be being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'What will be being done at 5 PM?', 'ua' => 'Що будуть робити о 17:00?'],
                                    ['en' => 'Where will the meeting be being held?', 'ua' => 'Де буде проводитися зустріч?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Alternatives
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Кращі альтернативи',
                        'intro' => 'Замість Future Continuous Passive краще використовувати:',
                        'rows' => [
                            [
                                'en' => '❌ The house will be being painted.',
                                'ua' => '(незграбно)',
                                'note' => '✅ The house will be painted. (Future Simple Passive)',
                            ],
                            [
                                'en' => '❌ The report will be being written.',
                                'ua' => '(занадто складно)',
                                'note' => '✅ They will be writing the report. (Future Continuous Active)',
                            ],
                            [
                                'en' => '❌ The car will be being repaired.',
                                'ua' => '(важко вимовити)',
                                'note' => '✅ The car will be repaired. / The car is going to be repaired.',
                            ],
                        ],
                        'warning' => '📌 У 99% випадків краще використовувати Future Simple Passive або Future Continuous Active!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Active vs Passive у Future Continuous',
                        'intro' => 'Порівняння активного та пасивного стану:',
                        'rows' => [
                            [
                                'en' => 'Active: They will be painting the house.',
                                'ua' => 'Вони будуть фарбувати будинок.',
                                'note' => '→ Passive: The house will be being painted. (рідко)',
                            ],
                            [
                                'en' => 'Active: We will be discussing the project.',
                                'ua' => 'Ми будемо обговорювати проєкт.',
                                'note' => '→ Passive: The project will be being discussed. (рідко)',
                            ],
                            [
                                'en' => 'Active: Someone will be repairing the car.',
                                'ua' => 'Хтось буде ремонтувати машину.',
                                'note' => '→ Passive: The car will be being repaired. (рідко)',
                            ],
                        ],
                        'warning' => '📌 Активний стан Future Continuous звучить набагато природніше!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск being.',
                                'wrong' => 'The house will be painted now. (якщо потрібен Continuous)',
                                'right' => '✅ The house will be being painted now. (або краще: is being painted)',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок.',
                                'wrong' => 'The report will being be written.',
                                'right' => '✅ The report will be being written.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання been замість being.',
                                'wrong' => 'The car will be been repaired.',
                                'right' => '✅ The car will be being repaired.',
                            ],
                            [
                                'label' => 'Порада',
                                'color' => 'emerald',
                                'title' => 'Краща стратегія.',
                                'wrong' => 'Намагатися використовувати Future Continuous Passive.',
                                'right' => '✅ Використовувати Future Simple Passive або Active.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            'Future Continuous Passive: <strong>will be being + V3</strong>.',
                            '⚠️ Ця форма <strong>майже не використовується</strong> в реальному спілкуванні.',
                            'Причина: конструкція <strong>занадто незграбна</strong> (три форми поспіль).',
                            'Заперечення: <strong>will not be being + V3</strong>.',
                            'Питання: <strong>Will + Subject + be being + V3?</strong>',
                            '<strong>Краще використовувати</strong>: Future Simple Passive (will be done).',
                            '<strong>Або</strong>: Future Continuous Active (will be doing).',
                            'Знати цю форму корисно для розуміння, але <strong>активно використовувати не потрібно</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
