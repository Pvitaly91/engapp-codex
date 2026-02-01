<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Tenses;

class PassiveVoiceV2PastSimpleTheorySeeder extends PassiveVoiceV2TensesPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-past-simple';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Simple Passive — Минулий простий пасив',
            'subtitle_html' => '<p><strong>Past Simple Passive</strong> використовується для опису дій, що <strong>відбулися в минулому</strong> в пасивному стані. Це одна з найпоширеніших форм пасиву, яку потрібно знати кожному.</p>',
            'subtitle_text' => 'Past Simple Passive (Минулий простий пасив): формула was/were + V3, ствердження, заперечення, питання та практичні приклади використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-tenses',
                'title' => 'Пасив у різних часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Past Simple Passive',
                'Минулий простий пасив',
                'was were',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>Past Simple Passive</strong> (Минулий простий пасив): як утворювати ствердження, заперечення та питання, коли використовувати цю форму та які типові помилки потрібно уникати.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + was/were + V3 (Past Participle)</strong>:',
                                'example' => 'The letter was written yesterday.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + was/were + not + V3</strong>:',
                                'example' => 'The letter was not sent on time.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Was/Were + Subject + V3?</strong>:',
                                'example' => 'Was the letter written in English?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - вибір was/were
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Вибір допоміжного дієслова was/were',
                        'intro' => 'Форма дієслова <strong>be</strong> в минулому часі залежить від підмета:',
                        'items' => [
                            [
                                'label' => 'I/He/She/It',
                                'title' => 'was + V3',
                                'subtitle' => 'The house was built in 1990. — Будинок був побудований у 1990 році.',
                            ],
                            [
                                'label' => 'You/We/They',
                                'title' => 'were + V3',
                                'subtitle' => 'The letters were sent yesterday. — Листи надіслали вчора.',
                            ],
                            [
                                'label' => 'Singular',
                                'title' => 'was + V3',
                                'subtitle' => 'The cake was baked by my mother. — Торт спекла моя мама.',
                            ],
                            [
                                'label' => 'Plural',
                                'title' => 'were + V3',
                                'subtitle' => 'The cars were stolen last night. — Машини вкрали минулої ночі.',
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
                                'description' => 'Порядок слів: <strong>Subject + was/were + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The letters were sent yesterday.', 'ua' => 'Листи надіслали вчора.'],
                                    ['en' => 'I was invited to the party.', 'ua' => 'Мене запросили на вечірку.'],
                                    ['en' => 'The thieves were caught by the police.', 'ua' => 'Злодіїв зловила поліція.'],
                                ],
                            ],
                            [
                                'label' => 'З вказівкою виконавця (by + agent)',
                                'color' => 'sky',
                                'description' => 'Коли важливо вказати <strong>хто виконав дію</strong>:',
                                'examples' => [
                                    ['en' => 'This book was written by a famous author.', 'ua' => 'Ця книга написана відомим автором.'],
                                    ['en' => 'The cake was baked by my grandmother.', 'ua' => 'Торт спекла моя бабуся.'],
                                    ['en' => 'The decision was made by the manager.', 'ua' => 'Рішення прийняв менеджер.'],
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
                        'title' => '3. Коли використовувати Past Simple Passive?',
                        'sections' => [
                            [
                                'label' => 'Завершені дії в минулому',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>завершилися в минулому</strong>:',
                                'examples' => [
                                    ['en' => 'The film was released in 2020.', 'ua' => 'Фільм вийшов у 2020 році.'],
                                    ['en' => 'The bridge was destroyed during the war.', 'ua' => 'Міст був зруйнований під час війни.'],
                                    ['en' => 'The meeting was cancelled yesterday.', 'ua' => 'Зустріч скасували вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Історичні факти',
                                'color' => 'blue',
                                'description' => 'Для опису <strong>історичних подій</strong>:',
                                'examples' => [
                                    ['en' => 'America was discovered in 1492.', 'ua' => 'Америку відкрили у 1492 році.'],
                                    ['en' => 'The pyramids were built thousands of years ago.', 'ua' => 'Піраміди побудували тисячі років тому.'],
                                    ['en' => 'The city was founded in the 18th century.', 'ua' => 'Місто засноване у 18 столітті.'],
                                ],
                            ],
                            [
                                'label' => 'Невідомий виконавець',
                                'color' => 'amber',
                                'description' => 'Коли <strong>виконавець невідомий</strong> або неважливий:',
                                'examples' => [
                                    ['en' => 'My bike was stolen yesterday.', 'ua' => 'Мій велосипед вкрали вчора.'],
                                    ['en' => 'The window was broken last night.', 'ua' => 'Вікно розбили минулої ночі.'],
                                    ['en' => 'The money was hidden somewhere.', 'ua' => 'Гроші заховали десь.'],
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
                                'description' => 'Додаємо <strong>not</strong> після was/were: <strong>Subject + was/were + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email was not sent on time.', 'ua' => 'Лист не надіслали вчасно.'],
                                    ['en' => 'We were not informed about the change.', 'ua' => 'Нас не повідомили про зміни.'],
                                    ['en' => 'The report was not finished yesterday.', 'ua' => 'Звіт не закінчили вчора.'],
                                    ['en' => 'The documents were not signed.', 'ua' => 'Документи не підписали.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові використовують <strong>wasn\'t / weren\'t</strong>:',
                                'examples' => [
                                    ['en' => "The window wasn't opened.", 'ua' => 'Вікно не відкривали.'],
                                    ['en' => "The letters weren't delivered.", 'ua' => 'Листи не доставили.'],
                                    ['en' => "I wasn't invited to the meeting.", 'ua' => 'Мене не запросили на зустріч.'],
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
                                'description' => '<strong>Was/Were</strong> виходить на перше місце: <strong>Was/Were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the house built in 1990?', 'ua' => 'Будинок побудували у 1990 році?'],
                                    ['en' => 'Were the documents signed?', 'ua' => 'Документи підписали?'],
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                    ['en' => 'Were you invited to the party?', 'ua' => 'Тебе запросили на вечірку?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + was/were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'When was the house built?', 'ua' => 'Коли побудували будинок?'],
                                    ['en' => 'Where were the letters sent?', 'ua' => 'Куди надіслали листи?'],
                                    ['en' => 'Why was the meeting cancelled?', 'ua' => 'Чому скасували зустріч?'],
                                    ['en' => 'How was the problem solved?', 'ua' => 'Як вирішили проблему?'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'amber',
                                'description' => 'Відповіді з <strong>Yes/No + pronoun + was/were (not)</strong>:',
                                'examples' => [
                                    ['en' => 'Was the letter sent? — Yes, it was. / No, it wasn\'t.', 'ua' => 'Лист надіслали? — Так. / Ні.'],
                                    ['en' => 'Were they invited? — Yes, they were. / No, they weren\'t.', 'ua' => 'Їх запросили? — Так. / Ні.'],
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
                        'title' => '6. Порівняння Active vs Passive у Past Simple',
                        'intro' => 'Як перетворити активне речення на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom wrote the letter yesterday.',
                                'ua' => 'Том написав листа вчора.',
                                'note' => '→ Passive: The letter was written (by Tom) yesterday.',
                            ],
                            [
                                'en' => 'Active: They built this house in 1990.',
                                'ua' => 'Вони побудували цей будинок у 1990.',
                                'note' => '→ Passive: This house was built in 1990.',
                            ],
                            [
                                'en' => 'Active: Someone stole my bike.',
                                'ua' => 'Хтось вкрав мій велосипед.',
                                'note' => '→ Passive: My bike was stolen.',
                            ],
                            [
                                'en' => 'Active: The police caught the thieves.',
                                'ua' => 'Поліція зловила злодіїв.',
                                'note' => '→ Passive: The thieves were caught by the police.',
                            ],
                        ],
                        'warning' => '📌 Маркери часу (yesterday, last week, in 1990) залишаються в реченні!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Time markers
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Маркери часу для Past Simple Passive',
                        'intro' => 'Типові слова та вирази, що вказують на Past Simple:',
                        'items' => [
                            [
                                'label' => 'Вчора',
                                'title' => 'yesterday, the day before yesterday',
                                'subtitle' => 'The letter was sent yesterday.',
                            ],
                            [
                                'label' => 'Минуле',
                                'title' => 'last week/month/year, ago',
                                'subtitle' => 'The house was built two years ago.',
                            ],
                            [
                                'label' => 'Конкретний час',
                                'title' => 'in 1990, on Monday, at 5 PM',
                                'subtitle' => 'The meeting was held on Monday.',
                            ],
                            [
                                'label' => 'Минулі події',
                                'title' => 'when, after, before, during',
                                'subtitle' => 'The decision was made after the meeting.',
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
                                'title' => 'Неправильний вибір was/were.',
                                'wrong' => 'The letters was sent yesterday.',
                                'right' => '✅ The letters were sent yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск was/were у питаннях.',
                                'wrong' => 'The house built in 1990?',
                                'right' => '✅ Was the house built in 1990?',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V2 замість V3.',
                                'wrong' => 'The house was builded in 1990.',
                                'right' => '✅ The house was built in 1990.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Зайве використання did.',
                                'wrong' => 'Did the letter was sent?',
                                'right' => '✅ Was the letter sent?',
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
                            'Past Simple Passive: <strong>was/were + V3 (Past Participle)</strong>.',
                            'Вибір be: <strong>I/He/She/It → was</strong>, <strong>You/We/They/Plural → were</strong>.',
                            'Заперечення: <strong>was/were + not + V3</strong> (скорочено: wasn\'t, weren\'t).',
                            'Питання: <strong>Was/Were + Subject + V3?</strong>',
                            'Wh-питання: <strong>Wh-word + was/were + Subject + V3?</strong>',
                            'Використовується для: <strong>завершених дій в минулому</strong>, <strong>історичних фактів</strong>.',
                            'Маркери часу: yesterday, last week, ago, in 1990 тощо.',
                            '<strong>by + agent</strong> додається, коли важливо вказати виконавця.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
