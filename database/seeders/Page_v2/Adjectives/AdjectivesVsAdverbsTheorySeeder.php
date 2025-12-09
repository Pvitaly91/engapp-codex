<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Pages\Adjectives\AdjectivePageSeeder;

class AdjectivesVsAdverbsTheorySeeder extends AdjectivePageSeeder
{
    protected function slug(): string
    {
        return 'adjectives-vs-adverbs';
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
            'title' => 'Adjectives vs Adverbs — різниця між прикметниками та прислівниками',
            'subtitle_html' => '<p><strong>Прикметники (Adjectives)</strong> описують іменники, а <strong>прислівники (Adverbs)</strong> описують дієслова, прикметники або інші прислівники. Розуміння цієї різниці допоможе уникнути типових помилок у мовленні.</p>',
            'subtitle_text' => 'Різниця між прикметниками та прислівниками: що описують, як утворюються, позиція в реченні, типові помилки.',
            'locale' => 'uk',
            'tags' => [
                'Adjectives vs Adverbs',
                'Прикметники',
                'Прислівники',
                'Adjectives',
                'Adverbs',
                'Good vs Well',
                'Fast vs Quickly',
                '-ly Formation',
                'Manner Adverbs',
                'Describing Words',
                'Grammar Differences',
                'Common Mistakes',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти навчишся <strong>розрізняти прикметники та прислівники</strong>, правильно їх утворювати та використовувати в реченнях.',
                        'rules' => [
                            [
                                'label' => 'Adjectives',
                                'color' => 'emerald',
                                'text' => '<strong>Прикметники</strong> описують іменники:',
                                'example' => 'a quick answer, She is happy.',
                            ],
                            [
                                'label' => 'Adverbs',
                                'color' => 'blue',
                                'text' => '<strong>Прислівники</strong> описують дієслова:',
                                'example' => 'He answered quickly. She smiled happily.',
                            ],
                            [
                                'label' => 'Formation',
                                'color' => 'amber',
                                'text' => 'Більшість прислівників = <em>прикметник + -ly</em>:',
                                'example' => 'slow → slowly, careful → carefully',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основна різниця',
                        'intro' => 'Прикметники та прислівники відповідають на різні питання та описують різні частини мови:',
                        'rows' => [
                            [
                                'en' => 'Adjectives',
                                'ua' => 'Прикметники',
                                'note' => 'Описують іменники (який? яка? яке?)',
                            ],
                            [
                                'en' => 'Adverbs',
                                'ua' => 'Прислівники',
                                'note' => 'Описують дієслова (як? коли? де?)',
                            ],
                            [
                                'en' => 'a beautiful dress',
                                'ua' => 'гарна сукня',
                                'note' => 'прикметник → описує dress',
                            ],
                            [
                                'en' => 'She dances beautifully.',
                                'ua' => 'Вона танцює гарно.',
                                'note' => 'прислівник → описує dances',
                            ],
                        ],
                        'warning' => '💡 Запитай себе: що описується? Якщо іменник — використовуй прикметник, якщо дієслово — прислівник.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Утворення прислівників',
                        'sections' => [
                            [
                                'label' => 'Загальне правило: + -ly',
                                'color' => 'emerald',
                                'description' => 'Більшість прислівників утворюються додаванням <strong>-ly</strong> до прикметника:',
                                'examples' => [
                                    ['en' => 'quick → quickly', 'ua' => 'швидкий → швидко'],
                                    ['en' => 'careful → carefully', 'ua' => 'обережний → обережно'],
                                    ['en' => 'slow → slowly', 'ua' => 'повільний → повільно'],
                                ],
                            ],
                            [
                                'label' => 'Закінчення на -y → -ily',
                                'color' => 'blue',
                                'description' => 'Якщо прикметник закінчується на <strong>-y</strong>, змінюємо на <strong>-ily</strong>:',
                                'examples' => [
                                    ['en' => 'happy → happily', 'ua' => 'щасливий → щасливо'],
                                    ['en' => 'easy → easily', 'ua' => 'легкий → легко'],
                                    ['en' => 'angry → angrily', 'ua' => 'сердитий → сердито'],
                                ],
                            ],
                            [
                                'label' => 'Закінчення на -le → -ly',
                                'color' => 'sky',
                                'description' => 'Якщо прикметник закінчується на <strong>-le</strong>, просто міняємо <strong>-e</strong> на <strong>-y</strong>:',
                                'examples' => [
                                    ['en' => 'simple → simply', 'ua' => 'простий → просто'],
                                    ['en' => 'gentle → gently', 'ua' => 'ніжний → ніжно'],
                                    ['en' => 'terrible → terribly', 'ua' => 'жахливий → жахливо'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Слова з однаковою формою',
                        'sections' => [
                            [
                                'label' => 'Fast, hard, late, early',
                                'color' => 'emerald',
                                'description' => 'Деякі слова мають <strong>однакову форму</strong> для прикметника та прислівника:',
                                'examples' => [
                                    ['en' => 'a fast car / He drives fast.', 'ua' => 'швидка машина / Він їздить швидко.'],
                                    ['en' => 'hard work / She works hard.', 'ua' => 'важка робота / Вона працює важко.'],
                                    ['en' => 'an early morning / I woke up early.', 'ua' => 'ранній ранок / Я прокинувся рано.'],
                                ],
                            ],
                            [
                                'label' => 'Увага: hardly ≠ hard!',
                                'color' => 'rose',
                                'description' => '<strong>Hardly</strong> означає «ледве, майже ні», а не «важко»:',
                                'examples' => [
                                    ['en' => 'I can hardly see anything.', 'ua' => 'Я ледве щось бачу.'],
                                    ['en' => 'She hardly ever calls me.', 'ua' => 'Вона майже ніколи мені не телефонує.'],
                                ],
                            ],
                            [
                                'label' => 'Увага: lately ≠ late!',
                                'color' => 'amber',
                                'description' => '<strong>Lately</strong> означає «останнім часом», а не «пізно»:',
                                'examples' => [
                                    ['en' => 'I arrived late. (пізно)', 'ua' => 'Я прибув пізно.'],
                                    ['en' => 'Lately, I have been busy. (останнім часом)', 'ua' => 'Останнім часом я був зайнятий.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Good vs Well',
                        'intro' => 'Одна з найпоширеніших помилок — плутати <strong>good</strong> (прикметник) з <strong>well</strong> (прислівник):',
                        'rows' => [
                            [
                                'en' => 'She is a good singer.',
                                'ua' => 'Вона гарна співачка.',
                                'note' => '✓ прикметник good описує singer',
                            ],
                            [
                                'en' => 'She sings well.',
                                'ua' => 'Вона гарно співає.',
                                'note' => '✓ прислівник well описує sings',
                            ],
                            [
                                'en' => '❌ She sings good.',
                                'ua' => 'ПОМИЛКА!',
                                'note' => 'Потрібно: She sings well.',
                            ],
                            [
                                'en' => '❌ She is a well singer.',
                                'ua' => 'ПОМИЛКА!',
                                'note' => 'Потрібно: She is a good singer.',
                            ],
                        ],
                        'warning' => '💡 <strong>Well</strong> як прикметник означає «здоровий»: I feel well today (Я почуваюся добре сьогодні).',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Позиція в реченні',
                        'sections' => [
                            [
                                'label' => 'Прикметники',
                                'color' => 'emerald',
                                'description' => 'Прикметники стоять <strong>перед іменником</strong> або <strong>після to be / linking verbs</strong>:',
                                'examples' => [
                                    ['en' => 'an expensive car (перед іменником)', 'ua' => 'дорога машина'],
                                    ['en' => 'The car is expensive. (після to be)', 'ua' => 'Машина дорога.'],
                                    ['en' => 'The soup smells delicious. (після linking verb)', 'ua' => 'Суп пахне смачно.'],
                                ],
                            ],
                            [
                                'label' => 'Прислівники',
                                'color' => 'blue',
                                'description' => 'Прислівники зазвичай стоять <strong>після дієслова</strong> або <strong>в кінці речення</strong>:',
                                'examples' => [
                                    ['en' => 'He speaks clearly.', 'ua' => 'Він говорить чітко.'],
                                    ['en' => 'She answered the question correctly.', 'ua' => 'Вона відповіла на питання правильно.'],
                                    ['en' => 'They drive carefully.', 'ua' => 'Вони їздять обережно.'],
                                ],
                            ],
                            [
                                'label' => 'Прислівники частотності',
                                'color' => 'sky',
                                'description' => 'Прислівники частотності (always, usually, often) стоять <strong>перед основним дієсловом</strong>:',
                                'examples' => [
                                    ['en' => 'I always wake up early.', 'ua' => 'Я завжди прокидаюся рано.'],
                                    ['en' => 'She never drinks coffee.', 'ua' => 'Вона ніколи не п\'є каву.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '6. Типові помилки',
                        'sections' => [
                            [
                                'label' => 'Помилка 1: Real / Really',
                                'color' => 'rose',
                                'description' => '<strong>Real</strong> — прикметник (справжній), <strong>really</strong> — прислівник (дійсно, дуже):',
                                'examples' => [
                                    ['en' => '✓ This is a real diamond. (справжній діамант)', 'ua' => 'Це справжній діамант.'],
                                    ['en' => '✓ I really like this song. (дійсно подобається)', 'ua' => 'Мені дуже подобається ця пісня.'],
                                    ['en' => '❌ I real like this. → ✓ I really like this.', 'ua' => 'ПОМИЛКА'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 2: Bad / Badly',
                                'color' => 'rose',
                                'description' => '<strong>Bad</strong> — прикметник (поганий), <strong>badly</strong> — прислівник (погано):',
                                'examples' => [
                                    ['en' => '✓ I feel bad. (почуваюся погано)', 'ua' => 'Я почуваюся погано.'],
                                    ['en' => '✓ He played badly. (грав погано)', 'ua' => 'Він грав погано.'],
                                    ['en' => '❌ He played bad. → ✓ He played badly.', 'ua' => 'ПОМИЛКА'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 3: Після linking verbs',
                                'color' => 'amber',
                                'description' => 'Після <strong>linking verbs</strong> (feel, look, smell, taste, sound) використовуємо <strong>прикметник</strong>:',
                                'examples' => [
                                    ['en' => '✓ The food tastes good. (не goodly)', 'ua' => 'Їжа смакує добре.'],
                                    ['en' => '✓ You look tired. (не tiredly)', 'ua' => 'Ти виглядаєш втомленим.'],
                                    ['en' => '✓ This sounds interesting. (не interestingly)', 'ua' => 'Це звучить цікаво.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '7. Зміна значення з -ly',
                        'intro' => 'Деякі слова мають різне значення як прикметник і як прислівник з -ly:',
                        'rows' => [
                            [
                                'en' => 'hard / hardly',
                                'ua' => 'важко / ледве',
                                'note' => 'He works hard. / He hardly works.',
                            ],
                            [
                                'en' => 'late / lately',
                                'ua' => 'пізно / останнім часом',
                                'note' => 'I arrived late. / Lately, I am busy.',
                            ],
                            [
                                'en' => 'near / nearly',
                                'ua' => 'близько / майже',
                                'note' => 'Come near. / I nearly forgot.',
                            ],
                            [
                                'en' => 'high / highly',
                                'ua' => 'високо / дуже (високо оцінюється)',
                                'note' => 'Fly high. / Highly recommended.',
                            ],
                            [
                                'en' => 'short / shortly',
                                'ua' => 'коротко / незабаром',
                                'note' => 'Cut it short. / I will call shortly.',
                            ],
                        ],
                        'warning' => '💡 Завжди перевіряй значення слова з -ly у словнику — воно може відрізнятися від базового прикметника!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '8. Практичні поради',
                        'intro' => 'Як визначити, що використовувати — прикметник чи прислівник:',
                        'items' => [
                            ['label' => 'Крок 1', 'title' => 'Визнач, що описується', 'subtitle' => 'Іменник → прикметник. Дієслово → прислівник.'],
                            ['label' => 'Крок 2', 'title' => 'Постав питання', 'subtitle' => 'Який? Яка? → прикметник. Як? → прислівник.'],
                            ['label' => 'Крок 3', 'title' => 'Перевір форму', 'subtitle' => 'Більшість прислівників мають закінчення -ly.'],
                            ['label' => 'Крок 4', 'title' => 'Пам\'ятай винятки', 'subtitle' => 'fast, hard, early, late — однакові форми!'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '9. Додаткові випадки',
                        'sections' => [
                            [
                                'label' => 'Прислівники, що описують прикметники',
                                'color' => 'emerald',
                                'description' => 'Прислівники можуть описувати <strong>прикметники</strong> для підсилення або послаблення:',
                                'examples' => [
                                    ['en' => 'It is extremely hot today.', 'ua' => 'Сьогодні надзвичайно спекотно.'],
                                    ['en' => 'She is very beautiful.', 'ua' => 'Вона дуже гарна.'],
                                    ['en' => 'This is quite interesting.', 'ua' => 'Це досить цікаво.'],
                                ],
                            ],
                            [
                                'label' => 'Прислівники, що описують прислівники',
                                'color' => 'blue',
                                'description' => 'Прислівники можуть описувати інші <strong>прислівники</strong>:',
                                'examples' => [
                                    ['en' => 'He drives very carefully.', 'ua' => 'Він їздить дуже обережно.'],
                                    ['en' => 'She speaks extremely quickly.', 'ua' => 'Вона говорить надзвичайно швидко.'],
                                    ['en' => 'They work surprisingly well together.', 'ua' => 'Вони напрочуд добре працюють разом.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
