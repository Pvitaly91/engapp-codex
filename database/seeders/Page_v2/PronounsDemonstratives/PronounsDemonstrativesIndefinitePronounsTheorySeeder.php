<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Page_v2\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsDemonstrativesIndefinitePronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'indefinite-pronouns-someone-anyone-nobody-nothing';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Indefinite Pronouns — someone, anyone, nobody, nothing',
            'subtitle_html' => "<p><strong>Indefinite pronouns</strong> (неозначені займенники) — це слова, що вказують на невизначених людей, речі або місця. Основні групи: <strong>some-</strong> (хтось, щось), <strong>any-</strong> (будь-хто, будь-що), <strong>no-</strong> (ніхто, ніщо), <strong>every-</strong> (кожен, усе).</p>",
            'subtitle_text' => "Теоретичний огляд неозначених займенників англійської мови: someone/somebody, anyone/anybody, no one/nobody, everyone/everybody, something, anything, nothing, everything, somewhere, anywhere, nowhere, everywhere.",
            'locale' => 'uk',
            'category' => [
                'slug' => 'zaimennyky-ta-vkazivni-slova',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Indefinite Pronouns',
                'Someone',
                'Anyone',
                'Nobody',
                'Nothing',
                'Somebody',
                'Anybody',
                'Something',
                'Anything',
                'Everyone',
                'Everything',
                'Somewhere',
                'Anywhere',
                'Nowhere',
                'Pronouns',
                'Grammar',
                'Theory',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => "У цій темі ти вивчиш <strong>неозначені займенники</strong> — слова для позначення невизначених людей, речей і місць.",
                        'rules' => [
                            [
                                'label' => 'SOME-',
                                'color' => 'emerald',
                                'text' => '<strong>Some-</strong> у ствердженнях:',
                                'example' => 'Someone called. Something happened.',
                            ],
                            [
                                'label' => 'ANY-',
                                'color' => 'blue',
                                'text' => '<strong>Any-</strong> у питаннях і запереченнях:',
                                'example' => 'Did anyone see it? I didn\'t see anything.',
                            ],
                            [
                                'label' => 'NO-',
                                'color' => 'rose',
                                'text' => '<strong>No-</strong> для заперечення:',
                                'example' => 'Nobody knows. Nothing is wrong.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Чотири групи неозначених займенників',
                        'intro' => "Неозначені займенники поділяються на чотири основні групи:",
                        'items' => [
                            ['label' => 'SOME-', 'title' => 'Хтось, щось', 'subtitle' => 'someone/somebody, something, somewhere — у ствердженнях'],
                            ['label' => 'ANY-', 'title' => 'Будь-хто, будь-що', 'subtitle' => 'anyone/anybody, anything, anywhere — у питаннях і запереченнях'],
                            ['label' => 'NO-', 'title' => 'Ніхто, ніщо', 'subtitle' => 'no one/nobody, nothing, nowhere — для заперечення'],
                            ['label' => 'EVERY-', 'title' => 'Кожен, усе', 'subtitle' => 'everyone/everybody, everything, everywhere — усі без винятку'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Повна таблиця неозначених займенників',
                        'intro' => "Три категорії: люди (-one/-body), речі (-thing), місця (-where):",
                        'rows' => [
                            [
                                'en' => 'SOME-',
                                'ua' => 'хтось, щось, десь',
                                'note' => 'someone/somebody, something, somewhere',
                            ],
                            [
                                'en' => 'ANY-',
                                'ua' => 'будь-хто, будь-що, будь-де',
                                'note' => 'anyone/anybody, anything, anywhere',
                            ],
                            [
                                'en' => 'NO-',
                                'ua' => 'ніхто, ніщо, ніде',
                                'note' => 'no one/nobody, nothing, nowhere',
                            ],
                            [
                                'en' => 'EVERY-',
                                'ua' => 'кожен, усе, всюди',
                                'note' => 'everyone/everybody, everything, everywhere',
                            ],
                        ],
                        'warning' => "📌 -one/-body (люди), -thing (речі), -where (місця).",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. SOME- (someone, something, somewhere)',
                        'sections' => [
                            [
                                'label' => 'У ствердженнях',
                                'color' => 'emerald',
                                'description' => "Займенники з <strong>some-</strong> використовуються в <strong>стверджувальних реченнях</strong>.",
                                'examples' => [
                                    ['en' => 'Someone is at the door.', 'ua' => 'Хтось біля дверей.'],
                                    ['en' => 'I saw somebody in the garden.', 'ua' => 'Я бачив когось у саду.'],
                                    ['en' => 'Something is wrong.', 'ua' => 'Щось не так.'],
                                    ['en' => 'I left my keys somewhere.', 'ua' => 'Я залишив ключі десь.'],
                                ],
                            ],
                            [
                                'label' => 'У питаннях (пропозиція)',
                                'color' => 'sky',
                                'description' => "Some- також використовується в <strong>питаннях-пропозиціях</strong> або коли очікуємо ствердну відповідь.",
                                'examples' => [
                                    ['en' => 'Would you like something to drink?', 'ua' => 'Хочете щось випити?'],
                                    ['en' => 'Can someone help me?', 'ua' => 'Хтось може мені допомогти?'],
                                    ['en' => 'Did you go somewhere nice?', 'ua' => 'Ти ходив кудись гарно?'],
                                ],
                                'note' => '📌 У питаннях-пропозиціях використовуємо some-, не any-!',
                            ],
                            [
                                'label' => 'Someone vs Somebody',
                                'color' => 'purple',
                                'description' => "<strong>Someone</strong> і <strong>somebody</strong> — синоніми. Те саме з something.",
                                'examples' => [
                                    ['en' => 'Someone called = Somebody called', 'ua' => 'Хтось телефонував'],
                                    ['en' => 'I heard something = I heard a noise', 'ua' => 'Я почув щось = Я почув шум'],
                                ],
                                'note' => 'Someone/something — трохи формальніше. Somebody — розмовне.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. ANY- (anyone, anything, anywhere)',
                        'sections' => [
                            [
                                'label' => 'У питаннях',
                                'color' => 'blue',
                                'description' => "Займенники з <strong>any-</strong> використовуються в <strong>питаннях</strong>.",
                                'examples' => [
                                    ['en' => 'Did anyone call?', 'ua' => 'Хтось телефонував?'],
                                    ['en' => 'Is anybody home?', 'ua' => 'Хтось є вдома?'],
                                    ['en' => 'Did you see anything?', 'ua' => 'Ти щось бачив?'],
                                    ['en' => 'Are you going anywhere?', 'ua' => 'Ти кудись йдеш?'],
                                ],
                            ],
                            [
                                'label' => 'У запереченнях',
                                'color' => 'sky',
                                'description' => "Any- також використовується в <strong>запереченнях</strong> (з not).",
                                'examples' => [
                                    ['en' => "I didn't see anyone.", 'ua' => 'Я нікого не бачив.'],
                                    ['en' => "She doesn't know anybody.", 'ua' => 'Вона нікого не знає.'],
                                    ['en' => "I didn't buy anything.", 'ua' => 'Я нічого не купив.'],
                                    ['en' => "We didn't go anywhere.", 'ua' => 'Ми нікуди не йшли.'],
                                ],
                                'note' => '📌 Not + any- = no- (обидва варіанти правильні).',
                            ],
                            [
                                'label' => 'Будь-хто, будь-що (у ствердженнях)',
                                'color' => 'purple',
                                'description' => "У ствердженнях any- означає <strong>будь-хто, будь-що</strong> (without restriction).",
                                'examples' => [
                                    ['en' => 'Anyone can do it.', 'ua' => 'Будь-хто може це зробити.'],
                                    ['en' => 'You can take anything you want.', 'ua' => 'Можеш взяти будь-що, що хочеш.'],
                                    ['en' => 'We can go anywhere.', 'ua' => 'Ми можемо піти куди завгодно.'],
                                ],
                                'note' => 'Any- у ствердженнях = "будь-який", "без обмежень".',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. NO- (nobody, nothing, nowhere)',
                        'sections' => [
                            [
                                'label' => 'Для заперечення',
                                'color' => 'rose',
                                'description' => "Займенники з <strong>no-</strong> самі створюють <strong>заперечення</strong>. Не потребують not!",
                                'examples' => [
                                    ['en' => 'Nobody knows the answer.', 'ua' => 'Ніхто не знає відповіді.'],
                                    ['en' => 'No one came to the party.', 'ua' => 'Ніхто не прийшов на вечірку.'],
                                    ['en' => 'Nothing is impossible.', 'ua' => 'Ніщо не є неможливим.'],
                                    ['en' => "I've been nowhere interesting.", 'ua' => 'Я не був ніде цікавому.'],
                                ],
                                'note' => '📌 NO- вже заперечення! Не додавай not!',
                            ],
                            [
                                'label' => 'Nobody = Not anybody',
                                'color' => 'amber',
                                'description' => "No- і not + any- — синоніми. Обидва варіанти правильні.",
                                'examples' => [
                                    ['en' => 'Nobody called = No one called', 'ua' => 'Ніхто не телефонував'],
                                    ['en' => "I didn't see anyone = I saw nobody", 'ua' => 'Я нікого не бачив'],
                                    ['en' => "She didn't say anything = She said nothing", 'ua' => 'Вона нічого не сказала'],
                                ],
                                'note' => 'No- трохи формальніше і категоричніше.',
                            ],
                            [
                                'label' => 'Помилка: подвійне заперечення!',
                                'color' => 'red',
                                'description' => "В англійській НЕ можна використовувати подвійне заперечення!",
                                'examples' => [
                                    ['en' => "I didn't see nobody. (✗)", 'ua' => 'ПОМИЛКА — подвійне заперечення!'],
                                    ['en' => "I didn't see anyone. (✓)", 'ua' => 'Я нікого не бачив.'],
                                    ['en' => 'I saw nobody. (✓)', 'ua' => 'Я нікого не бачив.'],
                                ],
                                'note' => '📌 Not + no- = ПОМИЛКА! Вибери одне: not + any- АБО no-.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. EVERY- (everyone, everything, everywhere)',
                        'sections' => [
                            [
                                'label' => 'Усі без винятку',
                                'color' => 'purple',
                                'description' => "Займенники з <strong>every-</strong> означають <strong>усі, кожен без винятку</strong>.",
                                'examples' => [
                                    ['en' => 'Everyone is here.', 'ua' => 'Усі тут.'],
                                    ['en' => 'Everybody knows that.', 'ua' => 'Усі це знають.'],
                                    ['en' => 'Everything is fine.', 'ua' => 'Усе гаразд.'],
                                    ['en' => "I've looked everywhere.", 'ua' => 'Я шукав скрізь.'],
                                ],
                            ],
                            [
                                'label' => 'З дієсловом в однині!',
                                'color' => 'amber',
                                'description' => "Everyone/everybody — <strong>однина</strong>! Дієслово в формі однини.",
                                'examples' => [
                                    ['en' => 'Everyone is ready. (✓)', 'ua' => 'Усі готові.'],
                                    ['en' => 'Everyone are ready. (✗)', 'ua' => 'ПОМИЛКА — дієслово має бути is!'],
                                    ['en' => 'Everybody has a ticket.', 'ua' => 'У кожного є квиток.'],
                                ],
                                'note' => '📌 Everyone/everybody = однина (is, has, does).',
                            ],
                            [
                                'label' => 'Everyone vs Everybody',
                                'color' => 'sky',
                                'description' => "<strong>Everyone</strong> і <strong>everybody</strong> — синоніми.",
                                'examples' => [
                                    ['en' => 'Everyone is welcome = Everybody is welcome', 'ua' => 'Усі вітаються'],
                                ],
                                'note' => 'Everyone — трохи формальніше. Everybody — розмовне.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Граматичні правила',
                        'sections' => [
                            [
                                'label' => 'Однина чи множина?',
                                'color' => 'emerald',
                                'description' => "Усі неозначені займенники граматично в <strong>однині</strong>, навіть everyone/everybody.",
                                'examples' => [
                                    ['en' => 'Someone is calling. (✓)', 'ua' => 'Хтось телефонує.'],
                                    ['en' => 'Nobody knows. (✓)', 'ua' => 'Ніхто не знає.'],
                                    ['en' => 'Everyone has arrived. (✓)', 'ua' => 'Усі прибули.'],
                                ],
                                'note' => '📌 Завжди is, has, does (не are, have, do).',
                            ],
                            [
                                'label' => 'З прикметниками',
                                'color' => 'blue',
                                'description' => "Прикметники ставляться <strong>після</strong> неозначеного займенника.",
                                'examples' => [
                                    ['en' => 'something interesting (✓)', 'ua' => 'щось цікаве'],
                                    ['en' => 'interesting something (✗)', 'ua' => 'ПОМИЛКА'],
                                    ['en' => 'someone special', 'ua' => 'хтось особливий'],
                                    ['en' => 'nothing important', 'ua' => 'нічого важливого'],
                                ],
                                'note' => '📌 Indefinite pronoun + adjective (після, не перед).',
                            ],
                            [
                                'label' => 'Займенники для повернення',
                                'color' => 'purple',
                                'description' => "Для повернення на everyone/somebody використовуємо they/their (хоч граматично однина).",
                                'examples' => [
                                    ['en' => 'Someone left their bag. (✓)', 'ua' => 'Хтось залишив свою сумку.'],
                                    ['en' => 'Everyone should do their homework.', 'ua' => 'Кожен має робити своє домашнє завдання.'],
                                ],
                                'note' => '📌 Використовуємо they/their для нейтральності (не he/his).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => "Подвійне заперечення (not + no-).",
                                'wrong' => "I didn't see nobody. / She didn't say nothing.",
                                'right' => "✅ <span class=\"font-mono\">I didn't see anyone. / She said nothing.</span>",
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Дієслово у множині з everyone.",
                                'wrong' => 'Everyone are here. / Everybody have tickets.',
                                'right' => '✅ <span class="font-mono">Everyone is here. / Everybody has tickets.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Прикметник перед займенником.",
                                'wrong' => 'I want interesting something. / He met special someone.',
                                'right' => '✅ <span class="font-mono">I want something interesting. / He met someone special.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "Some- у питаннях (коли не пропозиція).",
                                'wrong' => 'Did someone call? (загальне питання)',
                                'right' => '✅ <span class="font-mono">Did anyone call?</span> (загальне) / <span class="font-mono">Did someone help you?</span> (очікуємо "так")',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            "<strong>SOME-</strong> (someone, something, somewhere) — у ствердженнях та питаннях-пропозиціях.",
                            "<strong>ANY-</strong> (anyone, anything, anywhere) — у питаннях, запереченнях (з not), або 'будь-хто' у ствердженнях.",
                            "<strong>NO-</strong> (nobody, nothing, nowhere) — для заперечення (без not!).",
                            "<strong>EVERY-</strong> (everyone, everything, everywhere) — усі без винятку.",
                            "Усі неозначені займенники — <strong>граматично в однині</strong> (is, has, does).",
                            "Прикметники стоять <strong>після</strong> займенника: something <em>interesting</em>.",
                            "Не можна: <strong>not + no-</strong> (подвійне заперечення). Вибери одне!",
                            "Someone/somebody, anyone/anybody — синоніми (-one формальніше).",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Практика',
                        'select_title' => 'Вправа 1. Обери правильний займенник',
                        'select_intro' => 'Заповни пропуски правильним неозначеним займенником.',
                        'selects' => [
                            ['label' => '___ is at the door. (Someone / Anyone)', 'prompt' => 'Який займенник?'],
                            ['label' => 'Did ___ call? (someone / anyone)', 'prompt' => 'Який займенник?'],
                            ['label' => '___ knows the answer. (Nobody / Anybody)', 'prompt' => 'Який займенник?'],
                            ['label' => '___ is ready. (Everyone / Anyone)', 'prompt' => 'Який займенник?'],
                            ['label' => "I didn't see ___. (anyone / someone)", 'prompt' => 'Який займенник?'],
                        ],
                        'options' => ['someone', 'anyone', 'nobody', 'everyone', 'something', 'anything', 'nothing', 'everything'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний неозначений займенник.',
                        'inputs' => [
                            ['before' => '___ called me. (хтось подзвонив)', 'after' => '→'],
                            ['before' => 'Did you see ___? (щось бачив)', 'after' => '→'],
                            ['before' => '___ came to the party. (ніхто не прийшов)', 'after' => '→'],
                            ['before' => '___ is possible! (усе можливо)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку з неозначеними займенниками.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => "I didn't see nobody.",
                                'example_target' => "I didn't see anyone.",
                            ],
                            [
                                'original' => '1. Everyone are ready.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. I want interesting something.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => "3. She didn't say nothing.",
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Займенники та вказівні слова',
                        'items' => [
                            [
                                'label' => 'Pronouns — Займенники (огляд)',
                                'current' => false,
                            ],
                            [
                                'label' => "Personal & Object Pronouns — Особові й об'єктні",
                                'current' => false,
                            ],
                            [
                                'label' => 'Possessive Adjectives vs Pronouns — my / mine',
                                'current' => false,
                            ],
                            [
                                'label' => 'Indefinite Pronouns — someone, anyone, nobody, nothing (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
