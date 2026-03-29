<?php

namespace Database\Seeders\Page_v2\BasicGrammar\WordOrder;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class AdvancedWordOrderEmphasisTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-advanced-word-order-emphasis';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Advanced Word Order and Emphasis — Інверсія та підсилення',
            'subtitle_html' => '<p><strong>Inversion</strong> (інверсія) та <strong>cleft sentences</strong> (розщеплені речення) — потужні інструменти для підсилення (emphasis) в англійській мові.</p>',
            'subtitle_text' => 'Теоретичний огляд інверсії, cleft-речень та інших способів підсилення з прикладами та практикою.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'word-order',
                'title' => 'Word Order — Порядок слів',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Inversion',
                'Emphasis',
                'Cleft Sentences',
                'Fronting',
                'Theory',
                'B1',
                'B2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш <strong>просунуті структури</strong> для підсилення: інверсію з негативними прислівниками, cleft-речення та емфатичне do.',
                        'rules' => [
                            [
                                'label' => 'Інверсія',
                                'color' => 'emerald',
                                'text' => 'Negative adverb + <strong>Auxiliary + Subject</strong>:',
                                'example' => 'Never have I seen such beauty.',
                            ],
                            [
                                'label' => 'It-cleft',
                                'color' => 'blue',
                                'text' => '<strong>It was/is ... who/that</strong> — підсилення елемента:',
                                'example' => 'It was you who invited me.',
                            ],
                            [
                                'label' => 'What-cleft',
                                'color' => 'amber',
                                'text' => '<strong>What ... is/was</strong> — підсилення дії:',
                                'example' => 'What I need is some rest.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Коли використовувати підсилення',
                        'intro' => 'Emphatic structures використовуються для:',
                        'items' => [
                            ['label' => '1', 'title' => 'Виділення інформації', 'subtitle' => 'Підкреслити хто, що, коли, де.'],
                            ['label' => '2', 'title' => 'Формальний стиль', 'subtitle' => 'У письмовій, офіційній мові.'],
                            ['label' => '3', 'title' => 'Контраст', 'subtitle' => 'Протиставлення або корекція.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Інверсія з негативними прислівниками',
                        'sections' => [
                            [
                                'label' => 'Структура інверсії',
                                'color' => 'emerald',
                                'description' => 'Коли речення починається з <strong>негативного прислівника</strong>, порядок слів змінюється: <strong>Auxiliary + Subject</strong>.',
                                'examples' => [
                                    ['en' => 'Never have I seen such a beautiful sunset.', 'ua' => 'Ніколи я не бачив такого заходу сонця.'],
                                    ['en' => 'Rarely does she make mistakes.', 'ua' => 'Вона рідко помиляється.'],
                                    ['en' => 'Seldom do we get such opportunities.', 'ua' => 'Рідко ми отримуємо такі можливості.'],
                                ],
                            ],
                            [
                                'label' => 'Not only ... but also',
                                'color' => 'sky',
                                'description' => 'Після <strong>Not only</strong> обов\'язкова інверсія в першій частині.',
                                'examples' => [
                                    ['en' => 'Not only did he finish early, but he also helped others.', 'ua' => 'Він не тільки закінчив рано, але й допоміг іншим.'],
                                    ['en' => 'Not only is she smart, but she is also kind.', 'ua' => 'Вона не тільки розумна, але й добра.'],
                                ],
                            ],
                            [
                                'label' => 'Hardly / No sooner',
                                'color' => 'amber',
                                'description' => 'Структури для "ледве ... як" / "щойно ... як".',
                                'examples' => [
                                    ['en' => 'Hardly had we arrived when it started raining.', 'ua' => 'Ледве ми приїхали, як почався дощ.'],
                                    ['en' => 'No sooner had I sat down than the phone rang.', 'ua' => 'Щойно я сів, як зазвонив телефон.'],
                                ],
                                'note' => 'Hardly/Scarcely + when; No sooner + than.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. It-cleft речення',
                        'sections' => [
                            [
                                'label' => 'Підсилення особи (who)',
                                'color' => 'emerald',
                                'description' => 'Структура <strong>It was/is + person + who</strong> підсилює хто виконав дію.',
                                'examples' => [
                                    ['en' => 'You invited me. → It was you who invited me.', 'ua' => 'Це саме ти запросив мене.'],
                                    ['en' => 'She broke the vase. → It was she who broke the vase.', 'ua' => 'Саме вона розбила вазу.'],
                                    ['en' => 'John called. → It was John who called.', 'ua' => 'Саме Джон телефонував.'],
                                ],
                            ],
                            [
                                'label' => 'Підсилення часу/місця/об\'єкта (that)',
                                'color' => 'sky',
                                'description' => 'Структура <strong>It was/is + element + that</strong> для підсилення не-осіб.',
                                'examples' => [
                                    ['en' => 'She left yesterday. → It was yesterday that she left.', 'ua' => 'Саме вчора вона пішла.'],
                                    ['en' => 'I need help. → It is help that I need.', 'ua' => 'Саме допомога мені потрібна.'],
                                    ['en' => 'We met in Paris. → It was in Paris that we met.', 'ua' => 'Саме в Парижі ми зустрілися.'],
                                ],
                                'note' => 'Для людей — who, для решти — that.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. What-cleft речення (Pseudo-cleft)',
                        'sections' => [
                            [
                                'label' => 'Структура What-cleft',
                                'color' => 'emerald',
                                'description' => '<strong>What + clause + is/was + emphasized element</strong> — підсилює дію або об\'єкт.',
                                'examples' => [
                                    ['en' => 'I need rest. → What I need is some rest.', 'ua' => 'Те, що мені потрібно — це відпочинок.'],
                                    ['en' => 'I like the park. → What I like is the park.', 'ua' => 'Те, що мені подобається — це парк.'],
                                    ['en' => 'She wants a vacation. → What she wants is a vacation.', 'ua' => 'Те, чого вона хоче — це відпустка.'],
                                ],
                            ],
                            [
                                'label' => 'Інші Wh-cleft структури',
                                'color' => 'sky',
                                'description' => 'Можна використовувати <strong>Where, Why, Who, How</strong> для різних типів підсилення.',
                                'examples' => [
                                    ['en' => 'Where I grew up is very far from here.', 'ua' => 'Там, де я виріс, дуже далеко звідси.'],
                                    ['en' => 'Why she left is still a mystery.', 'ua' => 'Чому вона пішла — досі загадка.'],
                                    ['en' => 'How he did it amazed everyone.', 'ua' => 'Те, як він це зробив, здивувало всіх.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Fronting та Emphatic do',
                        'sections' => [
                            [
                                'label' => 'Fronting',
                                'color' => 'emerald',
                                'description' => '<strong>Fronting</strong> — винесення елемента на початок речення для акценту або контрасту.',
                                'examples' => [
                                    ['en' => 'The kids left quickly. → Quickly the kids left.', 'ua' => 'Швидко діти пішли.'],
                                    ['en' => 'I like this book very much. → This book I like very much.', 'ua' => 'Цю книгу я дуже люблю.'],
                                    ['en' => 'Away ran the children.', 'ua' => 'Геть побігли діти.'],
                                ],
                                'note' => 'Часто використовується в літературі та formal English.',
                            ],
                            [
                                'label' => 'Emphatic do/does/did',
                                'color' => 'sky',
                                'description' => 'Для підсилення у <strong>стверджувальних</strong> реченнях використовуй do/does/did з наголосом.',
                                'examples' => [
                                    ['en' => 'I like it. → I DO like it!', 'ua' => 'Мені це справді подобається!'],
                                    ['en' => 'She finished. → She DID finish her homework!', 'ua' => 'Вона таки зробила домашнє!'],
                                    ['en' => 'He knows. → He DOES know the answer.', 'ua' => 'Він справді знає відповідь.'],
                                ],
                                'note' => 'Часто використовується в розмовній мові для підтвердження.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Негативні прислівники для інверсії',
                        'intro' => 'Запам\'ятай ці вирази — після них обов\'язкова інверсія:',
                        'rows' => [
                            [
                                'en' => 'Never',
                                'ua' => 'ніколи',
                                'note' => 'Never have I...',
                            ],
                            [
                                'en' => 'Rarely / Seldom',
                                'ua' => 'рідко',
                                'note' => 'Rarely does she...',
                            ],
                            [
                                'en' => 'Hardly / Scarcely',
                                'ua' => 'ледве',
                                'note' => 'Hardly had we...',
                            ],
                            [
                                'en' => 'Not only',
                                'ua' => 'не тільки',
                                'note' => 'Not only did he...',
                            ],
                            [
                                'en' => 'No sooner',
                                'ua' => 'щойно',
                                'note' => 'No sooner had I...',
                            ],
                            [
                                'en' => 'Only when/after/if',
                                'ua' => 'тільки коли/після/якщо',
                                'note' => 'Only when I arrived did I...',
                            ],
                        ],
                        'warning' => '📌 Якщо немає допоміжного дієслова — додай do/does/did.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Відсутня інверсія після негативного прислівника.',
                                'wrong' => 'Never I have seen...',
                                'right' => '✅ <span class="font-mono">Never have I seen...</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Which замість who для людей.',
                                'wrong' => 'It was you which invited me.',
                                'right' => '✅ <span class="font-mono">It was you who invited me.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Інфінітив замість іменника у What-cleft.',
                                'wrong' => 'What I need is to rest.',
                                'right' => '✅ <span class="font-mono">What I need is some rest.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Дієслово з -s після emphatic do.',
                                'wrong' => 'He do knows the answer.',
                                'right' => '✅ <span class="font-mono">He does know the answer.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Інверсія: <strong>Neg adverb + Aux + S + V</strong> (Never have I seen...).',
                            'It-cleft: <strong>It was/is + X + who/that</strong> (It was you who...).',
                            'What-cleft: <strong>What + clause + is/was</strong> (What I need is...).',
                            'Fronting: винесення елемента на початок для акценту.',
                            'Emphatic do: <strong>S + do/does/did + base V</strong> для підсилення.',
                            'Ці структури типові для <strong>formal</strong> та <strong>written</strong> English.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери речення з правильною структурою.',
                        'selects' => [
                            ['label' => 'a) Never I have seen... / b) Never have I seen...', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) It was you who invited me. / b) It was you which invited me.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) What I need is some rest. / b) What I need is to rest.', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Перетвори на It-cleft',
                        'input_intro' => 'Підсиль виділений елемент за допомогою It-cleft.',
                        'inputs' => [
                            ['before' => 'YOU called me. →', 'after' => 'It was...'],
                            ['before' => 'She left YESTERDAY. →', 'after' => 'It was...'],
                            ['before' => 'I need HELP. →', 'after' => 'It is...'],
                        ],
                        'rephrase_title' => 'Вправа 3. Додай інверсію',
                        'rephrase_intro' => 'Перетвори речення, починаючи з негативного прислівника.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I have never seen such beauty.',
                                'example_target' => 'Never have I seen such beauty.',
                            ],
                            [
                                'original' => '1. She rarely makes mistakes.',
                                'placeholder' => 'Rarely...',
                            ],
                            [
                                'original' => '2. He not only finished early, but also helped.',
                                'placeholder' => 'Not only...',
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
                                'label' => 'Basic Word Order — Порядок слів у ствердженні',
                                'current' => false,
                            ],
                            [
                                'label' => 'Word Order in Questions and Negatives — Питання та заперечення',
                                'current' => false,
                            ],
                            [
                                'label' => 'Word Order with Adverbs — Прислівники та обставини',
                                'current' => false,
                            ],
                            [
                                'label' => 'Word Order with Verbs and Objects — Дієслова та додатки',
                                'current' => false,
                            ],
                            [
                                'label' => 'Advanced Word Order — Інверсія та підсилення (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
