<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityNoNoneNeitherEitherTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'no-none-neither-either';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'No / None / Neither / Either як означники кількості',
            'subtitle_html' => '<p><strong>No, none, neither, either</strong> — це означники кількості (quantifiers), що виражають <strong>відсутність</strong> або <strong>вибір між двома</strong> варіантами. <strong>No</strong> і <strong>none</strong> означають «жодного», а <strong>neither</strong> і <strong>either</strong> використовуються, коли йдеться про два предмети.</p>',
            'subtitle_text' => 'Теоретичний огляд no, none, neither, either в англійській мові: правила вживання, різниця між ними та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'No',
                'None',
                'Neither',
                'Either',
                'Quantifiers',
                'Negative',
                'Choice',
                'Quantity',
                'Grammar',
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
                        'intro' => 'У цій темі ти вивчиш означники кількості <strong>no, none, neither, either</strong> — коли їх використовувати та яка між ними різниця.',
                        'rules' => [
                            [
                                'label' => 'No',
                                'color' => 'rose',
                                'text' => '<strong>No</strong> — жодний (перед іменником):',
                                'example' => 'No students came. / I have no money.',
                            ],
                            [
                                'label' => 'None',
                                'color' => 'purple',
                                'text' => '<strong>None</strong> — жоден (самостійно):',
                                'example' => 'None of the students came. / I have none.',
                            ],
                            [
                                'label' => 'Neither/Either',
                                'color' => 'blue',
                                'text' => '<strong>Neither/Either</strong> — жоден/будь-який (з двох):',
                                'example' => 'Neither option works. / Either way is fine.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Огляд No / None / Neither / Either',
                        'intro' => 'Ці слова виражають відсутність або вибір:',
                        'items' => [
                            ['label' => 'No', 'title' => 'Жодний (означник)', 'subtitle' => 'Вживається перед іменником: no money, no time'],
                            ['label' => 'None', 'title' => 'Жоден (займенник)', 'subtitle' => 'Вживається самостійно: none of them, I have none'],
                            ['label' => 'Neither/Either', 'title' => 'З двох варіантів', 'subtitle' => 'Neither = жоден з двох, Either = будь-який з двох'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. No — жодний (перед іменником)',
                        'sections' => [
                            [
                                'label' => 'Основне використання',
                                'color' => 'rose',
                                'description' => '<strong>No</strong> — означник, який стоїть <strong>перед іменником</strong> і означає «жодний, жодної».',
                                'examples' => [
                                    ['en' => 'I have no money.', 'ua' => 'У мене немає грошей. (жодних грошей)'],
                                    ['en' => 'There are no students in the class.', 'ua' => 'У класі немає студентів.'],
                                    ['en' => 'She has no time for this.', 'ua' => 'У неї немає часу на це.'],
                                    ['en' => 'No problem!', 'ua' => 'Без проблем! (немає проблеми)'],
                                ],
                            ],
                            [
                                'label' => 'З обома типами іменників',
                                'color' => 'amber',
                                'description' => '<strong>No</strong> працює зі <strong>злічуваними та незлічуваними</strong> іменниками.',
                                'examples' => [
                                    ['en' => 'no books (countable)', 'ua' => 'жодних книг (злічувані)'],
                                    ['en' => 'no water (uncountable)', 'ua' => 'жодної води (незлічувані)'],
                                    ['en' => 'no friends, no information', 'ua' => 'жодних друзів, жодної інформації'],
                                ],
                            ],
                            [
                                'label' => 'No замість not any',
                                'color' => 'sky',
                                'description' => '<strong>No</strong> — це більш категорична форма заперечення.',
                                'examples' => [
                                    ['en' => 'I have no money. = I don\'t have any money.', 'ua' => 'У мене немає грошей.'],
                                    ['en' => 'There are no tickets. = There aren\'t any tickets.', 'ua' => 'Немає квитків.'],
                                ],
                                'note' => '📌 <strong>No</strong> — більш емфатичне (категоричне).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. None — жоден (займенник)',
                        'sections' => [
                            [
                                'label' => 'Використання без іменника',
                                'color' => 'purple',
                                'description' => '<strong>None</strong> — займенник, який <strong>вживається самостійно</strong> (без іменника після нього).',
                                'examples' => [
                                    ['en' => 'How many do you have? — None.', 'ua' => 'Скільки в тебе є? — Жодного.'],
                                    ['en' => 'I wanted some coffee, but there was none.', 'ua' => 'Я хотів кави, але її не було.'],
                                    ['en' => 'None arrived on time.', 'ua' => 'Жоден не прийшов вчасно.'],
                                ],
                            ],
                            [
                                'label' => 'None of + іменник',
                                'color' => 'rose',
                                'description' => '<strong>None of</strong> + означений іменник (the, these, my тощо).',
                                'examples' => [
                                    ['en' => 'None of the students passed.', 'ua' => 'Жоден зі студентів не склав.'],
                                    ['en' => 'None of my friends came.', 'ua' => 'Жоден з моїх друзів не прийшов.'],
                                    ['en' => 'None of this is true.', 'ua' => 'Нічого з цього не правда.'],
                                    ['en' => 'None of us knows the answer.', 'ua' => 'Ніхто з нас не знає відповіді.'],
                                ],
                            ],
                            [
                                'label' => 'Різниця: No vs None',
                                'color' => 'sky',
                                'description' => 'Головна різниця — позиція та наявність іменника.',
                                'examples' => [
                                    ['en' => 'No students came. (перед іменником)', 'ua' => 'Жодних студентів не прийшло.'],
                                    ['en' => 'None came. (без іменника)', 'ua' => 'Жоден не прийшов.'],
                                    ['en' => 'None of the students came. (of + іменник)', 'ua' => 'Жоден зі студентів не прийшов.'],
                                ],
                                'note' => '📌 <strong>No</strong> + іменник, <strong>None</strong> — самостійно або з of.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Neither — жоден (з двох)',
                        'sections' => [
                            [
                                'label' => 'Негативний вибір з двох',
                                'color' => 'blue',
                                'description' => '<strong>Neither</strong> означає «<strong>жоден з двох</strong>» (негативне значення).',
                                'examples' => [
                                    ['en' => 'Neither option is good.', 'ua' => 'Жоден з двох варіантів не хороший.'],
                                    ['en' => 'Neither answer is correct.', 'ua' => 'Жодна з двох відповідей не правильна.'],
                                    ['en' => 'Neither team won.', 'ua' => 'Жодна з двох команд не виграла.'],
                                ],
                            ],
                            [
                                'label' => 'Neither of + іменник',
                                'color' => 'purple',
                                'description' => '<strong>Neither of</strong> + означений іменник у множині.',
                                'examples' => [
                                    ['en' => 'Neither of the books is interesting.', 'ua' => 'Жодна з двох книг не цікава.'],
                                    ['en' => 'Neither of them came.', 'ua' => 'Жоден з них (двох) не прийшов.'],
                                    ['en' => 'Neither of my parents speaks English.', 'ua' => 'Жоден з моїх батьків не говорить англійською.'],
                                ],
                            ],
                            [
                                'label' => 'Neither... nor...',
                                'color' => 'rose',
                                'description' => 'Конструкція <strong>neither... nor...</strong> — «ні... ні...».',
                                'examples' => [
                                    ['en' => 'Neither John nor Mary came.', 'ua' => 'Ні Джон, ні Марія не прийшли.'],
                                    ['en' => 'I like neither tea nor coffee.', 'ua' => 'Я не люблю ні чай, ні каву.'],
                                    ['en' => 'It\'s neither hot nor cold.', 'ua' => 'Це ні гаряче, ні холодне.'],
                                ],
                                'note' => '⚠️ <strong>Neither</strong> вже містить заперечення — не потрібно not!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Either — будь-який (з двох)',
                        'sections' => [
                            [
                                'label' => 'Позитивний вибір з двох',
                                'color' => 'emerald',
                                'description' => '<strong>Either</strong> означає «<strong>будь-який з двох</strong>» (позитивне значення).',
                                'examples' => [
                                    ['en' => 'Either option is fine.', 'ua' => 'Будь-який з двох варіантів підійде.'],
                                    ['en' => 'You can take either book.', 'ua' => 'Ти можеш взяти будь-яку з двох книг.'],
                                    ['en' => 'Either way works for me.', 'ua' => 'Будь-який з двох способів мені підходить.'],
                                ],
                            ],
                            [
                                'label' => 'Either of + іменник',
                                'color' => 'sky',
                                'description' => '<strong>Either of</strong> + означений іменник у множині.',
                                'examples' => [
                                    ['en' => 'Either of the books is good.', 'ua' => 'Будь-яка з двох книг хороша.'],
                                    ['en' => 'Either of them can help you.', 'ua' => 'Будь-хто з них (двох) може тобі допомогти.'],
                                    ['en' => 'You can choose either of these.', 'ua' => 'Ти можеш обрати будь-який з цих (двох).'],
                                ],
                            ],
                            [
                                'label' => 'Either... or...',
                                'color' => 'amber',
                                'description' => 'Конструкція <strong>either... or...</strong> — «або... або...».',
                                'examples' => [
                                    ['en' => 'You can have either tea or coffee.', 'ua' => 'Ти можеш взяти або чай, або каву.'],
                                    ['en' => 'Either John or Mary will come.', 'ua' => 'Або Джон, або Марія прийде.'],
                                    ['en' => 'It\'s either right or wrong.', 'ua' => 'Це або правильно, або неправильно.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Neither vs Either — різниця',
                        'sections' => [
                            [
                                'label' => 'Негативне vs Позитивне',
                                'color' => 'purple',
                                'description' => '<strong>Neither</strong> = жоден з двох (негативне), <strong>Either</strong> = будь-який з двох (позитивне).',
                                'examples' => [
                                    ['en' => 'Neither book is good. ❌ (жодна не хороша)', 'ua' => 'Жодна з двох книг не хороша.'],
                                    ['en' => 'Either book is good. ✓ (будь-яка хороша)', 'ua' => 'Будь-яка з двох книг хороша.'],
                                ],
                            ],
                            [
                                'label' => 'У запереченнях',
                                'color' => 'rose',
                                'description' => '<strong>Either</strong> також може використовуватися в <strong>запереченнях</strong> = «також не».',
                                'examples' => [
                                    ['en' => 'I don\'t like coffee. — I don\'t like it either.', 'ua' => 'Я не люблю каву. — Я теж її не люблю.'],
                                    ['en' => 'She can\'t come. — He can\'t come either.', 'ua' => 'Вона не може прийти. — Він теж не може.'],
                                ],
                                'note' => '📌 У запереченнях <strong>either</strong> = «теж (не)», а <strong>neither</strong> = «також не» (без not).',
                            ],
                            [
                                'label' => 'Neither vs Either у реченнях',
                                'color' => 'sky',
                                'description' => 'Порівняння використання:',
                                'examples' => [
                                    ['en' => 'Neither of them came. (жоден не прийшов)', 'ua' => 'Жоден з них не прийшов.'],
                                    ['en' => 'Either of them can come. (будь-хто може)', 'ua' => 'Будь-хто з них може прийти.'],
                                    ['en' => 'Neither option works. (жодна не працює)', 'ua' => 'Жоден варіант не працює.'],
                                    ['en' => 'Either option works. (будь-яка працює)', 'ua' => 'Будь-який варіант працює.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Узгодження з дієсловом',
                        'sections' => [
                            [
                                'label' => 'No + іменник',
                                'color' => 'amber',
                                'description' => 'Дієслово узгоджується з <strong>іменником</strong> після no.',
                                'examples' => [
                                    ['en' => 'No student is here. (singular)', 'ua' => 'Жоден студент не тут.'],
                                    ['en' => 'No students are here. (plural)', 'ua' => 'Жодні студенти не тут.'],
                                ],
                            ],
                            [
                                'label' => 'None — однина або множина',
                                'color' => 'sky',
                                'description' => '<strong>None</strong> може вживатися з <strong>однинним або множинним</strong> дієсловом.',
                                'examples' => [
                                    ['en' => 'None of the students is/are here.', 'ua' => 'Жоден зі студентів не тут.'],
                                    ['en' => 'None was found. (формально)', 'ua' => 'Нічого не знайдено.'],
                                    ['en' => 'None were found. (неформально)', 'ua' => 'Нічого не знайдено.'],
                                ],
                                'note' => '📌 В сучасній англійській обидва варіанти прийнятні.',
                            ],
                            [
                                'label' => 'Neither/Either — однина',
                                'color' => 'purple',
                                'description' => '<strong>Neither</strong> та <strong>either</strong> зазвичай з <strong>однинним</strong> дієсловом.',
                                'examples' => [
                                    ['en' => 'Neither of them is here. (формально)', 'ua' => 'Жоден з них не тут.'],
                                    ['en' => 'Either of them is fine. (формально)', 'ua' => 'Будь-хто з них підійде.'],
                                ],
                                'note' => '📌 У розмовній мові можна використовувати множину: Neither/Either of them are...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняльна таблиця',
                        'intro' => 'Основні відмінності між no, none, neither, either:',
                        'rows' => [
                            [
                                'en' => 'No',
                                'ua' => 'Жодний (означник)',
                                'note' => 'Перед іменником: no money, no students',
                            ],
                            [
                                'en' => 'None',
                                'ua' => 'Жоден (займенник)',
                                'note' => 'Самостійно: none came, none of them',
                            ],
                            [
                                'en' => 'Neither',
                                'ua' => 'Жоден з двох (негативне)',
                                'note' => 'Neither option, neither of them',
                            ],
                            [
                                'en' => 'Either',
                                'ua' => 'Будь-який з двох (позитивне)',
                                'note' => 'Either option, either of them',
                            ],
                            [
                                'en' => 'Neither... nor...',
                                'ua' => 'Ні... ні...',
                                'note' => 'Neither tea nor coffee (подвійне заперечення)',
                            ],
                            [
                                'en' => 'Either... or...',
                                'ua' => 'Або... або...',
                                'note' => 'Either tea or coffee (вибір)',
                            ],
                        ],
                        'warning' => '📌 <strong>No</strong> — перед іменником, <strong>None</strong> — самостійно, <strong>Neither/Either</strong> — з двох варіантів!',
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
                                'title' => 'None з іменником без of.',
                                'wrong' => 'None students came.',
                                'right' => '✅ <span class="font-mono">None of the students came. / No students came.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Подвійне заперечення з neither.',
                                'wrong' => 'Neither of them didn\'t come.',
                                'right' => '✅ <span class="font-mono">Neither of them came. (без didn\'t!)</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Neither/Either з більше ніж двома.',
                                'wrong' => 'Neither of the three options works.',
                                'right' => '✅ <span class="font-mono">None of the three options works.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'No з of.',
                                'wrong' => 'No of the students came.',
                                'right' => '✅ <span class="font-mono">None of the students came. / No students came.</span>',
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
                            '<strong>No</strong> — означник «жодний» перед іменником: <em>no money, no students</em>.',
                            '<strong>None</strong> — займенник «жоден», вживається самостійно або з of: <em>none came, none of them</em>.',
                            '<strong>Neither</strong> — «жоден з двох» (негативне): <em>neither option, neither of them</em>.',
                            '<strong>Either</strong> — «будь-який з двох» (позитивне): <em>either option, either of them</em>.',
                            '<strong>Neither... nor...</strong> — «ні... ні...» (подвійне заперечення).',
                            '<strong>Either... or...</strong> — «або... або...» (вибір з двох).',
                            '<strong>Neither/Either</strong> — тільки для двох варіантів. Для більше — використовуй <strong>none</strong>.',
                            '<strong>Neither</strong> вже містить заперечення — не додавай not!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильне слово',
                        'select_intro' => 'Обери no, none, neither або either для кожного речення.',
                        'selects' => [
                            ['label' => 'I have ___ money. (no / none)', 'prompt' => 'Яке слово?'],
                            ['label' => 'How many came? — ___. (No / None)', 'prompt' => 'Яке слово?'],
                            ['label' => '___ of the two books is good. (Neither / None)', 'prompt' => 'Яке слово?'],
                            ['label' => '___ option works for me. (Either / None)', 'prompt' => 'Яке слово?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши no, none, neither або either.',
                        'inputs' => [
                            ['before' => '___ of the students passed the exam.', 'after' => '→'],
                            ['before' => 'There are ___ apples left.', 'after' => '→'],
                            ['before' => '___ of them is here. (з двох)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'None students came.',
                                'example_target' => 'None of the students came. / No students came.',
                            ],
                            [
                                'original' => '1. Neither of them didn\'t come.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. I have none money.',
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
                                'label' => 'No / None / Neither / Either (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Quantifiers — Much, Many, A Lot, Few, Little',
                                'current' => false,
                            ],
                            [
                                'label' => 'Partitives — a piece of, a cup of…',
                                'current' => false,
                            ],
                            [
                                'label' => 'Zero Article — Нульовий артикль',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
