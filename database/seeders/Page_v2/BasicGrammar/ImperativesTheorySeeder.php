<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\BasicGrammar\BasicGrammarPageSeeder;

class ImperativesTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'imperatives-sit-down-dont-open-it';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => "Imperatives — Наказові речення (Sit down!, Don't open it)",
            'subtitle_html' => '<p><strong>Imperative sentences</strong> (наказові речення) — це речення, які виражають наказ, прохання, пораду або інструкцію. Вони завжди звернені до співрозмовника (you) і починаються з дієслова в базовій формі.</p>',
            'subtitle_text' => 'Теоретичний огляд наказових речень в англійській мові: структура, стверджувальна та заперечна форми, ввічливі варіанти.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Imperatives',
                'Basic Grammar',
                'Commands',
                'Instructions',
                'Requests',
                'Advice',
                'Negative Imperatives',
                'Polite Forms',
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
                        'intro' => 'У цій темі ти вивчиш <strong>наказові речення</strong> (imperatives): як давати накази, прохання, поради та інструкції англійською мовою.',
                        'rules' => [
                            [
                                'label' => 'Affirmative',
                                'color' => 'emerald',
                                'text' => '<strong>Стверджувальна форма</strong> — починається з дієслова:',
                                'example' => 'Sit down. / Open the door.',
                            ],
                            [
                                'label' => 'Negative',
                                'color' => 'rose',
                                'text' => "<strong>Заперечна форма</strong> — Don't + дієслово:",
                                'example' => "Don't open it. / Don't be late.",
                            ],
                            [
                                'label' => 'Polite',
                                'color' => 'blue',
                                'text' => '<strong>Ввічлива форма</strong> — з Please:',
                                'example' => 'Please sit down. / Close the door, please.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке наказове речення?',
                        'intro' => 'Imperative sentence — це речення, яке виражає:',
                        'items' => [
                            ['label' => 'Command', 'title' => 'Наказ', 'subtitle' => 'Stop! / Be quiet! / Leave now!'],
                            ['label' => 'Request', 'title' => 'Прохання', 'subtitle' => 'Please help me. / Pass the salt, please.'],
                            ['label' => 'Advice', 'title' => 'Пораду', 'subtitle' => 'Take an umbrella. / Get some rest.'],
                            ['label' => 'Instruction', 'title' => 'Інструкцію', 'subtitle' => 'Turn left. / Press the button.'],
                            ['label' => 'Warning', 'title' => 'Попередження', 'subtitle' => 'Be careful! / Watch out!'],
                            ['label' => 'Invitation', 'title' => 'Запрошення', 'subtitle' => 'Come in! / Have a seat.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Структура наказового речення',
                        'sections' => [
                            [
                                'label' => 'Базова структура',
                                'color' => 'emerald',
                                'description' => 'Наказове речення починається з <strong>дієслова в базовій формі</strong> (без to). Підмет (you) <strong>не вживається</strong>.',
                                'examples' => [
                                    ['en' => 'Sit down.', 'ua' => 'Сідай.'],
                                    ['en' => 'Open the window.', 'ua' => 'Відчини вікно.'],
                                    ['en' => 'Listen to me.', 'ua' => 'Слухай мене.'],
                                ],
                            ],
                            [
                                'label' => 'Без підмета',
                                'color' => 'sky',
                                'description' => 'На відміну від інших речень, наказове <strong>не має підмета</strong>. Підмет (you) мається на увазі.',
                                'examples' => [
                                    ['en' => '(You) Close the door.', 'ua' => '(Ти) Зачини двері.'],
                                    ['en' => '(You) Be quiet.', 'ua' => '(Ти) Будь тихо.'],
                                    ['en' => '(You) Come here.', 'ua' => '(Ти) Іди сюди.'],
                                ],
                                'note' => 'Підмет <strong>you</strong> іноді вживається для емфази або контрасту: <em>You sit here, and you sit there.</em>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Стверджувальна форма (Affirmative)',
                        'sections' => [
                            [
                                'label' => 'Структура: Verb + Object',
                                'color' => 'emerald',
                                'description' => 'Стверджувальне наказове речення: <strong>дієслово + додаток</strong>.',
                                'examples' => [
                                    ['en' => 'Open the door.', 'ua' => 'Відчини двері.'],
                                    ['en' => 'Read this book.', 'ua' => 'Прочитай цю книгу.'],
                                    ['en' => 'Write your name.', 'ua' => 'Напиши своє імʼя.'],
                                    ['en' => 'Call me tomorrow.', 'ua' => 'Зателефонуй мені завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Без додатка',
                                'color' => 'sky',
                                'description' => 'Деякі наказові речення складаються лише з дієслова.',
                                'examples' => [
                                    ['en' => 'Stop!', 'ua' => 'Зупинись!'],
                                    ['en' => 'Wait!', 'ua' => 'Зачекай!'],
                                    ['en' => 'Listen!', 'ua' => 'Слухай!'],
                                    ['en' => 'Run!', 'ua' => 'Біжи!'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловом BE',
                                'color' => 'amber',
                                'description' => 'З дієсловом <strong>be</strong> використовуємо базову форму.',
                                'examples' => [
                                    ['en' => 'Be careful!', 'ua' => 'Будь обережний!'],
                                    ['en' => 'Be quiet.', 'ua' => 'Будь тихо.'],
                                    ['en' => 'Be patient.', 'ua' => 'Будь терплячим.'],
                                    ['en' => 'Be on time.', 'ua' => 'Будь вчасно.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => "4. Заперечна форма (Negative) — Don't",
                        'sections' => [
                            [
                                'label' => "Структура: Don't + Verb",
                                'color' => 'rose',
                                'description' => "Заперечна форма утворюється за допомогою <strong>Don't (Do not)</strong> перед дієсловом.",
                                'examples' => [
                                    ['en' => "Don't open it.", 'ua' => 'Не відкривай це.'],
                                    ['en' => "Don't touch that.", 'ua' => 'Не чіпай це.'],
                                    ['en' => "Don't forget your keys.", 'ua' => 'Не забудь свої ключі.'],
                                    ['en' => "Don't worry.", 'ua' => 'Не хвилюйся.'],
                                ],
                            ],
                            [
                                'label' => "Don't be...",
                                'color' => 'sky',
                                'description' => "З дієсловом <strong>be</strong>: <strong>Don't be</strong> + прикметник/іменник.",
                                'examples' => [
                                    ['en' => "Don't be late.", 'ua' => 'Не запізнюйся.'],
                                    ['en' => "Don't be silly.", 'ua' => 'Не будь дурним.'],
                                    ['en' => "Don't be afraid.", 'ua' => 'Не бійся.'],
                                    ['en' => "Don't be rude.", 'ua' => 'Не будь грубим.'],
                                ],
                            ],
                            [
                                'label' => 'Повна форма',
                                'color' => 'amber',
                                'description' => 'У формальному стилі використовують повну форму <strong>Do not</strong>.',
                                'examples' => [
                                    ['en' => 'Do not enter.', 'ua' => 'Не входити. (на знаках)'],
                                    ['en' => 'Do not disturb.', 'ua' => 'Не турбувати.'],
                                    ['en' => 'Do not smoke.', 'ua' => 'Не курити.'],
                                ],
                                'note' => '<strong>Do not</strong> частіше зустрічається на знаках, в інструкціях та офіційних документах.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Ввічливі форми (Polite Forms)',
                        'sections' => [
                            [
                                'label' => 'Please',
                                'color' => 'emerald',
                                'description' => 'Додавання <strong>please</strong> робить наказ ввічливішим. Може стояти на початку або в кінці.',
                                'examples' => [
                                    ['en' => 'Please sit down.', 'ua' => 'Будь ласка, сідай.'],
                                    ['en' => 'Close the door, please.', 'ua' => 'Зачини двері, будь ласка.'],
                                    ['en' => 'Please be quiet.', 'ua' => 'Будь ласка, будь тихо.'],
                                    ['en' => 'Help me, please.', 'ua' => 'Допоможи мені, будь ласка.'],
                                ],
                            ],
                            [
                                'label' => "Let's — для групи",
                                'color' => 'sky',
                                'description' => "<strong>Let's</strong> (Let us) використовується для пропозицій, коли мовець включає себе.",
                                'examples' => [
                                    ['en' => "Let's go!", 'ua' => 'Ходімо!'],
                                    ['en' => "Let's start.", 'ua' => 'Почнімо.'],
                                    ['en' => "Let's have lunch.", 'ua' => 'Давай пообідаємо.'],
                                    ['en' => "Let's not argue.", 'ua' => 'Давай не сперечатися.'],
                                ],
                            ],
                            [
                                'label' => 'Альтернативні форми',
                                'color' => 'amber',
                                'description' => 'Ще ввічливіші способи висловити прохання:',
                                'examples' => [
                                    ['en' => 'Could you open the window?', 'ua' => 'Не міг би ти відчинити вікно?'],
                                    ['en' => 'Would you mind closing the door?', 'ua' => 'Ти не проти зачинити двері?'],
                                    ['en' => 'Can you help me, please?', 'ua' => 'Можеш допомогти мені, будь ласка?'],
                                ],
                                'note' => 'Ці форми є питаннями, але функціонують як ввічливі прохання.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Використання наказових речень',
                        'sections' => [
                            [
                                'label' => 'Накази та команди',
                                'color' => 'emerald',
                                'description' => 'Прямі накази від батьків, вчителів, начальників тощо.',
                                'examples' => [
                                    ['en' => 'Clean your room!', 'ua' => 'Прибери свою кімнату!'],
                                    ['en' => 'Finish your homework.', 'ua' => 'Закінчи домашнє завдання.'],
                                    ['en' => 'Submit the report by Friday.', 'ua' => 'Здай звіт до пʼятниці.'],
                                ],
                            ],
                            [
                                'label' => 'Інструкції',
                                'color' => 'sky',
                                'description' => 'Покрокові вказівки в рецептах, інструкціях, маршрутах.',
                                'examples' => [
                                    ['en' => 'Mix the flour and eggs.', 'ua' => 'Змішай борошно і яйця.'],
                                    ['en' => 'Turn left at the corner.', 'ua' => 'Поверни наліво на розі.'],
                                    ['en' => 'Press the green button.', 'ua' => 'Натисни зелену кнопку.'],
                                ],
                            ],
                            [
                                'label' => 'Поради та попередження',
                                'color' => 'amber',
                                'description' => 'Рекомендації та застереження.',
                                'examples' => [
                                    ['en' => 'Take an umbrella. It might rain.', 'ua' => 'Візьми парасольку. Може піти дощ.'],
                                    ['en' => 'Be careful on the ice!', 'ua' => 'Будь обережний на льоду!'],
                                    ['en' => "Don't eat too much sugar.", 'ua' => 'Не їж занадто багато цукру.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Порівняння форм',
                        'intro' => 'Стверджувальна vs заперечна форма:',
                        'rows' => [
                            [
                                'en' => 'Affirmative',
                                'ua' => 'Стверджувальна',
                                'note' => 'Sit down. / Open it. / Be quiet.',
                            ],
                            [
                                'en' => 'Negative',
                                'ua' => 'Заперечна',
                                'note' => "Don't sit. / Don't open it. / Don't be loud.",
                            ],
                            [
                                'en' => 'Polite (please)',
                                'ua' => 'Ввічлива',
                                'note' => 'Please sit down. / Sit down, please.',
                            ],
                            [
                                'en' => "Let's (group)",
                                'ua' => 'Для групи',
                                'note' => "Let's sit down. / Let's not argue.",
                            ],
                        ],
                        'warning' => "📌 Формула: <strong>Verb + Object</strong> або <strong>Don't + Verb + Object</strong>",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Пунктуація',
                        'intro' => 'Наказові речення можуть закінчуватися:',
                        'rows' => [
                            [
                                'en' => 'Period (.)',
                                'ua' => 'Крапка',
                                'note' => 'Sit down. / Close the door. — нейтральний тон',
                            ],
                            [
                                'en' => 'Exclamation mark (!)',
                                'ua' => 'Знак оклику',
                                'note' => 'Stop! / Be careful! — сильна емоція або терміновість',
                            ],
                        ],
                        'warning' => '⚠️ Знак оклику (!) використовуй для термінових команд або попереджень.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Додавання підмета you.',
                                'wrong' => 'You open the door!',
                                'right' => '✅ <span class="font-mono">Open the door!</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Використання No замість Don't.",
                                'wrong' => 'No touch it!',
                                'right' => "✅ <span class=\"font-mono\">Don't touch it!</span>",
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Дієслово з to.',
                                'wrong' => 'To sit down!',
                                'right' => '✅ <span class="font-mono">Sit down!</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Don't з be — пропуск be.",
                                'wrong' => "Don't late!",
                                'right' => "✅ <span class=\"font-mono\">Don't be late!</span>",
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
                            '<strong>Наказове речення</strong> виражає наказ, прохання, пораду або інструкцію.',
                            '<strong>Структура:</strong> Verb + Object (без підмета).',
                            '<strong>Стверджувальна форма:</strong> Open the door. / Be quiet.',
                            "<strong>Заперечна форма:</strong> Don't + Verb → Don't open it. / Don't be late.",
                            '<strong>Ввічлива форма:</strong> Please sit down. / Sit down, please.',
                            "<strong>Let's</strong> — для пропозицій групі: Let's go!",
                            'Підмет <strong>you</strong> зазвичай не вживається.',
                            'Пунктуація: крапка (.) або знак оклику (!).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильну форму',
                        'select_intro' => 'Обери правильний варіант наказового речення.',
                        'selects' => [
                            ['label' => "a) Don't be late. / b) Don't late.", 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) You close the door! / b) Close the door!', 'prompt' => 'Який варіант правильний?'],
                            ['label' => "a) No touch it! / b) Don't touch it!", 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Утвори заперечну форму',
                        'input_intro' => "Перетвори стверджувальне речення на заперечне (з Don't).",
                        'inputs' => [
                            ['before' => 'Open the window. →', 'after' => ''],
                            ['before' => 'Be loud. →', 'after' => ''],
                            ['before' => 'Forget your keys. →', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку у наказовому реченні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'You sit down!',
                                'example_target' => 'Sit down!',
                            ],
                            [
                                'original' => '1. To be quiet!',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => "2. Don't late for class!",
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
                                'label' => 'Imperatives — Наказові речення (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Sentence Types — Види речень',
                                'current' => false,
                            ],
                            [
                                'label' => 'Sentence Structure — Будова речення S–V–O',
                                'current' => false,
                            ],
                            [
                                'label' => 'Parts of Speech — Частини мови',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
