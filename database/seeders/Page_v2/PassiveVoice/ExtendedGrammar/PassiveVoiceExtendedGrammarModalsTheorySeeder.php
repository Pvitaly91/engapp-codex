<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

class PassiveVoiceExtendedGrammarModalsTheorySeeder extends PassiveVoiceExtendedGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-modals';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive з модальними дієсловами — Passive with Modals',
            'subtitle_html' => '<p><strong>Passive with Modals</strong> — вивчи, як утворюється пасивний стан з модальними дієсловами: can, could, must, should, may, might. Формула: <strong>modal + be + V3</strong>.</p>',
            'subtitle_text' => 'Пасивний стан з модальними дієсловами: can be done, must be sent, should be checked. Формула: modal + be + V3.',
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
                        'intro' => 'У цій темі ти навчишся утворювати <strong>пасивний стан з модальними дієсловами</strong>: can, must, should, may, might, could. Базова формула: <strong>modal + be + V3</strong>.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>modal + be + V3</strong>:',
                                'example' => 'It must be done today.',
                            ],
                            [
                                'label' => 'Можливість',
                                'color' => 'blue',
                                'text' => '<strong>can/could + be + V3</strong>:',
                                'example' => 'This can be fixed easily.',
                            ],
                            [
                                'label' => 'Необхідність',
                                'color' => 'rose',
                                'text' => '<strong>must/should + be + V3</strong>:',
                                'example' => 'The rules must be followed.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Базова структура Modal Passive',
                        'intro' => 'Пасивний стан з модальними дієсловами завжди використовує <strong>modal + be + V3</strong>:',
                        'items' => [
                            [
                                'label' => 'Формула',
                                'title' => 'modal + be + V3',
                                'subtitle' => 'be завжди в інфінітиві, V3 — третя форма дієслова',
                            ],
                            [
                                'label' => 'Без to',
                                'title' => 'Без частки to',
                                'subtitle' => 'Після модальних дієслів be йде БЕЗ to',
                            ],
                            [
                                'label' => 'Незмінний V3',
                                'title' => 'V3 не змінюється',
                                'subtitle' => 'Past Participle завжди в одній формі',
                            ],
                            [
                                'label' => 'Всі підмети',
                                'title' => 'Для всіх підметів',
                                'subtitle' => 'Структура однакова для I/he/they',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Can/Could + be + V3 (можливість, здатність)',
                        'sections' => [
                            [
                                'label' => 'can be + V3',
                                'color' => 'emerald',
                                'description' => '<strong>Можливість</strong> або <strong>здатність</strong> бути дією. Теперішній час.',
                                'examples' => [
                                    ['en' => 'This problem can be solved easily.', 'ua' => 'Цю проблему можна легко вирішити.'],
                                    ['en' => 'The document can be downloaded from the website.', 'ua' => 'Документ можна завантажити з сайту.'],
                                    ['en' => 'English can be learned online.', 'ua' => 'Англійську можна вивчити онлайн.'],
                                ],
                            ],
                            [
                                'label' => 'could be + V3',
                                'color' => 'sky',
                                'description' => '<strong>Умовна можливість</strong> або можливість у минулому.',
                                'examples' => [
                                    ['en' => 'The work could be finished tomorrow.', 'ua' => 'Робота могла б бути закінчена завтра.'],
                                    ['en' => 'This issue could be resolved with more time.', 'ua' => 'Це питання могло б бути вирішене з більшим часом.'],
                                    ['en' => 'The building could be seen from far away.', 'ua' => 'Будівлю можна було побачити здалеку.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>cannot (can\'t) / could not (couldn\'t) + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'This error cannot be fixed.', 'ua' => 'Цю помилку не можна виправити.'],
                                    ['en' => 'The package couldn\'t be delivered.', 'ua' => 'Посилку не можна було доставити.'],
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
                        'title' => '3. Must/Have to + be + V3 (необхідність)',
                        'sections' => [
                            [
                                'label' => 'must be + V3',
                                'color' => 'rose',
                                'description' => '<strong>Сильна необхідність</strong> або обов\'язок. Наказ, правило.',
                                'examples' => [
                                    ['en' => 'This task must be done today.', 'ua' => 'Це завдання має бути виконане сьогодні.'],
                                    ['en' => 'The rules must be followed.', 'ua' => 'Правила мають дотримуватися.'],
                                    ['en' => 'All documents must be signed.', 'ua' => 'Усі документи мають бути підписані.'],
                                ],
                            ],
                            [
                                'label' => 'has to / have to be + V3',
                                'color' => 'amber',
                                'description' => 'Необхідність через зовнішні обставини. <strong>have to</strong> має форми в різних часах.',
                                'examples' => [
                                    ['en' => 'The work has to be finished by Friday.', 'ua' => 'Робота має бути закінчена до п\'ятниці.'],
                                    ['en' => 'The application had to be submitted yesterday.', 'ua' => 'Заявка мала бути подана вчора.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>must not (mustn\'t)</strong> — заборона. <strong>don\'t have to</strong> — не обов\'язково.',
                                'examples' => [
                                    ['en' => 'This button must not be pressed.', 'ua' => 'Цю кнопку не можна натискати. (заборона)'],
                                    ['en' => 'The report doesn\'t have to be submitted today.', 'ua' => 'Звіт не обов\'язково подавати сьогодні.'],
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
                        'title' => '4. Should/Ought to + be + V3 (рекомендація)',
                        'sections' => [
                            [
                                'label' => 'should be + V3',
                                'color' => 'blue',
                                'description' => '<strong>Порада</strong> або <strong>рекомендація</strong>. Що правильно або бажано зробити.',
                                'examples' => [
                                    ['en' => 'The report should be checked before submission.', 'ua' => 'Звіт слід перевірити перед поданням.'],
                                    ['en' => 'These instructions should be followed carefully.', 'ua' => 'Ці інструкції слід уважно дотримуватися.'],
                                    ['en' => 'Mistakes should be corrected immediately.', 'ua' => 'Помилки слід виправляти негайно.'],
                                ],
                            ],
                            [
                                'label' => 'ought to be + V3',
                                'color' => 'sky',
                                'description' => 'Те саме, що <strong>should</strong>, але більш формально. Рідко використовується.',
                                'examples' => [
                                    ['en' => 'The project ought to be completed soon.', 'ua' => 'Проєкт має бути завершений скоро.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>should not (shouldn\'t) + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'This information should not be shared.', 'ua' => 'Цю інформацію не слід поширювати.'],
                                    ['en' => 'The door shouldn\'t be left open.', 'ua' => 'Двері не слід залишати відкритими.'],
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
                        'title' => '5. May/Might + be + V3 (ймовірність, дозвіл)',
                        'sections' => [
                            [
                                'label' => 'may be + V3',
                                'color' => 'amber',
                                'description' => '<strong>Ймовірність</strong> (можливо) або <strong>дозвіл</strong> (формально).',
                                'examples' => [
                                    ['en' => 'The package may be delivered today.', 'ua' => 'Посилка, можливо, буде доставлена сьогодні.'],
                                    ['en' => 'This issue may be discussed at the meeting.', 'ua' => 'Це питання, можливо, буде обговорено на зустрічі.'],
                                    ['en' => 'Photos may be taken during the event.', 'ua' => 'Фото можуть робитися під час заходу. (дозволено)'],
                                ],
                            ],
                            [
                                'label' => 'might be + V3',
                                'color' => 'sky',
                                'description' => 'Менша ймовірність, ніж <strong>may</strong>. "Можливо, але малоймовірно".',
                                'examples' => [
                                    ['en' => 'The project might be postponed.', 'ua' => 'Проєкт, можливо, буде відкладено.'],
                                    ['en' => 'The error might be caused by the update.', 'ua' => 'Помилка, можливо, спричинена оновленням.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => '<strong>may not / might not + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The results may not be ready yet.', 'ua' => 'Результати, можливо, ще не готові.'],
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
                        'title' => '6. Питання з модальними в пасиві',
                        'sections' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => '<strong>Modal + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Can this problem be solved?', 'ua' => 'Цю проблему можна вирішити?'],
                                    ['en' => 'Must the task be done today?', 'ua' => 'Завдання має бути виконане сьогодні?'],
                                    ['en' => 'Should the report be submitted now?', 'ua' => 'Звіт слід подати зараз?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'sky',
                                'description' => '<strong>Wh-word + modal + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'How can this be fixed?', 'ua' => 'Як це можна виправити?'],
                                    ['en' => 'When should the work be finished?', 'ua' => 'Коли робота має бути закінчена?'],
                                    ['en' => 'Where must the documents be signed?', 'ua' => 'Де документи мають бути підписані?'],
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
                        'title' => '7. Зведення модальних у пасиві',
                        'intro' => 'Основні модальні дієслова з пасивним станом:',
                        'rows' => [
                            [
                                'en' => 'can be + V3',
                                'ua' => 'можливість',
                                'note' => 'This can be done easily.',
                            ],
                            [
                                'en' => 'could be + V3',
                                'ua' => 'умовна можливість',
                                'note' => 'It could be finished tomorrow.',
                            ],
                            [
                                'en' => 'must be + V3',
                                'ua' => 'сильна необхідність',
                                'note' => 'It must be done now.',
                            ],
                            [
                                'en' => 'should be + V3',
                                'ua' => 'рекомендація',
                                'note' => 'It should be checked.',
                            ],
                            [
                                'en' => 'may be + V3',
                                'ua' => 'ймовірність, дозвіл',
                                'note' => 'It may be delayed.',
                            ],
                            [
                                'en' => 'might be + V3',
                                'ua' => 'менша ймовірність',
                                'note' => 'It might be cancelled.',
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
                                'title' => 'Використання to перед be.',
                                'wrong' => 'It must to be done.',
                                'right' => '✅ It must be done.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна форма be.',
                                'wrong' => 'It can is done.',
                                'right' => '✅ It can be done.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'It should be do.',
                                'right' => '✅ It should be done.',
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
                            'Формула: <strong>modal + be + V3</strong>. be завжди в інфінітиві БЕЗ to.',
                            '<strong>can/could be + V3</strong> — можливість, здатність.',
                            '<strong>must/have to be + V3</strong> — необхідність, обов\'язок.',
                            '<strong>should/ought to be + V3</strong> — рекомендація, порада.',
                            '<strong>may/might be + V3</strong> — ймовірність, дозвіл.',
                            'Питання: <strong>Modal + S + be + V3?</strong>',
                            'Заперечення: <strong>modal + not + be + V3</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
