<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Page_v2\QuestionsNegations\QuestionsNegationsPageSeeder;

class QuestionsNegationsQuestionFormsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'question-forms';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Question Forms — Як ставити запитання',
            'subtitle_html' => '<p><strong>Question Forms</strong> (форми питань) — це основи того, як правильно будувати питальні речення в англійській мові. Ти вивчиш основні типи питань, порядок слів та правила використання допоміжних дієслів для формування питань.</p>',
            'subtitle_text' => 'Теоретичний огляд форм питань в англійській мові: типи питань, порядок слів, допоміжні дієслова, правила формування.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pytalni-rechennia-ta-zaperechennia',
                'title' => 'Питальні речення та заперечення',
                'language' => 'uk',
            ],
            'tags' => [
                'Question Forms',
                'Форми питань',
                'Question Structure',
                'Question Types',
                'Yes/No Questions',
                'Wh-Questions',
                'Auxiliary Verbs',
                'Word Order',
                'Do/Does/Did',
                'To Be',
                'Modal Verbs',
                'Grammar',
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
                        'intro' => 'У цій темі ти вивчиш <strong>форми питань</strong> (question forms) в англійській мові — основні типи питальних речень та правила їх побудови.',
                        'rules' => [
                            [
                                'label' => 'YES/NO QUESTIONS',
                                'color' => 'emerald',
                                'text' => '<strong>Загальні питання</strong> — відповідь "так" або "ні":',
                                'example' => 'Do you like tea? Are you ready? Can you help?',
                            ],
                            [
                                'label' => 'WH-QUESTIONS',
                                'color' => 'blue',
                                'text' => '<strong>Спеціальні питання</strong> — починаються з питального слова:',
                                'example' => 'What do you want? Where are you going? Why is she here?',
                            ],
                            [
                                'label' => 'WORD ORDER',
                                'color' => 'amber',
                                'text' => '<strong>Інверсія</strong> — допоміжне дієслово перед підметом:',
                                'example' => 'Auxiliary + Subject + Main Verb',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке форми питань?',
                        'intro' => 'В англійській мові існують різні способи ставити запитання:',
                        'items' => [
                            ['label' => 'Yes/No', 'title' => 'Загальні питання', 'subtitle' => 'Відповідь "так" або "ні" — Do you...? Is she...?'],
                            ['label' => 'Wh-Questions', 'title' => 'Спеціальні питання', 'subtitle' => 'Починаються з what, where, when, why, who, how'],
                            ['label' => 'Alternative', 'title' => 'Альтернативні питання', 'subtitle' => 'Вибір між варіантами — Tea or coffee?'],
                            ['label' => 'Tag', 'title' => 'Розділові питання', 'subtitle' => 'Питання в кінці речення — You like it, don\'t you?'],
                            ['label' => 'Subject', 'title' => 'Питання до підмета', 'subtitle' => 'Who/What як підмет — Who came? What happened?'],
                            ['label' => 'Indirect', 'title' => 'Непрямі питання', 'subtitle' => 'Ввічливіші форми — Can you tell me where...?'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Базовий порядок слів у питаннях',
                        'sections' => [
                            [
                                'label' => 'Інверсія підмета та дієслова',
                                'color' => 'emerald',
                                'description' => 'У питаннях <strong>допоміжне дієслово ставиться перед підметом</strong>. Це називається інверсією.',
                                'examples' => [
                                    ['en' => 'You are ready. → Are you ready?', 'ua' => 'Ти готовий? (інверсія are і you)'],
                                    ['en' => 'She can swim. → Can she swim?', 'ua' => 'Вона вміє плавати? (інверсія can і she)'],
                                    ['en' => 'They will come. → Will they come?', 'ua' => 'Вони прийдуть? (інверсія will і they)'],
                                ],
                            ],
                            [
                                'label' => 'Формула загального питання',
                                'color' => 'blue',
                                'description' => 'Базова структура питання в англійській мові:',
                                'examples' => [
                                    ['en' => 'Auxiliary + Subject + Main Verb + ...?', 'ua' => 'Допоміжне + Підмет + Основне дієслово + ...?'],
                                    ['en' => 'Do you speak English?', 'ua' => 'Ти розмовляєш англійською?'],
                                    ['en' => 'Is he coming tonight?', 'ua' => 'Він прийде сьогодні ввечері?'],
                                ],
                                'note' => '📌 Допоміжне дієслово завжди перед підметом у питаннях!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Yes/No Questions — Загальні питання',
                        'sections' => [
                            [
                                'label' => 'З DO/DOES/DID',
                                'color' => 'emerald',
                                'description' => 'У простих часах (Simple) використовуємо <strong>do/does/did</strong> для формування питань.',
                                'examples' => [
                                    ['en' => 'Do you like coffee?', 'ua' => 'Тобі подобається кава?'],
                                    ['en' => 'Does she work here?', 'ua' => 'Вона тут працює?'],
                                    ['en' => 'Did they arrive on time?', 'ua' => 'Вони приїхали вчасно?'],
                                ],
                            ],
                            [
                                'label' => 'З TO BE',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> робимо інверсію — ставимо be перед підметом.',
                                'examples' => [
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                    ['en' => 'Is she at home?', 'ua' => 'Вона вдома?'],
                                    ['en' => 'Were they happy?', 'ua' => 'Вони були щасливі?'],
                                ],
                            ],
                            [
                                'label' => 'З модальними дієсловами',
                                'color' => 'purple',
                                'description' => 'З <strong>модальними дієсловами</strong> (can, will, should, must, may) робимо інверсію.',
                                'examples' => [
                                    ['en' => 'Can you swim?', 'ua' => 'Ти вмієш плавати?'],
                                    ['en' => 'Will she come?', 'ua' => 'Вона прийде?'],
                                    ['en' => 'Should we wait?', 'ua' => 'Нам варто чекати?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Wh-Questions — Спеціальні питання',
                        'sections' => [
                            [
                                'label' => 'Питальні слова (Question Words)',
                                'color' => 'emerald',
                                'description' => 'Спеціальні питання починаються з <strong>питального слова</strong>:',
                                'examples' => [
                                    ['en' => 'What — Що?', 'ua' => 'What do you want? — Що ти хочеш?'],
                                    ['en' => 'Where — Де? Куди?', 'ua' => 'Where are you going? — Куди ти йдеш?'],
                                    ['en' => 'When — Коли?', 'ua' => 'When did they arrive? — Коли вони приїхали?'],
                                    ['en' => 'Why — Чому?', 'ua' => 'Why is she crying? — Чому вона плаче?'],
                                    ['en' => 'Who — Хто?', 'ua' => 'Who called you? — Хто тобі телефонував?'],
                                    ['en' => 'How — Як?', 'ua' => 'How do you know? — Звідки ти знаєш?'],
                                ],
                            ],
                            [
                                'label' => 'Структура Wh-питань',
                                'color' => 'blue',
                                'description' => 'Формула спеціального питання:',
                                'examples' => [
                                    ['en' => 'Wh-word + Auxiliary + Subject + Main Verb?', 'ua' => 'Питальне слово + Допоміжне + Підмет + Дієслово?'],
                                    ['en' => 'What do you want?', 'ua' => 'Що ти хочеш?'],
                                    ['en' => 'Where is she going?', 'ua' => 'Куди вона йде?'],
                                    ['en' => 'Why did they leave?', 'ua' => 'Чому вони пішли?'],
                                ],
                            ],
                            [
                                'label' => 'Питання до підмета (Subject Questions)',
                                'color' => 'amber',
                                'description' => 'Коли <strong>Who/What є підметом</strong>, порядок слів як у стверджувальному реченні (без do/does/did):',
                                'examples' => [
                                    ['en' => 'Who called you?', 'ua' => 'Хто тобі телефонував? (Who — підмет)'],
                                    ['en' => 'What happened?', 'ua' => 'Що сталося? (What — підмет)'],
                                    ['en' => 'Who knows the answer?', 'ua' => 'Хто знає відповідь?'],
                                ],
                                'note' => '📌 Якщо Who/What — підмет, не додаємо do/does/did!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Допоміжні дієслова у питаннях',
                        'sections' => [
                            [
                                'label' => 'DO/DOES/DID — для Simple Tenses',
                                'color' => 'emerald',
                                'description' => 'У простих часах (Present Simple, Past Simple) використовуємо do/does/did як допоміжні:',
                                'examples' => [
                                    ['en' => 'Present Simple: Do/Does', 'ua' => 'Do you live here? Does she work?'],
                                    ['en' => 'Past Simple: Did', 'ua' => 'Did they go? Did you see it?'],
                                ],
                                'note' => '📌 Основне дієслово у базовій формі (без -s, -ed)!',
                            ],
                            [
                                'label' => 'TO BE — інверсія',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> не потрібен do/does/did — просто робимо інверсію:',
                                'examples' => [
                                    ['en' => 'Present: am/is/are', 'ua' => 'Are you ready? Is she here?'],
                                    ['en' => 'Past: was/were', 'ua' => 'Was he there? Were they happy?'],
                                ],
                            ],
                            [
                                'label' => 'HAVE/HAS — для Perfect Tenses',
                                'color' => 'purple',
                                'description' => 'У перфектних часах (Present Perfect, Past Perfect) have/has є допоміжним дієсловом:',
                                'examples' => [
                                    ['en' => 'Present Perfect: have/has', 'ua' => 'Have you seen this? Has she finished?'],
                                    ['en' => 'Past Perfect: had', 'ua' => 'Had they left before you arrived?'],
                                ],
                            ],
                            [
                                'label' => 'Модальні дієслова — інверсія',
                                'color' => 'amber',
                                'description' => 'Модальні дієслова (can, will, should, must, may, might) виносяться перед підмет:',
                                'examples' => [
                                    ['en' => 'Can, Could', 'ua' => 'Can you help? Could she come?'],
                                    ['en' => 'Will, Would', 'ua' => 'Will they arrive? Would you like tea?'],
                                    ['en' => 'Should, Must, May', 'ua' => 'Should I wait? Must we go? May I ask?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Питання в різних часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'Використовуємо <strong>do/does</strong> (з to be — інверсія am/is/are):',
                                'examples' => [
                                    ['en' => 'Do you speak English?', 'ua' => 'Ти розмовляєш англійською?'],
                                    ['en' => 'Does she live here?', 'ua' => 'Вона тут живе?'],
                                    ['en' => 'Are you a student?', 'ua' => 'Ти студент?'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous',
                                'color' => 'sky',
                                'description' => 'Інверсія <strong>am/is/are</strong>:',
                                'examples' => [
                                    ['en' => 'Are you working now?', 'ua' => 'Ти зараз працюєш?'],
                                    ['en' => 'Is she coming?', 'ua' => 'Вона йде (приходить)?'],
                                    ['en' => 'What are they doing?', 'ua' => 'Що вони роблять?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'purple',
                                'description' => 'Використовуємо <strong>did</strong> (з to be — інверсія was/were):',
                                'examples' => [
                                    ['en' => 'Did you see the movie?', 'ua' => 'Ти бачив цей фільм?'],
                                    ['en' => 'Where did she go?', 'ua' => 'Куди вона пішла?'],
                                    ['en' => 'Were you at home?', 'ua' => 'Ти був вдома?'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'amber',
                                'description' => 'Інверсія <strong>have/has</strong>:',
                                'examples' => [
                                    ['en' => 'Have you ever been to Paris?', 'ua' => 'Ти коли-небудь був у Парижі?'],
                                    ['en' => 'Has she finished her work?', 'ua' => 'Вона закінчила свою роботу?'],
                                    ['en' => 'What have they done?', 'ua' => 'Що вони зробили?'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'rose',
                                'description' => 'Інверсія <strong>will</strong>:',
                                'examples' => [
                                    ['en' => 'Will you come to the party?', 'ua' => 'Ти прийдеш на вечірку?'],
                                    ['en' => 'When will she arrive?', 'ua' => 'Коли вона приїде?'],
                                    ['en' => 'What will they do?', 'ua' => 'Що вони робитимуть?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Порівняння типів питань',
                        'intro' => 'Різні типи питальних речень в англійській мові:',
                        'rows' => [
                            [
                                'en' => 'Yes/No Questions',
                                'ua' => 'Загальні питання',
                                'note' => 'Do you like tea? — Yes/No',
                            ],
                            [
                                'en' => 'Wh-Questions',
                                'ua' => 'Спеціальні питання',
                                'note' => 'What do you want? — Specific information',
                            ],
                            [
                                'en' => 'Subject Questions',
                                'ua' => 'Питання до підмета',
                                'note' => 'Who called? — No do/does/did needed',
                            ],
                            [
                                'en' => 'Alternative Questions',
                                'ua' => 'Альтернативні питання',
                                'note' => 'Tea or coffee? — Choice between options',
                            ],
                            [
                                'en' => 'Tag Questions',
                                'ua' => 'Розділові питання',
                                'note' => 'You like it, don\'t you? — Confirmation',
                            ],
                            [
                                'en' => 'Indirect Questions',
                                'ua' => 'Непрямі питання',
                                'note' => 'Can you tell me where...? — More polite',
                            ],
                        ],
                        'warning' => '📌 Кожен тип питання має свою специфічну структуру!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Коли використовувати do/does/did',
                        'intro' => 'Правила використання допоміжних дієслів do/does/did:',
                        'rows' => [
                            [
                                'en' => 'Simple Tenses (main verbs)',
                                'ua' => 'З основними дієсловами',
                                'note' => 'Do you work? Does she know? Did they go?',
                            ],
                            [
                                'en' => 'NOT with TO BE',
                                'ua' => 'НЕ з to be',
                                'note' => '❌ Do you are happy? ✅ Are you happy?',
                            ],
                            [
                                'en' => 'NOT with modals',
                                'ua' => 'НЕ з модальними',
                                'note' => '❌ Do you can swim? ✅ Can you swim?',
                            ],
                            [
                                'en' => 'NOT with have (perfect)',
                                'ua' => 'НЕ з have (perfect)',
                                'note' => '❌ Do you have seen? ✅ Have you seen?',
                            ],
                            [
                                'en' => 'NOT in subject questions',
                                'ua' => 'НЕ у питаннях до підмета',
                                'note' => '❌ Who did call? ✅ Who called?',
                            ],
                        ],
                        'warning' => '⚠️ Do/Does/Did використовуються ЛИШЕ з основними дієсловами у Simple Tenses!',
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
                                'title' => 'Неправильний порядок слів — підмет перед допоміжним дієсловом.',
                                'wrong' => '❌ You are happy? She can swim?',
                                'right' => '✅ <span class="font-mono">Are you happy? Can she swim?</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання do/does/did з to be.',
                                'wrong' => '❌ Do you are ready? Did you were there?',
                                'right' => '✅ <span class="font-mono">Are you ready? Were you there?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання do/does/did з модальними дієсловами.',
                                'wrong' => '❌ Do you can help? Did she could come?',
                                'right' => '✅ <span class="font-mono">Can you help? Could she come?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Неправильна форма основного дієслова.',
                                'wrong' => '❌ Does she works? Did they went?',
                                'right' => '✅ <span class="font-mono">Does she work? Did they go?</span>',
                            ],
                            [
                                'label' => 'Помилка 5',
                                'color' => 'emerald',
                                'title' => 'Додавання do/does/did у питаннях до підмета.',
                                'wrong' => '❌ Who did call you? What does happened?',
                                'right' => '✅ <span class="font-mono">Who called you? What happened?</span>',
                            ],
                            [
                                'label' => 'Помилка 6',
                                'color' => 'indigo',
                                'title' => 'Пропуск допоміжного дієслова.',
                                'wrong' => '❌ You like coffee? She speak English?',
                                'right' => '✅ <span class="font-mono">Do you like coffee? Does she speak English?</span>',
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
                            '<strong>Інверсія</strong> — допоміжне дієслово перед підметом у питаннях.',
                            '<strong>Yes/No Questions</strong> — загальні питання з відповіддю "так" або "ні".',
                            '<strong>Wh-Questions</strong> — спеціальні питання з what, where, when, why, who, how.',
                            '<strong>Do/Does/Did</strong> — допоміжні дієслова для Simple Tenses (з основними дієсловами).',
                            '<strong>З to be</strong> — інверсія без do/does/did: <em>Are you...? Was she...?</em>',
                            '<strong>З модальними</strong> — інверсія без do/does/did: <em>Can you...? Will she...?</em>',
                            '<strong>Питання до підмета</strong> — Who/What як підмет, без do/does/did: <em>Who called?</em>',
                            '<strong>Основне правило</strong> — завжди інверсія допоміжного дієслова та підмета!',
                            '<strong>Формула</strong> — Wh-word + Auxiliary + Subject + Main Verb + ...?',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери правильну форму питання.',
                        'selects' => [
                            ['label' => 'a) Do you are happy? / b) Are you happy? / c) You are happy?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Where does she lives? / b) Where she lives? / c) Where does she live?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Can you help me? / b) Do you can help me? / c) You can help me?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Who did call? / b) Who called? / c) Who does call?', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b', 'c'],
                        'input_title' => 'Вправа 2. Постав питання',
                        'input_intro' => 'Утвори питання з поданого речення.',
                        'inputs' => [
                            ['before' => 'She speaks French. → ', 'after' => ''],
                            ['before' => 'They are students. → ', 'after' => ''],
                            ['before' => 'He can drive. → ', 'after' => ''],
                            ['before' => 'You went to Paris. (Where...?) → ', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у питанні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'You are ready?',
                                'example_target' => 'Are you ready?',
                            ],
                            [
                                'original' => '1. Do you are happy?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Where she lives?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. Do you can swim?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '4. Who did call you?',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Питальні речення та заперечення',
                        'items' => [
                            [
                                'label' => 'Question Forms — Як ставити запитання (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Yes/No Questions — Загальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Question Tags — Розділові питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Short Answers — Короткі відповіді',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
