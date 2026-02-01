<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\InfinitivesGerund;

class PassiveVoiceV2PassiveInfinitiveTheorySeeder extends PassiveVoiceV2InfinitivesGerundPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-passive-infinitive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасивний інфінітив — to be done / to have been done',
            'subtitle_html' => '<p><strong>Пасивний інфінітив (Passive Infinitive)</strong> — це форма інфінітива, яка показує, що дія виконується над підметом, а не ним. Існують дві основні форми: <strong>to be done</strong> (простий пасивний інфінітив) та <strong>to have been done</strong> (перфектний пасивний інфінітив).</p>',
            'subtitle_text' => 'Пасивний інфінітив: to be done (простий) та to have been done (перфектний). Утворення, використання після need/want/expect/seem, приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-infinitives-gerund',
                'title' => 'Інфінітив та герундій у пасиві',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Infinitive',
                'Пасивний інфінітив',
                'to be done',
                'to have been done',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>пасивний інфінітив</strong>: як утворювати простий (to be done) та перфектний (to have been done) пасивний інфінітив, коли їх використовувати та з якими дієсловами вони поєднуються.',
                        'rules' => [
                            [
                                'label' => 'Простий',
                                'color' => 'emerald',
                                'text' => '<strong>to be + V3 (Past Participle)</strong> — теперішня/майбутня дія:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Перфектний',
                                'color' => 'blue',
                                'text' => '<strong>to have been + V3</strong> — попередня дія:',
                                'example' => 'He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Порівняння',
                                'color' => 'rose',
                                'text' => 'Active: <strong>to do / to have done</strong> → Passive: <strong>to be done / to have been done</strong>',
                                'example' => 'to write → to be written → to have been written',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - утворення
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Утворення пасивного інфінітива',
                        'intro' => 'Порівняння активних та пасивних форм інфінітива:',
                        'items' => [
                            [
                                'label' => 'Active Simple',
                                'title' => 'to do / to write',
                                'subtitle' => 'I want to write a letter. — Я хочу написати листа.',
                            ],
                            [
                                'label' => 'Passive Simple',
                                'title' => 'to be done / to be written',
                                'subtitle' => 'The letter needs to be written. — Листа потрібно написати.',
                            ],
                            [
                                'label' => 'Active Perfect',
                                'title' => 'to have done / to have written',
                                'subtitle' => 'He claims to have written the book. — Він стверджує, що написав книгу.',
                            ],
                            [
                                'label' => 'Passive Perfect',
                                'title' => 'to have been done / to have been written',
                                'subtitle' => 'The book seems to have been written long ago. — Книга, здається, була написана давно.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - простий пасивний інфінітив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Простий пасивний інфінітив (to be done)',
                        'sections' => [
                            [
                                'label' => 'Після need / want / expect',
                                'color' => 'emerald',
                                'description' => 'Коли хтось хоче/очікує, що <strong>щось буде зроблено</strong>:',
                                'examples' => [
                                    ['en' => 'The report needs to be finished by Friday.', 'ua' => 'Звіт потрібно закінчити до п\'ятниці.'],
                                    ['en' => 'I want this issue to be resolved immediately.', 'ua' => 'Я хочу, щоб це питання вирішили негайно.'],
                                    ['en' => 'They expect the project to be approved.', 'ua' => 'Вони очікують, що проєкт схвалять.'],
                                    ['en' => 'The car needs to be repaired.', 'ua' => 'Машину потрібно відремонтувати.'],
                                ],
                            ],
                            [
                                'label' => 'Після seem / appear',
                                'color' => 'blue',
                                'description' => 'Для вираження <strong>враження або здогадки</strong>:',
                                'examples' => [
                                    ['en' => 'The door seems to be locked.', 'ua' => 'Здається, двері замкнені.'],
                                    ['en' => 'He appears to be respected by everyone.', 'ua' => 'Здається, його всі поважають.'],
                                    ['en' => 'The problem appears to be solved.', 'ua' => 'Проблема, схоже, вирішена.'],
                                ],
                            ],
                            [
                                'label' => 'Після would like / prefer',
                                'color' => 'amber',
                                'description' => 'Для вираження <strong>бажання чи переваги</strong>:',
                                'examples' => [
                                    ['en' => 'I would like to be informed about the decision.', 'ua' => 'Я хотів би бути поінформованим про рішення.'],
                                    ['en' => 'She prefers to be left alone.', 'ua' => 'Вона віддає перевагу, щоб її залишили на самоті.'],
                                    ['en' => 'We would like to be invited to the meeting.', 'ua' => 'Ми хотіли б бути запрошеними на зустріч.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - перфектний пасивний інфінітив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '3. Перфектний пасивний інфінітив (to have been done)',
                        'sections' => [
                            [
                                'label' => 'Попередня дія',
                                'color' => 'emerald',
                                'description' => 'Вказує на дію, що <strong>відбулася раніше</strong> за основне дієслово:',
                                'examples' => [
                                    ['en' => 'He seems to have been promoted last month.', 'ua' => 'Здається, його підвищили минулого місяця.'],
                                    ['en' => 'The documents appear to have been lost.', 'ua' => 'Документи, схоже, були втрачені.'],
                                    ['en' => 'The letter seems to have been sent already.', 'ua' => 'Лист, здається, вже надіслано.'],
                                ],
                            ],
                            [
                                'label' => 'Reporting structures',
                                'color' => 'blue',
                                'description' => 'У безособових конструкціях з <strong>is said / believed / reported / known</strong>:',
                                'examples' => [
                                    ['en' => 'He is believed to have been kidnapped.', 'ua' => 'Вважається, що його викрали.'],
                                    ['en' => 'She is reported to have been seen in Paris.', 'ua' => 'Повідомляється, що її бачили в Парижі.'],
                                    ['en' => 'The painting is thought to have been stolen.', 'ua' => 'Вважається, що картину було вкрадено.'],
                                    ['en' => 'He is known to have been educated in Oxford.', 'ua' => 'Відомо, що він навчався в Оксфорді.'],
                                ],
                            ],
                            [
                                'label' => 'Після модальних + have',
                                'color' => 'rose',
                                'description' => 'Для <strong>припущень про минуле</strong>:',
                                'examples' => [
                                    ['en' => 'The work should have been completed yesterday.', 'ua' => 'Роботу мали б завершити вчора.'],
                                    ['en' => 'The message must have been deleted.', 'ua' => 'Повідомлення, мабуть, було видалено.'],
                                    ['en' => 'The car could have been stolen.', 'ua' => 'Машину могли вкрасти.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - дієслова з пасивним інфінітивом
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Дієслова, що поєднуються з пасивним інфінітивом',
                        'sections' => [
                            [
                                'label' => 'Очікування / бажання',
                                'color' => 'emerald',
                                'description' => '<strong>want, need, expect, would like, prefer, wish</strong>:',
                                'examples' => [
                                    ['en' => 'I expect to be paid on time.', 'ua' => 'Я очікую, що мені заплатять вчасно.'],
                                    ['en' => 'Nobody wants to be forgotten.', 'ua' => 'Ніхто не хоче бути забутим.'],
                                    ['en' => 'The floor needs to be cleaned.', 'ua' => 'Підлогу потрібно помити.'],
                                ],
                            ],
                            [
                                'label' => 'Здається / виглядає',
                                'color' => 'blue',
                                'description' => '<strong>seem, appear, happen, turn out, prove</strong>:',
                                'examples' => [
                                    ['en' => 'He seems to be trusted by his colleagues.', 'ua' => 'Здається, колеги йому довіряють.'],
                                    ['en' => 'The theory turned out to be proven wrong.', 'ua' => 'Теорія виявилася спростованою.'],
                                    ['en' => 'She happened to be chosen for the role.', 'ua' => 'Сталося так, що її обрали на роль.'],
                                ],
                            ],
                            [
                                'label' => 'Impersonal constructions',
                                'color' => 'amber',
                                'description' => '<strong>is said, is believed, is reported, is known, is thought</strong>:',
                                'examples' => [
                                    ['en' => 'He is said to be very intelligent.', 'ua' => 'Кажуть, що він дуже розумний.'],
                                    ['en' => 'They are believed to be innocent.', 'ua' => 'Вважається, що вони невинні.'],
                                    ['en' => 'The company is reported to be expanding.', 'ua' => 'Повідомляється, що компанія розширюється.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Simple vs Perfect
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Порівняння: to be done vs to have been done',
                        'intro' => 'Коли використовувати простий чи перфектний пасивний інфінітив:',
                        'rows' => [
                            [
                                'en' => 'to be done',
                                'ua' => 'Теперішня / майбутня дія',
                                'note' => 'He seems to be respected. — Його, здається, поважають (зараз).',
                            ],
                            [
                                'en' => 'to have been done',
                                'ua' => 'Попередня (минула) дія',
                                'note' => 'He seems to have been promoted. — Його, здається, підвищили (раніше).',
                            ],
                            [
                                'en' => 'to be done (need)',
                                'ua' => 'Необхідність виконання',
                                'note' => 'The work needs to be finished. — Роботу потрібно закінчити.',
                            ],
                            [
                                'en' => 'to have been done (should)',
                                'ua' => 'Критика минулої бездіяльності',
                                'note' => 'The work should have been finished. — Роботу мали б закінчити.',
                            ],
                        ],
                        'warning' => '📌 Перфектний інфінітив вказує на дію, яка сталася ПЕРЕД основним дієсловом!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск to be в пасивному інфінітиві.',
                                'wrong' => 'The report needs finished.',
                                'right' => '✅ The report needs to be finished.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'She wants to be invite.',
                                'right' => '✅ She wants to be invited.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутання простого та перфектного.',
                                'wrong' => 'He seems to be promoted last year.',
                                'right' => '✅ He seems to have been promoted last year.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів у перфектному.',
                                'wrong' => 'to been have done',
                                'right' => '✅ to have been done',
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
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Простий пасивний інфінітив: <strong>to be + V3</strong> (to be done, to be written).',
                            'Перфектний пасивний інфінітив: <strong>to have been + V3</strong> (to have been done).',
                            '<strong>to be done</strong> — для теперішніх/майбутніх дій або загальних тверджень.',
                            '<strong>to have been done</strong> — для дій, що сталися раніше.',
                            'Використовується після: <strong>need, want, expect, seem, appear, would like</strong>.',
                            'Reporting structures: <strong>is said/believed/reported + to be/have been done</strong>.',
                            'Модальні + have: <strong>should/must/could have been done</strong> — припущення про минуле.',
                            'V3 (Past Participle) — обов\'язковий компонент пасивного інфінітива.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
