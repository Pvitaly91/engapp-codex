<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Page_v2\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsDemonstrativesPossessiveAdjectivesVsPronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'possessive-adjectives-vs-pronouns-my-mine-your-yours';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Possessive Adjectives vs Pronouns — my / mine, your / yours',
            'subtitle_html' => "<p><strong>Possessive forms</strong> (присвійні форми) показують приналежність — чиє це. В англійській є дві форми: <strong>possessive adjectives</strong> (присвійні прикметники) — my, your, his, her, its, our, their, що стоять перед іменником, і <strong>possessive pronouns</strong> (присвійні займенники) — mine, yours, his, hers, ours, theirs, що заміняють іменник.</p>",
            'subtitle_text' => "Теоретичний огляд присвійних прикметників та присвійних займенників англійської мови: my/mine, your/yours, his/his, her/hers, our/ours, their/theirs — правила вживання та відмінності.",
            'locale' => 'uk',
            'category' => [
                'slug' => 'zaimennyky-ta-vkazivni-slova',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Possessive Adjectives',
                'Possessive Pronouns',
                'My',
                'Mine',
                'Your',
                'Yours',
                'His',
                'Her',
                'Hers',
                'Its',
                'Our',
                'Ours',
                'Their',
                'Theirs',
                'Possession',
                'Pronouns',
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
                        'intro' => "У цій темі ти вивчиш <strong>присвійні прикметники та присвійні займенники</strong> — дві форми для вираження приналежності.",
                        'rules' => [
                            [
                                'label' => 'Adjectives',
                                'color' => 'emerald',
                                'text' => '<strong>Присвійні прикметники</strong> — перед іменником:',
                                'example' => 'This is my book. Her name is Maria.',
                            ],
                            [
                                'label' => 'Pronouns',
                                'color' => 'blue',
                                'text' => '<strong>Присвійні займенники</strong> — замість іменника:',
                                'example' => 'This book is mine. The blue bag is hers.',
                            ],
                            [
                                'label' => 'Key Rule',
                                'color' => 'amber',
                                'text' => '<strong>Правило:</strong> adjective + noun, pronoun замість noun',
                                'example' => 'my car (adj+noun) → The car is mine (pronoun)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Дві присвійні форми',
                        'intro' => "Для вираження приналежності (чиє це?) англійська мова має дві форми:",
                        'items' => [
                            ['label' => 'Adjectives', 'title' => 'Присвійні прикметники', 'subtitle' => 'my, your, his, her, its, our, their — стоять ПЕРЕД іменником'],
                            ['label' => 'Pronouns', 'title' => 'Присвійні займенники', 'subtitle' => 'mine, yours, his, hers, ours, theirs — стоять ЗАМІСТЬ іменника'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Повна таблиця присвійних форм',
                        'intro' => "Порівняння всіх присвійних прикметників і присвійних займенників:",
                        'rows' => [
                            [
                                'en' => 'my',
                                'ua' => 'мій/моя/моє (+ noun)',
                                'note' => 'mine — мій/моя/моє (самостійно)',
                            ],
                            [
                                'en' => 'your',
                                'ua' => 'твій/ваш (+ noun)',
                                'note' => 'yours — твій/ваш (самостійно)',
                            ],
                            [
                                'en' => 'his',
                                'ua' => 'його (+ noun)',
                                'note' => 'his — його (самостійно)',
                            ],
                            [
                                'en' => 'her',
                                'ua' => 'її (+ noun)',
                                'note' => 'hers — її (самостійно)',
                            ],
                            [
                                'en' => 'its',
                                'ua' => 'його/її для тварин/предметів',
                                'note' => 'НЕ має форми займенника!',
                            ],
                            [
                                'en' => 'our',
                                'ua' => 'наш (+ noun)',
                                'note' => 'ours — наш (самостійно)',
                            ],
                            [
                                'en' => 'their',
                                'ua' => 'їхній (+ noun)',
                                'note' => 'theirs — їхній (самостійно)',
                            ],
                        ],
                        'warning' => "📌 His має однакову форму для adjective і pronoun. Its НЕ має форми pronoun!",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Possessive Adjectives — Присвійні прикметники',
                        'sections' => [
                            [
                                'label' => 'Що таке присвійні прикметники?',
                                'color' => 'emerald',
                                'description' => "Присвійні прикметники (possessive adjectives) <strong>завжди стоять перед іменником</strong> і показують, кому щось належить. Вони НЕ можуть стояти самостійно.",
                                'examples' => [
                                    ['en' => 'This is my book.', 'ua' => 'Це моя книга.'],
                                    ['en' => 'Your car is new.', 'ua' => 'Твоя машина нова.'],
                                    ['en' => 'His name is John.', 'ua' => "Його ім'я Джон."],
                                    ['en' => 'Her sister lives here.', 'ua' => 'Її сестра живе тут.'],
                                    ['en' => 'The dog wagged its tail.', 'ua' => 'Собака махала хвостом.'],
                                    ['en' => 'Our house is big.', 'ua' => 'Наш будинок великий.'],
                                    ['en' => 'Their children are at school.', 'ua' => 'Їхні діти в школі.'],
                                ],
                            ],
                            [
                                'label' => 'Завжди перед іменником',
                                'color' => 'sky',
                                'description' => "Присвійні прикметники <strong>ніколи не стоять самостійно</strong> — після них завжди іде іменник.",
                                'examples' => [
                                    ['en' => 'my friend (✓)', 'ua' => 'мій друг'],
                                    ['en' => 'This is my. (✗)', 'ua' => 'ПОМИЛКА — немає іменника!'],
                                    ['en' => 'her idea (✓)', 'ua' => 'її ідея'],
                                    ['en' => 'The idea is her. (✗)', 'ua' => 'ПОМИЛКА — потрібен займенник hers!'],
                                ],
                                'note' => '📌 Adjective завжди + noun. Без іменника — не працює!',
                            ],
                            [
                                'label' => 'My, your, his, her, its, our, their',
                                'color' => 'purple',
                                'description' => "Всі сім присвійних прикметників використовуються однаково — перед іменником.",
                                'examples' => [
                                    ['en' => 'my pen, your bag, his phone', 'ua' => 'моя ручка, твоя сумка, його телефон'],
                                    ['en' => 'her laptop, its color, our plan', 'ua' => 'її ноутбук, його колір, наш план'],
                                    ['en' => 'their house', 'ua' => 'їхній будинок'],
                                ],
                            ],
                            [
                                'label' => "Its vs It's — не плутай!",
                                'color' => 'amber',
                                'description' => "<strong>Its</strong> (без апострофа) — присвійний прикметник. <strong>It's</strong> (з апострофом) — скорочення від it is.",
                                'examples' => [
                                    ['en' => 'The cat ate its food. (possessive)', 'ua' => 'Кіт з\'їв свою їжу.'],
                                    ['en' => "It's raining. (it is)", 'ua' => 'Йде дощ.'],
                                    ['en' => 'The city and its people. (possessive)', 'ua' => 'Місто та його люди.'],
                                    ['en' => "It's a beautiful day. (it is)", 'ua' => 'Гарний день.'],
                                ],
                                'note' => "📌 Its = присвійний (чиє). It's = it is (це є).",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Possessive Pronouns — Присвійні займенники',
                        'sections' => [
                            [
                                'label' => 'Що таке присвійні займенники?',
                                'color' => 'blue',
                                'description' => "Присвійні займенники (possessive pronouns) <strong>заміняють іменник</strong> і показують приналежність. Вони стоять самостійно, БЕЗ іменника після них.",
                                'examples' => [
                                    ['en' => 'This book is mine.', 'ua' => 'Ця книга моя.'],
                                    ['en' => 'The car is yours.', 'ua' => 'Машина твоя.'],
                                    ['en' => 'That pen is his.', 'ua' => 'Та ручка його.'],
                                    ['en' => 'The blue bag is hers.', 'ua' => 'Синя сумка її.'],
                                    ['en' => 'The house is ours.', 'ua' => 'Будинок наш.'],
                                    ['en' => 'Those keys are theirs.', 'ua' => 'Ті ключі їхні.'],
                                ],
                            ],
                            [
                                'label' => 'Стоять самостійно',
                                'color' => 'sky',
                                'description' => "Присвійні займенники <strong>не потребують іменника</strong> після них — вони самі є заміною іменника.",
                                'examples' => [
                                    ['en' => 'This is mine. (✓)', 'ua' => 'Це моє.'],
                                    ['en' => 'This is mine book. (✗)', 'ua' => 'ПОМИЛКА — зайвий noun!'],
                                    ['en' => "That's hers. (✓)", 'ua' => 'Те її.'],
                                    ['en' => "That's hers bag. (✗)", 'ua' => 'ПОМИЛКА — не можна з іменником!'],
                                ],
                                'note' => '📌 Pronoun замість noun. Ніколи не додавай іменник після!',
                            ],
                            [
                                'label' => 'Mine, yours, his, hers, ours, theirs',
                                'color' => 'purple',
                                'description' => "Шість присвійних займенників (its не має такої форми!).",
                                'examples' => [
                                    ['en' => 'Is this pen yours? — Yes, it\'s mine.', 'ua' => 'Це твоя ручка? — Так, моя.'],
                                    ['en' => 'His car is red, hers is blue.', 'ua' => 'Його машина червона, її — синя.'],
                                    ['en' => 'Our office is big, theirs is small.', 'ua' => 'Наш офіс великий, їхній — маленький.'],
                                ],
                            ],
                            [
                                'label' => 'His — особливий випадок',
                                'color' => 'amber',
                                'description' => "<strong>His</strong> має однакову форму як присвійний прикметник і як присвійний займенник.",
                                'examples' => [
                                    ['en' => 'his book (adjective)', 'ua' => 'його книга'],
                                    ['en' => 'This book is his. (pronoun)', 'ua' => 'Ця книга його.'],
                                ],
                                'note' => '📌 His — єдине слово, що працює обома способами!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Як вибрати: Adjective чи Pronoun?',
                        'sections' => [
                            [
                                'label' => 'Правило 1: Є іменник після?',
                                'color' => 'emerald',
                                'description' => "Якщо після присвійної форми йде <strong>іменник</strong> — використовуй <strong>adjective</strong>.",
                                'examples' => [
                                    ['en' => 'This is my car.', 'ua' => 'Це моя машина. (є noun "car")'],
                                    ['en' => 'Her sister is tall.', 'ua' => 'Її сестра висока. (є noun "sister")'],
                                    ['en' => 'Their house is beautiful.', 'ua' => 'Їхній будинок гарний. (є noun "house")'],
                                ],
                            ],
                            [
                                'label' => 'Правило 2: Іменник уже згаданий?',
                                'color' => 'blue',
                                'description' => "Якщо іменник вже відомий і ти не хочеш його повторювати — використовуй <strong>pronoun</strong>.",
                                'examples' => [
                                    ['en' => 'My car is red. Yours is blue.', 'ua' => 'Моя машина червона. Твоя синя. (car не повторюємо)'],
                                    ['en' => 'Her bag is big. Mine is small.', 'ua' => 'Її сумка велика. Моя маленька. (bag не повторюємо)'],
                                    ['en' => 'Their office is new. Ours is old.', 'ua' => 'Їхній офіс новий. Наш старий.'],
                                ],
                            ],
                            [
                                'label' => 'Правило 3: Питання "Whose?"',
                                'color' => 'purple',
                                'description' => "У відповіді на питання <strong>Whose?</strong> (Чий?) часто використовуємо pronoun.",
                                'examples' => [
                                    ['en' => 'Whose book is this? — It\'s mine.', 'ua' => 'Чия це книга? — Моя.'],
                                    ['en' => 'Whose keys are these? — They\'re hers.', 'ua' => 'Чиї це ключі? — Її.'],
                                    ['en' => 'Whose car is that? — It\'s theirs.', 'ua' => 'Чия та машина? — Їхня.'],
                                ],
                                'note' => '📌 У відповіді: mine/yours/his/hers/ours/theirs (pronouns).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Порівняння форм',
                        'sections' => [
                            [
                                'label' => 'My vs Mine',
                                'color' => 'emerald',
                                'description' => "<strong>My</strong> + noun. <strong>Mine</strong> замість noun.",
                                'examples' => [
                                    ['en' => 'This is my book.', 'ua' => 'Це моя книга. (my + book)'],
                                    ['en' => 'This book is mine.', 'ua' => 'Ця книга моя. (mine замість book)'],
                                    ['en' => 'My phone is new.', 'ua' => 'Мій телефон новий.'],
                                    ['en' => 'The new phone is mine.', 'ua' => 'Новий телефон мій.'],
                                ],
                            ],
                            [
                                'label' => 'Your vs Yours',
                                'color' => 'sky',
                                'description' => "<strong>Your</strong> + noun. <strong>Yours</strong> замість noun.",
                                'examples' => [
                                    ['en' => 'Is this your car?', 'ua' => 'Це твоя машина? (your + car)'],
                                    ['en' => 'Is this car yours?', 'ua' => 'Ця машина твоя? (yours замість car)'],
                                    ['en' => 'Your idea is great.', 'ua' => 'Твоя ідея чудова.'],
                                    ['en' => 'The idea is yours.', 'ua' => 'Ідея твоя.'],
                                ],
                            ],
                            [
                                'label' => 'Her vs Hers',
                                'color' => 'purple',
                                'description' => "<strong>Her</strong> + noun. <strong>Hers</strong> замість noun.",
                                'examples' => [
                                    ['en' => 'Her bag is blue.', 'ua' => 'Її сумка синя. (her + bag)'],
                                    ['en' => 'The blue bag is hers.', 'ua' => 'Синя сумка її. (hers замість bag)'],
                                    ['en' => 'Her answer is correct.', 'ua' => 'Її відповідь правильна.'],
                                    ['en' => 'The correct answer is hers.', 'ua' => 'Правильна відповідь її.'],
                                ],
                            ],
                            [
                                'label' => 'Our vs Ours, Their vs Theirs',
                                'color' => 'amber',
                                'description' => "Той самий принцип для our/ours і their/theirs.",
                                'examples' => [
                                    ['en' => 'Our house vs The house is ours', 'ua' => 'Наш будинок vs Будинок наш'],
                                    ['en' => 'Their dog vs The dog is theirs', 'ua' => 'Їхня собака vs Собака їхня'],
                                ],
                                'note' => '📌 Adjective + noun. Pronoun замість noun.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => "Pronoun + noun (зайвий іменник).",
                                'wrong' => 'This is mine book. / That\'s hers car.',
                                'right' => '✅ <span class="font-mono">This is my book. / That\'s her car.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Adjective без іменника.",
                                'wrong' => 'This book is my. / The car is her.',
                                'right' => '✅ <span class="font-mono">This book is mine. / The car is hers.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Плутанина its та it's.",
                                'wrong' => "Its raining. / The dog wagged it's tail.",
                                'right' => "✅ <span class=\"font-mono\">It's raining. / The dog wagged its tail.</span>",
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Неправильна форма (mines, hers book).",
                                'wrong' => 'This is mines. / That\'s hers bag.',
                                'right' => '✅ <span class="font-mono">This is mine. / That\'s her bag.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            "<strong>Possessive adjectives</strong> — перед іменником: <em>my, your, his, her, its, our, their</em>.",
                            "<strong>Possessive pronouns</strong> — замість іменника: <em>mine, yours, his, hers, ours, theirs</em>.",
                            "Є іменник після? → Adjective (my book). Немає іменника? → Pronoun (mine).",
                            "<strong>His</strong> — єдине слово з однаковою формою: his book (adj), it's his (pronoun).",
                            "<strong>Its</strong> (присвійний прикметник) НЕ має форми pronoun. <strong>It's</strong> = it is.",
                            "У відповіді на Whose? використовуємо pronouns: mine, yours, his, hers, ours, theirs.",
                            "Pronoun НЕ може стояти з іменником: mine book (✗), my book (✓).",
                            "Adjective НЕ може стояти самостійно: This is my (✗), This is mine (✓).",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Практика',
                        'select_title' => 'Вправа 1. Обери правильну форму',
                        'select_intro' => 'Заповни пропуски правильною присвійною формою (adjective або pronoun).',
                        'selects' => [
                            ['label' => 'This is ___ book. (my / mine)', 'prompt' => 'Яка форма?'],
                            ['label' => 'This book is ___. (my / mine)', 'prompt' => 'Яка форма?'],
                            ['label' => '___ car is red. (Her / Hers)', 'prompt' => 'Яка форма?'],
                            ['label' => 'The red car is ___. (her / hers)', 'prompt' => 'Яка форма?'],
                            ['label' => 'Is this pen ___? (your / yours)', 'prompt' => 'Яка форма?'],
                        ],
                        'options' => ['my', 'mine', 'your', 'yours', 'his', 'her', 'hers', 'our', 'ours', 'their', 'theirs'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильну присвійну форму.',
                        'inputs' => [
                            ['before' => 'This is ___ phone. (мій телефон)', 'after' => '→'],
                            ['before' => 'This phone is ___. (мій)', 'after' => '→'],
                            ['before' => '___ house is big. (наш будинок)', 'after' => '→'],
                            ['before' => 'The house is ___. (наш)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку з присвійними формами.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'This is mines book.',
                                'example_target' => 'This is my book.',
                            ],
                            [
                                'original' => '1. This book is my.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. That is hers car.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => "3. Its a beautiful day.",
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
                                'label' => "Personal & Object Pronouns — Особові й об'єктні",
                                'current' => false,
                            ],
                            [
                                'label' => 'Possessive Adjectives vs Pronouns — my / mine, your / yours (поточна)',
                                'current' => true,
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
