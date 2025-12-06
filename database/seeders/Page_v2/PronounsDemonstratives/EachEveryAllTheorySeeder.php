<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class EachEveryAllTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'each-every-all';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Each / Every / All — відмінності',
            'subtitle_html' => "<p><strong>Each, every, all</strong> — три слова, що означають \"кожен\" або \"всі\", але використовуються по-різному. <strong>Each</strong> — кожен окремо (індивідуально), <strong>every</strong> — кожен без винятку (як група), <strong>all</strong> — всі разом (загальність).</p>",
            'subtitle_text' => "Теоретичний огляд відмінностей між each, every та all в англійській мові: правила вживання, граматичні особливості (однина/множина), типові контексти та помилки.",
            'locale' => 'uk',
            'category' => [
                'slug' => '3',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Each',
                'Every',
                'All',
                'Quantifiers',
                'Each vs Every',
                'Every vs All',
                'Each One',
                'Every One',
                'All of',
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
                        'intro' => "У цій темі ти вивчиш <strong>відмінності між each, every та all</strong> — три способи сказати \"кожен\" або \"всі\".",
                        'rules' => [
                            [
                                'label' => 'EACH',
                                'color' => 'emerald',
                                'text' => '<strong>Each</strong> — кожен окремо (індивідуально):',
                                'example' => 'Each student has a book. (кожен свою)',
                            ],
                            [
                                'label' => 'EVERY',
                                'color' => 'blue',
                                'text' => '<strong>Every</strong> — кожен без винятку (група):',
                                'example' => 'Every student passed. (усі як група)',
                            ],
                            [
                                'label' => 'ALL',
                                'color' => 'amber',
                                'text' => '<strong>All</strong> — всі разом (загальність):',
                                'example' => 'All students are here. (всі разом)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні відмінності',
                        'intro' => "Головні характеристики each, every та all:",
                        'rows' => [
                            [
                                'en' => 'each',
                                'ua' => 'кожен окремо',
                                'note' => '+ singular noun/verb. Індивідуально.',
                            ],
                            [
                                'en' => 'every',
                                'ua' => 'кожен без винятку',
                                'note' => '+ singular noun/verb. Як група.',
                            ],
                            [
                                'en' => 'all',
                                'ua' => 'всі разом',
                                'note' => '+ plural noun/verb. Загальність.',
                            ],
                        ],
                        'warning' => "📌 Each/every + однина. All + множина.",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. EACH — кожен окремо',
                        'sections' => [
                            [
                                'label' => 'Each = індивідуально',
                                'color' => 'emerald',
                                'description' => "<strong>Each</strong> означає \"кожен окремо\" — фокус на індивідуальності, на кожному елементі окремо.",
                                'examples' => [
                                    ['en' => 'Each student has a book.', 'ua' => 'Кожен студент має книгу (свою окрему).'],
                                    ['en' => 'Each person is different.', 'ua' => 'Кожна людина різна (окремо).'],
                                    ['en' => 'I gave each child a toy.', 'ua' => 'Я дав кожній дитині іграшку (окремо кожній).'],
                                ],
                            ],
                            [
                                'label' => 'Each + singular',
                                'color' => 'sky',
                                'description' => "<strong>Each</strong> завжди з <strong>іменником і дієсловом в однині</strong>.",
                                'examples' => [
                                    ['en' => 'Each student has... (✓)', 'ua' => 'has — однина'],
                                    ['en' => 'Each student have... (✗)', 'ua' => 'ПОМИЛКА — треба has!'],
                                    ['en' => 'Each book is interesting.', 'ua' => 'is — однина'],
                                ],
                                'note' => '📌 Each завжди + singular (is, has, does).',
                            ],
                            [
                                'label' => 'Each of + plural noun',
                                'color' => 'purple',
                                'description' => "Можна використовувати <strong>each of + the/these/those + plural noun</strong>. Дієслово — в однині!",
                                'examples' => [
                                    ['en' => 'Each of the students has a book.', 'ua' => 'Кожен зі студентів має книгу.'],
                                    ['en' => 'Each of these books is good.', 'ua' => 'Кожна з цих книг гарна.'],
                                ],
                                'note' => "Each of + plural, але дієслово singular!",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. EVERY — кожен без винятку',
                        'sections' => [
                            [
                                'label' => 'Every = як група',
                                'color' => 'blue',
                                'description' => "<strong>Every</strong> означає \"кожен без винятку\" — фокус на групі в цілому, всі без виключення.",
                                'examples' => [
                                    ['en' => 'Every student passed the exam.', 'ua' => 'Кожен студент склав іспит (усі разом, без винятку).'],
                                    ['en' => 'Every day I go to work.', 'ua' => 'Кожен день я йду на роботу.'],
                                    ['en' => 'Every person needs food.', 'ua' => 'Кожна людина потребує їжі (загалом всі).'],
                                ],
                            ],
                            [
                                'label' => 'Every + singular',
                                'color' => 'sky',
                                'description' => "<strong>Every</strong> завжди з <strong>іменником і дієсловом в однині</strong>.",
                                'examples' => [
                                    ['en' => 'Every student is... (✓)', 'ua' => 'is — однина'],
                                    ['en' => 'Every student are... (✗)', 'ua' => 'ПОМИЛКА — треба is!'],
                                    ['en' => 'Every child needs love.', 'ua' => 'needs — однина'],
                                ],
                                'note' => '📌 Every завжди + singular (is, has, does).',
                            ],
                            [
                                'label' => 'Every + time expressions',
                                'color' => 'purple',
                                'description' => "<strong>Every</strong> часто використовується з виразами часу.",
                                'examples' => [
                                    ['en' => 'every day, every week, every month, every year', 'ua' => 'щодня, щотижня, щомісяця, щороку'],
                                    ['en' => 'I exercise every day.', 'ua' => 'Я займаюся спортом щодня.'],
                                    ['en' => 'We meet every Friday.', 'ua' => 'Ми зустрічаємося щоп\'ятниці.'],
                                ],
                                'note' => 'Every = щодня, щотижня (регулярність).',
                            ],
                            [
                                'label' => 'НЕ можна: Every of',
                                'color' => 'rose',
                                'description' => "НЕ існує конструкції <strong>every of</strong>! Використовуй each of або all of.",
                                'examples' => [
                                    ['en' => 'Every of the students... (✗)', 'ua' => 'ПОМИЛКА — так не кажуть!'],
                                    ['en' => 'Each of the students... (✓)', 'ua' => 'Правильно — each of'],
                                    ['en' => 'All of the students... (✓)', 'ua' => 'Правильно — all of'],
                                ],
                                'note' => '📌 Не існує "every of"! Тільки each of або all of.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. ALL — всі разом',
                        'sections' => [
                            [
                                'label' => 'All = загальність',
                                'color' => 'amber',
                                'description' => "<strong>All</strong> означає \"всі\" або \"все\" — фокус на загальності, на всіх разом.",
                                'examples' => [
                                    ['en' => 'All students are here.', 'ua' => 'Всі студенти тут (всі разом).'],
                                    ['en' => 'All people need love.', 'ua' => 'Усі люди потребують кохання.'],
                                    ['en' => 'I ate all the cookies.', 'ua' => 'Я з\'їв усі печива.'],
                                ],
                            ],
                            [
                                'label' => 'All + plural',
                                'color' => 'sky',
                                'description' => "<strong>All</strong> зазвичай з <strong>іменником і дієсловом у множині</strong>.",
                                'examples' => [
                                    ['en' => 'All students are... (✓)', 'ua' => 'are — множина'],
                                    ['en' => 'All students is... (✗)', 'ua' => 'ПОМИЛКА — треба are!'],
                                    ['en' => 'All the books are interesting.', 'ua' => 'are — множина'],
                                ],
                                'note' => '📌 All (plural) + plural verb (are, have, do).',
                            ],
                            [
                                'label' => 'All + uncountable',
                                'color' => 'purple',
                                'description' => "<strong>All</strong> також використовується з незліченними іменниками (singular verb).",
                                'examples' => [
                                    ['en' => 'All water is important.', 'ua' => 'Уся вода важлива.'],
                                    ['en' => 'All information is correct.', 'ua' => 'Уся інформація правильна.'],
                                ],
                                'note' => 'All + uncountable → singular verb.',
                            ],
                            [
                                'label' => 'All of + the/these/my',
                                'color' => 'emerald',
                                'description' => "З артиклем, вказівними або присвійними словами використовуємо <strong>all of</strong>.",
                                'examples' => [
                                    ['en' => 'All of the students are here.', 'ua' => 'Усі студенти тут.'],
                                    ['en' => 'All of these books are mine.', 'ua' => 'Усі ці книги мої.'],
                                    ['en' => 'All of my friends came.', 'ua' => 'Усі мої друзі прийшли.'],
                                ],
                                'note' => '📌 All of + the/these/those/my/your.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Each vs Every — коли що використовувати',
                        'sections' => [
                            [
                                'label' => 'Акцент: індивідуальність vs група',
                                'color' => 'emerald',
                                'description' => "<strong>Each</strong> — коли важлива індивідуальність кожного. <strong>Every</strong> — коли розглядаємо як групу.",
                                'examples' => [
                                    ['en' => 'Each student has different needs. (індивідуально)', 'ua' => 'Кожен студент має різні потреби.'],
                                    ['en' => 'Every student must pass the exam. (всі як група)', 'ua' => 'Кожен студент має скласти іспит.'],
                                ],
                            ],
                            [
                                'label' => 'Each — для невеликих груп',
                                'color' => 'blue',
                                'description' => "<strong>Each</strong> частіше для невеликих груп (2-10). <strong>Every</strong> — для будь-якого розміру.",
                                'examples' => [
                                    ['en' => 'Each of my three cats... (мала група)', 'ua' => 'Each краще для 2-3 елементів'],
                                    ['en' => 'Every citizen has rights. (велика група)', 'ua' => 'Every для великих груп'],
                                ],
                                'note' => 'Each = невеликі групи. Every = будь-який розмір.',
                            ],
                            [
                                'label' => 'Each можна після іменника',
                                'color' => 'sky',
                                'description' => "<strong>Each</strong> може стояти після іменника. <strong>Every</strong> — тільки перед.",
                                'examples' => [
                                    ['en' => 'The students each have a book. (✓)', 'ua' => 'each після іменника'],
                                    ['en' => 'The students every have a book. (✗)', 'ua' => 'every не може після!'],
                                ],
                                'note' => '📌 Each може після noun. Every — тільки перед.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Every vs All — коли що використовувати',
                        'sections' => [
                            [
                                'label' => 'Every + singular, All + plural',
                                'color' => 'amber',
                                'description' => "Головна відмінність: <strong>every + singular</strong>, <strong>all + plural</strong>.",
                                'examples' => [
                                    ['en' => 'Every student is... (singular)', 'ua' => 'Кожен студент...'],
                                    ['en' => 'All students are... (plural)', 'ua' => 'Усі студенти...'],
                                ],
                            ],
                            [
                                'label' => 'All — коли є конкретна група',
                                'color' => 'blue',
                                'description' => "<strong>All</strong> — коли говоримо про конкретну, визначену групу.",
                                'examples' => [
                                    ['en' => 'All the students in my class passed.', 'ua' => 'Усі студенти в моєму класі склали (конкретна група).'],
                                    ['en' => 'Every student must study. (загалом)', 'ua' => 'Кожен студент має вчитися (загальне правило).'],
                                ],
                                'note' => 'All = конкретна група. Every = загальне правило.',
                            ],
                            [
                                'label' => 'All може бути самостійним',
                                'color' => 'sky',
                                'description' => "<strong>All</strong> може стояти самостійно без іменника. <strong>Every</strong> — ні.",
                                'examples' => [
                                    ['en' => 'All are welcome. (✓)', 'ua' => 'Усі вітаються.'],
                                    ['en' => 'Every are welcome. (✗)', 'ua' => 'ПОМИЛКА — every потребує noun!'],
                                ],
                                'note' => '📌 All може самостійно. Every — тільки + noun.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Позиція в реченні',
                        'sections' => [
                            [
                                'label' => 'Each — гнучка позиція',
                                'color' => 'emerald',
                                'description' => "<strong>Each</strong> може стояти: перед іменником, після іменника, або наприкінці.",
                                'examples' => [
                                    ['en' => 'Each student has a book.', 'ua' => 'перед іменником'],
                                    ['en' => 'The students each have a book.', 'ua' => 'після іменника'],
                                    ['en' => 'They cost $5 each.', 'ua' => 'наприкінці (по $5 кожен)'],
                                ],
                            ],
                            [
                                'label' => 'Every — тільки перед іменником',
                                'color' => 'blue',
                                'description' => "<strong>Every</strong> завжди стоїть <strong>тільки перед іменником</strong>.",
                                'examples' => [
                                    ['en' => 'Every student has a book. (✓)', 'ua' => 'перед іменником'],
                                    ['en' => 'The students every have... (✗)', 'ua' => 'ПОМИЛКА'],
                                ],
                            ],
                            [
                                'label' => 'All — перед або з of',
                                'color' => 'amber',
                                'description' => "<strong>All</strong> може: перед іменником (без артикля) або all of + артикль/присвійний.",
                                'examples' => [
                                    ['en' => 'All students... (без артикля)', 'ua' => 'Усі студенти...'],
                                    ['en' => 'All of the students... (з артиклем)', 'ua' => 'Усі студенти...'],
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
                                'title' => "Дієслово у множині з each/every.",
                                'wrong' => 'Each student are... / Every person have...',
                                'right' => '✅ <span class="font-mono">Each student is... / Every person has...</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Використання 'every of'.",
                                'wrong' => 'Every of the students passed.',
                                'right' => '✅ <span class="font-mono">Each of the students passed. / All of the students passed.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Дієслово в однині з all + plural.",
                                'wrong' => 'All students is here.',
                                'right' => '✅ <span class="font-mono">All students are here.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Every після іменника.",
                                'wrong' => 'The students every have books.',
                                'right' => '✅ <span class="font-mono">Every student has a book. / The students each have a book.</span>',
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
                            "<strong>Each</strong> — кожен окремо (індивідуально). Each + singular.",
                            "<strong>Every</strong> — кожен без винятку (як група). Every + singular.",
                            "<strong>All</strong> — всі разом (загальність). All + plural.",
                            "<strong>Each/Every + singular</strong> noun & verb (is, has, does).",
                            "<strong>All + plural</strong> noun & verb (are, have, do).",
                            "<strong>Each of / All of</strong> + the/these/my... Але НЕ 'every of'!",
                            "<strong>Each</strong> — гнучка позиція (перед, після, наприкінці). <strong>Every</strong> — тільки перед noun.",
                            "<strong>Each</strong> — для невеликих груп. <strong>Every</strong> — для будь-якого розміру.",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильне слово',
                        'select_intro' => 'Заповни пропуски словом each, every або all.',
                        'selects' => [
                            ['label' => '___ student has a book. (each / every / all)', 'prompt' => 'Яке слово?'],
                            ['label' => '___ students are here. (each / every / all)', 'prompt' => 'Яке слово?'],
                            ['label' => 'I see him ___ day. (each / every / all)', 'prompt' => 'Яке слово?'],
                            ['label' => '___ of the books is interesting. (each / every / all)', 'prompt' => 'Яке слово?'],
                            ['label' => '___ of the students passed. (each / every / all)', 'prompt' => 'Яке слово?'],
                        ],
                        'options' => ['each', 'every', 'all'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши each, every або all.',
                        'inputs' => [
                            ['before' => '___ person is different. (кожна людина окремо)', 'after' => '→'],
                            ['before' => '___ people need love. (усі люди)', 'after' => '→'],
                            ['before' => 'I exercise ___ morning. (щоранку)', 'after' => '→'],
                            ['before' => 'They cost $10 ___. (по $10 кожен)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку з each, every або all.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Each students are here.',
                                'example_target' => 'Each student is here.',
                            ],
                            [
                                'original' => '1. Every of the books is good.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. All students is ready.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. The students every have books.',
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
                                'label' => 'Pronouns — Займенники (огляд)',
                                'current' => false,
                            ],
                            [
                                'label' => "Personal & Object Pronouns",
                                'current' => false,
                            ],
                            [
                                'label' => 'Indefinite Pronouns',
                                'current' => false,
                            ],
                            [
                                'label' => 'Reflexive Pronouns',
                                'current' => false,
                            ],
                            [
                                'label' => 'Relative Pronouns',
                                'current' => false,
                            ],
                            [
                                'label' => 'Each / Every / All — відмінності (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
