<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2GetPassiveTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-get-passive';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Get-пасив (get + V3)',
            'subtitle_html' => '<p><strong>Get-пасив (Get Passive)</strong> — це альтернативний спосіб утворення пасиву за формулою <strong>get + V3</strong> замість <strong>be + V3</strong>. Він часто використовується в розмовній мові та підкреслює процес або несподівану/негативну подію.</p>',
            'subtitle_text' => 'Get-пасив: формула get + V3, порівняння з be-пасивом, вживання, типові вирази та приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Get Passive',
                'Get-пасив',
                'get + V3',
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
                        'intro' => 'У цій темі ти вивчиш <strong>get-пасив</strong> — альтернативу звичайному be-пасиву. Get-пасив часто підкреслює <strong>несподіваність</strong>, <strong>процес</strong> або <strong>негативний результат</strong> події.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + get + V3 (Past Participle)</strong>:',
                                'example' => 'He got injured in the accident.',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => '<strong>Subject + don\'t/doesn\'t/didn\'t get + V3</strong>:',
                                'example' => 'She didn\'t get invited to the party.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => '<strong>Do/Does/Did + Subject + get + V3?</strong>:',
                                'example' => 'Did he get promoted?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Be-passive vs Get-passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Be-пасив vs Get-пасив',
                        'intro' => 'Порівняння двох форм пасиву:',
                        'rows' => [
                            [
                                'en' => 'Be-passive: The window was broken.',
                                'ua' => 'Вікно було розбите.',
                                'note' => 'Нейтральне, формальне.',
                            ],
                            [
                                'en' => 'Get-passive: The window got broken.',
                                'ua' => 'Вікно розбилося.',
                                'note' => 'Підкреслює процес, неформальне.',
                            ],
                            [
                                'en' => 'Be-passive: He was injured.',
                                'ua' => 'Він був поранений.',
                                'note' => 'Констатація факту.',
                            ],
                            [
                                'en' => 'Get-passive: He got injured.',
                                'ua' => 'Він поранився.',
                                'note' => 'Підкреслює несподіваність.',
                            ],
                        ],
                        'warning' => '📌 Get-пасив підходить для розмовної мови; be-пасив — для формальних текстів!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли використовувати get-пасив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Коли використовувати get-пасив?',
                        'sections' => [
                            [
                                'label' => 'Несподівані / негативні події',
                                'color' => 'rose',
                                'description' => 'Коли подія є <strong>несподіваною</strong> або <strong>неприємною</strong>:',
                                'examples' => [
                                    ['en' => 'He got fired from his job.', 'ua' => 'Його звільнили з роботи.'],
                                    ['en' => 'My phone got stolen on the bus.', 'ua' => 'Мій телефон вкрали в автобусі.'],
                                    ['en' => 'She got hurt in the game.', 'ua' => 'Вона травмувалася під час гри.'],
                                ],
                            ],
                            [
                                'label' => 'Процес, зміна стану',
                                'color' => 'blue',
                                'description' => 'Коли підкреслюється <strong>процес</strong> або <strong>зміна стану</strong>:',
                                'examples' => [
                                    ['en' => 'We got stuck in traffic.', 'ua' => 'Ми застрягли в заторі.'],
                                    ['en' => 'The car got damaged in the storm.', 'ua' => 'Машина постраждала від бурі.'],
                                    ['en' => 'They got lost in the forest.', 'ua' => 'Вони загубилися в лісі.'],
                                ],
                            ],
                            [
                                'label' => 'Позитивні зміни',
                                'color' => 'emerald',
                                'description' => 'Також для <strong>позитивних змін</strong>:',
                                'examples' => [
                                    ['en' => 'He got promoted last month.', 'ua' => 'Його підвищили минулого місяця.'],
                                    ['en' => 'They got married in June.', 'ua' => 'Вони одружилися в червні.'],
                                    ['en' => 'She got accepted to university.', 'ua' => 'Її прийняли до університету.'],
                                ],
                            ],
                            [
                                'label' => 'Розмовна мова',
                                'color' => 'amber',
                                'description' => 'У <strong>неформальному</strong> спілкуванні:',
                                'examples' => [
                                    ['en' => 'Did you get invited to the party?', 'ua' => 'Тебе запросили на вечірку?'],
                                    ['en' => 'I always get confused by these rules.', 'ua' => 'Я завжди плутаюся в цих правилах.'],
                                    ['en' => 'Don\'t get stressed about it.', 'ua' => 'Не стресуй через це.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - утворення в різних часах
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Get-пасив у різних часах',
                        'intro' => 'Дієслово <strong>get</strong> змінюється за часами:',
                        'items' => [
                            [
                                'label' => 'Present Simple',
                                'title' => 'get/gets + V3',
                                'subtitle' => 'He always gets invited to parties.',
                            ],
                            [
                                'label' => 'Past Simple',
                                'title' => 'got + V3',
                                'subtitle' => 'She got promoted last year.',
                            ],
                            [
                                'label' => 'Future Simple',
                                'title' => 'will get + V3',
                                'subtitle' => 'You will get paid tomorrow.',
                            ],
                            [
                                'label' => 'Present Perfect',
                                'title' => 'has/have got + V3',
                                'subtitle' => 'He has got selected for the team.',
                            ],
                            [
                                'label' => 'Present Continuous',
                                'title' => 'am/is/are getting + V3',
                                'subtitle' => 'The house is getting renovated.',
                            ],
                            [
                                'label' => 'Past Continuous',
                                'title' => 'was/were getting + V3',
                                'subtitle' => 'The car was getting repaired.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - заперечення та питання
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Заперечення та питання в get-пасиві',
                        'sections' => [
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Використовуємо <strong>don\'t/doesn\'t/didn\'t + get</strong>:',
                                'examples' => [
                                    ['en' => "He didn't get promoted.", 'ua' => 'Його не підвищили.'],
                                    ['en' => "She doesn't get paid enough.", 'ua' => 'Їй платять недостатньо.'],
                                    ['en' => "They won't get invited.", 'ua' => 'Їх не запросять.'],
                                ],
                            ],
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'blue',
                                'description' => 'Використовуємо <strong>Do/Does/Did + get</strong>:',
                                'examples' => [
                                    ['en' => 'Did you get paid?', 'ua' => 'Тобі заплатили?'],
                                    ['en' => 'Does she get invited to meetings?', 'ua' => 'Її запрошують на зустрічі?'],
                                    ['en' => 'Will they get selected?', 'ua' => 'Їх оберуть?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'amber',
                                'description' => '<strong>Wh-word + do/does/did + Subject + get + V3?</strong>:',
                                'examples' => [
                                    ['en' => 'When did you get hired?', 'ua' => 'Коли тебе найняли?'],
                                    ['en' => 'Why did they get fired?', 'ua' => 'Чому їх звільнили?'],
                                    ['en' => 'How often do you get paid?', 'ua' => 'Як часто тобі платять?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - типові вирази з get-пасивом
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '5. Типові вирази з get-пасивом',
                        'intro' => 'Найпоширеніші вирази з get + V3:',
                        'items' => [
                            [
                                'label' => 'get married',
                                'title' => 'одружитися',
                                'subtitle' => 'They got married last summer.',
                            ],
                            [
                                'label' => 'get divorced',
                                'title' => 'розлучитися',
                                'subtitle' => 'They got divorced after 10 years.',
                            ],
                            [
                                'label' => 'get lost',
                                'title' => 'загубитися',
                                'subtitle' => 'We got lost in the city.',
                            ],
                            [
                                'label' => 'get stuck',
                                'title' => 'застрягти',
                                'subtitle' => 'The car got stuck in the mud.',
                            ],
                            [
                                'label' => 'get hurt / injured',
                                'title' => 'поранитися',
                                'subtitle' => 'She got hurt playing football.',
                            ],
                            [
                                'label' => 'get fired',
                                'title' => 'бути звільненим',
                                'subtitle' => 'He got fired for being late.',
                            ],
                            [
                                'label' => 'get paid',
                                'title' => 'отримати оплату',
                                'subtitle' => 'I get paid on Fridays.',
                            ],
                            [
                                'label' => 'get invited',
                                'title' => 'бути запрошеним',
                                'subtitle' => 'She got invited to the wedding.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - формальність
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Формальність: be-пасив vs get-пасив',
                        'intro' => 'Вибір між be та get залежить від контексту:',
                        'rows' => [
                            [
                                'en' => 'Formal: The documents were signed.',
                                'ua' => 'Документи були підписані.',
                                'note' => 'Офіційний стиль — be-пасив.',
                            ],
                            [
                                'en' => 'Informal: The documents got signed.',
                                'ua' => 'Документи підписали.',
                                'note' => 'Розмовний стиль — get-пасив.',
                            ],
                            [
                                'en' => 'Academic: The experiment was conducted.',
                                'ua' => 'Експеримент був проведений.',
                                'note' => 'Науковий стиль — be-пасив.',
                            ],
                            [
                                'en' => 'Everyday: The work got done quickly.',
                                'ua' => 'Роботу швидко зробили.',
                                'note' => 'Повсякденне мовлення — get-пасив.',
                            ],
                        ],
                        'warning' => '📌 Уникайте get-пасиву в академічних та офіційних текстах!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання get замість be у формальних текстах.',
                                'wrong' => 'The report got submitted to the committee.',
                                'right' => '✅ The report was submitted to the committee.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання got без V3.',
                                'wrong' => 'He got fire yesterday.',
                                'right' => '✅ He got fired yesterday.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутання get + V3 з get + прикметник.',
                                'wrong' => 'She got boring.',
                                'right' => '✅ She got bored. (V3 від bore)',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильне утворення питання.',
                                'wrong' => 'Got he promoted?',
                                'right' => '✅ Did he get promoted?',
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
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Get-пасив: <strong>get + V3</strong> (альтернатива be + V3).',
                            'Get-пасив типовий для <strong>розмовної мови</strong>.',
                            'Підкреслює <strong>несподіваність</strong>, <strong>процес</strong> або <strong>негативну подію</strong>.',
                            'Заперечення: <strong>don\'t/doesn\'t/didn\'t + get + V3</strong>.',
                            'Питання: <strong>Do/Does/Did + Subject + get + V3?</strong>',
                            'Типові вирази: <strong>get married, get lost, get hurt, get fired, get paid</strong>.',
                            'У формальних текстах використовуйте <strong>be-пасив</strong>!',
                            'Get змінюється за часами: <strong>get/gets/got/will get/has got</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
