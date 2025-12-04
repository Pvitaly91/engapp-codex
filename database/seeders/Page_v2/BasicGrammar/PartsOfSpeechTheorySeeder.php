<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\BasicGrammar\BasicGrammarPageSeeder;

class PartsOfSpeechTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'parts-of-speech';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Parts of Speech — Частини мови',
            'subtitle_html' => '<p><strong>Parts of speech</strong> (частини мови) — це категорії слів, які виконують різні функції в реченні. В англійській мові виділяють 8 основних частин мови.</p>',
            'subtitle_text' => 'Теоретичний огляд частин мови в англійській мові: іменники, дієслова, прикметники, прислівники, займенники, прийменники, сполучники та вигуки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Parts of Speech',
                'Basic Grammar',
                'Nouns',
                'Verbs',
                'Adjectives',
                'Adverbs',
                'Pronouns',
                'Prepositions',
                'Conjunctions',
                'Interjections',
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
                        'intro' => 'У цій темі ти вивчиш <strong>8 частин мови</strong> в англійській мові: іменники, дієслова, прикметники, прислівники, займенники, прийменники, сполучники та вигуки.',
                        'rules' => [
                            [
                                'label' => 'Content words',
                                'color' => 'emerald',
                                'text' => '<strong>Nouns, Verbs, Adjectives, Adverbs</strong> — несуть основний зміст:',
                                'example' => 'The happy girl runs quickly.',
                            ],
                            [
                                'label' => 'Function words',
                                'color' => 'blue',
                                'text' => '<strong>Pronouns, Prepositions, Conjunctions</strong> — з\'єднують та замінюють:',
                                'example' => 'She is in the park and he is at home.',
                            ],
                            [
                                'label' => 'Interjections',
                                'color' => 'amber',
                                'text' => '<strong>Вигуки</strong> — виражають емоції:',
                                'example' => 'Wow! Oh! Oops!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Огляд частин мови',
                        'intro' => 'В англійській мові виділяють 8 основних частин мови:',
                        'items' => [
                            ['label' => 'Noun', 'title' => 'Іменник', 'subtitle' => 'Назва предмета, особи, місця, ідеї: cat, John, city, love'],
                            ['label' => 'Verb', 'title' => 'Дієслово', 'subtitle' => 'Дія або стан: run, be, have, think'],
                            ['label' => 'Adjective', 'title' => 'Прикметник', 'subtitle' => 'Опис іменника: big, beautiful, smart'],
                            ['label' => 'Adverb', 'title' => 'Прислівник', 'subtitle' => 'Опис дієслова, прикметника: quickly, very, always'],
                            ['label' => 'Pronoun', 'title' => 'Займенник', 'subtitle' => 'Замінює іменник: I, you, he, she, it, we, they'],
                            ['label' => 'Preposition', 'title' => 'Прийменник', 'subtitle' => 'Показує зв\'язок: in, on, at, under, between'],
                            ['label' => 'Conjunction', 'title' => 'Сполучник', 'subtitle' => 'З\'єднує слова та речення: and, but, or, because'],
                            ['label' => 'Interjection', 'title' => 'Вигук', 'subtitle' => 'Виражає емоції: wow, oh, ouch, hey'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Nouns — Іменники',
                        'sections' => [
                            [
                                'label' => 'Що таке іменник?',
                                'color' => 'emerald',
                                'description' => 'Іменник (<strong>noun</strong>) — це слово, яке називає предмет, особу, місце, ідею або явище.',
                                'examples' => [
                                    ['en' => 'The cat is sleeping.', 'ua' => 'Кіт спить.'],
                                    ['en' => 'London is a big city.', 'ua' => 'Лондон — велике місто.'],
                                    ['en' => 'Love is important.', 'ua' => 'Любов важлива.'],
                                ],
                            ],
                            [
                                'label' => 'Типи іменників',
                                'color' => 'sky',
                                'description' => 'Іменники бувають <strong>загальні</strong> (common) та <strong>власні</strong> (proper), <strong>злічувані</strong> (countable) та <strong>незлічувані</strong> (uncountable).',
                                'examples' => [
                                    ['en' => 'Common: dog, book, city', 'ua' => 'Загальні: собака, книга, місто'],
                                    ['en' => 'Proper: Mary, Paris, Monday', 'ua' => 'Власні: Мері, Париж, понеділок'],
                                    ['en' => 'Countable: apple, chair, idea', 'ua' => 'Злічувані: яблуко, стілець, ідея'],
                                    ['en' => 'Uncountable: water, music, information', 'ua' => 'Незлічувані: вода, музика, інформація'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Verbs — Дієслова',
                        'sections' => [
                            [
                                'label' => 'Що таке дієслово?',
                                'color' => 'emerald',
                                'description' => 'Дієслово (<strong>verb</strong>) — це слово, яке виражає дію, стан або процес.',
                                'examples' => [
                                    ['en' => 'She runs every morning.', 'ua' => 'Вона бігає щоранку.'],
                                    ['en' => 'I am a student.', 'ua' => 'Я студент.'],
                                    ['en' => 'They have a car.', 'ua' => 'У них є машина.'],
                                ],
                            ],
                            [
                                'label' => 'Типи дієслів',
                                'color' => 'sky',
                                'description' => 'Дієслова бувають <strong>смислові</strong> (main/lexical) та <strong>допоміжні</strong> (auxiliary): be, do, have, will, can, must.',
                                'examples' => [
                                    ['en' => 'Main verbs: eat, sleep, work, play', 'ua' => 'Смислові: їсти, спати, працювати, грати'],
                                    ['en' => 'Auxiliary verbs: be, do, have', 'ua' => 'Допоміжні: be, do, have'],
                                    ['en' => 'Modal verbs: can, must, should, may', 'ua' => 'Модальні: can, must, should, may'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Adjectives — Прикметники',
                        'sections' => [
                            [
                                'label' => 'Що таке прикметник?',
                                'color' => 'emerald',
                                'description' => 'Прикметник (<strong>adjective</strong>) — це слово, яке описує або характеризує іменник.',
                                'examples' => [
                                    ['en' => 'She has a beautiful house.', 'ua' => 'У неї гарний будинок.'],
                                    ['en' => 'The big dog is friendly.', 'ua' => 'Великий пес дружелюбний.'],
                                    ['en' => 'It is an interesting book.', 'ua' => 'Це цікава книга.'],
                                ],
                            ],
                            [
                                'label' => 'Позиція прикметника',
                                'color' => 'sky',
                                'description' => 'Прикметник стоїть <strong>перед іменником</strong> або <strong>після дієслова-зв\'язки</strong> (be, seem, look, feel).',
                                'examples' => [
                                    ['en' => 'A tall man (before noun)', 'ua' => 'Високий чоловік (перед іменником)'],
                                    ['en' => 'The man is tall (after verb)', 'ua' => 'Чоловік високий (після дієслова)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Adverbs — Прислівники',
                        'sections' => [
                            [
                                'label' => 'Що таке прислівник?',
                                'color' => 'emerald',
                                'description' => 'Прислівник (<strong>adverb</strong>) — це слово, яке описує дієслово, прикметник або інший прислівник. Часто закінчується на <strong>-ly</strong>.',
                                'examples' => [
                                    ['en' => 'She speaks quickly.', 'ua' => 'Вона говорить швидко.'],
                                    ['en' => 'He is very tall.', 'ua' => 'Він дуже високий.'],
                                    ['en' => 'They work extremely hard.', 'ua' => 'Вони працюють надзвичайно наполегливо.'],
                                ],
                            ],
                            [
                                'label' => 'Типи прислівників',
                                'color' => 'sky',
                                'description' => 'Прислівники можуть виражати <strong>спосіб дії</strong>, <strong>час</strong>, <strong>місце</strong>, <strong>частотність</strong> та <strong>ступінь</strong>.',
                                'examples' => [
                                    ['en' => 'Manner: slowly, carefully, well', 'ua' => 'Спосіб: повільно, обережно, добре'],
                                    ['en' => 'Time: now, yesterday, soon', 'ua' => 'Час: зараз, вчора, скоро'],
                                    ['en' => 'Place: here, there, everywhere', 'ua' => 'Місце: тут, там, скрізь'],
                                    ['en' => 'Frequency: always, often, never', 'ua' => 'Частотність: завжди, часто, ніколи'],
                                    ['en' => 'Degree: very, quite, extremely', 'ua' => 'Ступінь: дуже, досить, надзвичайно'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Pronouns — Займенники',
                        'sections' => [
                            [
                                'label' => 'Що таке займенник?',
                                'color' => 'emerald',
                                'description' => 'Займенник (<strong>pronoun</strong>) — це слово, яке замінює іменник, щоб уникнути повторення.',
                                'examples' => [
                                    ['en' => 'John is a teacher. He works at school.', 'ua' => 'Джон — вчитель. Він працює в школі.'],
                                    ['en' => 'The book is interesting. It is about history.', 'ua' => 'Книга цікава. Вона про історію.'],
                                ],
                            ],
                            [
                                'label' => 'Типи займенників',
                                'color' => 'sky',
                                'description' => 'Займенники бувають <strong>особові</strong>, <strong>присвійні</strong>, <strong>вказівні</strong>, <strong>питальні</strong> та <strong>зворотні</strong>.',
                                'examples' => [
                                    ['en' => 'Personal: I, you, he, she, it, we, they', 'ua' => 'Особові: я, ти, він, вона, воно, ми, вони'],
                                    ['en' => 'Possessive: my, your, his, her, our, their', 'ua' => 'Присвійні: мій, твій, його, її, наш, їхній'],
                                    ['en' => 'Demonstrative: this, that, these, those', 'ua' => 'Вказівні: цей, той, ці, ті'],
                                    ['en' => 'Interrogative: who, what, which', 'ua' => 'Питальні: хто, що, який'],
                                    ['en' => 'Reflexive: myself, yourself, himself', 'ua' => 'Зворотні: себе, сам'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Prepositions — Прийменники',
                        'sections' => [
                            [
                                'label' => 'Що таке прийменник?',
                                'color' => 'emerald',
                                'description' => 'Прийменник (<strong>preposition</strong>) — це слово, яке показує зв\'язок між іменником/займенником та іншими словами в реченні.',
                                'examples' => [
                                    ['en' => 'The book is on the table.', 'ua' => 'Книга на столі.'],
                                    ['en' => 'She lives in London.', 'ua' => 'Вона живе в Лондоні.'],
                                    ['en' => 'I go to school at 8 o\'clock.', 'ua' => 'Я йду до школи о 8 годині.'],
                                ],
                            ],
                            [
                                'label' => 'Типи прийменників',
                                'color' => 'sky',
                                'description' => 'Прийменники можуть виражати <strong>місце</strong>, <strong>час</strong>, <strong>напрямок</strong> та <strong>інші відношення</strong>.',
                                'examples' => [
                                    ['en' => 'Place: in, on, at, under, between, behind', 'ua' => 'Місце: в, на, під, між, за'],
                                    ['en' => 'Time: at, on, in, before, after, during', 'ua' => 'Час: о, в, до, після, під час'],
                                    ['en' => 'Direction: to, from, into, out of, towards', 'ua' => 'Напрямок: до, з, у, з, до'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Conjunctions — Сполучники',
                        'sections' => [
                            [
                                'label' => 'Що таке сполучник?',
                                'color' => 'emerald',
                                'description' => 'Сполучник (<strong>conjunction</strong>) — це слово, яке з\'єднує слова, фрази або речення.',
                                'examples' => [
                                    ['en' => 'I like tea and coffee.', 'ua' => 'Мені подобається чай і кава.'],
                                    ['en' => 'She is smart but lazy.', 'ua' => 'Вона розумна, але лінива.'],
                                    ['en' => 'I stayed home because it was raining.', 'ua' => 'Я залишився вдома, бо йшов дощ.'],
                                ],
                            ],
                            [
                                'label' => 'Типи сполучників',
                                'color' => 'sky',
                                'description' => 'Сполучники бувають <strong>сурядні</strong> (coordinating), <strong>підрядні</strong> (subordinating) та <strong>корелятивні</strong> (correlative).',
                                'examples' => [
                                    ['en' => 'Coordinating: and, but, or, so, yet', 'ua' => 'Сурядні: і, але, або, тому'],
                                    ['en' => 'Subordinating: because, although, if, when, while', 'ua' => 'Підрядні: тому що, хоча, якщо, коли, поки'],
                                    ['en' => 'Correlative: both...and, either...or, neither...nor', 'ua' => 'Корелятивні: і...і, або...або, ні...ні'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Interjections — Вигуки',
                        'sections' => [
                            [
                                'label' => 'Що таке вигук?',
                                'color' => 'emerald',
                                'description' => 'Вигук (<strong>interjection</strong>) — це слово або фраза, яка виражає сильну емоцію або реакцію.',
                                'examples' => [
                                    ['en' => 'Wow! That\'s amazing!', 'ua' => 'Вау! Це дивовижно!'],
                                    ['en' => 'Ouch! That hurts!', 'ua' => 'Ой! Боляче!'],
                                    ['en' => 'Oh no! I forgot my keys.', 'ua' => 'О ні! Я забув ключі.'],
                                ],
                            ],
                            [
                                'label' => 'Приклади вигуків',
                                'color' => 'sky',
                                'description' => 'Вигуки виражають різні емоції: радість, подив, біль, розчарування тощо.',
                                'examples' => [
                                    ['en' => 'Joy: Yay! Hooray! Wow!', 'ua' => 'Радість: Ура! Вау!'],
                                    ['en' => 'Surprise: Oh! Wow! What!', 'ua' => 'Подив: О! Вау! Що!'],
                                    ['en' => 'Pain: Ouch! Ow! Ah!', 'ua' => 'Біль: Ой! Ай! Ах!'],
                                    ['en' => 'Greeting: Hi! Hello! Hey!', 'ua' => 'Привітання: Привіт! Гей!'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Порівняння частин мови',
                        'intro' => 'Як відрізнити частини мови одна від одної:',
                        'rows' => [
                            [
                                'en' => 'Noun',
                                'ua' => 'Іменник',
                                'note' => 'Відповідає на питання Хто? Що?',
                            ],
                            [
                                'en' => 'Verb',
                                'ua' => 'Дієслово',
                                'note' => 'Відповідає на питання Що робить?',
                            ],
                            [
                                'en' => 'Adjective',
                                'ua' => 'Прикметник',
                                'note' => 'Відповідає на питання Який? Яка? Яке?',
                            ],
                            [
                                'en' => 'Adverb',
                                'ua' => 'Прислівник',
                                'note' => 'Відповідає на питання Як? Коли? Де?',
                            ],
                            [
                                'en' => 'Pronoun',
                                'ua' => 'Займенник',
                                'note' => 'Замінює іменник',
                            ],
                            [
                                'en' => 'Preposition',
                                'ua' => 'Прийменник',
                                'note' => 'Показує зв\'язок (in, on, at)',
                            ],
                            [
                                'en' => 'Conjunction',
                                'ua' => 'Сполучник',
                                'note' => 'З\'єднує слова/речення (and, but, or)',
                            ],
                            [
                                'en' => 'Interjection',
                                'ua' => 'Вигук',
                                'note' => 'Виражає емоції (wow, oh)',
                            ],
                        ],
                        'warning' => '📌 Одне слово може бути різними частинами мови залежно від контексту: <strong>work</strong> (noun: робота / verb: працювати)',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Плутанина між прикметником і прислівником.',
                                'wrong' => 'She speaks English good.',
                                'right' => '✅ <span class="font-mono">She speaks English well.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск артикля перед іменником.',
                                'wrong' => 'I have cat.',
                                'right' => '✅ <span class="font-mono">I have a cat.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний вибір прийменника.',
                                'wrong' => 'I am in home.',
                                'right' => '✅ <span class="font-mono">I am at home.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Використання подвійного заперечення.',
                                'wrong' => 'I don\'t know nothing.',
                                'right' => '✅ <span class="font-mono">I don\'t know anything.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '12. Короткий конспект',
                        'items' => [
                            '<strong>Noun (іменник)</strong> — назва предмета, особи, місця: cat, John, city, love.',
                            '<strong>Verb (дієслово)</strong> — дія або стан: run, be, have, think.',
                            '<strong>Adjective (прикметник)</strong> — опис іменника: big, beautiful, smart.',
                            '<strong>Adverb (прислівник)</strong> — опис дієслова, прикметника: quickly, very, always.',
                            '<strong>Pronoun (займенник)</strong> — замінює іменник: I, you, he, she, it, we, they.',
                            '<strong>Preposition (прийменник)</strong> — показує зв\'язок: in, on, at, under, between.',
                            '<strong>Conjunction (сполучник)</strong> — з\'єднує слова/речення: and, but, or, because.',
                            '<strong>Interjection (вигук)</strong> — виражає емоції: wow, oh, ouch, hey.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '13. Практика',
                        'select_title' => 'Вправа 1. Визнач частину мови',
                        'select_intro' => 'Обери правильну частину мови для виділеного слова.',
                        'selects' => [
                            ['label' => 'She runs <u>quickly</u>.', 'prompt' => 'Яка це частина мови?'],
                            ['label' => 'The <u>beautiful</u> flower is red.', 'prompt' => 'Яка це частина мови?'],
                            ['label' => '<u>Wow</u>! That\'s amazing!', 'prompt' => 'Яка це частина мови?'],
                        ],
                        'options' => ['noun', 'verb', 'adjective', 'adverb', 'pronoun', 'preposition', 'conjunction', 'interjection'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Впиши правильну частину мови.',
                        'inputs' => [
                            ['before' => 'cat, dog, city — це', 'after' => '(частина мови)'],
                            ['before' => 'quickly, slowly, always — це', 'after' => '(частина мови)'],
                            ['before' => 'and, but, because — це', 'after' => '(частина мови)'],
                        ],
                        'rephrase_title' => 'Вправа 3. Знайди частину мови в реченні',
                        'rephrase_intro' => 'Визнач усі частини мови в реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'The cat sleeps.',
                                'example_target' => 'The (article) cat (noun) sleeps (verb).',
                            ],
                            [
                                'original' => '1. She is very happy.',
                                'placeholder' => 'Визнач частини мови',
                            ],
                            [
                                'original' => '2. Wow! He runs quickly.',
                                'placeholder' => 'Визнач частини мови',
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
                                'label' => 'Parts of Speech — Частини мови (поточна)',
                                'current' => true,
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
