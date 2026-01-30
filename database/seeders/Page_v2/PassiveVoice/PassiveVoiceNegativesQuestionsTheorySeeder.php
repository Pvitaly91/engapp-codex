<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\PassiveVoice\PassiveVoicePageSeeder;

class PassiveVoiceNegativesQuestionsTheorySeeder extends PassiveVoicePageSeeder
{
    protected function slug(): string
    {
        return 'passive-negatives-questions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Negatives & Questions in Passive — Питання та заперечення в пасиві',
            'subtitle_html' => '<p><strong>Питання та заперечення в пасиві</strong> утворюються так само, як і в активному стані — через інверсію дієслова to be та додавання not. Тут ти вивчиш, як правильно ставити питання та заперечення в пасивному стані, а також давати короткі відповіді.</p>',
            'subtitle_text' => 'Теоретичний огляд питань та заперечень у пасивному стані: структура, приклади, короткі відповіді.',
            'subtitle_level' => 'A2',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pasyvnyi-stan',
                'title' => 'Пасивний стан (Passive Voice)',
                'language' => 'uk',
            ],
            // Page anchor tags
            'tags' => [
                'Passive Voice',
                'Negatives in Passive',
                'Questions in Passive',
                'Short Answers',
                'Grammar',
                'Theory',
            ],
            // Base tags inherited by all blocks
            'base_tags' => [
                'Passive Voice',
                'Negatives in Passive',
                'Questions in Passive',
                'Short Answers',
            ],
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'hero',
                    'tags' => ['Introduction', 'Overview', 'To Be', 'Inversion', 'CEFR A2', 'CEFR B1'],
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти вивчиш, як утворювати <strong>питання та заперечення</strong> в пасивному стані, а також як давати <strong>короткі відповіді</strong>.',
                        'rules' => [
                            [
                                'label' => 'ПИТАННЯ',
                                'color' => 'emerald',
                                'text' => '<strong>Інверсія to be</strong> — дієслово перед підметом:',
                                'example' => 'Is it made? Was it built? Has it been done?',
                            ],
                            [
                                'label' => 'ЗАПЕРЕЧЕННЯ',
                                'color' => 'blue',
                                'text' => '<strong>To be + not</strong> — додаємо not після to be:',
                                'example' => 'It isn\'t made. It wasn\'t built. It hasn\'t been done.',
                            ],
                            [
                                'label' => 'КОРОТКІ ВІДПОВІДІ',
                                'color' => 'amber',
                                'text' => '<strong>Yes/No + підмет + to be</strong>:',
                                'example' => 'Yes, it is. / No, it wasn\'t. / Yes, it has.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'forms-grid-what-is',
                    'tags' => ['Definition', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '1. Питання в пасиві — базова структура',
                        'intro' => 'Для утворення питання в пасиві робимо інверсію — ставимо дієслово to be перед підметом:',
                        'items' => [
                            ['label' => 'Ствердження', 'title' => 'It is made in China.', 'subtitle' => 'Це зроблено в Китаї.'],
                            ['label' => 'Питання', 'title' => 'Is it made in China?', 'subtitle' => 'Це зроблено в Китаї?'],
                            ['label' => 'Формула', 'title' => 'To be + Subject + V3 + ...?', 'subtitle' => 'Форма to be + Підмет + Дієприкметник + ...?'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-yes-no-questions',
                    'tags' => ['Yes/No Questions', 'Present Simple Passive', 'Past Simple Passive', 'Inversion', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '2. Yes/No питання в пасиві',
                        'sections' => [
                            [
                                'label' => 'Present Simple Passive',
                                'color' => 'emerald',
                                'description' => 'У <strong>Present Simple Passive</strong> використовуємо <strong>is/are</strong> перед підметом.',
                                'examples' => [
                                    ['en' => 'Is it made in China?', 'ua' => 'Це зроблено в Китаї?'],
                                    ['en' => 'Are these cars produced locally?', 'ua' => 'Ці машини виробляються місцево?'],
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут говорять англійською?'],
                                    ['en' => 'Are the doors locked at night?', 'ua' => 'Двері замикаються на ніч?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'color' => 'sky',
                                'description' => 'У <strong>Past Simple Passive</strong> використовуємо <strong>was/were</strong> перед підметом.',
                                'examples' => [
                                    ['en' => 'Was it built in 1990?', 'ua' => 'Це було збудовано в 1990?'],
                                    ['en' => 'Were they invited to the party?', 'ua' => 'Їх запросили на вечірку?'],
                                    ['en' => 'Was the letter sent yesterday?', 'ua' => 'Лист був відправлений вчора?'],
                                    ['en' => 'Were the windows broken?', 'ua' => 'Вікна були розбиті?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'purple',
                                'description' => 'Формула Yes/No питання в пасиві:',
                                'examples' => [
                                    ['en' => 'Is/Are/Was/Were + Subject + Past Participle (V3) + ...?', 'ua' => 'Форма to be + Підмет + V3 + ...?'],
                                ],
                                'note' => '📌 Головне — перенести форму to be на початок речення!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-negatives',
                    'tags' => ['Negatives', 'Present Simple Passive', 'Past Simple Passive', 'Not', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '3. Заперечення в пасиві',
                        'sections' => [
                            [
                                'label' => 'Present Simple Passive',
                                'color' => 'blue',
                                'description' => 'У <strong>Present Simple Passive</strong> додаємо <strong>not</strong> після is/are.',
                                'examples' => [
                                    ['en' => 'It isn\'t made in China.', 'ua' => 'Це не зроблено в Китаї.'],
                                    ['en' => 'These cars aren\'t produced locally.', 'ua' => 'Ці машини не виробляються місцево.'],
                                    ['en' => 'English isn\'t spoken here.', 'ua' => 'Тут не говорять англійською.'],
                                    ['en' => 'The doors aren\'t locked.', 'ua' => 'Двері не замикаються.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'color' => 'amber',
                                'description' => 'У <strong>Past Simple Passive</strong> додаємо <strong>not</strong> після was/were.',
                                'examples' => [
                                    ['en' => 'It wasn\'t built in 1990.', 'ua' => 'Це не було збудовано в 1990.'],
                                    ['en' => 'They weren\'t invited.', 'ua' => 'Їх не запросили.'],
                                    ['en' => 'The letter wasn\'t sent.', 'ua' => 'Лист не був відправлений.'],
                                    ['en' => 'The windows weren\'t broken.', 'ua' => 'Вікна не були розбиті.'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Формула заперечення в пасиві:',
                                'examples' => [
                                    ['en' => 'Subject + is/are/was/were + not + Past Participle (V3) + ...', 'ua' => 'Підмет + форма to be + not + V3 + ...'],
                                ],
                                'note' => '📌 Скорочені форми: isn\'t, aren\'t, wasn\'t, weren\'t',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'usage-panels-short-answers',
                    'tags' => ['Short Answers', 'Yes/No Questions', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '4. Короткі відповіді',
                        'sections' => [
                            [
                                'label' => 'Позитивні відповіді',
                                'color' => 'emerald',
                                'description' => 'Використовуємо <strong>Yes, + підмет + форма to be</strong>.',
                                'examples' => [
                                    ['en' => 'Is it made in China? — Yes, it is.', 'ua' => 'Це зроблено в Китаї? — Так.'],
                                    ['en' => 'Was it built in 1990? — Yes, it was.', 'ua' => 'Це було збудовано в 1990? — Так.'],
                                    ['en' => 'Are they invited? — Yes, they are.', 'ua' => 'Їх запросили? — Так.'],
                                    ['en' => 'Were the doors locked? — Yes, they were.', 'ua' => 'Двері були замкнені? — Так.'],
                                ],
                            ],
                            [
                                'label' => 'Негативні відповіді',
                                'color' => 'rose',
                                'description' => 'Використовуємо <strong>No, + підмет + форма to be + not</strong>.',
                                'examples' => [
                                    ['en' => 'Is it made in China? — No, it isn\'t.', 'ua' => 'Це зроблено в Китаї? — Ні.'],
                                    ['en' => 'Was it built in 1990? — No, it wasn\'t.', 'ua' => 'Це було збудовано в 1990? — Ні.'],
                                    ['en' => 'Are they invited? — No, they aren\'t.', 'ua' => 'Їх запросили? — Ні.'],
                                    ['en' => 'Were the doors locked? — No, they weren\'t.', 'ua' => 'Двері були замкнені? — Ні.'],
                                ],
                            ],
                            [
                                'label' => 'Правило',
                                'color' => 'purple',
                                'description' => 'У коротких відповідях НЕ повторюємо V3:',
                                'examples' => [
                                    ['en' => '✓ Yes, it is. / ✗ Yes, it is made.', 'ua' => 'Так, це так. (без V3)'],
                                    ['en' => '✓ No, it wasn\'t. / ✗ No, it wasn\'t built.', 'ua' => 'Ні, не так. (без V3)'],
                                ],
                                'note' => '📌 Короткі відповіді складаються тільки з Yes/No + підмет + to be!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-wh-questions',
                    'tags' => ['Wh-Questions', 'Question Words', 'Where', 'When', 'Why', 'How', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '5. Wh-питання в пасиві',
                        'sections' => [
                            [
                                'label' => 'Where / When',
                                'color' => 'emerald',
                                'description' => 'Питання про місце та час:',
                                'examples' => [
                                    ['en' => 'Where is it made?', 'ua' => 'Де це зроблено?'],
                                    ['en' => 'Where was the car found?', 'ua' => 'Де знайшли машину?'],
                                    ['en' => 'When was it built?', 'ua' => 'Коли це було збудовано?'],
                                    ['en' => 'When is the meeting held?', 'ua' => 'Коли проводиться зустріч?'],
                                ],
                            ],
                            [
                                'label' => 'How / Why',
                                'color' => 'sky',
                                'description' => 'Питання про спосіб та причину:',
                                'examples' => [
                                    ['en' => 'How is it done?', 'ua' => 'Як це робиться?'],
                                    ['en' => 'How was the problem solved?', 'ua' => 'Як була вирішена проблема?'],
                                    ['en' => 'Why is it called that?', 'ua' => 'Чому це так називається?'],
                                    ['en' => 'Why was he fired?', 'ua' => 'Чому його звільнили?'],
                                ],
                            ],
                            [
                                'label' => 'By whom',
                                'color' => 'purple',
                                'description' => 'Питання про виконавця дії:',
                                'examples' => [
                                    ['en' => 'By whom was it written?', 'ua' => 'Ким це було написано?'],
                                    ['en' => 'Who was it made by?', 'ua' => 'Ким це зроблено?'],
                                ],
                                'note' => '📌 "By whom" — формальний стиль, "Who...by?" — розмовний',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'comparison-table',
                    'tags' => ['Summary', 'Comparison', 'All Structures', 'CEFR A2'],
                    'body' => json_encode([
                        'title' => '6. Порівняльна таблиця',
                        'intro' => 'Питання, заперечення та короткі відповіді в пасиві:',
                        'rows' => [
                            [
                                'en' => 'Yes/No Question',
                                'ua' => 'Загальне питання',
                                'note' => 'Is it made? Was it built?',
                            ],
                            [
                                'en' => 'Wh-Question',
                                'ua' => 'Спеціальне питання',
                                'note' => 'Where is it made? When was it built?',
                            ],
                            [
                                'en' => 'Negative',
                                'ua' => 'Заперечення',
                                'note' => 'It isn\'t made. It wasn\'t built.',
                            ],
                            [
                                'en' => 'Short Answer (Yes)',
                                'ua' => 'Коротка відповідь (Так)',
                                'note' => 'Yes, it is. Yes, it was.',
                            ],
                            [
                                'en' => 'Short Answer (No)',
                                'ua' => 'Коротка відповідь (Ні)',
                                'note' => 'No, it isn\'t. No, it wasn\'t.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'box',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'common-mistakes',
                    'tags' => ['Common Mistakes', 'Tips', 'CEFR B1'],
                    'body' => <<<'HTML'
<div class="gw-box">
<h4>⚠️ Типові помилки</h4>
<ul class="gw-list">
<li>❌ <span class="gw-en">Does it made?</span> → ✅ <span class="gw-en">Is it made?</span> (не do/does!)</li>
<li>❌ <span class="gw-en">Did it built?</span> → ✅ <span class="gw-en">Was it built?</span> (не did!)</li>
<li>❌ <span class="gw-en">It doesn't made.</span> → ✅ <span class="gw-en">It isn't made.</span></li>
<li>❌ <span class="gw-en">Yes, it is made.</span> → ✅ <span class="gw-en">Yes, it is.</span> (без V3)</li>
</ul>
<p><strong>Запам'ятай:</strong> У пасиві питання та заперечення утворюються тільки через to be!</p>
</div>
HTML,
                ],
                [
                    'type' => 'box',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'A2',
                    'uuid_key' => 'tips',
                    'tags' => ['Tips', 'Learning'],
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Для <strong>питань</strong> — перенеси to be на початок: <span class="gw-en">Is/Was + Subject + V3?</span></p>
<p>Для <strong>заперечень</strong> — додай not: <span class="gw-en">Subject + is/was + not + V3</span></p>
<p>Для <strong>коротких відповідей</strong> — тільки to be: <span class="gw-en">Yes, it is. No, it wasn't.</span></p>
<p>Ніколи не використовуй <strong>do/does/did</strong> у пасиві для питань та заперечень!</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
