<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityQuantifiersTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'quantifiers-much-many-a-lot-few-little';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Quantifiers — Much, Many, A Lot, Few, Little',
            'subtitle_html' => '<p><strong>Quantifiers</strong> (квантифікатори) — це слова кількості в англійській мові: <strong>much, many, a lot of, few, little</strong>. Вони вказують на кількість іменників і залежать від того, чи іменник злічуваний чи незлічуваний.</p>',
            'subtitle_text' => 'Теоретичний огляд слів кількості в англійській мові: much, many, a lot of, few, little — правила вживання та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Quantifiers',
                'Much',
                'Many',
                'A lot of',
                'Few',
                'Little',
                'A few',
                'A little',
                'Quantity',
                'Grammar',
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
                        'intro' => 'У цій темі ти вивчиш <strong>слова кількості</strong>: <strong>much, many, a lot of, few, little</strong> — як їх правильно використовувати зі злічуваними та незлічуваними іменниками.',
                        'rules' => [
                            [
                                'label' => 'Many',
                                'color' => 'emerald',
                                'text' => '<strong>Many</strong> — багато (злічувані):',
                                'example' => 'many books, many friends, many ideas',
                            ],
                            [
                                'label' => 'Much',
                                'color' => 'blue',
                                'text' => '<strong>Much</strong> — багато (незлічувані):',
                                'example' => 'much time, much money, much water',
                            ],
                            [
                                'label' => 'A lot of',
                                'color' => 'amber',
                                'text' => '<strong>A lot of</strong> — багато (обидва типи):',
                                'example' => 'a lot of books, a lot of time',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке квантифікатори?',
                        'intro' => 'Quantifiers (квантифікатори) — це слова, що позначають кількість:',
                        'items' => [
                            ['label' => 'Much/Many', 'title' => 'Багато', 'subtitle' => 'Much — з незлічуваними, Many — зі злічуваними'],
                            ['label' => 'A lot of', 'title' => 'Багато (універсальне)', 'subtitle' => 'Підходить для обох типів іменників'],
                            ['label' => 'Few/Little', 'title' => 'Мало', 'subtitle' => 'Few — зі злічуваними, Little — з незлічуваними'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Many — зі злічуваними іменниками',
                        'sections' => [
                            [
                                'label' => 'Правило',
                                'color' => 'emerald',
                                'description' => '<strong>Many</strong> вживається зі <strong>злічуваними іменниками у множині</strong>.',
                                'examples' => [
                                    ['en' => 'many books', 'ua' => 'багато книг'],
                                    ['en' => 'many friends', 'ua' => 'багато друзів'],
                                    ['en' => 'many people', 'ua' => 'багато людей'],
                                    ['en' => 'many ideas', 'ua' => 'багато ідей'],
                                ],
                            ],
                            [
                                'label' => 'У реченнях',
                                'color' => 'sky',
                                'description' => 'Приклади використання <strong>many</strong> у різних типах речень:',
                                'examples' => [
                                    ['en' => 'How many books do you have?', 'ua' => 'Скільки в тебе книг?'],
                                    ['en' => 'I don\'t have many friends here.', 'ua' => 'У мене тут немає багатьох друзів.'],
                                    ['en' => 'There are many people in the park.', 'ua' => 'У парку багато людей.'],
                                    ['en' => 'Do you have many questions?', 'ua' => 'У тебе багато питань?'],
                                ],
                            ],
                            [
                                'label' => 'Важливо!',
                                'color' => 'amber',
                                'description' => '<strong>Many</strong> частіше вживається у <strong>питаннях та запереченнях</strong>.',
                                'examples' => [
                                    ['en' => '❓ How many friends do you have?', 'ua' => 'Питання'],
                                    ['en' => '❌ I don\'t have many books.', 'ua' => 'Заперечення'],
                                    ['en' => '✓ I have a lot of books. (ствердження)', 'ua' => 'У стверджувальних краще a lot of'],
                                ],
                                'note' => '📌 У стверджувальних реченнях краще використовувати <strong>a lot of</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Much — з незлічуваними іменниками',
                        'sections' => [
                            [
                                'label' => 'Правило',
                                'color' => 'blue',
                                'description' => '<strong>Much</strong> вживається з <strong>незлічуваними іменниками</strong>.',
                                'examples' => [
                                    ['en' => 'much time', 'ua' => 'багато часу'],
                                    ['en' => 'much money', 'ua' => 'багато грошей'],
                                    ['en' => 'much water', 'ua' => 'багато води'],
                                    ['en' => 'much information', 'ua' => 'багато інформації'],
                                ],
                            ],
                            [
                                'label' => 'У реченнях',
                                'color' => 'sky',
                                'description' => 'Приклади використання <strong>much</strong> у різних типах речень:',
                                'examples' => [
                                    ['en' => 'How much money do you need?', 'ua' => 'Скільки грошей тобі потрібно?'],
                                    ['en' => 'I don\'t have much time today.', 'ua' => 'У мене сьогодні небагато часу.'],
                                    ['en' => 'There isn\'t much water left.', 'ua' => 'Води залишилося небагато.'],
                                    ['en' => 'Do you have much experience?', 'ua' => 'У тебе багато досвіду?'],
                                ],
                            ],
                            [
                                'label' => 'Важливо!',
                                'color' => 'amber',
                                'description' => '<strong>Much</strong> також частіше у <strong>питаннях та запереченнях</strong>.',
                                'examples' => [
                                    ['en' => '❓ How much time do you have?', 'ua' => 'Питання'],
                                    ['en' => '❌ I don\'t have much money.', 'ua' => 'Заперечення'],
                                    ['en' => '✓ I have a lot of money. (ствердження)', 'ua' => 'У стверджувальних краще a lot of'],
                                ],
                                'note' => '📌 У стверджувальних реченнях краще використовувати <strong>a lot of</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. A lot of / Lots of — універсальні',
                        'sections' => [
                            [
                                'label' => 'З обома типами',
                                'color' => 'purple',
                                'description' => '<strong>A lot of</strong> та <strong>lots of</strong> — універсальні, працюють з <strong>обома типами іменників</strong>.',
                                'examples' => [
                                    ['en' => 'a lot of books (countable)', 'ua' => 'багато книг (злічувані)'],
                                    ['en' => 'a lot of time (uncountable)', 'ua' => 'багато часу (незлічувані)'],
                                    ['en' => 'lots of friends', 'ua' => 'багато друзів'],
                                    ['en' => 'lots of money', 'ua' => 'багато грошей'],
                                ],
                            ],
                            [
                                'label' => 'У стверджувальних',
                                'color' => 'emerald',
                                'description' => '<strong>A lot of</strong> — найкраще для <strong>стверджувальних речень</strong>.',
                                'examples' => [
                                    ['en' => 'I have a lot of friends.', 'ua' => 'У мене багато друзів.'],
                                    ['en' => 'She has a lot of time.', 'ua' => 'У неї багато часу.'],
                                    ['en' => 'We read a lot of books.', 'ua' => 'Ми читаємо багато книг.'],
                                    ['en' => 'They drink a lot of water.', 'ua' => 'Вони пʼють багато води.'],
                                ],
                            ],
                            [
                                'label' => 'Lots of — неформальніше',
                                'color' => 'sky',
                                'description' => '<strong>Lots of</strong> — більш неформальний варіант.',
                                'examples' => [
                                    ['en' => 'I have lots of work to do.', 'ua' => 'У мене купа роботи.'],
                                    ['en' => 'There are lots of people here.', 'ua' => 'Тут багато людей.'],
                                ],
                                'note' => '📌 <strong>A lot of</strong> — нейтральніше, <strong>lots of</strong> — розмовніше.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Few — мало (злічувані)',
                        'sections' => [
                            [
                                'label' => 'Few — мало (негативно)',
                                'color' => 'rose',
                                'description' => '<strong>Few</strong> означає <strong>мало, недостатньо</strong> (негативний відтінок).',
                                'examples' => [
                                    ['en' => 'I have few friends here.', 'ua' => 'У мене тут мало друзів. (недостатньо)'],
                                    ['en' => 'Few people know about it.', 'ua' => 'Мало хто знає про це.'],
                                    ['en' => 'There are few books on the shelf.', 'ua' => 'На полиці мало книг.'],
                                ],
                            ],
                            [
                                'label' => 'A few — кілька (позитивно)',
                                'color' => 'emerald',
                                'description' => '<strong>A few</strong> означає <strong>кілька, трохи</strong> (позитивний відтінок).',
                                'examples' => [
                                    ['en' => 'I have a few friends here.', 'ua' => 'У мене тут є кілька друзів. (достатньо)'],
                                    ['en' => 'A few people know about it.', 'ua' => 'Кілька людей знають про це.'],
                                    ['en' => 'I need a few minutes.', 'ua' => 'Мені потрібно кілька хвилин.'],
                                ],
                            ],
                            [
                                'label' => 'Різниця Few vs A Few',
                                'color' => 'amber',
                                'description' => 'Артикль <strong>a</strong> змінює значення!',
                                'examples' => [
                                    ['en' => 'Few people came. ❌ (мало, погано)', 'ua' => 'Мало людей прийшло.'],
                                    ['en' => 'A few people came. ✓ (кілька, добре)', 'ua' => 'Кілька людей прийшло.'],
                                ],
                                'note' => '📌 <strong>Few</strong> = недостатньо, <strong>a few</strong> = достатньо.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Little — мало (незлічувані)',
                        'sections' => [
                            [
                                'label' => 'Little — мало (негативно)',
                                'color' => 'rose',
                                'description' => '<strong>Little</strong> означає <strong>мало, недостатньо</strong> (негативний відтінок).',
                                'examples' => [
                                    ['en' => 'I have little time.', 'ua' => 'У мене мало часу. (недостатньо)'],
                                    ['en' => 'There is little water left.', 'ua' => 'Води залишилося мало.'],
                                    ['en' => 'We have little information.', 'ua' => 'У нас мало інформації.'],
                                ],
                            ],
                            [
                                'label' => 'A little — трохи (позитивно)',
                                'color' => 'emerald',
                                'description' => '<strong>A little</strong> означає <strong>трохи, небагато</strong> (позитивний відтінок).',
                                'examples' => [
                                    ['en' => 'I have a little time.', 'ua' => 'У мене є трохи часу. (достатньо)'],
                                    ['en' => 'There is a little water left.', 'ua' => 'Ще є трохи води.'],
                                    ['en' => 'I need a little help.', 'ua' => 'Мені потрібна трохи допомоги.'],
                                ],
                            ],
                            [
                                'label' => 'Різниця Little vs A Little',
                                'color' => 'amber',
                                'description' => 'Артикль <strong>a</strong> змінює значення!',
                                'examples' => [
                                    ['en' => 'There is little hope. ❌ (мало, погано)', 'ua' => 'Є мало надії.'],
                                    ['en' => 'There is a little hope. ✓ (трохи, добре)', 'ua' => 'Є трохи надії.'],
                                ],
                                'note' => '📌 <strong>Little</strong> = недостатньо, <strong>a little</strong> = достатньо.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Some / Any — деяка кількість',
                        'sections' => [
                            [
                                'label' => 'Some — у стверджувальних',
                                'color' => 'sky',
                                'description' => '<strong>Some</strong> — для обох типів у <strong>стверджувальних реченнях</strong>.',
                                'examples' => [
                                    ['en' => 'I have some books.', 'ua' => 'У мене є кілька книг.'],
                                    ['en' => 'She has some money.', 'ua' => 'У неї є трохи грошей.'],
                                    ['en' => 'We need some time.', 'ua' => 'Нам потрібен час.'],
                                ],
                            ],
                            [
                                'label' => 'Any — у питаннях і запереченнях',
                                'color' => 'purple',
                                'description' => '<strong>Any</strong> — для обох типів у <strong>питаннях та запереченнях</strong>.',
                                'examples' => [
                                    ['en' => 'Do you have any questions?', 'ua' => 'У тебе є якісь питання?'],
                                    ['en' => 'I don\'t have any money.', 'ua' => 'У мене немає грошей.'],
                                    ['en' => 'Is there any water?', 'ua' => 'Є вода?'],
                                ],
                            ],
                            [
                                'label' => 'Універсальність',
                                'color' => 'emerald',
                                'description' => '<strong>Some/Any</strong> працюють з обома типами іменників.',
                                'examples' => [
                                    ['en' => 'some books / some water', 'ua' => 'кілька книг / трохи води'],
                                    ['en' => 'any friends / any time', 'ua' => 'якісь друзі / будь-який час'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняльна таблиця',
                        'intro' => 'Основні квантифікатори та їх використання:',
                        'rows' => [
                            [
                                'en' => 'Many',
                                'ua' => 'Багато (злічувані)',
                                'note' => 'many books, many friends (питання/заперечення)',
                            ],
                            [
                                'en' => 'Much',
                                'ua' => 'Багато (незлічувані)',
                                'note' => 'much time, much money (питання/заперечення)',
                            ],
                            [
                                'en' => 'A lot of / Lots of',
                                'ua' => 'Багато (обидва типи)',
                                'note' => 'a lot of books/time (особливо ствердження)',
                            ],
                            [
                                'en' => 'Few',
                                'ua' => 'Мало (злічувані, негативно)',
                                'note' => 'few people (недостатньо)',
                            ],
                            [
                                'en' => 'A few',
                                'ua' => 'Кілька (злічувані, позитивно)',
                                'note' => 'a few friends (достатньо)',
                            ],
                            [
                                'en' => 'Little',
                                'ua' => 'Мало (незлічувані, негативно)',
                                'note' => 'little time (недостатньо)',
                            ],
                            [
                                'en' => 'A little',
                                'ua' => 'Трохи (незлічувані, позитивно)',
                                'note' => 'a little water (достатньо)',
                            ],
                        ],
                        'warning' => '📌 Артикль <strong>a</strong> перед few/little змінює значення з негативного на позитивне!',
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
                                'title' => 'Much зі злічуваними іменниками.',
                                'wrong' => 'I have much books.',
                                'right' => '✅ <span class="font-mono">I have many books. / I have a lot of books.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Many з незлічуваними іменниками.',
                                'wrong' => 'I don\'t have many time.',
                                'right' => '✅ <span class="font-mono">I don\'t have much time.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Забути артикль a перед few/little.',
                                'wrong' => 'I have few time. (неправильне значення)',
                                'right' => '✅ <span class="font-mono">I have a little time. (позитивне значення)</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Many/much у стверджувальних.',
                                'wrong' => 'I have many friends. (краще інакше)',
                                'right' => '✅ <span class="font-mono">I have a lot of friends. (природніше)</span>',
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
                            '<strong>Many</strong> — зі злічуваними: <em>many books, many friends</em> (питання/заперечення).',
                            '<strong>Much</strong> — з незлічуваними: <em>much time, much money</em> (питання/заперечення).',
                            '<strong>A lot of / Lots of</strong> — універсальні для обох типів, особливо у стверджувальних.',
                            '<strong>Few</strong> (мало, негативно) vs <strong>a few</strong> (кілька, позитивно) — зі злічуваними.',
                            '<strong>Little</strong> (мало, негативно) vs <strong>a little</strong> (трохи, позитивно) — з незлічуваними.',
                            '<strong>Some</strong> — у стверджувальних, <strong>any</strong> — у питаннях і запереченнях.',
                            'Артикль <strong>a</strong> перед few/little робить значення позитивним!',
                            'У стверджувальних реченнях краще використовувати <strong>a lot of</strong> замість many/much.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильний квантифікатор',
                        'select_intro' => 'Обери правильне слово кількості для кожного речення.',
                        'selects' => [
                            ['label' => 'I don\'t have ___ time. (much / many)', 'prompt' => 'Який квантифікатор?'],
                            ['label' => 'How ___ books do you have? (much / many)', 'prompt' => 'Який квантифікатор?'],
                            ['label' => 'She has ___ friends. (a lot of / much)', 'prompt' => 'Який квантифікатор?'],
                            ['label' => 'There is ___ water left. (few / little)', 'prompt' => 'Який квантифікатор?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши many, much, або a lot of.',
                        'inputs' => [
                            ['before' => 'I have ___ work to do.', 'after' => '→'],
                            ['before' => 'How ___ money do you need?', 'after' => '→'],
                            ['before' => 'There are ___ people here.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку з квантифікатором.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I have much books.',
                                'example_target' => 'I have many books. / I have a lot of books.',
                            ],
                            [
                                'original' => '1. She doesn\'t have many time.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. There are much people in the room.',
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
                                'label' => 'Quantifiers — Much, Many, A Lot, Few, Little (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
                                'current' => false,
                            ],
                            [
                                'label' => 'Zero Article — Нульовий артикль',
                                'current' => false,
                            ],
                            [
                                'label' => 'Plural Nouns — Множина іменників',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
