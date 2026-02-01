<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2CausativeTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-causative';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Каузатив (have/get something done)',
            'subtitle_html' => '<p><strong>Каузатив (Causative)</strong> — це конструкція <strong>have/get + object + V3</strong>, яка означає, що хтось інший виконує дію для нас або замість нас. Це важлива конструкція для опису послуг, які ми замовляємо.</p>',
            'subtitle_text' => 'Каузатив: have/get something done — коли хтось робить щось для нас. Формула, приклади, різниця між have і get.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Causative',
                'Каузатив',
                'have something done',
                'get something done',
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
                        'intro' => 'У цій темі ти вивчиш <strong>каузативну конструкцію</strong> — спосіб сказати, що хтось інший виконує дію для тебе (зазвичай за гроші або на твоє прохання).',
                        'rules' => [
                            [
                                'label' => 'Have',
                                'color' => 'emerald',
                                'text' => '<strong>have + object + V3</strong> (формальніше):',
                                'example' => 'I had my car repaired. — Мені відремонтували машину.',
                            ],
                            [
                                'label' => 'Get',
                                'color' => 'blue',
                                'text' => '<strong>get + object + V3</strong> (розмовніше):',
                                'example' => 'I got my hair cut. — Мене підстригли.',
                            ],
                            [
                                'label' => 'Значення',
                                'color' => 'amber',
                                'text' => 'Хтось <strong>інший</strong> виконує дію для тебе:',
                                'example' => 'I don\'t cut my hair myself — a hairdresser does it.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Causative vs Active vs Passive
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '1. Каузатив vs Актив vs Пасив',
                        'intro' => 'Порівняння трьох конструкцій:',
                        'rows' => [
                            [
                                'en' => 'Active: I repaired my car.',
                                'ua' => 'Я відремонтував свою машину.',
                                'note' => 'Я сам це зробив.',
                            ],
                            [
                                'en' => 'Passive: My car was repaired.',
                                'ua' => 'Мою машину відремонтували.',
                                'note' => 'Хтось це зробив (невідомо хто).',
                            ],
                            [
                                'en' => 'Causative: I had my car repaired.',
                                'ua' => 'Мені відремонтували машину.',
                                'note' => 'Я замовив послугу (хтось зробив для мене).',
                            ],
                        ],
                        'warning' => '📌 Каузатив підкреслює, що ми організували виконання дії, а не зробили її самі!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - have something done
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '2. Have something done у різних часах',
                        'intro' => 'Дієслово <strong>have</strong> змінюється за часами:',
                        'items' => [
                            [
                                'label' => 'Present Simple',
                                'title' => 'have/has + object + V3',
                                'subtitle' => 'I have my hair cut every month.',
                            ],
                            [
                                'label' => 'Past Simple',
                                'title' => 'had + object + V3',
                                'subtitle' => 'She had her car serviced yesterday.',
                            ],
                            [
                                'label' => 'Future Simple',
                                'title' => 'will have + object + V3',
                                'subtitle' => 'I will have my suit cleaned tomorrow.',
                            ],
                            [
                                'label' => 'Present Perfect',
                                'title' => 'have/has had + object + V3',
                                'subtitle' => 'We have had our house painted.',
                            ],
                            [
                                'label' => 'Present Continuous',
                                'title' => 'am/is/are having + object + V3',
                                'subtitle' => 'They are having their kitchen renovated.',
                            ],
                            [
                                'label' => 'Modal verbs',
                                'title' => 'modal + have + object + V3',
                                'subtitle' => 'You should have your eyes tested.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - типові ситуації
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '3. Типові ситуації для каузативу',
                        'sections' => [
                            [
                                'label' => 'Послуги / сервіс',
                                'color' => 'emerald',
                                'description' => 'Коли ми <strong>замовляємо послугу</strong>:',
                                'examples' => [
                                    ['en' => 'I had my hair cut yesterday.', 'ua' => 'Мене підстригли вчора.'],
                                    ['en' => 'She has her nails done every week.', 'ua' => 'Їй роблять манікюр щотижня.'],
                                    ['en' => 'We had the house painted last month.', 'ua' => 'Нам пофарбували будинок минулого місяця.'],
                                ],
                            ],
                            [
                                'label' => 'Ремонт / обслуговування',
                                'color' => 'blue',
                                'description' => 'Для <strong>ремонту та технічного обслуговування</strong>:',
                                'examples' => [
                                    ['en' => 'I need to have my car serviced.', 'ua' => 'Мені потрібно здати машину на ТО.'],
                                    ['en' => 'They had their roof repaired.', 'ua' => 'Їм відремонтували дах.'],
                                    ['en' => 'You should have your brakes checked.', 'ua' => 'Тобі варто перевірити гальма.'],
                                ],
                            ],
                            [
                                'label' => 'Медичні послуги',
                                'color' => 'amber',
                                'description' => 'Для <strong>медичних</strong> процедур:',
                                'examples' => [
                                    ['en' => 'I had my eyes tested last week.', 'ua' => 'Мені перевірили зір минулого тижня.'],
                                    ['en' => 'She needs to have her teeth cleaned.', 'ua' => 'Їй потрібно почистити зуби (у стоматолога).'],
                                    ['en' => 'He had his blood pressure checked.', 'ua' => 'Йому виміряли тиск.'],
                                ],
                            ],
                            [
                                'label' => 'Доставка / встановлення',
                                'color' => 'rose',
                                'description' => 'Для <strong>доставки та встановлення</strong>:',
                                'examples' => [
                                    ['en' => 'We had a new kitchen installed.', 'ua' => 'Нам встановили нову кухню.'],
                                    ['en' => 'They had the furniture delivered.', 'ua' => 'Їм доставили меблі.'],
                                    ['en' => 'I had the internet connected yesterday.', 'ua' => 'Мені вчора підключили інтернет.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - have vs get
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '4. Have something done vs Get something done',
                        'intro' => 'Обидві форми мають схоже значення, але є відмінності:',
                        'rows' => [
                            [
                                'en' => 'Have: I had my car repaired.',
                                'ua' => 'Мені відремонтували машину.',
                                'note' => 'Формальніше, нейтральне.',
                            ],
                            [
                                'en' => 'Get: I got my car repaired.',
                                'ua' => 'Мені відремонтували машину.',
                                'note' => 'Розмовніше, підкреслює зусилля.',
                            ],
                            [
                                'en' => 'Have: She had her dress made.',
                                'ua' => 'Їй пошили сукню.',
                                'note' => 'Стандартна послуга.',
                            ],
                            [
                                'en' => 'Get: She finally got her dress made.',
                                'ua' => 'Їй нарешті пошили сукню.',
                                'note' => 'Підкреслює, що це було непросто.',
                            ],
                        ],
                        'warning' => '📌 Get часто підкреслює, що вам довелося докласти зусиль, щоб щось було зроблено!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - негативний каузатив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Негативний каузатив (неприємні події)',
                        'sections' => [
                            [
                                'label' => 'Негативні події',
                                'color' => 'rose',
                                'description' => 'Каузатив також використовується для <strong>неприємних подій</strong>, що сталися з нами:',
                                'examples' => [
                                    ['en' => 'I had my wallet stolen.', 'ua' => 'У мене вкрали гаманець.'],
                                    ['en' => 'She had her car broken into.', 'ua' => 'Їй зламали машину.'],
                                    ['en' => 'They had their house burgled.', 'ua' => 'Їх пограбували (вдерлися в будинок).'],
                                ],
                            ],
                            [
                                'label' => 'Різниця в контексті',
                                'color' => 'amber',
                                'description' => 'Контекст визначає, чи це <strong>послуга</strong> чи <strong>неприємність</strong>:',
                                'examples' => [
                                    ['en' => 'I had my hair cut. (послуга)', 'ua' => 'Мене підстригли (у перукарні).'],
                                    ['en' => 'I had my bag stolen. (неприємність)', 'ua' => 'У мене вкрали сумку.'],
                                    ['en' => 'She had her phone repaired. (послуга)', 'ua' => 'Їй відремонтували телефон.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - заперечення та питання
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => '6. Заперечення та питання',
                        'intro' => 'Утворення заперечень та питань у каузативі:',
                        'items' => [
                            [
                                'label' => 'Заперечення (have)',
                                'title' => 'don\'t/doesn\'t/didn\'t have + obj + V3',
                                'subtitle' => 'I didn\'t have my car repaired.',
                            ],
                            [
                                'label' => 'Заперечення (get)',
                                'title' => 'don\'t/doesn\'t/didn\'t get + obj + V3',
                                'subtitle' => 'She didn\'t get her hair done.',
                            ],
                            [
                                'label' => 'Питання (have)',
                                'title' => 'Do/Does/Did + subj + have + obj + V3?',
                                'subtitle' => 'Did you have your car serviced?',
                            ],
                            [
                                'label' => 'Питання (get)',
                                'title' => 'Do/Does/Did + subj + get + obj + V3?',
                                'subtitle' => 'Did she get her dress made?',
                            ],
                            [
                                'label' => 'Wh-питання',
                                'title' => 'Wh + do/does/did + subj + have/get + obj + V3?',
                                'subtitle' => 'Where did you have your hair cut?',
                            ],
                            [
                                'label' => 'Future',
                                'title' => 'Will + subj + have/get + obj + V3?',
                                'subtitle' => 'Will you have the report finished?',
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
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Використання infinitive замість V3.',
                                'wrong' => 'I had my car to repair.',
                                'right' => '✅ I had my car repaired.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск object між have/get та V3.',
                                'wrong' => 'I had repaired my car.',
                                'right' => '✅ I had my car repaired.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутання каузативу з активним станом.',
                                'wrong' => 'I had cut my hair. (= я сам підстригся)',
                                'right' => '✅ I had my hair cut. (= мене підстригли)',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'I had repaired my car yesterday.',
                                'right' => '✅ I had my car repaired yesterday.',
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
                            'Каузатив: <strong>have/get + object + V3</strong> — хтось робить щось для нас.',
                            '<strong>Have something done</strong> — формальніше, нейтральне.',
                            '<strong>Get something done</strong> — розмовніше, підкреслює зусилля.',
                            'Типові ситуації: послуги, ремонт, медицина, доставка.',
                            'Негативний каузатив: <strong>I had my wallet stolen</strong> — неприємна подія.',
                            'Заперечення: <strong>didn\'t have/get + object + V3</strong>.',
                            'Питання: <strong>Did you have/get + object + V3?</strong>',
                            'Object завжди стоїть <strong>між have/get та V3</strong>!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
