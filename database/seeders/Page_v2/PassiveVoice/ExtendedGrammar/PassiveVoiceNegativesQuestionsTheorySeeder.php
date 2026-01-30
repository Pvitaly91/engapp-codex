<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceNegativesQuestionsTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-negatives-questions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive Voice — Заперечення та питання',
            'subtitle_html' => '<p><strong>Negatives & Questions in Passive</strong> — це важлива частина вивчення пасивного стану. Тут ти навчишся будувати заперечні та питальні речення у пасиві, а також давати короткі відповіді (short answers).</p>',
            'subtitle_text' => 'Заперечення та питання у пасивному стані: isn\'t made, Was it built?, короткі відповіді (Yes, it was. / No, it wasn\'t.).',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-extended-grammar',
                'title' => 'Розширення граматики — Пасив у всіх часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Negatives',
                'Questions',
                'Short Answers',
                'A2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти вивчиш, як утворювати <strong>заперечення та питання</strong> у пасивному стані, а також як давати <strong>короткі відповіді</strong> на питання у пасиві.',
                        'rules' => [
                            [
                                'label' => 'Negative',
                                'color' => 'emerald',
                                'text' => 'Заперечення: <strong>be + not + V3</strong>:',
                                'example' => 'The cake isn\'t made here.',
                            ],
                            [
                                'label' => 'Question',
                                'color' => 'blue',
                                'text' => 'Питання: <strong>Be + S + V3?</strong>:',
                                'example' => 'Was the house built in 1990?',
                            ],
                            [
                                'label' => 'Short Answer',
                                'color' => 'rose',
                                'text' => 'Коротка відповідь: <strong>Yes/No + pronoun + be</strong>:',
                                'example' => 'Yes, it was. / No, it wasn\'t.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Заперечення у Present Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + not + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The cake isn\'t made here.', 'ua' => 'Торт тут не печуть.'],
                                    ['en' => 'These cars aren\'t manufactured in Japan.', 'ua' => 'Ці машини не виробляються в Японії.'],
                                    ['en' => 'I\'m not invited to the meeting.', 'ua' => 'Мене не запрошено на зустріч.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'sky',
                                'description' => 'У розмовній мові використовуємо скорочення: <strong>isn\'t, aren\'t</strong>',
                                'examples' => [
                                    ['en' => 'The door isn\'t locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'The windows aren\'t cleaned.', 'ua' => 'Вікна не помиті.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Заперечення у Past Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + not + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The email wasn\'t sent.', 'ua' => 'Електронний лист не було надіслано.'],
                                    ['en' => 'The documents weren\'t signed.', 'ua' => 'Документи не були підписані.'],
                                    ['en' => 'We weren\'t told about the changes.', 'ua' => 'Нас не повідомили про зміни.'],
                                ],
                            ],
                            [
                                'label' => 'Скорочені форми',
                                'color' => 'sky',
                                'description' => 'У розмовній мові: <strong>wasn\'t, weren\'t</strong>',
                                'examples' => [
                                    ['en' => 'The homework wasn\'t done on time.', 'ua' => 'Домашню роботу не зроблено вчасно.'],
                                    ['en' => 'They weren\'t invited to the party.', 'ua' => 'Їх не запросили на вечірку.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Питання у Present Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Yes/No Questions',
                                'color' => 'emerald',
                                'description' => 'Порядок слів: <strong>Am/Is/Are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is the report finished?', 'ua' => 'Звіт готовий?'],
                                    ['en' => 'Are these products made in Ukraine?', 'ua' => 'Ці продукти виробляються в Україні?'],
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-Questions',
                                'color' => 'blue',
                                'description' => 'Порядок слів: <strong>Wh-word + am/is/are + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Where is this wine made?', 'ua' => 'Де виробляється це вино?'],
                                    ['en' => 'How often are the rooms cleaned?', 'ua' => 'Як часто прибираються кімнати?'],
                                    ['en' => 'Why isn\'t the door locked?', 'ua' => 'Чому двері не замкнені?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Питання у Past Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Yes/No Questions',
                                'color' => 'emerald',
                                'description' => 'Порядок слів: <strong>Was/Were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the house built in 1990?', 'ua' => 'Будинок було збудовано в 1990 році?'],
                                    ['en' => 'Were the letters sent yesterday?', 'ua' => 'Листи були надіслані вчора?'],
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-Questions',
                                'color' => 'blue',
                                'description' => 'Порядок слів: <strong>Wh-word + was/were + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'When was the Eiffel Tower built?', 'ua' => 'Коли була побудована Ейфелева вежа?'],
                                    ['en' => 'Where were these photos taken?', 'ua' => 'Де були зроблені ці фотографії?'],
                                    ['en' => 'How was the problem solved?', 'ua' => 'Як було вирішено проблему?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Короткі відповіді (Short Answers)',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>Yes/No, + pronoun + am/is/are (not)</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here? — Yes, it is.', 'ua' => 'Тут розмовляють англійською? — Так.'],
                                    ['en' => 'Are the rooms cleaned daily? — No, they aren\'t.', 'ua' => 'Кімнати прибираються щодня? — Ні.'],
                                    ['en' => 'Is the door locked? — Yes, it is.', 'ua' => 'Двері замкнені? — Так.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => 'Формула: <strong>Yes/No, + pronoun + was/were (not)</strong>',
                                'examples' => [
                                    ['en' => 'Was the car repaired? — Yes, it was.', 'ua' => 'Машину відремонтували? — Так.'],
                                    ['en' => 'Were the documents signed? — No, they weren\'t.', 'ua' => 'Документи були підписані? — Ні.'],
                                    ['en' => 'Was the email sent? — No, it wasn\'t.', 'ua' => 'Електронний лист було надіслано? — Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Зведена таблиця',
                        'intro' => 'Структури заперечень та питань у пасиві:',
                        'rows' => [
                            [
                                'en' => 'Present Simple (−)',
                                'ua' => 'am/is/are + not + V3',
                                'note' => 'It isn\'t made here.',
                            ],
                            [
                                'en' => 'Present Simple (?)',
                                'ua' => 'Am/Is/Are + S + V3?',
                                'note' => 'Is it made here?',
                            ],
                            [
                                'en' => 'Past Simple (−)',
                                'ua' => 'was/were + not + V3',
                                'note' => 'It wasn\'t built.',
                            ],
                            [
                                'en' => 'Past Simple (?)',
                                'ua' => 'Was/Were + S + V3?',
                                'note' => 'Was it built?',
                            ],
                            [
                                'en' => 'Short Answer (+)',
                                'ua' => 'Yes, + pronoun + be',
                                'note' => 'Yes, it was.',
                            ],
                            [
                                'en' => 'Short Answer (−)',
                                'ua' => 'No, + pronoun + be + not',
                                'note' => 'No, it wasn\'t.',
                            ],
                        ],
                        'warning' => '📌 У коротких відповідях НЕ повторюємо V3!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
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
                                'title' => 'Неправильний порядок слів у питанні.',
                                'wrong' => 'The letter was sent?',
                                'right' => '✅ Was the letter sent?',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'V3 у короткій відповіді.',
                                'wrong' => 'Yes, it was sent.',
                                'right' => '✅ Yes, it was.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неузгодженість be з підметом.',
                                'wrong' => 'Were the letter sent?',
                                'right' => '✅ Was the letter sent?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Заперечення: <strong>be + not + V3</strong> (isn\'t made, wasn\'t built).',
                            'Питання: <strong>Be + Subject + V3?</strong> (Is it made? Was it built?).',
                            'Wh-питання: <strong>Wh + be + S + V3?</strong> (Where is it made?).',
                            'Коротка відповідь (+): <strong>Yes, + pronoun + be</strong> (Yes, it is.).',
                            'Коротка відповідь (−): <strong>No, + pronoun + be + not</strong> (No, it wasn\'t.).',
                            'У коротких відповідях <strong>НЕ повторюємо V3</strong>!',
                            'Дієслово be має узгоджуватися з підметом за числом.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з розширеної граматики пасиву',
                        'items' => [
                            [
                                'label' => 'Заперечення та питання (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Passive з модальними',
                                'current' => false,
                            ],
                            [
                                'label' => 'Passive в основних часах',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
