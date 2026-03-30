<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Tenses;

class PassiveVoicePresentPerfectTheorySeeder extends PassiveVoiceTensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-present-perfect';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Perfect Passive — Теперішній доконаний пасив',
            'subtitle_html' => '<p><strong>Present Perfect Passive</strong> використовується для опису дій, що <strong>завершилися</strong>, але мають <strong>зв\'язок з теперішнім</strong>, або коли важливий <strong>результат дії</strong>. Формула: <strong>has/have + been + V3</strong>.</p>',
            'subtitle_text' => 'Present Perfect Passive (Теперішній доконаний пасив): формула has/have + been + V3, завершені дії з результатом, практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Perfect Passive',
                'Теперішній доконаний пасив',
                'has have been',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Present Perfect Passive</strong> (Теперішній доконаний пасив): як утворювати цю форму, коли використовувати та яка відмінність від інших часів пасиву.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + has/have + been + V3</strong>:',
                                'example' => 'The work has been completed.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + has/have + not + been + V3</strong>:',
                                'example' => 'The letter has not been sent yet.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Has/Have + Subject + been + V3?</strong>:',
                                'example' => 'Has the project been finished?',
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
                        'title' => '1. Структура Present Perfect Passive',
                        'intro' => 'Вибір <strong>has/have</strong> залежить від підмета, а <strong>been</strong> залишається незмінним:',
                        'items' => [
                            [
                                'label' => 'I/You/We/They',
                                'title' => 'have been + V3',
                                'subtitle' => 'The documents have been signed. — Документи підписано.',
                            ],
                            [
                                'label' => 'He/She/It',
                                'title' => 'has been + V3',
                                'subtitle' => 'The report has been submitted. — Звіт подано.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'have been + V3',
                                'subtitle' => 'All tasks have been completed. — Усі завдання виконано.',
                            ],
                            [
                                'label' => 'Ключ',
                                'title' => 'been — незмінна частина',
                                'subtitle' => 'been завжди стоїть перед V3.',
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
                                'description' => 'Порядок слів: <strong>Subject + has/have + been + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The project has been completed successfully.', 'ua' => 'Проєкт успішно завершено.'],
                                    ['en' => 'All the tickets have been sold.', 'ua' => 'Усі квитки продано.'],
                                    ['en' => 'The problem has been solved.', 'ua' => 'Проблему вирішено.'],
                                    ['en' => 'New rules have been introduced.', 'ua' => 'Нові правила запроваджено.'],
                                ],
                            ],
                            [
                                'label' => 'Фокус на результаті',
                                'color' => 'sky',
                                'description' => 'Коли важливий <strong>результат</strong>, а не процес:',
                                'examples' => [
                                    ['en' => 'The email has been sent. (результат: лист відправлено)', 'ua' => 'Лист надіслано.'],
                                    ['en' => 'The decision has been made. (результат: рішення прийнято)', 'ua' => 'Рішення прийнято.'],
                                    ['en' => 'The repair has been done. (результат: ремонт зроблено)', 'ua' => 'Ремонт виконано.'],
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
                        'title' => '3. Коли використовувати Present Perfect Passive?',
                        'sections' => [
                            [
                                'label' => 'Завершені дії з результатом',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>завершилися</strong> і результат <strong>важливий зараз</strong>:',
                                'examples' => [
                                    ['en' => 'The report has been finished. (готово до перегляду)', 'ua' => 'Звіт завершено.'],
                                    ['en' => 'Your application has been approved.', 'ua' => 'Вашу заявку схвалено.'],
                                    ['en' => 'The system has been updated.', 'ua' => 'Систему оновлено.'],
                                ],
                            ],
                            [
                                'label' => 'Нещодавні дії',
                                'color' => 'blue',
                                'description' => 'Для дій, що <strong>щойно відбулися</strong> (just, recently):',
                                'examples' => [
                                    ['en' => 'The email has just been sent.', 'ua' => 'Лист щойно надіслано.'],
                                    ['en' => 'The meeting has recently been scheduled.', 'ua' => 'Зустріч нещодавно заплановано.'],
                                    ['en' => 'New software has just been installed.', 'ua' => 'Нове ПЗ щойно встановлено.'],
                                ],
                            ],
                            [
                                'label' => 'Дії до цього моменту',
                                'color' => 'amber',
                                'description' => 'Для опису того, що <strong>зроблено/не зроблено до цього моменту</strong> (already, yet, ever, never):',
                                'examples' => [
                                    ['en' => 'The file has already been uploaded.', 'ua' => 'Файл уже завантажено.'],
                                    ['en' => 'The payment has not been received yet.', 'ua' => 'Платіж ще не отримано.'],
                                    ['en' => 'Has this book ever been translated?', 'ua' => 'Цю книгу коли-небудь перекладали?'],
                                ],
                            ],
                            [
                                'label' => 'Період до тепер',
                                'color' => 'rose',
                                'description' => 'Для дій за <strong>період часу</strong>, що включає теперішній момент (today, this week, since, for):',
                                'examples' => [
                                    ['en' => 'Many changes have been made this year.', 'ua' => 'Цього року внесено багато змін.'],
                                    ['en' => 'The building has been renovated since 2020.', 'ua' => 'Будівлю ремонтують з 2020 року.'],
                                    ['en' => 'Three emails have been sent today.', 'ua' => 'Сьогодні надіслано три листи.'],
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
                                'description' => 'Додаємо <strong>not</strong> після has/have: <strong>Subject + has/have + not + been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report has not been submitted yet.', 'ua' => 'Звіт ще не подано.'],
                                    ['en' => 'The documents have not been signed.', 'ua' => 'Документи не підписано.'],
                                    ['en' => 'The problem has not been solved.', 'ua' => 'Проблему не вирішено.'],
                                    ['en' => 'The changes have not been approved.', 'ua' => 'Зміни не схвалено.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>hasn\'t been / haven\'t been</strong>',
                                'examples' => [
                                    ['en' => "The email hasn't been sent yet.", 'ua' => 'Лист ще не надіслано.'],
                                    ['en' => "The tasks haven't been completed.", 'ua' => 'Завдання не виконано.'],
                                    ['en' => "The decision hasn't been made.", 'ua' => 'Рішення не прийнято.'],
                                ],
                            ],
                            [
                                'label' => 'Типове використання з yet',
                                'color' => 'sky',
                                'description' => '<strong>yet</strong> (ще) часто вживається в заперечних реченнях:',
                                'examples' => [
                                    ['en' => "The order hasn't been delivered yet.", 'ua' => 'Замовлення ще не доставлено.'],
                                    ['en' => "The results haven't been announced yet.", 'ua' => 'Результати ще не оголошено.'],
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
                                'description' => '<strong>Has/Have</strong> виходить на перше місце: <strong>Has/Have + Subject + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Has the report been finished?', 'ua' => 'Звіт завершено?'],
                                    ['en' => 'Have the documents been signed?', 'ua' => 'Документи підписано?'],
                                    ['en' => 'Has the problem been solved?', 'ua' => 'Проблему вирішено?'],
                                    ['en' => 'Have you ever been interviewed?', 'ua' => 'Тебе коли-небудь інтерв\'ювали?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + has/have + Subject + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'How many books have been sold?', 'ua' => 'Скільки книг продано?'],
                                    ['en' => 'What has been done so far?', 'ua' => 'Що зроблено на даний момент?'],
                                    ['en' => 'Why has the meeting been cancelled?', 'ua' => 'Чому зустріч скасовано?'],
                                    ['en' => 'Where has the money been spent?', 'ua' => 'На що витрачено гроші?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + has/have (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Has the email been sent? — Yes, it has. / No, it hasn\'t.', 'ua' => 'Лист надіслано? — Так. / Ні.'],
                                    ['en' => 'Have they been informed? — Yes, they have. / No, they haven\'t.', 'ua' => 'Їх поінформовано? — Так. / Ні.'],
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
                        'title' => '6. Порівняння Active vs Passive у Present Perfect',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They have completed the project.',
                                'ua' => 'Вони завершили проєкт.',
                                'note' => '→ Passive: The project has been completed.',
                            ],
                            [
                                'en' => 'Active: Someone has stolen my bike.',
                                'ua' => 'Хтось вкрав мій велосипед.',
                                'note' => '→ Passive: My bike has been stolen.',
                            ],
                            [
                                'en' => 'Active: The company has hired new staff.',
                                'ua' => 'Компанія найняла нових працівників.',
                                'note' => '→ Passive: New staff has been hired.',
                            ],
                            [
                                'en' => 'Active: We have made a decision.',
                                'ua' => 'Ми прийняли рішення.',
                                'note' => '→ Passive: A decision has been made.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (just, already, yet, recently) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - всі Present Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Порівняння всіх Present Passive форм',
                        'intro' => 'Різниця між трьома формами теперішнього часу в пасиві:',
                        'rows' => [
                            [
                                'en' => 'Present Simple: The room is cleaned every day.',
                                'ua' => 'Кімната прибирається щодня.',
                                'note' => '→ Регулярна дія',
                            ],
                            [
                                'en' => 'Present Continuous: The room is being cleaned now.',
                                'ua' => 'Кімната прибирається зараз.',
                                'note' => '→ Дія прямо зараз',
                            ],
                            [
                                'en' => 'Present Perfect: The room has been cleaned.',
                                'ua' => 'Кімнату прибрано (результат).',
                                'note' => '→ Завершена дія з результатом',
                            ],
                        ],
                        'warning' => '📌 Present Perfect Passive підкреслює РЕЗУЛЬТАТ дії, а не процес!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Маркери часу для Present Perfect Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Present Perfect:',
                        'items' => [
                            [
                                'label' => 'Щойно',
                                'title' => 'just, recently, lately',
                                'subtitle' => 'The report has just been submitted.',
                            ],
                            [
                                'label' => 'Вже/ще',
                                'title' => 'already, yet, still',
                                'subtitle' => 'The task has already been completed.',
                            ],
                            [
                                'label' => 'Досвід',
                                'title' => 'ever, never, before',
                                'subtitle' => 'Has this method ever been used before?',
                            ],
                            [
                                'label' => 'Період',
                                'title' => 'this week/month/year, today, since, for',
                                'subtitle' => 'Many improvements have been made this year.',
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
                                'title' => 'Пропуск been.',
                                'wrong' => 'The report has completed.',
                                'right' => '✅ The report has been completed.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний вибір has/have.',
                                'wrong' => 'The documents has been signed.',
                                'right' => '✅ The documents have been signed.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутанина been та being.',
                                'wrong' => 'The work has being done.',
                                'right' => '✅ The work has been done.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Використання Past Simple замість Present Perfect.',
                                'wrong' => 'The email was sent already.',
                                'right' => '✅ The email has already been sent.',
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
                            'Present Perfect Passive: <strong>has/have + been + V3</strong>.',
                            'Вибір: <strong>I/You/We/They → have</strong>, <strong>He/She/It → has</strong>.',
                            '<strong>been</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>has/have + not + been + V3</strong> (hasn\'t, haven\'t).',
                            'Питання: <strong>Has/Have + Subject + been + V3?</strong>',
                            'Використовується для: <strong>завершених дій з результатом</strong>, <strong>нещодавніх подій</strong>, <strong>досвіду</strong>.',
                            'Маркери часу: just, already, yet, recently, ever, never, this week, since, for.',
                            'Фокус на <strong>РЕЗУЛЬТАТІ</strong>, а не на процесі!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
