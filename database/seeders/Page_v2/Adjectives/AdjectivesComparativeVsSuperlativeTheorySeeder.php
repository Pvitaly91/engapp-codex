<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Page_v2\Adjectives\AdjectivePageSeeder;

class AdjectivesComparativeVsSuperlativeTheorySeeder extends AdjectivePageSeeder
{
    protected function slug(): string
    {
        return 'comparative-vs-superlative';
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
            'title' => 'Comparative vs Superlative — вживання',
            'subtitle_html' => '<p><strong>Comparative</strong> (вищий ступінь) використовується для <strong>порівняння двох предметів</strong>, а <strong>Superlative</strong> (найвищий ступінь) — для <strong>виділення одного з групи</strong>. Правильне розуміння цієї різниці є ключовим для природного англійського мовлення.</p>',
            'subtitle_text' => 'Різниця між comparative та superlative: коли використовувати кожен, типові помилки, контекстуальні особливості вживання.',
            'locale' => 'uk',
            'tags' => [
                'Comparative vs Superlative',
                'Degrees of Comparison',
                'Comparative',
                'Superlative',
                'Comparison Usage',
                'Than vs The',
                'Two vs Three+',
                'Grammar Rules',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти навчишся <strong>правильно вибирати між comparative та superlative</strong> залежно від контексту та кількості об\'єктів порівняння.',
                        'rules' => [
                            [
                                'label' => 'COMPARATIVE',
                                'color' => 'blue',
                                'text' => '<strong>Порівняння двох</strong> — використовуй з <em>than</em>:',
                                'example' => 'This car is faster than that one.',
                            ],
                            [
                                'label' => 'SUPERLATIVE',
                                'color' => 'rose',
                                'text' => '<strong>Виділення з групи</strong> — використовуй з <em>the</em>:',
                                'example' => 'This is the fastest car in the race.',
                            ],
                            [
                                'label' => 'KEY DIFFERENCE',
                                'color' => 'emerald',
                                'text' => '<strong>Кількість об\'єктів</strong> визначає вибір:',
                                'example' => '2 objects → comparative; 3+ objects → superlative',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Основна різниця у вживанні',
                        'intro' => 'Головна відмінність між comparative та superlative полягає в <strong>кількості об\'єктів</strong>, що порівнюються:',
                        'rows' => [
                            [
                                'en' => 'Comparative',
                                'ua' => 'Вищий ступінь',
                                'note' => 'Порівняння ДВОХ предметів/осіб → -er / more ... than',
                            ],
                            [
                                'en' => 'Superlative',
                                'ua' => 'Найвищий ступінь',
                                'note' => 'Виділення ОДНОГО з групи (3+) → -est / the most',
                            ],
                            [
                                'en' => 'Tom is taller than John.',
                                'ua' => 'Том вищий за Джона.',
                                'note' => '✓ comparative — два об\'єкти (Tom і John)',
                            ],
                            [
                                'en' => 'Tom is the tallest in the class.',
                                'ua' => 'Том найвищий у класі.',
                                'note' => '✓ superlative — один з групи (весь клас)',
                            ],
                        ],
                        'warning' => '💡 Запам\'ятай: <strong>два об\'єкти = comparative</strong>, <strong>три+ об\'єкти = superlative</strong>.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Коли використовувати Comparative',
                        'sections' => [
                            [
                                'label' => 'Пряме порівняння двох',
                                'color' => 'blue',
                                'description' => 'Використовуй <strong>comparative</strong>, коли порівнюєш <strong>дві конкретні речі</strong>:',
                                'examples' => [
                                    ['en' => 'My house is bigger than your house.', 'ua' => 'Мій будинок більший за твій будинок.'],
                                    ['en' => 'She is more intelligent than her brother.', 'ua' => 'Вона розумніша за свого брата.'],
                                    ['en' => 'Today is colder than yesterday.', 'ua' => 'Сьогодні холодніше, ніж вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Порівняння з "than"',
                                'color' => 'sky',
                                'description' => 'Маркер <strong>than</strong> майже завжди вказує на потребу в comparative:',
                                'examples' => [
                                    ['en' => 'This book is more interesting than the previous one.', 'ua' => 'Ця книга цікавіша за попередню.'],
                                    ['en' => 'He runs faster than anyone I know.', 'ua' => 'Він бігає швидше за всіх, кого я знаю.'],
                                    ['en' => 'It\'s better than I expected.', 'ua' => 'Це краще, ніж я очікував.'],
                                ],
                            ],
                            [
                                'label' => 'Загальне порівняння',
                                'color' => 'emerald',
                                'description' => 'Навіть без явного "than", коли мається на увазі порівняння двох станів:',
                                'examples' => [
                                    ['en' => 'I feel better today. (ніж вчора)', 'ua' => 'Я почуваюся краще сьогодні.'],
                                    ['en' => 'Can you speak louder? (ніж зараз)', 'ua' => 'Можеш говорити голосніше?'],
                                    ['en' => 'This method is more effective. (ніж інший)', 'ua' => 'Цей метод ефективніший.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Коли використовувати Superlative',
                        'sections' => [
                            [
                                'label' => 'Виділення з групи',
                                'color' => 'rose',
                                'description' => 'Використовуй <strong>superlative</strong>, коли виділяєш <strong>один об\'єкт серед багатьох</strong>:',
                                'examples' => [
                                    ['en' => 'He is the tallest student in the class.', 'ua' => 'Він найвищий студент у класі.'],
                                    ['en' => 'This is the most expensive car in the showroom.', 'ua' => 'Це найдорожча машина в салоні.'],
                                    ['en' => 'She is the smartest person I know.', 'ua' => 'Вона найрозумніша людина, яку я знаю.'],
                                ],
                            ],
                            [
                                'label' => 'Використання "the"',
                                'color' => 'amber',
                                'description' => 'Маркер <strong>the</strong> перед прикметником майже завжди вказує на superlative:',
                                'examples' => [
                                    ['en' => 'Mount Everest is the highest mountain in the world.', 'ua' => 'Еверест — найвища гора в світі.'],
                                    ['en' => 'This is the best pizza I\'ve ever had.', 'ua' => 'Це найкраща піца, яку я коли-небудь їв.'],
                                    ['en' => 'She chose the cheapest option.', 'ua' => 'Вона обрала найдешевший варіант.'],
                                ],
                            ],
                            [
                                'label' => 'З прийменниками in/of',
                                'color' => 'sky',
                                'description' => 'Superlative часто йде з <strong>in</strong> (місце/група) або <strong>of</strong> (набір):',
                                'examples' => [
                                    ['en' => 'He is the fastest runner in the team.', 'ua' => 'Він найшвидший бігун у команді.'],
                                    ['en' => 'This is the oldest of all the buildings.', 'ua' => 'Це найстаріша з усіх будівель.'],
                                    ['en' => 'She is the youngest in her family.', 'ua' => 'Вона наймолодша у своїй родині.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Візуальне правило вибору',
                        'intro' => 'Простий алгоритм для вибору між comparative та superlative:',
                        'items' => [
                            ['label' => 'Крок 1', 'title' => 'Скільки об\'єктів?', 'subtitle' => '2 об\'єкти → Comparative; 3+ об\'єкти → Superlative'],
                            ['label' => 'Крок 2', 'title' => 'Шукай маркери', 'subtitle' => 'than = Comparative; the = Superlative'],
                            ['label' => 'Крок 3', 'title' => 'Контекст порівняння', 'subtitle' => 'Пряме порівняння → Comparative; Виділення → Superlative'],
                            ['label' => 'Крок 4', 'title' => 'Перевір прийменники', 'subtitle' => 'in/of після прикметника → ймовірно Superlative'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Порівняння конструкцій',
                        'intro' => 'Паралельні приклади для кращого розуміння різниці:',
                        'rows' => [
                            [
                                'en' => 'Paris is bigger than Lyon.',
                                'ua' => 'Париж більший за Ліон.',
                                'note' => '✓ comparative — два міста',
                            ],
                            [
                                'en' => 'Paris is the biggest city in France.',
                                'ua' => 'Париж — найбільше місто у Франції.',
                                'note' => '✓ superlative — одне з багатьох міст',
                            ],
                            [
                                'en' => 'This exam was harder than the last one.',
                                'ua' => 'Цей іспит був складніший за попередній.',
                                'note' => '✓ comparative — два іспити',
                            ],
                            [
                                'en' => 'This was the hardest exam of the year.',
                                'ua' => 'Це був найскладніший іспит року.',
                                'note' => '✓ superlative — один з усіх іспитів року',
                            ],
                            [
                                'en' => 'She is more experienced than me.',
                                'ua' => 'Вона досвідченіша за мене.',
                                'note' => '✓ comparative — дві особи',
                            ],
                            [
                                'en' => 'She is the most experienced in our team.',
                                'ua' => 'Вона найдосвідченіша в нашій команді.',
                                'note' => '✓ superlative — одна з команди',
                            ],
                        ],
                        'warning' => '💡 Зверни увагу на маркери: <strong>than</strong> для comparative, <strong>the</strong> для superlative.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Типові помилки україномовних',
                        'sections' => [
                            [
                                'label' => 'Помилка 1: Плутанина з "the"',
                                'color' => 'rose',
                                'description' => 'Використання <strong>the</strong> з comparative або його пропуск із superlative:',
                                'examples' => [
                                    ['en' => '❌ He is the taller than me. → ✓ He is taller than me.', 'ua' => 'ПОМИЛКА: the з comparative'],
                                    ['en' => '❌ She is most beautiful girl. → ✓ She is the most beautiful girl.', 'ua' => 'ПОМИЛКА: пропуск the'],
                                    ['en' => '❌ This is more better. → ✓ This is better.', 'ua' => 'ПОМИЛКА: подвійний маркер'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 2: Неправильний вибір',
                                'color' => 'amber',
                                'description' => 'Використання comparative замість superlative і навпаки:',
                                'examples' => [
                                    ['en' => '❌ She is the taller of two sisters. → ✓ She is the taller of the two.', 'ua' => 'Два об\'єкти — можна the taller'],
                                    ['en' => '❌ He is taller in the class. → ✓ He is the tallest in the class.', 'ua' => 'Група — потрібен superlative'],
                                    ['en' => '❌ This is the better than that. → ✓ This is better than that.', 'ua' => 'Порівняння — тільки comparative'],
                                ],
                            ],
                            [
                                'label' => 'Помилка 3: Пропуск "than"',
                                'color' => 'sky',
                                'description' => 'Забування <strong>than</strong> після comparative при порівнянні:',
                                'examples' => [
                                    ['en' => '❌ My car is faster your car. → ✓ My car is faster than yours.', 'ua' => 'ПОМИЛКА: пропуск than'],
                                    ['en' => '❌ She is more intelligent him. → ✓ She is more intelligent than him.', 'ua' => 'ПОМИЛКА: пропуск than'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Особливі випадки',
                        'sections' => [
                            [
                                'label' => 'Порівняння двох: може бути superlative!',
                                'color' => 'emerald',
                                'description' => 'З <strong>of the two</strong> можна використовувати superlative для підкреслення:',
                                'examples' => [
                                    ['en' => 'She is the taller of the two sisters.', 'ua' => 'Вона вища з двох сестер. (формально)'],
                                    ['en' => 'She is taller than her sister.', 'ua' => 'Вона вища за свою сестру. (звичайно)'],
                                    ['en' => 'This is the better of the two options.', 'ua' => 'Це кращий з двох варіантів.'],
                                ],
                            ],
                            [
                                'label' => 'Порівняння з собою',
                                'color' => 'blue',
                                'description' => 'Comparative використовується для порівняння <strong>різних станів однієї речі</strong>:',
                                'examples' => [
                                    ['en' => 'The weather is getting colder.', 'ua' => 'Погода стає холоднішою.'],
                                    ['en' => 'I\'m feeling better now.', 'ua' => 'Я почуваюся краще зараз.'],
                                    ['en' => 'Life is more complicated than before.', 'ua' => 'Життя складніше, ніж раніше.'],
                                ],
                            ],
                            [
                                'label' => 'Абсолютний superlative',
                                'color' => 'rose',
                                'description' => 'Іноді superlative вживається <strong>без групи</strong> для підкреслення:',
                                'examples' => [
                                    ['en' => 'You are the best! (взагалі, не в групі)', 'ua' => 'Ти найкращий!'],
                                    ['en' => 'This is the worst! (емоційно)', 'ua' => 'Це найгірше!'],
                                    ['en' => 'Have a most wonderful day! (дуже ввічливо)', 'ua' => 'Гарного дня!'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'right',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Контрольний список для перевірки',
                        'items' => [
                            [
                                'label' => 'Перевірка 1',
                                'color' => 'emerald',
                                'title' => 'Скільки об\'єктів порівнюється?',
                                'wrong' => 'Два об\'єкти?',
                                'right' => '→ Використовуй <strong>comparative</strong>',
                            ],
                            [
                                'label' => 'Перевірка 2',
                                'color' => 'blue',
                                'title' => 'Є слово "than" у реченні?',
                                'wrong' => 'Так, є "than"',
                                'right' => '→ Використовуй <strong>comparative</strong>',
                            ],
                            [
                                'label' => 'Перевірка 3',
                                'color' => 'rose',
                                'title' => 'Виділяєш одне з групи?',
                                'wrong' => 'Так, один з багатьох',
                                'right' => '→ Використовуй <strong>the + superlative</strong>',
                            ],
                            [
                                'label' => 'Перевірка 4',
                                'color' => 'amber',
                                'title' => 'Є "in/of" після прикметника?',
                                'wrong' => 'Так, є in/of',
                                'right' => '→ Швидше за все <strong>superlative</strong>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'right',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Comparative</strong> — для порівняння <span class="font-mono text-xs">двох об\'єктів</span>: bigger than, more interesting than.',
                            '<strong>Superlative</strong> — для виділення <span class="font-mono text-xs">одного з групи</span>: the biggest, the most interesting.',
                            '<strong>Маркер than</strong> — майже завжди вказує на необхідність comparative.',
                            '<strong>Маркер the</strong> — перед прикметником вказує на superlative.',
                            '<strong>Прийменники in/of</strong> — після superlative показують групу або місце.',
                            '<strong>Особливий випадок</strong> — "of the two" може йти з superlative: the taller of the two.',
                            '<strong>Уникай помилок</strong> — не використовуй the з comparative, не пропускай the з superlative.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Прикметники та прислівники',
                        'items' => [
                            [
                                'label' => 'Degrees of Comparison',
                                'url' => '/theory/adjectives/theory-degrees-of-comparison',
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
