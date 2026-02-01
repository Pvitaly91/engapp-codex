<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

class PassiveVoiceByPhraseTheorySeeder extends PassiveVoiceTypicalConstructionsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-by-phrase';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'By-phrase: коли додавати "by …", а коли ні',
            'subtitle_html' => '<p><strong>By-phrase</strong> — вказує на виконавця дії в пасивному реченні. Але не завжди "by" потрібен: часто виконавець очевидний, невідомий або неважливий. У цій темі ти навчишся правильно використовувати "by" та уникати типових помилок.</p>',
            'subtitle_text' => 'Коли вживати by + agent у пасиві, коли опускати, різниця між by та with, типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-typical-constructions',
                'title' => 'Типові конструкції й "фішки"',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'By-phrase',
                'Agent',
                'by vs with',
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
                        'intro' => 'У цій темі ти дізнаєшся, <strong>коли додавати "by + agent"</strong> у пасивних реченнях, коли його краще опустити, та яка різниця між <strong>by</strong> та <strong>with</strong>.',
                        'rules' => [
                            [
                                'label' => 'Додавай by',
                                'color' => 'emerald',
                                'text' => 'Коли виконавець <strong>важливий або новий</strong>:',
                                'example' => 'The book was written by J.K. Rowling.',
                            ],
                            [
                                'label' => 'Опускай by',
                                'color' => 'blue',
                                'text' => 'Коли виконавець <strong>очевидний або невідомий</strong>:',
                                'example' => 'He was arrested. (поліцією — очевидно)',
                            ],
                            [
                                'label' => 'by vs with',
                                'color' => 'rose',
                                'text' => '<strong>by</strong> — виконавець, <strong>with</strong> — інструмент:',
                                'example' => 'The letter was written by Tom with a pen.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Коли ДОДАВАТИ "by + agent"',
                        'sections' => [
                            [
                                'label' => 'Виконавець важливий',
                                'color' => 'emerald',
                                'description' => 'Якщо виконавець — <strong>ключова інформація</strong>, його потрібно вказати.',
                                'examples' => [
                                    ['en' => 'The Mona Lisa was painted by Leonardo da Vinci.', 'ua' => '«Мона Ліза» була намальована Леонардо да Вінчі.'],
                                    ['en' => 'This song was performed by Adele.', 'ua' => 'Ця пісня була виконана Адель.'],
                                    ['en' => 'The company was founded by Steve Jobs.', 'ua' => 'Компанію заснував Стів Джобс.'],
                                ],
                            ],
                            [
                                'label' => 'Нова інформація',
                                'color' => 'sky',
                                'description' => 'Коли виконавець — <strong>нова, несподівана інформація</strong> для слухача.',
                                'examples' => [
                                    ['en' => 'I was helped by a stranger.', 'ua' => 'Мені допоміг незнайомець.'],
                                    ['en' => 'The window was broken by the kids next door.', 'ua' => 'Вікно розбили сусідські діти.'],
                                    ['en' => 'The project was completed by our junior team.', 'ua' => 'Проєкт завершила наша молодша команда.'],
                                ],
                            ],
                            [
                                'label' => 'Контраст / акцент',
                                'color' => 'amber',
                                'description' => 'Коли хочемо <strong>підкреслити, хто саме</strong> виконав дію.',
                                'examples' => [
                                    ['en' => 'The report was written by me, not by Tom.', 'ua' => 'Звіт написав я, а не Том.'],
                                    ['en' => 'The cake was made by my grandmother.', 'ua' => 'Торт спекла моя бабуся. (акцент)'],
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
                        'title' => '2. Коли ОПУСКАТИ "by + agent"',
                        'sections' => [
                            [
                                'label' => 'Виконавець очевидний',
                                'color' => 'emerald',
                                'description' => 'Коли з контексту <strong>зрозуміло</strong>, хто виконує дію.',
                                'examples' => [
                                    ['en' => 'He was arrested.', 'ua' => 'Його заарештували. (поліція — очевидно)'],
                                    ['en' => 'The patient was operated on.', 'ua' => 'Пацієнта прооперували. (лікарі — очевидно)'],
                                    ['en' => 'The letter was delivered.', 'ua' => 'Листа доставили. (пошта — очевидно)'],
                                ],
                            ],
                            [
                                'label' => 'Виконавець невідомий',
                                'color' => 'sky',
                                'description' => 'Ми <strong>не знаємо</strong>, хто виконав дію.',
                                'examples' => [
                                    ['en' => 'My car was stolen.', 'ua' => 'Мою машину вкрали. (хто — невідомо)'],
                                    ['en' => 'The building was constructed in 1920.', 'ua' => 'Будівлю зведено в 1920 році.'],
                                    ['en' => 'The window was broken last night.', 'ua' => 'Вікно розбили вчора ввечері.'],
                                ],
                            ],
                            [
                                'label' => 'Виконавець неважливий',
                                'color' => 'amber',
                                'description' => 'Нам <strong>байдуже</strong>, хто виконує дію. Важливий результат.',
                                'examples' => [
                                    ['en' => 'English is spoken here.', 'ua' => 'Тут розмовляють англійською.'],
                                    ['en' => 'The rules must be followed.', 'ua' => 'Правила треба дотримуватися.'],
                                    ['en' => 'The work will be finished tomorrow.', 'ua' => 'Робота буде закінчена завтра.'],
                                ],
                            ],
                            [
                                'label' => 'Загальний виконавець',
                                'color' => 'rose',
                                'description' => 'Коли виконавець — <strong>люди загалом</strong>: people, someone, they.',
                                'examples' => [
                                    ['en' => 'It is believed that... (people believe)', 'ua' => 'Вважається, що... (люди вважають)'],
                                    ['en' => 'The meeting was cancelled.', 'ua' => 'Зустріч скасували. (хтось)'],
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
                        'title' => '3. "by" vs "with" — виконавець vs інструмент',
                        'sections' => [
                            [
                                'label' => 'by — виконавець',
                                'color' => 'emerald',
                                'description' => '<strong>by</strong> вказує на <strong>того, хто виконує</strong> дію (людина, організація, сила).',
                                'examples' => [
                                    ['en' => 'The letter was written by Tom.', 'ua' => 'Лист написав Том. (виконавець)'],
                                    ['en' => 'The house was destroyed by the earthquake.', 'ua' => 'Будинок зруйнувало землетрусом. (сила)'],
                                    ['en' => 'The decision was made by the committee.', 'ua' => 'Рішення ухвалив комітет. (організація)'],
                                ],
                            ],
                            [
                                'label' => 'with — інструмент',
                                'color' => 'blue',
                                'description' => '<strong>with</strong> вказує на <strong>інструмент або засіб</strong>, яким виконано дію.',
                                'examples' => [
                                    ['en' => 'The letter was written with a pen.', 'ua' => 'Лист написано ручкою. (інструмент)'],
                                    ['en' => 'The cake was decorated with cream.', 'ua' => 'Торт прикрашено кремом. (матеріал)'],
                                    ['en' => 'The door was locked with a key.', 'ua' => 'Двері замкнуто ключем.'],
                                ],
                            ],
                            [
                                'label' => 'Поєднання by + with',
                                'color' => 'amber',
                                'description' => 'Можна використати <strong>обидва</strong> в одному реченні.',
                                'examples' => [
                                    ['en' => 'The picture was painted by Monet with oil paints.', 'ua' => 'Картину намалював Моне олійними фарбами.'],
                                    ['en' => 'The document was signed by the manager with a fountain pen.', 'ua' => 'Документ підписав менеджер перовою ручкою.'],
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
                        'title' => '4. Порівняння: by vs with',
                        'intro' => 'Ключова різниця між by та with у пасивних реченнях:',
                        'rows' => [
                            [
                                'en' => 'by + agent',
                                'ua' => 'Виконавець дії',
                                'note' => 'The book was written by J.K. Rowling.',
                            ],
                            [
                                'en' => 'with + instrument',
                                'ua' => 'Інструмент, засіб',
                                'note' => 'The book was written with a pencil.',
                            ],
                            [
                                'en' => 'by + natural force',
                                'ua' => 'Природна сила',
                                'note' => 'The tree was struck by lightning.',
                            ],
                            [
                                'en' => 'with + material',
                                'ua' => 'Матеріал',
                                'note' => 'The house was built with bricks.',
                            ],
                        ],
                        'warning' => '📌 <strong>by</strong> = хто/що спричинило дію. <strong>with</strong> = чим виконано дію.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Зайвий "by" з очевидним виконавцем.',
                                'wrong' => 'He was arrested by police.',
                                'right' => '✅ He was arrested. (поліція очевидна)',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина by та with.',
                                'wrong' => 'The letter was written with Tom.',
                                'right' => '✅ The letter was written by Tom.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання by замість with для інструменту.',
                                'wrong' => 'The cake was cut by a knife.',
                                'right' => '✅ The cake was cut with a knife.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Додавання "by someone/people" без потреби.',
                                'wrong' => 'English is spoken by people here.',
                                'right' => '✅ English is spoken here.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Короткий конспект',
                        'items' => [
                            '<strong>Додавай "by"</strong>: коли виконавець важливий, несподіваний, або для контрасту.',
                            '<strong>Опускай "by"</strong>: коли виконавець очевидний, невідомий, неважливий, або загальний (people, they).',
                            '<strong>by</strong> = виконавець дії (хто/що спричинило).',
                            '<strong>with</strong> = інструмент, засіб, матеріал (чим виконано).',
                            'Можна поєднувати: "painted <strong>by</strong> Monet <strong>with</strong> oil paints".',
                            'Уникай зайвого "by" з очевидними виконавцями.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
