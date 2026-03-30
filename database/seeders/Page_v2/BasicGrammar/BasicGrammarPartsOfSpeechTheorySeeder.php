<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class BasicGrammarPartsOfSpeechTheorySeeder extends BasicGrammarPageSeeder
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
            'subtitle_html' => '<p><strong>Parts of speech</strong> (частини мови) — це основні категорії, на які поділяються всі слова англійської мови за їхньою функцією в реченні. Розуміння частин мови допоможе правильно будувати речення.</p>',
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
                        'intro' => 'У цій темі ти вивчиш <strong>8 основних частин мови</strong> англійської: іменники, дієслова, прикметники, прислівники, займенники, прийменники, сполучники та вигуки.',
                        'rules' => [
                            [
                                'label' => 'Content Words',
                                'color' => 'emerald',
                                'text' => '<strong>Nouns, Verbs, Adjectives, Adverbs</strong> — несуть основний зміст:',
                                'example' => 'The happy cat runs quickly.',
                            ],
                            [
                                'label' => 'Function Words',
                                'color' => 'blue',
                                'text' => '<strong>Pronouns, Prepositions, Conjunctions</strong> — звʼязують слова:',
                                'example' => 'She is at home and I am here.',
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
                        'title' => '1. Огляд 8 частин мови',
                        'intro' => 'Англійська мова має 8 основних частин мови:',
                        'items' => [
                            ['label' => 'Noun', 'title' => 'Іменник', 'subtitle' => 'Назви предметів, людей, місць, ідей (cat, John, city, love)'],
                            ['label' => 'Verb', 'title' => 'Дієслово', 'subtitle' => 'Дії або стани (run, eat, be, have)'],
                            ['label' => 'Adjective', 'title' => 'Прикметник', 'subtitle' => 'Описує іменники (big, red, beautiful, smart)'],
                            ['label' => 'Adverb', 'title' => 'Прислівник', 'subtitle' => 'Описує дієслова, прикметники (quickly, very, often)'],
                            ['label' => 'Pronoun', 'title' => 'Займенник', 'subtitle' => 'Замінює іменники (I, you, he, she, it, they)'],
                            ['label' => 'Preposition', 'title' => 'Прийменник', 'subtitle' => 'Показує відношення (in, on, at, under, between)'],
                            ['label' => 'Conjunction', 'title' => 'Сполучник', 'subtitle' => "Зʼєднує слова та речення (and, but, or, because)"],
                            ['label' => 'Interjection', 'title' => 'Вигук', 'subtitle' => 'Виражає емоції (Oh! Wow! Oops! Hey!)'],
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
                                'label' => 'Що таке іменник',
                                'color' => 'emerald',
                                'description' => '<strong>Noun</strong> — це слово, що називає людину, місце, річ або ідею.',
                                'examples' => [
                                    ['en' => 'The cat is sleeping.', 'ua' => 'Кіт спить. (cat — річ)'],
                                    ['en' => 'Mary lives in London.', 'ua' => 'Мері живе в Лондоні. (Mary — людина, London — місце)'],
                                    ['en' => 'Love is important.', 'ua' => 'Любов важлива. (love — ідея)'],
                                ],
                            ],
                            [
                                'label' => 'Типи іменників',
                                'color' => 'sky',
                                'description' => 'Іменники бувають <strong>common</strong> (загальні) та <strong>proper</strong> (власні).',
                                'examples' => [
                                    ['en' => 'common: dog, city, book', 'ua' => 'загальні: собака, місто, книга'],
                                    ['en' => 'proper: Tom, Paris, Monday', 'ua' => 'власні: Том, Париж, понеділок'],
                                ],
                                'note' => '<strong>Proper nouns</strong> завжди пишуться з великої літери.',
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
                                'label' => 'Що таке дієслово',
                                'color' => 'emerald',
                                'description' => '<strong>Verb</strong> — це слово, що виражає дію або стан.',
                                'examples' => [
                                    ['en' => 'She runs every morning.', 'ua' => 'Вона бігає щоранку. (run — дія)'],
                                    ['en' => 'He is a teacher.', 'ua' => 'Він учитель. (is — стан)'],
                                    ['en' => 'They have two children.', 'ua' => 'У них двоє дітей. (have — володіння)'],
                                ],
                            ],
                            [
                                'label' => 'Типи дієслів',
                                'color' => 'sky',
                                'description' => 'Основні типи: <strong>action verbs</strong> (дії), <strong>linking verbs</strong> (звʼязки), <strong>auxiliary verbs</strong> (допоміжні).',
                                'examples' => [
                                    ['en' => 'action: run, eat, write, play', 'ua' => 'дії: бігти, їсти, писати, грати'],
                                    ['en' => 'linking: be, seem, become', 'ua' => 'звʼязки: бути, здаватися, ставати'],
                                    ['en' => 'auxiliary: do, have, will, can', 'ua' => 'допоміжні: do, have, will, can'],
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
                                'label' => 'Що таке прикметник',
                                'color' => 'emerald',
                                'description' => '<strong>Adjective</strong> — слово, що описує або модифікує іменник.',
                                'examples' => [
                                    ['en' => 'a big house', 'ua' => 'великий будинок'],
                                    ['en' => 'a beautiful flower', 'ua' => 'гарна квітка'],
                                    ['en' => 'The coffee is hot.', 'ua' => 'Кава гаряча.'],
                                ],
                            ],
                            [
                                'label' => 'Позиція прикметника',
                                'color' => 'sky',
                                'description' => 'Прикметник стоїть <strong>перед іменником</strong> або <strong>після linking verb</strong>.',
                                'examples' => [
                                    ['en' => 'a tall man (перед іменником)', 'ua' => 'високий чоловік'],
                                    ['en' => 'The man is tall. (після linking verb)', 'ua' => 'Чоловік високий.'],
                                ],
                                'note' => 'В англійській прикметник <strong>не змінюється</strong> за родом і числом: a tall man, a tall woman, tall people.',
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
                                'label' => 'Що таке прислівник',
                                'color' => 'emerald',
                                'description' => '<strong>Adverb</strong> — слово, що модифікує дієслово, прикметник або інший прислівник.',
                                'examples' => [
                                    ['en' => 'She runs quickly.', 'ua' => 'Вона бігає швидко. (модифікує дієслово)'],
                                    ['en' => 'He is very tall.', 'ua' => 'Він дуже високий. (модифікує прикметник)'],
                                    ['en' => 'She speaks too quietly.', 'ua' => 'Вона говорить занадто тихо. (модифікує прислівник)'],
                                ],
                            ],
                            [
                                'label' => 'Утворення прислівників',
                                'color' => 'sky',
                                'description' => 'Багато прислівників утворюються додаванням <strong>-ly</strong> до прикметника.',
                                'examples' => [
                                    ['en' => 'quick → quickly', 'ua' => 'швидкий → швидко'],
                                    ['en' => 'careful → carefully', 'ua' => 'обережний → обережно'],
                                    ['en' => 'happy → happily', 'ua' => 'щасливий → щасливо'],
                                ],
                                'note' => 'Виняток: <strong>good → well</strong> (не goodly), <strong>fast → fast</strong> (однакова форма).',
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
                                'label' => 'Що таке займенник',
                                'color' => 'emerald',
                                'description' => '<strong>Pronoun</strong> — слово, що замінює іменник, щоб уникнути повторів.',
                                'examples' => [
                                    ['en' => 'Mary is a doctor. She works at a hospital.', 'ua' => 'Мері — лікар. Вона працює в лікарні.'],
                                    ['en' => 'The book is interesting. It is about history.', 'ua' => 'Книга цікава. Вона про історію.'],
                                ],
                            ],
                            [
                                'label' => 'Типи займенників',
                                'color' => 'sky',
                                'description' => 'Основні типи: <strong>personal</strong> (особові), <strong>possessive</strong> (присвійні), <strong>demonstrative</strong> (вказівні).',
                                'examples' => [
                                    ['en' => 'personal: I, you, he, she, it, we, they', 'ua' => 'особові: я, ти, він, вона, воно, ми, вони'],
                                    ['en' => 'possessive: my, your, his, her, our, their', 'ua' => 'присвійні: мій, твій, його, її, наш, їхній'],
                                    ['en' => 'demonstrative: this, that, these, those', 'ua' => 'вказівні: цей, той, ці, ті'],
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
                                'label' => 'Що таке прийменник',
                                'color' => 'emerald',
                                'description' => '<strong>Preposition</strong> — слово, що показує відношення між словами (місце, час, напрямок).',
                                'examples' => [
                                    ['en' => 'The book is on the table.', 'ua' => 'Книга на столі. (місце)'],
                                    ['en' => 'I wake up at 7 a.m.', 'ua' => 'Я прокидаюся о 7 ранку. (час)'],
                                    ['en' => 'She walked to the park.', 'ua' => 'Вона пішла до парку. (напрямок)'],
                                ],
                            ],
                            [
                                'label' => 'Найпоширеніші прийменники',
                                'color' => 'sky',
                                'description' => 'Основні прийменники місця, часу та руху.',
                                'examples' => [
                                    ['en' => 'place: in, on, at, under, behind, between', 'ua' => 'місце: в, на, біля, під, за, між'],
                                    ['en' => 'time: at, on, in, before, after', 'ua' => 'час: о (годині), у (день), в (місяць), до, після'],
                                    ['en' => 'movement: to, from, into, out of', 'ua' => 'рух: до, від, у (всередину), з'],
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
                                'label' => 'Що таке сполучник',
                                'color' => 'emerald',
                                'description' => "<strong>Conjunction</strong> — слово, що зʼєднує слова, фрази або речення.",
                                'examples' => [
                                    ['en' => 'I like tea and coffee.', 'ua' => 'Я люблю чай і каву.'],
                                    ['en' => 'She is smart but lazy.', 'ua' => 'Вона розумна, але лінива.'],
                                    ['en' => 'I stayed home because it was raining.', 'ua' => 'Я залишився вдома, бо йшов дощ.'],
                                ],
                            ],
                            [
                                'label' => 'Типи сполучників',
                                'color' => 'sky',
                                'description' => '<strong>Coordinating</strong> (сурядні) та <strong>subordinating</strong> (підрядні) сполучники.',
                                'examples' => [
                                    ['en' => 'coordinating: and, but, or, so, yet', 'ua' => 'сурядні: і, але, або, тому, однак'],
                                    ['en' => 'subordinating: because, if, when, although', 'ua' => 'підрядні: тому що, якщо, коли, хоча'],
                                ],
                                'note' => '<strong>FANBOYS</strong> — абревіатура для сурядних: For, And, Nor, But, Or, Yet, So.',
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
                                'label' => 'Що таке вигук',
                                'color' => 'emerald',
                                'description' => '<strong>Interjection</strong> — слово або фраза, що виражає емоції чи реакцію.',
                                'examples' => [
                                    ['en' => 'Wow! That is amazing!', 'ua' => 'Ого! Це неймовірно!'],
                                    ['en' => 'Oops! I made a mistake.', 'ua' => 'Ой! Я зробив помилку.'],
                                    ['en' => 'Hurray! We won!', 'ua' => 'Ура! Ми виграли!'],
                                ],
                            ],
                            [
                                'label' => 'Поширені вигуки',
                                'color' => 'sky',
                                'description' => 'Вигуки часто стоять окремо і виділяються знаком оклику.',
                                'examples' => [
                                    ['en' => 'Oh! Ah! Wow! Hey! Ouch!', 'ua' => 'О! А! Ого! Гей! Ой!'],
                                    ['en' => 'Well, I think so.', 'ua' => 'Ну, я думаю так. (помʼякшення)'],
                                ],
                                'note' => 'Вигуки додають емоційності мовленню, але в formal writing їх уникають.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Зведена таблиця частин мови',
                        'intro' => 'Усі 8 частин мови та їхні функції:',
                        'rows' => [
                            [
                                'en' => 'Noun',
                                'ua' => 'Іменник',
                                'note' => 'називає: cat, city, love',
                            ],
                            [
                                'en' => 'Verb',
                                'ua' => 'Дієслово',
                                'note' => 'дія/стан: run, is, have',
                            ],
                            [
                                'en' => 'Adjective',
                                'ua' => 'Прикметник',
                                'note' => 'описує іменник: big, red',
                            ],
                            [
                                'en' => 'Adverb',
                                'ua' => 'Прислівник',
                                'note' => 'модифікує дієслово: quickly, very',
                            ],
                            [
                                'en' => 'Pronoun',
                                'ua' => 'Займенник',
                                'note' => 'замінює іменник: I, she, they',
                            ],
                            [
                                'en' => 'Preposition',
                                'ua' => 'Прийменник',
                                'note' => 'відношення: in, on, at',
                            ],
                            [
                                'en' => 'Conjunction',
                                'ua' => 'Сполучник',
                                'note' => "зʼєднує: and, but, because",
                            ],
                            [
                                'en' => 'Interjection',
                                'ua' => 'Вигук',
                                'note' => 'емоції: Wow! Oh! Oops!',
                            ],
                        ],
                        'warning' => '📌 Одне слово може бути різними частинами мови залежно від контексту: <strong>run</strong> (verb: I run) vs <strong>run</strong> (noun: a morning run).',
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
                                'title' => 'Використання прикметника замість прислівника.',
                                'wrong' => 'He runs quick.',
                                'right' => '✅ <span class="font-mono">He runs quickly.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний вибір займенника.',
                                'wrong' => 'Me and Tom went to school.',
                                'right' => '✅ <span class="font-mono">Tom and I went to school.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Пропуск артикля перед іменником.',
                                'wrong' => 'I have cat.',
                                'right' => '✅ <span class="font-mono">I have a cat.</span>',
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
                            '<strong>Noun</strong> (іменник) — називає людину, місце, річ або ідею.',
                            '<strong>Verb</strong> (дієслово) — виражає дію або стан.',
                            '<strong>Adjective</strong> (прикметник) — описує іменник.',
                            '<strong>Adverb</strong> (прислівник) — модифікує дієслово, прикметник або прислівник.',
                            '<strong>Pronoun</strong> (займенник) — замінює іменник.',
                            '<strong>Preposition</strong> (прийменник) — показує відношення між словами.',
                            "<strong>Conjunction</strong> (сполучник) — зʼєднує слова та речення.",
                            '<strong>Interjection</strong> (вигук) — виражає емоції.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '13. Практика',
                        'select_title' => 'Вправа 1. Визнач частину мови',
                        'select_intro' => 'Визнач, якою частиною мови є виділене слово.',
                        'selects' => [
                            ['label' => 'The CAT is sleeping. (noun / verb / adjective)', 'prompt' => 'Яка частина мови?'],
                            ['label' => 'She QUICKLY finished her work. (adjective / adverb / noun)', 'prompt' => 'Яка частина мови?'],
                            ['label' => 'They went TO the park. (conjunction / preposition / adverb)', 'prompt' => 'Яка частина мови?'],
                        ],
                        'options' => ['a', 'b', 'c'],
                        'input_title' => 'Вправа 2. Заповни пропуски правильною формою',
                        'input_intro' => 'Обери правильну форму слова.',
                        'inputs' => [
                            ['before' => 'She sings (beautiful / beautifully)', 'after' => '→'],
                            ['before' => 'He is a (good / well) student', 'after' => '→'],
                            ['before' => 'They work (hard / hardly) every day', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Визнач усі частини мови в реченні',
                        'rephrase_intro' => 'Розбери речення на частини мови.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'The happy dog runs quickly.',
                                'example_target' => 'The (article), happy (adjective), dog (noun), runs (verb), quickly (adverb).',
                            ],
                            [
                                'original' => '1. She and I went to school yesterday.',
                                'placeholder' => 'Визнач частини мови',
                            ],
                            [
                                'original' => '2. Wow! The big cat is sleeping on the soft bed.',
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
                            [
                                'label' => 'Word Order in Questions and Negatives — Питання та заперечення',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
