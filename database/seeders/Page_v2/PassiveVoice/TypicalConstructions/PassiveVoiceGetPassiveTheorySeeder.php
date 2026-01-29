<?php

namespace Database\Seeders\Page_v2\PassiveVoice\TypicalConstructions;

class PassiveVoiceGetPassiveTheorySeeder extends PassiveVoiceTypicalConstructionsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-get-passive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Get-passive — розмовний пасив',
            'subtitle_html' => '<p><strong>Get-passive</strong> — розмовний варіант пасивного стану з <strong>get + V3</strong> замість be + V3. Використовується для <strong>несподіваних подій, змін стану</strong>, часто з негативним або емоційним відтінком: get married, get fired, get hurt, get lost.</p>',
            'subtitle_text' => 'Get-passive: get + V3 — розмовний пасив для несподіваних подій та змін стану. Get married, get fired, get hurt.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-typical-constructions',
                'title' => 'Типові конструкції й "фішки"',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Get Passive',
                'get married',
                'get fired',
                'get hurt',
                'Informal',
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
                        'intro' => 'У цій темі ти вивчиш <strong>get-passive</strong> — розмовний варіант пасивного стану. Він підкреслює <strong>зміну стану, подію чи несподіванку</strong>, і часто має емоційний відтінок.',
                        'rules' => [
                            [
                                'label' => 'Be Passive',
                                'color' => 'blue',
                                'text' => '<strong>be + V3</strong> — нейтральний, формальний:',
                                'example' => 'He was fired last week.',
                            ],
                            [
                                'label' => 'Get Passive',
                                'color' => 'emerald',
                                'text' => '<strong>get + V3</strong> — розмовний, динамічний:',
                                'example' => 'He got fired last week.',
                            ],
                            [
                                'label' => 'Відтінок',
                                'color' => 'rose',
                                'text' => 'Get-passive = подія, зміна, несподіванка:',
                                'example' => 'They got married in June.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Структура Get-passive',
                        'intro' => 'Формула: <strong>get + Past Participle (V3)</strong>',
                        'items' => [
                            [
                                'label' => 'Формула',
                                'title' => 'get + V3',
                                'subtitle' => 'get замість be у пасиві',
                            ],
                            [
                                'label' => 'Часи',
                                'title' => 'get змінюється за часами',
                                'subtitle' => 'get/gets/got/will get/getting + V3',
                            ],
                            [
                                'label' => 'Стиль',
                                'title' => 'Розмовний, неформальний',
                                'subtitle' => 'Частіше в spoken English',
                            ],
                            [
                                'label' => 'Відтінок',
                                'title' => 'Подія, зміна стану',
                                'subtitle' => 'Щось трапилося (часто несподівано)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Коли використовувати Get-passive?',
                        'sections' => [
                            [
                                'label' => 'Несподівані події',
                                'color' => 'emerald',
                                'description' => 'Коли щось <strong>трапилося несподівано</strong>, випадково.',
                                'examples' => [
                                    ['en' => 'He got hit by a car.', 'ua' => 'Його збила машина.'],
                                    ['en' => 'She got caught in the rain.', 'ua' => 'Вона потрапила під дощ.'],
                                    ['en' => 'I got stuck in traffic.', 'ua' => 'Я застряг у заторі.'],
                                ],
                            ],
                            [
                                'label' => 'Негативні події',
                                'color' => 'rose',
                                'description' => 'Часто для <strong>негативних, неприємних</strong> ситуацій.',
                                'examples' => [
                                    ['en' => 'He got fired from his job.', 'ua' => 'Його звільнили з роботи.'],
                                    ['en' => 'She got hurt in the accident.', 'ua' => 'Вона постраждала в аварії.'],
                                    ['en' => 'My phone got stolen.', 'ua' => 'Мій телефон вкрали.'],
                                ],
                            ],
                            [
                                'label' => 'Зміна стану',
                                'color' => 'sky',
                                'description' => 'Підкреслює <strong>перехід з одного стану в інший</strong>.',
                                'examples' => [
                                    ['en' => 'They got married last year.', 'ua' => 'Вони одружилися минулого року.'],
                                    ['en' => 'He got promoted to manager.', 'ua' => 'Його підвищили до менеджера.'],
                                    ['en' => 'The window got broken.', 'ua' => 'Вікно розбилося.'],
                                ],
                            ],
                            [
                                'label' => 'Позитивні події',
                                'color' => 'amber',
                                'description' => 'Іноді для <strong>позитивних змін</strong> — але рідше.',
                                'examples' => [
                                    ['en' => 'She got accepted to university.', 'ua' => 'Її прийняли до університету.'],
                                    ['en' => 'He got selected for the team.', 'ua' => 'Його відібрали в команду.'],
                                    ['en' => 'I got invited to the party.', 'ua' => 'Мене запросили на вечірку.'],
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
                        'title' => '3. Типові вирази з Get-passive',
                        'sections' => [
                            [
                                'label' => 'Особисте життя',
                                'color' => 'emerald',
                                'description' => 'Вирази про <strong>особисті події та стосунки</strong>.',
                                'examples' => [
                                    ['en' => 'get married', 'ua' => 'одружитися'],
                                    ['en' => 'get divorced', 'ua' => 'розлучитися'],
                                    ['en' => 'get engaged', 'ua' => 'заручитися'],
                                ],
                            ],
                            [
                                'label' => 'Робота',
                                'color' => 'blue',
                                'description' => 'Вирази про <strong>роботу та кар\'єру</strong>.',
                                'examples' => [
                                    ['en' => 'get hired', 'ua' => 'бути найнятим'],
                                    ['en' => 'get fired', 'ua' => 'бути звільненим'],
                                    ['en' => 'get promoted', 'ua' => 'отримати підвищення'],
                                    ['en' => 'get paid', 'ua' => 'отримати зарплату'],
                                ],
                            ],
                            [
                                'label' => 'Неприємності',
                                'color' => 'rose',
                                'description' => 'Вирази про <strong>проблеми та неприємності</strong>.',
                                'examples' => [
                                    ['en' => 'get hurt', 'ua' => 'постраждати'],
                                    ['en' => 'get injured', 'ua' => 'отримати травму'],
                                    ['en' => 'get lost', 'ua' => 'заблукати'],
                                    ['en' => 'get stuck', 'ua' => 'застрягти'],
                                ],
                            ],
                            [
                                'label' => 'Інші ситуації',
                                'color' => 'amber',
                                'description' => 'Інші <strong>типові вирази</strong> з get-passive.',
                                'examples' => [
                                    ['en' => 'get caught', 'ua' => 'бути спійманим'],
                                    ['en' => 'get arrested', 'ua' => 'бути заарештованим'],
                                    ['en' => 'get killed', 'ua' => 'бути вбитим'],
                                    ['en' => 'get dressed', 'ua' => 'одягнутися'],
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
                        'title' => '4. Get-passive у різних часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => '<strong>get/gets + V3</strong> — регулярні дії, звички.',
                                'examples' => [
                                    ['en' => 'He often gets invited to parties.', 'ua' => 'Його часто запрошують на вечірки.'],
                                    ['en' => 'People get hurt in accidents every day.', 'ua' => 'Люди отримують травми в аваріях щодня.'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => '<strong>got + V3</strong> — конкретні події в минулому.',
                                'examples' => [
                                    ['en' => 'She got promoted last month.', 'ua' => 'Її підвищили минулого місяця.'],
                                    ['en' => 'They got married in 2020.', 'ua' => 'Вони одружилися в 2020 році.'],
                                ],
                            ],
                            [
                                'label' => 'Future',
                                'color' => 'sky',
                                'description' => '<strong>will get + V3</strong> / <strong>be going to get + V3</strong>.',
                                'examples' => [
                                    ['en' => 'She will get paid tomorrow.', 'ua' => 'Їй заплатять завтра.'],
                                    ['en' => 'He is going to get fired if he is late again.', 'ua' => 'Його звільнять, якщо він знову запізниться.'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'amber',
                                'description' => '<strong>have/has got + V3</strong> — результат до теперішнього.',
                                'examples' => [
                                    ['en' => 'She has got accepted to Harvard!', 'ua' => 'Її прийняли до Гарварду!'],
                                    ['en' => 'I have got stuck in this traffic for an hour.', 'ua' => 'Я застряг у цьому заторі на годину.'],
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
                        'title' => '5. Be Passive vs Get Passive',
                        'intro' => 'Порівняння стандартного пасиву з get-passive:',
                        'rows' => [
                            [
                                'en' => 'be + V3',
                                'ua' => 'Нейтральний, формальний',
                                'note' => 'The report was completed.',
                            ],
                            [
                                'en' => 'get + V3',
                                'ua' => 'Розмовний, неформальний',
                                'note' => 'The report got completed.',
                            ],
                            [
                                'en' => 'be + V3',
                                'ua' => 'Стан або результат',
                                'note' => 'The door is closed. (стан зараз)',
                            ],
                            [
                                'en' => 'get + V3',
                                'ua' => 'Процес, зміна',
                                'note' => 'The door got closed. (дія, подія)',
                            ],
                            [
                                'en' => 'be + V3',
                                'ua' => 'Будь-які контексти',
                                'note' => 'Підходить завжди.',
                            ],
                            [
                                'en' => 'get + V3',
                                'ua' => 'Часто несподівані події',
                                'note' => 'Часто негативні, несподівані.',
                            ],
                        ],
                        'warning' => '📌 <strong>Get-passive</strong> частіше для несподіваних, негативних або динамічних подій!',
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
                                'title' => 'Використання get-passive у формальному контексті.',
                                'wrong' => 'The contract got signed by the CEO. (formal)',
                                'right' => '✅ The contract was signed by the CEO.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина get + adjective vs get + V3.',
                                'wrong' => 'I got interesting. (замість interested)',
                                'right' => '✅ I got interested in the topic.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання be замість get для зміни стану.',
                                'wrong' => 'They were married last June. (стан)',
                                'right' => '✅ They got married last June. (подія)',
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
                            '<strong>Get-passive</strong>: get + V3 — розмовний варіант пасиву.',
                            'Використовуй для <strong>несподіваних подій, змін стану</strong>, часто з негативним відтінком.',
                            'Типові вирази: <strong>get married, get fired, get hurt, get lost, get stuck</strong>.',
                            '<strong>Get змінюється за часами</strong>: get/gets/got/will get/have got + V3.',
                            '<strong>Be passive</strong> — нейтральний, <strong>get passive</strong> — динамічний, розмовний.',
                            'Уникай get-passive у формальному письмі та документах.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}