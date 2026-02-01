<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\InfinitivesGerund;

class PassiveVoiceV2PassiveInfinitiveTheorySeeder extends PassiveVoiceV2InfinitivesGerundPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-passive-infinitive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасивний інфінітив — Passive Infinitive',
            'subtitle_html' => '<p><strong>Пасивний інфінітив (Passive Infinitive)</strong> — це форма інфінітива, яка підкреслює дію, що виконується над об\'єктом, а не самим об\'єктом. Використовується у формальному та академічному стилі для вираження необхідності, очікування чи здогадок про дії.</p>',
            'subtitle_text' => 'Пасивний інфінітив: to be done, to have been done. Структура, використання та приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2-infinitives-gerund',
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
                        'intro' => 'У цьому розділі ти вивчиш <strong>пасивні форми інфінітива</strong> — важливі конструкції для формального та академічного письма.',
                        'rules' => [
                            [
                                'label' => 'Простий пасивний інфінітив',
                                'color' => 'emerald',
                                'text' => '<strong>to be + V3</strong> — теперішня/майбутня дія:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Перфектний пасивний інфінітив',
                                'color' => 'blue',
                                'text' => '<strong>to have been + V3</strong> — попередня дія:',
                                'example' => 'He seems to have been promoted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - дві форми
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Дві форми пасивного інфінітива',
                        'intro' => 'Порівняння активних та пасивних форм інфінітива:',
                        'items' => [
                            [
                                'label' => 'Active Infinitive',
                                'title' => 'to do',
                                'subtitle' => '→ Passive: to be done (теперішнє/майбутнє)',
                            ],
                            [
                                'label' => 'Perfect Active Inf.',
                                'title' => 'to have done',
                                'subtitle' => '→ Passive: to have been done (попередня дія)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Simple Passive Infinitive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Простий пасивний інфінітив (to be done)',
                        'sections' => [
                            [
                                'label' => 'Після need/want/expect',
                                'color' => 'emerald',
                                'description' => 'Коли хтось хоче/очікує, що <strong>щось буде зроблено</strong>:',
                                'examples' => [
                                    ['en' => 'The report needs to be finished by Friday.', 'ua' => 'Звіт потрібно закінчити до п\'ятниці.'],
                                    ['en' => 'I want this issue to be resolved quickly.', 'ua' => 'Я хочу, щоб це питання вирішили швидко.'],
                                    ['en' => 'They expect the project to be approved.', 'ua' => 'Вони очікують, що проєкт схвалять.'],
                                    ['en' => 'The documents need to be signed today.', 'ua' => 'Документи треба підписати сьогодні.'],
                                ],
                            ],
                            [
                                'label' => 'Після seem/appear',
                                'color' => 'blue',
                                'description' => 'Для враження, здогадки про теперішній стан:',
                                'examples' => [
                                    ['en' => 'The door seems to be locked.', 'ua' => 'Здається, двері замкнені.'],
                                    ['en' => 'He appears to be respected by everyone.', 'ua' => 'Здається, його всі поважають.'],
                                    ['en' => 'The problem seems to be solved.', 'ua' => 'Здається, проблему вирішено.'],
                                ],
                            ],
                            [
                                'label' => 'Після модальних дієслів',
                                'color' => 'amber',
                                'description' => 'З <strong>can, could, should, must, may, might</strong>:',
                                'examples' => [
                                    ['en' => 'This work must be done carefully.', 'ua' => 'Цю роботу треба зробити акуратно.'],
                                    ['en' => 'The rules should be followed strictly.', 'ua' => 'Правила слід дотримуватися суворо.'],
                                    ['en' => 'The mistake can be corrected easily.', 'ua' => 'Помилку можна легко виправити.'],
                                    ['en' => 'The meeting may be postponed.', 'ua' => 'Зустріч може бути відкладена.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Perfect Passive Infinitive
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
                                'description' => 'Вказує на дію, що <strong>відбулася раніше</strong> відносно іншої дії або моменту:',
                                'examples' => [
                                    ['en' => 'He seems to have been promoted last month.', 'ua' => 'Здається, його підвищили минулого місяця.'],
                                    ['en' => 'The documents appear to have been lost.', 'ua' => 'Документи, схоже, були втрачені.'],
                                    ['en' => 'She claims to have been informed earlier.', 'ua' => 'Вона стверджує, що їй повідомили раніше.'],
                                ],
                            ],
                            [
                                'label' => 'Reporting structures',
                                'color' => 'blue',
                                'description' => 'У безособових конструкціях з <strong>is said/believed/reported/thought</strong>:',
                                'examples' => [
                                    ['en' => 'He is believed to have been kidnapped.', 'ua' => 'Вважається, що його викрали.'],
                                    ['en' => 'She is reported to have been seen in Paris.', 'ua' => 'Повідомляється, що її бачили в Парижі.'],
                                    ['en' => 'The painting is thought to have been stolen.', 'ua' => 'Вважається, що картину вкрали.'],
                                    ['en' => 'He is said to have been arrested yesterday.', 'ua' => 'Кажуть, що його заарештували вчора.'],
                                ],
                            ],
                            [
                                'label' => 'З модальними дієсловами минулого',
                                'color' => 'rose',
                                'description' => 'З <strong>should/could/might/must + have been + V3</strong> для припущень про минуле:',
                                'examples' => [
                                    ['en' => 'The email should have been sent earlier.', 'ua' => 'Електронний лист треба було відправити раніше.'],
                                    ['en' => 'He might have been warned about it.', 'ua' => 'Його, можливо, попередили про це.'],
                                    ['en' => 'The project must have been approved.', 'ua' => 'Проєкт, напевно, схвалили.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Порівняння двох форм',
                        'intro' => 'Коли використовувати кожну форму:',
                        'rows' => [
                            [
                                'en' => 'to be + V3',
                                'ua' => 'Теперішня/майбутня дія',
                                'note' => 'The work needs to be done now.',
                            ],
                            [
                                'en' => 'to have been + V3',
                                'ua' => 'Попередня дія (вже відбулася)',
                                'note' => 'He seems to have been promoted.',
                            ],
                        ],
                        'warning' => '📌 Перфектна форма завжди вказує на дію, що передувала іншій або моменту мовлення!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - Структура
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Структура та формула',
                        'intro' => 'Як утворюються пасивні інфінітиви:',
                        'items' => [
                            [
                                'label' => 'Простий пасивний',
                                'title' => 'to be + V3',
                                'subtitle' => 'Verb: need/want/expect/seem + to be done',
                            ],
                            [
                                'label' => 'Перфектний пасивний',
                                'title' => 'to have been + V3',
                                'subtitle' => 'Verb: seem/appear/claim + to have been done',
                            ],
                            [
                                'label' => 'З модальними',
                                'title' => 'Modal + be + V3',
                                'subtitle' => 'must/should/can/may + be done',
                            ],
                            [
                                'label' => 'Перфектний з модальними',
                                'title' => 'Modal + have been + V3',
                                'subtitle' => 'should/could/might + have been done',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Типові дієслова
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Дієслова, після яких вживається пасивний інфінітив',
                        'sections' => [
                            [
                                'label' => 'Необхідність та бажання',
                                'color' => 'emerald',
                                'description' => '<strong>need, want, expect, require, demand</strong>:',
                                'examples' => [
                                    ['en' => 'The issue needs to be addressed immediately.', 'ua' => 'Питання потрібно вирішити негайно.'],
                                    ['en' => 'All participants are expected to be registered.', 'ua' => 'Очікується, що всі учасники зареєструються.'],
                                ],
                            ],
                            [
                                'label' => 'Здогадки та враження',
                                'color' => 'blue',
                                'description' => '<strong>seem, appear, happen, turn out</strong>:',
                                'examples' => [
                                    ['en' => 'The car seems to be damaged.', 'ua' => 'Здається, машина пошкоджена.'],
                                    ['en' => 'The truth turned out to be hidden.', 'ua' => 'Виявилося, що правду приховали.'],
                                ],
                            ],
                            [
                                'label' => 'Твердження',
                                'color' => 'amber',
                                'description' => '<strong>claim, pretend, allege</strong>:',
                                'examples' => [
                                    ['en' => 'He claims to have been mistreated.', 'ua' => 'Він стверджує, що з ним погано поводилися.'],
                                    ['en' => 'She pretends to be interested.', 'ua' => 'Вона вдає, що їй цікаво.'],
                                ],
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
                                'title' => 'Пропуск "be" у простій формі.',
                                'wrong' => 'The work needs to done.',
                                'right' => '✅ The work needs to be done.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок у перфектній формі.',
                                'wrong' => 'He seems to been have promoted.',
                                'right' => '✅ He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'The issue needs to be solve.',
                                'right' => '✅ The issue needs to be solved.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Плутанина форм часу.',
                                'wrong' => 'He seems to be promoted yesterday.',
                                'right' => '✅ He seems to have been promoted yesterday.',
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
                        'title' => '8. Ключові правила',
                        'items' => [
                            '<strong>to be + V3</strong> — простий пасивний інфінітив для теперішніх/майбутніх дій.',
                            '<strong>to have been + V3</strong> — перфектний пасивний інфінітив для попередніх дій.',
                            'Використовується після <strong>need, want, expect, seem, appear, claim</strong>.',
                            'З модальними: <strong>must/should/can + be + V3</strong>.',
                            'Перфектна форма з модальними: <strong>should/could/might + have been + V3</strong>.',
                            'Типові для <strong>формального та академічного</strong> стилю.',
                            'У reporting structures: <strong>is said/believed/reported + to have been + V3</strong>.',
                            'Завжди використовуй <strong>V3 (Past Participle)</strong>, не V1!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
