<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityCountableUncountableNounsTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'countable-vs-uncountable-nouns';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Countable vs Uncountable Nouns — Злічувані / незлічувані іменники',
            'subtitle_html' => '<p><strong>Countable nouns</strong> (злічувані іменники) можна порахувати: one apple, two apples. <strong>Uncountable nouns</strong> (незлічувані іменники) не мають множини: water, information, advice. Ця різниця впливає на вибір артиклів та слів кількості.</p>',
            'subtitle_text' => 'Теоретичний огляд злічуваних та незлічуваних іменників в англійській мові: правила, приклади та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Countable Nouns',
                'Uncountable Nouns',
                'Nouns',
                'Articles',
                'Quantity',
                'A/An',
                'Some/Any',
                'Much/Many',
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
                        'intro' => 'У цій темі ти вивчиш різницю між <strong>злічуваними</strong> (countable) та <strong>незлічуваними</strong> (uncountable) іменниками — як їх розрізняти та правильно використовувати.',
                        'rules' => [
                            [
                                'label' => 'Countable',
                                'color' => 'emerald',
                                'text' => '<strong>Злічувані</strong> — можна порахувати:',
                                'example' => 'one apple, two apples, three books',
                            ],
                            [
                                'label' => 'Uncountable',
                                'color' => 'amber',
                                'text' => '<strong>Незлічувані</strong> — не можна порахувати:',
                                'example' => 'water, information, advice, music',
                            ],
                            [
                                'label' => 'Articles',
                                'color' => 'blue',
                                'text' => '<strong>Артиклі:</strong> a/an — лише злічувані:',
                                'example' => 'a book ✓ / a water ✗',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке злічувані та незлічувані іменники?',
                        'intro' => 'Іменники поділяються на дві категорії:',
                        'items' => [
                            ['label' => 'Countable', 'title' => 'Злічувані', 'subtitle' => 'Можна порахувати, мають однину і множину (apple → apples)'],
                            ['label' => 'Uncountable', 'title' => 'Незлічувані', 'subtitle' => 'Не можна порахувати, не мають множини (water, music)'],
                            ['label' => 'Both', 'title' => 'Обидві форми', 'subtitle' => 'Деякі іменники можуть бути обома (chicken — курка / курятина)'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Countable Nouns — Злічувані іменники',
                        'sections' => [
                            [
                                'label' => 'Ознаки злічуваних',
                                'color' => 'emerald',
                                'description' => '<strong>Злічувані іменники</strong> можна порахувати. Вони мають <strong>однину</strong> та <strong>множину</strong>.',
                                'examples' => [
                                    ['en' => 'one book, two books, three books', 'ua' => 'одна книга, дві книги, три книги'],
                                    ['en' => 'a cat, some cats, many cats', 'ua' => 'кіт, кілька котів, багато котів'],
                                    ['en' => 'an idea, several ideas', 'ua' => 'ідея, кілька ідей'],
                                ],
                            ],
                            [
                                'label' => 'З артиклями a/an',
                                'color' => 'sky',
                                'description' => 'Злічувані іменники в однині вживаються з <strong>a/an</strong> або іншим визначником.',
                                'examples' => [
                                    ['en' => 'I need a pen.', 'ua' => 'Мені потрібна ручка.'],
                                    ['en' => 'She is an engineer.', 'ua' => 'Вона інженер.'],
                                    ['en' => 'Give me the book.', 'ua' => 'Дай мені книгу.'],
                                ],
                                'note' => '❌ Не можна: <em>I need pen.</em> — без артикля неправильно!',
                            ],
                            [
                                'label' => 'Приклади злічуваних',
                                'color' => 'amber',
                                'description' => 'Типові злічувані іменники:',
                                'examples' => [
                                    ['en' => 'book, pen, table, chair', 'ua' => 'книга, ручка, стіл, стілець'],
                                    ['en' => 'apple, banana, egg, sandwich', 'ua' => 'яблуко, банан, яйце, сендвіч'],
                                    ['en' => 'person, friend, student, teacher', 'ua' => 'людина, друг, студент, вчитель'],
                                    ['en' => 'idea, problem, question, answer', 'ua' => 'ідея, проблема, питання, відповідь'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Uncountable Nouns — Незлічувані іменники',
                        'sections' => [
                            [
                                'label' => 'Ознаки незлічуваних',
                                'color' => 'amber',
                                'description' => '<strong>Незлічувані іменники</strong> не можна порахувати. Вони <strong>не мають множини</strong> і вживаються з дієсловом в однині.',
                                'examples' => [
                                    ['en' => 'Water is important.', 'ua' => 'Вода важлива. (не waters)'],
                                    ['en' => 'Information is useful.', 'ua' => 'Інформація корисна. (не informations)'],
                                    ['en' => 'Music makes me happy.', 'ua' => 'Музика робить мене щасливим.'],
                                ],
                            ],
                            [
                                'label' => 'Без артикля a/an',
                                'color' => 'rose',
                                'description' => 'Незлічувані іменники <strong>НЕ вживаються</strong> з артиклями a/an.',
                                'examples' => [
                                    ['en' => '✓ I need water.', 'ua' => 'Мені потрібна вода.'],
                                    ['en' => '✗ I need a water.', 'ua' => '(неправильно!)'],
                                    ['en' => '✓ She gave me advice.', 'ua' => 'Вона дала мені пораду.'],
                                    ['en' => '✗ She gave me an advice.', 'ua' => '(неправильно!)'],
                                ],
                            ],
                            [
                                'label' => 'Категорії незлічуваних',
                                'color' => 'sky',
                                'description' => 'Основні групи незлічуваних іменників:',
                                'examples' => [
                                    ['en' => 'Liquids: water, milk, coffee, tea, juice', 'ua' => 'Рідини: вода, молоко, кава, чай, сік'],
                                    ['en' => 'Materials: wood, plastic, glass, paper', 'ua' => 'Матеріали: дерево, пластик, скло, папір'],
                                    ['en' => 'Abstract: information, advice, news, knowledge', 'ua' => 'Абстрактні: інформація, порада, новини, знання'],
                                    ['en' => 'Food masses: rice, bread, sugar, salt, meat', 'ua' => 'Їжа (маси): рис, хліб, цукор, сіль, мʼясо'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Як вимірювати незлічувані іменники',
                        'sections' => [
                            [
                                'label' => 'Одиниці виміру',
                                'color' => 'emerald',
                                'description' => 'Щоб «порахувати» незлічувані, використовуємо <strong>одиниці виміру</strong>.',
                                'examples' => [
                                    ['en' => 'a glass of water', 'ua' => 'склянка води'],
                                    ['en' => 'a cup of coffee', 'ua' => 'чашка кави'],
                                    ['en' => 'a bottle of milk', 'ua' => 'пляшка молока'],
                                    ['en' => 'a piece of advice', 'ua' => 'порада (одна)'],
                                ],
                            ],
                            [
                                'label' => 'Типові конструкції',
                                'color' => 'sky',
                                'description' => 'Популярні способи вимірювання:',
                                'examples' => [
                                    ['en' => 'a piece of: cake, paper, news, furniture', 'ua' => 'шматок/частина: торта, паперу, новина, меблі'],
                                    ['en' => 'a slice of: bread, pizza, cheese', 'ua' => 'скибка: хліба, піци, сиру'],
                                    ['en' => 'a loaf of: bread', 'ua' => 'буханка хліба'],
                                    ['en' => 'a bar of: chocolate, soap', 'ua' => 'плитка шоколаду, шматок мила'],
                                ],
                            ],
                            [
                                'label' => 'Some / A bit of',
                                'color' => 'amber',
                                'description' => 'Для невизначеної кількості:',
                                'examples' => [
                                    ['en' => 'some water, some information', 'ua' => 'трохи води, трохи інформації'],
                                    ['en' => 'a bit of luck, a bit of help', 'ua' => 'трохи удачі, трохи допомоги'],
                                    ['en' => 'a little sugar, a little time', 'ua' => 'трохи цукру, трохи часу'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Слова кількості (Quantifiers)',
                        'sections' => [
                            [
                                'label' => 'Тільки злічувані',
                                'color' => 'emerald',
                                'description' => 'Ці слова вживаються <strong>тільки зі злічуваними</strong>:',
                                'examples' => [
                                    ['en' => 'many books, many friends', 'ua' => 'багато книг, багато друзів'],
                                    ['en' => 'a few questions', 'ua' => 'кілька запитань'],
                                    ['en' => 'few people (мало — негативно)', 'ua' => 'мало людей'],
                                    ['en' => 'several ideas', 'ua' => 'кілька ідей'],
                                ],
                            ],
                            [
                                'label' => 'Тільки незлічувані',
                                'color' => 'amber',
                                'description' => 'Ці слова вживаються <strong>тільки з незлічуваними</strong>:',
                                'examples' => [
                                    ['en' => 'much time, much money', 'ua' => 'багато часу, багато грошей'],
                                    ['en' => 'a little water', 'ua' => 'трохи води'],
                                    ['en' => 'little hope (мало — негативно)', 'ua' => 'мало надії'],
                                ],
                            ],
                            [
                                'label' => 'Обидва типи',
                                'color' => 'sky',
                                'description' => 'Універсальні слова для <strong>обох типів</strong>:',
                                'examples' => [
                                    ['en' => 'some books / some water', 'ua' => 'кілька книг / трохи води'],
                                    ['en' => 'any questions / any information', 'ua' => 'будь-які питання / будь-яка інформація'],
                                    ['en' => 'a lot of friends / a lot of time', 'ua' => 'багато друзів / багато часу'],
                                    ['en' => 'lots of people / lots of money', 'ua' => 'багато людей / багато грошей'],
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
                        'intro' => 'Основні відмінності злічуваних та незлічуваних:',
                        'rows' => [
                            [
                                'en' => 'Plural form',
                                'ua' => 'Множина',
                                'note' => 'Countable: books ✓ / Uncountable: waters ✗',
                            ],
                            [
                                'en' => 'A / An',
                                'ua' => 'Артикль a/an',
                                'note' => 'Countable: a book ✓ / Uncountable: a water ✗',
                            ],
                            [
                                'en' => 'Numbers',
                                'ua' => 'Числа',
                                'note' => 'Countable: two books ✓ / Uncountable: two waters ✗',
                            ],
                            [
                                'en' => 'Many / Much',
                                'ua' => 'Багато',
                                'note' => 'Countable: many ✓ / Uncountable: much ✓',
                            ],
                            [
                                'en' => 'Few / Little',
                                'ua' => 'Мало',
                                'note' => 'Countable: few ✓ / Uncountable: little ✓',
                            ],
                        ],
                        'warning' => '📌 <strong>A lot of / Some / Any</strong> — універсальні для обох типів!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Іменники з подвійним значенням',
                        'sections' => [
                            [
                                'label' => 'Різне значення',
                                'color' => 'purple',
                                'description' => 'Деякі іменники можуть бути <strong>злічуваними АБО незлічуваними</strong> з різним значенням:',
                                'examples' => [
                                    ['en' => 'chicken (uncountable) = meat', 'ua' => 'курятина (мʼясо)'],
                                    ['en' => 'a chicken (countable) = bird', 'ua' => 'курка (птах)'],
                                    ['en' => 'coffee (uncountable) = drink', 'ua' => 'кава (напій)'],
                                    ['en' => 'a coffee (countable) = a cup', 'ua' => 'чашка кави'],
                                ],
                            ],
                            [
                                'label' => 'Більше прикладів',
                                'color' => 'sky',
                                'description' => 'Інші іменники з подвійним значенням:',
                                'examples' => [
                                    ['en' => 'glass (uncountable) = material', 'ua' => 'скло (матеріал)'],
                                    ['en' => 'a glass (countable) = container', 'ua' => 'склянка (посуд)'],
                                    ['en' => 'paper (uncountable) = material', 'ua' => 'папір (матеріал)'],
                                    ['en' => 'a paper (countable) = document/newspaper', 'ua' => 'документ / газета'],
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
                                'title' => 'Множина незлічуваних.',
                                'wrong' => 'I need some informations.',
                                'right' => '✅ <span class="font-mono">I need some information.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Артикль a/an з незлічуваними.',
                                'wrong' => 'Can I have a water?',
                                'right' => '✅ <span class="font-mono">Can I have some water?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Much зі злічуваними.',
                                'wrong' => 'There are much books here.',
                                'right' => '✅ <span class="font-mono">There are many books here.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Many з незлічуваними.',
                                'wrong' => 'I have many money.',
                                'right' => '✅ <span class="font-mono">I have much money. / I have a lot of money.</span>',
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
                            '<strong>Злічувані (Countable)</strong> — можна порахувати, мають множину: <em>one book, two books</em>.',
                            '<strong>Незлічувані (Uncountable)</strong> — не можна порахувати, без множини: <em>water, information</em>.',
                            '<strong>A / An</strong> — тільки зі злічуваними в однині: <em>a book</em> ✓, <em>a water</em> ✗.',
                            '<strong>Many / Few</strong> — тільки зі злічуваними: <em>many books, few friends</em>.',
                            '<strong>Much / Little</strong> — тільки з незлічуваними: <em>much time, little hope</em>.',
                            '<strong>A lot of / Some / Any</strong> — універсальні для обох типів.',
                            'Для «підрахунку» незлічуваних: <em>a glass of water, a piece of advice</em>.',
                            'Деякі іменники мають обидва значення: <em>chicken</em> (мʼясо) vs <em>a chicken</em> (птах).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Визнач тип іменника',
                        'select_intro' => 'Визнач, чи іменник злічуваний (C) чи незлічуваний (U).',
                        'selects' => [
                            ['label' => 'water (C / U)', 'prompt' => 'Який тип?'],
                            ['label' => 'book (C / U)', 'prompt' => 'Який тип?'],
                            ['label' => 'information (C / U)', 'prompt' => 'Який тип?'],
                            ['label' => 'apple (C / U)', 'prompt' => 'Який тип?'],
                        ],
                        'options' => ['Countable', 'Uncountable'],
                        'input_title' => 'Вправа 2. Обери many або much',
                        'input_intro' => 'Заповни пропуски словом many або much.',
                        'inputs' => [
                            ['before' => 'How ___ books do you have?', 'after' => '→'],
                            ['before' => 'I don\'t have ___ time.', 'after' => '→'],
                            ['before' => 'There are ___ people here.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I need a advice.',
                                'example_target' => 'I need some advice. / I need a piece of advice.',
                            ],
                            [
                                'original' => '1. She gave me many informations.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Can I have a milk?',
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
                                'label' => 'Countable vs Uncountable — Злічувані / незлічувані (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles — Артиклі a/an/the',
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
