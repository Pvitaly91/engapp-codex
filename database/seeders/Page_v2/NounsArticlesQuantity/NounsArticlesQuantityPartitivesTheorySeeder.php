<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Pages\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityPartitivesTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'partitives-with-uncountable-nouns-a-piece-of-a-cup-of';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Partitives with uncountable nouns — a piece of, a cup of…',
            'subtitle_html' => '<p><strong>Partitives</strong> (партитиви) — це конструкції, що допомагають «порахувати» незлічувані іменники: <strong>a piece of, a cup of, a glass of, a slice of</strong> тощо. Вони вказують на конкретну порцію або одиницю незлічуваного іменника.</p>',
            'subtitle_text' => 'Теоретичний огляд партитивів в англійській мові: як вимірювати незлічувані іменники за допомогою a piece of, a cup of та інших конструкцій.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Partitives',
                'Uncountable Nouns',
                'A piece of',
                'A cup of',
                'A glass of',
                'A slice of',
                'A bottle of',
                'Quantity',
                'Nouns',
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
                        'intro' => 'У цій темі ти вивчиш <strong>партитиви</strong> — конструкції, що допомагають <strong>вимірювати незлічувані іменники</strong>: <strong>a piece of, a cup of, a glass of, a slice of</strong> тощо.',
                        'rules' => [
                            [
                                'label' => 'A piece of',
                                'color' => 'emerald',
                                'text' => '<strong>Шматок / частина</strong> чогось:',
                                'example' => 'a piece of cake, a piece of advice',
                            ],
                            [
                                'label' => 'A cup of',
                                'color' => 'blue',
                                'text' => '<strong>Чашка / склянка</strong> напою:',
                                'example' => 'a cup of coffee, a cup of tea',
                            ],
                            [
                                'label' => 'A bottle of',
                                'color' => 'amber',
                                'text' => '<strong>Пляшка</strong> рідини:',
                                'example' => 'a bottle of water, a bottle of milk',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке партитиви?',
                        'intro' => 'Partitives (партитиви) — це способи вимірювання незлічуваних іменників:',
                        'items' => [
                            ['label' => 'Containers', 'title' => 'Посуд / Контейнери', 'subtitle' => 'a cup of, a glass of, a bottle of — вимірювання рідин'],
                            ['label' => 'Portions', 'title' => 'Порції / Частини', 'subtitle' => 'a piece of, a slice of, a loaf of — вимірювання їжі'],
                            ['label' => 'Units', 'title' => 'Одиниці виміру', 'subtitle' => 'a kilo of, a litre of, a pound of — вага та обʼєм'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. A piece of — шматок / частина',
                        'sections' => [
                            [
                                'label' => 'З їжею',
                                'color' => 'emerald',
                                'description' => '<strong>A piece of</strong> — для шматків їжі.',
                                'examples' => [
                                    ['en' => 'a piece of cake', 'ua' => 'шматок торта'],
                                    ['en' => 'a piece of bread', 'ua' => 'шматок хліба'],
                                    ['en' => 'a piece of cheese', 'ua' => 'шматок сиру'],
                                    ['en' => 'a piece of meat', 'ua' => 'шматок мʼяса'],
                                ],
                            ],
                            [
                                'label' => 'З абстракціями',
                                'color' => 'sky',
                                'description' => '<strong>A piece of</strong> — для абстрактних понять.',
                                'examples' => [
                                    ['en' => 'a piece of advice', 'ua' => 'порада (одна)'],
                                    ['en' => 'a piece of information', 'ua' => 'інформація (одна)'],
                                    ['en' => 'a piece of news', 'ua' => 'новина (одна)'],
                                    ['en' => 'a piece of evidence', 'ua' => 'доказ (один)'],
                                ],
                            ],
                            [
                                'label' => 'З предметами',
                                'color' => 'amber',
                                'description' => '<strong>A piece of</strong> — для предметів.',
                                'examples' => [
                                    ['en' => 'a piece of paper', 'ua' => 'аркуш паперу'],
                                    ['en' => 'a piece of furniture', 'ua' => 'предмет меблів'],
                                    ['en' => 'a piece of luggage', 'ua' => 'предмет багажу'],
                                    ['en' => 'a piece of equipment', 'ua' => 'предмет обладнання'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Напої — cup, glass, bottle',
                        'sections' => [
                            [
                                'label' => 'A cup of — чашка',
                                'color' => 'blue',
                                'description' => '<strong>A cup of</strong> — для гарячих напоїв у чашці.',
                                'examples' => [
                                    ['en' => 'a cup of coffee', 'ua' => 'чашка кави'],
                                    ['en' => 'a cup of tea', 'ua' => 'чашка чаю'],
                                    ['en' => 'a cup of hot chocolate', 'ua' => 'чашка гарячого шоколаду'],
                                    ['en' => 'two cups of coffee', 'ua' => 'дві чашки кави'],
                                ],
                            ],
                            [
                                'label' => 'A glass of — склянка',
                                'color' => 'sky',
                                'description' => '<strong>A glass of</strong> — для рідин у склянці.',
                                'examples' => [
                                    ['en' => 'a glass of water', 'ua' => 'склянка води'],
                                    ['en' => 'a glass of juice', 'ua' => 'склянка соку'],
                                    ['en' => 'a glass of milk', 'ua' => 'склянка молока'],
                                    ['en' => 'a glass of wine', 'ua' => 'келих вина'],
                                ],
                            ],
                            [
                                'label' => 'A bottle of — пляшка',
                                'color' => 'purple',
                                'description' => '<strong>A bottle of</strong> — для рідин у пляшці.',
                                'examples' => [
                                    ['en' => 'a bottle of water', 'ua' => 'пляшка води'],
                                    ['en' => 'a bottle of milk', 'ua' => 'пляшка молока'],
                                    ['en' => 'a bottle of wine', 'ua' => 'пляшка вина'],
                                    ['en' => 'a bottle of beer', 'ua' => 'пляшка пива'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Порції їжі — slice, loaf, bar',
                        'sections' => [
                            [
                                'label' => 'A slice of — скибка',
                                'color' => 'emerald',
                                'description' => '<strong>A slice of</strong> — для скибочок / шматочків.',
                                'examples' => [
                                    ['en' => 'a slice of bread', 'ua' => 'скибка хліба'],
                                    ['en' => 'a slice of pizza', 'ua' => 'шматок піци'],
                                    ['en' => 'a slice of cheese', 'ua' => 'скибка сиру'],
                                    ['en' => 'a slice of cake', 'ua' => 'шматок торта'],
                                ],
                            ],
                            [
                                'label' => 'A loaf of — буханка',
                                'color' => 'amber',
                                'description' => '<strong>A loaf of</strong> — для буханок.',
                                'examples' => [
                                    ['en' => 'a loaf of bread', 'ua' => 'буханка хліба'],
                                    ['en' => 'two loaves of bread', 'ua' => 'дві буханки хліба'],
                                ],
                            ],
                            [
                                'label' => 'A bar of — плитка / брусок',
                                'color' => 'rose',
                                'description' => '<strong>A bar of</strong> — для плиток / брусків.',
                                'examples' => [
                                    ['en' => 'a bar of chocolate', 'ua' => 'плитка шоколаду'],
                                    ['en' => 'a bar of soap', 'ua' => 'шматок мила'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Контейнери — box, can, jar',
                        'sections' => [
                            [
                                'label' => 'A box of — коробка',
                                'color' => 'purple',
                                'description' => '<strong>A box of</strong> — для речей у коробці.',
                                'examples' => [
                                    ['en' => 'a box of chocolates', 'ua' => 'коробка цукерок'],
                                    ['en' => 'a box of cereal', 'ua' => 'коробка сухого сніданку'],
                                    ['en' => 'a box of tissues', 'ua' => 'коробка серветок'],
                                ],
                            ],
                            [
                                'label' => 'A can of — банка (консервна)',
                                'color' => 'sky',
                                'description' => '<strong>A can of</strong> — для консервів.',
                                'examples' => [
                                    ['en' => 'a can of soup', 'ua' => 'банка супу'],
                                    ['en' => 'a can of beans', 'ua' => 'банка квасолі'],
                                    ['en' => 'a can of soda', 'ua' => 'банка газованої води'],
                                ],
                            ],
                            [
                                'label' => 'A jar of — банка (скляна)',
                                'color' => 'emerald',
                                'description' => '<strong>A jar of</strong> — для речей у скляній банці.',
                                'examples' => [
                                    ['en' => 'a jar of jam', 'ua' => 'банка джему'],
                                    ['en' => 'a jar of honey', 'ua' => 'банка меду'],
                                    ['en' => 'a jar of pickles', 'ua' => 'банка маринованих огірків'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Пакети та сумки — bag, packet, carton',
                        'sections' => [
                            [
                                'label' => 'A bag of — пакет / мішок',
                                'color' => 'amber',
                                'description' => '<strong>A bag of</strong> — для речей у пакеті або мішку.',
                                'examples' => [
                                    ['en' => 'a bag of sugar', 'ua' => 'пакет цукру'],
                                    ['en' => 'a bag of flour', 'ua' => 'пакет борошна'],
                                    ['en' => 'a bag of rice', 'ua' => 'мішок рису'],
                                    ['en' => 'a bag of potatoes', 'ua' => 'мішок картоплі'],
                                ],
                            ],
                            [
                                'label' => 'A packet of — пачка',
                                'color' => 'rose',
                                'description' => '<strong>A packet of</strong> — для речей у пачці.',
                                'examples' => [
                                    ['en' => 'a packet of crisps', 'ua' => 'пачка чіпсів'],
                                    ['en' => 'a packet of biscuits', 'ua' => 'пачка печива'],
                                    ['en' => 'a packet of cigarettes', 'ua' => 'пачка сигарет'],
                                ],
                            ],
                            [
                                'label' => 'A carton of — картонна коробка',
                                'color' => 'sky',
                                'description' => '<strong>A carton of</strong> — для рідин у картонній упаковці.',
                                'examples' => [
                                    ['en' => 'a carton of milk', 'ua' => 'картонна коробка молока'],
                                    ['en' => 'a carton of juice', 'ua' => 'картонна коробка соку'],
                                    ['en' => 'a carton of eggs', 'ua' => 'лоток яєць'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Одиниці виміру — kilo, litre, pound',
                        'sections' => [
                            [
                                'label' => 'A kilo of — кілограм',
                                'color' => 'purple',
                                'description' => '<strong>A kilo of</strong> — для ваги в кілограмах.',
                                'examples' => [
                                    ['en' => 'a kilo of sugar', 'ua' => 'кілограм цукру'],
                                    ['en' => 'a kilo of apples', 'ua' => 'кілограм яблук'],
                                    ['en' => 'two kilos of potatoes', 'ua' => 'два кілограми картоплі'],
                                ],
                            ],
                            [
                                'label' => 'A litre of — літр',
                                'color' => 'blue',
                                'description' => '<strong>A litre of</strong> — для обʼєму в літрах.',
                                'examples' => [
                                    ['en' => 'a litre of water', 'ua' => 'літр води'],
                                    ['en' => 'a litre of milk', 'ua' => 'літр молока'],
                                    ['en' => 'two litres of juice', 'ua' => 'два літри соку'],
                                ],
                            ],
                            [
                                'label' => 'A pound of — фунт',
                                'color' => 'emerald',
                                'description' => '<strong>A pound of</strong> — для ваги у фунтах (британська система).',
                                'examples' => [
                                    ['en' => 'a pound of butter', 'ua' => 'фунт масла'],
                                    ['en' => 'a pound of coffee', 'ua' => 'фунт кави'],
                                ],
                                'note' => '📌 1 pound = приблизно 450 грамів.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Інші корисні партитиви',
                        'sections' => [
                            [
                                'label' => 'A bowl of — миска',
                                'color' => 'sky',
                                'description' => '<strong>A bowl of</strong> — для їжі в мисці.',
                                'examples' => [
                                    ['en' => 'a bowl of soup', 'ua' => 'миска супу'],
                                    ['en' => 'a bowl of cereal', 'ua' => 'миска пластівців'],
                                    ['en' => 'a bowl of rice', 'ua' => 'миска рису'],
                                ],
                            ],
                            [
                                'label' => 'A spoonful of — ложка',
                                'color' => 'amber',
                                'description' => '<strong>A spoonful of</strong> — для однієї ложки.',
                                'examples' => [
                                    ['en' => 'a spoonful of sugar', 'ua' => 'ложка цукру'],
                                    ['en' => 'a spoonful of honey', 'ua' => 'ложка меду'],
                                ],
                            ],
                            [
                                'label' => 'A drop of — крапля',
                                'color' => 'rose',
                                'description' => '<strong>A drop of</strong> — для краплі рідини.',
                                'examples' => [
                                    ['en' => 'a drop of water', 'ua' => 'крапля води'],
                                    ['en' => 'a drop of milk', 'ua' => 'крапля молока'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Порівняльна таблиця партитивів',
                        'intro' => 'Основні партитиви та їх використання:',
                        'rows' => [
                            [
                                'en' => 'A piece of',
                                'ua' => 'Шматок / частина',
                                'note' => 'cake, bread, advice, information, furniture',
                            ],
                            [
                                'en' => 'A cup of',
                                'ua' => 'Чашка',
                                'note' => 'coffee, tea, hot chocolate (гарячі напої)',
                            ],
                            [
                                'en' => 'A glass of',
                                'ua' => 'Склянка',
                                'note' => 'water, juice, milk, wine (холодні напої)',
                            ],
                            [
                                'en' => 'A bottle of',
                                'ua' => 'Пляшка',
                                'note' => 'water, milk, wine, beer',
                            ],
                            [
                                'en' => 'A slice of',
                                'ua' => 'Скибка / шматок',
                                'note' => 'bread, pizza, cheese, cake',
                            ],
                            [
                                'en' => 'A loaf of',
                                'ua' => 'Буханка',
                                'note' => 'bread (loaves у множині)',
                            ],
                            [
                                'en' => 'A bar of',
                                'ua' => 'Плитка / брусок',
                                'note' => 'chocolate, soap',
                            ],
                        ],
                        'warning' => '📌 Партитиви завжди вживаються з <strong>of</strong> + незлічуваний іменник!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Забути про of після партитива.',
                                'wrong' => 'I need a piece advice.',
                                'right' => '✅ <span class="font-mono">I need a piece of advice.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна множина партитива.',
                                'wrong' => 'two loafs of bread',
                                'right' => '✅ <span class="font-mono">two loaves of bread</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний вибір партитива.',
                                'wrong' => 'a slice of milk (неправильно)',
                                'right' => '✅ <span class="font-mono">a glass of milk / a bottle of milk</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Артикль a з незлічуваними.',
                                'wrong' => 'I need a water.',
                                'right' => '✅ <span class="font-mono">I need a glass of water. / I need some water.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Короткий конспект',
                        'items' => [
                            '<strong>Партитиви</strong> — це конструкції для вимірювання незлічуваних іменників.',
                            '<strong>A piece of</strong> — універсальний партитив: <em>cake, bread, advice, information, furniture</em>.',
                            '<strong>Напої</strong>: a cup of (гарячі), a glass of (холодні), a bottle of (у пляшці).',
                            '<strong>Їжа</strong>: a slice of (скибка), a loaf of (буханка), a bar of (плитка).',
                            '<strong>Контейнери</strong>: a box of, a can of, a jar of, a bag of, a packet of, a carton of.',
                            '<strong>Виміри</strong>: a kilo of, a litre of, a pound of.',
                            'Партитиви завжди використовуються з <strong>of</strong> + незлічуваний іменник.',
                            'У множині змінюється партитив: <em>two pieces of cake, three cups of coffee</em>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '12. Практика',
                        'select_title' => 'Вправа 1. Обери правильний партитив',
                        'select_intro' => 'Обери правильну конструкцію для кожного іменника.',
                        'selects' => [
                            ['label' => 'I need ___ advice. (a piece of / a cup of)', 'prompt' => 'Який партитив?'],
                            ['label' => 'Can I have ___ water? (a slice of / a glass of)', 'prompt' => 'Який партитив?'],
                            ['label' => 'She bought ___ bread. (a loaf of / a bottle of)', 'prompt' => 'Який партитив?'],
                            ['label' => 'I want ___ chocolate. (a slice of / a bar of)', 'prompt' => 'Який партитив?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний партитив (з of).',
                        'inputs' => [
                            ['before' => 'I need ___ information.', 'after' => '→'],
                            ['before' => 'Can I have ___ coffee?', 'after' => '→'],
                            ['before' => 'She bought ___ jam.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку з партитивом.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I need a advice.',
                                'example_target' => 'I need a piece of advice. / I need some advice.',
                            ],
                            [
                                'original' => '1. Can I have a cup coffee?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. She bought two loafs of bread.',
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
                                'label' => 'Partitives — a piece of, a cup of… (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Quantifiers — Much, Many, A Lot, Few, Little',
                                'current' => false,
                            ],
                            [
                                'label' => 'Countable vs Uncountable — Злічувані / незлічувані',
                                'current' => false,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
