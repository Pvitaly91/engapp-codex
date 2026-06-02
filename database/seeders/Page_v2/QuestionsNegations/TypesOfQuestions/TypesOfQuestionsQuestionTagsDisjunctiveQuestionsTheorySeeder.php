<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Page_v2\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsQuestionTagsDisjunctiveQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'question-tags-disjunctive-questions-dont-you-isnt-it';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Question Tags (Disjunctive Questions) — Розділові питання: …, don\'t you? […, isn\'t it?]',
            'subtitle_html' => '<p><strong>Question tags</strong> (розділові питання) — це короткі питання в кінці речення, які використовуються для підтвердження інформації або початку розмови. Основне правило: позитивне речення + негативний tag, або негативне речення + позитивний tag.</p>',
            'subtitle_text' => 'Теоретичний огляд розділових питань (Question tags) в англійській мові: правила формування, використання та інтонація питань типу "don\'t you?", "isn\'t it?".',
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
                'Tag Questions',
                'Question Tags',
                'Disjunctive Questions',
                'Grammar',
                'Theory',
            ],
            // BLOCK-FIRST TAGGING: Base tags inherited by all blocks (controlled inheritance)
            'base_tags' => [
                'Types of Questions',
                'Question Forms',
                'Tag Questions',
                'Question Tags',
                'Polarity',
            ],
            // Subtitle block tags configuration
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'hero',
                    // BLOCK-FIRST: Hero block detailed tags
                    'tags' => ['Introduction', 'Overview', 'Positive-Negative Rule', 'Polarity', 'Intonation', 'CEFR B1', 'CEFR B2'],
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш <strong>розділові питання (Question Tags)</strong> — короткі питання в кінці речення для підтвердження інформації або ввічливого спілкування.',
                        'rules' => [
                            [
                                'label' => 'ПОЗИТИВ + НЕГАТИВ',
                                'color' => 'emerald',
                                'text' => '<strong>Ствердження</strong> + негативний tag:',
                                'example' => 'You like coffee, don\'t you?',
                            ],
                            [
                                'label' => 'НЕГАТИВ + ПОЗИТИВ',
                                'color' => 'blue',
                                'text' => '<strong>Заперечення</strong> + позитивний tag:',
                                'example' => 'You don\'t like coffee, do you?',
                            ],
                            [
                                'label' => 'ІНТОНАЦІЯ',
                                'color' => 'amber',
                                'text' => '<strong>Падаюча</strong> — підтвердження, <strong>зростаюча</strong> — справжнє питання:',
                                'example' => 'It\'s nice, isn\'t it? ↘ / ↗',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'forms-grid-what-is',
                    // BLOCK-FIRST: Definition block detailed tags
                    'tags' => ['Definition', 'Positive-Negative Rule', 'Polarity', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '1. Що таке Question Tags?',
                        'intro' => 'Question tags — це короткі питання в кінці речення:',
                        'items' => [
                            ['label' => 'Ствердження', 'title' => 'She is happy, isn\'t she?', 'subtitle' => 'Позитив + негативний tag'],
                            ['label' => 'Заперечення', 'title' => 'She isn\'t happy, is she?', 'subtitle' => 'Негатив + позитивний tag'],
                            ['label' => 'Основне правило', 'title' => 'Statement + opposite tag', 'subtitle' => 'Протилежна форма'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-to-be',
                    // BLOCK-FIRST: To Be block detailed tags
                    'tags' => ['To Be', 'Be (am/is/are/was/were)', 'Present Simple', 'Past Simple', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '2. Question Tags з дієсловом TO BE',
                        'sections' => [
                            [
                                'label' => 'Present Simple — AM/IS/ARE',
                                'color' => 'emerald',
                                'description' => 'З <strong>to be</strong> у Present Simple формуємо tag з am/is/are:',
                                'examples' => [
                                    ['en' => 'She is happy, isn\'t she?', 'ua' => 'Вона щаслива, чи не так?'],
                                    ['en' => 'You are tired, aren\'t you?', 'ua' => 'Ти втомлений, правда?'],
                                    ['en' => 'He is a doctor, isn\'t he?', 'ua' => 'Він лікар, так?'],
                                    ['en' => 'They are British, aren\'t they?', 'ua' => 'Вони британці, чи не так?'],
                                ],
                            ],
                            [
                                'label' => 'Негативні речення',
                                'color' => 'sky',
                                'description' => 'Якщо речення негативне, tag стає позитивним:',
                                'examples' => [
                                    ['en' => 'She isn\'t happy, is she?', 'ua' => 'Вона не щаслива, так?'],
                                    ['en' => 'You aren\'t tired, are you?', 'ua' => 'Ти не втомлений, так?'],
                                    ['en' => 'He isn\'t a doctor, is he?', 'ua' => 'Він не лікар, так?'],
                                    ['en' => 'They aren\'t British, are they?', 'ua' => 'Вони не британці, так?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple — WAS/WERE',
                                'color' => 'purple',
                                'description' => 'У минулому часі використовуємо was/were:',
                                'examples' => [
                                    ['en' => 'It was great, wasn\'t it?', 'ua' => 'Це було чудово, правда?'],
                                    ['en' => 'They were late, weren\'t they?', 'ua' => 'Вони спізнилися, так?'],
                                    ['en' => 'She wasn\'t there, was she?', 'ua' => 'Її там не було, так?'],
                                ],
                                'note' => '📌 Правило: позитив + негатив / негатив + позитив',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-do-does-did',
                    // BLOCK-FIRST: Do/Does/Did block detailed tags
                    'tags' => ['Do/Does/Did', 'Present Simple', 'Past Simple', 'Auxiliaries', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '3. Question Tags з DO/DOES/DID',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'blue',
                                'description' => 'У Present Simple використовуємо <strong>do/does</strong> у tag:',
                                'examples' => [
                                    ['en' => 'You like coffee, don\'t you?', 'ua' => 'Тобі подобається кава, правда?'],
                                    ['en' => 'She works here, doesn\'t she?', 'ua' => 'Вона тут працює, так?'],
                                    ['en' => 'They live in London, don\'t they?', 'ua' => 'Вони живуть у Лондоні, чи не так?'],
                                    ['en' => 'He knows the answer, doesn\'t he?', 'ua' => 'Він знає відповідь, так?'],
                                ],
                            ],
                            [
                                'label' => 'Негативні речення',
                                'color' => 'amber',
                                'description' => 'З негативними реченнями tag позитивний:',
                                'examples' => [
                                    ['en' => 'You don\'t like coffee, do you?', 'ua' => 'Тобі не подобається кава, так?'],
                                    ['en' => 'She doesn\'t work here, does she?', 'ua' => 'Вона тут не працює, так?'],
                                    ['en' => 'They don\'t live in London, do they?', 'ua' => 'Вони не живуть у Лондоні, так?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'rose',
                                'description' => 'У Past Simple використовуємо <strong>did</strong>:',
                                'examples' => [
                                    ['en' => 'You saw the movie, didn\'t you?', 'ua' => 'Ти бачив фільм, так?'],
                                    ['en' => 'She called you, didn\'t she?', 'ua' => 'Вона тобі телефонувала, правда?'],
                                    ['en' => 'They didn\'t come, did they?', 'ua' => 'Вони не прийшли, так?'],
                                ],
                                'note' => '📌 Tag завжди використовує допоміжне дієслово do/does/did',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-modals',
                    // BLOCK-FIRST: Modal verbs block detailed tags
                    'tags' => ['Modal Verbs', 'Can/Could', 'Will/Would', 'Should', 'Must', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '4. Question Tags з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'CAN / COULD / WILL / WOULD',
                                'color' => 'emerald',
                                'description' => 'З модальними дієсловами повторюємо модальне у tag:',
                                'examples' => [
                                    ['en' => 'You can swim, can\'t you?', 'ua' => 'Ти вмієш плавати, так?'],
                                    ['en' => 'She will come, won\'t she?', 'ua' => 'Вона прийде, правда?'],
                                    ['en' => 'They could help, couldn\'t they?', 'ua' => 'Вони могли б допомогти, так?'],
                                    ['en' => 'He would like it, wouldn\'t he?', 'ua' => 'Йому б це сподобалось, так?'],
                                ],
                            ],
                            [
                                'label' => 'SHOULD / MUST / MAY',
                                'color' => 'sky',
                                'description' => 'Інші модальні дієслова працюють так само:',
                                'examples' => [
                                    ['en' => 'We should go, shouldn\'t we?', 'ua' => 'Нам варто піти, так?'],
                                    ['en' => 'You must be tired, mustn\'t you?', 'ua' => 'Ти мабуть втомлений, так?'],
                                    ['en' => 'She might come, mightn\'t she?', 'ua' => 'Вона може прийти, так?'],
                                ],
                            ],
                            [
                                'label' => 'Негативні форми',
                                'color' => 'purple',
                                'description' => 'З негативними формами модальних:',
                                'examples' => [
                                    ['en' => 'You can\'t swim, can you?', 'ua' => 'Ти не вмієш плавати, так?'],
                                    ['en' => 'She won\'t come, will she?', 'ua' => 'Вона не прийде, так?'],
                                    ['en' => 'They shouldn\'t worry, should they?', 'ua' => 'Їм не варто хвилюватися, так?'],
                                ],
                                'note' => '📌 Модальне дієслово з речення повторюється у tag',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-have-has',
                    // BLOCK-FIRST: Have/Has block detailed tags
                    'tags' => ['Have/Has/Had', 'Have Got', 'Present Perfect', 'British vs American', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '5. Question Tags з HAVE/HAS',
                        'sections' => [
                            [
                                'label' => 'Present Perfect',
                                'color' => 'amber',
                                'description' => 'У Present Perfect також використовуємо have/has:',
                                'examples' => [
                                    ['en' => 'You have seen the movie, haven\'t you?', 'ua' => 'Ти бачив цей фільм, так?'],
                                    ['en' => 'She has finished, hasn\'t she?', 'ua' => 'Вона закінчила, правда?'],
                                    ['en' => 'They haven\'t arrived, have they?', 'ua' => 'Вони не приїхали, так?'],
                                ],
                            ],
                            [
                                'label' => 'HAVE GOT',
                                'color' => 'blue',
                                'description' => 'З <strong>have got</strong> (мати) використовуємо have/has у tag:',
                                'examples' => [
                                    ['en' => 'You have got a car, haven\'t you?', 'ua' => 'У тебе є машина, так?'],
                                    ['en' => 'She has got a dog, hasn\'t she?', 'ua' => 'У неї є собака, правда?'],
                                    ['en' => 'They haven\'t got time, have they?', 'ua' => 'У них немає часу, так?'],
                                ],
                            ],
                            [
                                'label' => 'Американський варіант',
                                'color' => 'rose',
                                'description' => 'В американській англійській з <strong>have</strong> (мати) часто використовують do:',
                                'examples' => [
                                    ['en' => 'You have a car, don\'t you?', 'ua' => 'У тебе є машина, так?'],
                                    ['en' => 'She has a dog, doesn\'t she?', 'ua' => 'У неї є собака, правда?'],
                                ],
                                'note' => '📌 Британський варіант: have got + haven\'t/hasn\'t, американський: have + don\'t/doesn\'t',
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
                    'tags' => ['Present Continuous', 'Past Continuous', 'Future Simple', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '6. Question Tags з іншими часами',
                        'sections' => [
                            [
                                'label' => 'Present Continuous',
                                'color' => 'emerald',
                                'description' => 'У Present Continuous використовуємо am/is/are:',
                                'examples' => [
                                    ['en' => 'You are working now, aren\'t you?', 'ua' => 'Ти зараз працюєш, так?'],
                                    ['en' => 'She is coming, isn\'t she?', 'ua' => 'Вона йде, правда?'],
                                    ['en' => 'They aren\'t playing, are they?', 'ua' => 'Вони не граються, так?'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'sky',
                                'description' => 'У Past Continuous використовуємо was/were:',
                                'examples' => [
                                    ['en' => 'You were sleeping, weren\'t you?', 'ua' => 'Ти спав, так?'],
                                    ['en' => 'She was studying, wasn\'t she?', 'ua' => 'Вона вчилася, правда?'],
                                    ['en' => 'They weren\'t watching TV, were they?', 'ua' => 'Вони не дивилися телевізор, так?'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'purple',
                                'description' => 'У Future Simple з <strong>will</strong>:',
                                'examples' => [
                                    ['en' => 'You will help me, won\'t you?', 'ua' => 'Ти мені допоможеш, так?'],
                                    ['en' => 'She will come tomorrow, won\'t she?', 'ua' => 'Вона прийде завтра, правда?'],
                                    ['en' => 'They won\'t be late, will they?', 'ua' => 'Вони не спізняться, так?'],
                                ],
                                'note' => '📌 Tag використовує допоміжне дієслово з речення',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'uuid_key' => 'usage-panels-special-cases',
                    // BLOCK-FIRST: Special cases block detailed tags
                    'tags' => ['Special Cases', 'I Am', 'Imperatives', 'Lets', 'This/That', 'CEFR B2'],
                    'body' => json_encode([
                        'title' => '7. Спеціальні випадки та винятки',
                        'sections' => [
                            [
                                'label' => 'I AM → AREN\'T I?',
                                'color' => 'rose',
                                'description' => 'З <strong>I am</strong> tag завжди <strong>aren\'t I?</strong> (не amn\'t I):',
                                'examples' => [
                                    ['en' => 'I am right, aren\'t I?', 'ua' => 'Я правий, так?'],
                                    ['en' => 'I am late, aren\'t I?', 'ua' => 'Я спізнився, так?'],
                                    ['en' => 'I am invited, aren\'t I?', 'ua' => 'Мене запросили, так?'],
                                ],
                            ],
                            [
                                'label' => 'Imperatives (наказовий спосіб)',
                                'color' => 'amber',
                                'description' => 'З наказовим способом використовуємо <strong>will you?</strong>:',
                                'examples' => [
                                    ['en' => 'Close the door, will you?', 'ua' => 'Зачини двері, добре?'],
                                    ['en' => 'Help me, will you?', 'ua' => 'Допоможи мені, добре?'],
                                    ['en' => 'Don\'t be late, will you?', 'ua' => 'Не спізнюйся, добре?'],
                                ],
                            ],
                            [
                                'label' => 'LET\'S → SHALL WE?',
                                'color' => 'blue',
                                'description' => 'З <strong>Let\'s</strong> (давай) завжди <strong>shall we?</strong>:',
                                'examples' => [
                                    ['en' => 'Let\'s go to the cinema, shall we?', 'ua' => 'Давай підемо в кіно, добре?'],
                                    ['en' => 'Let\'s have a break, shall we?', 'ua' => 'Давай зробимо перерву, добре?'],
                                ],
                            ],
                            [
                                'label' => 'THIS/THAT → IT',
                                'color' => 'emerald',
                                'description' => 'У tag замість this/that використовуємо <strong>it</strong>:',
                                'examples' => [
                                    ['en' => 'This is your book, isn\'t it?', 'ua' => 'Це твоя книга, так?'],
                                    ['en' => 'That was interesting, wasn\'t it?', 'ua' => 'Це було цікаво, правда?'],
                                ],
                                'note' => '📌 Особливі правила для I am, imperatives, let\'s, this/that',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'uuid_key' => 'usage-panels-intonation',
                    // BLOCK-FIRST: Intonation block detailed tags
                    'tags' => ['Intonation', 'Rising vs Falling', 'Confirmation', 'Usage', 'CEFR B2'],
                    'body' => json_encode([
                        'title' => '8. Інтонація та використання',
                        'sections' => [
                            [
                                'label' => 'Падаюча інтонація ↘',
                                'color' => 'emerald',
                                'description' => '<strong>Падаюча інтонація</strong> — коли ми впевнені і хочемо підтвердження:',
                                'examples' => [
                                    ['en' => 'It\'s a nice day, isn\'t it? ↘', 'ua' => 'Гарний день, правда? (впевнені)'],
                                    ['en' => 'You like coffee, don\'t you? ↘', 'ua' => 'Тобі подобається кава, так? (знаємо відповідь)'],
                                ],
                            ],
                            [
                                'label' => 'Зростаюча інтонація ↗',
                                'color' => 'sky',
                                'description' => '<strong>Зростаюча інтонація</strong> — коли ми не впевнені і справді питаємо:',
                                'examples' => [
                                    ['en' => 'You live in London, don\'t you? ↗', 'ua' => 'Ти живеш у Лондоні, так? (не впевнені)'],
                                    ['en' => 'She is coming, isn\'t she? ↗', 'ua' => 'Вона йде, правда? (справді питаємо)'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'purple',
                                'description' => 'Question tags використовуються для:',
                                'examples' => [
                                    ['en' => 'Confirming information', 'ua' => 'Підтвердження інформації'],
                                    ['en' => 'Starting a conversation', 'ua' => 'Початку розмови'],
                                    ['en' => 'Being polite', 'ua' => 'Ввічливості'],
                                    ['en' => 'Showing interest', 'ua' => 'Вираження зацікавленості'],
                                ],
                                'note' => '📌 Інтонація важлива: ↘ підтвердження, ↗ справжнє питання',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'comparison-table',
                    // BLOCK-FIRST: Comparison table block detailed tags
                    'tags' => ['Summary', 'Quick Reference', 'All Structures', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '9. Швидка таблиця Question Tags',
                        'intro' => 'Основні правила формування question tags:',
                        'rows' => [
                            [
                                'en' => 'TO BE (am/is/are/was/were)',
                                'ua' => 'Дієслово to be',
                                'note' => 'She is happy, isn\'t she? / She isn\'t happy, is she?',
                            ],
                            [
                                'en' => 'DO/DOES/DID',
                                'ua' => 'Прості часи',
                                'note' => 'You like it, don\'t you? / You don\'t like it, do you?',
                            ],
                            [
                                'en' => 'MODALS (can/will/should...)',
                                'ua' => 'Модальні дієслова',
                                'note' => 'She can swim, can\'t she? / She can\'t swim, can she?',
                            ],
                            [
                                'en' => 'HAVE/HAS (got/Perfect)',
                                'ua' => 'Have got та Perfect',
                                'note' => 'You have seen it, haven\'t you?',
                            ],
                            [
                                'en' => 'Special: I AM',
                                'ua' => 'Виняток з I am',
                                'note' => 'I am right, aren\'t I? (NOT: amn\'t I)',
                            ],
                            [
                                'en' => 'Imperatives',
                                'ua' => 'Наказовий спосіб',
                                'note' => 'Close the door, will you?',
                            ],
                        ],
                        'warning' => '📌 Основне правило: <strong>позитив + негатив / негатив + позитив</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'mistakes-grid',
                    // BLOCK-FIRST: Common mistakes block detailed tags
                    'tags' => ['Common Mistakes', 'Word Order', 'Grammar Errors', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '10. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Однакова форма у реченні та tag.',
                                'wrong' => '❌ She is happy, is she?',
                                'right' => '✅ <span class="font-mono">She is happy, isn\'t she?</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний tag з I am (amn\'t I — не існує, am I not — формальне).',
                                'wrong' => '❌ I am right, amn\'t I?',
                                'right' => '✅ <span class="font-mono">I am right, aren\'t I?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання do/does замість модального у tag.',
                                'wrong' => '❌ She can swim, doesn\'t she?',
                                'right' => '✅ <span class="font-mono">She can swim, can\'t she?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Повторення повного підмета у tag.',
                                'wrong' => '❌ John is happy, isn\'t John?',
                                'right' => '✅ <span class="font-mono">John is happy, isn\'t he?</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'summary-list',
                    // BLOCK-FIRST: Summary block detailed tags
                    'tags' => ['Summary', 'Key Rules', 'Quick Reference', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '11. Короткий конспект',
                        'items' => [
                            '<strong>Question tags</strong> — короткі питання в кінці речення для підтвердження.',
                            '<strong>Основне правило</strong>: позитивне речення + негативний tag / негативне + позитивний.',
                            '<strong>З to be</strong>: She is happy, isn\'t she? / She isn\'t happy, is she?',
                            '<strong>З do/does/did</strong>: You like it, don\'t you? / You don\'t like it, do you?',
                            '<strong>З модальними</strong>: повторюємо модальне — She can swim, can\'t she?',
                            '<strong>З have</strong>: You have seen it, haven\'t you? (Perfect) / You have a car, haven\'t you? (got)',
                            '<strong>Винятки</strong>: I am → aren\'t I? / Imperatives → will you? / Let\'s → shall we?',
                            '<strong>У tag</strong>: використовуємо займенник (he/she/it/they), не повторюємо іменник.',
                            '<strong>Інтонація</strong>: падаюча ↘ — підтвердження, зростаюча ↗ — справжнє питання.',
                            '<strong>Використання</strong>: для підтвердження, початку розмови, ввічливості.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'practice-set',
                    // BLOCK-FIRST: Practice block detailed tags
                    'tags' => ['Practice', 'Exercises', 'Interactive', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '12. Практика',
                        'select_title' => 'Вправа 1. Обери правильний tag',
                        'select_intro' => 'Обери правильний question tag для кожного речення.',
                        'selects' => [
                            ['label' => 'She is happy, _____? (isn\'t she / doesn\'t she / haven\'t she)', 'prompt' => 'Який tag правильний?'],
                            ['label' => 'You like coffee, _____? (don\'t you / aren\'t you / isn\'t it)', 'prompt' => 'Який tag правильний?'],
                            ['label' => 'They can swim, _____? (can they / can\'t they / don\'t they)', 'prompt' => 'Який tag правильний?'],
                            ['label' => 'I am right, _____? (am I not / aren\'t I / isn\'t I)', 'prompt' => 'Який tag правильний?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант', 'Третій варіант'],
                        'input_title' => 'Вправа 2. Додай question tag',
                        'input_intro' => 'Додай правильний question tag до речення.',
                        'inputs' => [
                            ['before' => 'She is tired, ', 'after' => '?'],
                            ['before' => 'You don\'t like tea, ', 'after' => '?'],
                            ['before' => 'He can drive, ', 'after' => '?'],
                            ['before' => 'They won\'t be late, ', 'after' => '?'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у question tag.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'She is happy, is she?',
                                'example_target' => 'She is happy, isn\'t she?',
                            ],
                            [
                                'original' => '1. You like coffee, aren\'t you?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. She can swim, doesn\'t she?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. I am right, am I not?',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'navigation-chips',
                    // BLOCK-FIRST: Navigation block - service block with no content tags
                    'inherit_base_tags' => false,
                    'tags' => ['Navigation'],
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Види питальних речень',
                        'items' => [
                            [
                                'label' => 'Yes/No Questions — Загальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Alternative Questions — Альтернативні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Subject Questions — Питання до підмета',
                                'current' => false,
                            ],
                            [
                                'label' => 'Question Tags — Розділові питання (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
