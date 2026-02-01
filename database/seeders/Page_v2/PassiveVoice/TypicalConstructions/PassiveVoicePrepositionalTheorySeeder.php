<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

class PassiveVoicePrepositionalTheorySeeder extends PassiveVoiceTypicalConstructionsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-prepositional';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Prepositional Passive — пасив з прийменниковими дієсловами',
            'subtitle_html' => '<p><strong>Prepositional Passive</strong> — це пасивний стан з дієсловами, які вимагають прийменника: look after, speak to, pay for, laugh at. У пасиві прийменник залишається з дієсловом: "The children were looked after" (за дітьми доглядали).</p>',
            'subtitle_text' => 'Пасив з прийменниковими дієсловами: be looked after, be spoken to, be paid for, be laughed at. Прийменник залишається після дієслова.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-typical-constructions',
                'title' => 'Типові конструкції й "фішки"',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Prepositional Passive',
                'Phrasal verbs',
                'look after',
                'speak to',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти навчишся утворювати <strong>пасив з прийменниковими дієсловами</strong>: look after, speak to, pay for та інші. Головне правило: <strong>прийменник залишається з дієсловом</strong>.',
                        'rules' => [
                            [
                                'label' => 'Active',
                                'color' => 'emerald',
                                'text' => 'Активний стан з прийменником:',
                                'example' => 'Someone looked after the children.',
                            ],
                            [
                                'label' => 'Passive',
                                'color' => 'blue',
                                'text' => 'Пасив — прийменник залишається:',
                                'example' => 'The children were looked after.',
                            ],
                            [
                                'label' => 'Правило',
                                'color' => 'rose',
                                'text' => '<strong>V + preposition</strong> → <strong>be + V3 + preposition</strong>',
                                'example' => 'speak to → be spoken to',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Як утворити Prepositional Passive',
                        'intro' => 'Формула пасиву з прийменниковими дієсловами:',
                        'items' => [
                            [
                                'label' => 'Формула',
                                'title' => 'be + V3 + preposition',
                                'subtitle' => 'Прийменник завжди залишається в кінці',
                            ],
                            [
                                'label' => 'Приклад',
                                'title' => 'look after → be looked after',
                                'subtitle' => 'She was looked after by her grandmother.',
                            ],
                            [
                                'label' => 'Не змінюй',
                                'title' => 'Прийменник не рухається',
                                'subtitle' => '❌ After her was looked (неправильно)',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Можна додати "by"',
                                'subtitle' => 'She was spoken to by the manager.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Типові прийменникові дієслова',
                        'sections' => [
                            [
                                'label' => 'look after / look at',
                                'color' => 'emerald',
                                'description' => '<strong>look after</strong> — доглядати. <strong>look at</strong> — дивитися на.',
                                'examples' => [
                                    ['en' => 'Active: Someone looks after the children.', 'ua' => 'Хтось доглядає за дітьми.'],
                                    ['en' => 'Passive: The children are looked after.', 'ua' => 'За дітьми доглядають.'],
                                    ['en' => 'Active: Everyone looked at him.', 'ua' => 'Усі дивилися на нього.'],
                                    ['en' => 'Passive: He was looked at by everyone.', 'ua' => 'На нього дивилися всі.'],
                                ],
                            ],
                            [
                                'label' => 'speak to / talk about',
                                'color' => 'blue',
                                'description' => '<strong>speak to</strong> — розмовляти з. <strong>talk about</strong> — говорити про.',
                                'examples' => [
                                    ['en' => 'Active: The manager spoke to her.', 'ua' => 'Менеджер поговорив з нею.'],
                                    ['en' => 'Passive: She was spoken to by the manager.', 'ua' => 'З нею поговорив менеджер.'],
                                    ['en' => 'Active: Everyone talks about this issue.', 'ua' => 'Усі говорять про це питання.'],
                                    ['en' => 'Passive: This issue is talked about a lot.', 'ua' => 'Про це питання багато говорять.'],
                                ],
                            ],
                            [
                                'label' => 'pay for / account for',
                                'color' => 'sky',
                                'description' => '<strong>pay for</strong> — платити за. <strong>account for</strong> — пояснювати.',
                                'examples' => [
                                    ['en' => 'Active: The company paid for the training.', 'ua' => 'Компанія оплатила навчання.'],
                                    ['en' => 'Passive: The training was paid for.', 'ua' => 'Навчання було оплачене.'],
                                    ['en' => 'Active: This accounts for 50% of sales.', 'ua' => 'Це складає 50% продажів.'],
                                    ['en' => 'Passive: 50% of sales is accounted for by this.', 'ua' => '50% продажів припадає на це.'],
                                ],
                            ],
                            [
                                'label' => 'laugh at / shout at',
                                'color' => 'amber',
                                'description' => '<strong>laugh at</strong> — сміятися з. <strong>shout at</strong> — кричати на.',
                                'examples' => [
                                    ['en' => 'Active: They laughed at his joke.', 'ua' => 'Вони посміялися з його жарту.'],
                                    ['en' => 'Passive: His joke was laughed at.', 'ua' => 'З його жарту посміялися.'],
                                    ['en' => 'Active: The boss shouted at him.', 'ua' => 'Бос накричав на нього.'],
                                    ['en' => 'Passive: He was shouted at by the boss.', 'ua' => 'На нього накричав бос.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Phrasal verbs у пасиві',
                        'sections' => [
                            [
                                'label' => 'Фразові дієслова',
                                'color' => 'emerald',
                                'description' => '<strong>Phrasal verbs</strong> з прийменниками/частками також утворюють пасив.',
                                'examples' => [
                                    ['en' => 'Active: They called off the meeting.', 'ua' => 'Вони скасували зустріч.'],
                                    ['en' => 'Passive: The meeting was called off.', 'ua' => 'Зустріч була скасована.'],
                                    ['en' => 'Active: Someone broke into the house.', 'ua' => 'Хтось вламався в будинок.'],
                                    ['en' => 'Passive: The house was broken into.', 'ua' => 'У будинок вламалися.'],
                                ],
                            ],
                            [
                                'label' => 'put off / turn down',
                                'color' => 'blue',
                                'description' => '<strong>put off</strong> — відкладати. <strong>turn down</strong> — відхиляти.',
                                'examples' => [
                                    ['en' => 'Active: They put off the decision.', 'ua' => 'Вони відклали рішення.'],
                                    ['en' => 'Passive: The decision was put off.', 'ua' => 'Рішення було відкладене.'],
                                    ['en' => 'Active: They turned down my application.', 'ua' => 'Вони відхилили мою заявку.'],
                                    ['en' => 'Passive: My application was turned down.', 'ua' => 'Мою заявку відхилили.'],
                                ],
                            ],
                            [
                                'label' => 'bring up / take care of',
                                'color' => 'sky',
                                'description' => '<strong>bring up</strong> — виховувати. <strong>take care of</strong> — піклуватися.',
                                'examples' => [
                                    ['en' => 'Active: Her grandmother brought her up.', 'ua' => 'Бабуся її виховала.'],
                                    ['en' => 'Passive: She was brought up by her grandmother.', 'ua' => 'Її виховала бабуся.'],
                                    ['en' => 'Active: Someone took care of the problem.', 'ua' => 'Хтось вирішив проблему.'],
                                    ['en' => 'Passive: The problem was taken care of.', 'ua' => 'Проблему вирішили.'],
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
                        'title' => '4. Зведення: Active → Passive',
                        'intro' => 'Трансформація прийменникових дієслів:',
                        'rows' => [
                            [
                                'en' => 'look after',
                                'ua' => 'доглядати за',
                                'note' => 'The baby was looked after.',
                            ],
                            [
                                'en' => 'speak to',
                                'ua' => 'говорити з',
                                'note' => 'She was spoken to.',
                            ],
                            [
                                'en' => 'pay for',
                                'ua' => 'платити за',
                                'note' => 'The meal was paid for.',
                            ],
                            [
                                'en' => 'laugh at',
                                'ua' => 'сміятися з',
                                'note' => 'He was laughed at.',
                            ],
                            [
                                'en' => 'deal with',
                                'ua' => 'мати справу з',
                                'note' => 'The issue was dealt with.',
                            ],
                            [
                                'en' => 'refer to',
                                'ua' => 'посилатися на',
                                'note' => 'This book is often referred to.',
                            ],
                        ],
                        'warning' => '📌 <strong>Прийменник завжди залишається в кінці речення!</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск прийменника.',
                                'wrong' => 'The children were looked.',
                                'right' => '✅ The children were looked after.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Прийменник на початку.',
                                'wrong' => 'After the children was looked.',
                                'right' => '✅ The children were looked after.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Зміна прийменника.',
                                'wrong' => 'She was spoken with.',
                                'right' => '✅ She was spoken to.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Пропуск частки у phrasal verb.',
                                'wrong' => 'The meeting was called.',
                                'right' => '✅ The meeting was called off.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Короткий конспект',
                        'items' => [
                            '<strong>Prepositional Passive</strong>: be + V3 + preposition (прийменник у кінці).',
                            'Приклади: <strong>be looked after, be spoken to, be paid for, be laughed at</strong>.',
                            '<strong>Phrasal verbs</strong> теж утворюють пасив: be called off, be broken into.',
                            '<strong>Не видаляй і не переміщуй</strong> прийменник/частку!',
                            'Agent (by + виконавець) можна додати за потреби.',
                            'Типові дієслова: look after, speak to, pay for, deal with, take care of.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
