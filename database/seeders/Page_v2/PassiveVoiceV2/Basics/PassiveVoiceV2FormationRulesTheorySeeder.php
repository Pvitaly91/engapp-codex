<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2FormationRulesTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-formation-rules';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Основні правила утворення пасиву',
            'subtitle_html' => '<p><strong>Пасивний стан (Passive Voice)</strong> утворюється за формулою <strong>be + V3 (Past Participle)</strong>. У цій темі ти детально вивчиш, як перетворювати активні речення на пасивні та коли використовувати пасив.</p>',
            'subtitle_text' => 'Основні правила утворення пасиву: формула be + V3, перетворення active → passive, коли використовувати пасив, by + agent.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан V2',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Formation Rules',
                'Правила утворення',
                'be + V3',
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
                        'intro' => 'У цій темі ти вивчиш <strong>основні правила утворення пасивного стану</strong>: базову формулу, як перетворювати активні речення на пасивні, та коли доречно використовувати пасив.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => 'Базова структура пасиву: <strong>Subject + be + V3 (Past Participle)</strong>:',
                                'example' => 'The cake is baked. — Торт випечений.',
                            ],
                            [
                                'label' => 'Перетворення',
                                'color' => 'blue',
                                'text' => 'Object активного речення → Subject пасивного:',
                                'example' => 'Tom writes letters. → Letters are written by Tom.',
                            ],
                            [
                                'label' => 'Виконавець',
                                'color' => 'rose',
                                'text' => '<strong>by + agent</strong> — вказує, хто виконує дію:',
                                'example' => 'The book was written by Shakespeare.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - Active vs Passive
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Активний vs Пасивний стан',
                        'intro' => 'Порівняння активного та пасивного стану:',
                        'items' => [
                            [
                                'label' => 'Active',
                                'title' => 'Subject виконує дію',
                                'subtitle' => 'Tom writes letters. — Том пише листи.',
                            ],
                            [
                                'label' => 'Passive',
                                'title' => 'Subject отримує дію',
                                'subtitle' => 'Letters are written by Tom. — Листи пишуться Томом.',
                            ],
                            [
                                'label' => 'Формула',
                                'title' => 'be + V3',
                                'subtitle' => 'Дієслово be у потрібному часі + Past Participle.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'by + виконавець',
                                'subtitle' => 'Вказується, якщо важливо знати виконавця.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - кроки перетворення
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Як перетворити Active → Passive?',
                        'sections' => [
                            [
                                'label' => 'Крок 1',
                                'color' => 'emerald',
                                'description' => '<strong>Object</strong> активного речення стає <strong>Subject</strong> пасивного:',
                                'examples' => [
                                    ['en' => 'Active: Tom writes letters.', 'ua' => 'letters → Letters (стає підметом)'],
                                    ['en' => 'Active: They built a house.', 'ua' => 'a house → A house (стає підметом)'],
                                ],
                            ],
                            [
                                'label' => 'Крок 2',
                                'color' => 'blue',
                                'description' => 'Додаємо <strong>be</strong> у відповідному часі + <strong>V3</strong>:',
                                'examples' => [
                                    ['en' => 'writes → is/are written', 'ua' => 'Present Simple: am/is/are + V3'],
                                    ['en' => 'built → was/were built', 'ua' => 'Past Simple: was/were + V3'],
                                ],
                            ],
                            [
                                'label' => 'Крок 3',
                                'color' => 'rose',
                                'description' => '<strong>Subject</strong> активного речення стає <strong>by + agent</strong> (за потреби):',
                                'examples' => [
                                    ['en' => 'Tom → by Tom', 'ua' => 'Якщо важливо, хто виконує дію'],
                                    ['en' => 'They → (пропускається)', 'ua' => 'Якщо виконавець невідомий або неважливий'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - перетворення прикладів
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Приклади перетворення Active → Passive',
                        'intro' => 'Повний процес перетворення активних речень на пасивні:',
                        'rows' => [
                            [
                                'en' => 'Active: Mary bakes a cake.',
                                'ua' => 'Марія печe торт.',
                                'note' => '→ Passive: A cake is baked by Mary.',
                            ],
                            [
                                'en' => 'Active: They speak English here.',
                                'ua' => 'Тут розмовляють англійською.',
                                'note' => '→ Passive: English is spoken here.',
                            ],
                            [
                                'en' => 'Active: Someone stole my bike.',
                                'ua' => 'Хтось вкрав мій велосипед.',
                                'note' => '→ Passive: My bike was stolen.',
                            ],
                            [
                                'en' => 'Active: The company will hire new employees.',
                                'ua' => 'Компанія найме нових працівників.',
                                'note' => '→ Passive: New employees will be hired by the company.',
                            ],
                        ],
                        'warning' => '📌 by + agent пропускається, якщо виконавець невідомий, неважливий або очевидний!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати пасив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Коли використовувати пасивний стан?',
                        'sections' => [
                            [
                                'label' => 'Невідомий виконавець',
                                'color' => 'emerald',
                                'description' => 'Коли <strong>невідомо</strong>, хто виконав дію:',
                                'examples' => [
                                    ['en' => 'My car was stolen last night.', 'ua' => 'Мою машину вкрали минулої ночі.'],
                                    ['en' => 'The window has been broken.', 'ua' => 'Вікно розбите.'],
                                    ['en' => 'The building was destroyed.', 'ua' => 'Будівля була зруйнована.'],
                                ],
                            ],
                            [
                                'label' => 'Неважливий виконавець',
                                'color' => 'blue',
                                'description' => 'Коли виконавець <strong>неважливий</strong> або очевидний:',
                                'examples' => [
                                    ['en' => 'The streets are cleaned every day.', 'ua' => 'Вулиці прибираються щодня.'],
                                    ['en' => 'Tickets are sold at the entrance.', 'ua' => 'Квитки продаються біля входу.'],
                                    ['en' => 'The law was passed last year.', 'ua' => 'Закон був прийнятий минулого року.'],
                                ],
                            ],
                            [
                                'label' => 'Фокус на дії/об\'єкті',
                                'color' => 'amber',
                                'description' => 'Коли важливіша <strong>сама дія</strong> або <strong>об\'єкт</strong>:',
                                'examples' => [
                                    ['en' => 'The report was submitted on time.', 'ua' => 'Звіт був поданий вчасно.'],
                                    ['en' => 'The new system will be launched tomorrow.', 'ua' => 'Нова система буде запущена завтра.'],
                                    ['en' => 'The product is made in Germany.', 'ua' => 'Продукт виробляється в Німеччині.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'rose',
                                'description' => 'У <strong>науковому, офіційному</strong> та <strong>діловому</strong> стилі:',
                                'examples' => [
                                    ['en' => 'The experiment was conducted carefully.', 'ua' => 'Експеримент був проведений ретельно.'],
                                    ['en' => 'Applicants will be notified by email.', 'ua' => 'Заявників повідомлять електронною поштою.'],
                                    ['en' => 'The data is analyzed weekly.', 'ua' => 'Дані аналізуються щотижня.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - by vs with
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. By vs With у пасиві',
                        'intro' => 'Різниця між <strong>by</strong> та <strong>with</strong> у пасивних реченнях:',
                        'items' => [
                            [
                                'label' => 'by',
                                'title' => 'Хто виконує дію (agent)',
                                'subtitle' => 'The book was written by Mark Twain.',
                            ],
                            [
                                'label' => 'with',
                                'title' => 'Чим/за допомогою чого (instrument)',
                                'subtitle' => 'The letter was written with a pen.',
                            ],
                            [
                                'label' => 'by',
                                'title' => 'Жива істота або організація',
                                'subtitle' => 'The project was completed by our team.',
                            ],
                            [
                                'label' => 'with',
                                'title' => 'Інструмент, матеріал',
                                'subtitle' => 'The cake was decorated with chocolate.',
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
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => 'The letter written yesterday.',
                                'right' => '✅ The letter was written yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V1/V2 замість V3.',
                                'wrong' => 'English is speak here.',
                                'right' => '✅ English is spoken here.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання with замість by для виконавця.',
                                'wrong' => 'The book was written with Shakespeare.',
                                'right' => '✅ The book was written by Shakespeare.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Зайвий by agent.',
                                'wrong' => 'My car was stolen by someone.',
                                'right' => '✅ My car was stolen. (виконавець невідомий)',
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
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Базова формула пасиву: <strong>Subject + be + V3 (Past Participle)</strong>.',
                            'Object активного речення → Subject пасивного.',
                            '<strong>by + agent</strong> — вказує виконавця (людина, організація).',
                            '<strong>with + instrument</strong> — вказує інструмент або засіб.',
                            'Пасив використовують, коли виконавець <strong>невідомий</strong>, <strong>неважливий</strong> або <strong>очевидний</strong>.',
                            'Пасив типовий для <strong>формального, наукового та ділового</strong> стилю.',
                            'by + agent пропускається, якщо виконавець невідомий або неважливий.',
                            'Дієслово <strong>be</strong> змінюється відповідно до часу, <strong>V3</strong> — ні.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
