<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'pronouns';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Pronouns — Займенники',
            'subtitle_html' => '<p><strong>Pronouns</strong> (займенники) — це слова, що замінюють іменники, щоб уникнути повторів. Основні типи: <strong>особові (I, you, he, she)</strong>, <strong>присвійні (my, mine)</strong>, <strong>зворотні (myself, yourself)</strong>, <strong>вказівні (this, that)</strong> та інші.</p>',
            'subtitle_text' => 'Теоретичний огляд займенників англійської мови: особові, присвійні, зворотні, вказівні, неозначені та відносні займенники.',
            'locale' => 'uk',
            'category' => [
                'slug' => '3',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Pronouns',
                'Personal Pronouns',
                'Possessive Pronouns',
                'Reflexive Pronouns',
                'Demonstrative Pronouns',
                'I',
                'You',
                'He',
                'She',
                'It',
                'We',
                'They',
                'My',
                'Mine',
                'Myself',
                'This',
                'That',
                'Grammar',
                'Theory',
                'A1',
                'A2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цій темі ти вивчиш <strong>всі основні типи займенників</strong> англійської мови та правила їх вживання.',
                        'rules' => [
                            [
                                'label' => 'Personal',
                                'color' => 'emerald',
                                'text' => '<strong>Особові займенники</strong> — хто діє:',
                                'example' => 'I work. She reads. They play.',
                            ],
                            [
                                'label' => 'Possessive',
                                'color' => 'blue',
                                'text' => '<strong>Присвійні форми</strong> — чиє це:',
                                'example' => 'my book, mine, his, hers',
                            ],
                            [
                                'label' => 'Reflexive',
                                'color' => 'amber',
                                'text' => '<strong>Зворотні займенники</strong> — дія на себе:',
                                'example' => 'I did it myself. She taught herself.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Типи займенників',
                        'intro' => 'В англійській мові є кілька основних типів займенників:',
                        'items' => [
                            ['label' => 'Personal', 'title' => 'Особові', 'subtitle' => 'I, you, he, she, it, we, they — хто діє або отримує дію'],
                            ['label' => 'Possessive', 'title' => 'Присвійні', 'subtitle' => 'my/mine, your/yours, his, her/hers — чиє це'],
                            ['label' => 'Reflexive', 'title' => 'Зворотні', 'subtitle' => 'myself, yourself, himself, herself — дія на себе'],
                            ['label' => 'Demonstrative', 'title' => 'Вказівні', 'subtitle' => 'this, that, these, those — вказують на предмети'],
                            ['label' => 'Indefinite', 'title' => 'Неозначені', 'subtitle' => 'someone, anybody, nothing — невизначені особи/речі'],
                            ['label' => 'Relative', 'title' => 'Відносні', 'subtitle' => "who, which, that — з'єднують частини речення"],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Особові займенники (Personal Pronouns)',
                        'sections' => [
                            [
                                'label' => 'Subject Pronouns — Підметові',
                                'color' => 'emerald',
                                'description' => 'Використовуються як <strong>підмет</strong> речення (хто виконує дію).',
                                'examples' => [
                                    ['en' => 'I work every day.', 'ua' => 'Я працюю щодня.'],
                                    ['en' => 'You are smart.', 'ua' => 'Ти розумний.'],
                                    ['en' => 'He reads books.', 'ua' => 'Він читає книги.'],
                                    ['en' => 'She likes music.', 'ua' => 'Вона любить музику.'],
                                    ['en' => 'It is a cat.', 'ua' => 'Це кіт.'],
                                    ['en' => 'We are friends.', 'ua' => 'Ми друзі.'],
                                    ['en' => 'They play football.', 'ua' => 'Вони грають у футбол.'],
                                ],
                            ],
                            [
                                'label' => "Object Pronouns — Об'єктні",
                                'color' => 'sky',
                                'description' => "Використовуються як <strong>додаток</strong> (хто/що отримує дію).",
                                'examples' => [
                                    ['en' => 'Call me later.', 'ua' => 'Подзвони мені пізніше.'],
                                    ['en' => 'I see you.', 'ua' => 'Я бачу тебе.'],
                                    ['en' => 'She loves him.', 'ua' => 'Вона любить його.'],
                                    ['en' => 'I know her.', 'ua' => 'Я знаю її.'],
                                    ['en' => 'Give it to me.', 'ua' => 'Дай це мені.'],
                                    ['en' => 'They invited us.', 'ua' => 'Вони запросили нас.'],
                                    ['en' => 'I like them.', 'ua' => 'Мені вони подобаються.'],
                                ],
                            ],
                            [
                                'label' => 'Таблиця особових займенників',
                                'color' => 'purple',
                                'description' => "Повна таблиця підметових та об'єктних займенників:",
                                'examples' => [
                                    ['en' => 'I → me', 'ua' => 'я → мене'],
                                    ['en' => 'you → you', 'ua' => 'ти/ви → тебе/вас'],
                                    ['en' => 'he → him', 'ua' => 'він → його'],
                                    ['en' => 'she → her', 'ua' => 'вона → її'],
                                    ['en' => 'it → it', 'ua' => 'воно → його/її'],
                                    ['en' => 'we → us', 'ua' => 'ми → нас'],
                                    ['en' => 'they → them', 'ua' => 'вони → їх'],
                                ],
                                'note' => '📌 Subject = підмет (хто діє), Object = додаток (кого/що).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Присвійні форми (Possessive Forms)',
                        'sections' => [
                            [
                                'label' => 'Possessive Adjectives — Присвійні прикметники',
                                'color' => 'blue',
                                'description' => 'Стоять <strong>перед іменником</strong> і показують приналежність.',
                                'examples' => [
                                    ['en' => 'This is my book.', 'ua' => 'Це моя книга.'],
                                    ['en' => 'Your car is new.', 'ua' => 'Твоя машина нова.'],
                                    ['en' => 'His name is John.', 'ua' => "Його ім'я Джон."],
                                    ['en' => 'Her sister is a doctor.', 'ua' => 'Її сестра — лікар.'],
                                    ['en' => 'Its color is blue.', 'ua' => 'Його колір синій.'],
                                    ['en' => 'Our house is big.', 'ua' => 'Наш будинок великий.'],
                                    ['en' => 'Their dog is cute.', 'ua' => 'Їхній собака милий.'],
                                ],
                                'note' => 'Завжди перед іменником!',
                            ],
                            [
                                'label' => 'Possessive Pronouns — Присвійні займенники',
                                'color' => 'sky',
                                'description' => '<strong>Заміняють</strong> іменник (стоять замість нього).',
                                'examples' => [
                                    ['en' => 'This book is mine.', 'ua' => 'Ця книга моя.'],
                                    ['en' => 'The car is yours.', 'ua' => 'Машина твоя.'],
                                    ['en' => 'That pen is his.', 'ua' => 'Та ручка його.'],
                                    ['en' => 'The bag is hers.', 'ua' => 'Сумка її.'],
                                    ['en' => 'The house is ours.', 'ua' => 'Будинок наш.'],
                                    ['en' => 'Those keys are theirs.', 'ua' => 'Ті ключі їхні.'],
                                ],
                                'note' => 'Стоять самостійно, без іменника!',
                            ],
                            [
                                'label' => 'Важливо!',
                                'color' => 'amber',
                                'description' => "Не плутай <strong>its</strong> (присвійне) та <strong>it's</strong> (скорочення).",
                                'examples' => [
                                    ['en' => "Its color is red. (присвійне)", 'ua' => 'Його колір червоний.'],
                                    ['en' => "It's a cat. (it is)", 'ua' => 'Це кіт.'],
                                ],
                                'note' => "📌 Its = присвійне, It's = скорочення від It is.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Зворотні займенники (Reflexive Pronouns)',
                        'sections' => [
                            [
                                'label' => 'Форми зворотних займенників',
                                'color' => 'purple',
                                'description' => 'Закінчуються на <strong>-self</strong> (однина) або <strong>-selves</strong> (множина).',
                                'examples' => [
                                    ['en' => 'I → myself', 'ua' => 'я → себе (сам/сама)'],
                                    ['en' => 'you → yourself / yourselves', 'ua' => 'ти/ви → себе'],
                                    ['en' => 'he → himself', 'ua' => 'він → себе (сам)'],
                                    ['en' => 'she → herself', 'ua' => 'вона → себе (сама)'],
                                    ['en' => 'it → itself', 'ua' => 'воно → себе (саме)'],
                                    ['en' => 'we → ourselves', 'ua' => 'ми → себе (самі)'],
                                    ['en' => 'they → themselves', 'ua' => 'вони → себе (самі)'],
                                ],
                            ],
                            [
                                'label' => 'Коли вживати',
                                'color' => 'emerald',
                                'description' => "Коли <strong>підмет і об'єкт</strong> — одна й та сама особа.",
                                'examples' => [
                                    ['en' => 'I taught myself English.', 'ua' => 'Я навчив себе (сам навчився) англійської.'],
                                    ['en' => 'She cut herself.', 'ua' => 'Вона порізалася (порізала себе).'],
                                    ['en' => 'We enjoyed ourselves.', 'ua' => 'Ми добре провели час (розважили себе).'],
                                    ['en' => 'They did it themselves.', 'ua' => 'Вони зробили це самі.'],
                                ],
                            ],
                            [
                                'label' => 'Emphatic use — Підсилення',
                                'color' => 'amber',
                                'description' => 'Для <strong>підсилення</strong>: акцент на тому, хто саме виконав дію.',
                                'examples' => [
                                    ['en' => 'I did it myself.', 'ua' => 'Я зробив це сам (сам, без допомоги).'],
                                    ['en' => 'The boss himself came.', 'ua' => 'Сам бос прийшов.'],
                                    ['en' => 'She fixed the car herself.', 'ua' => 'Вона сама полагодила машину.'],
                                ],
                                'note' => '📌 By myself/yourself = самостійно, без допомоги.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Вказівні займенники (Demonstrative Pronouns)',
                        'sections' => [
                            [
                                'label' => 'This / That — Однина',
                                'color' => 'blue',
                                'description' => '<strong>This</strong> — близько, <strong>That</strong> — далеко.',
                                'examples' => [
                                    ['en' => 'This is my phone.', 'ua' => 'Це мій телефон. (близько)'],
                                    ['en' => 'That is your car.', 'ua' => 'Те — твоя машина. (далеко)'],
                                    ['en' => 'I like this.', 'ua' => 'Мені це подобається.'],
                                    ['en' => 'What is that?', 'ua' => 'Що то таке?'],
                                ],
                            ],
                            [
                                'label' => 'These / Those — Множина',
                                'color' => 'sky',
                                'description' => '<strong>These</strong> — близько, <strong>Those</strong> — далеко.',
                                'examples' => [
                                    ['en' => 'These are my books.', 'ua' => 'Це мої книги. (близько)'],
                                    ['en' => 'Those are your keys.', 'ua' => 'Ті — твої ключі. (далеко)'],
                                    ['en' => 'I want these.', 'ua' => 'Я хочу ці.'],
                                    ['en' => 'Those are expensive.', 'ua' => 'Ті дорогі.'],
                                ],
                                'note' => '📌 This/That = однина, These/Those = множина.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Інші типи займенників (огляд)',
                        'sections' => [
                            [
                                'label' => 'Indefinite Pronouns — Неозначені',
                                'color' => 'purple',
                                'description' => 'Вказують на невизначених осіб або речі.',
                                'examples' => [
                                    ['en' => 'Someone is calling.', 'ua' => 'Хтось телефонує.'],
                                    ['en' => 'Anybody can do it.', 'ua' => 'Будь-хто може це зробити.'],
                                    ['en' => 'Nothing is impossible.', 'ua' => 'Ніщо не є неможливим.'],
                                    ['en' => 'Everybody knows that.', 'ua' => 'Всі це знають.'],
                                ],
                            ],
                            [
                                'label' => 'Relative Pronouns — Відносні',
                                'color' => 'emerald',
                                'description' => "З'єднують частини складного речення.",
                                'examples' => [
                                    ['en' => 'The man who called is my boss.', 'ua' => 'Чоловік, який телефонував — мій бос.'],
                                    ['en' => 'The book that I read was great.', 'ua' => 'Книга, яку я прочитав, була чудова.'],
                                    ['en' => 'The car which is red is mine.', 'ua' => 'Машина, яка червона — моя.'],
                                ],
                            ],
                            [
                                'label' => 'Reciprocal Pronouns — Взаємні',
                                'color' => 'amber',
                                'description' => 'Вказують на взаємну дію.',
                                'examples' => [
                                    ['en' => 'They love each other.', 'ua' => 'Вони люблять один одного.'],
                                    ['en' => 'We help one another.', 'ua' => 'Ми допомагаємо один одному.'],
                                ],
                                'note' => '📌 Each other = для двох, One another = для багатьох.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Порівняльна таблиця основних займенників',
                        'intro' => 'Основні форми займенників:',
                        'rows' => [
                            [
                                'en' => 'Subject',
                                'ua' => 'Підмет',
                                'note' => 'I, you, he, she, it, we, they',
                            ],
                            [
                                'en' => 'Object',
                                'ua' => 'Додаток',
                                'note' => 'me, you, him, her, it, us, them',
                            ],
                            [
                                'en' => 'Possessive Adj.',
                                'ua' => 'Присвійний прикм.',
                                'note' => 'my, your, his, her, its, our, their',
                            ],
                            [
                                'en' => 'Possessive Pron.',
                                'ua' => 'Присвійний займ.',
                                'note' => 'mine, yours, his, hers, ours, theirs',
                            ],
                            [
                                'en' => 'Reflexive',
                                'ua' => 'Зворотний',
                                'note' => 'myself, yourself, himself, herself, itself, ourselves, yourselves, themselves',
                            ],
                        ],
                        'warning' => '📌 Subject = підмет, Object = додаток, Possessive = присвійний, Reflexive = зворотний.',
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
                                'title' => "Плутанина підметових та об'єктних займенників.",
                                'wrong' => 'Me and John went to the park.',
                                'right' => '✅ <span class="font-mono">John and I went to the park.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Плутанина its та it's.",
                                'wrong' => "Its a cat. / The dog wagged it's tail.",
                                'right' => "✅ <span class=\"font-mono\">It's a cat. / The dog wagged its tail.</span>",
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Пропуск зворотного займенника.',
                                'wrong' => 'I taught English.',
                                'right' => '✅ <span class="font-mono">I taught myself English.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Використання присвійного прикметника без іменника.',
                                'wrong' => 'This book is my.',
                                'right' => '✅ <span class="font-mono">This book is mine.</span>',
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
                            '<strong>Subject pronouns</strong> — підмет: <em>I, you, he, she, it, we, they</em>.',
                            '<strong>Object pronouns</strong> — додаток: <em>me, you, him, her, it, us, them</em>.',
                            '<strong>Possessive adjectives</strong> — перед іменником: <em>my, your, his, her, its, our, their</em>.',
                            '<strong>Possessive pronouns</strong> — замість іменника: <em>mine, yours, his, hers, ours, theirs</em>.',
                            '<strong>Reflexive pronouns</strong> — зворотні: <em>myself, yourself, himself, herself, itself, ourselves, yourselves, themselves</em>.',
                            '<strong>Demonstrative pronouns</strong> — вказівні: <em>this, that, these, those</em>.',
                            '<strong>Its</strong> = присвійне (його/її), <strong>It\'s</strong> = скорочення (it is).',
                            'Зворотні займенники: <strong>-self</strong> (однина), <strong>-selves</strong> (множина).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний займенник',
                        'select_intro' => 'Заповни пропуски правильним займенником.',
                        'selects' => [
                            ['label' => '___ am a student. (I / Me)', 'prompt' => 'Який займенник?'],
                            ['label' => 'This book is ___. (my / mine)', 'prompt' => 'Який займенник?'],
                            ['label' => 'She did it ___. (herself / her)', 'prompt' => 'Який займенник?'],
                            ['label' => '___ is my phone. (This / These)', 'prompt' => 'Який займенник?'],
                        ],
                        'options' => ['I', 'me', 'my', 'mine', 'myself', 'this', 'these', 'her', 'herself'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний займенник.',
                        'inputs' => [
                            ['before' => '___ like music. (я)', 'after' => '→'],
                            ['before' => 'Give it to ___. (мені)', 'after' => '→'],
                            ['before' => 'This is ___ car. (мій)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку із займенниками.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Me and John are friends.',
                                'example_target' => 'John and I are friends.',
                            ],
                            [
                                'original' => "1. Its a beautiful day.",
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. This book is my.',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Займенники та вказівні слова',
                        'items' => [
                            [
                                'label' => 'Pronouns — Займенники (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => "Personal & Object Pronouns — Особові та об'єктні",
                                'current' => false,
                            ],
                            [
                                'label' => 'Possessive Forms — Присвійні форми',
                                'current' => false,
                            ],
                            [
                                'label' => 'Reflexive Pronouns — Зворотні займенники',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
