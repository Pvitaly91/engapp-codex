<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceImpersonalPassiveTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-impersonal-passive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Безособовий пасив (Impersonal Passive)',
            'subtitle_html' => '<p><strong>Безособовий пасив (Impersonal Passive)</strong> — це конструкція, яка використовується для передачі інформації, чуток, думок без вказівки на конкретне джерело. Типові конструкції: <strong>It is said that...</strong> або <strong>Subject + is said + to...</strong></p>',
            'subtitle_text' => 'Безособовий пасив: It is said that..., Subject + is said to..., reporting verbs у пасиві, вживання в офіційному стилі.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Impersonal Passive',
                'Безособовий пасив',
                'It is said',
                'B2',
                'C1',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2–C1',
                        'intro' => 'У цій темі ти вивчиш <strong>безособовий пасив</strong> — конструкцію для передачі інформації, коли джерело <strong>невідоме</strong>, <strong>неважливе</strong> або коли ви хочете зробити текст <strong>більш формальним</strong>.',
                        'rules' => [
                            [
                                'label' => 'Конструкція 1',
                                'color' => 'emerald',
                                'text' => '<strong>It + is/was + V3 + that + clause</strong>:',
                                'example' => 'It is said that he is very rich.',
                            ],
                            [
                                'label' => 'Конструкція 2',
                                'color' => 'blue',
                                'text' => '<strong>Subject + is/was + V3 + to + infinitive</strong>:',
                                'example' => 'He is said to be very rich.',
                            ],
                            [
                                'label' => 'Дієслова',
                                'color' => 'rose',
                                'text' => 'Reporting verbs: <strong>say, believe, think, know, report, expect, consider</strong>',
                                'example' => 'She is believed to be the best candidate.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - reporting verbs
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Reporting verbs у безособовому пасиві',
                        'intro' => 'Дієслова, які використовуються у безособовому пасиві:',
                        'items' => [
                            [
                                'label' => 'say',
                                'title' => 'It is said that... / Subject is said to...',
                                'subtitle' => 'Кажуть, що...',
                            ],
                            [
                                'label' => 'believe',
                                'title' => 'It is believed that... / Subject is believed to...',
                                'subtitle' => 'Вважається, що...',
                            ],
                            [
                                'label' => 'think',
                                'title' => 'It is thought that... / Subject is thought to...',
                                'subtitle' => 'Вважають, що...',
                            ],
                            [
                                'label' => 'know',
                                'title' => 'It is known that... / Subject is known to...',
                                'subtitle' => 'Відомо, що...',
                            ],
                            [
                                'label' => 'report',
                                'title' => 'It is reported that... / Subject is reported to...',
                                'subtitle' => 'Повідомляється, що...',
                            ],
                            [
                                'label' => 'expect',
                                'title' => 'It is expected that... / Subject is expected to...',
                                'subtitle' => 'Очікується, що...',
                            ],
                            [
                                'label' => 'consider',
                                'title' => 'It is considered that... / Subject is considered to...',
                                'subtitle' => 'Вважається, що...',
                            ],
                            [
                                'label' => 'suppose',
                                'title' => 'It is supposed that... / Subject is supposed to...',
                                'subtitle' => 'Передбачається, що...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - конструкція з It
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Конструкція: It + is/was + V3 + that...',
                        'sections' => [
                            [
                                'label' => 'Present',
                                'color' => 'emerald',
                                'description' => '<strong>It is + V3 + that...</strong> — про теперішнє:',
                                'examples' => [
                                    ['en' => 'It is said that he works very hard.', 'ua' => 'Кажуть, що він працює дуже наполегливо.'],
                                    ['en' => 'It is believed that the company is growing.', 'ua' => 'Вважається, що компанія розвивається.'],
                                    ['en' => 'It is reported that the situation is improving.', 'ua' => 'Повідомляється, що ситуація покращується.'],
                                ],
                            ],
                            [
                                'label' => 'Past',
                                'color' => 'blue',
                                'description' => '<strong>It was + V3 + that...</strong> — про минуле:',
                                'examples' => [
                                    ['en' => 'It was thought that the project would fail.', 'ua' => 'Вважалося, що проєкт провалиться.'],
                                    ['en' => 'It was reported that the accident happened at night.', 'ua' => 'Повідомлялося, що аварія сталася вночі.'],
                                    ['en' => 'It was believed that he was innocent.', 'ua' => 'Вважалося, що він невинний.'],
                                ],
                            ],
                            [
                                'label' => 'Other tenses',
                                'color' => 'amber',
                                'description' => 'Інші часи: <strong>It has been said, It will be reported...</strong>:',
                                'examples' => [
                                    ['en' => 'It has been reported that the storm is approaching.', 'ua' => 'Повідомлено, що наближається буря.'],
                                    ['en' => 'It will be announced that the winner is...', 'ua' => 'Буде оголошено, що переможець...'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - конструкція з Subject
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Конструкція: Subject + is/was + V3 + to + infinitive',
                        'sections' => [
                            [
                                'label' => 'to + Simple Infinitive',
                                'color' => 'emerald',
                                'description' => '<strong>to be / to do</strong> — дія одночасна з reporting verb:',
                                'examples' => [
                                    ['en' => 'He is said to be a good doctor.', 'ua' => 'Кажуть, що він гарний лікар.'],
                                    ['en' => 'She is believed to know the truth.', 'ua' => 'Вважається, що вона знає правду.'],
                                    ['en' => 'They are reported to be in Paris.', 'ua' => 'Повідомляється, що вони в Парижі.'],
                                ],
                            ],
                            [
                                'label' => 'to + Perfect Infinitive',
                                'color' => 'blue',
                                'description' => '<strong>to have done</strong> — дія передує reporting verb:',
                                'examples' => [
                                    ['en' => 'He is said to have won the lottery.', 'ua' => 'Кажуть, що він виграв лотерею.'],
                                    ['en' => 'She is believed to have left the country.', 'ua' => 'Вважається, що вона покинула країну.'],
                                    ['en' => 'They are reported to have stolen millions.', 'ua' => 'Повідомляється, що вони вкрали мільйони.'],
                                ],
                            ],
                            [
                                'label' => 'to + Continuous Infinitive',
                                'color' => 'amber',
                                'description' => '<strong>to be doing</strong> — дія у процесі:',
                                'examples' => [
                                    ['en' => 'He is said to be working on a new project.', 'ua' => 'Кажуть, що він працює над новим проєктом.'],
                                    ['en' => 'She is believed to be living in London.', 'ua' => 'Вважається, що вона живе в Лондоні.'],
                                    ['en' => 'They are reported to be hiding somewhere.', 'ua' => 'Повідомляється, що вони десь ховаються.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - перетворення
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Перетворення Active → Impersonal Passive',
                        'intro' => 'Як перетворити активне речення на безособовий пасив:',
                        'rows' => [
                            [
                                'en' => 'Active: People say that he is rich.',
                                'ua' => 'Люди кажуть, що він багатий.',
                                'note' => '→ It is said that he is rich. / He is said to be rich.',
                            ],
                            [
                                'en' => 'Active: They believe that she knows the truth.',
                                'ua' => 'Вони вважають, що вона знає правду.',
                                'note' => '→ It is believed that... / She is believed to know...',
                            ],
                            [
                                'en' => 'Active: Reports say that the company is growing.',
                                'ua' => 'У звітах говориться, що компанія росте.',
                                'note' => '→ It is reported that... / The company is reported to be growing.',
                            ],
                            [
                                'en' => 'Active: They thought that he had left.',
                                'ua' => 'Вони думали, що він пішов.',
                                'note' => '→ It was thought that... / He was thought to have left.',
                            ],
                        ],
                        'warning' => '📌 Використовуй Perfect Infinitive (to have done), якщо дія сталася раніше!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Коли використовувати безособовий пасив?',
                        'sections' => [
                            [
                                'label' => 'Невідоме джерело',
                                'color' => 'emerald',
                                'description' => 'Коли <strong>джерело інформації невідоме</strong> або неконкретне:',
                                'examples' => [
                                    ['en' => 'It is said that the castle is haunted.', 'ua' => 'Кажуть, що замок населений привидами.'],
                                    ['en' => 'It is believed that the earth is 4.5 billion years old.', 'ua' => 'Вважається, що Землі 4,5 мільярди років.'],
                                ],
                            ],
                            [
                                'label' => 'Офіційний стиль',
                                'color' => 'blue',
                                'description' => 'У <strong>новинах, офіційних документах, академічних текстах</strong>:',
                                'examples' => [
                                    ['en' => 'The suspect is reported to have fled the country.', 'ua' => 'Повідомляється, що підозрюваний втік з країни.'],
                                    ['en' => 'The economy is expected to grow by 3%.', 'ua' => 'Очікується, що економіка зросте на 3%.'],
                                ],
                            ],
                            [
                                'label' => 'Об\'єктивність',
                                'color' => 'amber',
                                'description' => 'Для <strong>об\'єктивного викладу</strong> без особистої думки:',
                                'examples' => [
                                    ['en' => 'It is thought that the painting is a forgery.', 'ua' => 'Вважається, що картина — підробка.'],
                                    ['en' => 'He is considered to be an expert in the field.', 'ua' => 'Він вважається експертом у цій галузі.'],
                                ],
                            ],
                            [
                                'label' => 'Чутки, припущення',
                                'color' => 'rose',
                                'description' => 'Для <strong>чуток, непідтверджених даних</strong>:',
                                'examples' => [
                                    ['en' => 'The celebrity is rumored to be dating a famous actor.', 'ua' => 'Ходять чутки, що знаменитість зустрічається з відомим актором.'],
                                    ['en' => 'It is alleged that the politician took bribes.', 'ua' => 'Стверджується, що політик брав хабарі.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - інші reporting verbs
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Інші дієслова у безособовому пасиві',
                        'intro' => 'Розширений список дієслів:',
                        'items' => [
                            [
                                'label' => 'allege',
                                'title' => 'It is alleged that...',
                                'subtitle' => 'Стверджується (без доказів), що...',
                            ],
                            [
                                'label' => 'claim',
                                'title' => 'It is claimed that...',
                                'subtitle' => 'Стверджується, що...',
                            ],
                            [
                                'label' => 'rumor',
                                'title' => 'It is rumored that...',
                                'subtitle' => 'Ходять чутки, що...',
                            ],
                            [
                                'label' => 'estimate',
                                'title' => 'It is estimated that...',
                                'subtitle' => 'За оцінками...',
                            ],
                            [
                                'label' => 'assume',
                                'title' => 'It is assumed that...',
                                'subtitle' => 'Припускається, що...',
                            ],
                            [
                                'label' => 'understand',
                                'title' => 'It is understood that...',
                                'subtitle' => 'Є розуміння, що...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск to перед інфінітивом.',
                                'wrong' => 'He is said be rich.',
                                'right' => '✅ He is said to be rich.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний вибір Simple/Perfect Infinitive.',
                                'wrong' => 'He is said to leave yesterday. (минула дія)',
                                'right' => '✅ He is said to have left yesterday.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання that з конструкцією Subject + is said to...',
                                'wrong' => 'He is said that to be rich.',
                                'right' => '✅ He is said to be rich.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильний час reporting verb.',
                                'wrong' => 'It is said that he was working there. (якщо він досі працює)',
                                'right' => '✅ It is said that he works there.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Конструкція 1: <strong>It + is/was + V3 + that + clause</strong> (It is said that...).',
                            'Конструкція 2: <strong>Subject + is/was + V3 + to + infinitive</strong> (He is said to...).',
                            'Reporting verbs: <strong>say, believe, think, know, report, expect, consider</strong>.',
                            '<strong>Simple Infinitive</strong> (to be) — дія одночасна з reporting verb.',
                            '<strong>Perfect Infinitive</strong> (to have done) — дія передує reporting verb.',
                            '<strong>Continuous Infinitive</strong> (to be doing) — дія у процесі.',
                            'Використовується в <strong>офіційному стилі, новинах, академічних текстах</strong>.',
                            'Надає тексту <strong>об\'єктивності</strong> та <strong>формальності</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
