<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\BasicGrammar\BasicGrammarPageSeeder;

class BasicGrammarConjunctionsTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'basic-conjunctions-and-but-or-because-so';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Basic Conjunctions — and, but, or, because, so',
            'subtitle_html' => "<p><strong>Conjunctions</strong> (сполучники) — це слова, які зʼєднують слова, фрази або речення. Базові сполучники <strong>and, but, or, because, so</strong> є найуживанішими в англійській мові.</p>",
            'subtitle_text' => 'Теоретичний огляд базових сполучників англійської мови: and, but, or, because, so — значення, використання та приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Conjunctions',
                'Basic Grammar',
                'And',
                'But',
                'Or',
                'Because',
                'So',
                'Coordinating Conjunctions',
                'Linking Words',
                'Theory',
                'A1',
                'A2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => "У цій темі ти вивчиш <strong>пʼять базових сполучників</strong> англійської мови: and, but, or, because, so — їхнє значення та правила використання.",
                        'rules' => [
                            [
                                'label' => 'And',
                                'color' => 'emerald',
                                'text' => "<strong>І, та</strong> — зʼєднує схожі ідеї:",
                                'example' => 'I like tea and coffee.',
                            ],
                            [
                                'label' => 'But',
                                'color' => 'rose',
                                'text' => '<strong>Але, проте</strong> — показує контраст:',
                                'example' => "I like tea, but I don't like coffee.",
                            ],
                            [
                                'label' => 'Or',
                                'color' => 'blue',
                                'text' => '<strong>Або</strong> — показує вибір:',
                                'example' => 'Do you want tea or coffee?',
                            ],
                            [
                                'label' => 'Because',
                                'color' => 'amber',
                                'text' => '<strong>Тому що</strong> — пояснює причину:',
                                'example' => "I stayed home because it was raining.",
                            ],
                            [
                                'label' => 'So',
                                'color' => 'purple',
                                'text' => '<strong>Тому, отже</strong> — показує результат:',
                                'example' => 'It was raining, so I stayed home.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке сполучники?',
                        'intro' => "Сполучники (Conjunctions) — це слова, які зʼєднують:",
                        'items' => [
                            ['label' => 'Words', 'title' => 'Слова', 'subtitle' => 'tea and coffee / big but cheap'],
                            ['label' => 'Phrases', 'title' => 'Фрази', 'subtitle' => 'in the morning or in the evening'],
                            ['label' => 'Clauses', 'title' => 'Речення', 'subtitle' => 'I came home and I cooked dinner.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. AND — і, та (додавання)',
                        'sections' => [
                            [
                                'label' => 'Значення',
                                'color' => 'emerald',
                                'description' => "<strong>And</strong> зʼєднує схожі або рівноцінні елементи. Додає інформацію.",
                                'examples' => [
                                    ['en' => 'I have a cat and a dog.', 'ua' => 'У мене є кіт і собака.'],
                                    ['en' => 'She is smart and beautiful.', 'ua' => 'Вона розумна і красива.'],
                                    ['en' => 'I came home and watched TV.', 'ua' => 'Я прийшов додому і дивився телевізор.'],
                                ],
                            ],
                            [
                                'label' => 'Зʼєднання слів',
                                'color' => 'sky',
                                'description' => '<strong>And</strong> може зʼєднувати іменники, прикметники, дієслова.',
                                'examples' => [
                                    ['en' => 'bread and butter', 'ua' => 'хліб і масло (іменники)'],
                                    ['en' => 'big and comfortable', 'ua' => 'великий і зручний (прикметники)'],
                                    ['en' => 'run and jump', 'ua' => 'бігати і стрибати (дієслова)'],
                                ],
                            ],
                            [
                                'label' => 'Зʼєднання речень',
                                'color' => 'amber',
                                'description' => 'При зʼєднанні двох речень перед <strong>and</strong> часто ставиться кома.',
                                'examples' => [
                                    ['en' => 'I woke up early, and I went for a run.', 'ua' => 'Я прокинувся рано і пішов на пробіжку.'],
                                    ['en' => 'She cooked dinner, and he washed the dishes.', 'ua' => 'Вона приготувала вечерю, а він помив посуд.'],
                                ],
                                'note' => 'Кома перед <strong>and</strong> необовʼязкова, якщо речення короткі.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. BUT — але, проте (контраст)',
                        'sections' => [
                            [
                                'label' => 'Значення',
                                'color' => 'rose',
                                'description' => '<strong>But</strong> показує контраст, протиставлення або несподіваний результат.',
                                'examples' => [
                                    ['en' => "I like coffee, but I don't like tea.", 'ua' => 'Я люблю каву, але не люблю чай.'],
                                    ['en' => 'She is young but very experienced.', 'ua' => 'Вона молода, але дуже досвідчена.'],
                                    ['en' => "I tried hard, but I couldn't finish.", 'ua' => 'Я намагався, але не зміг закінчити.'],
                                ],
                            ],
                            [
                                'label' => 'Протиставлення',
                                'color' => 'sky',
                                'description' => '<strong>But</strong> вводить інформацію, яка суперечить очікуванням.',
                                'examples' => [
                                    ['en' => 'The hotel was expensive, but it was worth it.', 'ua' => 'Готель був дорогий, але воно того вартувало.'],
                                    ['en' => "He's rich, but he's not happy.", 'ua' => 'Він багатий, але він не щасливий.'],
                                ],
                            ],
                            [
                                'label' => 'Пунктуація',
                                'color' => 'amber',
                                'description' => 'Перед <strong>but</strong> зазвичай ставиться кома, коли зʼєднуються два повних речення.',
                                'examples' => [
                                    ['en' => 'I wanted to go, but it was too late.', 'ua' => 'Я хотів піти, але було надто пізно.'],
                                ],
                                'note' => 'Без коми: <em>small but cozy</em> (між словами).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. OR — або (вибір)',
                        'sections' => [
                            [
                                'label' => 'Значення',
                                'color' => 'blue',
                                'description' => '<strong>Or</strong> показує альтернативу, вибір між варіантами.',
                                'examples' => [
                                    ['en' => 'Do you want tea or coffee?', 'ua' => 'Ти хочеш чай чи каву?'],
                                    ['en' => 'We can go today or tomorrow.', 'ua' => 'Ми можемо піти сьогодні або завтра.'],
                                    ['en' => 'Is it true or false?', 'ua' => 'Це правда чи брехня?'],
                                ],
                            ],
                            [
                                'label' => 'У питаннях',
                                'color' => 'sky',
                                'description' => '<strong>Or</strong> часто використовується в питаннях для пропозиції вибору.',
                                'examples' => [
                                    ['en' => 'Do you prefer cats or dogs?', 'ua' => 'Ти віддаєш перевагу котам чи собакам?'],
                                    ['en' => 'Should I call or text you?', 'ua' => 'Мені зателефонувати чи написати тобі?'],
                                ],
                            ],
                            [
                                'label' => 'У запереченнях',
                                'color' => 'amber',
                                'description' => 'В заперечних реченнях часто використовують <strong>or</strong> замість <strong>and</strong>.',
                                'examples' => [
                                    ['en' => "I don't like tea or coffee.", 'ua' => 'Я не люблю ні чаю, ні кави.'],
                                    ['en' => "She doesn't eat meat or fish.", 'ua' => "Вона не їсть мʼяса ні риби."],
                                ],
                                'note' => 'Порівняй: <em>I like tea <strong>and</strong> coffee</em> vs <em>I don\'t like tea <strong>or</strong> coffee</em>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. BECAUSE — тому що (причина)',
                        'sections' => [
                            [
                                'label' => 'Значення',
                                'color' => 'amber',
                                'description' => '<strong>Because</strong> вводить причину, пояснює чому щось сталося.',
                                'examples' => [
                                    ['en' => 'I stayed home because it was raining.', 'ua' => 'Я залишився вдома, тому що йшов дощ.'],
                                    ['en' => "She's tired because she worked all day.", 'ua' => 'Вона втомлена, тому що працювала весь день.'],
                                    ['en' => "I can't come because I'm busy.", 'ua' => 'Я не можу прийти, тому що я зайнятий.'],
                                ],
                            ],
                            [
                                'label' => 'Позиція в реченні',
                                'color' => 'sky',
                                'description' => 'Речення з <strong>because</strong> може стояти на початку або в кінці.',
                                'examples' => [
                                    ['en' => "I stayed home because it was raining.", 'ua' => '(причина в кінці)'],
                                    ['en' => 'Because it was raining, I stayed home.', 'ua' => '(причина на початку — з комою)'],
                                ],
                            ],
                            [
                                'label' => 'Because vs Because of',
                                'color' => 'emerald',
                                'description' => '<strong>Because</strong> + речення, <strong>because of</strong> + іменник.',
                                'examples' => [
                                    ['en' => "I stayed home because it was raining.", 'ua' => '(because + речення)'],
                                    ['en' => 'I stayed home because of the rain.', 'ua' => '(because of + іменник)'],
                                ],
                                'note' => 'Не плутай: <em>because</em> (тому що) vs <em>because of</em> (через).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. SO — тому, отже (результат)',
                        'sections' => [
                            [
                                'label' => 'Значення',
                                'color' => 'purple',
                                'description' => '<strong>So</strong> вводить результат, наслідок попередньої дії або ситуації.',
                                'examples' => [
                                    ['en' => 'It was raining, so I stayed home.', 'ua' => 'Йшов дощ, тому я залишився вдома.'],
                                    ['en' => "I was hungry, so I made a sandwich.", 'ua' => 'Я був голодний, тому зробив бутерброд.'],
                                    ['en' => "She studied hard, so she passed the exam.", 'ua' => 'Вона наполегливо вчилася, тому склала іспит.'],
                                ],
                            ],
                            [
                                'label' => 'Because vs So',
                                'color' => 'sky',
                                'description' => '<strong>Because</strong> = причина, <strong>So</strong> = результат. Одна інформація, різний порядок.',
                                'examples' => [
                                    ['en' => 'I stayed home because it was raining.', 'ua' => '(результат + причина)'],
                                    ['en' => 'It was raining, so I stayed home.', 'ua' => '(причина + результат)'],
                                ],
                            ],
                            [
                                'label' => 'Пунктуація',
                                'color' => 'amber',
                                'description' => 'Перед <strong>so</strong> зазвичай ставиться кома.',
                                'examples' => [
                                    ['en' => 'The shop was closed, so we went home.', 'ua' => 'Магазин був зачинений, тому ми пішли додому.'],
                                    ['en' => "I don't have money, so I can't buy it.", 'ua' => 'У мене немає грошей, тому я не можу це купити.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Порівняльна таблиця',
                        'intro' => "Пʼять базових сполучників та їхні функції:",
                        'rows' => [
                            [
                                'en' => 'AND',
                                'ua' => 'і, та',
                                'note' => 'додавання: I like tea and coffee.',
                            ],
                            [
                                'en' => 'BUT',
                                'ua' => 'але, проте',
                                'note' => "контраст: I like tea, but I don't like coffee.",
                            ],
                            [
                                'en' => 'OR',
                                'ua' => 'або',
                                'note' => 'вибір: Do you want tea or coffee?',
                            ],
                            [
                                'en' => 'BECAUSE',
                                'ua' => 'тому що',
                                'note' => 'причина: I stayed home because it was raining.',
                            ],
                            [
                                'en' => 'SO',
                                'ua' => 'тому, отже',
                                'note' => 'результат: It was raining, so I stayed home.',
                            ],
                        ],
                        'warning' => '📌 <strong>And, but, or</strong> — координаційні сполучники. <strong>Because, so</strong> — підрядні/результативні.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Пунктуація зі сполучниками',
                        'intro' => 'Коли ставити кому перед сполучником:',
                        'rows' => [
                            [
                                'en' => 'Between words',
                                'ua' => 'Між словами',
                                'note' => 'Без коми: tea and coffee, big but cheap',
                            ],
                            [
                                'en' => 'Between clauses',
                                'ua' => 'Між реченнями',
                                'note' => 'З комою: I came, and she left.',
                            ],
                            [
                                'en' => 'Short clauses',
                                'ua' => 'Короткі речення',
                                'note' => 'Кома необовʼязкова: I came and she left.',
                            ],
                        ],
                        'warning' => '⚠️ Перед <strong>but, so</strong> кома зазвичай ставиться при зʼєднанні речень.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Плутання because та because of.',
                                'wrong' => 'I stayed home because of it was raining.',
                                'right' => '✅ <span class="font-mono">I stayed home because it was raining.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання and замість or у запереченнях.',
                                'wrong' => "I don't like tea and coffee.",
                                'right' => "✅ <span class=\"font-mono\">I don't like tea or coffee.</span>",
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Because на початку без коми.',
                                'wrong' => 'Because it was raining I stayed home.',
                                'right' => '✅ <span class="font-mono">Because it was raining, I stayed home.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'So that замість so (для результату).',
                                'wrong' => 'I was tired so that I went to bed.',
                                'right' => '✅ <span class="font-mono">I was tired, so I went to bed.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            "<strong>And</strong> (і, та) — зʼєднує схожі елементи: <em>tea and coffee</em>.",
                            '<strong>But</strong> (але) — показує контраст: <em>cheap but good</em>.',
                            '<strong>Or</strong> (або) — показує вибір: <em>tea or coffee?</em>',
                            '<strong>Because</strong> (тому що) — пояснює причину: <em>because it was raining</em>.',
                            '<strong>So</strong> (тому) — показує результат: <em>so I stayed home</em>.',
                            "У запереченнях використовуй <strong>or</strong>: <em>I don't like tea or coffee</em>.",
                            'Перед <strong>but, so</strong> ставиться кома при зʼєднанні речень.',
                            '<strong>Because</strong> + речення, <strong>because of</strong> + іменник.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильний сполучник',
                        'select_intro' => 'Заповни пропуск відповідним сполучником.',
                        'selects' => [
                            ['label' => 'I like tea ___ coffee. (and / but / or)', 'prompt' => 'Який сполучник?'],
                            ['label' => "She is smart ___ she doesn't study much. (and / but / because)", 'prompt' => 'Який сполучник?'],
                            ['label' => 'Do you want pizza ___ pasta? (and / but / or)', 'prompt' => 'Який сполучник?'],
                            ['label' => 'I stayed home ___ it was raining. (but / because / so)', 'prompt' => 'Який сполучник?'],
                        ],
                        'options' => ['and', 'but', 'or', 'because', 'so'],
                        'input_title' => "Вправа 2. Зʼєднай речення",
                        'input_intro' => "Зʼєднай два речення за допомогою відповідного сполучника.",
                        'inputs' => [
                            ['before' => 'I was tired. I went to bed. (so) →', 'after' => ''],
                            ['before' => "She's rich. She's not happy. (but) →", 'after' => ''],
                            ['before' => "I can't come. I'm busy. (because) →", 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у використанні сполучника.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => "I don't like tea and coffee.",
                                'example_target' => "I don't like tea or coffee.",
                            ],
                            [
                                'original' => '1. I stayed home because of it was cold.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Because I was hungry I made lunch.',
                                'placeholder' => 'Виправ помилку (пунктуація)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з базової граматики',
                        'items' => [
                            [
                                'label' => 'Basic Conjunctions — and, but, or, because, so (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Imperatives — Наказові речення',
                                'current' => false,
                            ],
                            [
                                'label' => 'Sentence Types — Види речень',
                                'current' => false,
                            ],
                            [
                                'label' => 'Sentence Structure — Будова речення S–V–O',
                                'current' => false,
                            ],
                            [
                                'label' => 'Parts of Speech — Частини мови',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
