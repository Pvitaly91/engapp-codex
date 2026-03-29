<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityArticlesTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'articles-a-an-the';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Articles A / An / The — Артиклі',
            'subtitle_html' => '<p><strong>Articles</strong> (артиклі) — це маленькі, але дуже важливі слова в англійській мові. <strong>A / An</strong> — неозначені артиклі для чогось загального або нового. <strong>The</strong> — означений артикль для чогось конкретного або вже відомого.</p>',
            'subtitle_text' => 'Теоретичний огляд артиклів англійської мови: a, an, the — правила вживання, винятки та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Articles',
                'A',
                'An',
                'The',
                'Indefinite Article',
                'Definite Article',
                'Zero Article',
                'Nouns',
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
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>три артиклі</strong> англійської мови: <strong>a</strong>, <strong>an</strong> та <strong>the</strong> — коли їх вживати і коли обходитись без них.',
                        'rules' => [
                            [
                                'label' => 'A / An',
                                'color' => 'emerald',
                                'text' => '<strong>Неозначений артикль</strong> — щось загальне або нове:',
                                'example' => 'I saw a dog. / She is an engineer.',
                            ],
                            [
                                'label' => 'The',
                                'color' => 'blue',
                                'text' => '<strong>Означений артикль</strong> — щось конкретне або відоме:',
                                'example' => 'The dog was big. / The sun is bright.',
                            ],
                            [
                                'label' => 'Zero (Ø)',
                                'color' => 'amber',
                                'text' => '<strong>Без артикля</strong> — загальні поняття, множина:',
                                'example' => 'I love music. / Dogs are loyal.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Три типи артиклів',
                        'intro' => 'В англійській мові є три варіанти вживання артиклів:',
                        'items' => [
                            ['label' => 'A / An', 'title' => 'Неозначений', 'subtitle' => 'Indefinite article — щось нове або загальне (a book, an apple)'],
                            ['label' => 'The', 'title' => 'Означений', 'subtitle' => 'Definite article — щось конкретне або відоме (the book I bought)'],
                            ['label' => 'Zero Ø', 'title' => 'Без артикля', 'subtitle' => 'Zero article — абстрактні поняття, загальна множина (I love music)'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. A vs An — коли яке?',
                        'sections' => [
                            [
                                'label' => 'A — перед приголосним ЗВУКОМ',
                                'color' => 'emerald',
                                'description' => 'Артикль <strong>a</strong> вживається перед словами, що <strong>починаються з приголосного звуку</strong>.',
                                'examples' => [
                                    ['en' => 'a book, a car, a dog', 'ua' => 'книга, машина, собака'],
                                    ['en' => 'a university [juːnɪˈvɜːsɪti]', 'ua' => 'університет (звук [j] — приголосний!)'],
                                    ['en' => 'a European country', 'ua' => 'європейська країна (звук [j])'],
                                    ['en' => 'a one-way street', 'ua' => 'вулиця з одностороннім рухом (звук [w])'],
                                ],
                            ],
                            [
                                'label' => 'An — перед голосним ЗВУКОМ',
                                'color' => 'sky',
                                'description' => 'Артикль <strong>an</strong> вживається перед словами, що <strong>починаються з голосного звуку</strong>.',
                                'examples' => [
                                    ['en' => 'an apple, an egg, an idea', 'ua' => 'яблуко, яйце, ідея'],
                                    ['en' => 'an hour [aʊə]', 'ua' => 'година (h — німа, звук [aʊ] — голосний!)'],
                                    ['en' => 'an honest person', 'ua' => 'чесна людина (h — німа)'],
                                    ['en' => 'an MBA degree', 'ua' => 'ступінь MBA (звук [e] — голосний)'],
                                ],
                            ],
                            [
                                'label' => 'Важливо!',
                                'color' => 'amber',
                                'description' => 'Вибір a/an залежить від <strong>ЗВУКУ</strong>, а не від літери!',
                                'examples' => [
                                    ['en' => 'a uniform (звук [j])', 'ua' => 'уніформа'],
                                    ['en' => 'an umbrella (звук [ʌ])', 'ua' => 'парасолька'],
                                ],
                                'note' => '📌 Дивись на вимову, а не на написання!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Коли вживати A / An (неозначений)',
                        'sections' => [
                            [
                                'label' => 'Щось нове / вперше',
                                'color' => 'emerald',
                                'description' => 'Коли ми <strong>вперше згадуємо</strong> щось або <strong>не уточнюємо</strong>, що саме.',
                                'examples' => [
                                    ['en' => 'I saw a cat in the garden.', 'ua' => 'Я бачив кота в саду. (якогось, невідомо якого)'],
                                    ['en' => 'She bought a new dress.', 'ua' => 'Вона купила нову сукню.'],
                                    ['en' => 'There is a problem.', 'ua' => 'Є проблема. (якась)'],
                                ],
                            ],
                            [
                                'label' => 'Професії та ролі',
                                'color' => 'sky',
                                'description' => 'Коли говоримо про <strong>професію, національність, релігію</strong>.',
                                'examples' => [
                                    ['en' => 'She is a doctor.', 'ua' => 'Вона лікар.'],
                                    ['en' => 'He is an engineer.', 'ua' => 'Він інженер.'],
                                    ['en' => "I'm a student.", 'ua' => 'Я студент.'],
                                ],
                            ],
                            [
                                'label' => 'Один з багатьох',
                                'color' => 'amber',
                                'description' => 'Коли маємо на увазі <strong>один представник групи</strong>.',
                                'examples' => [
                                    ['en' => 'A dog is a loyal animal.', 'ua' => 'Собака — вірна тварина. (будь-який собака)'],
                                    ['en' => 'I need a pen.', 'ua' => 'Мені потрібна ручка. (будь-яка)'],
                                ],
                                'note' => 'A/An = «один», «якийсь», «будь-який».',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Коли вживати THE (означений)',
                        'sections' => [
                            [
                                'label' => 'Вже відоме / згадане',
                                'color' => 'blue',
                                'description' => 'Коли співрозмовник <strong>знає</strong>, про що йдеться, або це вже <strong>згадувалось</strong>.',
                                'examples' => [
                                    ['en' => 'I saw a cat. The cat was black.', 'ua' => 'Я бачив кота. Кіт був чорний.'],
                                    ['en' => 'Where is the bathroom?', 'ua' => 'Де ванна? (в цьому будинку)'],
                                    ['en' => 'Did you read the book I gave you?', 'ua' => 'Ти прочитав книгу, яку я тобі дав?'],
                                ],
                            ],
                            [
                                'label' => 'Єдине у своєму роді',
                                'color' => 'sky',
                                'description' => 'Коли щось <strong>унікальне</strong> або <strong>єдине</strong>.',
                                'examples' => [
                                    ['en' => 'the sun, the moon, the Earth', 'ua' => 'сонце, місяць, Земля'],
                                    ['en' => 'the Internet, the radio', 'ua' => 'інтернет, радіо'],
                                    ['en' => 'the President of Ukraine', 'ua' => 'Президент України'],
                                ],
                            ],
                            [
                                'label' => 'З визначенням / уточненням',
                                'color' => 'purple',
                                'description' => 'Коли є <strong>уточнення</strong> (of, that, which, тощо).',
                                'examples' => [
                                    ['en' => 'the capital of France', 'ua' => 'столиця Франції'],
                                    ['en' => 'the man in the red jacket', 'ua' => 'чоловік у червоній куртці'],
                                    ['en' => 'the book that you recommended', 'ua' => 'книга, яку ти порекомендував'],
                                ],
                            ],
                            [
                                'label' => 'Географія та місця',
                                'color' => 'amber',
                                'description' => 'З деякими географічними назвами:',
                                'examples' => [
                                    ['en' => 'the USA, the UK, the Netherlands', 'ua' => 'США, Велика Британія, Нідерланди'],
                                    ['en' => 'the Pacific Ocean, the Dnipro', 'ua' => 'Тихий океан, Дніпро'],
                                    ['en' => 'the Alps, the Carpathians', 'ua' => 'Альпи, Карпати'],
                                ],
                                'note' => 'The = «той самий», «конкретний», «відомий».',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Коли НЕ вживати артикль (Zero Article)',
                        'sections' => [
                            [
                                'label' => 'Загальні поняття / абстракції',
                                'color' => 'amber',
                                'description' => 'Коли говоримо про щось <strong>у загальному</strong>, абстрактні поняття.',
                                'examples' => [
                                    ['en' => 'I love music.', 'ua' => 'Я люблю музику. (музику взагалі)'],
                                    ['en' => 'Life is beautiful.', 'ua' => 'Життя прекрасне.'],
                                    ['en' => 'Money is not everything.', 'ua' => 'Гроші — не все.'],
                                ],
                            ],
                            [
                                'label' => 'Множина в загальному',
                                'color' => 'sky',
                                'description' => 'Коли говоримо про <strong>множину загалом</strong>.',
                                'examples' => [
                                    ['en' => 'Dogs are loyal animals.', 'ua' => 'Собаки — вірні тварини. (усі собаки)'],
                                    ['en' => 'I like cats.', 'ua' => 'Я люблю котів. (котів взагалі)'],
                                    ['en' => 'Books are expensive.', 'ua' => 'Книги дорогі.'],
                                ],
                            ],
                            [
                                'label' => 'Країни, міста, імена',
                                'color' => 'emerald',
                                'description' => 'Більшість <strong>власних назв</strong> не потребують артикля.',
                                'examples' => [
                                    ['en' => 'Ukraine, Kyiv, London', 'ua' => 'Україна, Київ, Лондон'],
                                    ['en' => 'Mount Everest, Lake Baikal', 'ua' => 'Гора Еверест, озеро Байкал'],
                                    ['en' => 'John, Maria', 'ua' => 'Джон, Марія'],
                                ],
                            ],
                            [
                                'label' => 'Сталі вирази',
                                'color' => 'purple',
                                'description' => 'У багатьох <strong>сталих виразах</strong> артикль відсутній.',
                                'examples' => [
                                    ['en' => 'at home, at work, at school', 'ua' => 'вдома, на роботі, в школі'],
                                    ['en' => 'by bus, by car, by plane', 'ua' => 'автобусом, машиною, літаком'],
                                    ['en' => 'go to bed, have breakfast', 'ua' => 'лягти спати, снідати'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Порівняльна таблиця',
                        'intro' => 'Основні випадки вживання артиклів:',
                        'rows' => [
                            [
                                'en' => 'First mention',
                                'ua' => 'Перша згадка',
                                'note' => 'A/An: I saw a dog.',
                            ],
                            [
                                'en' => 'Known / mentioned',
                                'ua' => 'Відоме / згадане',
                                'note' => 'The: The dog was big.',
                            ],
                            [
                                'en' => 'Unique things',
                                'ua' => 'Унікальні речі',
                                'note' => 'The: the sun, the moon',
                            ],
                            [
                                'en' => 'Professions',
                                'ua' => 'Професії',
                                'note' => 'A/An: She is a teacher.',
                            ],
                            [
                                'en' => 'General / abstract',
                                'ua' => 'Загальне / абстрактне',
                                'note' => 'Ø: I love music.',
                            ],
                            [
                                'en' => 'Plural in general',
                                'ua' => 'Множина загалом',
                                'note' => 'Ø: Dogs are loyal.',
                            ],
                        ],
                        'warning' => '📌 <strong>A/An</strong> = новий, один з багатьох. <strong>The</strong> = конкретний, відомий. <strong>Ø</strong> = загальне поняття.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Особливі випадки',
                        'sections' => [
                            [
                                'label' => 'The + прикметник = група людей',
                                'color' => 'purple',
                                'description' => '<strong>The + adjective</strong> означає групу людей.',
                                'examples' => [
                                    ['en' => 'the rich, the poor', 'ua' => 'багаті, бідні (люди)'],
                                    ['en' => 'the young, the elderly', 'ua' => 'молодь, люди похилого віку'],
                                    ['en' => 'the unemployed', 'ua' => 'безробітні'],
                                ],
                            ],
                            [
                                'label' => 'Музичні інструменти',
                                'color' => 'sky',
                                'description' => 'З музичними інструментами вживаємо <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'play the piano', 'ua' => 'грати на піаніно'],
                                    ['en' => 'play the guitar', 'ua' => 'грати на гітарі'],
                                    ['en' => 'play the violin', 'ua' => 'грати на скрипці'],
                                ],
                            ],
                            [
                                'label' => 'Час доби',
                                'color' => 'amber',
                                'description' => 'З часом доби: <strong>in the morning/afternoon/evening</strong>, але <strong>at night</strong>.',
                                'examples' => [
                                    ['en' => 'in the morning', 'ua' => 'вранці'],
                                    ['en' => 'in the afternoon', 'ua' => 'вдень'],
                                    ['en' => 'at night (без the!)', 'ua' => 'вночі'],
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
                                'title' => 'Пропуск артикля перед професією.',
                                'wrong' => 'She is doctor.',
                                'right' => '✅ <span class="font-mono">She is a doctor.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'A замість An перед голосним звуком.',
                                'wrong' => 'a hour, a honest person',
                                'right' => '✅ <span class="font-mono">an hour, an honest person</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'The з абстрактними поняттями.',
                                'wrong' => 'The life is beautiful.',
                                'right' => '✅ <span class="font-mono">Life is beautiful.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Артикль перед країнами (більшість).',
                                'wrong' => 'I live in the Ukraine.',
                                'right' => '✅ <span class="font-mono">I live in Ukraine.</span>',
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
                            '<strong>A</strong> — перед приголосним ЗВУКОМ: <em>a book, a university</em>.',
                            '<strong>An</strong> — перед голосним ЗВУКОМ: <em>an apple, an hour</em>.',
                            '<strong>A/An</strong> — для чогось нового, невизначеного, професій.',
                            '<strong>The</strong> — для чогось відомого, конкретного, унікального.',
                            '<strong>Zero Ø</strong> — для загальних понять, абстракцій, множини.',
                            'Більшість країн, міст, імен — <strong>без артикля</strong>.',
                            'Винятки: <strong>the USA, the UK, the Netherlands</strong> (множина/союз).',
                            '<strong>The</strong> + прикметник = група людей: <em>the rich, the poor</em>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний артикль',
                        'select_intro' => 'Заповни пропуски артиклем a, an, the або Ø (без артикля).',
                        'selects' => [
                            ['label' => 'She is ___ engineer. (a / an / the / Ø)', 'prompt' => 'Який артикль?'],
                            ['label' => 'I saw ___ sun today. (a / an / the / Ø)', 'prompt' => 'Який артикль?'],
                            ['label' => '___ dogs are loyal animals. (a / an / the / Ø)', 'prompt' => 'Який артикль?'],
                            ['label' => 'I need ___ hour to finish. (a / an / the / Ø)', 'prompt' => 'Який артикль?'],
                        ],
                        'options' => ['a', 'an', 'the', 'Ø'],
                        'input_title' => 'Вправа 2. Вибери a або an',
                        'input_intro' => 'Напиши a або an перед словом.',
                        'inputs' => [
                            ['before' => '___ university', 'after' => '→'],
                            ['before' => '___ honest answer', 'after' => '→'],
                            ['before' => '___ European country', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку з артиклем.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'He is teacher.',
                                'example_target' => 'He is a teacher.',
                            ],
                            [
                                'original' => '1. I love the music.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. She lives in the France.',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Іменники, артиклі та кількість',
                        'items' => [
                            [
                                'label' => 'Articles A / An / The — Артиклі (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Countable vs Uncountable — Злічувані / незлічувані',
                                'current' => false,
                            ],
                            [
                                'label' => 'Some / Any — Кількість',
                                'current' => false,
                            ],
                            [
                                'label' => 'Much / Many / A lot of',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
