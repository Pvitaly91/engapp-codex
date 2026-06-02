<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Page_v2\Adjectives\AdjectivePageSeeder;

class AdjectivesEqualityComparisonAsAsNotAsAsTheorySeeder extends AdjectivePageSeeder
{
    protected function slug(): string
    {
        return 'equality-comparison-asas-not-asas';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function category(): array
    {
        return [
            'slug' => 'prykmetniky-ta-pryslinknyky',
            'title' => 'Прикметники та прислівники',
            'language' => 'uk',
        ];
    }

    protected function page(): array
    {
        return [
            'title' => 'Equality comparison — as…as, not as…as',
            'subtitle_html' => '<p>Конструкції <strong>as…as</strong> та <strong>not as…as</strong> використовуються для порівняння предметів, осіб чи дій за рівністю або нерівністю. Ці структури допомагають висловити, що щось <strong>таке ж</strong> або <strong>не таке ж</strong>, як інше.</p>',
            'subtitle_text' => 'Порівняння рівності as…as та нерівності not as…as: структура, використання, типові помилки та різниця з іншими формами порівняння.',
            'locale' => 'uk',
            'tags' => [
                'as...as',
                'not as...as',
                'equality comparison',
                'порівняння рівності',
                'конструкції порівняння',
                'прикметники',
                'прислівники',
                'comparative structures',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти навчишся <strong>порівнювати об\'єкти за рівністю</strong> за допомогою конструкцій <em>as…as</em> та <em>not as…as</em>, що є важливою альтернативою звичайним ступеням порівняння.',
                        'rules' => [
                            [
                                'label' => 'AS...AS',
                                'color' => 'emerald',
                                'text' => '<strong>Рівність</strong> — щось таке ж, як інше:',
                                'example' => 'He is as tall as his brother.',
                            ],
                            [
                                'label' => 'NOT AS...AS',
                                'color' => 'rose',
                                'text' => '<strong>Нерівність</strong> — щось не таке ж, як інше:',
                                'example' => 'She is not as tall as her brother.',
                            ],
                            [
                                'label' => 'STRUCTURE',
                                'color' => 'blue',
                                'text' => 'Формула: <strong>as + прикметник/прислівник + as</strong>',
                                'example' => 'as fast as, as beautiful as, as quickly as',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '1. Основна структура as…as',
                        'intro' => 'Конструкція <strong>as…as</strong> використовується для вираження рівності між двома об\'єктами:',
                        'rows' => [
                            [
                                'en' => 'Subject + be + as + прикметник + as + об\'єкт',
                                'ua' => 'Підмет + be + as + прикметник + as + об\'єкт',
                                'note' => 'Базова формула для прикметників',
                            ],
                            [
                                'en' => 'He is as tall as me.',
                                'ua' => 'Він такий же високий, як я.',
                                'note' => '✓ рівність у висоті',
                            ],
                            [
                                'en' => 'This book is as interesting as that one.',
                                'ua' => 'Ця книга така ж цікава, як та.',
                                'note' => '✓ рівність у цікавості',
                            ],
                            [
                                'en' => 'Subject + дієслово + as + прислівник + as + об\'єкт',
                                'ua' => 'Підмет + дієслово + as + прислівник + as + об\'єкт',
                                'note' => 'Базова формула для прислівників',
                            ],
                            [
                                'en' => 'She runs as fast as her sister.',
                                'ua' => 'Вона бігає так само швидко, як її сестра.',
                                'note' => '✓ рівність у швидкості',
                            ],
                            [
                                'en' => 'He works as hard as anyone.',
                                'ua' => 'Він працює так само важко, як будь-хто.',
                                'note' => '✓ рівність у старанності',
                            ],
                        ],
                        'warning' => '💡 Запам\'ятай: між двома <strong>as</strong> завжди стоїть <strong>базова форма</strong> прикметника або прислівника (не comparative).',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '2. Використання as…as для рівності',
                        'sections' => [
                            [
                                'label' => 'Рівність з прикметниками',
                                'color' => 'emerald',
                                'description' => 'Використовуй <strong>as…as</strong> для вираження, що дві речі <strong>однакові</strong> за певною характеристикою:',
                                'examples' => [
                                    ['en' => 'My car is as expensive as yours.', 'ua' => 'Моя машина така ж дорога, як твоя.'],
                                    ['en' => 'This task is as difficult as the previous one.', 'ua' => 'Це завдання таке ж складне, як попереднє.'],
                                    ['en' => 'He is as smart as his father.', 'ua' => 'Він такий же розумний, як його батько.'],
                                ],
                            ],
                            [
                                'label' => 'Рівність з прислівниками',
                                'color' => 'blue',
                                'description' => 'Використовуй <strong>as…as</strong> з прислівниками для порівняння <strong>дій</strong>:',
                                'examples' => [
                                    ['en' => 'She sings as beautifully as a professional.', 'ua' => 'Вона співає так само гарно, як професіонал.'],
                                    ['en' => 'He speaks English as fluently as a native.', 'ua' => 'Він говорить англійською так само вільно, як носій мови.'],
                                    ['en' => 'They work as efficiently as we do.', 'ua' => 'Вони працюють так само ефективно, як ми.'],
                                ],
                            ],
                            [
                                'label' => 'З кількісними виразами',
                                'color' => 'amber',
                                'description' => 'Конструкція <strong>as…as</strong> може використовуватися з <strong>much/many</strong> для кількості:',
                                'examples' => [
                                    ['en' => 'I have as much money as you.', 'ua' => 'У мене стільки ж грошей, як у тебе.'],
                                    ['en' => 'She has as many friends as her sister.', 'ua' => 'У неї стільки ж друзів, як у її сестри.'],
                                    ['en' => 'We need as much time as possible.', 'ua' => 'Нам потрібно стільки часу, скільки можливо.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '3. Структура not as…as',
                        'intro' => 'Конструкція <strong>not as…as</strong> використовується для вираження <strong>нерівності</strong> — коли щось менше або гірше за інше:',
                        'rows' => [
                            [
                                'en' => 'Subject + be + not as + прикметник + as + об\'єкт',
                                'ua' => 'Підмет + be + not as + прикметник + as + об\'єкт',
                                'note' => 'Базова формула для негативного порівняння',
                            ],
                            [
                                'en' => 'He is not as tall as me.',
                                'ua' => 'Він не такий високий, як я.',
                                'note' => '✓ він нижчий',
                            ],
                            [
                                'en' => 'This book is not as interesting as that one.',
                                'ua' => 'Ця книга не така цікава, як та.',
                                'note' => '✓ вона менш цікава',
                            ],
                            [
                                'en' => 'The weather is not as cold as yesterday.',
                                'ua' => 'Погода не така холодна, як вчора.',
                                'note' => '✓ сьогодні тепліше',
                            ],
                            [
                                'en' => 'She doesn\'t run as fast as her sister.',
                                'ua' => 'Вона бігає не так швидко, як її сестра.',
                                'note' => '✓ вона повільніша',
                            ],
                        ],
                        'warning' => '💡 <strong>Not as…as</strong> = менш... ніж. Це м\'який спосіб сказати, що щось гірше або менше.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '4. Not as…as vs менш прямі форми',
                        'sections' => [
                            [
                                'label' => 'М\'який спосіб критики',
                                'color' => 'rose',
                                'description' => '<strong>Not as…as</strong> є <strong>ввічливішим способом</strong> сказати, що щось гірше:',
                                'examples' => [
                                    ['en' => 'It\'s not as good as I expected. (м\'якше)', 'ua' => 'Це не так добре, як я очікував.'],
                                    ['en' => 'It\'s worse than I expected. (прямо)', 'ua' => 'Це гірше, ніж я очікував.'],
                                    ['en' => 'She is not as experienced as him. (ввічливо)', 'ua' => 'Вона не така досвідчена, як він.'],
                                ],
                            ],
                            [
                                'label' => 'Різниця у значенні',
                                'color' => 'blue',
                                'description' => '<strong>Not as…as</strong> фокусується на <strong>відсутності рівності</strong>, а не на прямому порівнянні:',
                                'examples' => [
                                    ['en' => 'This car is not as fast as mine. (нейтрально)', 'ua' => 'Ця машина не така швидка, як моя.'],
                                    ['en' => 'This car is slower than mine. (прямо)', 'ua' => 'Ця машина повільніша за мою.'],
                                    ['en' => 'The test wasn\'t as hard as I thought. (полегшення)', 'ua' => 'Тест був не таким складним, як я думав.'],
                                ],
                            ],
                            [
                                'label' => 'У позитивному контексті',
                                'color' => 'emerald',
                                'description' => '<strong>Not as…as</strong> може використовуватися для <strong>приємного здивування</strong>:',
                                'examples' => [
                                    ['en' => 'The exam wasn\'t as difficult as I feared.', 'ua' => 'Іспит був не таким складним, як я боявся.'],
                                    ['en' => 'The price is not as high as expected.', 'ua' => 'Ціна не така висока, як очікувалося.'],
                                    ['en' => 'Learning English isn\'t as hard as people say.', 'ua' => 'Вивчати англійську не так важко, як люди кажуть.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'right',
                    'level' => 'B1',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '5. Поширені вирази з as…as',
                        'intro' => 'Існують <strong>стійкі вирази</strong> з конструкцією as…as, які варто запам\'ятати:',
                        'items' => [
                            ['label' => 'as soon as possible', 'title' => 'якомога швидше', 'subtitle' => 'Please reply as soon as possible.'],
                            ['label' => 'as far as I know', 'title' => 'наскільки я знаю', 'subtitle' => 'As far as I know, he\'s not coming.'],
                            ['label' => 'as long as', 'title' => 'доки / за умови що', 'subtitle' => 'You can stay as long as you want.'],
                            ['label' => 'as well as', 'title' => 'так само як / а також', 'subtitle' => 'She speaks French as well as English.'],
                            ['label' => 'as much as', 'title' => 'стільки ж / настільки ж', 'subtitle' => 'I like it as much as you do.'],
                            ['label' => 'as many as', 'title' => 'стільки ж (для зліч.)', 'subtitle' => 'There were as many as 500 people.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '6. Різниця: as…as vs comparative',
                        'intro' => 'Іноді можна використати обидві форми, але з <strong>різними відтінками</strong> значення:',
                        'rows' => [
                            [
                                'en' => 'He is as tall as me.',
                                'ua' => 'Він такий же високий, як я.',
                                'note' => '= рівні за висотою',
                            ],
                            [
                                'en' => 'He is taller than me.',
                                'ua' => 'Він вищий за мене.',
                                'note' => '= він вищий',
                            ],
                            [
                                'en' => 'He is not as tall as me.',
                                'ua' => 'Він не такий високий, як я.',
                                'note' => '= він нижчий (м\'якше)',
                            ],
                            [
                                'en' => 'He is shorter than me.',
                                'ua' => 'Він нижчий за мене.',
                                'note' => '= він нижчий (прямо)',
                            ],
                            [
                                'en' => 'This is not as expensive as I thought.',
                                'ua' => 'Це не так дорого, як я думав.',
                                'note' => '= це дешевше (непряме)',
                            ],
                            [
                                'en' => 'This is cheaper than I thought.',
                                'ua' => 'Це дешевше, ніж я думав.',
                                'note' => '= це дешевше (пряме)',
                            ],
                        ],
                        'warning' => '💡 Використовуй <strong>as…as</strong> для рівності та м\'якого вираження нерівності, <strong>comparative</strong> для прямого порівняння.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'level' => 'B1',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '7. Типові помилки україномовних',
                        'sections' => [
                            [
                                'label' => 'Помилка 1: Неправильна форма прикметника',
                                'color' => 'rose',
                                'description' => 'Використання <strong>comparative форми</strong> замість базової між as…as:',
                                'examples' => [
                                    ['en' => '❌ He is as taller as me. → ✓ He is as tall as me.', 'ua' => 'ПОМИЛКА: taller замість tall'],
                                    ['en' => '❌ She is as more beautiful as her sister. → ✓ She is as beautiful as her sister.', 'ua' => 'ПОМИЛКА: more beautiful'],
                                    ['en' => '❌ It\'s as better as before. → ✓ It\'s as good as before.', 'ua' => 'ПОМИЛКА: better замість good'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 2: Пропуск другого "as"',
                                'color' => 'amber',
                                'description' => 'Забування <strong>другого as</strong> у конструкції:',
                                'examples' => [
                                    ['en' => '❌ He is as tall me. → ✓ He is as tall as me.', 'ua' => 'ПОМИЛКА: пропуск as'],
                                    ['en' => '❌ She runs as fast her brother. → ✓ She runs as fast as her brother.', 'ua' => 'ПОМИЛКА: пропуск as'],
                                    ['en' => '❌ It\'s as good before. → ✓ It\'s as good as before.', 'ua' => 'ПОМИЛКА: пропуск as'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 3: Неправильне розташування not',
                                'color' => 'sky',
                                'description' => 'Неправильна позиція <strong>not</strong> у конструкції:',
                                'examples' => [
                                    ['en' => '❌ He as not tall as me. → ✓ He is not as tall as me.', 'ua' => 'ПОМИЛКА: not після as'],
                                    ['en' => '❌ She not runs as fast as him. → ✓ She doesn\'t run as fast as him.', 'ua' => 'ПОМИЛКА: not без допоміжного'],
                                    ['en' => '❌ It\'s as not good as that. → ✓ It\'s not as good as that.', 'ua' => 'ПОМИЛКА: не там стоїть not'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 4: Плутанина than і as',
                                'color' => 'blue',
                                'description' => 'Використання <strong>than</strong> замість <strong>as</strong> або навпаки:',
                                'examples' => [
                                    ['en' => '❌ He is as tall than me. → ✓ He is as tall as me / taller than me.', 'ua' => 'ПОМИЛКА: as...than'],
                                    ['en' => '❌ She is not as fast than him. → ✓ She is not as fast as him.', 'ua' => 'ПОМИЛКА: as...than'],
                                    ['en' => '❌ It\'s better as before. → ✓ It\'s better than before.', 'ua' => 'ПОМИЛКА: comparative з as'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'level' => 'B2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '8. Розширені конструкції',
                        'sections' => [
                            [
                                'label' => 'З twice/three times',
                                'color' => 'emerald',
                                'description' => 'Можна додавати <strong>множники</strong> перед as…as:',
                                'examples' => [
                                    ['en' => 'He is twice as tall as his brother.', 'ua' => 'Він вдвічі вищий за свого брата.'],
                                    ['en' => 'This costs three times as much as that.', 'ua' => 'Це коштує втричі більше за те.'],
                                    ['en' => 'She earns half as much as him.', 'ua' => 'Вона заробляє вдвічі менше за нього.'],
                                ],
                            ],
                            [
                                'label' => 'З nearly/almost/quite',
                                'color' => 'blue',
                                'description' => 'Можна використовувати <strong>підсилювачі</strong> перед першим as:',
                                'examples' => [
                                    ['en' => 'He is nearly as tall as me.', 'ua' => 'Він майже такий же високий, як я.'],
                                    ['en' => 'She runs almost as fast as him.', 'ua' => 'Вона бігає майже так само швидко, як він.'],
                                    ['en' => 'It\'s quite as good as the original.', 'ua' => 'Це цілком так само добре, як оригінал.'],
                                ],
                            ],
                            [
                                'label' => 'У питаннях',
                                'color' => 'amber',
                                'description' => 'Конструкція <strong>as…as</strong> у питальних реченнях:',
                                'examples' => [
                                    ['en' => 'Is he as tall as you?', 'ua' => 'Він такий же високий, як ти?'],
                                    ['en' => 'Does she work as hard as her colleagues?', 'ua' => 'Вона працює так само важко, як її колеги?'],
                                    ['en' => 'Isn\'t this as expensive as that one?', 'ua' => 'Хіба це не так дорого, як те?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'right',
                    'level' => 'A2',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '9. Контрольний список для перевірки',
                        'items' => [
                            [
                                'label' => 'Перевірка 1',
                                'color' => 'emerald',
                                'title' => 'Чи є обидва "as"?',
                                'wrong' => 'He is as tall me.',
                                'right' => '→ He is as tall <strong>as</strong> me.',
                            ],
                            [
                                'label' => 'Перевірка 2',
                                'color' => 'blue',
                                'title' => 'Чи базова форма прикметника?',
                                'wrong' => 'She is as taller as him.',
                                'right' => '→ She is as <strong>tall</strong> as him.',
                            ],
                            [
                                'label' => 'Перевірка 3',
                                'color' => 'rose',
                                'title' => 'Чи правильно стоїть "not"?',
                                'wrong' => 'He as not tall as me.',
                                'right' => '→ He is <strong>not</strong> as tall as me.',
                            ],
                            [
                                'label' => 'Перевірка 4',
                                'color' => 'amber',
                                'title' => 'Чи не плутаєш as з than?',
                                'wrong' => 'She is as tall than me.',
                                'right' => '→ She is as tall <strong>as</strong> me.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'right',
                    'level' => 'B1',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            '<strong>As…as</strong> — для вираження <span class="font-mono text-xs">рівності</span>: He is as tall as me.',
                            '<strong>Not as…as</strong> — для вираження <span class="font-mono text-xs">нерівності</span>: He is not as tall as me.',
                            '<strong>Базова форма</strong> — між as…as завжди <span class="font-mono text-xs">базовий прикметник/прислівник</span> (не comparative).',
                            '<strong>Обидва as</strong> — обов\'язково потрібні <span class="font-mono text-xs">обидва as</span> у конструкції.',
                            '<strong>М\'який спосіб</strong> — not as…as є <span class="font-mono text-xs">ввічливішою формою</span> ніж comparative.',
                            '<strong>Стійкі вирази</strong> — as soon as possible, as far as I know, as long as, as well as.',
                            '<strong>Розширення</strong> — можна додавати множники (twice as), підсилювачі (nearly as), використовувати в питаннях.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'A1',
                    'seeder' => self::class,
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Прикметники та прислівники',
                        'items' => [
                            [
                                'label' => 'Degrees of Comparison',
                                'url' => '/theory/adjectives/theory-degrees-of-comparison',
                            ],
                            [
                                'label' => 'Comparative vs Superlative',
                                'url' => '/theory/adjectives/comparative-vs-superlative',
                            ],
                            [
                                'label' => 'Adjectives vs Adverbs',
                                'url' => '/theory/adjectives/adjectives-vs-adverbs',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
