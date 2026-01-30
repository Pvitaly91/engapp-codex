<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceWithModalsTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-with-modals';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive Voice — Пасив з модальними дієсловами',
            'subtitle_html' => '<p><strong>Passive with Modals</strong> — це конструкція, де пасивний стан поєднується з модальними дієсловами: can, could, may, might, must, should, have to. Формула: <strong>modal + be + V3</strong>. Наприклад: "It must be done."</p>',
            'subtitle_text' => 'Пасивний стан з модальними дієсловами: can/must/should + be + V3. Формула та приклади використання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-extended-grammar',
                'title' => 'Розширення граматики — Пасив у всіх часах',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Modal Verbs',
                'Модальні дієслова',
                'can',
                'must',
                'should',
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
                        'intro' => 'У цій темі ти вивчиш <strong>пасивний стан з модальними дієсловами</strong>: can, could, may, might, must, should, have to. Формула: <strong>modal + be + V3</strong>.',
                        'rules' => [
                            [
                                'label' => 'Formula',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>modal + be + V3</strong>:',
                                'example' => 'It must be done today.',
                            ],
                            [
                                'label' => 'Can/Could',
                                'color' => 'blue',
                                'text' => 'Можливість: <strong>can/could + be + V3</strong>:',
                                'example' => 'This problem can be solved.',
                            ],
                            [
                                'label' => 'Must/Should',
                                'color' => 'rose',
                                'text' => 'Обов\'язок: <strong>must/should + be + V3</strong>:',
                                'example' => 'The rules should be followed.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Формула Modal Passive',
                        'intro' => 'Пасив з модальними утворюється за формулою:',
                        'items' => [
                            [
                                'label' => 'Modal',
                                'title' => 'Модальне дієслово',
                                'subtitle' => 'can, could, may, might, must, should, have to, ought to',
                            ],
                            [
                                'label' => '+',
                                'title' => 'Плюс',
                                'subtitle' => '',
                            ],
                            [
                                'label' => 'be',
                                'title' => 'Інфінітив be',
                                'subtitle' => 'Завжди у формі be (не am/is/are)',
                            ],
                            [
                                'label' => '+',
                                'title' => 'Плюс',
                                'subtitle' => '',
                            ],
                            [
                                'label' => 'V3',
                                'title' => 'Past Participle',
                                'subtitle' => 'Третя форма дієслова: done, made, written...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Can / Could + be + V3',
                        'sections' => [
                            [
                                'label' => 'Can (можливість)',
                                'color' => 'emerald',
                                'description' => '<strong>Can + be + V3</strong> — щось можливо зробити (теперішній час).',
                                'examples' => [
                                    ['en' => 'This problem can be solved.', 'ua' => 'Ця проблема може бути вирішена.'],
                                    ['en' => 'The document can be downloaded.', 'ua' => 'Документ можна завантажити.'],
                                    ['en' => 'The tickets can be bought online.', 'ua' => 'Квитки можна придбати онлайн.'],
                                ],
                            ],
                            [
                                'label' => 'Could (можливість у минулому / ввічливість)',
                                'color' => 'sky',
                                'description' => '<strong>Could + be + V3</strong> — можливість у минулому або ввічлива форма.',
                                'examples' => [
                                    ['en' => 'The work could be finished earlier.', 'ua' => 'Роботу можна було б закінчити раніше.'],
                                    ['en' => 'This mistake could be avoided.', 'ua' => 'Цієї помилки можна було б уникнути.'],
                                    ['en' => 'Could the meeting be rescheduled?', 'ua' => 'Чи можна перенести зустріч?'],
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
                        'title' => '3. Must + be + V3',
                        'sections' => [
                            [
                                'label' => 'Must (обов\'язок)',
                                'color' => 'emerald',
                                'description' => '<strong>Must + be + V3</strong> — щось обов\'язково має бути зроблено.',
                                'examples' => [
                                    ['en' => 'This task must be done today.', 'ua' => 'Це завдання має бути виконане сьогодні.'],
                                    ['en' => 'The rules must be followed.', 'ua' => 'Правила мають дотримуватися.'],
                                    ['en' => 'Safety regulations must be observed.', 'ua' => 'Правила безпеки мають дотримуватися.'],
                                ],
                            ],
                            [
                                'label' => 'Must not (заборона)',
                                'color' => 'rose',
                                'description' => '<strong>Must not + be + V3</strong> — заборона.',
                                'examples' => [
                                    ['en' => 'This information must not be shared.', 'ua' => 'Цю інформацію не можна розголошувати.'],
                                    ['en' => 'The door must not be left open.', 'ua' => 'Двері не можна залишати відчиненими.'],
                                    ['en' => 'Personal data must not be disclosed.', 'ua' => 'Особисті дані не можна розголошувати.'],
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
                        'title' => '4. Should / Ought to + be + V3',
                        'sections' => [
                            [
                                'label' => 'Should (рекомендація)',
                                'color' => 'emerald',
                                'description' => '<strong>Should + be + V3</strong> — рекомендація, порада.',
                                'examples' => [
                                    ['en' => 'The report should be submitted by Friday.', 'ua' => 'Звіт слід подати до п\'ятниці.'],
                                    ['en' => 'Children should be supervised.', 'ua' => 'Діти мають бути під наглядом.'],
                                    ['en' => 'This issue should be discussed.', 'ua' => 'Це питання слід обговорити.'],
                                ],
                            ],
                            [
                                'label' => 'Ought to (обов\'язок)',
                                'color' => 'sky',
                                'description' => '<strong>Ought to + be + V3</strong> — більш формальна форма should.',
                                'examples' => [
                                    ['en' => 'The contract ought to be signed.', 'ua' => 'Контракт слід підписати.'],
                                    ['en' => 'The problem ought to be addressed.', 'ua' => 'Проблему слід вирішити.'],
                                    ['en' => 'This matter ought to be taken seriously.', 'ua' => 'До цього питання слід поставитися серйозно.'],
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
                        'title' => '5. May / Might + be + V3',
                        'sections' => [
                            [
                                'label' => 'May (ймовірність / дозвіл)',
                                'color' => 'emerald',
                                'description' => '<strong>May + be + V3</strong> — щось, можливо, відбудеться.',
                                'examples' => [
                                    ['en' => 'The meeting may be postponed.', 'ua' => 'Зустріч, можливо, буде перенесено.'],
                                    ['en' => 'The project may be cancelled.', 'ua' => 'Проєкт, можливо, буде скасовано.'],
                                    ['en' => 'Tickets may be purchased at the entrance.', 'ua' => 'Квитки можна придбати на вході.'],
                                ],
                            ],
                            [
                                'label' => 'Might (менша ймовірність)',
                                'color' => 'sky',
                                'description' => '<strong>Might + be + V3</strong> — менша ймовірність, ніж may.',
                                'examples' => [
                                    ['en' => 'The event might be cancelled.', 'ua' => 'Захід, можливо, буде скасовано.'],
                                    ['en' => 'Mistakes might be made.', 'ua' => 'Помилки можуть бути допущені.'],
                                    ['en' => 'The deadline might be extended.', 'ua' => 'Дедлайн, можливо, буде продовжено.'],
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
                        'title' => '6. Have to / Need to + be + V3',
                        'sections' => [
                            [
                                'label' => 'Have to (необхідність)',
                                'color' => 'emerald',
                                'description' => '<strong>Have to + be + V3</strong> — необхідність через зовнішні обставини.',
                                'examples' => [
                                    ['en' => 'The work has to be finished by Monday.', 'ua' => 'Роботу треба закінчити до понеділка.'],
                                    ['en' => 'These forms have to be filled in.', 'ua' => 'Ці форми треба заповнити.'],
                                    ['en' => 'The password has to be changed.', 'ua' => 'Пароль треба змінити.'],
                                ],
                            ],
                            [
                                'label' => 'Need to (потреба)',
                                'color' => 'sky',
                                'description' => '<strong>Need to + be + V3</strong> — щось потрібно зробити.',
                                'examples' => [
                                    ['en' => 'The car needs to be repaired.', 'ua' => 'Машину треба відремонтувати.'],
                                    ['en' => 'The software needs to be updated.', 'ua' => 'Програмне забезпечення треба оновити.'],
                                    ['en' => 'The issue needs to be resolved.', 'ua' => 'Проблему треба вирішити.'],
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
                        'title' => '7. Заперечення та питання',
                        'sections' => [
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Формула: <strong>modal + not + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report can\'t be finished today.', 'ua' => 'Звіт не може бути завершений сьогодні.'],
                                    ['en' => 'This shouldn\'t be ignored.', 'ua' => 'Це не слід ігнорувати.'],
                                    ['en' => 'The door mustn\'t be left unlocked.', 'ua' => 'Двері не можна залишати незамкненими.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => 'Формула: <strong>Modal + Subject + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Can this problem be solved?', 'ua' => 'Чи можна вирішити цю проблему?'],
                                    ['en' => 'Should the meeting be rescheduled?', 'ua' => 'Чи слід перенести зустріч?'],
                                    ['en' => 'Must the form be signed?', 'ua' => 'Чи треба підписати форму?'],
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
                        'title' => '8. Зведена таблиця Modal Passive',
                        'intro' => 'Всі модальні дієслова у пасиві:',
                        'rows' => [
                            [
                                'en' => 'can + be + V3',
                                'ua' => 'можливість',
                                'note' => 'It can be done.',
                            ],
                            [
                                'en' => 'could + be + V3',
                                'ua' => 'можливість / ввічливість',
                                'note' => 'It could be improved.',
                            ],
                            [
                                'en' => 'may + be + V3',
                                'ua' => 'ймовірність / дозвіл',
                                'note' => 'It may be cancelled.',
                            ],
                            [
                                'en' => 'might + be + V3',
                                'ua' => 'менша ймовірність',
                                'note' => 'It might be delayed.',
                            ],
                            [
                                'en' => 'must + be + V3',
                                'ua' => 'обов\'язок',
                                'note' => 'It must be done.',
                            ],
                            [
                                'en' => 'should + be + V3',
                                'ua' => 'рекомендація',
                                'note' => 'It should be checked.',
                            ],
                            [
                                'en' => 'have to + be + V3',
                                'ua' => 'необхідність',
                                'note' => 'It has to be finished.',
                            ],
                            [
                                'en' => 'need to + be + V3',
                                'ua' => 'потреба',
                                'note' => 'It needs to be repaired.',
                            ],
                        ],
                        'warning' => '📌 Після модального дієслова завжди <strong>be</strong> (не am/is/are)!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання is/are замість be.',
                                'wrong' => 'The work must is done.',
                                'right' => '✅ The work must be done.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск be.',
                                'wrong' => 'This should finished today.',
                                'right' => '✅ This should be finished today.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'The report can be write.',
                                'right' => '✅ The report can be written.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            'Формула: <strong>modal + be + V3</strong> (can be done, must be finished).',
                            'Після модального дієслова завжди <strong>be</strong>, а не am/is/are.',
                            '<strong>Can/Could</strong> — можливість: This can be done.',
                            '<strong>Must</strong> — обов\'язок: This must be done.',
                            '<strong>Should/Ought to</strong> — рекомендація: This should be done.',
                            '<strong>May/Might</strong> — ймовірність: This may be cancelled.',
                            '<strong>Have to/Need to</strong> — необхідність: This has to be done.',
                            'Заперечення: <strong>modal + not + be + V3</strong>.',
                            'Питання: <strong>Modal + S + be + V3?</strong>',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з розширеної граматики пасиву',
                        'items' => [
                            [
                                'label' => 'Заперечення та питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Passive з модальними (поточна)',
                                'current' => true,
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
