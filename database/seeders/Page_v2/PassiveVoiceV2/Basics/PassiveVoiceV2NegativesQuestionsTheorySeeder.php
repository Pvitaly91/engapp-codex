<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2NegativesQuestionsTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-negatives-questions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Заперечення та питання у пасивному стані',
            'subtitle_html' => '<p><strong>Заперечення та питання у пасиві</strong> утворюються за допомогою дієслова <strong>be</strong>. Для заперечення додаємо <strong>not</strong> після be, для питань — виносимо be на перше місце.</p>',
            'subtitle_text' => 'Заперечення та питання у пасиві: структура, Yes/No питання, Wh-питання, короткі відповіді та практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан V2',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Negatives',
                'Questions',
                'Заперечення',
                'Питання',
                'A2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти вивчиш, як утворювати <strong>заперечення та питання</strong> у пасивному стані. Ключову роль відіграє дієслово <strong>be</strong>, яке змінюється залежно від часу.',
                        'rules' => [
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + be + not + V3</strong>:',
                                'example' => 'The letter is not written. — Лист не написаний.',
                            ],
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'text' => '<strong>Be + Subject + V3?</strong>:',
                                'example' => 'Is the letter written? — Лист написаний?',
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'amber',
                                'text' => '<strong>Wh-word + be + Subject + V3?</strong>:',
                                'example' => 'When was the letter written? — Коли був написаний лист?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - заперечення в різних часах
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Заперечення у різних часах',
                        'intro' => 'Структура заперечення: <strong>Subject + be + not + V3</strong>:',
                        'items' => [
                            [
                                'label' => 'Present Simple',
                                'title' => 'am/is/are + not + V3',
                                'subtitle' => 'The letter is not written. — Лист не написаний.',
                            ],
                            [
                                'label' => 'Past Simple',
                                'title' => 'was/were + not + V3',
                                'subtitle' => 'The letter was not written. — Лист не був написаний.',
                            ],
                            [
                                'label' => 'Future Simple',
                                'title' => 'will + not + be + V3',
                                'subtitle' => 'The letter will not be written. — Лист не буде написаний.',
                            ],
                            [
                                'label' => 'Present Perfect',
                                'title' => 'has/have + not + been + V3',
                                'subtitle' => 'The letter has not been written. — Лист не написаний.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення приклади
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Заперечення: приклади',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => '<strong>am/is/are + not + V3</strong>:',
                                'examples' => [
                                    ['en' => 'English is not spoken here.', 'ua' => 'Тут не розмовляють англійською.'],
                                    ['en' => 'The rooms are not cleaned daily.', 'ua' => 'Кімнати не прибираються щодня.'],
                                    ['en' => "The door isn't locked at night.", 'ua' => 'Двері не замикаються вночі.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => '<strong>was/were + not + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The report was not submitted on time.', 'ua' => 'Звіт не був поданий вчасно.'],
                                    ['en' => 'The letters were not sent yesterday.', 'ua' => 'Листи не були відправлені вчора.'],
                                    ['en' => "The window wasn't broken.", 'ua' => 'Вікно не було розбите.'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'amber',
                                'description' => '<strong>will not (won\'t) + be + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The project will not be finished by Friday.', 'ua' => 'Проєкт не буде завершено до п\'ятниці.'],
                                    ['en' => "You won't be contacted today.", 'ua' => 'З вами не зв\'яжуться сьогодні.'],
                                    ['en' => 'The meeting will not be held tomorrow.', 'ua' => 'Зустріч не відбудеться завтра.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - питання в різних часах
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Yes/No питання у різних часах',
                        'intro' => 'Структура: <strong>Be + Subject + V3?</strong>:',
                        'items' => [
                            [
                                'label' => 'Present Simple',
                                'title' => 'Am/Is/Are + Subject + V3?',
                                'subtitle' => 'Is the letter written? — Лист написаний?',
                            ],
                            [
                                'label' => 'Past Simple',
                                'title' => 'Was/Were + Subject + V3?',
                                'subtitle' => 'Was the letter written? — Лист був написаний?',
                            ],
                            [
                                'label' => 'Future Simple',
                                'title' => 'Will + Subject + be + V3?',
                                'subtitle' => 'Will the letter be written? — Лист буде написаний?',
                            ],
                            [
                                'label' => 'Present Perfect',
                                'title' => 'Has/Have + Subject + been + V3?',
                                'subtitle' => 'Has the letter been written? — Лист написаний?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Yes/No питання приклади
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Yes/No питання: приклади',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => '<strong>Am/Is/Are + Subject + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the rooms cleaned daily?', 'ua' => 'Кімнати прибираються щодня?'],
                                    ['en' => 'Am I included in the list?', 'ua' => 'Мене включено до списку?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were + Subject + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'Was the report submitted?', 'ua' => 'Звіт був поданий?'],
                                    ['en' => 'Were the letters sent yesterday?', 'ua' => 'Листи були відправлені вчора?'],
                                    ['en' => 'Was the house built in 1990?', 'ua' => 'Будинок був побудований у 1990?'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'amber',
                                'description' => '<strong>Will + Subject + be + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'Will the project be finished by Friday?', 'ua' => 'Проєкт буде завершено до п\'ятниці?'],
                                    ['en' => 'Will we be contacted tomorrow?', 'ua' => 'З нами зв\'яжуться завтра?'],
                                    ['en' => 'Will the meeting be held online?', 'ua' => 'Зустріч відбудеться онлайн?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Wh-питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Wh-питання у пасиві',
                        'sections' => [
                            [
                                'label' => 'What / Which',
                                'color' => 'emerald',
                                'description' => '<strong>What/Which + be + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'What is produced in this factory?', 'ua' => 'Що виробляється на цій фабриці?'],
                                    ['en' => 'What was decided at the meeting?', 'ua' => 'Що було вирішено на зустрічі?'],
                                    ['en' => 'Which option will be chosen?', 'ua' => 'Який варіант буде обрано?'],
                                ],
                            ],
                            [
                                'label' => 'When / Where',
                                'color' => 'blue',
                                'description' => '<strong>When/Where + be + Subject + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'When was the building constructed?', 'ua' => 'Коли була побудована будівля?'],
                                    ['en' => 'Where is coffee grown?', 'ua' => 'Де вирощують каву?'],
                                    ['en' => 'When will the results be announced?', 'ua' => 'Коли будуть оголошені результати?'],
                                ],
                            ],
                            [
                                'label' => 'How / Why',
                                'color' => 'amber',
                                'description' => '<strong>How/Why + be + Subject + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'How is this dish prepared?', 'ua' => 'Як готується ця страва?'],
                                    ['en' => 'Why was the meeting cancelled?', 'ua' => 'Чому зустріч була скасована?'],
                                    ['en' => 'How often is the office cleaned?', 'ua' => 'Як часто прибирається офіс?'],
                                ],
                            ],
                            [
                                'label' => 'By whom',
                                'color' => 'rose',
                                'description' => '<strong>By whom + be + Subject + V3?</strong> (запитуємо про виконавця):',
                                'examples' => [
                                    ['en' => 'By whom was this book written?', 'ua' => 'Ким була написана ця книга?'],
                                    ['en' => 'By whom was the decision made?', 'ua' => 'Ким було прийнято рішення?'],
                                    ['en' => 'By whom will the project be managed?', 'ua' => 'Ким буде керуватися проєкт?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - короткі відповіді
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Короткі відповіді на Yes/No питання',
                        'intro' => 'Структура коротких відповідей у пасиві:',
                        'rows' => [
                            [
                                'en' => 'Is the letter written?',
                                'ua' => 'Лист написаний?',
                                'note' => '— Yes, it is. / No, it isn\'t.',
                            ],
                            [
                                'en' => 'Was the report submitted?',
                                'ua' => 'Звіт був поданий?',
                                'note' => '— Yes, it was. / No, it wasn\'t.',
                            ],
                            [
                                'en' => 'Will the project be finished?',
                                'ua' => 'Проєкт буде завершено?',
                                'note' => '— Yes, it will. / No, it won\'t.',
                            ],
                            [
                                'en' => 'Has the work been done?',
                                'ua' => 'Робота виконана?',
                                'note' => '— Yes, it has. / No, it hasn\'t.',
                            ],
                        ],
                        'warning' => '📌 У коротких відповідях не повторюємо V3!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання do/does у питаннях.',
                                'wrong' => 'Does the letter is written?',
                                'right' => '✅ Is the letter written?',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів у питаннях.',
                                'wrong' => 'The letter is written?',
                                'right' => '✅ Is the letter written?',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Пропуск be у запереченні.',
                                'wrong' => 'The letter not written.',
                                'right' => '✅ The letter is not written.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Повна відповідь замість короткої.',
                                'wrong' => 'Is the letter written? — Yes, it is written.',
                                'right' => '✅ Is the letter written? — Yes, it is.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Заперечення: <strong>Subject + be + not + V3</strong> (скорочено: isn\'t, aren\'t, wasn\'t, weren\'t, won\'t).',
                            'Yes/No питання: <strong>Be + Subject + V3?</strong>',
                            'Wh-питання: <strong>Wh-word + be + Subject + V3?</strong>',
                            'Для запитання про виконавця: <strong>By whom + be + Subject + V3?</strong>',
                            'Короткі відповіді: <strong>Yes, it is/was/will.</strong> / <strong>No, it isn\'t/wasn\'t/won\'t.</strong>',
                            'Не використовуємо <strong>do/does/did</strong> у питаннях пасиву!',
                            'Порядок слів у питаннях: <strong>be виходить на перше місце</strong>.',
                            'У коротких відповідях <strong>не повторюємо V3</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
