<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceTwoObjectVerbsTheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-two-object-verbs';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасив двооб\'єктних дієслів',
            'subtitle_html' => '<p><strong>Двооб\'єктні дієслова (Ditransitive Verbs)</strong> — це дієслова, які мають два додатки: прямий та непрямий. У пасиві можна зробити підметом будь-який з цих додатків, що дає два варіанти пасивної конструкції.</p>',
            'subtitle_text' => 'Пасив двооб\'єктних дієслів: give, send, show, tell, offer, teach. Два варіанти пасиву: з прямим та непрямим додатком.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Ditransitive Verbs',
                'Two Object Verbs',
                'Двооб\'єктні дієслова',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш, як утворювати пасив з <strong>двооб\'єктними дієсловами</strong> — дієсловами, які мають два додатки: <strong>прямий</strong> (що?) та <strong>непрямий</strong> (кому?).',
                        'rules' => [
                            [
                                'label' => 'Активний',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + verb + indirect object + direct object</strong>:',
                                'example' => 'They gave me a present. (me = indirect, present = direct)',
                            ],
                            [
                                'label' => 'Пасив 1',
                                'color' => 'blue',
                                'text' => '<strong>Indirect object</strong> стає підметом:',
                                'example' => 'I was given a present. — Мені дали подарунок.',
                            ],
                            [
                                'label' => 'Пасив 2',
                                'color' => 'rose',
                                'text' => '<strong>Direct object</strong> стає підметом:',
                                'example' => 'A present was given to me. — Подарунок дали мені.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - двооб'єктні дієслова
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Типові двооб\'єктні дієслова',
                        'intro' => 'Дієслова, які можуть мати два додатки:',
                        'items' => [
                            [
                                'label' => 'give',
                                'title' => 'давати',
                                'subtitle' => 'She gave him a book. → He was given a book.',
                            ],
                            [
                                'label' => 'send',
                                'title' => 'надсилати',
                                'subtitle' => 'They sent her a letter. → She was sent a letter.',
                            ],
                            [
                                'label' => 'show',
                                'title' => 'показувати',
                                'subtitle' => 'He showed me the way. → I was shown the way.',
                            ],
                            [
                                'label' => 'tell',
                                'title' => 'розповідати',
                                'subtitle' => 'They told us the news. → We were told the news.',
                            ],
                            [
                                'label' => 'offer',
                                'title' => 'пропонувати',
                                'subtitle' => 'They offered him a job. → He was offered a job.',
                            ],
                            [
                                'label' => 'teach',
                                'title' => 'навчати',
                                'subtitle' => 'She taught me English. → I was taught English.',
                            ],
                            [
                                'label' => 'pay',
                                'title' => 'платити',
                                'subtitle' => 'They paid him $100. → He was paid $100.',
                            ],
                            [
                                'label' => 'lend',
                                'title' => 'позичати',
                                'subtitle' => 'She lent me money. → I was lent money.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - два варіанти пасиву
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Два варіанти пасиву',
                        'intro' => 'Кожне двооб\'єктне дієслово може мати два варіанти пасиву:',
                        'rows' => [
                            [
                                'en' => 'Active: They gave me a present.',
                                'ua' => 'Вони дали мені подарунок.',
                                'note' => 'me = indirect, present = direct',
                            ],
                            [
                                'en' => 'Passive 1: I was given a present.',
                                'ua' => 'Мені дали подарунок.',
                                'note' => 'Indirect object → Subject (поширеніше)',
                            ],
                            [
                                'en' => 'Passive 2: A present was given to me.',
                                'ua' => 'Подарунок дали мені.',
                                'note' => 'Direct object → Subject',
                            ],
                        ],
                        'warning' => '📌 Варіант 1 (indirect object → Subject) зазвичай звучить природніше в англійській!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - приклади з різними дієсловами
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Приклади з різними дієсловами',
                        'sections' => [
                            [
                                'label' => 'give / send',
                                'color' => 'emerald',
                                'description' => 'Дієслова <strong>передачі</strong>:',
                                'examples' => [
                                    ['en' => 'Active: They gave her flowers.', 'ua' => 'Вони подарували їй квіти.'],
                                    ['en' => 'Passive: She was given flowers.', 'ua' => 'Їй подарували квіти.'],
                                    ['en' => 'Active: He sent me an email.', 'ua' => 'Він надіслав мені листа.'],
                                    ['en' => 'Passive: I was sent an email.', 'ua' => 'Мені надіслали листа.'],
                                ],
                            ],
                            [
                                'label' => 'tell / show',
                                'color' => 'blue',
                                'description' => 'Дієслова <strong>інформування</strong>:',
                                'examples' => [
                                    ['en' => 'Active: Someone told him the truth.', 'ua' => 'Хтось сказав йому правду.'],
                                    ['en' => 'Passive: He was told the truth.', 'ua' => 'Йому сказали правду.'],
                                    ['en' => 'Active: They showed us the results.', 'ua' => 'Вони показали нам результати.'],
                                    ['en' => 'Passive: We were shown the results.', 'ua' => 'Нам показали результати.'],
                                ],
                            ],
                            [
                                'label' => 'offer / promise',
                                'color' => 'amber',
                                'description' => 'Дієслова <strong>пропозиції</strong>:',
                                'examples' => [
                                    ['en' => 'Active: They offered me a job.', 'ua' => 'Вони запропонували мені роботу.'],
                                    ['en' => 'Passive: I was offered a job.', 'ua' => 'Мені запропонували роботу.'],
                                    ['en' => 'Active: She promised him help.', 'ua' => 'Вона пообіцяла йому допомогу.'],
                                    ['en' => 'Passive: He was promised help.', 'ua' => 'Йому пообіцяли допомогу.'],
                                ],
                            ],
                            [
                                'label' => 'teach / ask',
                                'color' => 'rose',
                                'description' => 'Дієслова <strong>навчання та запиту</strong>:',
                                'examples' => [
                                    ['en' => 'Active: She taught me grammar.', 'ua' => 'Вона навчала мене граматики.'],
                                    ['en' => 'Passive: I was taught grammar.', 'ua' => 'Мене навчали граматики.'],
                                    ['en' => 'Active: They asked us questions.', 'ua' => 'Вони задавали нам питання.'],
                                    ['en' => 'Passive: We were asked questions.', 'ua' => 'Нам задавали питання.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - який варіант обрати
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Який варіант пасиву обрати?',
                        'sections' => [
                            [
                                'label' => 'Indirect object → Subject',
                                'color' => 'emerald',
                                'description' => 'Використовується <strong>частіше</strong>, звучить природніше:',
                                'examples' => [
                                    ['en' => 'I was given a book. (природно)', 'ua' => 'Мені дали книгу.'],
                                    ['en' => 'She was told the news. (природно)', 'ua' => 'Їй повідомили новину.'],
                                    ['en' => 'He was offered a promotion. (природно)', 'ua' => 'Йому запропонували підвищення.'],
                                ],
                            ],
                            [
                                'label' => 'Direct object → Subject',
                                'color' => 'blue',
                                'description' => 'Використовується, коли <strong>важливий предмет</strong>, а не людина:',
                                'examples' => [
                                    ['en' => 'The award was given to the winner.', 'ua' => 'Нагорода була вручена переможцю.'],
                                    ['en' => 'The message was sent to all employees.', 'ua' => 'Повідомлення надіслано всім працівникам.'],
                                    ['en' => 'A large sum was paid to the contractor.', 'ua' => 'Велику суму виплачено підряднику.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - дієслова з обмеженнями
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Дієслова з обмеженнями',
                        'intro' => 'Деякі дієслова мають лише один варіант пасиву:',
                        'items' => [
                            [
                                'label' => 'explain',
                                'title' => 'explain something to someone',
                                'subtitle' => 'The rule was explained to us. ✅ (не: We were explained...)',
                            ],
                            [
                                'label' => 'suggest',
                                'title' => 'suggest something to someone',
                                'subtitle' => 'A plan was suggested to them. ✅ (не: They were suggested...)',
                            ],
                            [
                                'label' => 'describe',
                                'title' => 'describe something to someone',
                                'subtitle' => 'The scene was described to the police. ✅',
                            ],
                            [
                                'label' => 'announce',
                                'title' => 'announce something to someone',
                                'subtitle' => 'The news was announced to the public. ✅',
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
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск to з direct object як підметом.',
                                'wrong' => 'A book was given me.',
                                'right' => '✅ A book was given to me.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання explain з indirect object як підметом.',
                                'wrong' => 'We were explained the rule.',
                                'right' => '✅ The rule was explained to us.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'Was given she a prize.',
                                'right' => '✅ She was given a prize.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Зайве by agent.',
                                'wrong' => 'I was told the news by someone.',
                                'right' => '✅ I was told the news. (якщо виконавець неважливий)',
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
                        'title' => '7. Короткий конспект',
                        'items' => [
                            'Двооб\'єктні дієслова мають <strong>прямий</strong> (що?) та <strong>непрямий</strong> (кому?) додатки.',
                            'Можна зробити підметом <strong>indirect object</strong>: I was given a book.',
                            'Або <strong>direct object</strong>: A book was given to me.',
                            'Варіант з <strong>indirect object</strong> як підметом звучить <strong>природніше</strong>.',
                            'Типові дієслова: <strong>give, send, show, tell, offer, teach, pay, lend</strong>.',
                            'Деякі дієслова (explain, suggest) мають <strong>лише один варіант</strong>.',
                            'Не забувай <strong>to</strong>, якщо direct object стає підметом: given to me.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
