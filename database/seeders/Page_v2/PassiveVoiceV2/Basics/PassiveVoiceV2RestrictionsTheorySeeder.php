<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2RestrictionsTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-restrictions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Обмеження вживання пасиву',
            'subtitle_html' => '<p><strong>Обмеження пасиву</strong> — не всі дієслова можуть вживатися в пасивному стані. Неперехідні дієслова, деякі дієслова стану та зворотні дієслова зазвичай не утворюють пасиву.</p>',
            'subtitle_text' => 'Обмеження вживання пасиву: неперехідні дієслова, дієслова стану, зворотні дієслова, обмеження значення.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Restrictions',
                'Обмеження',
                'Intransitive Verbs',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти дізнаєшся, які <strong>дієслова НЕ можуть утворювати пасив</strong> і чому. Це важливо, щоб уникнути граматичних помилок.',
                        'rules' => [
                            [
                                'label' => 'Неперехідні',
                                'color' => 'rose',
                                'text' => '<strong>Неперехідні дієслова</strong> (без додатка) — без пасиву:',
                                'example' => 'He arrived. ✅ (не: He was arrived. ❌)',
                            ],
                            [
                                'label' => 'Дієслова стану',
                                'color' => 'amber',
                                'text' => 'Деякі <strong>stative verbs</strong> — без пасиву:',
                                'example' => 'I have a car. ✅ (не: A car is had by me. ❌)',
                            ],
                            [
                                'label' => 'Зворотні',
                                'color' => 'blue',
                                'text' => '<strong>Зворотні дієслова</strong> — без пасиву:',
                                'example' => 'She washed herself. ✅ (не: Herself was washed. ❌)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - неперехідні дієслова
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Неперехідні дієслова (Intransitive Verbs)',
                        'intro' => 'Дієслова без прямого додатка <strong>не утворюють пасиву</strong>:',
                        'items' => [
                            [
                                'label' => 'arrive',
                                'title' => 'прибувати',
                                'subtitle' => 'He arrived. ✅ (не: was arrived ❌)',
                            ],
                            [
                                'label' => 'go',
                                'title' => 'йти',
                                'subtitle' => 'They went home. ✅ (не: Home was gone ❌)',
                            ],
                            [
                                'label' => 'come',
                                'title' => 'приходити',
                                'subtitle' => 'She came early. ✅ (без пасиву)',
                            ],
                            [
                                'label' => 'sleep',
                                'title' => 'спати',
                                'subtitle' => 'He slept well. ✅ (без пасиву)',
                            ],
                            [
                                'label' => 'die',
                                'title' => 'помирати',
                                'subtitle' => 'The king died. ✅ (без пасиву)',
                            ],
                            [
                                'label' => 'happen',
                                'title' => 'траплятися',
                                'subtitle' => 'It happened yesterday. ✅ (без пасиву)',
                            ],
                            [
                                'label' => 'exist',
                                'title' => 'існувати',
                                'subtitle' => 'Problems exist. ✅ (без пасиву)',
                            ],
                            [
                                'label' => 'appear',
                                'title' => 'з\'являтися',
                                'subtitle' => 'A ghost appeared. ✅ (без пасиву)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - дієслова стану
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Дієслова стану (Stative Verbs)',
                        'sections' => [
                            [
                                'label' => 'Володіння',
                                'color' => 'rose',
                                'description' => 'Дієслова <strong>володіння</strong> зазвичай без пасиву:',
                                'examples' => [
                                    ['en' => 'I have a car. ✅', 'ua' => '(не: A car is had by me. ❌)'],
                                    ['en' => 'She owns a house. ✅', 'ua' => '(не: A house is owned by her. ❌ — рідко)'],
                                    ['en' => 'They possess great wealth. ✅', 'ua' => '(не типово в пасиві)'],
                                ],
                            ],
                            [
                                'label' => 'Відповідність',
                                'color' => 'amber',
                                'description' => 'Дієслова <strong>відповідності та схожості</strong>:',
                                'examples' => [
                                    ['en' => 'The dress fits her. ✅', 'ua' => '(не: She is fitted by the dress. ❌)'],
                                    ['en' => 'This shirt suits you. ✅', 'ua' => '(не: You are suited by this shirt. ❌)'],
                                    ['en' => 'The key fits the lock. ✅', 'ua' => '(без пасиву)'],
                                ],
                            ],
                            [
                                'label' => 'Зміст / вміст',
                                'color' => 'blue',
                                'description' => 'Дієслова <strong>місткості</strong>:',
                                'examples' => [
                                    ['en' => 'The hall holds 500 people. ✅', 'ua' => '(не: 500 people are held by the hall. ❌)'],
                                    ['en' => 'The bottle contains water. ✅', 'ua' => '(не: Water is contained by the bottle. ❌)'],
                                    ['en' => 'The bag weighs 5 kg. ✅', 'ua' => '(не типово в пасиві)'],
                                ],
                            ],
                            [
                                'label' => 'Взаємність',
                                'color' => 'emerald',
                                'description' => 'Деякі <strong>взаємні дієслова</strong>:',
                                'examples' => [
                                    ['en' => 'Tom resembles his father. ✅', 'ua' => '(не: His father is resembled by Tom. ❌)'],
                                    ['en' => 'This equals that. ✅', 'ua' => '(не типово в пасиві)'],
                                    ['en' => 'They lack experience. ✅', 'ua' => '(не: Experience is lacked. ❌)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - перехідні vs неперехідні
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Перехідні vs Неперехідні дієслова',
                        'intro' => 'Тільки <strong>перехідні дієслова</strong> (з прямим додатком) утворюють пасив:',
                        'rows' => [
                            [
                                'en' => 'Transitive: They wrote a letter.',
                                'ua' => 'Вони написали листа.',
                                'note' => '→ Passive: A letter was written. ✅',
                            ],
                            [
                                'en' => 'Intransitive: He arrived late.',
                                'ua' => 'Він прибув пізно.',
                                'note' => '→ Passive: ❌ (немає додатка)',
                            ],
                            [
                                'en' => 'Transitive: She opened the door.',
                                'ua' => 'Вона відчинила двері.',
                                'note' => '→ Passive: The door was opened. ✅',
                            ],
                            [
                                'en' => 'Intransitive: The door opened.',
                                'ua' => 'Двері відчинилися.',
                                'note' => '→ Passive: ❌ (немає додатка)',
                            ],
                        ],
                        'warning' => '📌 Деякі дієслова можуть бути перехідними і неперехідними залежно від контексту!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - зворотні дієслова
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Зворотні дієслова (Reflexive Verbs)',
                        'sections' => [
                            [
                                'label' => 'Зворотні дії',
                                'color' => 'rose',
                                'description' => 'Коли суб\'єкт і об\'єкт <strong>однакові</strong>, пасив не використовується:',
                                'examples' => [
                                    ['en' => 'She washed herself. ✅', 'ua' => '(не: Herself was washed by her. ❌)'],
                                    ['en' => 'He hurt himself. ✅', 'ua' => '(не: Himself was hurt by him. ❌)'],
                                    ['en' => 'They enjoyed themselves. ✅', 'ua' => '(без пасиву)'],
                                ],
                            ],
                            [
                                'label' => 'Взаємні дії',
                                'color' => 'blue',
                                'description' => 'Коли дія <strong>взаємна</strong> (each other):',
                                'examples' => [
                                    ['en' => 'They love each other. ✅', 'ua' => '(не: Each other is loved by them. ❌)'],
                                    ['en' => 'We helped each other. ✅', 'ua' => '(без пасиву)'],
                                    ['en' => 'They met each other. ✅', 'ua' => '(без пасиву)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - дієслова з обмеженим пасивом
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Інші обмеження',
                        'intro' => 'Деякі конструкції та значення обмежують пасив:',
                        'items' => [
                            [
                                'label' => 'Cognate objects',
                                'title' => 'споріднені додатки',
                                'subtitle' => 'live a happy life, dream a dream — без пасиву',
                            ],
                            [
                                'label' => 'Ідіоми',
                                'title' => 'сталі вирази',
                                'subtitle' => 'keep an eye on — зазвичай в активі',
                            ],
                            [
                                'label' => 'get + adj',
                                'title' => 'зміна стану',
                                'subtitle' => 'get angry, get tired — це НЕ пасив',
                            ],
                            [
                                'label' => 'let',
                                'title' => 'дозволяти',
                                'subtitle' => 'He let me go. → I was allowed to go. (не: was let)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - дієслова з подвійним значенням
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Дієслова з подвійним значенням',
                        'intro' => 'Деякі дієслова мають перехідне і неперехідне значення:',
                        'rows' => [
                            [
                                'en' => 'run (transitive): They run a business.',
                                'ua' => 'Вони ведуть бізнес.',
                                'note' => '→ A business is run by them. ✅',
                            ],
                            [
                                'en' => 'run (intransitive): He runs every day.',
                                'ua' => 'Він бігає щодня.',
                                'note' => '→ ❌ (без пасиву)',
                            ],
                            [
                                'en' => 'grow (transitive): They grow vegetables.',
                                'ua' => 'Вони вирощують овочі.',
                                'note' => '→ Vegetables are grown. ✅',
                            ],
                            [
                                'en' => 'grow (intransitive): Children grow fast.',
                                'ua' => 'Діти ростуть швидко.',
                                'note' => '→ ❌ (без пасиву)',
                            ],
                        ],
                        'warning' => '📌 Перевіряй, чи є у дієслова прямий додаток, перш ніж утворювати пасив!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пасив неперехідного дієслова.',
                                'wrong' => 'The accident was happened yesterday.',
                                'right' => '✅ The accident happened yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пасив дієслова володіння.',
                                'wrong' => 'A nice house is had by her.',
                                'right' => '✅ She has a nice house.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Пасив зворотного дієслова.',
                                'wrong' => 'Himself was cut by him.',
                                'right' => '✅ He cut himself.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Плутання перехідного/неперехідного.',
                                'wrong' => 'He was arrived late.',
                                'right' => '✅ He arrived late.',
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
                        'title' => '8. Короткий конспект',
                        'items' => [
                            '<strong>Неперехідні дієслова</strong> (без додатка) — без пасиву: arrive, go, happen, die.',
                            '<strong>Дієслова стану</strong>: have, fit, contain, resemble — зазвичай без пасиву.',
                            '<strong>Зворотні дієслова</strong> (з myself, himself) — без пасиву.',
                            '<strong>Взаємні дієслова</strong> (з each other) — без пасиву.',
                            'Тільки <strong>перехідні дієслова</strong> (з прямим додатком) утворюють пасив.',
                            'Деякі дієслова можуть бути <strong>і перехідними, і неперехідними</strong>.',
                            'Перевіряй <strong>наявність прямого додатка</strong> перед утворенням пасиву.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
