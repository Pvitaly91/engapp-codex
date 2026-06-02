<?php

namespace Database\Seeders\Page_v2\BasicGrammar\WordOrder;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class WordOrderVerbsObjectsTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-word-order-verbs-objects';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order with Verbs and Objects — Допоміжні, модальні, фразові дієслова',
            'subtitle_html' => '<p><strong>Word order</strong> (порядок слів) з допоміжними, модальними та фразовими дієсловами має свої правила. Особлива увага — на позицію додатка у phrasal verbs.</p>',
            'subtitle_text' => 'Теоретичний огляд порядку слів з допоміжними, модальними та фразовими дієсловами з прикладами та практикою.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'word-order',
                'title' => 'Word Order — Порядок слів',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Auxiliary Verbs',
                'Modal Verbs',
                'Phrasal Verbs',
                'Objects',
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
                        'intro' => 'У цій темі ти вивчиш <strong>порядок слів</strong> з допоміжними, модальними та фразовими дієсловами: структуру речень і правила розташування додатків.',
                        'rules' => [
                            [
                                'label' => 'Допоміжні',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + Auxiliary + Main Verb + Object</strong>:',
                                'example' => 'She is reading a book.',
                            ],
                            [
                                'label' => 'Модальні',
                                'color' => 'blue',
                                'text' => '<strong>Subject + Modal + Base Verb + Object</strong> (без to):',
                                'example' => 'He can speak English.',
                            ],
                            [
                                'label' => 'Phrasal Verbs',
                                'color' => 'amber',
                                'text' => 'Займенник <strong>обовʼязково</strong> між дієсловом і часткою:',
                                'example' => 'Turn it off. (NOT: Turn off it.)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Допоміжні дієслова (Auxiliary Verbs)',
                        'intro' => 'Допоміжні дієслова be, do, have стоять перед основним дієсловом:',
                        'items' => [
                            ['label' => 'Be', 'title' => 'Continuous / Passive', 'subtitle' => 'She is reading. / The book was written.'],
                            ['label' => 'Have', 'title' => 'Perfect tenses', 'subtitle' => 'They have finished. / He had left.'],
                            ['label' => 'Do', 'title' => 'Questions / Negatives', 'subtitle' => 'Do you know? / She doesn\'t like it.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Модальні дієслова (Modal Verbs)',
                        'sections' => [
                            [
                                'label' => 'Структура з модальними',
                                'color' => 'emerald',
                                'description' => 'Модальні дієслова <strong>can, should, must, might, will, would</strong> стоять перед основним дієсловом (без to).',
                                'examples' => [
                                    ['en' => 'She can speak English.', 'ua' => 'Вона вміє розмовляти англійською.'],
                                    ['en' => 'You should finish your homework.', 'ua' => 'Тобі слід закінчити домашнє завдання.'],
                                    ['en' => 'He must call the doctor.', 'ua' => 'Він повинен зателефонувати лікарю.'],
                                    ['en' => 'They might arrive late.', 'ua' => 'Вони можуть запізнитися.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення з модальними',
                                'color' => 'sky',
                                'description' => 'У запереченнях <strong>not</strong> ставиться після модального дієслова.',
                                'examples' => [
                                    ['en' => 'She cannot (can\'t) swim.', 'ua' => 'Вона не вміє плавати.'],
                                    ['en' => 'You should not (shouldn\'t) eat too much.', 'ua' => 'Тобі не слід їсти занадто багато.'],
                                    ['en' => 'He must not (mustn\'t) be late.', 'ua' => 'Він не повинен запізнюватися.'],
                                ],
                                'note' => 'Після модального дієслова завжди base form (без to, без -s).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Фразові дієслова — розділювані (Separable)',
                        'sections' => [
                            [
                                'label' => 'Додаток-іменник',
                                'color' => 'emerald',
                                'description' => 'Додаток-іменник може стояти <strong>між</strong> дієсловом і часткою або <strong>після</strong> всієї конструкції.',
                                'examples' => [
                                    ['en' => 'Turn off the light. = Turn the light off.', 'ua' => 'Вимкни світло.'],
                                    ['en' => 'Pick up the package. = Pick the package up.', 'ua' => 'Забери посилку.'],
                                    ['en' => 'Put on your coat. = Put your coat on.', 'ua' => 'Надягни пальто.'],
                                ],
                            ],
                            [
                                'label' => 'Додаток-займенник',
                                'color' => 'rose',
                                'description' => 'Займенник (it, him, her, them) <strong>ОБОВ\'ЯЗКОВО</strong> стоїть між дієсловом і часткою.',
                                'examples' => [
                                    ['en' => 'Turn it off. ❌ Turn off it.', 'ua' => 'Вимкни його.'],
                                    ['en' => 'Pick them up. ❌ Pick up them.', 'ua' => 'Забери їх.'],
                                    ['en' => 'Put it on. ❌ Put on it.', 'ua' => 'Надягни це.'],
                                ],
                                'note' => 'Це правило обов\'язкове! Займенник ніколи не стоїть після частки.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Фразові дієслова — нерозділювані (Inseparable)',
                        'sections' => [
                            [
                                'label' => 'Додаток після phrasal verb',
                                'color' => 'emerald',
                                'description' => 'У <strong>нерозділюваних</strong> фразових дієсловах додаток завжди стоїть <strong>після</strong> всієї конструкції.',
                                'examples' => [
                                    ['en' => 'Look after your dog.', 'ua' => 'Доглядай за своїм собакою.'],
                                    ['en' => 'She got over the illness.', 'ua' => 'Вона одужала від хвороби.'],
                                    ['en' => 'I came across an old photo.', 'ua' => 'Я натрапив на стару фотографію.'],
                                    ['en' => 'He ran into an old friend.', 'ua' => 'Він випадково зустрів старого друга.'],
                                ],
                            ],
                            [
                                'label' => 'Трислівні phrasal verbs',
                                'color' => 'sky',
                                'description' => 'Трислівні фразові дієслова <strong>завжди</strong> нерозділювані.',
                                'examples' => [
                                    ['en' => 'She can\'t put up with the noise.', 'ua' => 'Вона не може терпіти шум.'],
                                    ['en' => 'I\'m looking forward to the trip.', 'ua' => 'Я з нетерпінням чекаю на подорож.'],
                                    ['en' => 'We\'ve run out of milk.', 'ua' => 'У нас закінчилося молоко.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Популярні фразові дієслова',
                        'intro' => 'Запам\'ятай, які phrasal verbs розділювані, а які ні:',
                        'rows' => [
                            [
                                'en' => 'turn on/off',
                                'ua' => 'вмикати/вимикати',
                                'note' => 'Розділюване',
                            ],
                            [
                                'en' => 'pick up',
                                'ua' => 'підбирати, забирати',
                                'note' => 'Розділюване',
                            ],
                            [
                                'en' => 'put on',
                                'ua' => 'надягати',
                                'note' => 'Розділюване',
                            ],
                            [
                                'en' => 'throw away',
                                'ua' => 'викидати',
                                'note' => 'Розділюване',
                            ],
                            [
                                'en' => 'look after',
                                'ua' => 'доглядати',
                                'note' => 'Нерозділюване',
                            ],
                            [
                                'en' => 'look for',
                                'ua' => 'шукати',
                                'note' => 'Нерозділюване',
                            ],
                            [
                                'en' => 'get over',
                                'ua' => 'одужати, подолати',
                                'note' => 'Нерозділюване',
                            ],
                        ],
                        'warning' => '📌 Якщо не впевнений — перевір у словнику!',
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
                                'title' => 'Займенник після частки замість між.',
                                'wrong' => 'Turn off it.',
                                'right' => '✅ <span class="font-mono">Turn it off.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Модальне дієслово + to.',
                                'wrong' => 'She can to swim.',
                                'right' => '✅ <span class="font-mono">She can swim.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Розділення нерозділюваного phrasal verb.',
                                'wrong' => 'Look your dog after.',
                                'right' => '✅ <span class="font-mono">Look after your dog.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Дієслово з -s після модального.',
                                'wrong' => 'He must calls.',
                                'right' => '✅ <span class="font-mono">He must call.</span>',
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
                            'Допоміжні (be, do, have): <strong>S + Aux + V + O</strong>.',
                            'Модальні: <strong>S + Modal + Base Verb + O</strong> (без to, без -s).',
                            'Розділювані phrasal verbs: додаток може бути між або після.',
                            'Займенник у розділюваних: <strong>обов\'язково</strong> між дієсловом і часткою.',
                            'Нерозділювані та 3-слівні phrasal verbs: додаток <strong>після</strong> всієї конструкції.',
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
                            ['label' => 'a) Turn it off. / b) Turn off it.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) She can to swim. / b) She can swim.', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Look after the kids. / b) Look the kids after.', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Встав займенник у правильне місце',
                        'input_intro' => 'Заміни іменник на займенник у дужках.',
                        'inputs' => [
                            ['before' => 'Pick up the book. (it)', 'after' => '→'],
                            ['before' => 'Turn off the TV. (it)', 'after' => '→'],
                            ['before' => 'Put on your shoes. (them)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у порядку слів.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'She must calls him.',
                                'example_target' => 'She must call him.',
                            ],
                            [
                                'original' => '1. He can to play guitar.',
                                'placeholder' => 'Виправ порядок слів',
                            ],
                            [
                                'original' => '2. Throw away them.',
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
                                'label' => 'Word Order with Adverbs — Прислівники та обставини',
                                'current' => false,
                            ],
                            [
                                'label' => 'Word Order with Verbs and Objects — Дієслова та додатки (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Advanced Word Order — Інверсія та підсилення',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
