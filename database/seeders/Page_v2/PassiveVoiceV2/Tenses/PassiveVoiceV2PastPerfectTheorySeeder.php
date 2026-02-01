<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

class PassiveVoiceV2PastPerfectTheorySeeder extends PassiveVoiceV2TensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-past-perfect';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Perfect Passive — Минулий доконаний пасив',
            'subtitle_html' => '<p><strong>Past Perfect Passive</strong> використовується для опису дій, що <strong>завершилися до іншої минулої дії</strong> або <strong>до певного моменту в минулому</strong>. Формула: <strong>had + been + V3</strong>.</p>',
            'subtitle_text' => 'Past Perfect Passive (Минулий доконаний пасив): формула had + been + V3, дії що передували іншим минулим діям.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Past Perfect Passive',
                'Минулий доконаний пасив',
                'had been',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Past Perfect Passive</strong> (Минулий доконаний пасив): як утворювати цю форму, коли використовувати та яка відмінність від інших минулих часів.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + had + been + V3</strong>:',
                                'example' => 'The work had been completed before the deadline.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + had + not + been + V3</strong>:',
                                'example' => 'The letter had not been sent before he left.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Had + Subject + been + V3?</strong>:',
                                'example' => 'Had the project been finished before the meeting?',
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
                        'title' => '1. Структура Past Perfect Passive',
                        'intro' => '<strong>Had</strong> однакове для всіх осіб, а <strong>been</strong> залишається незмінним:',
                        'items' => [
                            [
                                'label' => 'Всі особи',
                                'title' => 'had been + V3',
                                'subtitle' => 'I/You/He/She/It/We/They had been invited.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'had been + V3',
                                'subtitle' => 'The report had been submitted. — Звіт було подано.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'had been + V3',
                                'subtitle' => 'All tasks had been completed. — Усі завдання було виконано.',
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
                                'description' => 'Порядок слів: <strong>Subject + had + been + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The project had been completed before the deadline.', 'ua' => 'Проєкт було завершено до дедлайну.'],
                                    ['en' => 'All the tickets had been sold before we arrived.', 'ua' => 'Усі квитки були розпродані до нашого приїзду.'],
                                    ['en' => 'The problem had been solved by the time they called.', 'ua' => 'Проблему вирішили до того, як вони подзвонили.'],
                                    ['en' => 'The documents had been signed by the manager.', 'ua' => 'Документи підписав менеджер.'],
                                ],
                            ],
                            [
                                'label' => 'До іншої минулої дії',
                                'color' => 'sky',
                                'description' => 'Коли важливо показати, що дія <strong>передувала іншій</strong>:',
                                'examples' => [
                                    ['en' => 'The house had been cleaned before the guests came.', 'ua' => 'Будинок прибрали до приходу гостей.'],
                                    ['en' => 'The email had been sent before he asked about it.', 'ua' => 'Лист надіслали до того, як він запитав.'],
                                    ['en' => 'The decision had been made before the meeting started.', 'ua' => 'Рішення прийняли до початку зустрічі.'],
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
                        'title' => '3. Коли використовувати Past Perfect Passive?',
                        'sections' => [
                            [
                                'label' => 'Дія до іншої минулої',
                                'color' => 'emerald',
                                'description' => 'Коли одна дія <strong>завершилася раніше</strong> за іншу минулу:',
                                'examples' => [
                                    ['en' => 'The work had been finished before the boss arrived.', 'ua' => 'Роботу закінчили до приходу боса.'],
                                    ['en' => 'The movie had been released before I heard about it.', 'ua' => 'Фільм вийшов до того, як я про нього почув.'],
                                    ['en' => 'The cake had been eaten before I got home.', 'ua' => 'Торт з\'їли до мого приходу.'],
                                ],
                            ],
                            [
                                'label' => 'До певного моменту',
                                'color' => 'blue',
                                'description' => 'До <strong>конкретного часу або події</strong> в минулому:',
                                'examples' => [
                                    ['en' => 'By 5 PM, the report had been submitted.', 'ua' => 'До 17:00 звіт було подано.'],
                                    ['en' => 'By the end of the day, all tasks had been completed.', 'ua' => 'До кінця дня всі завдання були виконані.'],
                                    ['en' => 'By last week, the decision had been made.', 'ua' => 'До минулого тижня рішення було прийнято.'],
                                ],
                            ],
                            [
                                'label' => 'У розповідях',
                                'color' => 'amber',
                                'description' => 'Для <strong>передісторії</strong> в оповіданнях:',
                                'examples' => [
                                    ['en' => 'She found out that her car had been stolen.', 'ua' => 'Вона дізналася, що її машину вкрали.'],
                                    ['en' => 'He discovered that the money had been hidden.', 'ua' => 'Він виявив, що гроші заховали.'],
                                    ['en' => 'They realized the door had been left unlocked.', 'ua' => 'Вони зрозуміли, що двері залишили незамкненими.'],
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
                                'description' => 'Додаємо <strong>not</strong> після had: <strong>Subject + had + not + been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report had not been submitted by the deadline.', 'ua' => 'Звіт не було подано до дедлайну.'],
                                    ['en' => 'The documents had not been signed before the meeting.', 'ua' => 'Документи не підписали до зустрічі.'],
                                    ['en' => 'The problem had not been solved when they arrived.', 'ua' => 'Проблема не була вирішена, коли вони прийшли.'],
                                    ['en' => 'The changes had not been approved yet.', 'ua' => 'Зміни ще не схвалили.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочена форма',
                                'color' => 'amber',
                                'description' => 'У розмовній мові: <strong>hadn\'t been</strong>',
                                'examples' => [
                                    ['en' => "The email hadn't been sent before he left.", 'ua' => 'Лист не надіслали до його від\'їзду.'],
                                    ['en' => "The tasks hadn't been completed on time.", 'ua' => 'Завдання не виконали вчасно.'],
                                    ['en' => "The decision hadn't been made by then.", 'ua' => 'Рішення не було прийнято на той час.'],
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
                                'description' => '<strong>Had</strong> виходить на перше місце: <strong>Had + Subject + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Had the report been finished before the meeting?', 'ua' => 'Звіт закінчили до зустрічі?'],
                                    ['en' => 'Had the documents been signed?', 'ua' => 'Документи підписали?'],
                                    ['en' => 'Had the problem been solved by then?', 'ua' => 'Проблему вирішили до того часу?'],
                                    ['en' => 'Had you ever been interviewed before?', 'ua' => 'Тебе раніше коли-небудь інтерв\'ювали?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + had + Subject + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'What had been done before he arrived?', 'ua' => 'Що було зроблено до його приходу?'],
                                    ['en' => 'Why had the meeting been cancelled?', 'ua' => 'Чому зустріч скасували?'],
                                    ['en' => 'Where had the money been hidden?', 'ua' => 'Де заховали гроші?'],
                                    ['en' => 'How had the problem been solved?', 'ua' => 'Як вирішили проблему?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + had (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Had the email been sent? — Yes, it had. / No, it hadn\'t.', 'ua' => 'Лист надіслали? — Так. / Ні.'],
                                    ['en' => 'Had they been informed? — Yes, they had. / No, they hadn\'t.', 'ua' => 'Їх поінформували? — Так. / Ні.'],
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
                        'title' => '6. Порівняння Active vs Passive у Past Perfect',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: They had completed the project.',
                                'ua' => 'Вони завершили проєкт.',
                                'note' => '→ Passive: The project had been completed.',
                            ],
                            [
                                'en' => 'Active: Someone had stolen my bike.',
                                'ua' => 'Хтось вкрав мій велосипед.',
                                'note' => '→ Passive: My bike had been stolen.',
                            ],
                            [
                                'en' => 'Active: The company had hired new staff.',
                                'ua' => 'Компанія найняла нових працівників.',
                                'note' => '→ Passive: New staff had been hired.',
                            ],
                            [
                                'en' => 'Active: We had made a decision.',
                                'ua' => 'Ми прийняли рішення.',
                                'note' => '→ Passive: A decision had been made.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (before, by the time, already) залишаються!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - all Past Passive forms
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Порівняння всіх Past Passive форм',
                        'intro' => 'Різниця між трьома формами минулого часу в пасиві:',
                        'rows' => [
                            [
                                'en' => 'Past Simple: The room was cleaned yesterday.',
                                'ua' => 'Кімнату прибрали вчора.',
                                'note' => '→ Факт у минулому',
                            ],
                            [
                                'en' => 'Past Continuous: The room was being cleaned when I arrived.',
                                'ua' => 'Кімнату прибирали, коли я прийшов.',
                                'note' => '→ Тривала дія в момент минулого',
                            ],
                            [
                                'en' => 'Past Perfect: The room had been cleaned before I arrived.',
                                'ua' => 'Кімнату прибрали до мого приходу.',
                                'note' => '→ Дія до іншої минулої',
                            ],
                        ],
                        'warning' => '📌 Past Perfect Passive підкреслює, що дія ПЕРЕДУВАЛА іншій минулій події!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Маркери часу для Past Perfect Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Past Perfect:',
                        'items' => [
                            [
                                'label' => 'До',
                                'title' => 'before, by the time, by + час',
                                'subtitle' => 'The report had been submitted before noon.',
                            ],
                            [
                                'label' => 'Вже',
                                'title' => 'already, yet, still',
                                'subtitle' => 'The task had already been completed.',
                            ],
                            [
                                'label' => 'Щойно',
                                'title' => 'just, recently',
                                'subtitle' => 'The email had just been sent when he arrived.',
                            ],
                            [
                                'label' => 'Досвід',
                                'title' => 'ever, never, before',
                                'subtitle' => 'Had this method ever been used before?',
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
                                'wrong' => 'The report had completed before.',
                                'right' => '✅ The report had been completed before.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина been та being.',
                                'wrong' => 'The work had being done.',
                                'right' => '✅ The work had been done.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання was замість had.',
                                'wrong' => 'The letter was been sent before.',
                                'right' => '✅ The letter had been sent before.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Past Simple замість Past Perfect.',
                                'wrong' => 'The email was sent before he arrived. (якщо важлива послідовність)',
                                'right' => '✅ The email had been sent before he arrived.',
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
                            'Past Perfect Passive: <strong>had + been + V3</strong>.',
                            '<strong>Had</strong> — однакове для всіх осіб.',
                            '<strong>been</strong> — незмінна частина, завжди стоїть перед V3.',
                            'Заперечення: <strong>had + not + been + V3</strong> (скорочено: hadn\'t).',
                            'Питання: <strong>Had + Subject + been + V3?</strong>',
                            'Використовується для: <strong>дій, що передували іншим минулим</strong>.',
                            'Маркери часу: before, by the time, already, just, ever, never.',
                            'Підкреслює <strong>послідовність</strong> минулих подій.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
