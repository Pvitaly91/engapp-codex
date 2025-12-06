<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class RelativePronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'relative-pronouns-who-which-that-whose';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Relative Pronouns — who, which, that, whose',
            'subtitle_html' => "<p><strong>Relative pronouns</strong> (відносні займенники) — це займенники, що з'єднують частини складного речення та дають додаткову інформацію про іменник. Основні: <strong>who</strong> (для людей), <strong>which</strong> (для речей), <strong>that</strong> (універсальний), <strong>whose</strong> (чий), <strong>whom</strong> (кого — формально).</p>",
            'subtitle_text' => "Теоретичний огляд відносних займенників англійської мови: who, which, that, whose, whom — правила вживання, defining vs non-defining clauses, та типові помилки.",
            'locale' => 'uk',
            'category' => [
                'slug' => '3',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Relative Pronouns',
                'Who',
                'Which',
                'That',
                'Whose',
                'Whom',
                'Relative Clauses',
                'Defining Clauses',
                'Non-defining Clauses',
                'Pronouns',
                'Grammar',
                'Theory',
                'B1',
                'B2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => "У цій темі ти вивчиш <strong>відносні займенники</strong> — слова, що з'єднують речення та дають додаткову інформацію.",
                        'rules' => [
                            [
                                'label' => 'WHO',
                                'color' => 'emerald',
                                'text' => '<strong>Who</strong> — для людей (підмет/додаток):',
                                'example' => 'The man who called is my boss.',
                            ],
                            [
                                'label' => 'WHICH',
                                'color' => 'blue',
                                'text' => '<strong>Which</strong> — для речей і тварин:',
                                'example' => 'The book which I read was great.',
                            ],
                            [
                                'label' => 'THAT',
                                'color' => 'amber',
                                'text' => '<strong>That</strong> — універсальний (люди/речі):',
                                'example' => 'The car that he bought is new.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => "1. П'ять основних відносних займенників",
                        'intro' => "Кожен відносний займенник має своє використання:",
                        'items' => [
                            ['label' => 'WHO', 'title' => 'Хто (люди)', 'subtitle' => 'підмет або додаток — The woman who lives here...'],
                            ['label' => 'WHICH', 'title' => 'Який (речі)', 'subtitle' => 'речі та тварини — The car which is red...'],
                            ['label' => 'THAT', 'title' => 'Що/який (універсальний)', 'subtitle' => 'люди або речі — The book that I read...'],
                            ['label' => 'WHOSE', 'title' => 'Чий (приналежність)', 'subtitle' => 'показує володіння — The man whose car was stolen...'],
                            ['label' => 'WHOM', 'title' => 'Кого (формально)', 'subtitle' => 'додаток (формальна мова) — The person whom I met...'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. WHO — для людей',
                        'sections' => [
                            [
                                'label' => 'Who як підмет',
                                'color' => 'emerald',
                                'description' => "<strong>Who</strong> використовується для людей. Може бути <strong>підметом</strong> у відносному реченні.",
                                'examples' => [
                                    ['en' => 'The man who called is my boss.', 'ua' => 'Чоловік, який телефонував, — мій бос.'],
                                    ['en' => 'The woman who lives here is a doctor.', 'ua' => 'Жінка, яка тут живе, — лікар.'],
                                    ['en' => 'People who exercise regularly are healthier.', 'ua' => 'Люди, які регулярно займаються спортом, здоровіші.'],
                                    ['en' => 'The teacher who taught me was excellent.', 'ua' => 'Вчитель, який мене навчав, був чудовим.'],
                                ],
                            ],
                            [
                                'label' => 'Who як додаток',
                                'color' => 'sky',
                                'description' => "<strong>Who</strong> може бути <strong>додатком</strong> (неформально замість whom).",
                                'examples' => [
                                    ['en' => 'The man who I met was friendly.', 'ua' => 'Чоловік, якого я зустрів, був дружелюбним.'],
                                    ['en' => 'The person who you called is here.', 'ua' => 'Людина, якій ти телефонував, тут.'],
                                    ['en' => 'The woman who we saw yesterday is my neighbor.', 'ua' => 'Жінка, яку ми бачили вчора, — моя сусідка.'],
                                ],
                                'note' => '📌 Who замість whom — неформально, але дуже поширено!',
                            ],
                            [
                                'label' => 'Who НЕ для тварин/речей',
                                'color' => 'purple',
                                'description' => "<strong>Who</strong> тільки для людей! Для тварин і речей — which або that.",
                                'examples' => [
                                    ['en' => 'The dog who barks... (✗)', 'ua' => 'ПОМИЛКА'],
                                    ['en' => 'The dog that/which barks... (✓)', 'ua' => 'Собака, який гавкає...'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. WHICH — для речей і тварин',
                        'sections' => [
                            [
                                'label' => 'Which для речей',
                                'color' => 'blue',
                                'description' => "<strong>Which</strong> використовується для речей та тварин (не для людей).",
                                'examples' => [
                                    ['en' => 'The book which I read was great.', 'ua' => 'Книга, яку я прочитав, була чудова.'],
                                    ['en' => 'The car which is red is mine.', 'ua' => 'Машина, яка червона, — моя.'],
                                    ['en' => 'The house which we bought is old.', 'ua' => 'Будинок, який ми купили, старий.'],
                                    ['en' => 'The phone which I want costs a lot.', 'ua' => 'Телефон, який я хочу, коштує дорого.'],
                                ],
                            ],
                            [
                                'label' => 'Which для тварин',
                                'color' => 'sky',
                                'description' => "Which також використовується для тварин.",
                                'examples' => [
                                    ['en' => 'The dog which lives next door is friendly.', 'ua' => 'Собака, яка живе по сусідству, дружелюбна.'],
                                    ['en' => 'The cat which I saw was black.', 'ua' => 'Кіт, якого я бачив, був чорний.'],
                                ],
                            ],
                            [
                                'label' => 'Which для цілого речення',
                                'color' => 'purple',
                                'description' => "<strong>Which</strong> може посилатися на всю попередню фразу (з комою!).",
                                'examples' => [
                                    ['en' => 'He passed the exam, which surprised everyone.', 'ua' => 'Він склав іспит, що всіх здивувало.'],
                                    ['en' => 'She arrived late, which made me angry.', 'ua' => 'Вона прийшла пізно, що мене розлютило.'],
                                ],
                                'note' => '📌 , which = посилання на всю ситуацію.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. THAT — універсальний займенник',
                        'sections' => [
                            [
                                'label' => 'That для людей і речей',
                                'color' => 'amber',
                                'description' => "<strong>That</strong> — універсальний! Можна використовувати замість who або which (але не завжди!).",
                                'examples' => [
                                    ['en' => 'The man that called... = The man who called...', 'ua' => 'Чоловік, який телефонував...'],
                                    ['en' => 'The book that I read... = The book which I read...', 'ua' => 'Книга, яку я прочитав...'],
                                    ['en' => 'The car that he bought is new.', 'ua' => 'Машина, яку він купив, нова.'],
                                ],
                            ],
                            [
                                'label' => 'Коли ТРЕБА використовувати that',
                                'color' => 'emerald',
                                'description' => "Після <strong>superlatives, all, every, only, first/last</strong> краще використовувати that.",
                                'examples' => [
                                    ['en' => "It's the best movie that I've ever seen.", 'ua' => 'Це найкращий фільм, який я коли-небудь бачив.'],
                                    ['en' => 'Everything that he said was true.', 'ua' => 'Усе, що він сказав, було правдою.'],
                                    ['en' => "She's the only person that can help.", 'ua' => 'Вона єдина людина, яка може допомогти.'],
                                    ['en' => "It's the first time that I've been here.", 'ua' => 'Це перший раз, коли я тут.'],
                                ],
                                'note' => '📌 best, all, every, only, first → використовуй that!',
                            ],
                            [
                                'label' => 'Коли НЕ МОЖНА використовувати that',
                                'color' => 'rose',
                                'description' => "That <strong>НЕ можна</strong> використовувати з комою (non-defining clauses).",
                                'examples' => [
                                    ['en' => 'My brother, who lives in Kyiv, is a doctor. (✓)', 'ua' => 'Мій брат, який живе в Києві, лікар.'],
                                    ['en' => 'My brother, that lives in Kyiv, is a doctor. (✗)', 'ua' => 'ПОМИЛКА з that після коми!'],
                                ],
                                'note' => '📌 З комою — тільки who/which, НЕ that!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. WHOSE — чий (приналежність)',
                        'sections' => [
                            [
                                'label' => 'Whose показує володіння',
                                'color' => 'purple',
                                'description' => "<strong>Whose</strong> (чий) показує приналежність. Використовується для людей і речей.",
                                'examples' => [
                                    ['en' => 'The man whose car was stolen called the police.', 'ua' => 'Чоловік, чию машину вкрали, викликав поліцію.'],
                                    ['en' => 'The woman whose daughter is a doctor lives here.', 'ua' => 'Жінка, чия донька лікар, живе тут.'],
                                    ['en' => 'I know someone whose brother is famous.', 'ua' => 'Я знаю когось, чий брат відомий.'],
                                ],
                            ],
                            [
                                'label' => 'Whose + noun',
                                'color' => 'blue',
                                'description' => "Після <strong>whose</strong> завжди йде <strong>іменник</strong>.",
                                'examples' => [
                                    ['en' => 'The boy whose bike was stolen... (✓)', 'ua' => 'Хлопець, чий велосипед вкрали...'],
                                    ['en' => 'The boy whose was stolen... (✗)', 'ua' => 'ПОМИЛКА — потрібен іменник!'],
                                ],
                                'note' => '📌 Whose завжди + noun (чий велосипед, чия сестра).',
                            ],
                            [
                                'label' => "Не плутай whose і who's",
                                'color' => 'amber',
                                'description' => "<strong>Whose</strong> (чий) ≠ <strong>who's</strong> (who is/has).",
                                'examples' => [
                                    ['en' => 'The man whose car... (possessive)', 'ua' => 'Чоловік, чия машина...'],
                                    ['en' => "The man who's calling... (who is)", 'ua' => 'Чоловік, який телефонує...'],
                                ],
                                'note' => "📌 Whose = чий. Who's = who is.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. WHOM — формальна мова',
                        'sections' => [
                            [
                                'label' => 'Whom як додаток (формально)',
                                'color' => 'sky',
                                'description' => "<strong>Whom</strong> — формальний варіант who як додаток. У розмовній мові зазвичай використовують who.",
                                'examples' => [
                                    ['en' => 'The person whom I met was kind. (formal)', 'ua' => 'Людина, яку я зустрів, була доброю.'],
                                    ['en' => 'The person who I met was kind. (informal)', 'ua' => 'Те саме, але розмовний варіант.'],
                                ],
                            ],
                            [
                                'label' => 'Whom після прийменників',
                                'color' => 'purple',
                                'description' => "Після прийменників (with, to, for) формально використовується <strong>whom</strong>.",
                                'examples' => [
                                    ['en' => 'The person to whom I spoke... (formal)', 'ua' => 'Людина, з якою я розмовляв...'],
                                    ['en' => 'The person who I spoke to... (informal)', 'ua' => 'Те саме, розмовно.'],
                                ],
                                'note' => '📌 У розмовній мові whom майже не використовується.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Defining vs Non-defining clauses',
                        'sections' => [
                            [
                                'label' => 'Defining — без ком',
                                'color' => 'emerald',
                                'description' => "<strong>Defining clauses</strong> — важлива інформація (без неї речення незрозуміле). Без ком!",
                                'examples' => [
                                    ['en' => 'The man who called is my boss.', 'ua' => 'Чоловік, який телефонував, — мій бос. (який саме?)'],
                                    ['en' => 'The book that I read was great.', 'ua' => 'Книга, яку я прочитав, була чудова. (яка книга?)'],
                                ],
                                'note' => '📌 Defining = важлива інформація. Без ком. Можна that.',
                            ],
                            [
                                'label' => 'Non-defining — з комами',
                                'color' => 'blue',
                                'description' => "<strong>Non-defining clauses</strong> — додаткова інформація (можна прибрати). З комами!",
                                'examples' => [
                                    ['en' => 'My brother, who lives in Kyiv, is a doctor.', 'ua' => 'Мій брат, який живе в Києві, лікар.'],
                                    ['en' => 'London, which is the capital, is big.', 'ua' => 'Лондон, який є столицею, великий.'],
                                ],
                                'note' => '📌 Non-defining = додаткова інформація. З комами. Тільки who/which!',
                            ],
                            [
                                'label' => 'Ключові відмінності',
                                'color' => 'amber',
                                'description' => "Defining (без ком) — необхідна інформація. Non-defining (з комами) — додаткова.",
                                'examples' => [
                                    ['en' => 'Students who work hard pass exams. (не всі, тільки ті, хто працює)', 'ua' => 'defining'],
                                    ['en' => 'My students, who work hard, pass exams. (усі мої студенти)', 'ua' => 'non-defining'],
                                ],
                                'note' => 'Коми змінюють сенс речення!',
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
                                'title' => "That після коми (non-defining).",
                                'wrong' => 'My brother, that lives in Kyiv, is a doctor.',
                                'right' => '✅ <span class="font-mono">My brother, who lives in Kyiv, is a doctor.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Who для речей або тварин.",
                                'wrong' => 'The book who I read... / The dog who barks...',
                                'right' => '✅ <span class="font-mono">The book that/which I read... / The dog that/which barks...</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Плутанина whose і who's.",
                                'wrong' => "The man who's car was stolen... (who is car?)",
                                'right' => '✅ <span class="font-mono">The man whose car was stolen...</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Зайвий займенник після relative pronoun.",
                                'wrong' => 'The man who he called is my boss.',
                                'right' => '✅ <span class="font-mono">The man who called is my boss.</span>',
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
                            "<strong>Who</strong> — для людей (підмет/додаток): The man who called...",
                            "<strong>Which</strong> — для речей і тварин: The book which I read...",
                            "<strong>That</strong> — універсальний (люди/речі), але НЕ після коми!",
                            "<strong>Whose</strong> — чий (приналежність): The man whose car...",
                            "<strong>Whom</strong> — кого (формально, замість who як додаток).",
                            "<strong>Defining clauses</strong> — без ком (важлива інформація). Можна that.",
                            "<strong>Non-defining clauses</strong> — з комами (додаткова інформація). Тільки who/which!",
                            "Після <strong>best, all, every, only, first</strong> краще використовувати <strong>that</strong>.",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний займенник',
                        'select_intro' => 'Заповни пропуски правильним відносним займенником.',
                        'selects' => [
                            ['label' => 'The man ___ called is my boss. (who / which)', 'prompt' => 'Який займенник?'],
                            ['label' => 'The book ___ I read was great. (who / which)', 'prompt' => 'Який займенник?'],
                            ['label' => 'The woman ___ car was stolen... (whose / who)', 'prompt' => 'Який займенник?'],
                            ['label' => "It's the best film ___ I've seen. (that / which)", 'prompt' => 'Який займенник?'],
                            ['label' => 'My brother, ___ lives in Kyiv... (that / who)', 'prompt' => 'Який займенник?'],
                        ],
                        'options' => ['who', 'which', 'that', 'whose', 'whom'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний відносний займенник.',
                        'inputs' => [
                            ['before' => 'The woman ___ called is a doctor. (жінка, яка)', 'after' => '→'],
                            ['before' => 'The car ___ I bought is new. (машина, яку)', 'after' => '→'],
                            ['before' => 'The man ___ dog is big... (чоловік, чий собака)', 'after' => '→'],
                            ['before' => 'Everything ___ he said was true. (усе, що)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку з відносними займенниками.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'The book who I read...',
                                'example_target' => 'The book which/that I read...',
                            ],
                            [
                                'original' => '1. My brother, that lives in Kyiv, is a doctor.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. The dog who barks is mine.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => "3. The man who's car was stolen...",
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
                                'label' => 'Possessive Adjectives vs Pronouns',
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
                                'label' => 'Relative Pronouns — who, which, that, whose (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
