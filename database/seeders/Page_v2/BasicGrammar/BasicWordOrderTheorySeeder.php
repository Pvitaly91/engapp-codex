<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\BasicGrammar\BasicGrammarPageSeeder;

class BasicWordOrderTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-basic-word-order';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Basic Word Order — Порядок слів у ствердженні',
            'subtitle_html' => '<p><strong>Word order</strong> (порядок слів) — ключовий елемент англійської граматики. На відміну від української мови, де порядок слів відносно вільний, англійська вимагає чіткої послідовності.</p>',
            'subtitle_text' => 'Теоретичний огляд базового порядку слів у ствердних реченнях англійської мови з прикладами та практикою.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Sentence Structure',
                'Affirmative Sentences',
                'Adverbs of Frequency',
                'Time Adverbials',
                'Place Adverbials',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>базовий порядок слів</strong> в англійських ствердних реченнях: структуру S–V–O, розташування прислівників частотності та обставин часу й місця.',
                        'rules' => [
                            [
                                'label' => 'Базова структура',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + Verb + Object</strong> — основа англійського речення:',
                                'example' => 'She reads books.',
                            ],
                            [
                                'label' => 'Прислівники частотності',
                                'color' => 'blue',
                                'text' => '<strong>Always, often, sometimes, never</strong> стоять перед основним дієсловом:',
                                'example' => 'I always drink tea in the morning.',
                            ],
                            [
                                'label' => 'Час і місце',
                                'color' => 'amber',
                                'text' => '<strong>Place + Time</strong> зазвичай в кінці речення:',
                                'example' => 'We play football in the park every Sunday.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Структура S–V–O (Subject–Verb–Object)',
                        'intro' => 'Англійське ствердне речення будується за схемою:',
                        'items' => [
                            ['label' => 'S — Subject', 'title' => 'Підмет', 'subtitle' => 'Хто або що виконує дію (I, you, she, Tom, the dog)'],
                            ['label' => 'V — Verb', 'title' => 'Дієслово', 'subtitle' => 'Дія або стан (read, eat, is, have)'],
                            ['label' => 'O — Object', 'title' => 'Додаток', 'subtitle' => 'На кого/що спрямована дія (book, coffee, him)'],
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
                                'description' => 'Якщо в реченні дієслово <strong>to be</strong> (am, is, are, was, were), прислівник частотності ставиться <strong>після</strong> нього.',
                                'examples' => [
                                    ['en' => 'He is always late.', 'ua' => 'Він завжди запізнюється.'],
                                    ['en' => 'She is usually happy.', 'ua' => 'Вона зазвичай щаслива.'],
                                    ['en' => 'They are never bored.', 'ua' => 'Їм ніколи не нудно.'],
                                ],
                            ],
                            [
                                'label' => 'Sometimes / Usually на початку',
                                'color' => 'amber',
                                'description' => 'Деякі прислівники (<strong>sometimes, usually, often</strong>) можуть стояти на початку речення для акценту.',
                                'examples' => [
                                    ['en' => 'Sometimes I read before bed.', 'ua' => 'Іноді я читаю перед сном.'],
                                    ['en' => 'Usually we have dinner at 7.', 'ua' => 'Зазвичай ми вечеряємо о 7-й.'],
                                ],
                                'note' => '<strong>Always</strong> та <strong>never</strong> рідко ставлять на початку речення.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Обставини часу (Time Adverbials)',
                        'sections' => [
                            [
                                'label' => 'В кінці речення',
                                'color' => 'emerald',
                                'description' => "Обставини часу (<strong>yesterday, every day, at 7 o'clock, last week</strong>) зазвичай стоять <strong>в кінці</strong> речення.",
                                'examples' => [
                                    ['en' => "I wake up at 6 o'clock.", 'ua' => 'Я прокидаюся о шостій годині.'],
                                    ['en' => 'She studies English every day.', 'ua' => 'Вона вчить англійську щодня.'],
                                    ['en' => 'We went to the cinema last night.', 'ua' => 'Ми ходили в кіно вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'На початку речення',
                                'color' => 'sky',
                                'description' => 'Для акценту обставини часу можна поставити <strong>на початку</strong>.',
                                'examples' => [
                                    ['en' => 'Yesterday, I met an old friend.', 'ua' => 'Вчора я зустрів старого друга.'],
                                    ['en' => 'Every morning, she jogs in the park.', 'ua' => 'Щоранку вона бігає в парку.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Обставини місця (Place Adverbials)',
                        'sections' => [
                            [
                                'label' => 'Після дієслова / додатка',
                                'color' => 'emerald',
                                'description' => 'Обставини місця (<strong>at school, in the park, at home</strong>) ставляться <strong>після дієслова</strong> або <strong>додатка</strong>.',
                                'examples' => [
                                    ['en' => 'He works at the office.', 'ua' => 'Він працює в офісі.'],
                                    ['en' => 'She reads books in her room.', 'ua' => 'Вона читає книжки у своїй кімнаті.'],
                                ],
                            ],
                            [
                                'label' => 'Place перед Time',
                                'color' => 'sky',
                                'description' => 'Якщо є і місце, і час, порядок такий: <strong>Place + Time</strong>.',
                                'examples' => [
                                    ['en' => 'We eat dinner at home on Sundays.', 'ua' => 'Ми вечеряємо вдома по неділях.'],
                                    ['en' => 'The children play in the park every afternoon.', 'ua' => 'Діти граються в парку щодня після обіду.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Повна структура речення',
                        'intro' => 'Повний порядок елементів у ствердному реченні:',
                        'rows' => [
                            [
                                'en' => 'Subject',
                                'ua' => 'Підмет',
                                'note' => 'She, Tom, They',
                            ],
                            [
                                'en' => 'Adverb of Frequency',
                                'ua' => 'Прислівник частотності',
                                'note' => 'always, often, never (перед основним дієсловом)',
                            ],
                            [
                                'en' => 'Verb',
                                'ua' => 'Дієслово',
                                'note' => 'reads, eats, plays',
                            ],
                            [
                                'en' => 'Object',
                                'ua' => 'Додаток',
                                'note' => 'books, coffee, football',
                            ],
                            [
                                'en' => 'Place',
                                'ua' => 'Місце',
                                'note' => 'at school, in the park',
                            ],
                            [
                                'en' => 'Time',
                                'ua' => 'Час',
                                'note' => "every day, yesterday, at 7 o'clock",
                            ],
                        ],
                        'warning' => '📌 Формула: <strong>S + (Adv) + V + O + Place + Time</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Зміна порядку S–V–O під впливом української.',
                                'wrong' => 'Reads she books every day.',
                                'right' => '✅ <span class="font-mono">She reads books every day.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильне розташування прислівника частотності.',
                                'wrong' => 'She reads always books.',
                                'right' => '✅ <span class="font-mono">She always reads books.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Час посередині речення замість кінця.',
                                'wrong' => 'I go every day to school.',
                                'right' => '✅ <span class="font-mono">I go to school every day.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Базова структура: <strong>S + V + O</strong> (підмет + дієслово + додаток).',
                            'Прислівники частотності (<em>always, often, never</em>) — <strong>перед основним дієсловом</strong> або <strong>після to be</strong>.',
                            'Обставини часу (<em>yesterday, every day</em>) — зазвичай <strong>в кінці</strong> речення.',
                            'Обставини місця (<em>at home, in the park</em>) — після додатка, <strong>перед часом</strong>.',
                            'Формула: <strong>S + (Adv) + V + O + Place + Time</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери речення з правильним порядком слів.',
                        'selects' => [
                            ['label' => 'a) She always drinks coffee. / b) Always she drinks coffee.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) I go to work every day. / b) I every day go to work.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) He in the office works. / b) He works in the office.', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Розташуй слова у правильному порядку',
                        'input_intro' => 'Склади речення з поданих слів.',
                        'inputs' => [
                            ['before' => '(she / reads / books / often)', 'after' => '→'],
                            ['before' => '(every morning / I / breakfast / eat)', 'after' => '→'],
                            ['before' => '(at home / on Sundays / we / dinner / have)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у порядку слів.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Plays he football every day.',
                                'example_target' => 'He plays football every day.',
                            ],
                            [
                                'original' => '1. She reads always books in the evening.',
                                'placeholder' => 'Виправ порядок слів',
                            ],
                            [
                                'original' => '2. We go every week to the supermarket.',
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
                                'label' => 'Word Order — Порядок слів (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
