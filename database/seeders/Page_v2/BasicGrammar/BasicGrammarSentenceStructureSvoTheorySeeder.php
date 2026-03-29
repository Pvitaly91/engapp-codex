<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class BasicGrammarSentenceStructureSvoTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'sentence-structure-svo';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Sentence Structure — Будова простого речення (S–V–O)',
            'subtitle_html' => '<p><strong>Sentence structure</strong> (структура речення) — це основа англійської мови. Англійське просте речення будується за фіксованою схемою <strong>S–V–O</strong> (Subject–Verb–Object), на відміну від української, де порядок слів вільніший.</p>',
            'subtitle_text' => 'Теоретичний огляд будови простого англійського речення: структура S–V–O, елементи речення та їхнє розташування.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Sentence Structure',
                'Basic Grammar',
                'SVO',
                'Subject',
                'Verb',
                'Object',
                'Simple Sentence',
                'Word Order',
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
                        'intro' => 'У цій темі ти вивчиш <strong>будову простого англійського речення</strong>: що таке підмет, присудок і додаток, та як правильно їх розташовувати за схемою S–V–O.',
                        'rules' => [
                            [
                                'label' => 'Subject (S)',
                                'color' => 'emerald',
                                'text' => '<strong>Підмет</strong> — хто або що виконує дію:',
                                'example' => 'She / The cat / My friends',
                            ],
                            [
                                'label' => 'Verb (V)',
                                'color' => 'blue',
                                'text' => '<strong>Присудок</strong> — дія або стан:',
                                'example' => 'reads / is / have',
                            ],
                            [
                                'label' => 'Object (O)',
                                'color' => 'amber',
                                'text' => '<strong>Додаток</strong> — на кого/що спрямована дія:',
                                'example' => 'a book / happy / two cats',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке просте речення?',
                        'intro' => 'Просте речення (Simple Sentence) має один підмет і один присудок:',
                        'items' => [
                            ['label' => 'Один підмет', 'title' => 'Subject', 'subtitle' => 'Одна особа або предмет виконує дію'],
                            ['label' => 'Один присудок', 'title' => 'Verb', 'subtitle' => 'Одна дія або один стан'],
                            ['label' => 'Закінчена думка', 'title' => 'Complete thought', 'subtitle' => 'Речення має повний сенс'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Subject — Підмет',
                        'sections' => [
                            [
                                'label' => 'Що таке підмет',
                                'color' => 'emerald',
                                'description' => '<strong>Subject</strong> — це слово або група слів, що вказує, хто або що виконує дію. Підмет <strong>завжди стоїть на першому місці</strong> в ствердному реченні.',
                                'examples' => [
                                    ['en' => 'I study English.', 'ua' => 'Я вивчаю англійську.'],
                                    ['en' => 'She works at a bank.', 'ua' => 'Вона працює в банку.'],
                                    ['en' => 'The children play outside.', 'ua' => 'Діти грають надворі.'],
                                ],
                            ],
                            [
                                'label' => 'Типи підметів',
                                'color' => 'sky',
                                'description' => 'Підметом може бути: <strong>займенник</strong> (I, you, he, she, it, we, they), <strong>іменник</strong> (Tom, dog, car), або <strong>група слів</strong> (the old man, my best friend).',
                                'examples' => [
                                    ['en' => 'He is a doctor.', 'ua' => 'Він лікар. (займенник)'],
                                    ['en' => 'Maria speaks Spanish.', 'ua' => 'Марія говорить іспанською. (іменник)'],
                                    ['en' => 'My older brother lives in Kyiv.', 'ua' => 'Мій старший брат живе у Києві. (група слів)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Verb — Присудок (дієслово)',
                        'sections' => [
                            [
                                'label' => 'Що таке присудок',
                                'color' => 'emerald',
                                'description' => '<strong>Verb</strong> — це слово, що виражає дію або стан. Присудок <strong>завжди стоїть після підмета</strong>.',
                                'examples' => [
                                    ['en' => 'She reads books.', 'ua' => 'Вона читає книги. (дія)'],
                                    ['en' => 'He is happy.', 'ua' => 'Він щасливий. (стан)'],
                                    ['en' => 'They have a car.', 'ua' => 'У них є машина. (володіння)'],
                                ],
                            ],
                            [
                                'label' => 'Типи дієслів',
                                'color' => 'sky',
                                'description' => 'Основні типи: <strong>action verbs</strong> (дії: run, eat, write), <strong>linking verbs</strong> (звʼязки: be, seem, become), <strong>auxiliary verbs</strong> (допоміжні: do, have, will).',
                                'examples' => [
                                    ['en' => 'I eat breakfast at 8.', 'ua' => 'Я снідаю о 8-й. (action verb)'],
                                    ['en' => 'She is a teacher.', 'ua' => 'Вона вчителька. (linking verb)'],
                                    ['en' => 'They do not like coffee.', 'ua' => 'Вони не люблять каву. (auxiliary + main verb)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Object — Додаток',
                        'sections' => [
                            [
                                'label' => 'Що таке додаток',
                                'color' => 'emerald',
                                'description' => '<strong>Object</strong> — це слово або група слів, на які спрямована дія. Додаток <strong>стоїть після дієслова</strong>.',
                                'examples' => [
                                    ['en' => 'She reads a book.', 'ua' => 'Вона читає книгу.'],
                                    ['en' => 'I love my family.', 'ua' => 'Я люблю свою сімʼю.'],
                                    ['en' => 'He bought a new car.', 'ua' => 'Він купив нову машину.'],
                                ],
                            ],
                            [
                                'label' => 'Direct vs Indirect Object',
                                'color' => 'sky',
                                'description' => '<strong>Direct object</strong> — прямий додаток (що?), <strong>indirect object</strong> — непрямий додаток (кому?).',
                                'examples' => [
                                    ['en' => 'She gave me a gift.', 'ua' => 'Вона дала мені подарунок. (me — indirect, gift — direct)'],
                                    ['en' => 'I sent him a letter.', 'ua' => 'Я надіслав йому листа. (him — indirect, letter — direct)'],
                                ],
                                'note' => 'Порядок: <strong>S + V + indirect object + direct object</strong> АБО <strong>S + V + direct object + to/for + indirect object</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Повна структура S–V–O',
                        'intro' => 'Базова схема простого ствердного речення:',
                        'rows' => [
                            [
                                'en' => 'Subject (S)',
                                'ua' => 'Підмет',
                                'note' => 'I, She, The dog, My friends',
                            ],
                            [
                                'en' => 'Verb (V)',
                                'ua' => 'Присудок',
                                'note' => 'read, is, have, like',
                            ],
                            [
                                'en' => 'Object (O)',
                                'ua' => 'Додаток',
                                'note' => 'books, happy, a car, music',
                            ],
                        ],
                        'warning' => '📌 Формула: <strong>Subject + Verb + Object</strong> (S + V + O)',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Приклади речень за структурою S–V–O',
                        'sections' => [
                            [
                                'label' => 'Прості приклади',
                                'color' => 'emerald',
                                'description' => 'Базові речення зі структурою <strong>Subject + Verb + Object</strong>:',
                                'examples' => [
                                    ['en' => 'I eat breakfast.', 'ua' => 'Я їм сніданок. (I — S, eat — V, breakfast — O)'],
                                    ['en' => 'She loves music.', 'ua' => 'Вона любить музику. (She — S, loves — V, music — O)'],
                                    ['en' => 'They play football.', 'ua' => 'Вони грають у футбол. (They — S, play — V, football — O)'],
                                ],
                            ],
                            [
                                'label' => 'Розширені приклади',
                                'color' => 'sky',
                                'description' => 'Речення з групами слів як підмет або додаток:',
                                'examples' => [
                                    ['en' => 'My best friend speaks three languages.', 'ua' => 'Мій найкращий друг говорить трьома мовами.'],
                                    ['en' => 'The old man reads the morning newspaper.', 'ua' => 'Старий чоловік читає ранкову газету.'],
                                    ['en' => 'Our teacher explains difficult grammar rules.', 'ua' => 'Наш вчитель пояснює складні граматичні правила.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Речення без додатка (S–V)',
                        'sections' => [
                            [
                                'label' => 'Неперехідні дієслова',
                                'color' => 'emerald',
                                'description' => 'Деякі дієслова (<strong>intransitive verbs</strong>) не потребують додатка. Структура: <strong>S + V</strong>.',
                                'examples' => [
                                    ['en' => 'She sleeps.', 'ua' => 'Вона спить.'],
                                    ['en' => 'The baby cries.', 'ua' => 'Дитина плаче.'],
                                    ['en' => 'Birds fly.', 'ua' => 'Птахи літають.'],
                                ],
                            ],
                            [
                                'label' => 'Дієслово TO BE',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> часто вживається не додаток, а <strong>complement</strong> (предикатив).',
                                'examples' => [
                                    ['en' => 'She is a doctor.', 'ua' => 'Вона лікар. (doctor — complement)'],
                                    ['en' => 'They are happy.', 'ua' => 'Вони щасливі. (happy — complement)'],
                                    ['en' => 'I am at home.', 'ua' => 'Я вдома. (at home — complement)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняння: англійська vs українська',
                        'intro' => 'Головна різниця між мовами:',
                        'rows' => [
                            [
                                'en' => 'English: Fixed word order',
                                'ua' => 'Англійська: фіксований порядок',
                                'note' => 'She reads books. ✅',
                            ],
                            [
                                'en' => 'Ukrainian: Flexible word order',
                                'ua' => 'Українська: вільний порядок',
                                'note' => 'Книги читає вона. ✅',
                            ],
                            [
                                'en' => 'Wrong in English',
                                'ua' => 'Неправильно англійською',
                                'note' => 'Books reads she. ❌',
                            ],
                        ],
                        'warning' => '⚠️ В англійській порядок слів <strong>визначає значення</strong>. Не можна міняти місцями підмет і додаток!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск підмета під впливом української.',
                                'wrong' => 'Is raining. / Am tired.',
                                'right' => '✅ <span class="font-mono">It is raining. / I am tired.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Зміна порядку S–V–O.',
                                'wrong' => 'Books reads she every day.',
                                'right' => '✅ <span class="font-mono">She reads books every day.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Додаток перед дієсловом.',
                                'wrong' => 'I English study.',
                                'right' => '✅ <span class="font-mono">I study English.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Підмет після дієслова.',
                                'wrong' => 'Plays Tom football.',
                                'right' => '✅ <span class="font-mono">Tom plays football.</span>',
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
                            '<strong>Просте речення</strong> має один підмет і один присудок.',
                            '<strong>Базова структура:</strong> S + V + O (Subject + Verb + Object).',
                            '<strong>Subject (підмет)</strong> — хто або що виконує дію; стоїть <strong>на першому місці</strong>.',
                            '<strong>Verb (присудок)</strong> — дія або стан; стоїть <strong>після підмета</strong>.',
                            '<strong>Object (додаток)</strong> — на що спрямована дія; стоїть <strong>після дієслова</strong>.',
                            'В англійській <strong>порядок слів фіксований</strong> — не можна міняти місцями елементи.',
                            'Деякі речення мають структуру <strong>S + V</strong> (без додатка): She sleeps.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Визнач елементи речення',
                        'select_intro' => 'Визнач, яким елементом є виділене слово.',
                        'selects' => [
                            ['label' => 'SHE reads books. (Subject / Verb / Object)', 'prompt' => 'Який елемент?'],
                            ['label' => 'She READS books. (Subject / Verb / Object)', 'prompt' => 'Який елемент?'],
                            ['label' => 'She reads BOOKS. (Subject / Verb / Object)', 'prompt' => 'Який елемент?'],
                        ],
                        'options' => ['Subject', 'Verb', 'Object'],
                        'input_title' => 'Вправа 2. Склади речення за структурою S–V–O',
                        'input_intro' => 'Розташуй слова у правильному порядку.',
                        'inputs' => [
                            ['before' => '(loves / music / Maria)', 'after' => '→'],
                            ['before' => '(plays / he / tennis)', 'after' => '→'],
                            ['before' => '(books / read / the students)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у структурі речення.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Coffee drinks she.',
                                'example_target' => 'She drinks coffee.',
                            ],
                            [
                                'original' => '1. English I study every day.',
                                'placeholder' => 'Виправ порядок слів',
                            ],
                            [
                                'original' => '2. Football plays my brother.',
                                'placeholder' => 'Виправ порядок слів',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з базової граматики',
                        'items' => [
                            [
                                'label' => 'Sentence Structure — Будова речення S–V–O (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Parts of Speech — Частини мови',
                                'current' => false,
                            ],
                            [
                                'label' => 'Basic Word Order — Порядок слів у ствердженні',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
