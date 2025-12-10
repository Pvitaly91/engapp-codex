<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Pages\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsWhQuestionsSpecialQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'wh-questions-special-questions-who-what-where-when-why-how';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Wh-questions (Special Questions) — Спеціальні питання: who, what, where, when, why, how',
            'subtitle_html' => '<p><strong>Wh-questions</strong> (спеціальні питання) — це питання, які починаються з питальних слів (who, what, where, when, why, how) і потребують конкретної інформації у відповіді, а не просто "так" чи "ні".</p>',
            'subtitle_text' => 'Теоретичний огляд спеціальних питань (Wh-questions) в англійській мові: питальні слова who, what, where, when, why, how та правила їх використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'types-of-questions',
                'title' => 'Види питальних речень',
                'language' => 'uk',
            ],
            'tags' => [
                'Wh-Questions',
                'Special Questions',
                'Спеціальні питання',
                'Question Words',
                'Who',
                'What',
                'Where',
                'When',
                'Why',
                'How',
                'Grammar',
                'Theory',
                'A1',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цій темі ти вивчиш <strong>спеціальні питання (Wh-questions)</strong> — питання, які починаються з питальних слів і вимагають конкретної інформації у відповіді.',
                        'rules' => [
                            [
                                'label' => 'WHO / WHAT',
                                'color' => 'emerald',
                                'text' => '<strong>Хто? Що?</strong> — питання про людей та речі:',
                                'example' => 'Who is she? What do you want?',
                            ],
                            [
                                'label' => 'WHERE / WHEN',
                                'color' => 'blue',
                                'text' => '<strong>Де? Коли?</strong> — питання про місце та час:',
                                'example' => 'Where do you live? When does it start?',
                            ],
                            [
                                'label' => 'WHY / HOW',
                                'color' => 'amber',
                                'text' => '<strong>Чому? Як?</strong> — питання про причину та спосіб:',
                                'example' => 'Why are you late? How does it work?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '1. Що таке Wh-questions?',
                        'intro' => 'Wh-questions (спеціальні питання) — це питання з питальними словами, які потребують детальної відповіді:',
                        'items' => [
                            ['label' => 'Загальне питання', 'title' => 'Do you like coffee?', 'subtitle' => 'Відповідь: Yes/No'],
                            ['label' => 'Спеціальне питання', 'title' => 'What do you like?', 'subtitle' => 'Відповідь: I like tea.'],
                            ['label' => 'Структура', 'title' => 'Wh-word + Auxiliary + Subject + Verb', 'subtitle' => 'Питальне слово завжди на початку'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. WHO — Хто? (про людей)',
                        'sections' => [
                            [
                                'label' => 'WHO — питання про людину',
                                'color' => 'emerald',
                                'description' => '<strong>Who</strong> (хто) — використовуємо, коли запитуємо про людину як додаток.',
                                'examples' => [
                                    ['en' => 'Who do you love?', 'ua' => 'Кого ти кохаєш?'],
                                    ['en' => 'Who did you see?', 'ua' => 'Кого ти бачив?'],
                                    ['en' => 'Who are you talking to?', 'ua' => 'З ким ти розмовляєш?'],
                                    ['en' => 'Who does she work with?', 'ua' => 'З ким вона працює?'],
                                ],
                            ],
                            [
                                'label' => 'WHO — питання до підмета',
                                'color' => 'sky',
                                'description' => 'Коли <strong>who</strong> є підметом, НЕ використовуємо допоміжне дієслово do/does/did.',
                                'examples' => [
                                    ['en' => 'Who lives here?', 'ua' => 'Хто тут живе?'],
                                    ['en' => 'Who called you?', 'ua' => 'Хто тобі телефонував?'],
                                    ['en' => 'Who wants coffee?', 'ua' => 'Хто хоче кави?'],
                                    ['en' => 'Who knows the answer?', 'ua' => 'Хто знає відповідь?'],
                                ],
                            ],
                            [
                                'label' => 'WHO — з різними часами',
                                'color' => 'purple',
                                'description' => 'Who може використовуватися з різними часовими формами.',
                                'examples' => [
                                    ['en' => 'Who is coming to the party?', 'ua' => 'Хто йде на вечірку?'],
                                    ['en' => 'Who has finished the work?', 'ua' => 'Хто закінчив роботу?'],
                                    ['en' => 'Who will help me?', 'ua' => 'Хто мені допоможе?'],
                                ],
                                'note' => '📌 Структура: Who + Auxiliary + Subject + Verb? (або Who + Verb? для питань до підмета)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '3. WHAT — Що? (про речі та інформацію)',
                        'sections' => [
                            [
                                'label' => 'WHAT — загальні питання',
                                'color' => 'blue',
                                'description' => '<strong>What</strong> (що) — використовуємо для запитання про речі, дії або інформацію.',
                                'examples' => [
                                    ['en' => 'What do you want?', 'ua' => 'Що ти хочеш?'],
                                    ['en' => 'What did she say?', 'ua' => 'Що вона сказала?'],
                                    ['en' => 'What are you doing?', 'ua' => 'Що ти робиш?'],
                                    ['en' => 'What is your name?', 'ua' => "Як тебе звати? (Що є твоє ім'я?)"],
                                ],
                            ],
                            [
                                'label' => 'WHAT + іменник',
                                'color' => 'amber',
                                'description' => '<strong>What</strong> може використовуватися разом з іменником для уточнення.',
                                'examples' => [
                                    ['en' => 'What colour is it?', 'ua' => 'Якого це кольору?'],
                                    ['en' => 'What time is it?', 'ua' => 'Котра година?'],
                                    ['en' => 'What kind of music do you like?', 'ua' => 'Яку музику ти любиш?'],
                                    ['en' => 'What size do you need?', 'ua' => 'Який розмір тобі потрібен?'],
                                ],
                            ],
                            [
                                'label' => 'WHAT — питання до підмета',
                                'color' => 'rose',
                                'description' => 'Коли <strong>what</strong> є підметом, не використовуємо do/does/did.',
                                'examples' => [
                                    ['en' => 'What happened?', 'ua' => 'Що сталося?'],
                                    ['en' => 'What makes you happy?', 'ua' => 'Що робить тебе щасливим?'],
                                    ['en' => 'What caused the problem?', 'ua' => 'Що спричинило проблему?'],
                                ],
                                'note' => '📌 What happened? — НЕ What did happen?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '4. WHERE — Де? (про місце)',
                        'sections' => [
                            [
                                'label' => 'WHERE — питання про місце',
                                'color' => 'emerald',
                                'description' => '<strong>Where</strong> (де, куди) — використовуємо для запитання про місце або напрямок.',
                                'examples' => [
                                    ['en' => 'Where do you live?', 'ua' => 'Де ти живеш?'],
                                    ['en' => 'Where are you going?', 'ua' => 'Куди ти йдеш?'],
                                    ['en' => 'Where did you buy it?', 'ua' => 'Де ти це купив?'],
                                    ['en' => 'Where is the bank?', 'ua' => 'Де знаходиться банк?'],
                                ],
                            ],
                            [
                                'label' => 'WHERE — з прийменниками',
                                'color' => 'sky',
                                'description' => 'Where може використовуватися з прийменниками <strong>from, to, at</strong> тощо.',
                                'examples' => [
                                    ['en' => 'Where are you from?', 'ua' => 'Звідки ти?'],
                                    ['en' => 'Where are you going to?', 'ua' => 'Куди ти збираєшся?'],
                                    ['en' => 'Where did she come from?', 'ua' => 'Звідки вона прийшла?'],
                                ],
                                'note' => '📌 Структура: Where + Auxiliary + Subject + Verb?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '5. WHEN — Коли? (про час)',
                        'sections' => [
                            [
                                'label' => 'WHEN — питання про час',
                                'color' => 'blue',
                                'description' => '<strong>When</strong> (коли) — використовуємо для запитання про час події.',
                                'examples' => [
                                    ['en' => 'When do you wake up?', 'ua' => 'Коли ти прокидаєшся?'],
                                    ['en' => 'When did it happen?', 'ua' => 'Коли це сталося?'],
                                    ['en' => 'When are you leaving?', 'ua' => 'Коли ти виїжджаєш?'],
                                    ['en' => 'When will you come back?', 'ua' => 'Коли ти повернешся?'],
                                ],
                            ],
                            [
                                'label' => 'WHEN — різні часові форми',
                                'color' => 'purple',
                                'description' => 'When працює з усіма часовими формами.',
                                'examples' => [
                                    ['en' => 'When is your birthday?', 'ua' => 'Коли твій день народження?'],
                                    ['en' => 'When does the meeting start?', 'ua' => 'Коли починається зустріч?'],
                                    ['en' => 'When have you been there?', 'ua' => 'Коли ти там був?'],
                                ],
                                'note' => '📌 Відповідь може бути конкретною (at 5 pm) або загальною (tomorrow, yesterday)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. WHY — Чому? (про причину)',
                        'sections' => [
                            [
                                'label' => 'WHY — питання про причину',
                                'color' => 'amber',
                                'description' => '<strong>Why</strong> (чому) — використовуємо для запитання про причину або мету.',
                                'examples' => [
                                    ['en' => 'Why are you sad?', 'ua' => 'Чому ти сумний?'],
                                    ['en' => 'Why did she leave?', 'ua' => 'Чому вона пішла?'],
                                    ['en' => 'Why do you study English?', 'ua' => 'Чому ти вчиш англійську?'],
                                    ['en' => 'Why is this important?', 'ua' => 'Чому це важливо?'],
                                ],
                            ],
                            [
                                'label' => 'WHY — відповідь з BECAUSE',
                                'color' => 'rose',
                                'description' => 'На питання <strong>why</strong> зазвичай відповідаємо з <strong>because</strong> (тому що).',
                                'examples' => [
                                    ['en' => 'Why are you late? — Because I missed the bus.', 'ua' => 'Чому ти спізнився? — Бо я пропустив автобус.'],
                                    ['en' => 'Why is she angry? — Because he forgot her birthday.', 'ua' => 'Чому вона сердита? — Бо він забув її день народження.'],
                                ],
                            ],
                            [
                                'label' => 'WHY — пропозиції та поради',
                                'color' => 'emerald',
                                'description' => 'Why може використовуватися для пропозицій або порад у конструкції <strong>Why don\'t we/you...?</strong>',
                                'examples' => [
                                    ['en' => "Why don't we go to the cinema?", 'ua' => 'Чому б нам не піти в кіно?'],
                                    ['en' => "Why don't you ask him?", 'ua' => 'Чому б тобі його не запитати?'],
                                ],
                                'note' => '📌 Why don\'t we/you...? = пропозиція або порада',
                            ],
                            [
                                'label' => 'WHY — з модальними дієсловами',
                                'color' => 'purple',
                                'description' => 'Why може використовуватися з модальними дієсловами <strong>(should, can, could, would, must)</strong> для запитання про причину обов\'язку, можливості або умови.',
                                'examples' => [
                                    ['en' => 'Why should I believe you?', 'ua' => 'Чому я маю тобі вірити?'],
                                    ['en' => 'Why would she do that?', 'ua' => 'Навіщо їй це робити?'],
                                    ['en' => 'Why can\'t we stay longer?', 'ua' => 'Чому ми не можемо залишитися довше?'],
                                    ['en' => 'Why must you leave now?', 'ua' => 'Чому ти маєш йти зараз?'],
                                ],
                                'note' => '📌 Структура: Why + Modal Verb + Subject + Main Verb?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. HOW — Як? (про спосіб та ступінь)',
                        'sections' => [
                            [
                                'label' => 'HOW — питання про спосіб',
                                'color' => 'blue',
                                'description' => '<strong>How</strong> (як) — використовуємо для запитання про спосіб, метод або стан.',
                                'examples' => [
                                    ['en' => 'How do you go to work?', 'ua' => 'Як ти їдеш на роботу?'],
                                    ['en' => 'How does it work?', 'ua' => 'Як це працює?'],
                                    ['en' => 'How are you?', 'ua' => 'Як ти? (Як справи?)'],
                                    ['en' => 'How did you learn English?', 'ua' => 'Як ти вивчив англійську?'],
                                ],
                            ],
                            [
                                'label' => 'HOW + прикметник/прислівник',
                                'color' => 'purple',
                                'description' => '<strong>How</strong> комбінується з прикметниками для запитання про ступінь, кількість, відстань.',
                                'examples' => [
                                    ['en' => 'How old are you?', 'ua' => 'Скільки тобі років?'],
                                    ['en' => 'How tall is he?', 'ua' => 'Якого він зросту?'],
                                    ['en' => 'How far is the station?', 'ua' => 'Як далеко станція?'],
                                    ['en' => 'How long does it take?', 'ua' => 'Скільки це займає часу?'],
                                ],
                            ],
                            [
                                'label' => 'HOW MUCH / HOW MANY',
                                'color' => 'emerald',
                                'description' => '<strong>How much</strong> (незлічувані) / <strong>How many</strong> (злічувані) — для запитання про кількість.',
                                'examples' => [
                                    ['en' => 'How much is it?', 'ua' => 'Скільки це коштує?'],
                                    ['en' => 'How many books do you have?', 'ua' => 'Скільки у тебе книг?'],
                                    ['en' => 'How much time do we have?', 'ua' => 'Скільки у нас є часу?'],
                                    ['en' => 'How many people are coming?', 'ua' => 'Скільки людей приходить?'],
                                ],
                                'note' => '📌 How much — з незлічуваними іменниками, How many — зі злічуваними',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Порівняльна таблиця питальних слів',
                        'intro' => 'Основні питальні слова та їх значення:',
                        'rows' => [
                            [
                                'en' => 'WHO',
                                'ua' => 'Хто? Кого?',
                                'note' => 'Про людей — Who is she? Who did you see?',
                            ],
                            [
                                'en' => 'WHAT',
                                'ua' => 'Що? Який?',
                                'note' => 'Про речі, інформацію — What do you want? What time?',
                            ],
                            [
                                'en' => 'WHERE',
                                'ua' => 'Де? Куди? Звідки?',
                                'note' => 'Про місце — Where do you live? Where from?',
                            ],
                            [
                                'en' => 'WHEN',
                                'ua' => 'Коли?',
                                'note' => 'Про час — When did it happen? When are you coming?',
                            ],
                            [
                                'en' => 'WHY',
                                'ua' => 'Чому?',
                                'note' => 'Про причину — Why are you late? → Because...',
                            ],
                            [
                                'en' => 'HOW',
                                'ua' => 'Як? Яким чином?',
                                'note' => 'Про спосіб — How do you do it? How old? How much?',
                            ],
                        ],
                        'warning' => '📌 Структура: <strong>Wh-word + Auxiliary + Subject + Verb + ...?</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Структура Wh-questions',
                        'sections' => [
                            [
                                'label' => 'Стандартна структура',
                                'color' => 'emerald',
                                'description' => 'Загальна формула для більшості Wh-questions:',
                                'examples' => [
                                    ['en' => 'Wh-word + Auxiliary + Subject + Main Verb + ...?', 'ua' => 'Питальне слово + Допоміжне + Підмет + Дієслово + ...?'],
                                    ['en' => 'What do you want?', 'ua' => 'Що (do) ти (want) хочеш?'],
                                    ['en' => 'Where did she go?', 'ua' => 'Куди (did) вона (go) пішла?'],
                                ],
                            ],
                            [
                                'label' => 'Питання до підмета',
                                'color' => 'sky',
                                'description' => 'Коли питальне слово є підметом, НЕ використовуємо do/does/did:',
                                'examples' => [
                                    ['en' => 'Who lives here? (NOT: Who does live here?)', 'ua' => 'Хто тут живе?'],
                                    ['en' => 'What happened? (NOT: What did happen?)', 'ua' => 'Що сталося?'],
                                ],
                                'note' => '📌 Структура: Wh-word (subject) + Verb + ...?',
                            ],
                            [
                                'label' => 'З дієсловом TO BE',
                                'color' => 'amber',
                                'description' => 'З to be робимо інверсію без do/does/did:',
                                'examples' => [
                                    ['en' => 'Where are you?', 'ua' => 'Де ти?'],
                                    ['en' => 'Who is she?', 'ua' => 'Хто вона?'],
                                    ['en' => 'What is your name?', 'ua' => "Як тебе звати?"],
                                ],
                                'note' => '📌 Структура: Wh-word + be + Subject + ...?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '10. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів у питанні.',
                                'wrong' => '❌ What you want? Where you live?',
                                'right' => '✅ <span class="font-mono">What do you want? Where do you live?</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання do/does/did у питаннях до підмета.',
                                'wrong' => '❌ Who does live here? What did happen?',
                                'right' => '✅ <span class="font-mono">Who lives here? What happened?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма дієслова після допоміжного.',
                                'wrong' => '❌ Where does she lives? What did you saw?',
                                'right' => '✅ <span class="font-mono">Where does she live? What did you see?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Плутанина між How much та How many.',
                                'wrong' => '❌ How many money? How much people?',
                                'right' => '✅ <span class="font-mono">How much money? How many people?</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '11. Короткий конспект',
                        'items' => [
                            '<strong>Wh-questions</strong> — спеціальні питання, які потребують конкретної відповіді (не Yes/No).',
                            '<strong>Who</strong> (хто, кого) — про людей. Як підмет: Who lives here? Як додаток: Who do you see?',
                            '<strong>What</strong> (що, який) — про речі та інформацію. What do you want? What time is it?',
                            '<strong>Where</strong> (де, куди) — про місце. Where do you live? Where are you going?',
                            '<strong>When</strong> (коли) — про час. When did it happen? When are you leaving?',
                            '<strong>Why</strong> (чому) — про причину. Why are you sad? → відповідь з because.',
                            '<strong>How</strong> (як) — про спосіб. How do you do it? How old/much/many/far?',
                            '<strong>Структура</strong>: Wh-word + Auxiliary + Subject + Verb (окрім питань до підмета).',
                            '<strong>Питання до підмета</strong>: Wh-word + Verb (без do/does/did) — Who lives here?',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => '12. Практика',
                        'select_title' => 'Вправа 1. Обери правильне питальне слово',
                        'select_intro' => 'Обери правильне питальне слово для кожного питання.',
                        'selects' => [
                            ['label' => '_____ is your name? (Who / What / Where)', 'prompt' => 'Яке питальне слово?'],
                            ['label' => '_____ do you live? (When / Where / Why)', 'prompt' => 'Яке питальне слово?'],
                            ['label' => '_____ are you learning English? (How / Why / What)', 'prompt' => 'Яке питальне слово?'],
                            ['label' => '_____ old are you? (What / Where / How)', 'prompt' => 'Яке питальне слово?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант', 'Третій варіант'],
                        'input_title' => 'Вправа 2. Постав питання',
                        'input_intro' => 'Утвори питання, використовуючи подане питальне слово.',
                        'inputs' => [
                            ['before' => 'You live in Kyiv. (Where) → ', 'after' => ''],
                            ['before' => 'She is 25 years old. (How old) → ', 'after' => ''],
                            ['before' => 'He went to the park. (Where) → ', 'after' => ''],
                            ['before' => 'They arrived at 5 pm. (When) → ', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у спеціальному питанні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'What you want?',
                                'example_target' => 'What do you want?',
                            ],
                            [
                                'original' => '1. Where she lives?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Who does live here?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. How many money do you have?',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Види питальних речень',
                        'items' => [
                            [
                                'label' => 'Yes/No Questions — Загальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Subject Questions — Питання до підмета',
                                'current' => false,
                            ],
                            [
                                'label' => 'Alternative Questions — Альтернативні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Question Tags — Розділові питання',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
