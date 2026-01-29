<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

class PassiveVoiceReportingImpersonalTheorySeeder extends PassiveVoiceTypicalConstructionsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-reporting-impersonal';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Reporting / Impersonal Passive — безособовий пасив',
            'subtitle_html' => '<p><strong>Reporting Passive</strong> (безособовий пасив) — це формальні конструкції для передачі думок, повідомлень, чуток: <strong>It is said that...</strong>, <strong>He is believed to...</strong>. Використовується в новинах, науці, офіційних текстах.</p>',
            'subtitle_text' => 'Безособовий пасив: It is said that..., He is believed to... — формальні конструкції для повідомлень, думок, чуток.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-typical-constructions',
                'title' => 'Типові конструкції й "фішки"',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Impersonal Passive',
                'Reporting Passive',
                'It is said',
                'He is believed',
                'Formal English',
                'B2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2',
                        'intro' => 'У цій темі ти вивчиш <strong>безособовий/репортажний пасив</strong> — формальні конструкції для передачі думок, повідомлень, чуток: <strong>It is said that...</strong>, <strong>He is believed to...</strong>.',
                        'rules' => [
                            [
                                'label' => 'Impersonal',
                                'color' => 'emerald',
                                'text' => 'Безособова конструкція: <strong>It is said/believed that...</strong>',
                                'example' => 'It is said that he is very rich.',
                            ],
                            [
                                'label' => 'Personal',
                                'color' => 'blue',
                                'text' => 'Особова конструкція: <strong>He is said/believed to...</strong>',
                                'example' => 'He is said to be very rich.',
                            ],
                            [
                                'label' => 'Стиль',
                                'color' => 'rose',
                                'text' => 'Формальний стиль: новини, наука, офіційні тексти',
                                'example' => 'It is reported that the talks have failed.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Безособова конструкція: It is said/believed that...',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>It + is/was + V3 (reporting verb) + that + clause</strong>',
                                'examples' => [
                                    ['en' => 'Active: People say that he is very rich.', 'ua' => 'Люди кажуть, що він дуже багатий.'],
                                    ['en' => 'Passive: It is said that he is very rich.', 'ua' => 'Кажуть, що він дуже багатий.'],
                                ],
                            ],
                            [
                                'label' => 'It is believed',
                                'color' => 'sky',
                                'description' => 'Для передачі <strong>переконань, думок</strong>.',
                                'examples' => [
                                    ['en' => 'It is believed that the universe is expanding.', 'ua' => 'Вважається, що Всесвіт розширюється.'],
                                    ['en' => 'It was believed that the Earth was flat.', 'ua' => 'Вважалося, що Земля пласка.'],
                                ],
                            ],
                            [
                                'label' => 'It is reported',
                                'color' => 'blue',
                                'description' => 'Для передачі <strong>новин, повідомлень</strong>.',
                                'examples' => [
                                    ['en' => 'It is reported that the storm is approaching.', 'ua' => 'Повідомляється, що наближається шторм.'],
                                    ['en' => 'It was reported that the talks had failed.', 'ua' => 'Повідомлялося, що переговори провалилися.'],
                                ],
                            ],
                            [
                                'label' => 'It is known',
                                'color' => 'amber',
                                'description' => 'Для передачі <strong>загальновідомих фактів</strong>.',
                                'examples' => [
                                    ['en' => 'It is known that smoking causes cancer.', 'ua' => 'Відомо, що куріння спричиняє рак.'],
                                    ['en' => 'It is widely known that she is an expert.', 'ua' => 'Широко відомо, що вона експерт.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Особова конструкція: He is said/believed to...',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>Subject + is/was + V3 + to + infinitive</strong>',
                                'examples' => [
                                    ['en' => 'Active: People say that he is very rich.', 'ua' => 'Люди кажуть, що він дуже багатий.'],
                                    ['en' => 'Passive: He is said to be very rich.', 'ua' => 'Кажуть, що він дуже багатий.'],
                                ],
                            ],
                            [
                                'label' => 'Теперішні дії',
                                'color' => 'sky',
                                'description' => '<strong>to + infinitive</strong> — коли дія одночасна з моментом мовлення.',
                                'examples' => [
                                    ['en' => 'She is believed to live in Paris.', 'ua' => 'Вважається, що вона живе в Парижі.'],
                                    ['en' => 'He is thought to be the best candidate.', 'ua' => 'Вважається, що він найкращий кандидат.'],
                                ],
                            ],
                            [
                                'label' => 'Минулі дії',
                                'color' => 'blue',
                                'description' => '<strong>to have + V3</strong> — коли дія відбулася раніше.',
                                'examples' => [
                                    ['en' => 'She is believed to have left the country.', 'ua' => 'Вважається, що вона покинула країну.'],
                                    ['en' => 'He is said to have written many books.', 'ua' => 'Кажуть, що він написав багато книг.'],
                                ],
                            ],
                            [
                                'label' => 'Триваючі дії',
                                'color' => 'amber',
                                'description' => '<strong>to be + V-ing</strong> — коли дія триває.',
                                'examples' => [
                                    ['en' => 'They are reported to be negotiating.', 'ua' => 'Повідомляється, що вони ведуть переговори.'],
                                    ['en' => 'He is believed to be working on a new project.', 'ua' => 'Вважається, що він працює над новим проєктом.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Типові reporting verbs',
                        'sections' => [
                            [
                                'label' => 'say / tell',
                                'color' => 'emerald',
                                'description' => '<strong>say</strong> — для загальних висловлювань. <strong>tell</strong> — рідше в безособовому.',
                                'examples' => [
                                    ['en' => 'It is said that he resigned.', 'ua' => 'Кажуть, що він звільнився.'],
                                    ['en' => 'He is said to have resigned.', 'ua' => 'Кажуть, що він звільнився.'],
                                ],
                            ],
                            [
                                'label' => 'believe / think',
                                'color' => 'blue',
                                'description' => '<strong>believe, think</strong> — для думок та переконань.',
                                'examples' => [
                                    ['en' => 'It is believed that the plan will work.', 'ua' => 'Вважається, що план спрацює.'],
                                    ['en' => 'She is thought to be an expert.', 'ua' => 'Вважається, що вона експерт.'],
                                ],
                            ],
                            [
                                'label' => 'report / announce',
                                'color' => 'sky',
                                'description' => '<strong>report, announce</strong> — для офіційних повідомлень.',
                                'examples' => [
                                    ['en' => 'It was reported that 50 people were injured.', 'ua' => 'Повідомлялося, що 50 людей поранено.'],
                                    ['en' => 'The company is reported to be closing.', 'ua' => 'Повідомляється, що компанія закривається.'],
                                ],
                            ],
                            [
                                'label' => 'expect / suppose',
                                'color' => 'amber',
                                'description' => '<strong>expect, suppose, consider</strong> — для очікувань та припущень.',
                                'examples' => [
                                    ['en' => 'It is expected that prices will rise.', 'ua' => 'Очікується, що ціни зростуть.'],
                                    ['en' => 'She is supposed to arrive at 5.', 'ua' => 'Вона має прибути о 5.'],
                                    ['en' => 'He is considered to be a genius.', 'ua' => 'Його вважають генієм.'],
                                ],
                            ],
                            [
                                'label' => 'know / understand',
                                'color' => 'rose',
                                'description' => '<strong>know, understand</strong> — для фактів та розуміння.',
                                'examples' => [
                                    ['en' => 'It is known that water boils at 100°C.', 'ua' => 'Відомо, що вода кипить при 100°C.'],
                                    ['en' => 'He is understood to have accepted the offer.', 'ua' => 'Зрозуміло, що він прийняв пропозицію.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Безособовий vs Особовий пасив',
                        'intro' => 'Обидві конструкції передають те саме значення, але з різним фокусом:',
                        'rows' => [
                            [
                                'en' => 'It is said that he is rich.',
                                'ua' => 'Безособова (It-structure)',
                                'note' => 'Фокус на факті, нейтрально.',
                            ],
                            [
                                'en' => 'He is said to be rich.',
                                'ua' => 'Особова (Subject-structure)',
                                'note' => 'Фокус на особі, коротше.',
                            ],
                            [
                                'en' => 'It is believed that she left.',
                                'ua' => 'Безособова',
                                'note' => 'She is believed to have left.',
                            ],
                            [
                                'en' => 'It was reported that they won.',
                                'ua' => 'Безособова',
                                'note' => 'They were reported to have won.',
                            ],
                        ],
                        'warning' => '📌 Особова конструкція часто звучить <strong>більш природно</strong> в сучасній англійській.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Трансформація: Active → Passive',
                        'sections' => [
                            [
                                'label' => 'Крок 1',
                                'color' => 'emerald',
                                'description' => 'Визначте основне дієслово (say, believe, think...) та підрядне речення.',
                                'examples' => [
                                    ['en' => 'People say that he is a genius.', 'ua' => 'People = загальний підмет, say = дієслово, that... = clause'],
                                ],
                            ],
                            [
                                'label' => 'Крок 2: It-structure',
                                'color' => 'sky',
                                'description' => 'Зробіть <strong>It</strong> підметом + is/was + V3 + that-clause.',
                                'examples' => [
                                    ['en' => 'People say that he is a genius.', 'ua' => '→ It is said that he is a genius.'],
                                ],
                            ],
                            [
                                'label' => 'Крок 3: Subject-structure',
                                'color' => 'blue',
                                'description' => 'Зробіть підмет підрядного речення головним + is/was + V3 + to-infinitive.',
                                'examples' => [
                                    ['en' => 'People say that he is a genius.', 'ua' => '→ He is said to be a genius.'],
                                    ['en' => 'People believed that she had left.', 'ua' => '→ She was believed to have left.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
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
                                'title' => 'Пропуск "to" перед інфінітивом.',
                                'wrong' => 'He is said be rich.',
                                'right' => '✅ He is said to be rich.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина часів: минулі дії з простим інфінітивом.',
                                'wrong' => 'She is believed to leave yesterday.',
                                'right' => '✅ She is believed to have left yesterday.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання "that" у subject-structure.',
                                'wrong' => 'He is said that to be rich.',
                                'right' => '✅ He is said to be rich.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Пропуск "that" у It-structure.',
                                'wrong' => 'It is said he is rich.',
                                'right' => '✅ It is said that he is rich.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            '<strong>Безособовий пасив (It-structure)</strong>: It is said/believed/reported that + clause.',
                            '<strong>Особовий пасив (Subject-structure)</strong>: He is said/believed to + infinitive.',
                            'Для <strong>минулих дій</strong>: to have + V3 (She is believed to have left).',
                            'Для <strong>триваючих дій</strong>: to be + V-ing (They are reported to be negotiating).',
                            '<strong>Типові дієслова</strong>: say, believe, think, know, report, expect, suppose, consider.',
                            'Використовується в <strong>формальному стилі</strong>: новини, наука, офіційні тексти.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
