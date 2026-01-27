<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceExtendedGrammarNegativesQuestionsTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
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
            'title' => 'Заперечення та питання у пасиві — Negatives & Questions in Passive',
            'subtitle_html' => '<p><strong>Negatives & Questions in Passive</strong> — навчись правильно будувати заперечні та питальні речення у пасивному стані, а також давати короткі відповіді на питання.</p>',
            'subtitle_text' => 'Заперечення (isn\'t made, wasn\'t built) та питання (Was it built?) у пасивному стані з короткими відповідями.',
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
                'Заперечення',
                'Питання',
                'B1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти навчишся будувати <strong>заперечні та питальні речення</strong> у пасивному стані, а також давати <strong>короткі відповіді</strong> на yes/no питання.',
                        'rules' => [
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => 'Додаємо <strong>not</strong> після be:',
                                'example' => 'The house isn\'t made of wood.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>be</strong> виходить на перше місце:',
                                'example' => 'Was it built in 1990?',
                            ],
                            [
                                'label' => 'Короткі відповіді',
                                'color' => 'emerald',
                                'text' => '<strong>Yes/No + subject + be</strong>:',
                                'example' => 'Yes, it was. / No, it wasn\'t.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Заперечення в Present Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Формула: <strong>am/is/are + not + V3</strong>. Скорочені форми: <strong>isn\'t, aren\'t</strong>',
                                'examples' => [
                                    ['en' => 'The products are not made in China.', 'ua' => 'Продукти не виробляються в Китаї.'],
                                    ['en' => 'This material isn\'t used anymore.', 'ua' => 'Цей матеріал більше не використовується.'],
                                    ['en' => 'I am not invited to the party.', 'ua' => 'Мене не запросили на вечірку.'],
                                    ['en' => 'The doors aren\'t locked at night.', 'ua' => 'Двері не замикаються на ніч.'],
                                ],
                            ],
                            [
                                'label' => 'Нюанси',
                                'color' => 'amber',
                                'description' => 'Форма <strong>am not</strong> не скорочується до "amn\'t". Використовуємо: <strong>I\'m not</strong>',
                                'examples' => [
                                    ['en' => 'I\'m not included in the list.', 'ua' => 'Мене не включено до списку.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Заперечення в Past Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Формула: <strong>was/were + not + V3</strong>. Скорочені: <strong>wasn\'t, weren\'t</strong>',
                                'examples' => [
                                    ['en' => 'The house was not built in 1990.', 'ua' => 'Будинок не був побудований у 1990.'],
                                    ['en' => 'The letter wasn\'t sent yesterday.', 'ua' => 'Лист не був надісланий вчора.'],
                                    ['en' => 'We were not informed about the changes.', 'ua' => 'Нас не повідомили про зміни.'],
                                    ['en' => 'The tickets weren\'t sold out.', 'ua' => 'Квитки не були розпродані.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Заперечення в інших часах',
                        'sections' => [
                            [
                                'label' => 'Future Simple',
                                'color' => 'rose',
                                'description' => '<strong>will not (won\'t) + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершено вчасно.'],
                                    ['en' => 'The meeting won\'t be held tomorrow.', 'ua' => 'Зустріч не відбудеться завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'rose',
                                'description' => '<strong>has/have + not + been + V3</strong>. Скорочені: <strong>hasn\'t, haven\'t</strong>',
                                'examples' => [
                                    ['en' => 'The work has not been completed yet.', 'ua' => 'Робота ще не завершена.'],
                                    ['en' => 'The files haven\'t been uploaded.', 'ua' => 'Файли не були завантажені.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Yes/No питання в пасиві',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'blue',
                                'description' => '<strong>Am/Is/Are + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the rooms cleaned daily?', 'ua' => 'Кімнати прибираються щодня?'],
                                    ['en' => 'Am I included in the team?', 'ua' => 'Мене включено до команди?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the house built in 1990?', 'ua' => 'Будинок був побудований у 1990?'],
                                    ['en' => 'Were the documents signed?', 'ua' => 'Документи були підписані?'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'blue',
                                'description' => '<strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Will the project be finished on time?', 'ua' => 'Проєкт буде завершено вчасно?'],
                                    ['en' => 'Will you be invited to the meeting?', 'ua' => 'Тебе запросять на зустріч?'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'blue',
                                'description' => '<strong>Has/Have + S + been + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Has the work been completed?', 'ua' => 'Роботу завершено?'],
                                    ['en' => 'Have the tickets been sold?', 'ua' => 'Квитки продано?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Короткі відповіді (Short Answers)',
                        'sections' => [
                            [
                                'label' => 'Правило',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>Yes/No + subject pronoun + be (у тому ж часі)</strong>',
                                'examples' => [
                                    ['en' => 'Is the door locked? — Yes, it is. / No, it isn\'t.', 'ua' => 'Двері замкнені? — Так. / Ні.'],
                                    ['en' => 'Was the letter sent? — Yes, it was. / No, it wasn\'t.', 'ua' => 'Лист надіслано? — Так. / Ні.'],
                                    ['en' => 'Are they invited? — Yes, they are. / No, they aren\'t.', 'ua' => 'Їх запросили? — Так. / Ні.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'emerald',
                                'description' => 'У Present Perfect: <strong>Yes/No + S + has/have</strong>',
                                'examples' => [
                                    ['en' => 'Has the work been finished? — Yes, it has. / No, it hasn\'t.', 'ua' => 'Роботу закінчено? — Так. / Ні.'],
                                    ['en' => 'Have they been informed? — Yes, they have. / No, they haven\'t.', 'ua' => 'Їх повідомили? — Так. / Ні.'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'emerald',
                                'description' => 'У Future: <strong>Yes/No + S + will/won\'t</strong>',
                                'examples' => [
                                    ['en' => 'Will it be done tomorrow? — Yes, it will. / No, it won\'t.', 'ua' => 'Це буде зроблено завтра? — Так. / Ні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Wh-питання в пасиві',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + be + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Where is coffee grown?', 'ua' => 'Де вирощують каву?'],
                                    ['en' => 'When was the house built?', 'ua' => 'Коли був побудований будинок?'],
                                    ['en' => 'How are these products made?', 'ua' => 'Як виробляються ці продукти?'],
                                    ['en' => 'Why was the meeting cancelled?', 'ua' => 'Чому зустріч скасували?'],
                                ],
                            ],
                            [
                                'label' => 'Who/What як підмет',
                                'color' => 'sky',
                                'description' => 'Коли питальне слово — підмет: <strong>Who/What + be + V3?</strong> (без інверсії)',
                                'examples' => [
                                    ['en' => 'What is produced here?', 'ua' => 'Що тут виробляється?'],
                                    ['en' => 'Who was invited to the party?', 'ua' => 'Кого запросили на вечірку?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Зведена таблиця питань та заперечень',
                        'intro' => 'Основні структури заперечень та питань у різних часах:',
                        'rows' => [
                            [
                                'en' => 'Present Simple',
                                'ua' => 'It isn\'t made here.',
                                'note' => 'Is it made here? — Yes, it is. / No, it isn\'t.',
                            ],
                            [
                                'en' => 'Past Simple',
                                'ua' => 'It wasn\'t built in 1990.',
                                'note' => 'Was it built in 1990? — Yes, it was. / No, it wasn\'t.',
                            ],
                            [
                                'en' => 'Future Simple',
                                'ua' => 'It won\'t be finished.',
                                'note' => 'Will it be finished? — Yes, it will. / No, it won\'t.',
                            ],
                            [
                                'en' => 'Present Perfect',
                                'ua' => 'It hasn\'t been done.',
                                'note' => 'Has it been done? — Yes, it has. / No, it hasn\'t.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '8. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів у питанні.',
                                'wrong' => 'The house was built in 1990?',
                                'right' => '✅ Was the house built in 1990?',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск допоміжного дієслова в питанні.',
                                'wrong' => 'It made in China?',
                                'right' => '✅ Is it made in China?',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неповна коротка відповідь.',
                                'wrong' => 'Was it built? — Yes, it.',
                                'right' => '✅ Yes, it was.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            'Заперечення: <strong>be + not + V3</strong> (isn\'t, wasn\'t, won\'t be, hasn\'t been).',
                            'Yes/No питання: <strong>be</strong> виходить на перше місце перед підметом.',
                            'Короткі відповіді: <strong>Yes/No + subject + be</strong> (у тому ж часі).',
                            'Wh-питання: <strong>Wh-word + be + S + V3?</strong>',
                            'Коли Who/What — підмет, інверсія не потрібна: <strong>What is produced?</strong>',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
