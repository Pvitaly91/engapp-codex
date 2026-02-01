<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

class PassiveVoiceV2FuturePerfectTheorySeeder extends PassiveVoiceV2TensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-future-perfect';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Perfect Passive — Майбутній доконаний пасив',
            'subtitle_html' => '<p><strong>Future Perfect Passive</strong> використовується для опису дій, що <strong>будуть завершені до певного моменту в майбутньому</strong>. Формула: <strong>will have been + V3</strong>.</p>',
            'subtitle_text' => 'Future Perfect Passive (Майбутній доконаний пасив): формула will have been + V3, дії що будуть завершені до певного моменту в майбутньому.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Future Perfect Passive',
                'Майбутній доконаний пасив',
                'will have been',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Future Perfect Passive</strong> (Майбутній доконаний пасив): як утворювати цю форму, коли використовувати та яка відмінність від інших майбутніх часів.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + will have been + V3</strong>:',
                                'example' => 'The project will have been completed by Friday.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + will not have been + V3</strong>:',
                                'example' => 'The report will not have been submitted by then.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Will + Subject + have been + V3?</strong>:',
                                'example' => 'Will the work have been finished by Monday?',
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
                        'title' => '1. Структура Future Perfect Passive',
                        'intro' => '<strong>Will have been</strong> однакове для всіх осіб:',
                        'items' => [
                            [
                                'label' => 'Всі особи',
                                'title' => 'will have been + V3',
                                'subtitle' => 'I/You/He/She/It/We/They will have been invited.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'will have been + V3',
                                'subtitle' => 'The report will have been submitted. — Звіт буде поданий.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'will have been + V3',
                                'subtitle' => 'All tasks will have been completed. — Усі завдання будуть виконані.',
                            ],
                            [
                                'label' => 'Ключ',
                                'title' => 'will have been — незмінна частина',
                                'subtitle' => 'will have been завжди стоїть перед V3.',
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
                                'description' => 'Порядок слів: <strong>Subject + will have been + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The project will have been completed by Friday.', 'ua' => 'Проєкт буде завершений до п\'ятниці.'],
                                    ['en' => 'All the documents will have been signed by tomorrow.', 'ua' => 'Усі документи будуть підписані до завтра.'],
                                    ['en' => 'The work will have been finished by the end of the week.', 'ua' => 'Робота буде закінчена до кінця тижня.'],
                                    ['en' => 'The emails will have been sent by 6 PM.', 'ua' => 'Листи будуть надіслані до 18:00.'],
                                ],
                            ],
                            [
                                'label' => 'До певного моменту',
                                'color' => 'sky',
                                'description' => 'Підкреслюємо, що дія <strong>буде завершена до конкретного часу</strong>:',
                                'examples' => [
                                    ['en' => 'By next month, the building will have been renovated.', 'ua' => 'До наступного місяця будівлю відремонтують.'],
                                    ['en' => 'By 2030, many changes will have been made.', 'ua' => 'До 2030 року буде внесено багато змін.'],
                                    ['en' => 'By the time you arrive, dinner will have been prepared.', 'ua' => 'Коли ти приїдеш, вечеря буде готова.'],
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
                        'title' => '3. Коли використовувати Future Perfect Passive?',
                        'sections' => [
                            [
                                'label' => 'Завершення до моменту в майбутньому',
                                'color' => 'emerald',
                                'description' => 'Коли важливо, що дія <strong>буде завершена до певного часу</strong>:',
                                'examples' => [
                                    ['en' => 'The contract will have been signed by Monday.', 'ua' => 'Контракт буде підписаний до понеділка.'],
                                    ['en' => 'The repairs will have been done by next week.', 'ua' => 'Ремонт буде зроблений до наступного тижня.'],
                                    ['en' => 'The order will have been delivered by noon.', 'ua' => 'Замовлення буде доставлене до полудня.'],
                                ],
                            ],
                            [
                                'label' => 'Дедлайни та терміни',
                                'color' => 'blue',
                                'description' => 'Для опису <strong>завершення до дедлайну</strong>:',
                                'examples' => [
                                    ['en' => 'The report will have been submitted before the deadline.', 'ua' => 'Звіт буде поданий до дедлайну.'],
                                    ['en' => 'All tests will have been completed by June.', 'ua' => 'Усі тести будуть завершені до червня.'],
                                    ['en' => 'The budget will have been approved by the end of the quarter.', 'ua' => 'Бюджет буде схвалений до кінця кварталу.'],
                                ],
                            ],
                            [
                                'label' => 'Прогнози на майбутнє',
                                'color' => 'amber',
                                'description' => 'Для <strong>прогнозів</strong> про завершення:',
                                'examples' => [
                                    ['en' => 'By 2050, many problems will have been solved.', 'ua' => 'До 2050 року багато проблем буде вирішено.'],
                                    ['en' => 'The new system will have been implemented by then.', 'ua' => 'На той час нову систему буде впроваджено.'],
                                    ['en' => 'A cure will have been found by the end of the decade.', 'ua' => 'До кінця десятиліття буде знайдено ліки.'],
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
                                'description' => 'Додаємо <strong>not</strong> після will: <strong>Subject + will not have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The project will not have been finished by Friday.', 'ua' => 'Проєкт не буде закінчений до п\'ятниці.'],
                                    ['en' => 'The documents will not have been signed by then.', 'ua' => 'Документи не будуть підписані до того часу.'],
                                    ['en' => 'The report will not have been submitted before the deadline.', 'ua' => 'Звіт не буде поданий до дедлайну.'],
                                    ['en' => 'All tasks will not have been completed by Monday.', 'ua' => 'Не всі завдання будуть виконані до понеділка.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочена форма',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>won\'t have been</strong>',
                                'examples' => [
                                    ['en' => "The work won't have been finished by tomorrow.", 'ua' => 'Робота не буде закінчена до завтра.'],
                                    ['en' => "The email won't have been sent by 5 PM.", 'ua' => 'Лист не буде надісланий до 17:00.'],
                                    ['en' => "The decision won't have been made by then.", 'ua' => 'Рішення не буде прийняте до того часу.'],
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
                                'description' => '<strong>Will + Subject + have been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the project have been completed by Friday?', 'ua' => 'Проєкт буде завершений до п\'ятниці?'],
                                    ['en' => 'Will all documents have been signed by tomorrow?', 'ua' => 'Усі документи будуть підписані до завтра?'],
                                    ['en' => 'Will the work have been finished by then?', 'ua' => 'Робота буде закінчена до того часу?'],
                                    ['en' => 'Will the order have been delivered by noon?', 'ua' => 'Замовлення буде доставлене до полудня?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + will + Subject + have been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'What will have been done by Friday?', 'ua' => 'Що буде зроблено до п\'ятниці?'],
                                    ['en' => 'How much will have been completed by the deadline?', 'ua' => 'Скільки буде завершено до дедлайну?'],
                                    ['en' => 'When will the report have been submitted?', 'ua' => 'Коли звіт буде поданий?'],
                                    ['en' => 'Why will the project have been delayed?', 'ua' => 'Чому проєкт буде затриманий?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + will (not) have</strong>:',
                                'examples' => [
                                    ['en' => 'Will the email have been sent? — Yes, it will. / No, it won\'t.', 'ua' => 'Лист буде надісланий? — Так. / Ні.'],
                                    ['en' => 'Will they have been informed? — Yes, they will. / No, they won\'t.', 'ua' => 'Їх поінформують? — Так. / Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Порівняння Active vs Passive у Future Perfect',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They will have completed the project.',
                                'ua' => 'Вони завершать проєкт.',
                                'note' => '→ Passive: The project will have been completed.',
                            ],
                            [
                                'en' => 'Active: Someone will have sent the email.',
                                'ua' => 'Хтось надішле лист.',
                                'note' => '→ Passive: The email will have been sent.',
                            ],
                            [
                                'en' => 'Active: The company will have hired new staff.',
                                'ua' => 'Компанія найме нових працівників.',
                                'note' => '→ Passive: New staff will have been hired.',
                            ],
                            [
                                'en' => 'Active: We will have made a decision.',
                                'ua' => 'Ми приймемо рішення.',
                                'note' => '→ Passive: A decision will have been made.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (by Friday, by then, by the end of) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - all Future Passive forms
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Порівняння всіх Future Passive форм',
                        'intro' => 'Різниця між формами майбутнього часу в пасиві:',
                        'rows' => [
                            [
                                'en' => 'Future Simple: The letter will be sent tomorrow.',
                                'ua' => 'Лист надішлють завтра.',
                                'note' => '→ Дія в майбутньому',
                            ],
                            [
                                'en' => 'Future Continuous: The letter will be being written. (рідко)',
                                'ua' => 'Лист будуть писати.',
                                'note' => '→ Тривала дія в момент майбутнього (рідко!)',
                            ],
                            [
                                'en' => 'Future Perfect: The letter will have been sent by 5 PM.',
                                'ua' => 'Лист буде надісланий до 17:00.',
                                'note' => '→ Буде завершено до певного моменту',
                            ],
                        ],
                        'warning' => '📌 Future Perfect Passive підкреслює ЗАВЕРШЕННЯ до конкретного часу!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Маркери часу для Future Perfect Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Future Perfect:',
                        'items' => [
                            [
                                'label' => 'До',
                                'title' => 'by + час (by Friday, by 5 PM, by tomorrow)',
                                'subtitle' => 'The report will have been submitted by Friday.',
                            ],
                            [
                                'label' => 'До кінця',
                                'title' => 'by the end of + період',
                                'subtitle' => 'The project will have been completed by the end of the month.',
                            ],
                            [
                                'label' => 'До того часу',
                                'title' => 'by then, by that time',
                                'subtitle' => 'All tasks will have been finished by then.',
                            ],
                            [
                                'label' => 'До року',
                                'title' => 'by 2030, by next year',
                                'subtitle' => 'Many changes will have been made by 2030.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск been.',
                                'wrong' => 'The report will have completed.',
                                'right' => '✅ The report will have been completed.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина been та being.',
                                'wrong' => 'The work will have being done.',
                                'right' => '✅ The work will have been done.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний порядок.',
                                'wrong' => 'The letter will been have sent.',
                                'right' => '✅ The letter will have been sent.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Future Simple замість Future Perfect.',
                                'wrong' => 'The project will be finished by Friday. (якщо важливе завершення)',
                                'right' => '✅ The project will have been finished by Friday.',
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
                        'title' => '10. Короткий конспект',
                        'items' => [
                            'Future Perfect Passive: <strong>will have been + V3</strong>.',
                            '<strong>Will have been</strong> — однакове для всіх осіб.',
                            '<strong>been</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>will not (won\'t) have been + V3</strong>.',
                            'Питання: <strong>Will + Subject + have been + V3?</strong>',
                            'Використовується для: <strong>дій, що будуть завершені до певного моменту</strong>.',
                            'Маркери часу: by Friday, by then, by the end of, by 2030 тощо.',
                            'Підкреслює <strong>ЗАВЕРШЕННЯ</strong> до конкретного часу в майбутньому.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
