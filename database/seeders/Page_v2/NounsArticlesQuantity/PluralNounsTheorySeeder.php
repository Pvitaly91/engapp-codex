<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Pages\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class PluralNounsTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'plural-nouns-s-es-ies';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Plural Nouns — Множина іменників (s, es, ies)',
            'subtitle_html' => '<p><strong>Plural nouns</strong> (множина іменників) — це форма іменників, що позначає більше одного предмета. Основні правила: додавання <strong>-s</strong>, <strong>-es</strong> або заміна <strong>-y</strong> на <strong>-ies</strong>. Є також винятки та неправильні форми множини.</p>',
            'subtitle_text' => 'Теоретичний огляд утворення множини іменників в англійській мові: правила додавання -s, -es, -ies та неправильні форми.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Plural Nouns',
                'Nouns',
                'Singular',
                'Plural',
                '-s',
                '-es',
                '-ies',
                'Irregular Plurals',
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
                        'intro' => 'У цій темі ти вивчиш, як утворюється <strong>множина іменників</strong> в англійській мові — основні правила та винятки.',
                        'rules' => [
                            [
                                'label' => '+ S',
                                'color' => 'emerald',
                                'text' => '<strong>Додавання -s</strong> — основне правило:',
                                'example' => 'cat → cats, book → books, dog → dogs',
                            ],
                            [
                                'label' => '+ ES',
                                'color' => 'blue',
                                'text' => '<strong>Додавання -es</strong> — після s, x, z, ch, sh:',
                                'example' => 'box → boxes, bus → buses, watch → watches',
                            ],
                            [
                                'label' => 'Y → IES',
                                'color' => 'amber',
                                'text' => '<strong>Заміна y на ies</strong> — після приголосної:',
                                'example' => 'baby → babies, city → cities, party → parties',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні способи утворення множини',
                        'intro' => 'Є три основні правила утворення множини англійських іменників:',
                        'items' => [
                            ['label' => '+ S', 'title' => 'Додати -s', 'subtitle' => 'Більшість іменників — cat → cats, book → books'],
                            ['label' => '+ ES', 'title' => 'Додати -es', 'subtitle' => 'Після s, x, z, ch, sh — box → boxes, bus → buses'],
                            ['label' => 'Y → IES', 'title' => 'Y змінити на IES', 'subtitle' => 'Після приголосної — baby → babies, city → cities'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Правило 1: Додавання -s',
                        'sections' => [
                            [
                                'label' => 'Основне правило',
                                'color' => 'emerald',
                                'description' => 'Більшість іменників утворюють множину простим <strong>додаванням -s</strong>.',
                                'examples' => [
                                    ['en' => 'cat → cats', 'ua' => 'кіт → коти'],
                                    ['en' => 'dog → dogs', 'ua' => 'собака → собаки'],
                                    ['en' => 'book → books', 'ua' => 'книга → книги'],
                                    ['en' => 'table → tables', 'ua' => 'стіл → столи'],
                                ],
                            ],
                            [
                                'label' => 'Після голосних',
                                'color' => 'sky',
                                'description' => 'Після голосних літер (a, e, i, o, u) просто додаємо <strong>-s</strong>.',
                                'examples' => [
                                    ['en' => 'bee → bees', 'ua' => 'бджола → бджоли'],
                                    ['en' => 'tree → trees', 'ua' => 'дерево → дерева'],
                                    ['en' => 'shoe → shoes', 'ua' => 'черевик → черевики'],
                                    ['en' => 'zoo → zoos', 'ua' => 'зоопарк → зоопарки'],
                                ],
                            ],
                            [
                                'label' => 'Вимова',
                                'color' => 'amber',
                                'description' => 'Закінчення <strong>-s</strong> вимовляється як [s], [z] або [ɪz] залежно від попереднього звуку.',
                                'examples' => [
                                    ['en' => 'books [bʊks] — звук [s]', 'ua' => 'після глухих приголосних'],
                                    ['en' => 'dogs [dɒɡz] — звук [z]', 'ua' => 'після дзвінких приголосних та голосних'],
                                    ['en' => 'buses [ˈbʌsɪz] — звук [ɪz]', 'ua' => 'після s, z, sh, ch, x'],
                                ],
                                'note' => '📌 Це автоматично — не треба спеціально вчити!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Правило 2: Додавання -es',
                        'sections' => [
                            [
                                'label' => 'Після s, ss, x, z',
                                'color' => 'blue',
                                'description' => 'Якщо іменник закінчується на <strong>s, ss, x, z</strong>, додаємо <strong>-es</strong>.',
                                'examples' => [
                                    ['en' => 'bus → buses', 'ua' => 'автобус → автобуси'],
                                    ['en' => 'glass → glasses', 'ua' => 'склянка → склянки'],
                                    ['en' => 'box → boxes', 'ua' => 'коробка → коробки'],
                                    ['en' => 'quiz → quizzes', 'ua' => 'тест → тести'],
                                ],
                            ],
                            [
                                'label' => 'Після ch, sh',
                                'color' => 'sky',
                                'description' => 'Якщо іменник закінчується на <strong>ch або sh</strong>, також додаємо <strong>-es</strong>.',
                                'examples' => [
                                    ['en' => 'watch → watches', 'ua' => 'годинник → годинники'],
                                    ['en' => 'church → churches', 'ua' => 'церква → церкви'],
                                    ['en' => 'dish → dishes', 'ua' => 'тарілка → тарілки'],
                                    ['en' => 'brush → brushes', 'ua' => 'щітка → щітки'],
                                ],
                            ],
                            [
                                'label' => 'Чому -es?',
                                'color' => 'purple',
                                'description' => 'Додавання <strong>-es</strong> робить вимову легшою після шиплячих та свистячих звуків.',
                                'examples' => [
                                    ['en' => 'box + s = boxs ✗ (важко вимовити)', 'ua' => 'неправильно'],
                                    ['en' => 'box + es = boxes ✓ [ˈbɒksɪz]', 'ua' => 'правильно'],
                                ],
                                'note' => '📌 Це додає склад для зручності вимови!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Правило 3: Y → IES',
                        'sections' => [
                            [
                                'label' => 'Приголосна + Y',
                                'color' => 'amber',
                                'description' => 'Якщо перед <strong>y</strong> стоїть <strong>приголосна</strong>, змінюємо <strong>y на ies</strong>.',
                                'examples' => [
                                    ['en' => 'baby → babies', 'ua' => 'немовля → немовлята'],
                                    ['en' => 'city → cities', 'ua' => 'місто → міста'],
                                    ['en' => 'party → parties', 'ua' => 'вечірка → вечірки'],
                                    ['en' => 'country → countries', 'ua' => 'країна → країни'],
                                ],
                            ],
                            [
                                'label' => 'Голосна + Y',
                                'color' => 'emerald',
                                'description' => 'Якщо перед <strong>y</strong> стоїть <strong>голосна</strong> (a, e, i, o, u), просто додаємо <strong>-s</strong>.',
                                'examples' => [
                                    ['en' => 'boy → boys', 'ua' => 'хлопчик → хлопчики'],
                                    ['en' => 'day → days', 'ua' => 'день → дні'],
                                    ['en' => 'key → keys', 'ua' => 'ключ → ключі'],
                                    ['en' => 'toy → toys', 'ua' => 'іграшка → іграшки'],
                                ],
                            ],
                            [
                                'label' => 'Як запамʼятати?',
                                'color' => 'sky',
                                'description' => 'Простий спосіб запамʼятати правило:',
                                'examples' => [
                                    ['en' => 'Приголосна + y → ies', 'ua' => 'baby → babies'],
                                    ['en' => 'Голосна + y → ys', 'ua' => 'boy → boys'],
                                ],
                                'note' => '📌 Дивись на літеру ПЕРЕД y!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Іменники на -o',
                        'sections' => [
                            [
                                'label' => 'Приголосна + O → OES',
                                'color' => 'purple',
                                'description' => 'Деякі іменники на <strong>-o</strong> (після приголосної) додають <strong>-es</strong>.',
                                'examples' => [
                                    ['en' => 'tomato → tomatoes', 'ua' => 'помідор → помідори'],
                                    ['en' => 'potato → potatoes', 'ua' => 'картопля → картоплі'],
                                    ['en' => 'hero → heroes', 'ua' => 'герой → герої'],
                                    ['en' => 'echo → echoes', 'ua' => 'відлуння → відлуння'],
                                ],
                            ],
                            [
                                'label' => 'Голосна + O → OS',
                                'color' => 'emerald',
                                'description' => 'Якщо перед <strong>-o</strong> голосна, просто додаємо <strong>-s</strong>.',
                                'examples' => [
                                    ['en' => 'radio → radios', 'ua' => 'радіо → радіо'],
                                    ['en' => 'video → videos', 'ua' => 'відео → відео'],
                                    ['en' => 'zoo → zoos', 'ua' => 'зоопарк → зоопарки'],
                                    ['en' => 'studio → studios', 'ua' => 'студія → студії'],
                                ],
                            ],
                            [
                                'label' => 'Музика і скорочення → OS',
                                'color' => 'sky',
                                'description' => 'Музичні терміни та скорочення завжди з <strong>-s</strong>.',
                                'examples' => [
                                    ['en' => 'piano → pianos', 'ua' => 'піаніно → піаніно'],
                                    ['en' => 'photo → photos', 'ua' => 'фото → фото'],
                                    ['en' => 'kilo → kilos', 'ua' => 'кілограм → кілограми'],
                                ],
                                'note' => '⚠️ Винятки треба запамʼятати!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Іменники на -f/-fe → VES',
                        'sections' => [
                            [
                                'label' => 'F/FE → VES',
                                'color' => 'rose',
                                'description' => 'Багато іменників на <strong>-f</strong> або <strong>-fe</strong> змінюють закінчення на <strong>-ves</strong>.',
                                'examples' => [
                                    ['en' => 'knife → knives', 'ua' => 'ніж → ножі'],
                                    ['en' => 'wife → wives', 'ua' => 'дружина → дружини'],
                                    ['en' => 'leaf → leaves', 'ua' => 'листок → листки'],
                                    ['en' => 'wolf → wolves', 'ua' => 'вовк → вовки'],
                                ],
                            ],
                            [
                                'label' => 'Ще приклади',
                                'color' => 'amber',
                                'description' => 'Інші поширені іменники з цією зміною:',
                                'examples' => [
                                    ['en' => 'half → halves', 'ua' => 'половина → половини'],
                                    ['en' => 'shelf → shelves', 'ua' => 'полиця → полиці'],
                                    ['en' => 'thief → thieves', 'ua' => 'злодій → злодії'],
                                    ['en' => 'life → lives', 'ua' => 'життя → життя (мн.)'],
                                ],
                            ],
                            [
                                'label' => 'Винятки: тільки + S',
                                'color' => 'sky',
                                'description' => 'Але деякі іменники на -f просто додають <strong>-s</strong>:',
                                'examples' => [
                                    ['en' => 'roof → roofs', 'ua' => 'дах → дахи'],
                                    ['en' => 'chief → chiefs', 'ua' => 'керівник → керівники'],
                                    ['en' => 'belief → beliefs', 'ua' => 'переконання → переконання'],
                                ],
                                'note' => '📌 Ці винятки треба запамʼятати!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Неправильні форми множини (Irregular)',
                        'sections' => [
                            [
                                'label' => 'Зміна голосної',
                                'color' => 'purple',
                                'description' => 'Деякі іменники змінюють <strong>внутрішню голосну</strong>.',
                                'examples' => [
                                    ['en' => 'man → men', 'ua' => 'чоловік → чоловіки'],
                                    ['en' => 'woman → women', 'ua' => 'жінка → жінки'],
                                    ['en' => 'foot → feet', 'ua' => 'стопа → стопи'],
                                    ['en' => 'tooth → teeth', 'ua' => 'зуб → зуби'],
                                ],
                            ],
                            [
                                'label' => 'Повністю інша форма',
                                'color' => 'rose',
                                'description' => 'Деякі іменники мають <strong>абсолютно іншу</strong> форму множини.',
                                'examples' => [
                                    ['en' => 'child → children', 'ua' => 'дитина → діти'],
                                    ['en' => 'person → people', 'ua' => 'людина → люди'],
                                    ['en' => 'mouse → mice', 'ua' => 'миша → миші'],
                                    ['en' => 'goose → geese', 'ua' => 'гусак → гуси'],
                                ],
                            ],
                            [
                                'label' => 'Однакова форма',
                                'color' => 'emerald',
                                'description' => 'Деякі іменники <strong>не змінюються</strong> у множині.',
                                'examples' => [
                                    ['en' => 'sheep → sheep', 'ua' => 'вівця → вівці'],
                                    ['en' => 'fish → fish', 'ua' => 'риба → риби'],
                                    ['en' => 'deer → deer', 'ua' => 'олень → олені'],
                                    ['en' => 'series → series', 'ua' => 'серія → серії'],
                                ],
                                'note' => '📌 Fish може бути fishes, якщо різні види риб.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняльна таблиця правил',
                        'intro' => 'Основні правила утворення множини:',
                        'rows' => [
                            [
                                'en' => 'Most nouns',
                                'ua' => 'Більшість іменників',
                                'note' => '+ s: cat → cats, book → books',
                            ],
                            [
                                'en' => 'After s, x, z, ch, sh',
                                'ua' => 'Після s, x, z, ch, sh',
                                'note' => '+ es: box → boxes, bus → buses',
                            ],
                            [
                                'en' => 'Consonant + y',
                                'ua' => 'Приголосна + y',
                                'note' => 'y → ies: baby → babies, city → cities',
                            ],
                            [
                                'en' => 'Vowel + y',
                                'ua' => 'Голосна + y',
                                'note' => '+ s: boy → boys, day → days',
                            ],
                            [
                                'en' => 'f / fe',
                                'ua' => 'Закінчення f / fe',
                                'note' => 'f/fe → ves: knife → knives, leaf → leaves',
                            ],
                            [
                                'en' => 'Irregular',
                                'ua' => 'Неправильні',
                                'note' => 'man → men, child → children, sheep → sheep',
                            ],
                        ],
                        'warning' => '📌 Неправильні форми множини треба вивчити напамʼять!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Забути про -es після шиплячих.',
                                'wrong' => 'two boxs, three buss',
                                'right' => '✅ <span class="font-mono">two boxes, three buses</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Не змінити y на ies.',
                                'wrong' => 'babys, citys',
                                'right' => '✅ <span class="font-mono">babies, cities</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Змінити y на ies після голосної.',
                                'wrong' => 'boies, daies',
                                'right' => '✅ <span class="font-mono">boys, days</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Правильна форма неправильних іменників.',
                                'wrong' => 'childs, mans, foots',
                                'right' => '✅ <span class="font-mono">children, men, feet</span>',
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
                            '<strong>Більшість іменників</strong> — просто додаємо <strong>-s</strong>: <em>cat → cats, book → books</em>.',
                            '<strong>Після s, x, z, ch, sh</strong> — додаємо <strong>-es</strong>: <em>box → boxes, bus → buses</em>.',
                            '<strong>Приголосна + y</strong> — змінюємо на <strong>-ies</strong>: <em>baby → babies, city → cities</em>.',
                            '<strong>Голосна + y</strong> — просто додаємо <strong>-s</strong>: <em>boy → boys, day → days</em>.',
                            '<strong>F / Fe</strong> — часто змінюємо на <strong>-ves</strong>: <em>knife → knives, leaf → leaves</em>.',
                            '<strong>Неправильні форми</strong> — треба запамʼятати: <em>man → men, child → children, sheep → sheep</em>.',
                            '<strong>Вимова -s</strong>: [s] після глухих, [z] після дзвінких, [ɪz] після шиплячих.',
                            'Деякі іменники <strong>не змінюються</strong>: <em>sheep, fish, deer</em>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильну форму множини',
                        'select_intro' => 'Обери правильну форму множини для кожного іменника.',
                        'selects' => [
                            ['label' => 'cat → (cats / cates / caties)', 'prompt' => 'Яка множина?'],
                            ['label' => 'box → (boxs / boxes / boxies)', 'prompt' => 'Яка множина?'],
                            ['label' => 'baby → (babys / babies / babes)', 'prompt' => 'Яка множина?'],
                            ['label' => 'boy → (boies / boys / boyes)', 'prompt' => 'Яка множина?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант', 'Третій варіант'],
                        'input_title' => 'Вправа 2. Напиши форму множини',
                        'input_intro' => 'Напиши правильну форму множини для цих іменників.',
                        'inputs' => [
                            ['before' => 'child → ', 'after' => ''],
                            ['before' => 'knife → ', 'after' => ''],
                            ['before' => 'potato → ', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у формі множини.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I have two childs.',
                                'example_target' => 'I have two children.',
                            ],
                            [
                                'original' => '1. There are three boxs on the table.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. I visited five citys last summer.',
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
                                'label' => 'Plural Nouns — Множина іменників (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
                                'current' => false,
                            ],
                            [
                                'label' => 'Countable vs Uncountable — Злічувані / незлічувані',
                                'current' => false,
                            ],
                            [
                                'label' => 'Some / Any — Кількість',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
