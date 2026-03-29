<?php

namespace Database\Seeders\Page_v2\BasicGrammar\WordOrder;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class WordOrderAdverbsAdverbialsTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-word-order-adverbs-adverbials';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order with Adverbs and Adverbials — Прислівники та обставини',
            'subtitle_html' => '<p><strong>Adverbs</strong> (прислівники) та <strong>adverbials</strong> (обставини) мають своє чітке місце в англійському реченні. Їхнє розташування залежить від типу прислівника.</p>',
            'subtitle_text' => 'Теоретичний огляд позиції прислівників та обставин в англійських реченнях з прикладами та практикою.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'word-order',
                'title' => 'Word Order — Порядок слів',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Adverbs',
                'Adverbials',
                'Manner',
                'Place',
                'Time',
                'Frequency',
                'Theory',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти вивчиш <strong>позицію прислівників</strong> в англійських реченнях: прислівники частотності, способу дії, місця, часу та ступеня.',
                        'rules' => [
                            [
                                'label' => 'Frequency',
                                'color' => 'emerald',
                                'text' => '<strong>Always, often, never</strong> — перед основним дієсловом, після to be:',
                                'example' => 'She always eats breakfast.',
                            ],
                            [
                                'label' => 'Manner',
                                'color' => 'blue',
                                'text' => '<strong>Quickly, well, carefully</strong> — в кінці речення:',
                                'example' => 'He speaks English fluently.',
                            ],
                            [
                                'label' => 'Place + Time',
                                'color' => 'amber',
                                'text' => '<strong>Порядок:</strong> Manner → Place → Time:',
                                'example' => 'She worked hard in London last year.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Три позиції прислівників',
                        'intro' => 'Прислівники можуть займати три основні позиції в реченні:',
                        'items' => [
                            ['label' => 'Front', 'title' => 'На початку', 'subtitle' => 'Перед підметом: Yesterday, I met my friend.'],
                            ['label' => 'Mid', 'title' => 'Посередині', 'subtitle' => 'Між підметом і дієсловом: She always drinks coffee.'],
                            ['label' => 'End', 'title' => 'В кінці', 'subtitle' => 'Після дієслова/додатка: He speaks English fluently.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Прислівники частотності (Adverbs of Frequency)',
                        'sections' => [
                            [
                                'label' => 'Перед основним дієсловом',
                                'color' => 'emerald',
                                'description' => 'Прислівники <strong>always, usually, often, sometimes, rarely, never</strong> ставляться <strong>перед</strong> основним дієсловом.',
                                'examples' => [
                                    ['en' => 'She always eats breakfast.', 'ua' => 'Вона завжди снідає.'],
                                    ['en' => 'I often go to the gym.', 'ua' => 'Я часто ходжу до спортзалу.'],
                                    ['en' => 'They never drink coffee.', 'ua' => 'Вони ніколи не пʼють каву.'],
                                ],
                            ],
                            [
                                'label' => 'Після дієслова TO BE',
                                'color' => 'sky',
                                'description' => 'Якщо в реченні дієслово <strong>to be</strong>, прислівник частотності ставиться <strong>після</strong> нього.',
                                'examples' => [
                                    ['en' => 'He is always late.', 'ua' => 'Він завжди запізнюється.'],
                                    ['en' => 'She is usually happy.', 'ua' => 'Вона зазвичай щаслива.'],
                                    ['en' => 'They are rarely bored.', 'ua' => 'Їм рідко нудно.'],
                                ],
                            ],
                            [
                                'label' => 'Після допоміжного дієслова',
                                'color' => 'amber',
                                'description' => 'У складених часах прислівник стоїть <strong>після</strong> допоміжного дієслова.',
                                'examples' => [
                                    ['en' => 'I have never been to Paris.', 'ua' => 'Я ніколи не був у Парижі.'],
                                    ['en' => 'She has always wanted a dog.', 'ua' => 'Вона завжди хотіла собаку.'],
                                    ['en' => 'They will usually arrive on time.', 'ua' => 'Вони зазвичай приїжджають вчасно.'],
                                ],
                                'note' => '<strong>Sometimes, usually, often</strong> можуть стояти на початку для акценту.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Прислівники способу дії (Adverbs of Manner)',
                        'sections' => [
                            [
                                'label' => 'В кінці речення',
                                'color' => 'emerald',
                                'description' => 'Прислівники <strong>quickly, slowly, carefully, well, badly, beautifully</strong> зазвичай стоять <strong>в кінці</strong> речення.',
                                'examples' => [
                                    ['en' => 'She sings beautifully.', 'ua' => 'Вона гарно співає.'],
                                    ['en' => 'He finished the test quickly.', 'ua' => 'Він швидко закінчив тест.'],
                                    ['en' => 'They work hard every day.', 'ua' => 'Вони щодня наполегливо працюють.'],
                                ],
                            ],
                            [
                                'label' => 'Після додатка',
                                'color' => 'sky',
                                'description' => 'Якщо є додаток, прислівник способу дії стоїть <strong>після</strong> нього.',
                                'examples' => [
                                    ['en' => 'She speaks English fluently.', 'ua' => 'Вона вільно розмовляє англійською.'],
                                    ['en' => 'He plays the piano well.', 'ua' => 'Він добре грає на піаніно.'],
                                    ['en' => 'They did the work carefully.', 'ua' => 'Вони виконали роботу старанно.'],
                                ],
                                'note' => '❌ <em>She speaks fluently English</em> — неправильно!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Обставини місця (Adverbs of Place)',
                        'sections' => [
                            [
                                'label' => 'В кінці речення',
                                'color' => 'emerald',
                                'description' => 'Обставини <strong>here, there, at home, in the park, at school</strong> стоять <strong>в кінці</strong> речення.',
                                'examples' => [
                                    ['en' => 'She lives here.', 'ua' => 'Вона живе тут.'],
                                    ['en' => 'He works at the office.', 'ua' => 'Він працює в офісі.'],
                                    ['en' => 'The children play in the park.', 'ua' => 'Діти граються в парку.'],
                                ],
                            ],
                            [
                                'label' => 'На початку для акценту',
                                'color' => 'sky',
                                'description' => 'У formal style або для акценту обставина місця може бути на початку.',
                                'examples' => [
                                    ['en' => 'Here is your book.', 'ua' => 'Ось твоя книга.'],
                                    ['en' => 'There goes the bus.', 'ua' => 'Ось їде автобус.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Обставини часу (Adverbs of Time)',
                        'sections' => [
                            [
                                'label' => 'В кінці речення',
                                'color' => 'emerald',
                                'description' => 'Обставини часу (<strong>yesterday, today, tomorrow, last week, every day</strong>) зазвичай стоять <strong>в кінці</strong>.',
                                'examples' => [
                                    ['en' => "I wake up at 6 o'clock.", 'ua' => 'Я прокидаюся о шостій годині.'],
                                    ['en' => 'She studies English every day.', 'ua' => 'Вона вчить англійську щодня.'],
                                    ['en' => 'We went to the cinema last night.', 'ua' => 'Ми ходили в кіно вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'На початку для акценту',
                                'color' => 'sky',
                                'description' => 'Для акценту обставини часу можна поставити <strong>на початку</strong>.',
                                'examples' => [
                                    ['en' => 'Yesterday, I met an old friend.', 'ua' => 'Вчора я зустрів старого друга.'],
                                    ['en' => 'Every morning, she jogs in the park.', 'ua' => 'Щоранку вона бігає в парку.'],
                                ],
                            ],
                            [
                                'label' => 'Кілька обставин часу',
                                'color' => 'amber',
                                'description' => 'Якщо є кілька обставин часу, більш специфічна йде першою.',
                                'examples' => [
                                    ['en' => 'He is leaving at 10 a.m. on Monday.', 'ua' => 'Він виїжджає о 10 годині ранку в понеділок.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Порядок обставин (Manner → Place → Time)',
                        'intro' => 'Якщо в кінці речення є кілька обставин, порядок такий:',
                        'rows' => [
                            [
                                'en' => 'Manner',
                                'ua' => 'Як?',
                                'note' => 'hard, quickly, well',
                            ],
                            [
                                'en' => 'Place',
                                'ua' => 'Де?',
                                'note' => 'in London, at home, here',
                            ],
                            [
                                'en' => 'Time',
                                'ua' => 'Коли?',
                                'note' => 'yesterday, last year, every day',
                            ],
                        ],
                        'warning' => '📌 Приклад: <strong>She worked hard in London last year.</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Прислівники ступеня (Adverbs of Degree)',
                        'sections' => [
                            [
                                'label' => 'Перед прикметником/прислівником',
                                'color' => 'emerald',
                                'description' => 'Прислівники <strong>very, quite, really, extremely, fairly</strong> стоять <strong>перед</strong> словом, яке модифікують.',
                                'examples' => [
                                    ['en' => 'She is very smart.', 'ua' => 'Вона дуже розумна.'],
                                    ['en' => 'He runs quite fast.', 'ua' => 'Він бігає досить швидко.'],
                                    ['en' => 'The movie was extremely boring.', 'ua' => 'Фільм був надзвичайно нудним.'],
                                ],
                            ],
                            [
                                'label' => 'A lot, much — в кінці',
                                'color' => 'sky',
                                'description' => 'Прислівники <strong>a lot, much</strong> зазвичай стоять в кінці речення.',
                                'examples' => [
                                    ['en' => 'We travel a lot.', 'ua' => 'Ми багато подорожуємо.'],
                                    ['en' => 'I like it very much.', 'ua' => 'Мені це дуже подобається.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Manner перед додатком замість після.',
                                'wrong' => 'She speaks fluently English.',
                                'right' => '✅ <span class="font-mono">She speaks English fluently.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Frequency на початку замість перед дієсловом.',
                                'wrong' => 'Always I drink coffee.',
                                'right' => '✅ <span class="font-mono">I always drink coffee.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Frequency після дієслова замість перед.',
                                'wrong' => 'He goes often to the gym.',
                                'right' => '✅ <span class="font-mono">He often goes to the gym.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Час у середині речення.',
                                'wrong' => 'I yesterday saw him.',
                                'right' => '✅ <span class="font-mono">I saw him yesterday.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Frequency</strong> (always, often, never): перед основним дієсловом, після to be.',
                            '<strong>Manner</strong> (quickly, well): в кінці, після додатка.',
                            '<strong>Place</strong> (here, at home): в кінці речення.',
                            '<strong>Time</strong> (yesterday, every day): в кінці або на початку для акценту.',
                            '<strong>Degree</strong> (very, quite): перед прикметником/прислівником.',
                            '<strong>Порядок в кінці:</strong> Manner → Place → Time.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери речення з правильним розташуванням прислівника.',
                        'selects' => [
                            ['label' => 'a) She always drinks coffee. / b) She drinks always coffee.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) He speaks fluently English. / b) He speaks English fluently.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) I yesterday saw him. / b) I saw him yesterday.', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Постав прислівник у правильне місце',
                        'input_intro' => 'Всунь прислівник у правильну позицію.',
                        'inputs' => [
                            ['before' => 'She is late. (always)', 'after' => '→'],
                            ['before' => 'He plays the piano. (well)', 'after' => '→'],
                            ['before' => 'They work. / in the office / every day', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у розташуванні прислівника.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Always he is happy.',
                                'example_target' => 'He is always happy.',
                            ],
                            [
                                'original' => '1. She speaks well French.',
                                'placeholder' => 'Виправ порядок слів',
                            ],
                            [
                                'original' => '2. Never I have been to Japan.',
                                'placeholder' => 'Виправ порядок слів',
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
                                'label' => 'Word Order with Adverbs — Прислівники та обставини (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
