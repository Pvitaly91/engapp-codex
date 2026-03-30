<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class BasicGrammarSentenceTypesTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'sentence-types';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Sentence Types — Види речень',
            'subtitle_html' => '<p><strong>Sentence types</strong> (види речень) — це основні категорії речень за метою висловлювання. В англійській мові є чотири основних типи: <strong>стверджувальні</strong> (affirmative), <strong>заперечні</strong> (negative), <strong>питальні</strong> (interrogative) та <strong>наказові</strong> (imperative).</p>',
            'subtitle_text' => 'Теоретичний огляд видів речень в англійській мові: стверджувальні, заперечні, питальні та наказові речення з прикладами.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Sentence Types',
                'Basic Grammar',
                'Affirmative',
                'Negative',
                'Interrogative',
                'Imperative',
                'Questions',
                'Statements',
                'Commands',
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
                        'intro' => 'У цій темі ти вивчиш <strong>чотири види речень</strong> в англійській мові: стверджувальні, заперечні, питальні та наказові — їхню структуру та особливості.',
                        'rules' => [
                            [
                                'label' => 'Affirmative',
                                'color' => 'emerald',
                                'text' => '<strong>Стверджувальні</strong> — констатують факт:',
                                'example' => 'She reads books.',
                            ],
                            [
                                'label' => 'Negative',
                                'color' => 'rose',
                                'text' => '<strong>Заперечні</strong> — заперечують дію:',
                                'example' => 'She does not read books.',
                            ],
                            [
                                'label' => 'Interrogative',
                                'color' => 'blue',
                                'text' => '<strong>Питальні</strong> — ставлять запитання:',
                                'example' => 'Does she read books?',
                            ],
                            [
                                'label' => 'Imperative',
                                'color' => 'amber',
                                'text' => '<strong>Наказові</strong> — виражають наказ або прохання:',
                                'example' => 'Read this book!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Огляд чотирьох видів речень',
                        'intro' => 'Англійські речення поділяються на чотири типи за метою висловлювання:',
                        'items' => [
                            ['label' => 'Affirmative', 'title' => 'Стверджувальне', 'subtitle' => 'Констатує факт або дію (She works here.)'],
                            ['label' => 'Negative', 'title' => 'Заперечне', 'subtitle' => 'Заперечує факт або дію (She does not work here.)'],
                            ['label' => 'Interrogative', 'title' => 'Питальне', 'subtitle' => 'Ставить запитання (Does she work here?)'],
                            ['label' => 'Imperative', 'title' => 'Наказове', 'subtitle' => 'Виражає наказ, прохання, пораду (Work harder!)'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Affirmative Sentences — Стверджувальні речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Стверджувальне речення констатує факт. Структура: <strong>Subject + Verb + Object</strong>.',
                                'examples' => [
                                    ['en' => 'I like coffee.', 'ua' => 'Я люблю каву.'],
                                    ['en' => 'She works at a hospital.', 'ua' => 'Вона працює в лікарні.'],
                                    ['en' => 'They have two children.', 'ua' => 'У них двоє дітей.'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловом TO BE',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> структура: <strong>Subject + am/is/are + Complement</strong>.',
                                'examples' => [
                                    ['en' => 'I am a student.', 'ua' => 'Я студент.'],
                                    ['en' => 'She is happy.', 'ua' => 'Вона щаслива.'],
                                    ['en' => 'They are at home.', 'ua' => 'Вони вдома.'],
                                ],
                            ],
                            [
                                'label' => 'Пунктуація',
                                'color' => 'amber',
                                'description' => 'Стверджувальне речення закінчується <strong>крапкою (.)</strong>',
                                'examples' => [
                                    ['en' => 'The sun rises in the east.', 'ua' => 'Сонце сходить на сході.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Negative Sentences — Заперечні речення',
                        'sections' => [
                            [
                                'label' => 'З допоміжним дієсловом',
                                'color' => 'emerald',
                                'description' => 'Для заперечення додаємо <strong>do/does/did + not</strong> перед основним дієсловом.',
                                'examples' => [
                                    ['en' => 'I do not like coffee.', 'ua' => 'Я не люблю каву.'],
                                    ['en' => 'She does not work here.', 'ua' => 'Вона тут не працює.'],
                                    ['en' => 'They did not come yesterday.', 'ua' => 'Вони не прийшли вчора.'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловом TO BE',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> додаємо <strong>not</strong> після нього: <strong>am/is/are + not</strong>.',
                                'examples' => [
                                    ['en' => 'I am not tired.', 'ua' => 'Я не втомлений.'],
                                    ['en' => 'She is not at home.', 'ua' => 'Її немає вдома.'],
                                    ['en' => 'They are not ready.', 'ua' => 'Вони не готові.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'amber',
                                'description' => 'У розмовній мові часто використовують <strong>скорочення</strong>.',
                                'examples' => [
                                    ['en' => "don't = do not", 'ua' => "I don't like it."],
                                    ['en' => "doesn't = does not", 'ua' => "She doesn't know."],
                                    ['en' => "isn't = is not", 'ua' => "He isn't here."],
                                    ['en' => "aren't = are not", 'ua' => "They aren't coming."],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Interrogative Sentences — Питальні речення',
                        'sections' => [
                            [
                                'label' => 'Yes/No Questions',
                                'color' => 'emerald',
                                'description' => 'Загальні питання (відповідь Yes/No). Структура: <strong>Do/Does/Did + Subject + Verb?</strong>',
                                'examples' => [
                                    ['en' => 'Do you like coffee?', 'ua' => 'Ти любиш каву?'],
                                    ['en' => 'Does she work here?', 'ua' => 'Вона тут працює?'],
                                    ['en' => 'Did they come yesterday?', 'ua' => 'Вони прийшли вчора?'],
                                ],
                            ],
                            [
                                'label' => 'Питання з TO BE',
                                'color' => 'sky',
                                'description' => 'З дієсловом <strong>to be</strong> воно виходить на перше місце: <strong>Am/Is/Are + Subject?</strong>',
                                'examples' => [
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                    ['en' => 'Is she a doctor?', 'ua' => 'Вона лікар?'],
                                    ['en' => 'Were they at the party?', 'ua' => 'Вони були на вечірці?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-Questions',
                                'color' => 'amber',
                                'description' => 'Спеціальні питання з питальними словами: <strong>What, Where, When, Why, Who, How</strong>.',
                                'examples' => [
                                    ['en' => 'What do you do?', 'ua' => 'Чим ти займаєшся?'],
                                    ['en' => 'Where does she live?', 'ua' => 'Де вона живе?'],
                                    ['en' => 'Why are you late?', 'ua' => 'Чому ти запізнився?'],
                                    ['en' => 'How did they get there?', 'ua' => 'Як вони туди дістались?'],
                                ],
                                'note' => 'Питальне речення закінчується <strong>знаком питання (?)</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Imperative Sentences — Наказові речення',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Наказове речення виражає наказ, прохання або пораду. Починається з <strong>дієслова в базовій формі</strong>, без підмета.',
                                'examples' => [
                                    ['en' => 'Open the door.', 'ua' => 'Відчини двері.'],
                                    ['en' => 'Come here.', 'ua' => 'Іди сюди.'],
                                    ['en' => 'Be quiet.', 'ua' => 'Будь тихо.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечна форма',
                                'color' => 'sky',
                                'description' => 'Для заперечного наказу використовуємо <strong>Do not (Don\'t) + Verb</strong>.',
                                'examples' => [
                                    ['en' => "Don't touch that!", 'ua' => 'Не чіпай це!'],
                                    ['en' => "Don't be late.", 'ua' => 'Не запізнюйся.'],
                                    ['en' => "Don't forget your keys.", 'ua' => 'Не забудь свої ключі.'],
                                ],
                            ],
                            [
                                'label' => 'Ввічливі форми',
                                'color' => 'amber',
                                'description' => 'Для ввічливості додаємо <strong>please</strong> або використовуємо <strong>Let\'s</strong> для пропозицій.',
                                'examples' => [
                                    ['en' => 'Please sit down.', 'ua' => 'Будь ласка, сідай.'],
                                    ['en' => 'Please be patient.', 'ua' => 'Будь ласка, будь терплячим.'],
                                    ['en' => "Let's go to the cinema.", 'ua' => 'Ходімо в кіно.'],
                                    ['en' => "Let's start the meeting.", 'ua' => 'Почнімо зустріч.'],
                                ],
                                'note' => 'Наказове речення може закінчуватися <strong>крапкою (.)</strong> або <strong>знаком оклику (!)</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Порівняльна таблиця структур',
                        'intro' => 'Структури чотирьох видів речень:',
                        'rows' => [
                            [
                                'en' => 'Affirmative',
                                'ua' => 'Стверджувальне',
                                'note' => 'S + V + O → She reads books.',
                            ],
                            [
                                'en' => 'Negative',
                                'ua' => 'Заперечне',
                                'note' => "S + do/does + not + V → She doesn't read.",
                            ],
                            [
                                'en' => 'Interrogative',
                                'ua' => 'Питальне',
                                'note' => 'Do/Does + S + V? → Does she read?',
                            ],
                            [
                                'en' => 'Imperative',
                                'ua' => 'Наказове',
                                'note' => 'V + O → Read this book!',
                            ],
                        ],
                        'warning' => '📌 Запамʼятай: в питаннях і запереченнях потрібні <strong>допоміжні дієслова</strong> (do/does/did)!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Пунктуація різних типів речень',
                        'intro' => 'Кожен тип речення має свій розділовий знак:',
                        'rows' => [
                            [
                                'en' => 'Affirmative',
                                'ua' => 'Стверджувальне',
                                'note' => 'Крапка (.) — She is a teacher.',
                            ],
                            [
                                'en' => 'Negative',
                                'ua' => 'Заперечне',
                                'note' => 'Крапка (.) — She is not a teacher.',
                            ],
                            [
                                'en' => 'Interrogative',
                                'ua' => 'Питальне',
                                'note' => 'Знак питання (?) — Is she a teacher?',
                            ],
                            [
                                'en' => 'Imperative',
                                'ua' => 'Наказове',
                                'note' => 'Крапка (.) або знак оклику (!) — Be quiet! / Be quiet.',
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
                                'title' => 'Пропуск допоміжного дієслова у запереченнях.',
                                'wrong' => 'She not likes coffee.',
                                'right' => '✅ <span class="font-mono">She does not like coffee.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск допоміжного дієслова у питаннях.',
                                'wrong' => 'You like coffee?',
                                'right' => '✅ <span class="font-mono">Do you like coffee?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Закінчення -s після does.',
                                'wrong' => 'Does she likes coffee?',
                                'right' => '✅ <span class="font-mono">Does she like coffee?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Підмет у наказовому реченні.',
                                'wrong' => 'You open the door!',
                                'right' => '✅ <span class="font-mono">Open the door!</span>',
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
                            '<strong>Стверджувальне (Affirmative):</strong> S + V + O — констатує факт, закінчується крапкою.',
                            '<strong>Заперечне (Negative):</strong> S + do/does/did + not + V — заперечує дію.',
                            '<strong>Питальне (Interrogative):</strong> Do/Does/Did + S + V? — ставить запитання, закінчується знаком питання.',
                            '<strong>Наказове (Imperative):</strong> V + O — виражає наказ або прохання, без підмета.',
                            'У запереченнях і питаннях потрібні <strong>допоміжні дієслова</strong> (do/does/did).',
                            'Після <strong>does/did</strong> дієслово вживається без закінчення -s.',
                            'Скорочені форми: <strong>don\'t, doesn\'t, isn\'t, aren\'t</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Визнач тип речення',
                        'select_intro' => 'Визнач, до якого типу належить речення.',
                        'selects' => [
                            ['label' => 'She works at a bank. (Affirmative / Negative / Interrogative / Imperative)', 'prompt' => 'Який тип?'],
                            ['label' => 'Do you speak English? (Affirmative / Negative / Interrogative / Imperative)', 'prompt' => 'Який тип?'],
                            ['label' => 'Close the window. (Affirmative / Negative / Interrogative / Imperative)', 'prompt' => 'Який тип?'],
                            ['label' => "I don't understand. (Affirmative / Negative / Interrogative / Imperative)", 'prompt' => 'Який тип?'],
                        ],
                        'options' => ['Affirmative', 'Negative', 'Interrogative', 'Imperative'],
                        'input_title' => 'Вправа 2. Перетвори речення',
                        'input_intro' => 'Перетвори стверджувальне речення на інші типи.',
                        'inputs' => [
                            ['before' => 'She likes music. → Negative:', 'after' => '→'],
                            ['before' => 'She likes music. → Question:', 'after' => '→'],
                            ['before' => 'You are quiet. → Imperative:', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'She not like coffee.',
                                'example_target' => "She doesn't like coffee.",
                            ],
                            [
                                'original' => '1. Does he likes football?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. You be quiet!',
                                'placeholder' => 'Виправ помилку',
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
                                'label' => 'Sentence Types — Види речень (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Sentence Structure — Будова речення S–V–O',
                                'current' => false,
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
