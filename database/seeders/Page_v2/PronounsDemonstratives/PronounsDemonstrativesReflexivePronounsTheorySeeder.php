<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Page_v2\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsDemonstrativesReflexivePronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'reflexive-pronouns-myself-yourself-themselves';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Reflexive Pronouns — myself, yourself, themselves',
            'subtitle_html' => "<p><strong>Reflexive pronouns</strong> (зворотні займенники) — це займенники, що закінчуються на <strong>-self</strong> (однина) або <strong>-selves</strong> (множина). Вони показують, що <strong>підмет і об'єкт дії — одна й та сама особа</strong>: I taught myself, She cut herself, They enjoyed themselves.</p>",
            'subtitle_text' => "Теоретичний огляд зворотних займенників англійської мови: myself, yourself, himself, herself, itself, ourselves, yourselves, themselves — правила вживання, emphatic use, та типові помилки.",
            'locale' => 'uk',
            'category' => [
                'slug' => 'zaimennyky-ta-vkazivni-slova',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Reflexive Pronouns',
                'Myself',
                'Yourself',
                'Himself',
                'Herself',
                'Itself',
                'Ourselves',
                'Yourselves',
                'Themselves',
                'Self',
                'Selves',
                'Pronouns',
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
                        'intro' => "У цій темі ти вивчиш <strong>зворотні займенники</strong> — слова, що показують, що дія повертається на себе.",
                        'rules' => [
                            [
                                'label' => '-SELF/-SELVES',
                                'color' => 'emerald',
                                'text' => '<strong>Форма:</strong> -self (однина), -selves (множина)',
                                'example' => 'myself, yourself, himself, ourselves, themselves',
                            ],
                            [
                                'label' => 'Reflexive use',
                                'color' => 'blue',
                                'text' => '<strong>Дія на себе:</strong> підмет = об\'єкт',
                                'example' => 'I taught myself Spanish. She cut herself.',
                            ],
                            [
                                'label' => 'Emphatic use',
                                'color' => 'amber',
                                'text' => '<strong>Підсилення:</strong> сам, сама, самі',
                                'example' => 'I did it myself. The boss himself came.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Повна таблиця зворотних займенників',
                        'intro' => "Усі зворотні займенники закінчуються на -self або -selves:",
                        'rows' => [
                            [
                                'en' => 'I → myself',
                                'ua' => 'я → себе (сам/сама)',
                                'note' => 'I hurt myself.',
                            ],
                            [
                                'en' => 'you → yourself (singular)',
                                'ua' => 'ти → себе (сам/сама)',
                                'note' => 'You did it yourself.',
                            ],
                            [
                                'en' => 'he → himself',
                                'ua' => 'він → себе (сам)',
                                'note' => 'He cut himself.',
                            ],
                            [
                                'en' => 'she → herself',
                                'ua' => 'вона → себе (сама)',
                                'note' => 'She taught herself.',
                            ],
                            [
                                'en' => 'it → itself',
                                'ua' => 'воно → себе (саме)',
                                'note' => 'The cat cleaned itself.',
                            ],
                            [
                                'en' => 'we → ourselves',
                                'ua' => 'ми → себе (самі)',
                                'note' => 'We enjoyed ourselves.',
                            ],
                            [
                                'en' => 'you → yourselves (plural)',
                                'ua' => 'ви → себе (самі)',
                                'note' => 'You all did it yourselves.',
                            ],
                            [
                                'en' => 'they → themselves',
                                'ua' => 'вони → себе (самі)',
                                'note' => 'They introduced themselves.',
                            ],
                        ],
                        'warning' => "📌 -self = однина, -selves = множина.",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Reflexive use — дія на себе',
                        'sections' => [
                            [
                                'label' => 'Коли підмет = об\'єкт',
                                'color' => 'emerald',
                                'description' => "Зворотні займенники використовуються, коли <strong>той, хто діє, і той, на кого спрямована дія — одна й та сама особа</strong>.",
                                'examples' => [
                                    ['en' => 'I hurt myself.', 'ua' => 'Я поранив себе.'],
                                    ['en' => 'She cut herself with a knife.', 'ua' => 'Вона порізалася ножем.'],
                                    ['en' => 'He taught himself to play guitar.', 'ua' => 'Він навчив себе грати на гітарі.'],
                                    ['en' => 'We enjoyed ourselves at the party.', 'ua' => 'Ми добре провели час на вечірці.'],
                                    ['en' => 'They introduced themselves.', 'ua' => 'Вони представилися.'],
                                ],
                            ],
                            [
                                'label' => 'Типові дієслова з reflexive',
                                'color' => 'sky',
                                'description' => "Деякі дієслова часто використовуються з зворотними займенниками:",
                                'examples' => [
                                    ['en' => 'hurt yourself — поранити себе', 'ua' => 'I hurt myself playing football.'],
                                    ['en' => 'cut yourself — порізатися', 'ua' => 'Be careful not to cut yourself!'],
                                    ['en' => 'teach yourself — навчити себе', 'ua' => 'She taught herself Spanish.'],
                                    ['en' => 'enjoy yourself — насолоджуватися', 'ua' => 'Enjoy yourselves at the concert!'],
                                    ['en' => 'introduce yourself — представитися', 'ua' => 'Let me introduce myself.'],
                                    ['en' => 'help yourself — частуватися', 'ua' => 'Help yourself to some cake.'],
                                ],
                            ],
                            [
                                'label' => 'Look at yourself — подивися на себе',
                                'color' => 'purple',
                                'description' => "З дієсловами типу <strong>look at, talk to, believe in</strong> також використовуємо reflexive.",
                                'examples' => [
                                    ['en' => 'Look at yourself in the mirror.', 'ua' => 'Подивися на себе в дзеркало.'],
                                    ['en' => 'She talks to herself sometimes.', 'ua' => 'Вона іноді розмовляє сама з собою.'],
                                    ['en' => 'You should believe in yourself.', 'ua' => 'Ти маєш вірити в себе.'],
                                    ['en' => 'They laughed at themselves.', 'ua' => 'Вони сміялися з себе.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Emphatic use — підсилення (сам, сама, самі)',
                        'sections' => [
                            [
                                'label' => 'Для підсилення',
                                'color' => 'amber',
                                'description' => "Зворотні займенники також використовуються для <strong>підсилення</strong> — щоб наголосити, що саме ця особа виконала дію (не хтось інший).",
                                'examples' => [
                                    ['en' => 'I did it myself.', 'ua' => 'Я зробив це сам (без чужої допомоги).'],
                                    ['en' => 'She cooked dinner herself.', 'ua' => 'Вона сама приготувала вечерю.'],
                                    ['en' => 'The president himself came to the event.', 'ua' => 'Сам президент прийшов на подію.'],
                                    ['en' => 'We built this house ourselves.', 'ua' => 'Ми самі побудували цей будинок.'],
                                ],
                            ],
                            [
                                'label' => 'Позиція emphatic',
                                'color' => 'blue',
                                'description' => "В emphatic use зворотний займенник може стояти <strong>відразу після підмета</strong> або <strong>в кінці речення</strong>.",
                                'examples' => [
                                    ['en' => 'I myself saw it. = I saw it myself.', 'ua' => 'Я сам це бачив.'],
                                    ['en' => 'The boss himself called. = The boss called himself.', 'ua' => 'Сам бос телефонував.'],
                                    ['en' => 'She herself told me. = She told me herself.', 'ua' => 'Вона сама мені сказала.'],
                                ],
                                'note' => '📌 Обидві позиції правильні — після підмета або в кінці.',
                            ],
                            [
                                'label' => 'Відмінність: reflexive vs emphatic',
                                'color' => 'purple',
                                'description' => "<strong>Reflexive</strong> — обов'язковий (дія на себе). <strong>Emphatic</strong> — необов'язковий (для підсилення).",
                                'examples' => [
                                    ['en' => 'I hurt myself. (reflexive — обов\'язково)', 'ua' => 'Я поранив себе.'],
                                    ['en' => 'I did it myself. (emphatic — можна: I did it)', 'ua' => 'Я зробив це сам.'],
                                ],
                                'note' => "Reflexive — необхідний. Emphatic — для акценту.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. By yourself, for yourself, to yourself',
                        'sections' => [
                            [
                                'label' => 'By yourself — самостійно, наодинці',
                                'color' => 'emerald',
                                'description' => "<strong>By + reflexive</strong> означає <strong>самостійно</strong> (без допомоги) або <strong>наодинці</strong> (сам).",
                                'examples' => [
                                    ['en' => 'I did it by myself.', 'ua' => 'Я зробив це самостійно (без допомоги).'],
                                    ['en' => 'She lives by herself.', 'ua' => 'Вона живе сама (одна).'],
                                    ['en' => 'Can you do it by yourself?', 'ua' => 'Ти можеш зробити це сам?'],
                                    ['en' => 'He went to the cinema by himself.', 'ua' => 'Він пішов у кіно сам (один).'],
                                ],
                                'note' => '📌 By myself = alone (сам) або without help (самостійно).',
                            ],
                            [
                                'label' => 'For yourself — для себе',
                                'color' => 'blue',
                                'description' => "<strong>For + reflexive</strong> означає <strong>для себе</strong>.",
                                'examples' => [
                                    ['en' => 'Buy something for yourself.', 'ua' => 'Купи щось для себе.'],
                                    ['en' => 'Keep it for yourself.', 'ua' => 'Залиш це собі.'],
                                    ['en' => 'I made this cake for myself.', 'ua' => 'Я спекла цей торт для себе.'],
                                ],
                            ],
                            [
                                'label' => 'To yourself — собі, для себе',
                                'color' => 'sky',
                                'description' => "<strong>To + reflexive</strong> часто в виразах типу 'говорити собі', 'тримати при собі'.",
                                'examples' => [
                                    ['en' => 'Keep it to yourself.', 'ua' => 'Тримай це при собі (не розповідай).'],
                                    ['en' => 'I said to myself...', 'ua' => 'Я сказав собі...'],
                                    ['en' => 'She whispered to herself.', 'ua' => 'Вона прошепотіла собі.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Коли НЕ використовувати reflexive',
                        'sections' => [
                            [
                                'label' => 'З деякими дієсловами НЕ треба',
                                'color' => 'rose',
                                'description' => "Деякі дієслова в англійській <strong>НЕ потребують</strong> зворотного займенника (на відміну від української!).",
                                'examples' => [
                                    ['en' => 'I feel good. (✓)', 'ua' => 'Я почуваюся добре.'],
                                    ['en' => 'I feel myself good. (✗)', 'ua' => 'ПОМИЛКА'],
                                    ['en' => 'She got dressed. (✓)', 'ua' => 'Вона вдяглася.'],
                                    ['en' => 'She dressed herself. (✗ у сучасній англійській)', 'ua' => 'старомодно'],
                                    ['en' => 'We met at 5 pm. (✓)', 'ua' => 'Ми зустрілися о 17:00.'],
                                    ['en' => 'We met ourselves. (✗)', 'ua' => 'ПОМИЛКА'],
                                ],
                                'note' => '📌 Feel, get dressed, meet, wash, shave — без reflexive!',
                            ],
                            [
                                'label' => 'Wash, shave — особливий випадок',
                                'color' => 'amber',
                                'description' => "<strong>Wash</strong> і <strong>shave</strong> зазвичай без reflexive, але можна додати для підсилення.",
                                'examples' => [
                                    ['en' => 'I wash every day. (✓ звичайно)', 'ua' => 'Я вмиваюся щодня.'],
                                    ['en' => 'I wash myself every day. (✓ з підсиленням)', 'ua' => 'Я вмиваю себе щодня.'],
                                    ['en' => 'He shaves in the morning. (✓)', 'ua' => 'Він голиться вранці.'],
                                    ['en' => 'The baby can wash himself. (✓)', 'ua' => 'Малюк вже може вмиватися сам.'],
                                ],
                                'note' => 'Wash/shave без reflexive — норма. З reflexive — для акценту.',
                            ],
                            [
                                'label' => 'Коли об\'єкт — інша особа',
                                'color' => 'purple',
                                'description' => "Якщо дія спрямована на <strong>іншу особу</strong> (не на себе) — НЕ використовуємо reflexive.",
                                'examples' => [
                                    ['en' => 'She washed the baby. (✓)', 'ua' => 'Вона вмила дитину.'],
                                    ['en' => 'She washed herself. (✓)', 'ua' => 'Вона вмилася (сама).'],
                                    ['en' => 'I hurt my friend. (✓)', 'ua' => 'Я поранив свого друга.'],
                                    ['en' => 'I hurt myself. (✓)', 'ua' => 'Я поранив себе.'],
                                ],
                                'note' => 'Reflexive тільки коли дія на СЕБЕ, не на інших!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Each other vs Reflexive',
                        'sections' => [
                            [
                                'label' => 'Each other — один одного (взаємно)',
                                'color' => 'blue',
                                'description' => "<strong>Each other</strong> показує взаємну дію (A → B і B → A). <strong>Reflexive</strong> — дія на себе (A → A).",
                                'examples' => [
                                    ['en' => 'They love each other.', 'ua' => 'Вони люблять один одного. (взаємно)'],
                                    ['en' => 'They love themselves.', 'ua' => 'Вони люблять себе. (кожен себе)'],
                                    ['en' => 'We help each other.', 'ua' => 'Ми допомагаємо один одному.'],
                                    ['en' => 'We help ourselves.', 'ua' => 'Ми допомагаємо собі (самим).'],
                                ],
                                'note' => '📌 Each other = взаємно. Reflexive = сам собі.',
                            ],
                            [
                                'label' => 'Приклади відмінності',
                                'color' => 'sky',
                                'description' => "Контекст показує різницю:",
                                'examples' => [
                                    ['en' => 'They looked at themselves in the mirror.', 'ua' => 'Вони подивилися на себе в дзеркало (кожен на себе).'],
                                    ['en' => 'They looked at each other.', 'ua' => 'Вони подивилися один на одного.'],
                                    ['en' => 'Talk to yourself! (reflexive)', 'ua' => 'Поговори сам із собою!'],
                                    ['en' => 'Talk to each other! (reciprocal)', 'ua' => 'Поговоріть один з одним!'],
                                ],
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
                                'title' => "Зайвий reflexive з feel, meet, wash.",
                                'wrong' => 'I feel myself good. / We met ourselves at 5.',
                                'right' => '✅ <span class="font-mono">I feel good. / We met at 5.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Неправильна форма (hisself, theirselves).",
                                'wrong' => 'He did it hisself. / They enjoyed theirselves.',
                                'right' => '✅ <span class="font-mono">He did it himself. / They enjoyed themselves.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Плутанина themselves і each other.",
                                'wrong' => 'They love themselves. (коли взаємно)',
                                'right' => '✅ <span class="font-mono">They love each other.</span> (один одного)',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Пропуск reflexive, коли він потрібен.",
                                'wrong' => 'I hurt yesterday. / She taught Spanish.',
                                'right' => '✅ <span class="font-mono">I hurt myself. / She taught herself Spanish.</span>',
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
                            "<strong>Reflexive pronouns:</strong> myself, yourself, himself, herself, itself, ourselves, yourselves, themselves.",
                            "<strong>Форма:</strong> -self (однина), -selves (множина).",
                            "<strong>Reflexive use:</strong> коли підмет = об'єкт (дія на себе): I hurt myself.",
                            "<strong>Emphatic use:</strong> для підсилення (сам, сама): I did it myself.",
                            "<strong>By myself</strong> = самостійно або наодинці.",
                            "<strong>For myself</strong> = для себе.",
                            "Деякі дієслова <strong>НЕ потребують reflexive:</strong> feel, meet, get dressed, wash, shave.",
                            "<strong>Each other</strong> = один одного (взаємно). <strong>Reflexive</strong> = себе.",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Практика',
                        'select_title' => 'Вправа 1. Обери правильну форму',
                        'select_intro' => 'Заповни пропуски правильним зворотним займенником або залиш без нього.',
                        'selects' => [
                            ['label' => 'I hurt ___ playing football. (myself / me)', 'prompt' => 'Яка форма?'],
                            ['label' => 'She taught ___ Spanish. (her / herself)', 'prompt' => 'Яка форма?'],
                            ['label' => 'I feel ___ good today. (—  / myself)', 'prompt' => 'Потрібен reflexive?'],
                            ['label' => 'We enjoyed ___ at the party. (us / ourselves)', 'prompt' => 'Яка форма?'],
                            ['label' => 'They met ___ at the station. (— / themselves)', 'prompt' => 'Потрібен reflexive?'],
                        ],
                        'options' => ['myself', 'yourself', 'himself', 'herself', 'ourselves', 'themselves', '—'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний зворотний займенник.',
                        'inputs' => [
                            ['before' => 'I did it ___. (я сам)', 'after' => '→'],
                            ['before' => 'She cut ___ with a knife. (порізалася)', 'after' => '→'],
                            ['before' => 'They introduced ___. (представилися)', 'after' => '→'],
                            ['before' => 'We enjoyed ___ at the concert. (добре провели час)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку з зворотними займенниками.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I feel myself good.',
                                'example_target' => 'I feel good.',
                            ],
                            [
                                'original' => '1. He did it hisself.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. They enjoyed theirselves.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. We met ourselves at 5.',
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
                                'label' => 'Possessive Adjectives vs Pronouns — my / mine',
                                'current' => false,
                            ],
                            [
                                'label' => 'Indefinite Pronouns — someone, anyone, nobody',
                                'current' => false,
                            ],
                            [
                                'label' => 'Reflexive Pronouns — myself, yourself, themselves (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
