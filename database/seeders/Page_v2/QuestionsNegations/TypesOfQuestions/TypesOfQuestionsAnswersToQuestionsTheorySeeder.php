<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Page_v2\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsAnswersToQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'answers-to-questions-yes-i-do-no-i-dont';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Answers to Questions — Короткі і повні відповіді (Yes, I do / No, I don\'t)',
            'subtitle_html' => '<p><strong>Answers to questions</strong> (відповіді на питання) — важливий аспект англійської мови. У розмові зазвичай використовуються короткі відповіді з допоміжним дієсловом (Yes, I do / No, I don\'t), а не просто "Yes" або "No". Повні відповіді використовуються для більшої чіткості.</p>',
            'subtitle_text' => 'Теоретичний огляд відповідей на питання в англійській мові: короткі та повні відповіді, правила формування з різними типами дієслів.',
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
                'Short Answers',
                'Grammar',
                'Theory',
            ],
            // BLOCK-FIRST TAGGING: Base tags inherited by all blocks (controlled inheritance)
            'base_tags' => [
                'Types of Questions',
                'Question Forms',
                'Short Answers',
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
                    'tags' => ['Introduction', 'Overview', 'Full Answers', 'CEFR A1', 'CEFR A2'],
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>відповіді на питання (Answers to Questions)</strong> — як правильно відповідати на різні типи питань, використовуючи короткі та повні відповіді.',
                        'rules' => [
                            [
                                'label' => 'КОРОТКІ ВІДПОВІДІ',
                                'color' => 'emerald',
                                'text' => '<strong>Yes/No + підмет + допоміжне</strong>:',
                                'example' => 'Do you like coffee? — Yes, I do.',
                            ],
                            [
                                'label' => 'ПОВНІ ВІДПОВІДІ',
                                'color' => 'blue',
                                'text' => '<strong>Повторюємо все речення</strong>:',
                                'example' => 'Do you like coffee? — Yes, I like coffee.',
                            ],
                            [
                                'label' => 'НЕ "YES/NO"',
                                'color' => 'amber',
                                'text' => '<strong>Уникаємо лише "Yes"/"No"</strong> у формальному контексті:',
                                'example' => '❌ Yes. ✅ Yes, I do.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A1',
                    'uuid_key' => 'forms-grid-types',
                    // BLOCK-FIRST: Answer types block detailed tags
                    'tags' => ['Definition', 'Full Answers', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '1. Типи відповідей',
                        'intro' => 'В англійській мові є кілька способів відповісти на питання:',
                        'items' => [
                            ['label' => 'Тільки Yes/No', 'title' => 'Yes. / No.', 'subtitle' => 'Дуже неформально, рідко використовується'],
                            ['label' => 'Короткі відповіді', 'title' => 'Yes, I do. / No, I don\'t.', 'subtitle' => 'Стандартні, найпоширеніші'],
                            ['label' => 'Повні відповіді', 'title' => 'Yes, I like coffee.', 'subtitle' => 'Для ясності та деталізації'],
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
                        'title' => '2. Короткі відповіді з DO/DOES/DID',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'У Present Simple використовуємо <strong>do/does</strong> у відповіді:',
                                'examples' => [
                                    ['en' => 'Q: Do you like coffee? — A: Yes, I do. / No, I don\'t.', 'ua' => 'Питання з do → відповідь з do'],
                                    ['en' => 'Q: Does she work here? — A: Yes, she does. / No, she doesn\'t.', 'ua' => 'Питання з does → відповідь з does'],
                                    ['en' => 'Q: Do they know? — A: Yes, they do. / No, they don\'t.', 'ua' => 'Повторюємо допоміжне дієслово'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'sky',
                                'description' => 'У Past Simple використовуємо <strong>did</strong>:',
                                'examples' => [
                                    ['en' => 'Q: Did you see the movie? — A: Yes, I did. / No, I didn\'t.', 'ua' => 'Питання з did → відповідь з did'],
                                    ['en' => 'Q: Did she call you? — A: Yes, she did. / No, she didn\'t.', 'ua' => 'Завжди did для всіх осіб'],
                                    ['en' => 'Q: Did they arrive? — A: Yes, they did. / No, they didn\'t.', 'ua' => 'Коротко та ясно'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'purple',
                                'description' => 'Формула короткої відповіді:',
                                'examples' => [
                                    ['en' => 'Yes/No + Subject + do/does/did (NOT)', 'ua' => 'Так/Ні + Підмет + допоміжне'],
                                ],
                                'note' => '📌 Основне дієслово НЕ повторюємо у короткій відповіді',
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
                    'tags' => ['To Be', 'Be (am/is/are/was/were)', 'Contractions', 'CEFR A1'],
                    'body' => json_encode([
                        'title' => '3. Короткі відповіді з TO BE',
                        'sections' => [
                            [
                                'label' => 'Present Simple — AM/IS/ARE',
                                'color' => 'blue',
                                'description' => 'З <strong>to be</strong> повторюємо відповідну форму:',
                                'examples' => [
                                    ['en' => 'Q: Are you ready? — A: Yes, I am. / No, I\'m not.', 'ua' => 'Питання з are → відповідь з am (для I)'],
                                    ['en' => 'Q: Is she happy? — A: Yes, she is. / No, she isn\'t.', 'ua' => 'Питання з is → відповідь з is'],
                                    ['en' => 'Q: Are they at home? — A: Yes, they are. / No, they aren\'t.', 'ua' => 'Питання з are → відповідь з are'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple — WAS/WERE',
                                'color' => 'amber',
                                'description' => 'У минулому часі використовуємо was/were:',
                                'examples' => [
                                    ['en' => 'Q: Were you tired? — A: Yes, I was. / No, I wasn\'t.', 'ua' => 'Питання з were → відповідь з was (для I)'],
                                    ['en' => 'Q: Was it difficult? — A: Yes, it was. / No, it wasn\'t.', 'ua' => 'Питання з was → відповідь з was'],
                                    ['en' => 'Q: Were they happy? — A: Yes, they were. / No, they weren\'t.', 'ua' => 'Питання з were → відповідь з were'],
                                ],
                            ],
                            [
                                'label' => 'Скорочення',
                                'color' => 'rose',
                                'description' => 'У негативних коротких відповідях зазвичай використовуємо скорочення:',
                                'examples' => [
                                    ['en' => 'No, I\'m not. (NOT: No, I am not.)', 'ua' => 'Скорочена форма природніша'],
                                    ['en' => 'No, she isn\'t. / No, she\'s not.', 'ua' => 'Обидва варіанти правильні'],
                                    ['en' => 'No, they aren\'t. / No, they\'re not.', 'ua' => 'Скорочення у розмові'],
                                ],
                                'note' => '📌 У позитивних відповідях зазвичай повна форма: Yes, I am.',
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
                    'tags' => ['Modal Verbs', 'Can/Could', 'Will/Would', 'Should', 'Must', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '4. Короткі відповіді з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'CAN / COULD',
                                'color' => 'emerald',
                                'description' => 'З <strong>can/could</strong> повторюємо модальне у відповіді:',
                                'examples' => [
                                    ['en' => 'Q: Can you swim? — A: Yes, I can. / No, I can\'t.', 'ua' => 'Питання з can → відповідь з can'],
                                    ['en' => 'Q: Could she help? — A: Yes, she could. / No, she couldn\'t.', 'ua' => 'Питання з could → відповідь з could'],
                                ],
                            ],
                            [
                                'label' => 'WILL / WOULD',
                                'color' => 'sky',
                                'description' => 'З <strong>will/would</strong> також повторюємо:',
                                'examples' => [
                                    ['en' => 'Q: Will you come? — A: Yes, I will. / No, I won\'t.', 'ua' => 'Питання з will → відповідь з will'],
                                    ['en' => 'Q: Would you like coffee? — A: Yes, I would. / No, I wouldn\'t.', 'ua' => 'Питання з would → відповідь з would'],
                                ],
                            ],
                            [
                                'label' => 'SHOULD / MUST / MAY',
                                'color' => 'purple',
                                'description' => 'Інші модальні працюють за тим самим принципом:',
                                'examples' => [
                                    ['en' => 'Q: Should we go? — A: Yes, we should. / No, we shouldn\'t.', 'ua' => 'Повторюємо should'],
                                    ['en' => 'Q: Must I wait? — A: Yes, you must. / No, you needn\'t.', 'ua' => 'Must або needn\'t у негативній формі'],
                                ],
                                'note' => '📌 Структура: Yes/No + Subject + Modal',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-have-has',
                    // BLOCK-FIRST: Have/Has block detailed tags
                    'tags' => ['Have/Has/Had', 'Have Got', 'Present Perfect', 'British vs American', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '5. Короткі відповіді з HAVE/HAS',
                        'sections' => [
                            [
                                'label' => 'Present Perfect',
                                'color' => 'amber',
                                'description' => 'У Present Perfect також використовуємо have/has:',
                                'examples' => [
                                    ['en' => 'Q: Have you seen it? — A: Yes, I have. / No, I haven\'t.', 'ua' => 'Питання з have → відповідь з have'],
                                    ['en' => 'Q: Has she finished? — A: Yes, she has. / No, she hasn\'t.', 'ua' => 'Питання з has → відповідь з has'],
                                ],
                            ],
                            [
                                'label' => 'HAVE GOT',
                                'color' => 'blue',
                                'description' => 'З <strong>have got</strong> повторюємо have/has:',
                                'examples' => [
                                    ['en' => 'Q: Have you got a car? — A: Yes, I have. / No, I haven\'t.', 'ua' => 'Питання з have → відповідь з have'],
                                    ['en' => 'Q: Has she got a dog? — A: Yes, she has. / No, she hasn\'t.', 'ua' => 'Питання з has → відповідь з has'],
                                ],
                            ],
                            [
                                'label' => 'Американський варіант',
                                'color' => 'rose',
                                'description' => 'В американському варіанті з <strong>have</strong> (володіння) часто do:',
                                'examples' => [
                                    ['en' => 'Q: Do you have a car? — A: Yes, I do. / No, I don\'t.', 'ua' => 'Американський варіант'],
                                ],
                                'note' => '📌 Британський варіант: have got, американський варіант: have з do',
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
                    'tags' => ['Present Continuous', 'Past Continuous', 'Advanced Tenses', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '6. Короткі відповіді з іншими часами',
                        'sections' => [
                            [
                                'label' => 'Present Continuous',
                                'color' => 'emerald',
                                'description' => 'У Present Continuous використовуємо am/is/are:',
                                'examples' => [
                                    ['en' => 'Q: Are you working? — A: Yes, I am. / No, I\'m not.', 'ua' => 'Відповідь з am/is/are'],
                                    ['en' => 'Q: Is she coming? — A: Yes, she is. / No, she isn\'t.', 'ua' => 'Повторюємо to be'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'sky',
                                'description' => 'У Past Continuous використовуємо was/were:',
                                'examples' => [
                                    ['en' => 'Q: Were you sleeping? — A: Yes, I was. / No, I wasn\'t.', 'ua' => 'Відповідь з was/were'],
                                    ['en' => 'Q: Was she studying? — A: Yes, she was. / No, she wasn\'t.', 'ua' => 'Повторюємо was/were'],
                                ],
                            ],
                            [
                                'label' => 'Загальне правило',
                                'color' => 'purple',
                                'description' => 'У короткій відповіді завжди повторюємо допоміжне дієслово з питання:',
                                'examples' => [
                                    ['en' => 'Do → do/does/did', 'ua' => 'Повторюємо do/does/did'],
                                    ['en' => 'To be → am/is/are/was/were', 'ua' => 'Повторюємо форму to be'],
                                    ['en' => 'Modal → can/will/should...', 'ua' => 'Повторюємо модальне'],
                                ],
                                'note' => '📌 Короткі відповіді = Yes/No + Subject + Auxiliary',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-full-answers',
                    // BLOCK-FIRST: Full answers block detailed tags
                    'tags' => ['Full Answers', 'Additional Information', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '7. Повні відповіді',
                        'sections' => [
                            [
                                'label' => 'Коли використовувати',
                                'color' => 'blue',
                                'description' => 'Повні відповіді використовуємо для <strong>ясності</strong> або <strong>додаткової інформації</strong>:',
                                'examples' => [
                                    ['en' => 'Q: Do you like coffee? — A: Yes, I like coffee very much.', 'ua' => 'Додаємо деталі'],
                                    ['en' => 'Q: Did she call? — A: Yes, she called me this morning.', 'ua' => 'Додаємо час'],
                                    ['en' => 'Q: Are they coming? — A: No, they aren\'t coming. They\'re busy.', 'ua' => 'Пояснюємо причину'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'amber',
                                'description' => 'У повній відповіді повторюємо все речення:',
                                'examples' => [
                                    ['en' => 'Q: Do you work here? — A: Yes, I work here.', 'ua' => 'Повне речення'],
                                    ['en' => 'Q: Is she a teacher? — A: Yes, she is a teacher.', 'ua' => 'Все речення'],
                                    ['en' => 'Q: Can you swim? — A: No, I can\'t swim.', 'ua' => 'Повна форма'],
                                ],
                            ],
                            [
                                'label' => 'Комбінація',
                                'color' => 'emerald',
                                'description' => 'Часто комбінуємо коротку і повну відповідь:',
                                'examples' => [
                                    ['en' => 'Q: Do you speak English? — A: Yes, I do. I speak English and French.', 'ua' => 'Коротка + додаткова інформація'],
                                    ['en' => 'Q: Did you like the movie? — A: No, I didn\'t. It was too long.', 'ua' => 'Коротка + пояснення'],
                                ],
                                'note' => '📌 Коротка відповідь + додаткова інформація = природна розмова',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-wh-answers',
                    // BLOCK-FIRST: Wh-answers block detailed tags
                    'tags' => ['Wh-Questions', 'Concrete Information', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '8. Відповіді на Wh-питання',
                        'sections' => [
                            [
                                'label' => 'Конкретна інформація',
                                'color' => 'purple',
                                'description' => 'На Wh-питання НЕ відповідаємо Yes/No, а даємо конкретну інформацію:',
                                'examples' => [
                                    ['en' => 'Q: What do you like? — A: I like coffee. (NOT: Yes)', 'ua' => 'Відповідаємо на питання "що"'],
                                    ['en' => 'Q: Where do you live? — A: I live in Kyiv.', 'ua' => 'Відповідаємо на питання "де"'],
                                    ['en' => 'Q: When did you arrive? — A: I arrived yesterday.', 'ua' => 'Відповідаємо на питання "коли"'],
                                ],
                            ],
                            [
                                'label' => 'Короткі відповіді на Wh-питання',
                                'color' => 'sky',
                                'description' => 'Можна відповідати коротко без повного речення:',
                                'examples' => [
                                    ['en' => 'Q: Where are you from? — A: From Ukraine. / I\'m from Ukraine.', 'ua' => 'Коротко або повно'],
                                    ['en' => 'Q: What time is it? — A: 5 o\'clock. / It\'s 5 o\'clock.', 'ua' => 'Обидва варіанти ОК'],
                                    ['en' => 'Q: Who is she? — A: My sister. / She\'s my sister.', 'ua' => 'Коротка або повна'],
                                ],
                                'note' => '📌 На Wh-питання даємо конкретну інформацію, не Yes/No',
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
                    'tags' => ['Summary', 'Quick Reference', 'All Structures', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '9. Швидка таблиця відповідей',
                        'intro' => 'Як формувати короткі відповіді з різними дієсловами:',
                        'rows' => [
                            [
                                'en' => 'DO/DOES/DID',
                                'ua' => 'Прості часи',
                                'note' => 'Yes, I do. / No, I don\'t. (does/doesn\'t, did/didn\'t)',
                            ],
                            [
                                'en' => 'TO BE',
                                'ua' => 'Дієслово to be',
                                'note' => 'Yes, I am. / No, I\'m not. (is/isn\'t, are/aren\'t)',
                            ],
                            [
                                'en' => 'MODALS',
                                'ua' => 'Модальні дієслова',
                                'note' => 'Yes, I can. / No, I can\'t. (will/won\'t, should/shouldn\'t)',
                            ],
                            [
                                'en' => 'HAVE/HAS',
                                'ua' => 'Have got, Perfect',
                                'note' => 'Yes, I have. / No, I haven\'t. (has/hasn\'t)',
                            ],
                            [
                                'en' => 'CONTINUOUS',
                                'ua' => 'Тривалі часи',
                                'note' => 'Yes, I am. / No, I\'m not. (is/isn\'t, are/aren\'t)',
                            ],
                        ],
                        'warning' => '📌 Основне правило: <strong>повторюємо допоміжне дієслово з питання</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'mistakes-grid',
                    // BLOCK-FIRST: Common mistakes block detailed tags
                    'tags' => ['Common Mistakes', 'Auxiliary Verbs', 'Grammar Errors', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '10. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Тільки Yes/No без допоміжного дієслова.',
                                'wrong' => '❌ Q: Do you like it? A: Yes. (неповна відповідь)',
                                'right' => '✅ <span class="font-mono">Q: Do you like it? A: Yes, I do.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Повторення основного дієслова у короткій відповіді.',
                                'wrong' => '❌ Q: Do you like it? A: Yes, I like.',
                                'right' => '✅ <span class="font-mono">Q: Do you like it? A: Yes, I do.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильне допоміжне дієслово у відповіді.',
                                'wrong' => '❌ Q: Can you swim? A: Yes, I do.',
                                'right' => '✅ <span class="font-mono">Q: Can you swim? A: Yes, I can.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Yes/No на Wh-питання.',
                                'wrong' => '❌ Q: Where do you live? A: Yes, in Kyiv.',
                                'right' => '✅ <span class="font-mono">Q: Where do you live? A: In Kyiv. / I live in Kyiv.</span>',
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
                        'title' => '11. Короткий конспект',
                        'items' => [
                            '<strong>Короткі відповіді</strong> — стандартний спосіб відповідати на Yes/No питання.',
                            '<strong>Структура</strong>: Yes/No + Subject + Auxiliary (Yes, I do / No, I don\'t).',
                            '<strong>З do/does/did</strong>: повторюємо do/does/did у відповіді.',
                            '<strong>З to be</strong>: повторюємо am/is/are/was/were у відповіді.',
                            '<strong>З модальними</strong>: повторюємо модальне дієслово (can, will, should).',
                            '<strong>З have</strong>: повторюємо have/has (have got, Present Perfect).',
                            '<strong>Повні відповіді</strong> — для ясності або додаткової інформації.',
                            '<strong>Уникаємо</strong> тільки "Yes" або "No" у формальному контексті.',
                            '<strong>На Wh-питання</strong> НЕ відповідаємо Yes/No, а даємо конкретну інформацію.',
                            '<strong>Комбінація</strong>: коротка відповідь + додаткові деталі = природна розмова.',
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
                        'title' => '12. Практика',
                        'select_title' => 'Вправа 1. Обери правильну відповідь',
                        'select_intro' => 'Обери правильну коротку відповідь на питання.',
                        'selects' => [
                            ['label' => 'Q: Do you like coffee? A: (Yes / Yes, I like / Yes, I do)', 'prompt' => 'Яка відповідь правильна?'],
                            ['label' => 'Q: Is she happy? A: (Yes / Yes, she happy / Yes, she is)', 'prompt' => 'Яка відповідь правильна?'],
                            ['label' => 'Q: Can you swim? A: (Yes / Yes, I swim / Yes, I can)', 'prompt' => 'Яка відповідь правильна?'],
                            ['label' => 'Q: Did they come? A: (No / No, they come / No, they didn\'t)', 'prompt' => 'Яка відповідь правильна?'],
                        ],
                        'options' => ['Перший варіант', 'Другий варіант', 'Третій варіант'],
                        'input_title' => 'Вправа 2. Дай коротку відповідь',
                        'input_intro' => 'Дай правильну коротку відповідь на питання.',
                        'inputs' => [
                            ['before' => 'Q: Do you speak English? A: Yes, ', 'after' => '.'],
                            ['before' => 'Q: Is she a teacher? A: No, ', 'after' => '.'],
                            ['before' => 'Q: Can they help? A: Yes, ', 'after' => '.'],
                            ['before' => 'Q: Did you see it? A: No, ', 'after' => '.'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у відповіді.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Q: Do you like it? A: Yes.',
                                'example_target' => 'Q: Do you like it? A: Yes, I do.',
                            ],
                            [
                                'original' => 'Q: Can you swim? A: Yes, I do.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => 'Q: Is she happy? A: Yes, she happy.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => 'Q: Do you like it? A: Yes, I like.',
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
                                'label' => 'Question Tags — Розділові питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Negative Questions — Заперечні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Answers to Questions — Відповіді на питання (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
