<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2PhrasalVerbsTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-phrasal-verbs';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасив фразових дієслів',
            'subtitle_html' => '<p><strong>Пасив фразових дієслів (Phrasal Verbs in Passive)</strong> — утворюється так само, як і звичайний пасив, але прийменник або частка залишається після дієслова. Це важлива тема для природного звучання англійської.</p>',
            'subtitle_text' => 'Пасив фразових дієслів: look after, bring up, put off, turn down. Структура, порядок слів та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан V2',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Phrasal Verbs',
                'Фразові дієслова',
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
                        'intro' => 'У цій темі ти вивчиш, як утворювати пасив з <strong>фразовими дієсловами</strong>. Головне правило: <strong>прийменник або частка залишається з дієсловом</strong>.',
                        'rules' => [
                            [
                                'label' => 'Активний',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + phrasal verb + object</strong>:',
                                'example' => 'They called off the meeting.',
                            ],
                            [
                                'label' => 'Пасивний',
                                'color' => 'blue',
                                'text' => '<strong>Object + be + V3 + particle/preposition</strong>:',
                                'example' => 'The meeting was called off.',
                            ],
                            [
                                'label' => 'Важливо',
                                'color' => 'rose',
                                'text' => 'Частка/прийменник <strong>не відривається</strong> від дієслова:',
                                'example' => 'The baby was looked after. ✅ (не: was looked the baby after ❌)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - типові фразові дієслова
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Типові фразові дієслова у пасиві',
                        'intro' => 'Найпоширеніші фразові дієслова, що вживаються в пасиві:',
                        'items' => [
                            [
                                'label' => 'look after',
                                'title' => 'доглядати',
                                'subtitle' => 'The children are looked after well.',
                            ],
                            [
                                'label' => 'bring up',
                                'title' => 'виховувати',
                                'subtitle' => 'She was brought up by her grandmother.',
                            ],
                            [
                                'label' => 'call off',
                                'title' => 'скасувати',
                                'subtitle' => 'The match was called off.',
                            ],
                            [
                                'label' => 'put off',
                                'title' => 'відкладати',
                                'subtitle' => 'The meeting was put off until Monday.',
                            ],
                            [
                                'label' => 'turn down',
                                'title' => 'відхиляти',
                                'subtitle' => 'His offer was turned down.',
                            ],
                            [
                                'label' => 'carry out',
                                'title' => 'виконувати',
                                'subtitle' => 'The experiment was carried out.',
                            ],
                            [
                                'label' => 'look into',
                                'title' => 'розслідувати',
                                'subtitle' => 'The matter is being looked into.',
                            ],
                            [
                                'label' => 'break into',
                                'title' => 'вламуватися',
                                'subtitle' => 'The house was broken into.',
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
                        'title' => '2. Порівняння Active vs Passive',
                        'intro' => 'Перетворення фразових дієслів з активу в пасив:',
                        'rows' => [
                            [
                                'en' => 'Active: They called off the game.',
                                'ua' => 'Вони скасували гру.',
                                'note' => '→ Passive: The game was called off.',
                            ],
                            [
                                'en' => 'Active: Someone broke into the car.',
                                'ua' => 'Хтось вламався в машину.',
                                'note' => '→ Passive: The car was broken into.',
                            ],
                            [
                                'en' => 'Active: They are looking into the problem.',
                                'ua' => 'Вони розслідують проблему.',
                                'note' => '→ Passive: The problem is being looked into.',
                            ],
                            [
                                'en' => 'Active: Her grandparents brought her up.',
                                'ua' => 'Її виховували бабуся і дідусь.',
                                'note' => '→ Passive: She was brought up by her grandparents.',
                            ],
                        ],
                        'warning' => '📌 Частка/прийменник ЗАВЖДИ залишається з дієсловом!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - за типами фразових дієслів
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Фразові дієслова у пасиві за категоріями',
                        'sections' => [
                            [
                                'label' => 'Скасування / відкладання',
                                'color' => 'rose',
                                'description' => 'call off, put off, cancel:',
                                'examples' => [
                                    ['en' => 'The concert was called off due to rain.', 'ua' => 'Концерт скасували через дощ.'],
                                    ['en' => 'The meeting has been put off.', 'ua' => 'Зустріч відклали.'],
                                    ['en' => 'The flight was put off until tomorrow.', 'ua' => 'Рейс перенесли на завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Виконання / здійснення',
                                'color' => 'emerald',
                                'description' => 'carry out, bring about, set up:',
                                'examples' => [
                                    ['en' => 'The research was carried out last year.', 'ua' => 'Дослідження провели минулого року.'],
                                    ['en' => 'Major changes were brought about.', 'ua' => 'Були внесені значні зміни.'],
                                    ['en' => 'A new company was set up.', 'ua' => 'Була створена нова компанія.'],
                                ],
                            ],
                            [
                                'label' => 'Відхилення / відмова',
                                'color' => 'blue',
                                'description' => 'turn down, rule out, throw out:',
                                'examples' => [
                                    ['en' => 'Her application was turned down.', 'ua' => 'Її заявку відхилили.'],
                                    ['en' => 'This option cannot be ruled out.', 'ua' => 'Цей варіант не можна виключати.'],
                                    ['en' => 'The proposal was thrown out.', 'ua' => 'Пропозицію відкинули.'],
                                ],
                            ],
                            [
                                'label' => 'Догляд / виховання',
                                'color' => 'amber',
                                'description' => 'look after, bring up, take care of:',
                                'examples' => [
                                    ['en' => 'The patient is being looked after.', 'ua' => 'За пацієнтом доглядають.'],
                                    ['en' => 'He was brought up in a small village.', 'ua' => 'Він виріс у маленькому селі.'],
                                    ['en' => 'Everything will be taken care of.', 'ua' => 'Про все подбають.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - різні часи
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Фразові дієслова у пасиві: різні часи',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => '<strong>am/is/are + V3 + particle</strong>:',
                                'examples' => [
                                    ['en' => 'Children are looked after at the nursery.', 'ua' => 'За дітьми доглядають у дитсадку.'],
                                    ['en' => 'Problems are dealt with quickly.', 'ua' => 'Проблеми вирішуються швидко.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => '<strong>was/were + V3 + particle</strong>:',
                                'examples' => [
                                    ['en' => 'The project was carried out successfully.', 'ua' => 'Проєкт було успішно виконано.'],
                                    ['en' => 'The meeting was called off yesterday.', 'ua' => 'Зустріч скасували вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'amber',
                                'description' => '<strong>has/have been + V3 + particle</strong>:',
                                'examples' => [
                                    ['en' => 'The event has been put off.', 'ua' => 'Захід відклали.'],
                                    ['en' => 'His idea has been turned down.', 'ua' => 'Його ідею відхилили.'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous',
                                'color' => 'rose',
                                'description' => '<strong>am/is/are being + V3 + particle</strong>:',
                                'examples' => [
                                    ['en' => 'The matter is being looked into.', 'ua' => 'Справу розслідують.'],
                                    ['en' => 'New measures are being brought in.', 'ua' => 'Впроваджуються нові заходи.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - prepositional verbs
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Дієслова з прийменниками у пасиві',
                        'intro' => 'Дієслова з фіксованими прийменниками також утворюють пасив:',
                        'items' => [
                            [
                                'label' => 'laugh at',
                                'title' => 'сміятися з',
                                'subtitle' => 'He hates being laughed at.',
                            ],
                            [
                                'label' => 'look at',
                                'title' => 'дивитися на',
                                'subtitle' => 'The painting is being looked at.',
                            ],
                            [
                                'label' => 'speak to',
                                'title' => 'говорити з',
                                'subtitle' => 'I don\'t like being spoken to like that.',
                            ],
                            [
                                'label' => 'refer to',
                                'title' => 'посилатися на',
                                'subtitle' => 'This book is often referred to.',
                            ],
                            [
                                'label' => 'rely on',
                                'title' => 'покладатися на',
                                'subtitle' => 'He can be relied on.',
                            ],
                            [
                                'label' => 'deal with',
                                'title' => 'мати справу з',
                                'subtitle' => 'The problem is being dealt with.',
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
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Відривання частки від дієслова.',
                                'wrong' => 'The baby was looked well after.',
                                'right' => '✅ The baby was looked after well.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск частки/прийменника.',
                                'wrong' => 'The meeting was called.',
                                'right' => '✅ The meeting was called off.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний прийменник.',
                                'wrong' => 'He was laughed on by everyone.',
                                'right' => '✅ He was laughed at by everyone.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів з by.',
                                'wrong' => 'The child was by her aunt brought up.',
                                'right' => '✅ The child was brought up by her aunt.',
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
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Пасив фразових дієслів: <strong>Object + be + V3 + particle/preposition</strong>.',
                            'Частка/прийменник <strong>ЗАВЖДИ залишається з дієсловом</strong>.',
                            'Типові: <strong>look after, bring up, call off, turn down, carry out</strong>.',
                            'Дієслова з прийменниками: <strong>laugh at, deal with, rely on</strong>.',
                            'by + agent ставиться <strong>після частки</strong>: brought up by her aunt.',
                            'Прислівники ставляться <strong>після частки</strong>: looked after well.',
                            'Не змінюй прийменник: <strong>laughed at</strong> (не: laughed on).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
