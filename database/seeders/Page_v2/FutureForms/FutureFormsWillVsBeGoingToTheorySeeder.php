<?php

namespace Database\Seeders\Page_v2\FutureForms;

use Database\Seeders\Pages\FutureForms\FutureFormsPageSeeder;

class FutureFormsWillVsBeGoingToTheorySeeder extends FutureFormsPageSeeder
{
    protected function slug(): string
    {
        return 'will-vs-be-going-to';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Will vs Be Going To — яка різниця?',
            'subtitle_html' => '<p><strong>Will</strong> і <strong>be going to</strong> обидва говорять про майбутнє, але не означають одне й те саме. Найчастіше <strong>will</strong> використовують для рішень у момент мовлення, обіцянок і прогнозів на основі думки, а <strong>be going to</strong> — для вже запланованих дій та прогнозів на основі того, що ми бачимо зараз.</p>',
            'subtitle_text' => 'Різниця між will і be going to: спонтанні рішення, плани, прогнози, обіцянки, видимі докази та типові помилки.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'maibutni-formy',
                'title' => 'Майбутні форми',
                'language' => 'uk',
            ],
            'tags' => [
                'Future Forms',
                'Will vs Be Going To',
                'Will',
                'Be Going To',
                'Future Simple',
                'Plans and Intentions',
                'Predictions',
                'Spontaneous Decisions',
                'Promises and Offers',
                'Grammar',
                'Theory',
                'A2',
                'B1',
            ],
            'base_tags' => [
                'Future Forms',
                'Will vs Be Going To',
                'Will',
                'Be Going To',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'tags' => ['Future Forms', 'Will', 'Be Going To', 'A2'],
                    'body' => json_encode([
                        'level' => 'A2–B1',
                        'intro' => 'У цій темі ти навчишся <strong>вибирати між will та be going to</strong> залежно від того, чи це спонтанне рішення, уже готовий план, думка або прогноз із видимим доказом.',
                        'rules' => [
                            [
                                'label' => 'WILL',
                                'color' => 'blue',
                                'text' => '<strong>Рішення зараз, обіцянка, пропозиція, думка</strong>:',
                                'example' => 'I\'ll get some milk. / I think they will win.',
                            ],
                            [
                                'label' => 'BE GOING TO',
                                'color' => 'emerald',
                                'text' => '<strong>План до моменту мовлення або видимий доказ</strong>:',
                                'example' => 'We\'re going to visit Lisbon. / Look at those clouds!',
                            ],
                            [
                                'label' => 'KEY QUESTION',
                                'color' => 'amber',
                                'text' => 'Запитай себе: це <strong>рішення зараз</strong>, <strong>готовий намір</strong> чи <strong>висновок із того, що видно</strong>?',
                                'example' => 'now / already planned / evidence',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'tags' => ['Will', 'Be Going To', 'Comparison', 'Future Simple'],
                    'body' => json_encode([
                        'title' => '1. Швидке порівняння',
                        'intro' => 'Ось основна різниця між цими двома формами:',
                        'rows' => [
                            [
                                'en' => 'Will + V1',
                                'ua' => 'рішення в момент мовлення',
                                'note' => 'We\'re out of milk. — I\'ll buy some.',
                            ],
                            [
                                'en' => 'Be going to + V1',
                                'ua' => 'план або намір, що вже існував раніше',
                                'note' => 'I\'m going to buy some after work.',
                            ],
                            [
                                'en' => 'Will + V1',
                                'ua' => 'думка, нейтральний прогноз',
                                'note' => 'I think she will come.',
                            ],
                            [
                                'en' => 'Be going to + V1',
                                'ua' => 'передбачення на основі доказу',
                                'note' => 'Look at those clouds. It\'s going to rain.',
                            ],
                            [
                                'en' => 'Will + V1',
                                'ua' => 'обіцянки, пропозиції, прохання',
                                'note' => 'I\'ll help you. / Will you open the window?',
                            ],
                        ],
                        'warning' => '💡 Обидві форми говорять про майбутнє, але передають різну логіку мовця.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'tags' => ['Will', 'Be Going To', 'Formulas', 'Negatives', 'Questions'],
                    'body' => json_encode([
                        'title' => '2. Формули',
                        'intro' => 'Порівняй основні структури ствердження, заперечення та питання:',
                        'items' => [
                            [
                                'label' => 'Will',
                                'title' => 'Affirmative',
                                'subtitle' => 'Subject + will + V1: I will call you.',
                            ],
                            [
                                'label' => 'Won\'t',
                                'title' => 'Negative',
                                'subtitle' => 'Subject + will not + V1: She won\'t be late.',
                            ],
                            [
                                'label' => 'Will ...?',
                                'title' => 'Question',
                                'subtitle' => 'Will + subject + V1? Will you come?',
                            ],
                            [
                                'label' => 'Going to',
                                'title' => 'Affirmative',
                                'subtitle' => 'Subject + am/is/are going to + V1: We are going to move.',
                            ],
                            [
                                'label' => 'Not going to',
                                'title' => 'Negative',
                                'subtitle' => 'Subject + am not/isn\'t/aren\'t going to + V1.',
                            ],
                            [
                                'label' => 'Are ... going to?',
                                'title' => 'Question',
                                'subtitle' => 'Am/Is/Are + subject + going to + V1?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'tags' => ['Will', 'Spontaneous Decisions', 'Predictions', 'Promises and Offers'],
                    'body' => json_encode([
                        'title' => '3. Коли вживати will',
                        'sections' => [
                            [
                                'label' => 'Спонтанне рішення',
                                'color' => 'blue',
                                'description' => 'Якщо рішення народжується <strong>прямо під час розмови</strong>, природно вживаємо <strong>will</strong>.',
                                'examples' => [
                                    ['en' => 'The phone is ringing. I\'ll answer it.', 'ua' => 'Телефон дзвонить. Я відповім.'],
                                    ['en' => 'You look tired. I\'ll make some tea.', 'ua' => 'Ти виглядаєш втомленим. Я зроблю чай.'],
                                    ['en' => 'We have no bread. I\'ll go to the shop.', 'ua' => 'У нас немає хліба. Я сходжу в магазин.'],
                                ],
                            ],
                            [
                                'label' => 'Думка або припущення',
                                'color' => 'emerald',
                                'description' => 'Для <strong>думки, прогнозу чи припущення</strong> без прямого доказу часто використовуємо <strong>will</strong>.',
                                'examples' => [
                                    ['en' => 'I think the Conservatives will win.', 'ua' => 'Я думаю, що консерватори виграють.'],
                                    ['en' => 'I don\'t think she will come.', 'ua' => 'Я не думаю, що вона прийде.'],
                                    ['en' => 'You\'ll love this movie.', 'ua' => 'Тобі сподобається цей фільм.'],
                                ],
                            ],
                            [
                                'label' => 'Обіцянки, пропозиції, прохання',
                                'color' => 'amber',
                                'description' => 'У коротких <strong>обіцянках, пропозиціях допомоги, проханнях або відмовах</strong> теж типово вживаємо <strong>will</strong>.',
                                'examples' => [
                                    ['en' => 'I\'ll help you tomorrow.', 'ua' => 'Я допоможу тобі завтра.'],
                                    ['en' => 'Will you close the door, please?', 'ua' => 'Зачиниш двері, будь ласка?'],
                                    ['en' => 'I won\'t tell anyone.', 'ua' => 'Я нікому не скажу.'],
                                ],
                                'note' => '📌 Також <strong>will</strong> часто вживаємо для майбутніх фактів: <em>The sun will rise tomorrow.</em>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'tags' => ['Be Going To', 'Plans and Intentions', 'Predictions', 'Evidence'],
                    'body' => json_encode([
                        'title' => '4. Коли вживати be going to',
                        'sections' => [
                            [
                                'label' => 'План або намір',
                                'color' => 'emerald',
                                'description' => 'Якщо рішення було прийняте <strong>до моменту мовлення</strong>, зазвичай обираємо <strong>be going to</strong>.',
                                'examples' => [
                                    ['en' => 'We\'re going to visit Lisbon in July.', 'ua' => 'Ми збираємося відвідати Лісабон у липні.'],
                                    ['en' => 'I\'m going to study at a different university next year.', 'ua' => 'Наступного року я збираюся навчатися в іншому університеті.'],
                                    ['en' => 'She\'s going to stay in tonight.', 'ua' => 'Сьогодні ввечері вона збирається залишитися вдома.'],
                                ],
                            ],
                            [
                                'label' => 'Передбачення з доказом',
                                'color' => 'blue',
                                'description' => 'Коли ми <strong>бачимо ознаку або доказ зараз</strong>, природніше вжити <strong>be going to</strong>.',
                                'examples' => [
                                    ['en' => 'Look at those clouds. It\'s going to rain.', 'ua' => 'Подивись на ті хмари. Зараз піде дощ.'],
                                    ['en' => 'He\'s driving too fast. He\'s going to have an accident.', 'ua' => 'Він їде надто швидко. У нього буде аварія.'],
                                    ['en' => 'The glass is near the edge. It\'s going to fall.', 'ua' => 'Склянка стоїть надто близько до краю. Вона зараз впаде.'],
                                ],
                            ],
                            [
                                'label' => 'Питання про плани',
                                'color' => 'amber',
                                'description' => 'Коли хочемо дізнатися про <strong>чийсь намір або організований план</strong>, часто питаємо через <strong>be going to</strong>.',
                                'examples' => [
                                    ['en' => 'What are you going to do after the lesson?', 'ua' => 'Що ти збираєшся робити після уроку?'],
                                    ['en' => 'Are you going to drive to work today?', 'ua' => 'Ти збираєшся сьогодні їхати на роботу машиною?'],
                                    ['en' => 'Where are they going to stay?', 'ua' => 'Де вони збираються зупинитися?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'level' => 'A2',
                    'tags' => ['Will vs Be Going To', 'Comparison', 'Meaning Contrast'],
                    'body' => json_encode([
                        'title' => '5. Паралельні приклади',
                        'intro' => 'Схожі ситуації, але різний зміст форми:',
                        'rows' => [
                            [
                                'en' => 'There\'s no milk. — I\'ll buy some.',
                                'ua' => 'Рішення з\'явилося зараз.',
                                'note' => 'spontaneous reaction',
                            ],
                            [
                                'en' => 'I know. I\'m going to buy some later.',
                                'ua' => 'План уже існував до розмови.',
                                'note' => 'prior intention',
                            ],
                            [
                                'en' => 'I think they will win.',
                                'ua' => 'Це думка мовця.',
                                'note' => 'prediction based on opinion',
                            ],
                            [
                                'en' => 'They are going to win. They already have most of the votes.',
                                'ua' => 'Є доказ або сильна ознака зараз.',
                                'note' => 'prediction based on evidence',
                            ],
                        ],
                        'warning' => '💡 Та сама майбутня подія може описуватися по-різному, якщо змінюється причина вибору форми.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'right',
                    'level' => 'A2',
                    'tags' => ['Common Mistakes', 'Will', 'Be Going To'],
                    'body' => json_encode([
                        'title' => '6. Типові помилки',
                        'items' => [
                            'Не кажемо <strong>will to buy</strong>. Після <strong>will</strong> потрібна <strong>базова форма без to</strong>: <em>will buy</em>.',
                            'Не пропускаємо дієслово <strong>be</strong> у конструкції <strong>going to</strong>: правильно <em>I am going to go</em>, а не <em>I going to go</em>.',
                            'Не плутай <strong>думку</strong> та <strong>видимий доказ</strong>: <em>I think it will rain</em> vs <em>Look at the sky. It\'s going to rain</em>.',
                            'Для пропозицій допомоги та коротких обіцянок частіше беремо <strong>will</strong>, а не <strong>be going to</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'right',
                    'level' => 'A2',
                    'tags' => ['Decision Guide', 'Will', 'Be Going To'],
                    'body' => json_encode([
                        'title' => '7. Короткий алгоритм вибору',
                        'items' => [
                            'Рішення виникло <strong>прямо зараз</strong> → обирай <strong>will</strong>.',
                            'План або намір був <strong>ще до моменту мовлення</strong> → обирай <strong>be going to</strong>.',
                            'Це <strong>обіцянка, пропозиція, прохання</strong> → зазвичай <strong>will</strong>.',
                            'Це <strong>думка, припущення, прогноз без доказу</strong> → частіше <strong>will</strong>.',
                            'Є <strong>видимий доказ або ситуація перед очима</strong> → частіше <strong>be going to</strong>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
