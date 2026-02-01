<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

class PassiveVoiceTwoObjectVerbsTheorySeeder extends PassiveVoiceTypicalConstructionsPageSeeder
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
            'title' => 'Two-object verbs (give/send/offer) у пасиві',
            'subtitle_html' => '<p><strong>Two-object verbs</strong> (дієслова з двома додатками) — give, send, offer, tell, show — мають <strong>непрямий додаток</strong> (кому) та <strong>прямий додаток</strong> (що). В англійській обидва можуть стати підметом пасивного речення: "He was given a book" або "A book was given to him".</p>',
            'subtitle_text' => 'Дієслова з двома додатками у пасиві: give, send, offer, tell, show. Два варіанти пасиву: "He was given…" vs "A book was given to him".',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-typical-constructions',
                'title' => 'Типові конструкції й "фішки"',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Two-object verbs',
                'Ditransitive verbs',
                'give',
                'send',
                'offer',
                'tell',
                'show',
                'B1',
                'B2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти навчишся утворювати <strong>пасив з дієсловами, що мають два додатки</strong>: give, send, offer, tell, show та інші. Дізнаєшся, який варіант природніший: "He was given a book" чи "A book was given to him".',
                        'rules' => [
                            [
                                'label' => 'Active',
                                'color' => 'emerald',
                                'text' => 'Активний стан з двома додатками:',
                                'example' => 'They gave him a book.',
                            ],
                            [
                                'label' => 'Passive 1',
                                'color' => 'blue',
                                'text' => 'Особа як підмет (частіше):',
                                'example' => 'He was given a book.',
                            ],
                            [
                                'label' => 'Passive 2',
                                'color' => 'rose',
                                'text' => 'Річ як підмет (рідше):',
                                'example' => 'A book was given to him.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Що таке two-object verbs?',
                        'intro' => 'Деякі дієслова мають два додатки — непрямий (indirect object) та прямий (direct object):',
                        'items' => [
                            [
                                'label' => 'Indirect Object',
                                'title' => 'Непрямий додаток',
                                'subtitle' => 'КОМУ? — зазвичай особа (him, her, me)',
                            ],
                            [
                                'label' => 'Direct Object',
                                'title' => 'Прямий додаток',
                                'subtitle' => 'ЩО? — зазвичай річ (a book, money)',
                            ],
                            [
                                'label' => 'Структура',
                                'title' => 'S + V + IO + DO',
                                'subtitle' => 'They gave him (IO) a book (DO).',
                            ],
                            [
                                'label' => 'Альтернатива',
                                'title' => 'S + V + DO + to/for + IO',
                                'subtitle' => 'They gave a book to him.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Два варіанти пасиву',
                        'sections' => [
                            [
                                'label' => 'Варіант 1: Особа — підмет',
                                'color' => 'emerald',
                                'description' => '<strong>Непрямий додаток</strong> (особа) стає підметом. Це <strong>частіший</strong> та природніший варіант.',
                                'examples' => [
                                    ['en' => 'Active: They gave him a book.', 'ua' => 'Вони дали йому книгу.'],
                                    ['en' => 'Passive: He was given a book.', 'ua' => 'Йому дали книгу. (фокус на "він")'],
                                    ['en' => 'Active: Someone sent her a letter.', 'ua' => 'Хтось надіслав їй листа.'],
                                    ['en' => 'Passive: She was sent a letter.', 'ua' => 'Їй надіслали листа.'],
                                ],
                            ],
                            [
                                'label' => 'Варіант 2: Річ — підмет',
                                'color' => 'sky',
                                'description' => '<strong>Прямий додаток</strong> (річ) стає підметом. Потрібен прийменник <strong>to/for</strong>.',
                                'examples' => [
                                    ['en' => 'Active: They gave him a book.', 'ua' => 'Вони дали йому книгу.'],
                                    ['en' => 'Passive: A book was given to him.', 'ua' => 'Книгу дали йому. (фокус на "книга")'],
                                    ['en' => 'Active: Someone sent her a letter.', 'ua' => 'Хтось надіслав їй листа.'],
                                    ['en' => 'Passive: A letter was sent to her.', 'ua' => 'Листа надіслали їй.'],
                                ],
                            ],
                            [
                                'label' => 'Який обрати?',
                                'color' => 'amber',
                                'description' => 'Варіант 1 (особа — підмет) <strong>природніший</strong> в англійській. Варіант 2 — коли акцент на речі.',
                                'examples' => [
                                    ['en' => 'She was offered a job. ✓ (звичайно)', 'ua' => 'Їй запропонували роботу.'],
                                    ['en' => 'A job was offered to her. (акцент на "роботу")', 'ua' => 'Роботу запропонували їй.'],
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
                        'title' => '3. Типові two-object verbs',
                        'sections' => [
                            [
                                'label' => 'give / send / bring',
                                'color' => 'emerald',
                                'description' => 'Дієслова <strong>передачі</strong>: give (давати), send (надсилати), bring (приносити).',
                                'examples' => [
                                    ['en' => 'He was given a warning.', 'ua' => 'Йому дали попередження.'],
                                    ['en' => 'She was sent the documents.', 'ua' => 'Їй надіслали документи.'],
                                    ['en' => 'They were brought refreshments.', 'ua' => 'Їм принесли напої.'],
                                ],
                            ],
                            [
                                'label' => 'offer / promise / lend',
                                'color' => 'blue',
                                'description' => 'Дієслова <strong>пропозиції</strong>: offer (пропонувати), promise (обіцяти), lend (позичати).',
                                'examples' => [
                                    ['en' => 'She was offered a promotion.', 'ua' => 'Їй запропонували підвищення.'],
                                    ['en' => 'He was promised a raise.', 'ua' => 'Йому обіцяли підвищення зарплати.'],
                                    ['en' => 'They were lent some money.', 'ua' => 'Їм позичили гроші.'],
                                ],
                            ],
                            [
                                'label' => 'tell / show / teach',
                                'color' => 'sky',
                                'description' => 'Дієслова <strong>інформації</strong>: tell (розповідати), show (показувати), teach (навчати).',
                                'examples' => [
                                    ['en' => 'He was told the truth.', 'ua' => 'Йому сказали правду.'],
                                    ['en' => 'She was shown the new system.', 'ua' => 'Їй показали нову систему.'],
                                    ['en' => 'We were taught English grammar.', 'ua' => 'Нас навчали англійської граматики.'],
                                ],
                            ],
                            [
                                'label' => 'pay / award / grant',
                                'color' => 'amber',
                                'description' => 'Дієслова <strong>винагороди</strong>: pay (платити), award (нагороджувати), grant (надавати).',
                                'examples' => [
                                    ['en' => 'She was paid a bonus.', 'ua' => 'Їй виплатили бонус.'],
                                    ['en' => 'He was awarded a medal.', 'ua' => 'Його нагородили медаллю.'],
                                    ['en' => 'They were granted permission.', 'ua' => 'Їм надали дозвіл.'],
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
                        'title' => '4. Трансформація Active → Passive',
                        'intro' => 'Як перетворити речення з двома додатками в пасив:',
                        'rows' => [
                            [
                                'en' => 'Active: They gave her a gift.',
                                'ua' => 'IO: her, DO: a gift',
                                'note' => 'Passive 1: She was given a gift. ✓',
                            ],
                            [
                                'en' => 'Active: They gave her a gift.',
                                'ua' => 'IO: her, DO: a gift',
                                'note' => 'Passive 2: A gift was given to her.',
                            ],
                            [
                                'en' => 'Active: Someone sent him a message.',
                                'ua' => 'IO: him, DO: a message',
                                'note' => 'Passive: He was sent a message. ✓',
                            ],
                            [
                                'en' => 'Active: They offered us help.',
                                'ua' => 'IO: us, DO: help',
                                'note' => 'Passive: We were offered help. ✓',
                            ],
                        ],
                        'warning' => '📌 В англійській <strong>особа як підмет</strong> звучить природніше!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Прийменник "to" vs "for"',
                        'sections' => [
                            [
                                'label' => 'to — передача',
                                'color' => 'emerald',
                                'description' => 'Використовуй <strong>to</strong> з дієсловами передачі (give, send, tell, show, offer).',
                                'examples' => [
                                    ['en' => 'A book was given to him.', 'ua' => 'Книгу дали йому.'],
                                    ['en' => 'The letter was sent to her.', 'ua' => 'Лист надіслали їй.'],
                                    ['en' => 'The news was told to everyone.', 'ua' => 'Новину розповіли всім.'],
                                ],
                            ],
                            [
                                'label' => 'for — на користь',
                                'color' => 'blue',
                                'description' => 'Використовуй <strong>for</strong> з дієсловами "на користь" (buy, make, cook, find, get).',
                                'examples' => [
                                    ['en' => 'A cake was made for her.', 'ua' => 'Торт спекли для неї.'],
                                    ['en' => 'A ticket was bought for him.', 'ua' => 'Квиток купили для нього.'],
                                    ['en' => 'A solution was found for them.', 'ua' => 'Рішення знайшли для них.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
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
                                'title' => 'Пропуск прийменника, коли річ — підмет.',
                                'wrong' => 'A book was given him.',
                                'right' => '✅ A book was given to him.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Зайвий прийменник, коли особа — підмет.',
                                'wrong' => 'He was given to a book.',
                                'right' => '✅ He was given a book.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутанина "to" та "for".',
                                'wrong' => 'A cake was made to her.',
                                'right' => '✅ A cake was made for her.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            '<strong>Two-object verbs</strong> мають IO (кому) та DO (що): give, send, offer, tell, show.',
                            '<strong>Два варіанти пасиву</strong>: особа як підмет (частіше) або річ як підмет.',
                            '<strong>He was given a book.</strong> — природніший варіант (особа — підмет).',
                            '<strong>A book was given to him.</strong> — коли акцент на речі.',
                            'Прийменник <strong>to</strong> — для передачі, <strong>for</strong> — "на користь".',
                            'Не додавай прийменник, коли особа — підмет пасиву.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
