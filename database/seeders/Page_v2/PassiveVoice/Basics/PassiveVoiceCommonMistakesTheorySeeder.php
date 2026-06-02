<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceCommonMistakesTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-common-mistakes';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Приклади вживання та типові помилки',
            'subtitle_html' => '<p><strong>Приклади вживання та типові помилки</strong> — повний огляд найпоширеніших помилок при вживанні пасивного стану та практичні поради, як їх уникати.</p>',
            'subtitle_text' => 'Типові помилки у пасиві: неправильний вибір часу, пропуск be, плутання V2/V3, зайвий пасив та практичні приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Common Mistakes',
                'Типові помилки',
                'Examples',
                'A2',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2–B2',
                        'intro' => 'У цій темі ми зібрали <strong>найпоширеніші помилки</strong> при вживанні пасивного стану та практичні поради, як їх уникати. Вивчення помилок — найкращий спосіб їх не повторювати!',
                        'rules' => [
                            [
                                'label' => 'Be + V3',
                                'color' => 'emerald',
                                'text' => 'Не забувай <strong>дієслово be</strong> та <strong>V3 (Past Participle)</strong>:',
                                'example' => 'The letter is written. ✅ (не: The letter written. ❌)',
                            ],
                            [
                                'label' => 'V3 ≠ V2',
                                'color' => 'rose',
                                'text' => 'Використовуй <strong>V3</strong>, не V2:',
                                'example' => 'The window was broken. ✅ (не: was broke. ❌)',
                            ],
                            [
                                'label' => 'Без do/does',
                                'color' => 'blue',
                                'text' => 'У питаннях <strong>не використовуй do/does/did</strong>:',
                                'example' => 'Is the letter written? ✅ (не: Does the letter is written? ❌)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - основні помилки з be
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Помилки з дієсловом BE',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => 'The letter written yesterday.',
                                'right' => '✅ The letter was written yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна форма be (is/are).',
                                'wrong' => 'The letters is sent every day.',
                                'right' => '✅ The letters are sent every day.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма be (was/were).',
                                'wrong' => 'The windows was broken.',
                                'right' => '✅ The windows were broken.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Пропуск been у Perfect.',
                                'wrong' => 'The work has done.',
                                'right' => '✅ The work has been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - помилки з V3
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Помилки з Past Participle (V3)',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання V1 (infinitive) замість V3.',
                                'wrong' => 'English is speak here.',
                                'right' => '✅ English is spoken here.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V2 (past simple) замість V3.',
                                'wrong' => 'The window was broke.',
                                'right' => '✅ The window was broken.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутання неправильних дієслів.',
                                'wrong' => 'The book was writed by him.',
                                'right' => '✅ The book was written by him.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Використання -ing замість V3.',
                                'wrong' => 'The letter is writing now.',
                                'right' => '✅ The letter is being written now.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - помилки в питаннях
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Помилки в питаннях',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання do/does/did у питаннях.',
                                'wrong' => 'Does the letter is written?',
                                'right' => '✅ Is the letter written?',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'The letter is written?',
                                'right' => '✅ Is the letter written?',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Помилка у wh-питаннях.',
                                'wrong' => 'Where the book was found?',
                                'right' => '✅ Where was the book found?',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильна коротка відповідь.',
                                'wrong' => 'Was the work done? — Yes, it done.',
                                'right' => '✅ Was the work done? — Yes, it was.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - помилки в запереченнях
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Помилки в запереченнях',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск not або неправильне розташування.',
                                'wrong' => 'The letter is not write.',
                                'right' => '✅ The letter is not written.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання don\'t/doesn\'t.',
                                'wrong' => 'The work doesn\'t finished.',
                                'right' => '✅ The work isn\'t finished.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма not.',
                                'wrong' => 'The letter is no written.',
                                'right' => '✅ The letter is not written.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Помилка в Perfect.',
                                'wrong' => 'The work hasn\'t done.',
                                'right' => '✅ The work hasn\'t been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - by vs with
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Помилки з by та with',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання with для виконавця.',
                                'wrong' => 'The book was written with Shakespeare.',
                                'right' => '✅ The book was written by Shakespeare.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання by для інструменту.',
                                'wrong' => 'The letter was written by a pen.',
                                'right' => '✅ The letter was written with a pen.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Зайвий by + agent.',
                                'wrong' => 'My car was stolen by someone.',
                                'right' => '✅ My car was stolen.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильне розташування by.',
                                'wrong' => 'By the team the report was prepared.',
                                'right' => '✅ The report was prepared by the team.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - практичні приклади правильного вживання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Правильні приклади вживання',
                        'sections' => [
                            [
                                'label' => 'Present Simple Passive',
                                'color' => 'emerald',
                                'description' => '<strong>am/is/are + V3</strong>:',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійською розмовляють у багатьох країнах.'],
                                    ['en' => 'The office is cleaned every day.', 'ua' => 'Офіс прибирається щодня.'],
                                    ['en' => 'Cars are made in Germany.', 'ua' => 'Машини виробляються в Німеччині.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'color' => 'blue',
                                'description' => '<strong>was/were + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The letter was sent yesterday.', 'ua' => 'Лист надіслали вчора.'],
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The windows were broken during the storm.', 'ua' => 'Вікна розбилися під час бурі.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect Passive',
                                'color' => 'amber',
                                'description' => '<strong>has/have been + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The work has been completed.', 'ua' => 'Робота завершена.'],
                                    ['en' => 'All tickets have been sold.', 'ua' => 'Усі квитки продані.'],
                                    ['en' => 'The report has been submitted.', 'ua' => 'Звіт подано.'],
                                ],
                            ],
                            [
                                'label' => 'Modal Passive',
                                'color' => 'rose',
                                'description' => '<strong>modal + be + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The task must be done today.', 'ua' => 'Завдання має бути виконане сьогодні.'],
                                    ['en' => 'This problem can be solved.', 'ua' => 'Цю проблему можна вирішити.'],
                                    ['en' => 'The report should be checked carefully.', 'ua' => 'Звіт слід ретельно перевірити.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Active vs Passive перетворення
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '7. Правильне перетворення Active → Passive',
                        'intro' => 'Поетапне перетворення активних речень на пасивні:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom writes letters.',
                                'ua' => 'Том пише листи.',
                                'note' => '→ Passive: Letters are written by Tom.',
                            ],
                            [
                                'en' => 'Active: She cleaned the room.',
                                'ua' => 'Вона прибрала кімнату.',
                                'note' => '→ Passive: The room was cleaned.',
                            ],
                            [
                                'en' => 'Active: They have finished the project.',
                                'ua' => 'Вони завершили проєкт.',
                                'note' => '→ Passive: The project has been finished.',
                            ],
                            [
                                'en' => 'Active: Someone must do this work.',
                                'ua' => 'Хтось має зробити цю роботу.',
                                'note' => '→ Passive: This work must be done.',
                            ],
                        ],
                        'warning' => '📌 Object активного речення → Subject пасивного. Дієслово → be + V3.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - чек-лист перевірки
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '8. Чек-лист перевірки пасиву',
                        'intro' => 'Перевір своє речення за цими пунктами:',
                        'items' => [
                            [
                                'label' => '✓ 1',
                                'title' => 'Чи є дієслово BE?',
                                'subtitle' => 'am/is/are/was/were/been/being',
                            ],
                            [
                                'label' => '✓ 2',
                                'title' => 'Чи правильна форма BE за часом?',
                                'subtitle' => 'Present: is/are; Past: was/were',
                            ],
                            [
                                'label' => '✓ 3',
                                'title' => 'Чи використано V3 (Past Participle)?',
                                'subtitle' => 'written, broken, done, made',
                            ],
                            [
                                'label' => '✓ 4',
                                'title' => 'Чи правильна форма V3?',
                                'subtitle' => 'broken (не broke), written (не writed)',
                            ],
                            [
                                'label' => '✓ 5',
                                'title' => 'Чи немає do/does/did у питаннях?',
                                'subtitle' => 'Is it done? (не: Does it is done?)',
                            ],
                            [
                                'label' => '✓ 6',
                                'title' => 'Чи правильно by/with?',
                                'subtitle' => 'by + agent; with + instrument',
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
                        'title' => '9. Головні правила — уникай помилок!',
                        'items' => [
                            'Завжди використовуй <strong>be + V3</strong> у пасиві.',
                            '<strong>Не плутай V2 і V3</strong>: broken (не broke), written (не wrote).',
                            '<strong>Не використовуй do/does/did</strong> у питаннях пасиву.',
                            '<strong>by + agent</strong> (хто?); <strong>with + instrument</strong> (чим?).',
                            'У Perfect: <strong>has/have been + V3</strong> (не: has done).',
                            'У Continuous: <strong>am/is/are being + V3</strong> (не: is writing).',
                            'Перевіряй <strong>узгодження be з підметом</strong>: is/are, was/were.',
                            '<strong>Не зловживай пасивом</strong> — актив часто звучить природніше.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
