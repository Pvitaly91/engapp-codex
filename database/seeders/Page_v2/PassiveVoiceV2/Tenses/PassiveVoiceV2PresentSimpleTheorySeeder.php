<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

class PassiveVoiceV2PresentSimpleTheorySeeder extends PassiveVoiceV2TensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-present-simple';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Simple Passive — Теперішній простий пасив',
            'subtitle_html' => '<p><strong>Present Simple Passive</strong> використовується для опису регулярних, звичних дій або загальних фактів у пасивному стані. Це найбільш базова форма пасиву, яку потрібно знати кожному.</p>',
            'subtitle_text' => 'Present Simple Passive (Теперішній простий пасив): формула am/is/are + V3, ствердження, заперечення, питання та практичні приклади використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Simple Passive',
                'Теперішній простий пасив',
                'am is are',
                'A2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти детально вивчиш <strong>Present Simple Passive</strong> (Теперішній простий пасив): як утворювати ствердження, заперечення та питання, коли використовувати цю форму та які типові помилки потрібно уникати.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + am/is/are + V3 (Past Participle)</strong>:',
                                'example' => 'The letter is written every day.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + am/is/are + not + V3</strong>:',
                                'example' => 'The letter is not written by hand.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Am/Is/Are + Subject + V3?</strong>:',
                                'example' => 'Is the letter written in English?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - вибір am/is/are
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Вибір допоміжного дієслова am/is/are',
                        'intro' => 'Форма дієслова <strong>be</strong> залежить від підмета речення:',
                        'items' => [
                            [
                                'label' => 'I',
                                'title' => 'am + V3',
                                'subtitle' => 'I am paid every month. — Мені платять щомісяця.',
                            ],
                            [
                                'label' => 'He/She/It',
                                'title' => 'is + V3',
                                'subtitle' => 'The cake is baked fresh. — Торт випікається свіжим.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'are + V3',
                                'subtitle' => 'Cars are made in Germany. — Машини виробляються в Німеччині.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'are + V3',
                                'subtitle' => 'The letters are sent daily. — Листи надсилаються щодня.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - ствердження
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
                                'description' => 'Порядок слів: <strong>Subject + am/is/are + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійська розмовляється в багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every morning.', 'ua' => 'Кімнати прибираються щоранку.'],
                                    ['en' => 'I am invited to all the meetings.', 'ua' => 'Мене запрошують на всі зустрічі.'],
                                    ['en' => 'This product is made in Italy.', 'ua' => 'Цей продукт виробляється в Італії.'],
                                ],
                            ],
                            [
                                'label' => 'З вказівкою виконавця (by + agent)',
                                'color' => 'sky',
                                'description' => 'Коли важливо вказати <strong>хто виконує дію</strong>:',
                                'examples' => [
                                    ['en' => 'This book is written by a famous author.', 'ua' => 'Ця книга написана відомим автором.'],
                                    ['en' => 'The dinner is cooked by my mother.', 'ua' => 'Вечеря готується моєю мамою.'],
                                    ['en' => 'The report is prepared by the team.', 'ua' => 'Звіт готується командою.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати Present Simple Passive?',
                        'sections' => [
                            [
                                'label' => 'Регулярні дії',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>відбуваються регулярно</strong>:',
                                'examples' => [
                                    ['en' => 'Newspapers are delivered every morning.', 'ua' => 'Газети доставляються щоранку.'],
                                    ['en' => 'The office is cleaned twice a week.', 'ua' => 'Офіс прибирається двічі на тиждень.'],
                                    ['en' => 'Bills are paid at the end of each month.', 'ua' => 'Рахунки оплачуються наприкінці кожного місяця.'],
                                ],
                            ],
                            [
                                'label' => 'Загальні факти та істини',
                                'color' => 'blue',
                                'description' => 'Для <strong>загальновідомих фактів</strong>:',
                                'examples' => [
                                    ['en' => 'Coffee is grown in Brazil.', 'ua' => 'Каву вирощують у Бразилії.'],
                                    ['en' => 'Gold is found in South Africa.', 'ua' => 'Золото видобувається в Південній Африці.'],
                                    ['en' => 'Water is used by all living things.', 'ua' => 'Вода використовується всіма живими істотами.'],
                                ],
                            ],
                            [
                                'label' => 'Процеси та інструкції',
                                'color' => 'amber',
                                'description' => 'Для опису <strong>процесів</strong> та <strong>інструкцій</strong>:',
                                'examples' => [
                                    ['en' => 'The data is processed automatically.', 'ua' => 'Дані обробляються автоматично.'],
                                    ['en' => 'First, the ingredients are mixed.', 'ua' => 'Спочатку інгредієнти змішуються.'],
                                    ['en' => 'The application is reviewed within 5 days.', 'ua' => 'Заявка розглядається протягом 5 днів.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Заперечні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після am/is/are: <strong>Subject + am/is/are + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked at night.', 'ua' => 'Двері не замикаються вночі.'],
                                    ['en' => 'These products are not sold online.', 'ua' => 'Ці продукти не продаються онлайн.'],
                                    ['en' => 'I am not included in the team.', 'ua' => 'Мене не включено до команди.'],
                                    ['en' => 'The report is not finished yet.', 'ua' => 'Звіт ще не завершено.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові використовують <strong>isn\'t / aren\'t</strong>:',
                                'examples' => [
                                    ['en' => "The window isn't opened in winter.", 'ua' => 'Вікно не відкривається взимку.'],
                                    ['en' => "The documents aren't signed.", 'ua' => 'Документи не підписані.'],
                                    ['en' => "This service isn't provided here.", 'ua' => 'Ця послуга тут не надається.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Питальні речення',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Am/Is/Are</strong> виходить на перше місце: <strong>Am/Is/Are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the rooms cleaned daily?', 'ua' => 'Кімнати прибираються щодня?'],
                                    ['en' => 'Am I included in the list?', 'ua' => 'Мене включено до списку?'],
                                    ['en' => 'Is this product made locally?', 'ua' => 'Цей продукт виробляється локально?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + am/is/are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Where is coffee grown?', 'ua' => 'Де вирощують каву?'],
                                    ['en' => 'How often are the rooms cleaned?', 'ua' => 'Як часто прибираються кімнати?'],
                                    ['en' => 'When are the bills paid?', 'ua' => 'Коли оплачуються рахунки?'],
                                    ['en' => 'Why is this rule used?', 'ua' => 'Чому використовується це правило?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + am/is/are (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Is the letter sent? — Yes, it is. / No, it isn\'t.', 'ua' => 'Лист відправлено? — Так. / Ні.'],
                                    ['en' => 'Are they invited? — Yes, they are. / No, they aren\'t.', 'ua' => 'Їх запросили? — Так. / Ні.'],
                                    ['en' => 'Am I needed? — Yes, you are. / No, you aren\'t.', 'ua' => 'Я потрібен? — Так. / Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Порівняння Active vs Passive у Present Simple',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom writes letters every day.',
                                'ua' => 'Том пише листи щодня.',
                                'note' => '→ Passive: Letters are written (by Tom) every day.',
                            ],
                            [
                                'en' => 'Active: They clean the office daily.',
                                'ua' => 'Вони прибирають офіс щодня.',
                                'note' => '→ Passive: The office is cleaned daily.',
                            ],
                            [
                                'en' => 'Active: The company produces cars.',
                                'ua' => 'Компанія виробляє машини.',
                                'note' => '→ Passive: Cars are produced by the company.',
                            ],
                            [
                                'en' => 'Active: People speak English here.',
                                'ua' => 'Люди тут розмовляють англійською.',
                                'note' => '→ Passive: English is spoken here.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (every day, always, often, usually) залишаються в реченні!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Маркери часу для Present Simple Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Present Simple:',
                        'items' => [
                            [
                                'label' => 'Частота',
                                'title' => 'always, often, usually, sometimes, rarely, never',
                                'subtitle' => 'Letters are always sent on time.',
                            ],
                            [
                                'label' => 'Періодичність',
                                'title' => 'every day/week/month/year',
                                'subtitle' => 'The report is prepared every month.',
                            ],
                            [
                                'label' => 'Розклад',
                                'title' => 'on Mondays, in the morning, twice a week',
                                'subtitle' => 'The office is cleaned twice a week.',
                            ],
                            [
                                'label' => 'Загальні',
                                'title' => 'generally, normally, typically',
                                'subtitle' => 'Coffee is typically grown in warm climates.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
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
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Зайве використання do/does.',
                                'wrong' => 'Does the letter is written?',
                                'right' => '✅ Is the letter written?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            'Present Simple Passive: <strong>am/is/are + V3 (Past Participle)</strong>.',
                            'Вибір be: <strong>I → am</strong>, <strong>He/She/It → is</strong>, <strong>You/We/They/Plural → are</strong>.',
                            'Заперечення: <strong>am/is/are + not + V3</strong> (скорочено: isn\'t, aren\'t).',
                            'Питання: <strong>Am/Is/Are + Subject + V3?</strong>',
                            'Wh-питання: <strong>Wh-word + am/is/are + Subject + V3?</strong>',
                            'Використовується для: <strong>регулярних дій</strong>, <strong>загальних фактів</strong>, <strong>процесів</strong>.',
                            'Маркери часу: every day, always, often, usually, twice a week тощо.',
                            '<strong>by + agent</strong> додається, коли важливо вказати виконавця.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
