<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceKeyTensesTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
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
            'title' => 'Passive Voice — Огляд пасиву в основних часах',
            'subtitle_html' => '<p><strong>Passive in Key Tenses</strong> — це огляд пасивного стану в основних часах англійської мови. Тут ти вивчиш пасив у Present/Past Continuous (is being done), Present Perfect (has been done) та Future Simple (will be done).</p>',
            'subtitle_text' => 'Огляд пасивного стану в основних часах: Present/Past Continuous Passive, Present Perfect Passive, Future Simple Passive.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-extended-grammar',
                'title' => 'Розширення граматики — Пасив у всіх часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Continuous Passive',
                'Past Continuous Passive',
                'Present Perfect Passive',
                'Future Simple Passive',
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
                        'intro' => 'У цій темі ти вивчиш <strong>пасивний стан у різних часах</strong>: Continuous (is being done), Perfect (has been done), Future (will be done) та їх заперечення й питання.',
                        'rules' => [
                            [
                                'label' => 'Continuous',
                                'color' => 'emerald',
                                'text' => 'Тривалі часи: <strong>be + being + V3</strong>:',
                                'example' => 'The house is being painted.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'blue',
                                'text' => 'Завершені часи: <strong>have/had + been + V3</strong>:',
                                'example' => 'The letter has been sent.',
                            ],
                            [
                                'label' => 'Future',
                                'color' => 'rose',
                                'text' => 'Майбутній час: <strong>will + be + V3</strong>:',
                                'example' => 'The project will be finished tomorrow.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Present Continuous Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted right now.', 'ua' => 'Будинок фарбується прямо зараз.'],
                                    ['en' => 'The documents are being prepared.', 'ua' => 'Документи готуються.'],
                                    ['en' => 'I am being interviewed at the moment.', 'ua' => 'Мене зараз інтерв\'юють.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>відбувається зараз</strong>, у процесі.',
                                'examples' => [
                                    ['en' => 'The road is being repaired this week.', 'ua' => 'Дорогу ремонтують цього тижня.'],
                                    ['en' => 'New software is being installed.', 'ua' => 'Нове програмне забезпечення встановлюється.'],
                                    ['en' => 'Dinner is being cooked.', 'ua' => 'Вечерю готують.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Заперечення: <strong>am/is/are + not + being + V3</strong>. Питання: <strong>Am/Is/Are + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The car isn\'t being washed now.', 'ua' => 'Машину зараз не миють.'],
                                    ['en' => 'Is the report being written?', 'ua' => 'Звіт пишеться?'],
                                    ['en' => 'Are the guests being served?', 'ua' => 'Гостей обслуговують?'],
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
                        'title' => '2. Past Continuous Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The car was being repaired when I arrived.', 'ua' => 'Машину ремонтували, коли я прийшов.'],
                                    ['en' => 'The rooms were being cleaned all morning.', 'ua' => 'Кімнати прибиралися весь ранок.'],
                                    ['en' => 'The house was being built last year.', 'ua' => 'Будинок будувався минулого року.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>тривала в минулому</strong> в певний момент або період.',
                                'examples' => [
                                    ['en' => 'While I was waiting, my application was being processed.', 'ua' => 'Поки я чекав, мою заявку обробляли.'],
                                    ['en' => 'The bridge was being constructed for two years.', 'ua' => 'Міст будувався два роки.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Заперечення: <strong>was/were + not + being + V3</strong>. Питання: <strong>Was/Were + S + being + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The project wasn\'t being discussed at that time.', 'ua' => 'Проєкт не обговорювався в той час.'],
                                    ['en' => 'Was the dinner being prepared when you called?', 'ua' => 'Вечерю готували, коли ти зателефонував?'],
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
                        'title' => '3. Present Perfect Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>has/have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The project has been completed.', 'ua' => 'Проєкт завершено.'],
                                    ['en' => 'All tickets have been sold.', 'ua' => 'Усі квитки продано.'],
                                    ['en' => 'I have been promoted!', 'ua' => 'Мене підвищили!'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>завершилась</strong>, результат важливий зараз.',
                                'examples' => [
                                    ['en' => 'The report has just been submitted.', 'ua' => 'Звіт щойно подано.'],
                                    ['en' => 'The files have already been deleted.', 'ua' => 'Файли вже видалено.'],
                                    ['en' => 'The problem has finally been solved.', 'ua' => 'Проблему нарешті вирішено.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Заперечення: <strong>has/have + not + been + V3</strong>. Питання: <strong>Has/Have + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The work hasn\'t been finished yet.', 'ua' => 'Роботу ще не закінчено.'],
                                    ['en' => 'Has the email been sent?', 'ua' => 'Електронний лист надіслано?'],
                                    ['en' => 'Have the documents been signed?', 'ua' => 'Документи підписано?'],
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
                        'title' => '4. Past Perfect Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>had + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work had been finished before the deadline.', 'ua' => 'Роботу було закінчено до дедлайну.'],
                                    ['en' => 'The tickets had been sold out before we arrived.', 'ua' => 'Квитки були розпродані до нашого приїзду.'],
                                    ['en' => 'The decision had already been made.', 'ua' => 'Рішення вже було прийнято.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія завершилась <strong>до іншої минулої</strong> дії.',
                                'examples' => [
                                    ['en' => 'By the time I got there, the problem had been solved.', 'ua' => 'На момент мого приходу проблему вже вирішили.'],
                                    ['en' => 'The house had been built before the war.', 'ua' => 'Будинок був побудований до війни.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Заперечення: <strong>had + not + been + V3</strong>. Питання: <strong>Had + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The report hadn\'t been submitted before the meeting.', 'ua' => 'Звіт не було подано до зустрічі.'],
                                    ['en' => 'Had the room been cleaned before the guests arrived?', 'ua' => 'Кімнату прибрали до приїзду гостей?'],
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
                        'title' => '5. Future Simple Passive',
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
                                'description' => 'Дія відбудеться <strong>в майбутньому</strong>.',
                                'examples' => [
                                    ['en' => 'The new bridge will be opened in June.', 'ua' => 'Новий міст відкриють у червні.'],
                                    ['en' => 'The winners will be chosen by the judges.', 'ua' => 'Переможців обиратиме журі.'],
                                    ['en' => 'The package will be delivered tomorrow.', 'ua' => 'Посилку доставлять завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'amber',
                                'description' => 'Заперечення: <strong>will + not + be + V3</strong>. Питання: <strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершено вчасно.'],
                                    ['en' => 'Will the report be ready by Friday?', 'ua' => 'Звіт буде готовий до п\'ятниці?'],
                                    ['en' => 'Won\'t the meeting be cancelled?', 'ua' => 'Зустріч не буде скасована?'],
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
                        'title' => '6. Be Going To Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + going to + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The building is going to be renovated.', 'ua' => 'Будівля буде відремонтована.'],
                                    ['en' => 'The roads are going to be repaired.', 'ua' => 'Дороги будуть відремонтовані.'],
                                    ['en' => 'The old factory is going to be demolished.', 'ua' => 'Стара фабрика буде знесена.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Запланована дія в <strong>найближчому майбутньому</strong> або явні ознаки.',
                                'examples' => [
                                    ['en' => 'The party is going to be cancelled. (I can see the signs)', 'ua' => 'Вечірка буде скасована. (Я бачу ознаки)'],
                                    ['en' => 'A new hospital is going to be built here.', 'ua' => 'Тут буде побудована нова лікарня.'],
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
                        'title' => '7. Зведена таблиця часів у пасиві',
                        'intro' => 'Структури пасивного стану в різних часах:',
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
                                'en' => 'Be Going To',
                                'ua' => 'am/is/are + going to + be + V3',
                                'note' => 'The letter is going to be written.',
                            ],
                        ],
                        'warning' => '📌 Компонент <strong>be</strong> змінюється за часом, <strong>V3</strong> залишається незмінним!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск being у Continuous.',
                                'wrong' => 'The house is painted now.',
                                'right' => '✅ The house is being painted now.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск been у Perfect.',
                                'wrong' => 'The work has finished.',
                                'right' => '✅ The work has been finished.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутанина часів be.',
                                'wrong' => 'The report has being sent.',
                                'right' => '✅ The report has been sent.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Present Continuous Passive</strong>: am/is/are + being + V3 (is being done).',
                            '<strong>Past Continuous Passive</strong>: was/were + being + V3 (was being done).',
                            '<strong>Present Perfect Passive</strong>: has/have + been + V3 (has been done).',
                            '<strong>Past Perfect Passive</strong>: had + been + V3 (had been done).',
                            '<strong>Future Simple Passive</strong>: will + be + V3 (will be done).',
                            '<strong>Be Going To Passive</strong>: am/is/are + going to + be + V3.',
                            'Continuous = <strong>being</strong>, Perfect = <strong>been</strong>.',
                            'Заперечення: додаємо <strong>not</strong> після першого допоміжного.',
                            'Питання: перший допоміжний виходить на перше місце.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з розширеної граматики пасиву',
                        'items' => [
                            [
                                'label' => 'Заперечення та питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Passive з модальними',
                                'current' => false,
                            ],
                            [
                                'label' => 'Passive в основних часах (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
