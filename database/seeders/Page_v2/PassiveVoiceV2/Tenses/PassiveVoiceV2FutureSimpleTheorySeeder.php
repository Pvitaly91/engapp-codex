<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

class PassiveVoiceV2FutureSimpleTheorySeeder extends PassiveVoiceV2TensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-future-simple';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Simple Passive — Майбутній простий пасив',
            'subtitle_html' => '<p><strong>Future Simple Passive</strong> використовується для опису дій, що <strong>відбудуться в майбутньому</strong> в пасивному стані. Формула: <strong>will be + V3</strong>.</p>',
            'subtitle_text' => 'Future Simple Passive (Майбутній простий пасив): формула will be + V3, ствердження, заперечення, питання та практичні приклади використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Future Simple Passive',
                'Майбутній простий пасив',
                'will be',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Future Simple Passive</strong> (Майбутній простий пасив): як утворювати ствердження, заперечення та питання, коли використовувати цю форму та які типові помилки потрібно уникати.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + will be + V3 (Past Participle)</strong>:',
                                'example' => 'The letter will be sent tomorrow.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + will not (won\'t) be + V3</strong>:',
                                'example' => 'The letter will not be sent today.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Will + Subject + be + V3?</strong>:',
                                'example' => 'Will the letter be sent on time?',
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
                        'title' => '1. Структура Future Simple Passive',
                        'intro' => '<strong>Will</strong> однакове для всіх осіб, а <strong>be</strong> залишається незмінним:',
                        'items' => [
                            [
                                'label' => 'Всі особи',
                                'title' => 'will be + V3',
                                'subtitle' => 'I/You/He/She/It/We/They will be invited.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'will be + V3',
                                'subtitle' => 'The report will be submitted. — Звіт буде поданий.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'will be + V3',
                                'subtitle' => 'All tasks will be completed. — Усі завдання будуть виконані.',
                            ],
                            [
                                'label' => 'Ключ',
                                'title' => 'will be — незмінна частина',
                                'subtitle' => 'will be завжди стоїть перед V3.',
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
                                'description' => 'Порядок слів: <strong>Subject + will be + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                    ['en' => 'The meeting will be held next Monday.', 'ua' => 'Зустріч відбудеться наступного понеділка.'],
                                    ['en' => 'You will be contacted soon.', 'ua' => 'З вами скоро зв\'яжуться.'],
                                    ['en' => 'The project will be completed by the team.', 'ua' => 'Проєкт буде завершений командою.'],
                                ],
                            ],
                            [
                                'label' => 'З вказівкою виконавця',
                                'color' => 'sky',
                                'description' => 'Коли важливо вказати <strong>хто виконає дію</strong>:',
                                'examples' => [
                                    ['en' => 'The decision will be made by the board.', 'ua' => 'Рішення прийме рада.'],
                                    ['en' => 'The cake will be baked by my mother.', 'ua' => 'Торт спече моя мама.'],
                                    ['en' => 'The email will be sent by the secretary.', 'ua' => 'Лист надішле секретар.'],
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
                        'title' => '3. Коли використовувати Future Simple Passive?',
                        'sections' => [
                            [
                                'label' => 'Плани та прогнози',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>відбудуться в майбутньому</strong>:',
                                'examples' => [
                                    ['en' => 'The new bridge will be built next year.', 'ua' => 'Новий міст побудують наступного року.'],
                                    ['en' => 'The package will be delivered tomorrow.', 'ua' => 'Посилку доставлять завтра.'],
                                    ['en' => 'The guests will be welcomed at the entrance.', 'ua' => 'Гостей зустрінуть біля входу.'],
                                ],
                            ],
                            [
                                'label' => 'Офіційні оголошення',
                                'color' => 'blue',
                                'description' => 'У <strong>офіційних повідомленнях та оголошеннях</strong>:',
                                'examples' => [
                                    ['en' => 'The winner will be announced at 5 PM.', 'ua' => 'Переможця оголосять о 17:00.'],
                                    ['en' => 'All applicants will be notified by email.', 'ua' => 'Усіх заявників повідомлять електронною поштою.'],
                                    ['en' => 'The office will be closed for renovation.', 'ua' => 'Офіс буде закритий на ремонт.'],
                                ],
                            ],
                            [
                                'label' => 'Обіцянки та запевнення',
                                'color' => 'amber',
                                'description' => 'Для <strong>обіцянок</strong> та <strong>запевнень</strong>:',
                                'examples' => [
                                    ['en' => 'Your complaint will be addressed immediately.', 'ua' => 'Вашу скаргу розглянуть негайно.'],
                                    ['en' => 'You will be given a full refund.', 'ua' => 'Вам повернуть повну суму.'],
                                    ['en' => 'The problem will be solved as soon as possible.', 'ua' => 'Проблему вирішать якнайшвидше.'],
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
                                'description' => 'Додаємо <strong>not</strong> після will: <strong>Subject + will not (won\'t) be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершений вчасно.'],
                                    ['en' => 'The documents will not be signed today.', 'ua' => 'Документи не підпишуть сьогодні.'],
                                    ['en' => 'The meeting will not be held tomorrow.', 'ua' => 'Зустріч не відбудеться завтра.'],
                                    ['en' => 'You will not be charged for this service.', 'ua' => 'З вас не візьмуть плату за цю послугу.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочена форма',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>won\'t be</strong>',
                                'examples' => [
                                    ['en' => "The email won't be sent until Monday.", 'ua' => 'Лист не надішлють до понеділка.'],
                                    ['en' => "The tickets won't be available online.", 'ua' => 'Квитки не будуть доступні онлайн.'],
                                    ['en' => "The decision won't be made today.", 'ua' => 'Рішення не приймуть сьогодні.'],
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
                                'description' => '<strong>Will</strong> виходить на перше місце: <strong>Will + Subject + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the report be ready by Friday?', 'ua' => 'Звіт буде готовий до п\'ятниці?'],
                                    ['en' => 'Will the meeting be held online?', 'ua' => 'Зустріч відбудеться онлайн?'],
                                    ['en' => 'Will we be informed about the changes?', 'ua' => 'Нас повідомлять про зміни?'],
                                    ['en' => 'Will the package be delivered tomorrow?', 'ua' => 'Посилку доставлять завтра?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + will + Subject + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'When will the results be announced?', 'ua' => 'Коли оголосять результати?'],
                                    ['en' => 'Where will the event be held?', 'ua' => 'Де відбудеться подія?'],
                                    ['en' => 'How will the problem be solved?', 'ua' => 'Як вирішать проблему?'],
                                    ['en' => 'Why will the office be closed?', 'ua' => 'Чому офіс буде закритий?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + will (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Will the email be sent? — Yes, it will. / No, it won\'t.', 'ua' => 'Лист надішлють? — Так. / Ні.'],
                                    ['en' => 'Will they be invited? — Yes, they will. / No, they won\'t.', 'ua' => 'Їх запросять? — Так. / Ні.'],
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
                        'title' => '6. Порівняння Active vs Passive у Future Simple',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They will send the letter tomorrow.',
                                'ua' => 'Вони надішлють листа завтра.',
                                'note' => '→ Passive: The letter will be sent tomorrow.',
                            ],
                            [
                                'en' => 'Active: The company will hire new staff.',
                                'ua' => 'Компанія найме нових працівників.',
                                'note' => '→ Passive: New staff will be hired.',
                            ],
                            [
                                'en' => 'Active: Someone will repair the car.',
                                'ua' => 'Хтось відремонтує машину.',
                                'note' => '→ Passive: The car will be repaired.',
                            ],
                            [
                                'en' => 'Active: We will hold the meeting next week.',
                                'ua' => 'Ми проведемо зустріч наступного тижня.',
                                'note' => '→ Passive: The meeting will be held next week.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (tomorrow, next week, soon) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Маркери часу для Future Simple Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Future Simple:',
                        'items' => [
                            [
                                'label' => 'Завтра',
                                'title' => 'tomorrow, the day after tomorrow',
                                'subtitle' => 'The report will be submitted tomorrow.',
                            ],
                            [
                                'label' => 'Наступний',
                                'title' => 'next week/month/year',
                                'subtitle' => 'The project will be completed next month.',
                            ],
                            [
                                'label' => 'Скоро',
                                'title' => 'soon, shortly, in a few days',
                                'subtitle' => 'You will be contacted soon.',
                            ],
                            [
                                'label' => 'Конкретний час',
                                'title' => 'at 5 PM, on Monday, in 2025',
                                'subtitle' => 'The results will be announced at 5 PM.',
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
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск be.',
                                'wrong' => 'The letter will sent tomorrow.',
                                'right' => '✅ The letter will be sent tomorrow.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'The report will be write.',
                                'right' => '✅ The report will be written.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний порядок слів у питанні.',
                                'wrong' => 'Will be the meeting held?',
                                'right' => '✅ Will the meeting be held?',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Подвійне will.',
                                'wrong' => 'The project will will be completed.',
                                'right' => '✅ The project will be completed.',
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
                        'title' => '9. Короткий конспект',
                        'items' => [
                            'Future Simple Passive: <strong>will be + V3</strong>.',
                            '<strong>Will</strong> — однакове для всіх осіб.',
                            '<strong>be</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>will not (won\'t) be + V3</strong>.',
                            'Питання: <strong>Will + Subject + be + V3?</strong>',
                            'Використовується для: <strong>майбутніх дій</strong>, <strong>офіційних оголошень</strong>, <strong>обіцянок</strong>.',
                            'Маркери часу: tomorrow, next week, soon, in 2025 тощо.',
                            'Не забувай <strong>be</strong> між will та V3!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
