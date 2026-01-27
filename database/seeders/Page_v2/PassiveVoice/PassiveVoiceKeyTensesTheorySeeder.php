<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\PassiveVoice\PassiveVoicePageSeeder;

class PassiveVoiceKeyTensesTheorySeeder extends PassiveVoicePageSeeder
{
    protected function slug(): string
    {
        return 'passive-key-tenses';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive in Key Tenses — Пасив у ключових часах',
            'subtitle_html' => '<p><strong>Пасив у ключових часах</strong> — це огляд утворення пасивного стану в Present/Past Continuous, Present Perfect та Future Simple. Кожен час має свою формулу: Continuous використовує <em>being</em>, Perfect — <em>been</em>, Future — <em>will be</em>.</p>',
            'subtitle_text' => 'Теоретичний огляд пасиву в різних часах: Present Continuous, Past Continuous, Present Perfect, Future Simple — формули, приклади, порівняння.',
            'subtitle_level' => 'B1',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pasyvnyi-stan',
                'title' => 'Пасивний стан (Passive Voice)',
                'language' => 'uk',
            ],
            // Page anchor tags
            'tags' => [
                'Passive Voice',
                'Passive in Different Tenses',
                'Present Continuous Passive',
                'Past Continuous Passive',
                'Present Perfect Passive',
                'Future Passive',
                'Grammar',
                'Theory',
            ],
            // Base tags inherited by all blocks
            'base_tags' => [
                'Passive Voice',
                'Passive in Different Tenses',
            ],
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'hero',
                    'tags' => ['Introduction', 'Overview', 'Continuous Passive', 'Perfect Passive', 'Future Passive', 'CEFR B1', 'CEFR B2'],
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш <strong>пасив у ключових часах</strong>: Present/Past Continuous, Present Perfect та Future. Кожен час має свою формулу утворення пасиву.',
                        'rules' => [
                            [
                                'label' => 'CONTINUOUS',
                                'color' => 'emerald',
                                'text' => '<strong>is/was being + V3</strong> — дія в процесі:',
                                'example' => 'It is being done. It was being repaired.',
                            ],
                            [
                                'label' => 'PERFECT',
                                'color' => 'blue',
                                'text' => '<strong>has/had been + V3</strong> — завершена дія:',
                                'example' => 'It has been done. It had been finished.',
                            ],
                            [
                                'label' => 'FUTURE',
                                'color' => 'amber',
                                'text' => '<strong>will be + V3</strong> — майбутня дія:',
                                'example' => 'It will be done tomorrow.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'forms-grid-overview',
                    'tags' => ['Overview', 'All Tenses', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '1. Огляд усіх часів у пасиві',
                        'intro' => 'Порівняй формули пасиву в різних часах:',
                        'items' => [
                            ['label' => 'Present Simple', 'title' => 'is/are + V3', 'subtitle' => 'It is made.'],
                            ['label' => 'Past Simple', 'title' => 'was/were + V3', 'subtitle' => 'It was made.'],
                            ['label' => 'Present Continuous', 'title' => 'is/are being + V3', 'subtitle' => 'It is being made.'],
                            ['label' => 'Past Continuous', 'title' => 'was/were being + V3', 'subtitle' => 'It was being made.'],
                            ['label' => 'Present Perfect', 'title' => 'has/have been + V3', 'subtitle' => 'It has been made.'],
                            ['label' => 'Future Simple', 'title' => 'will be + V3', 'subtitle' => 'It will be made.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-present-continuous',
                    'tags' => ['Present Continuous Passive', 'Being', 'In Progress', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '2. Present Continuous Passive — is/are being + V3',
                        'sections' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'description' => '<strong>Subject + is/are + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being built.', 'ua' => 'Будинок будується (зараз).'],
                                    ['en' => 'The car is being repaired.', 'ua' => 'Машина ремонтується.'],
                                    ['en' => 'New roads are being constructed.', 'ua' => 'Нові дороги будуються.'],
                                    ['en' => 'The report is being written.', 'ua' => 'Звіт пишеться.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія відбувається <strong>прямо зараз</strong> або <strong>в цей період</strong>.',
                                'examples' => [
                                    ['en' => 'The bridge is being painted this week.', 'ua' => 'Міст фарбують цього тижня.'],
                                    ['en' => 'Changes are being made to the system.', 'ua' => 'До системи вносяться зміни.'],
                                    ['en' => 'The situation is being monitored.', 'ua' => 'Ситуація відстежується.'],
                                ],
                            ],
                            [
                                'label' => 'Питання та заперечення',
                                'color' => 'purple',
                                'description' => 'Інверсія is/are, додавання not:',
                                'examples' => [
                                    ['en' => 'Is it being done?', 'ua' => 'Чи це робиться?'],
                                    ['en' => 'It isn\'t being used.', 'ua' => 'Це не використовується.'],
                                ],
                                'note' => '📌 Being — ключове слово для Continuous Passive!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-past-continuous',
                    'tags' => ['Past Continuous Passive', 'Being', 'Was Being', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '3. Past Continuous Passive — was/were being + V3',
                        'sections' => [
                            [
                                'label' => 'Формула',
                                'color' => 'blue',
                                'description' => '<strong>Subject + was/were + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house was being built.', 'ua' => 'Будинок будувався (в той момент).'],
                                    ['en' => 'The car was being repaired.', 'ua' => 'Машина ремонтувалася.'],
                                    ['en' => 'The documents were being prepared.', 'ua' => 'Документи готувалися.'],
                                    ['en' => 'The meeting was being recorded.', 'ua' => 'Зустріч записувалася.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'amber',
                                'description' => 'Дія <strong>тривала в конкретний момент у минулому</strong>.',
                                'examples' => [
                                    ['en' => 'When I arrived, dinner was being prepared.', 'ua' => 'Коли я приїхав, вечеря готувалася.'],
                                    ['en' => 'The film was being shown when the power went out.', 'ua' => 'Коли вимкнулось світло, показували фільм.'],
                                    ['en' => 'At 3 PM, the package was being delivered.', 'ua' => 'О 15:00 посилку доставляли.'],
                                ],
                            ],
                            [
                                'label' => 'Питання та заперечення',
                                'color' => 'rose',
                                'description' => 'Інверсія was/were, додавання not:',
                                'examples' => [
                                    ['en' => 'Was it being done?', 'ua' => 'Чи це робилося?'],
                                    ['en' => 'It wasn\'t being used.', 'ua' => 'Це не використовувалося.'],
                                ],
                                'note' => '📌 Часто вживається з when/while для опису паралельних дій.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-present-perfect',
                    'tags' => ['Present Perfect Passive', 'Been', 'Has Been', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '4. Present Perfect Passive — has/have been + V3',
                        'sections' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'description' => '<strong>Subject + has/have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work has been done.', 'ua' => 'Робота зроблена.'],
                                    ['en' => 'The letter has been sent.', 'ua' => 'Лист надіслано.'],
                                    ['en' => 'The problem has been solved.', 'ua' => 'Проблема вирішена.'],
                                    ['en' => 'Changes have been made.', 'ua' => 'Зміни внесено.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>завершена</strong> з результатом у теперішньому.',
                                'examples' => [
                                    ['en' => 'The room has been cleaned. (it\'s clean now)', 'ua' => 'Кімната прибрана. (зараз чиста)'],
                                    ['en' => 'The tickets have been bought.', 'ua' => 'Квитки куплені.'],
                                    ['en' => 'New software has been installed.', 'ua' => 'Нове програмне забезпечення встановлено.'],
                                ],
                            ],
                            [
                                'label' => 'Питання та заперечення',
                                'color' => 'purple',
                                'description' => 'Інверсія has/have, додавання not:',
                                'examples' => [
                                    ['en' => 'Has it been done?', 'ua' => 'Чи це зроблено?'],
                                    ['en' => 'Have they been invited?', 'ua' => 'Чи їх запросили?'],
                                    ['en' => 'It hasn\'t been finished yet.', 'ua' => 'Це ще не завершено.'],
                                ],
                                'note' => '📌 Been — ключове слово для Perfect Passive!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-future',
                    'tags' => ['Future Passive', 'Will Be', 'Future Simple Passive', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '5. Future Simple Passive — will be + V3',
                        'sections' => [
                            [
                                'label' => 'Формула',
                                'color' => 'blue',
                                'description' => '<strong>Subject + will + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work will be done tomorrow.', 'ua' => 'Робота буде зроблена завтра.'],
                                    ['en' => 'The letter will be sent.', 'ua' => 'Лист буде надісланий.'],
                                    ['en' => 'You will be notified.', 'ua' => 'Вас повідомлять.'],
                                    ['en' => 'The project will be completed next month.', 'ua' => 'Проект буде завершено наступного місяця.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'amber',
                                'description' => 'Дія відбудеться <strong>в майбутньому</strong>.',
                                'examples' => [
                                    ['en' => 'New employees will be hired next year.', 'ua' => 'Нових працівників найматимуть наступного року.'],
                                    ['en' => 'The results will be announced soon.', 'ua' => 'Результати будуть оголошені скоро.'],
                                    ['en' => 'Tickets will be sold online.', 'ua' => 'Квитки продаватимуться онлайн.'],
                                ],
                            ],
                            [
                                'label' => 'Питання та заперечення',
                                'color' => 'rose',
                                'description' => 'Інверсія will, додавання not:',
                                'examples' => [
                                    ['en' => 'Will it be done?', 'ua' => 'Чи це буде зроблено?'],
                                    ['en' => 'When will it be finished?', 'ua' => 'Коли це буде закінчено?'],
                                    ['en' => 'It won\'t be ready on time.', 'ua' => 'Це не буде готово вчасно.'],
                                ],
                                'note' => '📌 Можна також: is/are going to be + V3 (для планів)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'uuid_key' => 'usage-panels-past-perfect-future-perfect',
                    'tags' => ['Past Perfect Passive', 'Future Perfect Passive', 'Advanced Tenses', 'CEFR B2'],
                    'body' => json_encode([
                        'title' => '6. Інші часи (B2+)',
                        'sections' => [
                            [
                                'label' => 'Past Perfect Passive',
                                'color' => 'purple',
                                'description' => '<strong>had been + V3</strong> — дія завершилася до іншої минулої події.',
                                'examples' => [
                                    ['en' => 'The work had been finished before I arrived.', 'ua' => 'Робота була закінчена до мого приїзду.'],
                                    ['en' => 'The decision had been made.', 'ua' => 'Рішення вже було прийнято.'],
                                    ['en' => 'By 5 PM, all tickets had been sold.', 'ua' => 'До 17:00 всі квитки були продані.'],
                                ],
                            ],
                            [
                                'label' => 'Future Perfect Passive',
                                'color' => 'indigo',
                                'description' => '<strong>will have been + V3</strong> — дія буде завершена до певного моменту в майбутньому.',
                                'examples' => [
                                    ['en' => 'The work will have been finished by Friday.', 'ua' => 'Робота буде закінчена до п\'ятниці.'],
                                    ['en' => 'The report will have been submitted.', 'ua' => 'Звіт буде поданий.'],
                                    ['en' => 'By next year, the building will have been completed.', 'ua' => 'До наступного року будівля буде завершена.'],
                                ],
                            ],
                            [
                                'label' => 'Примітка',
                                'color' => 'gray',
                                'description' => 'Present Perfect Continuous та Past Perfect Continuous Passive існують теоретично, але рідко використовуються.',
                                'examples' => [
                                    ['en' => 'It has been being done. (rare)', 'ua' => 'Це робилося. (рідко вживається)'],
                                ],
                                'note' => '📌 У розмовній мові зазвичай замінюють на простіші конструкції.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'comparison-table',
                    'tags' => ['Summary', 'Comparison', 'All Tenses', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '7. Порівняльна таблиця часів',
                        'intro' => 'Усі часи пасиву в одній таблиці:',
                        'rows' => [
                            [
                                'en' => 'Present Simple',
                                'ua' => 'is/are + V3',
                                'note' => 'It is done.',
                            ],
                            [
                                'en' => 'Past Simple',
                                'ua' => 'was/were + V3',
                                'note' => 'It was done.',
                            ],
                            [
                                'en' => 'Present Continuous',
                                'ua' => 'is/are being + V3',
                                'note' => 'It is being done.',
                            ],
                            [
                                'en' => 'Past Continuous',
                                'ua' => 'was/were being + V3',
                                'note' => 'It was being done.',
                            ],
                            [
                                'en' => 'Present Perfect',
                                'ua' => 'has/have been + V3',
                                'note' => 'It has been done.',
                            ],
                            [
                                'en' => 'Past Perfect',
                                'ua' => 'had been + V3',
                                'note' => 'It had been done.',
                            ],
                            [
                                'en' => 'Future Simple',
                                'ua' => 'will be + V3',
                                'note' => 'It will be done.',
                            ],
                            [
                                'en' => 'Future Perfect',
                                'ua' => 'will have been + V3',
                                'note' => 'It will have been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'box',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'key-words',
                    'tags' => ['Key Words', 'Being', 'Been', 'CEFR B1'],
                    'body' => <<<'HTML'
<div class="gw-box">
<h4>🔑 Ключові слова</h4>
<ul class="gw-list">
<li><strong>BEING</strong> — для Continuous (тривалих) часів: <span class="gw-en">is/was being + V3</span></li>
<li><strong>BEEN</strong> — для Perfect (завершених) часів: <span class="gw-en">has/had been + V3</span></li>
<li><strong>WILL BE</strong> — для Future: <span class="gw-en">will be + V3</span></li>
</ul>
<p><strong>Запам'ятай:</strong> Being = процес, Been = результат!</p>
</div>
HTML,
                ],
                [
                    'type' => 'box',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'tips',
                    'tags' => ['Tips', 'Learning'],
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Для <strong>Continuous</strong> — додай being: <span class="gw-en">is/was + being + V3</span></p>
<p>Для <strong>Perfect</strong> — додай been: <span class="gw-en">has/had + been + V3</span></p>
<p>Для <strong>Future</strong> — will + be: <span class="gw-en">will be + V3</span></p>
<p>Практикуй трансформацію Active → Passive в усіх часах!</p>
<p>Зверни увагу на <strong>контекст</strong> — він визначає час.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
