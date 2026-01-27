<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceExtendedGrammarKeyTensesTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-key-tenses';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасив у ключових часах — Passive in Key Tenses',
            'subtitle_html' => '<p><strong>Passive in Key Tenses</strong> — огляд пасивного стану в основних часах: Present/Past Continuous (is being done), Present Perfect (has been done), Future (will be done).</p>',
            'subtitle_text' => 'Пасивний стан у ключових часах: Continuous (is being done), Perfect (has been done), Future (will be done). Огляд всіх основних часів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-extended-grammar',
                'title' => 'Розширення граматики — Пасив у всіх часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Continuous Passive',
                'Perfect Passive',
                'Future Passive',
                'Tenses',
                'Часи',
                'B1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти вивчиш <strong>пасивний стан у ключових часах</strong>: Continuous (тривалі), Perfect (завершені), та Future (майбутній).',
                        'rules' => [
                            [
                                'label' => 'Continuous',
                                'color' => 'emerald',
                                'text' => '<strong>be + being + V3</strong> — дія в процесі:',
                                'example' => 'The house is being painted.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'blue',
                                'text' => '<strong>have/had + been + V3</strong> — завершена дія:',
                                'example' => 'The letter has been sent.',
                            ],
                            [
                                'label' => 'Future',
                                'color' => 'rose',
                                'text' => '<strong>will + be + V3</strong> — майбутня дія:',
                                'example' => 'The work will be done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Present Continuous Passive — is being done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted right now.', 'ua' => 'Будинок фарбується прямо зараз.'],
                                    ['en' => 'The documents are being prepared at the moment.', 'ua' => 'Документи готуються в даний момент.'],
                                    ['en' => 'I am being interviewed for a new position.', 'ua' => 'Мене інтерв\'юють на нову посаду.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>відбувається зараз</strong>, у процесі. Тривала дія в пасивному стані.',
                                'examples' => [
                                    ['en' => 'The road is being repaired this week.', 'ua' => 'Дорогу ремонтують цього тижня.'],
                                    ['en' => 'New software is being installed on all computers.', 'ua' => 'Нове програмне забезпечення встановлюється на всі комп\'ютери.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Neg: <strong>am/is/are + not + being + V3</strong>. Q: <strong>Am/Is/Are + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The house is not being painted today.', 'ua' => 'Будинок не фарбується сьогодні.'],
                                    ['en' => 'Is the project being worked on?', 'ua' => 'Над проєктом працюють?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Past Continuous Passive — was being done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The car was being repaired when I arrived.', 'ua' => 'Машину ремонтували, коли я прийшов.'],
                                    ['en' => 'The rooms were being cleaned all morning.', 'ua' => 'Кімнати прибиралися весь ранок.'],
                                    ['en' => 'I was being examined by the doctor.', 'ua' => 'Мене оглядав лікар.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>тривала в минулому</strong> в певний момент. Процес, що відбувався.',
                                'examples' => [
                                    ['en' => 'While I was waiting, my application was being processed.', 'ua' => 'Поки я чекав, мою заявку обробляли.'],
                                    ['en' => 'The building was being constructed last year.', 'ua' => 'Будівля будувалася минулого року.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Neg: <strong>was/were + not + being + V3</strong>. Q: <strong>Was/Were + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The issue was not being discussed then.', 'ua' => 'Питання не обговорювалося тоді.'],
                                    ['en' => 'Were the emails being sent?', 'ua' => 'Електронні листи надсилалися?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Present Perfect Passive — has been done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>has/have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The project has been completed successfully.', 'ua' => 'Проєкт успішно завершено.'],
                                    ['en' => 'All the tickets have been sold already.', 'ua' => 'Усі квитки вже продано.'],
                                    ['en' => 'I have been promoted to manager!', 'ua' => 'Мене підвищили до менеджера!'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>завершилась</strong>, і результат важливий зараз. Зв\'язок з теперішнім.',
                                'examples' => [
                                    ['en' => 'The report has just been submitted.', 'ua' => 'Звіт щойно подано.'],
                                    ['en' => 'The files have already been deleted.', 'ua' => 'Файли вже видалено.'],
                                    ['en' => 'The work has been done perfectly.', 'ua' => 'Роботу зроблено ідеально.'],
                                ],
                            ],
                            [
                                'label' => 'Маркери часу',
                                'color' => 'amber',
                                'description' => 'Типові слова: <strong>just, already, yet, recently, ever, never, since, for</strong>',
                                'examples' => [
                                    ['en' => 'The email has not been sent yet.', 'ua' => 'Електронний лист ще не надіслано.'],
                                    ['en' => 'Have you ever been interviewed?', 'ua' => 'Тебе коли-небудь інтерв\'ювали?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Past Perfect Passive — had been done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>had + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work had been finished before the deadline.', 'ua' => 'Роботу було закінчено до дедлайну.'],
                                    ['en' => 'The tickets had been sold out before we arrived.', 'ua' => 'Квитки були розпродані до нашого приїзду.'],
                                    ['en' => 'The documents had been signed earlier.', 'ua' => 'Документи були підписані раніше.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія завершилась <strong>до іншої минулої дії</strong> або моменту.',
                                'examples' => [
                                    ['en' => 'By the time I got there, the problem had been solved.', 'ua' => 'На момент мого приходу проблему вже вирішили.'],
                                    ['en' => 'The email had been deleted before I could read it.', 'ua' => 'Лист був видалений до того, як я міг його прочитати.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Neg: <strong>had + not + been + V3</strong>. Q: <strong>Had + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The work had not been completed by then.', 'ua' => 'Робота не була завершена до того часу.'],
                                    ['en' => 'Had the letter been sent?', 'ua' => 'Лист був надісланий?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Future Simple Passive — will be done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                    ['en' => 'The meeting will be held next Monday.', 'ua' => 'Зустріч відбудеться наступного понеділка.'],
                                    ['en' => 'You will be contacted soon.', 'ua' => 'З вами скоро зв\'яжуться.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>відбудеться в майбутньому</strong>. План, прогноз, обіцянка в пасиві.',
                                'examples' => [
                                    ['en' => 'The work will be done by Friday.', 'ua' => 'Робота буде зроблена до п\'ятниці.'],
                                    ['en' => 'A new office will be opened next year.', 'ua' => 'Новий офіс буде відкритий наступного року.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Neg: <strong>will not (won\'t) + be + V3</strong>. Q: <strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершено вчасно.'],
                                    ['en' => 'Will the report be ready by Friday?', 'ua' => 'Звіт буде готовий до п\'ятниці?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Future Perfect Passive — will have been done',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will have + been + Past Participle (V3)</strong>. Рідко використовується.',
                                'examples' => [
                                    ['en' => 'The work will have been completed by next week.', 'ua' => 'Робота буде завершена до наступного тижня.'],
                                    ['en' => 'By tomorrow, the report will have been submitted.', 'ua' => 'До завтра звіт буде поданий.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>завершиться до певного моменту в майбутньому</strong>. Рідко зустрічається.',
                                'examples' => [
                                    ['en' => 'By 6 PM, all emails will have been sent.', 'ua' => 'До 18:00 усі листи будуть надіслані.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Зведена таблиця всіх часів у пасиві',
                        'intro' => 'Структури пасивного стану в усіх основних часах:',
                        'rows' => [
                            [
                                'en' => 'Present Simple',
                                'ua' => 'am/is/are + V3',
                                'note' => 'The letter is written.',
                            ],
                            [
                                'en' => 'Past Simple',
                                'ua' => 'was/were + V3',
                                'note' => 'The letter was written.',
                            ],
                            [
                                'en' => 'Present Continuous',
                                'ua' => 'am/is/are + being + V3',
                                'note' => 'The letter is being written.',
                            ],
                            [
                                'en' => 'Past Continuous',
                                'ua' => 'was/were + being + V3',
                                'note' => 'The letter was being written.',
                            ],
                            [
                                'en' => 'Present Perfect',
                                'ua' => 'has/have + been + V3',
                                'note' => 'The letter has been written.',
                            ],
                            [
                                'en' => 'Past Perfect',
                                'ua' => 'had + been + V3',
                                'note' => 'The letter had been written.',
                            ],
                            [
                                'en' => 'Future Simple',
                                'ua' => 'will + be + V3',
                                'note' => 'The letter will be written.',
                            ],
                            [
                                'en' => 'Future Perfect',
                                'ua' => 'will have + been + V3',
                                'note' => 'The letter will have been written.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Які часи не мають пасиву?',
                        'sections' => [
                            [
                                'label' => 'Немає Continuous Perfect',
                                'color' => 'rose',
                                'description' => '<strong>Present Perfect Continuous</strong> та інші Perfect Continuous не мають пасивної форми.',
                                'examples' => [
                                    ['en' => '❌ The letter has been being written. (немає такої форми)', 'ua' => 'Замість цього використовують Present Perfect Passive: has been written'],
                                ],
                            ],
                            [
                                'label' => 'Рідко: Future Continuous',
                                'color' => 'amber',
                                'description' => '<strong>Future Continuous Passive</strong> теоретично існує, але дуже рідко використовується.',
                                'examples' => [
                                    ['en' => 'The house will be being painted. (рідко)', 'ua' => 'Краще використати: The house will be painted.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
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
                                'title' => 'Плутанина з being у Continuous.',
                                'wrong' => 'The house is painted now.',
                                'right' => '✅ The house is being painted now.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск been у Perfect.',
                                'wrong' => 'The work has completed.',
                                'right' => '✅ The work has been completed.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний порядок у Future.',
                                'wrong' => 'It be will done.',
                                'right' => '✅ It will be done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            '<strong>Continuous Passive</strong>: be + being + V3 — дія в процесі (is/was being done).',
                            '<strong>Perfect Passive</strong>: have/had + been + V3 — завершена дія (has/had been done).',
                            '<strong>Future Simple Passive</strong>: will + be + V3 — майбутня дія (will be done).',
                            '<strong>Future Perfect Passive</strong>: will have + been + V3 — рідко використовується.',
                            'Perfect Continuous та деякі складні часи НЕ мають пасивної форми.',
                            'Компонент <strong>be</strong> змінюється за часом, а <strong>V3</strong> залишається незмінним.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
