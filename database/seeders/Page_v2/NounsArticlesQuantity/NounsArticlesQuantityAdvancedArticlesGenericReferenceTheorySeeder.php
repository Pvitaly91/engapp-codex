<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Page_v2\NounsArticlesQuantity\NounsArticlesQuantityPageSeeder;

class NounsArticlesQuantityAdvancedArticlesGenericReferenceTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function slug(): string
    {
        return 'advanced-articles-generic-reference-the-rich-a-tiger-people';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Advanced articles — узагальнення, generic reference (the rich, a tiger, ∅ people)',
            'subtitle_html' => '<p><strong>Generic reference</strong> (узагальнююче значення) — це спосіб говорити про <strong>весь клас / категорію</strong> предметів або осіб. В англійській мові можна використовувати: <strong>the + прикметник</strong> (the rich), <strong>a/an + однина</strong> (a tiger), або <strong>Ø + множина</strong> (people), залежно від контексту.</p>',
            'subtitle_text' => 'Теоретичний огляд просунутого вживання артиклів в англійській мові: узагальнюючі конструкції the rich, a tiger, people та інші способи передачі generic reference.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'imennyky-artykli-ta-kilkist',
                'title' => 'Іменники, артиклі та кількість',
                'language' => 'uk',
            ],
            'tags' => [
                'Articles',
                'Advanced',
                'Generic Reference',
                'The + adjective',
                'Generalizations',
                'A/An',
                'Zero Article',
                'Grammar',
                'Theory',
                'B2',
                'C1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'B2–C1',
                        'intro' => 'У цій темі ти вивчиш <strong>просунуте вживання артиклів</strong> для узагальнень — як говорити про <strong>весь клас / категорію</strong> предметів або людей.',
                        'rules' => [
                            [
                                'label' => 'THE + adjective',
                                'color' => 'purple',
                                'text' => '<strong>The + прикметник</strong> = група людей:',
                                'example' => 'the rich, the poor, the young, the elderly',
                            ],
                            [
                                'label' => 'A/AN + singular',
                                'color' => 'blue',
                                'text' => '<strong>A/An + однина</strong> = весь вид (formal):',
                                'example' => 'A tiger is a dangerous animal.',
                            ],
                            [
                                'label' => 'Ø + plural',
                                'color' => 'emerald',
                                'text' => '<strong>Без артикля + множина</strong> = загалом:',
                                'example' => 'Tigers are dangerous. / People need love.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке generic reference?',
                        'intro' => 'Generic reference — це узагальнююче посилання на весь клас або категорію:',
                        'items' => [
                            ['label' => 'THE + adj', 'title' => 'Група людей за ознакою', 'subtitle' => 'the rich, the poor, the blind — всі багаті, всі бідні'],
                            ['label' => 'A + singular', 'title' => 'Представник виду', 'subtitle' => 'A tiger hunts at night — тигр (як вид) полює вночі'],
                            ['label' => 'Ø + plural', 'title' => 'Усі представники', 'subtitle' => 'Tigers hunt at night — тигри (загалом) полюють вночі'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. THE + прикметник = група людей',
                        'sections' => [
                            [
                                'label' => 'Основне правило',
                                'color' => 'purple',
                                'description' => '<strong>The + прикметник</strong> означає <strong>всіх людей з цією ознакою</strong> (множинне значення).',
                                'examples' => [
                                    ['en' => 'the rich = all rich people', 'ua' => 'всі багаті люди'],
                                    ['en' => 'the poor = all poor people', 'ua' => 'всі бідні люди'],
                                    ['en' => 'the young = all young people', 'ua' => 'всі молоді люди'],
                                    ['en' => 'the elderly = all elderly people', 'ua' => 'всі літні люди'],
                                ],
                            ],
                            [
                                'label' => 'У реченнях',
                                'color' => 'blue',
                                'description' => 'Приклади використання <strong>the + прикметник</strong>:',
                                'examples' => [
                                    ['en' => 'The rich get richer.', 'ua' => 'Багаті стають багатшими.'],
                                    ['en' => 'The poor need help.', 'ua' => 'Бідні потребують допомоги.'],
                                    ['en' => 'The young often rebel.', 'ua' => 'Молоді часто бунтують.'],
                                    ['en' => 'Society must protect the vulnerable.', 'ua' => 'Суспільство має захищати вразливих.'],
                                ],
                            ],
                            [
                                'label' => 'Популярні приклади',
                                'color' => 'sky',
                                'description' => 'Найпоширеніші конструкції:',
                                'examples' => [
                                    ['en' => 'the rich / the poor / the wealthy', 'ua' => 'багаті / бідні / заможні'],
                                    ['en' => 'the young / the old / the elderly', 'ua' => 'молоді / старі / літні'],
                                    ['en' => 'the blind / the deaf / the disabled', 'ua' => 'сліпі / глухі / люди з інвалідністю'],
                                    ['en' => 'the unemployed / the homeless', 'ua' => 'безробітні / бездомні'],
                                ],
                                'note' => '⚠️ Дієслово завжди у <strong>множині</strong>: The rich <strong>are</strong> getting richer.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. THE + національність',
                        'sections' => [
                            [
                                'label' => 'Закінчення -ese, -ss, -sh, -ch',
                                'color' => 'amber',
                                'description' => '<strong>The + національність</strong> на -ese, -ss, -sh, -ch = всі представники нації.',
                                'examples' => [
                                    ['en' => 'the Chinese = all Chinese people', 'ua' => 'всі китайці'],
                                    ['en' => 'the Japanese = all Japanese people', 'ua' => 'всі японці'],
                                    ['en' => 'the Swiss = all Swiss people', 'ua' => 'всі швейцарці'],
                                    ['en' => 'the British = all British people', 'ua' => 'всі британці'],
                                    ['en' => 'the French = all French people', 'ua' => 'всі французи'],
                                ],
                            ],
                            [
                                'label' => 'У реченнях',
                                'color' => 'purple',
                                'description' => 'Приклади використання:',
                                'examples' => [
                                    ['en' => 'The Japanese are known for their politeness.', 'ua' => 'Японці відомі своєю ввічливістю.'],
                                    ['en' => 'The British love tea.', 'ua' => 'Британці люблять чай.'],
                                    ['en' => 'The French value art and culture.', 'ua' => 'Французи цінують мистецтво та культуру.'],
                                ],
                            ],
                            [
                                'label' => 'Інші національності',
                                'color' => 'sky',
                                'description' => 'Національності з іншими закінченнями — <strong>без the</strong>, у множині:',
                                'examples' => [
                                    ['en' => 'Ukrainians (not the Ukrainians)', 'ua' => 'українці'],
                                    ['en' => 'Americans, Germans, Italians', 'ua' => 'американці, німці, італійці'],
                                ],
                                'note' => '📌 <strong>The + -ese/-ss/-sh/-ch</strong>, інші — просто множина.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. A/AN + однина = весь вид (formal)',
                        'sections' => [
                            [
                                'label' => 'Представник виду',
                                'color' => 'blue',
                                'description' => '<strong>A/An + однина</strong> може означати <strong>весь вид</strong> (більш формально).',
                                'examples' => [
                                    ['en' => 'A tiger is a dangerous animal.', 'ua' => 'Тигр (як вид) — небезпечна тварина.'],
                                    ['en' => 'A computer can store data.', 'ua' => 'Компʼютер (як пристрій) може зберігати дані.'],
                                    ['en' => 'A dog is loyal to its owner.', 'ua' => 'Собака (загалом) вірна господарю.'],
                                ],
                            ],
                            [
                                'label' => 'Коли використовувати',
                                'color' => 'purple',
                                'description' => 'Використовується у <strong>визначеннях, наукових текстах, формальних описах</strong>.',
                                'examples' => [
                                    ['en' => 'A whale is a mammal, not a fish.', 'ua' => 'Кит — ссавець, а не риба.'],
                                    ['en' => 'A triangle has three sides.', 'ua' => 'Трикутник має три сторони.'],
                                    ['en' => 'An atom consists of protons, neutrons, and electrons.', 'ua' => 'Атом складається з протонів, нейтронів та електронів.'],
                                ],
                            ],
                            [
                                'label' => 'Порівняння з множиною',
                                'color' => 'sky',
                                'description' => 'Різниця між a/an та множиною:',
                                'examples' => [
                                    ['en' => 'A tiger is dangerous. (formal)', 'ua' => 'Тигр небезпечний. (формально)'],
                                    ['en' => 'Tigers are dangerous. (neutral)', 'ua' => 'Тигри небезпечні. (нейтрально)'],
                                ],
                                'note' => '📌 Обидва варіанти правильні, але <strong>a/an</strong> — формальніше.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Ø + множина = загалом (neutral)',
                        'sections' => [
                            [
                                'label' => 'Загальне значення',
                                'color' => 'emerald',
                                'description' => '<strong>Без артикля + множина</strong> — найпоширеніший спосіб узагальнення.',
                                'examples' => [
                                    ['en' => 'Tigers are dangerous.', 'ua' => 'Тигри небезпечні. (усі тигри загалом)'],
                                    ['en' => 'People need love.', 'ua' => 'Люди потребують любові.'],
                                    ['en' => 'Dogs are loyal animals.', 'ua' => 'Собаки — вірні тварини.'],
                                    ['en' => 'Books are sources of knowledge.', 'ua' => 'Книги — джерела знань.'],
                                ],
                            ],
                            [
                                'label' => 'Найпоширеніший варіант',
                                'color' => 'sky',
                                'description' => 'У розмовній та нейтральній мові — найчастіше множина без артикля.',
                                'examples' => [
                                    ['en' => 'Computers make life easier.', 'ua' => 'Компʼютери полегшують життя.'],
                                    ['en' => 'Children need education.', 'ua' => 'Діти потребують освіти.'],
                                    ['en' => 'Scientists study nature.', 'ua' => 'Вчені вивчають природу.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. THE + однина = весь вид (rare)',
                        'sections' => [
                            [
                                'label' => 'Унікальні винятки',
                                'color' => 'rose',
                                'description' => '<strong>The + однина</strong> рідко, але може означати весь вид (дуже формально).',
                                'examples' => [
                                    ['en' => 'The tiger is a dangerous animal.', 'ua' => 'Тигр (як вид) — небезпечна тварина.'],
                                    ['en' => 'The computer has changed our lives.', 'ua' => 'Компʼютер (як винахід) змінив наше життя.'],
                                ],
                            ],
                            [
                                'label' => 'Коли використовується',
                                'color' => 'amber',
                                'description' => 'Тільки у <strong>дуже формальних</strong> контекстах або історичних описах.',
                                'examples' => [
                                    ['en' => 'The telephone revolutionized communication.', 'ua' => 'Телефон революціонізував комунікацію.'],
                                    ['en' => 'The dinosaur became extinct millions of years ago.', 'ua' => 'Динозавр вимер мільйони років тому.'],
                                ],
                                'note' => '📌 Рідкісний варіант — краще використовувати множину або a/an.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Інші узагальнюючі конструкції',
                        'sections' => [
                            [
                                'label' => 'THE + superlative',
                                'color' => 'purple',
                                'description' => '<strong>The + найвищий ступінь</strong> для узагальнень.',
                                'examples' => [
                                    ['en' => 'The best things in life are free.', 'ua' => 'Найкращі речі в житті — безкоштовні.'],
                                    ['en' => 'The most important thing is health.', 'ua' => 'Найважливіша річ — здоровʼя.'],
                                ],
                            ],
                            [
                                'label' => 'THE + only/main/same',
                                'color' => 'blue',
                                'description' => 'З певними прикметниками завжди <strong>the</strong>.',
                                'examples' => [
                                    ['en' => 'The only solution is to try harder.', 'ua' => 'Єдиний вихід — старатися більше.'],
                                    ['en' => 'The main problem is lack of time.', 'ua' => 'Головна проблема — нестача часу.'],
                                    ['en' => 'We have the same opinion.', 'ua' => 'У нас однакова думка.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняльна таблиця узагальнень',
                        'intro' => 'Різні способи передачі generic reference:',
                        'rows' => [
                            [
                                'en' => 'THE + adjective',
                                'ua' => 'Група людей за ознакою',
                                'note' => 'the rich, the poor, the young (plural meaning)',
                            ],
                            [
                                'en' => 'THE + nationality (-ese/-sh/-ch)',
                                'ua' => 'Всі представники нації',
                                'note' => 'the Chinese, the British, the French',
                            ],
                            [
                                'en' => 'A/AN + singular',
                                'ua' => 'Весь вид (formal)',
                                'note' => 'A tiger is dangerous. (formal definition)',
                            ],
                            [
                                'en' => 'Ø + plural',
                                'ua' => 'Весь вид (neutral)',
                                'note' => 'Tigers are dangerous. (most common)',
                            ],
                            [
                                'en' => 'THE + singular',
                                'ua' => 'Весь вид (very formal)',
                                'note' => 'The tiger is dangerous. (rare, very formal)',
                            ],
                        ],
                        'warning' => '📌 Найпоширеніший варіант — <strong>множина без артикля</strong>. <strong>A/an + однина</strong> — формально. <strong>The + однина</strong> — рідко.',
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
                                'title' => 'Однинне дієслово з the + adjective.',
                                'wrong' => 'The rich is getting richer.',
                                'right' => '✅ <span class="font-mono">The rich are getting richer. (plural!)</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'The перед звичайною національністю.',
                                'wrong' => 'The Ukrainians are friendly.',
                                'right' => '✅ <span class="font-mono">Ukrainians are friendly. (без the)</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Артикль the у загальному твердженні.',
                                'wrong' => 'The tigers are dangerous. (усі тигри взагалі)',
                                'right' => '✅ <span class="font-mono">Tigers are dangerous. (без the)</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Неправильний вибір форми.',
                                'wrong' => 'Tiger is dangerous. (без артикля в однині)',
                                'right' => '✅ <span class="font-mono">A tiger is dangerous. / Tigers are dangerous.</span>',
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
                            '<strong>The + прикметник</strong> = всі люди з цією ознакою: <em>the rich, the poor, the young</em> (дієслово у множині!).',
                            '<strong>The + національність</strong> (-ese, -sh, -ch) = всі представники нації: <em>the Chinese, the British, the French</em>.',
                            '<strong>A/An + однина</strong> = весь вид у формальному стилі: <em>A tiger is dangerous</em>.',
                            '<strong>Ø + множина</strong> = весь вид, найпоширеніший варіант: <em>Tigers are dangerous, People need love</em>.',
                            '<strong>The + однина</strong> = весь вид у дуже формальному стилі (рідко): <em>The computer has changed our lives</em>.',
                            'У загальних твердженнях <strong>не використовуй the</strong> перед множиною: Tigers (not the tigers).',
                            'The rich <strong>are</strong> (plural verb), not is!',
                            'Українці, німці, італійці — <strong>без the</strong> (Ukrainians, not the Ukrainians).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери правильну форму для узагальнення.',
                        'selects' => [
                            ['label' => '___ need education. (The children / Children)', 'prompt' => 'Який варіант?'],
                            ['label' => '___ are getting richer. (The rich / Rich)', 'prompt' => 'Який варіант?'],
                            ['label' => '___ is a dangerous animal. (Tiger / A tiger)', 'prompt' => 'Який варіант?'],
                            ['label' => '___ are known for politeness. (Japanese / The Japanese)', 'prompt' => 'Який варіант?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильну форму (the, a, або Ø).',
                        'inputs' => [
                            ['before' => '___ poor need help.', 'after' => '→'],
                            ['before' => '___ computer has changed our lives.', 'after' => '→'],
                            ['before' => '___ dogs are loyal animals.', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'The rich is getting richer.',
                                'example_target' => 'The rich are getting richer.',
                            ],
                            [
                                'original' => '1. The tigers are dangerous animals. (загальне твердження)',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. The Ukrainians are friendly people.',
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
                                'label' => 'Advanced articles — узагальнення (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Articles with geographical names',
                                'current' => false,
                            ],
                            [
                                'label' => 'Articles A / An / The — Артиклі',
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
