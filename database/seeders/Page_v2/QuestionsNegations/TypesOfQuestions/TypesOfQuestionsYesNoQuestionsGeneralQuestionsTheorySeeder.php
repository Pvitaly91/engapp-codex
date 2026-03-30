<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Page_v2\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsYesNoQuestionsGeneralQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'yesno-questions-general-questions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Yes/No Questions (General Questions) — Загальні питання',
            'subtitle_html' => '<p><strong>Yes/No Questions</strong> (загальні питання) — це питання, на які можна відповісти "так" або "ні". Вони є найпростішим типом питань в англійській мові і формуються шляхом інверсії підмета та допоміжного дієслова.</p>',
            'subtitle_text' => 'Теоретичний огляд загальних питань (Yes/No Questions) в англійській мові: структура, правила формування та приклади використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'types-of-questions',
                'title' => 'Види питальних речень',
                'language' => 'uk',
            ],
            // BLOCK-FIRST TAGGING: Page anchor tags (short, general page identifiers)
            'tags' => [
                'Types of Questions',
                'Question Forms',
                'Yes/No Questions',
                'General Questions',
                'Grammar',
                'Theory',
            ],
            // BLOCK-FIRST TAGGING: Base tags inherited by all blocks (controlled inheritance)
            'base_tags' => [
                'Types of Questions',
                'Question Forms',
                'Yes/No Questions',
                'General Questions',
            ],
            // Subtitle block tags configuration
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'hero',
                    // BLOCK-FIRST: Hero block detailed tags
                    'tags' => ['Introduction', 'Overview', 'Do/Does/Did', 'To Be', 'Modal Verbs', 'CEFR A1', 'CEFR A2'],
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>загальні питання (Yes/No Questions)</strong> — найпростіший тип питань в англійській мові, на які можна відповісти "так" або "ні".',
                        'rules' => [
                            [
                                'label' => 'З DO/DOES/DID',
                                'color' => 'emerald',
                                'text' => '<strong>Допоміжне дієслово</strong> перед підметом:',
                                'example' => 'Do you like coffee? Does she work here?',
                            ],
                            [
                                'label' => 'З TO BE',
                                'color' => 'blue',
                                'text' => '<strong>Інверсія</strong> — дієслово перед підметом:',
                                'example' => 'Are you ready? Is he a teacher?',
                            ],
                            [
                                'label' => 'З МОДАЛЬНИМИ',
                                'color' => 'amber',
                                'text' => '<strong>Модальне дієслово</strong> перед підметом:',
                                'example' => 'Can you swim? Should we go?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'forms-grid-what-is',
                    // BLOCK-FIRST: Definition block detailed tags
                    'tags' => ['Definition', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '1. Що таке загальні питання?',
                        'intro' => 'Yes/No Questions (загальні питання) — це питання, на які можна відповісти "так" або "ні":',
                        'items' => [
                            ['label' => 'Питання', 'title' => 'Do you like tea?', 'subtitle' => 'Тобі подобається чай?'],
                            ['label' => 'Відповідь "Так"', 'title' => 'Yes, I do.', 'subtitle' => 'Так (подобається).'],
                            ['label' => 'Відповідь "Ні"', 'title' => 'No, I don\'t.', 'subtitle' => 'Ні (не подобається).'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'usage-panels-do-does-did',
                    // BLOCK-FIRST: Do/Does/Did block detailed tags
                    'tags' => ['Do/Does/Did', 'Present Simple', 'Past Simple', 'Auxiliaries', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '2. Загальні питання з DO/DOES/DID',
                        'sections' => [
                            [
                                'label' => 'Present Simple — DO/DOES',
                                'color' => 'emerald',
                                'description' => 'У <strong>Present Simple</strong> використовуємо <strong>do</strong> (з I, you, we, they) або <strong>does</strong> (з he, she, it).',
                                'examples' => [
                                    ['en' => 'Do you speak English?', 'ua' => 'Ти розмовляєш англійською?'],
                                    ['en' => 'Do they live in Kyiv?', 'ua' => 'Вони живуть у Києві?'],
                                    ['en' => 'Does she work here?', 'ua' => 'Вона тут працює?'],
                                    ['en' => 'Does it work?', 'ua' => 'Це працює?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple — DID',
                                'color' => 'sky',
                                'description' => 'У <strong>Past Simple</strong> використовуємо <strong>did</strong> з усіма особами. Основне дієслово залишається у базовій формі.',
                                'examples' => [
                                    ['en' => 'Did you see the movie?', 'ua' => 'Ти бачив цей фільм?'],
                                    ['en' => 'Did she call you?', 'ua' => 'Вона тобі телефонувала?'],
                                    ['en' => 'Did they arrive on time?', 'ua' => 'Вони приїхали вчасно?'],
                                    ['en' => 'Did it rain yesterday?', 'ua' => 'Вчора йшов дощ?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'purple',
                                'description' => 'Формула загального питання з do/does/did:',
                                'examples' => [
                                    ['en' => 'Do/Does/Did + Subject + Verb (base form) + ...?', 'ua' => 'Допоміжне + Підмет + Дієслово + ...?'],
                                ],
                                'note' => '📌 Основне дієслово завжди у базовій формі (без -s, -ed)!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'usage-panels-to-be',
                    // BLOCK-FIRST: To Be block detailed tags
                    'tags' => ['To Be', 'Be (am/is/are/was/were)', 'Present Simple', 'Past Simple', 'Inversion', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '3. Загальні питання з TO BE',
                        'sections' => [
                            [
                                'label' => 'Present Simple — AM/IS/ARE',
                                'color' => 'blue',
                                'description' => 'З дієсловом <strong>to be</strong> у Present Simple просто міняємо місцями дієслово і підмет.',
                                'examples' => [
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                    ['en' => 'Is she a teacher?', 'ua' => 'Вона вчителька?'],
                                    ['en' => 'Are they at home?', 'ua' => 'Вони вдома?'],
                                    ['en' => 'Is it expensive?', 'ua' => 'Це дорого?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple — WAS/WERE',
                                'color' => 'amber',
                                'description' => 'У Past Simple використовуємо <strong>was</strong> (з I, he, she, it) або <strong>were</strong> (з you, we, they).',
                                'examples' => [
                                    ['en' => 'Was he at work yesterday?', 'ua' => 'Він був на роботі вчора?'],
                                    ['en' => 'Were you tired?', 'ua' => 'Ти був втомлений?'],
                                    ['en' => 'Were they happy?', 'ua' => 'Вони були щасливі?'],
                                    ['en' => 'Was it difficult?', 'ua' => 'Це було складно?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Формула загального питання з to be:',
                                'examples' => [
                                    ['en' => 'Am/Is/Are/Was/Were + Subject + ...?', 'ua' => 'Форма to be + Підмет + ...?'],
                                ],
                                'note' => '📌 Не потрібно додавати do/does/did з дієсловом to be!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-modals',
                    // BLOCK-FIRST: Modal verbs block detailed tags
                    'tags' => ['Modal Verbs', 'Can/Could', 'Will/Would', 'Should', 'Must', 'May', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '4. Загальні питання з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'CAN / COULD',
                                'color' => 'emerald',
                                'description' => 'З <strong>can/could</strong> (можу/міг би) ставимо модальне дієслово перед підметом.',
                                'examples' => [
                                    ['en' => 'Can you swim?', 'ua' => 'Ти вмієш плавати?'],
                                    ['en' => 'Can she drive?', 'ua' => 'Вона вміє водити?'],
                                    ['en' => 'Could you help me?', 'ua' => 'Ти міг би мені допомогти?'],
                                    ['en' => 'Could they come?', 'ua' => 'Вони змогли прийти?'],
                                ],
                            ],
                            [
                                'label' => 'WILL / WOULD',
                                'color' => 'sky',
                                'description' => 'З <strong>will/would</strong> (буду/був би) також ставимо модальне перед підметом.',
                                'examples' => [
                                    ['en' => 'Will you come?', 'ua' => 'Ти прийдеш?'],
                                    ['en' => 'Will it rain?', 'ua' => 'Буде дощ?'],
                                    ['en' => 'Would you like coffee?', 'ua' => 'Ти б хотів кави?'],
                                    ['en' => 'Would she agree?', 'ua' => 'Вона б погодилась?'],
                                ],
                            ],
                            [
                                'label' => 'SHOULD / MUST / MAY',
                                'color' => 'purple',
                                'description' => 'Інші модальні дієслова працюють за тим самим принципом.',
                                'examples' => [
                                    ['en' => 'Should we go?', 'ua' => 'Нам варто піти?'],
                                    ['en' => 'Must I wait?', 'ua' => 'Я мушу чекати?'],
                                    ['en' => 'May I ask?', 'ua' => 'Можна запитати?'],
                                ],
                                'note' => '📌 Структура: Modal + Subject + Verb (base form) + ...?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-have-got',
                    // BLOCK-FIRST: Have Got block detailed tags
                    'tags' => ['Have Got', 'Possession', 'British English', 'American English', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '5. Загальні питання з HAVE GOT',
                        'sections' => [
                            [
                                'label' => 'HAVE GOT / HAS GOT',
                                'color' => 'blue',
                                'description' => 'З <strong>have got</strong> (маю) робимо інверсію — ставимо have/has перед підметом.',
                                'examples' => [
                                    ['en' => 'Have you got a car?', 'ua' => 'У тебе є машина?'],
                                    ['en' => 'Has she got a dog?', 'ua' => 'У неї є собака?'],
                                    ['en' => 'Have they got time?', 'ua' => 'У них є час?'],
                                ],
                            ],
                            [
                                'label' => 'HAVE / HAS (володіння)',
                                'color' => 'amber',
                                'description' => 'У американському варіанті з <strong>have</strong> як основним дієсловом використовуємо do/does.',
                                'examples' => [
                                    ['en' => 'Do you have a car?', 'ua' => 'У тебе є машина?'],
                                    ['en' => 'Does she have a dog?', 'ua' => 'У неї є собака?'],
                                    ['en' => 'Do they have time?', 'ua' => 'У них є час?'],
                                ],
                                'note' => '📌 У британському варіанті частіше have got, у американському — have з do/does.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-other-tenses',
                    // BLOCK-FIRST: Other Tenses block detailed tags
                    'tags' => ['Present Continuous', 'Present Perfect', 'Past Continuous', 'Advanced Tenses', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '6. Інші часи',
                        'sections' => [
                            [
                                'label' => 'Present Continuous',
                                'color' => 'emerald',
                                'description' => 'З <strong>Present Continuous</strong> (am/is/are + Ving) робимо інверсію am/is/are.',
                                'examples' => [
                                    ['en' => 'Are you working now?', 'ua' => 'Ти зараз працюєш?'],
                                    ['en' => 'Is she coming?', 'ua' => 'Вона йде (приходить)?'],
                                    ['en' => 'Are they playing?', 'ua' => 'Вони граються?'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'sky',
                                'description' => 'З <strong>Present Perfect</strong> (have/has + V3) робимо інверсію have/has.',
                                'examples' => [
                                    ['en' => 'Have you seen this movie?', 'ua' => 'Ти бачив цей фільм?'],
                                    ['en' => 'Has she finished?', 'ua' => 'Вона закінчила?'],
                                    ['en' => 'Have they arrived?', 'ua' => 'Вони приїхали?'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'purple',
                                'description' => 'З <strong>Past Continuous</strong> (was/were + Ving) робимо інверсію was/were.',
                                'examples' => [
                                    ['en' => 'Were you sleeping?', 'ua' => 'Ти спав?'],
                                    ['en' => 'Was she studying?', 'ua' => 'Вона вчилася?'],
                                    ['en' => 'Were they watching TV?', 'ua' => 'Вони дивилися телевізор?'],
                                ],
                                'note' => '📌 Загальне правило: допоміжне дієслово виноситься перед підмет.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'comparison-table',
                    // BLOCK-FIRST: Comparison table block detailed tags
                    'tags' => ['Summary', 'Comparison', 'All Structures', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '7. Порівняльна таблиця',
                        'intro' => 'Як формувати загальні питання з різними типами дієслів:',
                        'rows' => [
                            [
                                'en' => 'Simple (do/does/did)',
                                'ua' => 'Прості часи',
                                'note' => 'Do you like...? Does she work...? Did they come...?',
                            ],
                            [
                                'en' => 'To be (am/is/are/was/were)',
                                'ua' => 'Дієслово to be',
                                'note' => 'Are you ready? Is she here? Were they happy?',
                            ],
                            [
                                'en' => 'Modals (can/will/should...)',
                                'ua' => 'Модальні дієслова',
                                'note' => 'Can you swim? Will you come? Should we go?',
                            ],
                            [
                                'en' => 'Have got',
                                'ua' => 'Мати (have got)',
                                'note' => 'Have you got...? Has she got...?',
                            ],
                            [
                                'en' => 'Continuous (am/is/are + Ving)',
                                'ua' => 'Тривалі часи',
                                'note' => 'Are you working? Was she sleeping?',
                            ],
                            [
                                'en' => 'Perfect (have/has + V3)',
                                'ua' => 'Перфектні часи',
                                'note' => 'Have you seen...? Has she finished...?',
                            ],
                        ],
                        'warning' => '📌 Основний принцип: <strong>допоміжне дієслово перед підметом</strong>!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'mistakes-grid',
                    // BLOCK-FIRST: Common mistakes block detailed tags
                    'tags' => ['Common Mistakes', 'Word Order', 'Grammar Errors', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів — підмет перед дієсловом.',
                                'wrong' => '❌ You are ready? You can swim?',
                                'right' => '✅ <span class="font-mono">Are you ready? Can you swim?</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання do/does/did з to be.',
                                'wrong' => '❌ Do you are happy? Did you were there?',
                                'right' => '✅ <span class="font-mono">Are you happy? Were you there?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма основного дієслова.',
                                'wrong' => '❌ Does she works? Did they went?',
                                'right' => '✅ <span class="font-mono">Does she work? Did they go?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Пропуск допоміжного дієслова.',
                                'wrong' => '❌ You like coffee? She works here?',
                                'right' => '✅ <span class="font-mono">Do you like coffee? Does she work here?</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'summary-list',
                    // BLOCK-FIRST: Summary block detailed tags
                    'tags' => ['Summary', 'Key Rules', 'Quick Reference', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Yes/No Questions</strong> — питання з відповіддю "так" або "ні".',
                            '<strong>З Simple Tenses</strong> — використовуємо <strong>do/does/did</strong> перед підметом.',
                            '<strong>З to be</strong> — міняємо місцями дієслово і підмет: <em>Are you...? Is she...? Were they...?</em>',
                            '<strong>З модальними</strong> — модальне дієслово перед підметом: <em>Can you...? Will she...? Should we...?</em>',
                            '<strong>З have got</strong> — інверсія have/has: <em>Have you got...? Has she got...?</em>',
                            '<strong>З Continuous</strong> — інверсія am/is/are/was/were: <em>Are you working? Was she sleeping?</em>',
                            '<strong>З Perfect</strong> — інверсія have/has: <em>Have you seen...? Has she finished...?</em>',
                            '<strong>Основне правило</strong> — допоміжне дієслово завжди перед підметом!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'practice-set',
                    // BLOCK-FIRST: Practice block detailed tags
                    'tags' => ['Practice', 'Exercises', 'Interactive', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери правильну форму загального питання.',
                        'selects' => [
                            ['label' => 'a) Do you like coffee? / b) You like coffee? / c) You do like coffee?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Is she ready? / b) Does she is ready? / c) She is ready?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Can you swim? / b) Do you can swim? / c) You can swim?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Did they go? / b) Did they went? / c) They did go?', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b', 'c'],
                        'input_title' => 'Вправа 2. Постав питання',
                        'input_intro' => 'Утвори загальне питання з поданого речення.',
                        'inputs' => [
                            ['before' => 'She likes tea. → ', 'after' => ''],
                            ['before' => 'They are happy. → ', 'after' => ''],
                            ['before' => 'He can dance. → ', 'after' => ''],
                            ['before' => 'You have a car. → ', 'after' => ''],
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
                                'original' => '1. Does she is happy?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Did they went home?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. You can help me?',
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
                    'uuid_key' => 'navigation-chips',
                    // BLOCK-FIRST: Navigation block - service block with no content tags
                    'inherit_base_tags' => false,
                    'tags' => ['Navigation'],
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Види питальних речень',
                        'items' => [
                            [
                                'label' => 'Yes/No Questions — Загальні питання (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання',
                                'current' => false,
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
