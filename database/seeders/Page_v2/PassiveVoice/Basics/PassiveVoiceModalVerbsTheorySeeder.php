<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceModalVerbsTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-modal-verbs';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасив з модальними дієсловами',
            'subtitle_html' => '<p><strong>Пасив з модальними дієсловами</strong> утворюється за формулою <strong>modal + be + V3</strong>. Модальні дієслова (can, could, may, might, must, should, will, would) не змінюються, а пасив додається через <strong>be + V3</strong>.</p>',
            'subtitle_text' => 'Пасив з модальними дієсловами: can/could/may/might/must/should be done, утворення, заперечення, питання та приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Modal Verbs',
                'Модальні дієслова',
                'can be done',
                'must be done',
                'B1',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти вивчиш, як використовувати <strong>пасив з модальними дієсловами</strong>: can, could, may, might, must, should, will, would. Формула проста: <strong>modal + be + V3</strong>.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + modal + be + V3</strong>:',
                                'example' => 'This task must be done today.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + modal + not + be + V3</strong>:',
                                'example' => 'This task cannot be done today.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Modal + Subject + be + V3?</strong>:',
                                'example' => 'Can this task be done today?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - модальні дієслова у пасиві
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Модальні дієслова у пасиві',
                        'intro' => 'Формула: <strong>modal + be + V3</strong>:',
                        'items' => [
                            [
                                'label' => 'can',
                                'title' => 'can be done',
                                'subtitle' => 'This can be fixed. — Це можна виправити.',
                            ],
                            [
                                'label' => 'could',
                                'title' => 'could be done',
                                'subtitle' => 'It could be improved. — Це можна було б покращити.',
                            ],
                            [
                                'label' => 'may',
                                'title' => 'may be done',
                                'subtitle' => 'It may be postponed. — Це може бути відкладено.',
                            ],
                            [
                                'label' => 'might',
                                'title' => 'might be done',
                                'subtitle' => 'It might be cancelled. — Це може бути скасовано.',
                            ],
                            [
                                'label' => 'must',
                                'title' => 'must be done',
                                'subtitle' => 'It must be finished. — Це має бути завершено.',
                            ],
                            [
                                'label' => 'should',
                                'title' => 'should be done',
                                'subtitle' => 'It should be checked. — Це варто перевірити.',
                            ],
                            [
                                'label' => 'will',
                                'title' => 'will be done',
                                'subtitle' => 'It will be completed. — Це буде завершено.',
                            ],
                            [
                                'label' => 'would',
                                'title' => 'would be done',
                                'subtitle' => 'It would be appreciated. — Це було б оцінено.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - значення модальних
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Значення модальних дієслів у пасиві',
                        'sections' => [
                            [
                                'label' => 'can / could — можливість',
                                'color' => 'emerald',
                                'description' => '<strong>Можливість</strong> або <strong>здатність</strong>:',
                                'examples' => [
                                    ['en' => 'The problem can be solved easily.', 'ua' => 'Проблему можна легко вирішити.'],
                                    ['en' => 'This could be done in a different way.', 'ua' => 'Це можна було б зробити інакше.'],
                                    ['en' => 'The document can be downloaded online.', 'ua' => 'Документ можна завантажити онлайн.'],
                                ],
                            ],
                            [
                                'label' => 'may / might — ймовірність',
                                'color' => 'blue',
                                'description' => '<strong>Ймовірність</strong> або <strong>дозвіл</strong>:',
                                'examples' => [
                                    ['en' => 'The meeting may be postponed.', 'ua' => 'Зустріч може бути відкладена.'],
                                    ['en' => 'The flight might be cancelled.', 'ua' => 'Рейс може бути скасовано.'],
                                    ['en' => 'Photos may be taken here.', 'ua' => 'Тут можна фотографувати.'],
                                ],
                            ],
                            [
                                'label' => 'must / have to — необхідність',
                                'color' => 'amber',
                                'description' => '<strong>Необхідність</strong> або <strong>обов\'язок</strong>:',
                                'examples' => [
                                    ['en' => 'This work must be done today.', 'ua' => 'Ця робота має бути зроблена сьогодні.'],
                                    ['en' => 'The rules must be followed.', 'ua' => 'Правила мають дотримуватися.'],
                                    ['en' => 'Taxes have to be paid on time.', 'ua' => 'Податки мають сплачуватися вчасно.'],
                                ],
                            ],
                            [
                                'label' => 'should / ought to — рекомендація',
                                'color' => 'rose',
                                'description' => '<strong>Рекомендація</strong> або <strong>порада</strong>:',
                                'examples' => [
                                    ['en' => 'The report should be checked carefully.', 'ua' => 'Звіт варто ретельно перевірити.'],
                                    ['en' => 'Children should be protected.', 'ua' => 'Дітей треба захищати.'],
                                    ['en' => 'This information ought to be verified.', 'ua' => 'Цю інформацію слід перевірити.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Заперечення з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'cannot / can\'t be done',
                                'color' => 'rose',
                                'description' => '<strong>Неможливість</strong>:',
                                'examples' => [
                                    ['en' => 'This problem cannot be solved.', 'ua' => 'Цю проблему неможливо вирішити.'],
                                    ['en' => "The deadline can't be extended.", 'ua' => 'Дедлайн не можна продовжити.'],
                                ],
                            ],
                            [
                                'label' => 'must not / mustn\'t be done',
                                'color' => 'amber',
                                'description' => '<strong>Заборона</strong>:',
                                'examples' => [
                                    ['en' => 'This information must not be shared.', 'ua' => 'Цю інформацію не можна поширювати.'],
                                    ['en' => 'The password mustn\'t be revealed.', 'ua' => 'Пароль не можна розголошувати.'],
                                ],
                            ],
                            [
                                'label' => 'should not / shouldn\'t be done',
                                'color' => 'sky',
                                'description' => '<strong>Не рекомендується</strong>:',
                                'examples' => [
                                    ['en' => 'This shouldn\'t be ignored.', 'ua' => 'Це не варто ігнорувати.'],
                                    ['en' => 'Children should not be left alone.', 'ua' => 'Дітей не слід залишати наодинці.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Питання з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Modal + Subject + be + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'Can this be done today?', 'ua' => 'Чи можна це зробити сьогодні?'],
                                    ['en' => 'Should the report be submitted now?', 'ua' => 'Чи потрібно подати звіт зараз?'],
                                    ['en' => 'Must the rules be followed?', 'ua' => 'Чи треба дотримуватися правил?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'emerald',
                                'description' => '<strong>Wh-word + modal + Subject + be + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'When can the work be finished?', 'ua' => 'Коли можна закінчити роботу?'],
                                    ['en' => 'How should the problem be solved?', 'ua' => 'Як слід вирішити проблему?'],
                                    ['en' => 'Where can the tickets be bought?', 'ua' => 'Де можна купити квитки?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Active vs Passive з модальними
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Порівняння Active vs Passive з модальними',
                        'intro' => 'Як перетворити активне речення з модальним дієсловом на пасивне:',
                        'rows' => [
                            [
                                'en' => 'Active: You must finish this work.',
                                'ua' => 'Ти маєш закінчити цю роботу.',
                                'note' => '→ Passive: This work must be finished.',
                            ],
                            [
                                'en' => 'Active: They can solve the problem.',
                                'ua' => 'Вони можуть вирішити проблему.',
                                'note' => '→ Passive: The problem can be solved.',
                            ],
                            [
                                'en' => 'Active: You should check the report.',
                                'ua' => 'Тобі слід перевірити звіт.',
                                'note' => '→ Passive: The report should be checked.',
                            ],
                            [
                                'en' => 'Active: We may postpone the meeting.',
                                'ua' => 'Ми можемо відкласти зустріч.',
                                'note' => '→ Passive: The meeting may be postponed.',
                            ],
                        ],
                        'warning' => '📌 Модальне дієслово залишається незмінним, змінюється тільки основне дієслово!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - Perfect модальні у пасиві
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Perfect модальні у пасиві (про минуле)',
                        'intro' => 'Формула: <strong>modal + have been + V3</strong>:',
                        'items' => [
                            [
                                'label' => 'must have been',
                                'title' => 'must have been done',
                                'subtitle' => 'It must have been stolen. — Напевно, це вкрали.',
                            ],
                            [
                                'label' => 'should have been',
                                'title' => 'should have been done',
                                'subtitle' => 'It should have been checked. — Це слід було перевірити.',
                            ],
                            [
                                'label' => 'could have been',
                                'title' => 'could have been done',
                                'subtitle' => 'It could have been avoided. — Цього можна було уникнути.',
                            ],
                            [
                                'label' => 'might have been',
                                'title' => 'might have been done',
                                'subtitle' => 'It might have been lost. — Можливо, це загубили.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Perfect модальні приклади
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Perfect модальні у пасиві: приклади',
                        'sections' => [
                            [
                                'label' => 'Припущення про минуле',
                                'color' => 'emerald',
                                'description' => '<strong>must/may/might/could have been + V3</strong>:',
                                'examples' => [
                                    ['en' => 'The letter must have been sent yesterday.', 'ua' => 'Лист, напевно, був відправлений вчора.'],
                                    ['en' => 'The window may have been broken by the kids.', 'ua' => 'Вікно, можливо, було розбите дітьми.'],
                                    ['en' => 'The file might have been deleted accidentally.', 'ua' => 'Файл, можливо, був видалений випадково.'],
                                ],
                            ],
                            [
                                'label' => 'Критика минулої дії',
                                'color' => 'rose',
                                'description' => '<strong>should have been + V3</strong> (слід було зробити, але не зробили):',
                                'examples' => [
                                    ['en' => 'The report should have been submitted on time.', 'ua' => 'Звіт слід було подати вчасно.'],
                                    ['en' => 'The problem should have been fixed earlier.', 'ua' => 'Проблему слід було виправити раніше.'],
                                    ['en' => 'The warning should have been given.', 'ua' => 'Попередження слід було дати.'],
                                ],
                            ],
                            [
                                'label' => 'Нереалізована можливість',
                                'color' => 'blue',
                                'description' => '<strong>could have been + V3</strong> (могло бути зроблено):',
                                'examples' => [
                                    ['en' => 'The accident could have been avoided.', 'ua' => 'Аварії можна було уникнути.'],
                                    ['en' => 'The work could have been done faster.', 'ua' => 'Роботу можна було зробити швидше.'],
                                    ['en' => 'The mistake could have been prevented.', 'ua' => 'Помилки можна було запобігти.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
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
                                'title' => 'Пропуск be після модального дієслова.',
                                'wrong' => 'The work must done today.',
                                'right' => '✅ The work must be done today.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання to після модального.',
                                'wrong' => 'It can to be fixed.',
                                'right' => '✅ It can be fixed.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильна форма у Perfect.',
                                'wrong' => 'It should have be done.',
                                'right' => '✅ It should have been done.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Змінювання модального дієслова.',
                                'wrong' => 'The work musts be done.',
                                'right' => '✅ The work must be done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            'Формула пасиву з модальними: <strong>modal + be + V3</strong>.',
                            'Заперечення: <strong>modal + not + be + V3</strong>.',
                            'Питання: <strong>Modal + Subject + be + V3?</strong>',
                            'Perfect форми про минуле: <strong>modal + have been + V3</strong>.',
                            '<strong>can/could be done</strong> — можливість.',
                            '<strong>may/might be done</strong> — ймовірність.',
                            '<strong>must be done</strong> — необхідність; <strong>mustn\'t be done</strong> — заборона.',
                            '<strong>should be done</strong> — рекомендація.',
                            '<strong>should have been done</strong> — критика (слід було зробити, але не зробили).',
                            'Модальні дієслова <strong>не змінюються</strong> за особами та числами!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
