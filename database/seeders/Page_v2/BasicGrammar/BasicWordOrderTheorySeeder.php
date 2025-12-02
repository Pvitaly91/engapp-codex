<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\BasicGrammar\BasicGrammarPageSeeder;

class BasicWordOrderTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-basic-word-order';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Basic Word Order — Порядок слів у ствердженні',
            'subtitle_html' => '<p>Англійська мова має <strong>фіксований порядок слів</strong>. Розуміння структури S–V–O та правильного розташування обставин — основа для побудови зрозумілих речень.</p>',
            'subtitle_text' => 'Порядок слів в англійській мові чіткий: підмет — дієслово — додаток. Обставини часу і місця мають свої позиції.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Sentence Structure',
                'S-V-O',
                'Adverbs of Frequency',
                'Time Adverbials',
                'Place Adverbials',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1',
                        'intro' => 'У цій темі ти навчишся правильно розташовувати слова в англійському реченні: <strong>підмет, дієслово, додаток</strong>, а також обставини часу, місця та частотності.',
                        'rules' => [
                            [
                                'label' => 'Основна структура',
                                'color' => 'emerald',
                                'text' => '<strong>S–V–O</strong> — підмет, дієслово, додаток:',
                                'example' => 'She reads books.',
                            ],
                            [
                                'label' => 'Прислівники частотності',
                                'color' => 'blue',
                                'text' => '<strong>Перед дієсловом</strong> (але після to be):',
                                'example' => 'He always eats breakfast. / She is usually happy.',
                            ],
                            [
                                'label' => 'Час і місце',
                                'color' => 'amber',
                                'text' => '<strong>Місце → Час</strong> у кінці речення:',
                                'example' => 'I work at the office every day.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Структура S–V–O',
                        'intro' => 'Базовий порядок слів у ствердженні:',
                        'items' => [
                            ['label' => 'S (Subject)', 'title' => 'Підмет', 'subtitle' => 'Хто виконує дію'],
                            ['label' => 'V (Verb)', 'title' => 'Дієслово', 'subtitle' => 'Що робить підмет'],
                            ['label' => 'O (Object)', 'title' => 'Додаток', 'subtitle' => 'На кого/що спрямована дія'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Приклади базового порядку',
                        'sections' => [
                            [
                                'label' => 'Просте речення',
                                'color' => 'emerald',
                                'description' => 'Підмет стоїть на початку, потім дієслово, потім додаток.',
                                'examples' => [
                                    ['en' => 'I like music.', 'ua' => 'Я люблю музику.'],
                                    ['en' => 'They play games.', 'ua' => 'Вони грають в ігри.'],
                                    ['en' => 'She writes letters.', 'ua' => 'Вона пише листи.'],
                                ],
                            ],
                            [
                                'label' => 'Без додатка',
                                'color' => 'sky',
                                'description' => 'Деякі дієслова не потребують додатка.',
                                'examples' => [
                                    ['en' => 'He sleeps.', 'ua' => 'Він спить.'],
                                    ['en' => 'She laughs.', 'ua' => 'Вона сміється.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Прислівники частотності',
                        'sections' => [
                            [
                                'label' => 'Перед основним дієсловом',
                                'color' => 'blue',
                                'description' => 'Прислівники <strong>always, usually, often, sometimes, rarely, never</strong> стоять перед основним дієсловом.',
                                'examples' => [
                                    ['en' => 'She always drinks coffee.', 'ua' => 'Вона завжди п\'є каву.'],
                                    ['en' => 'I often read books.', 'ua' => 'Я часто читаю книжки.'],
                                    ['en' => 'They never watch TV.', 'ua' => 'Вони ніколи не дивляться телевізор.'],
                                ],
                            ],
                            [
                                'label' => 'Після to be',
                                'color' => 'rose',
                                'description' => 'Коли головне дієслово — <strong>am/is/are/was/were</strong>, прислівник стоїть після нього.',
                                'examples' => [
                                    ['en' => 'He is usually late.', 'ua' => 'Він зазвичай запізнюється.'],
                                    ['en' => 'They are always happy.', 'ua' => 'Вони завжди щасливі.'],
                                    ['en' => 'I am never bored.', 'ua' => 'Мені ніколи не нудно.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Обставини часу',
                        'sections' => [
                            [
                                'label' => 'У кінці речення',
                                'color' => 'amber',
                                'description' => 'Найчастіше обставини часу стоять <strong>у кінці</strong>.',
                                'examples' => [
                                    ['en' => 'I go to work every day.', 'ua' => 'Я ходжу на роботу щодня.'],
                                    ['en' => 'We have dinner at 7 o\'clock.', 'ua' => 'Ми вечеряємо о 7 годині.'],
                                    ['en' => 'She called me yesterday.', 'ua' => 'Вона зателефонувала мені вчора.'],
                                ],
                            ],
                            [
                                'label' => 'На початку речення',
                                'color' => 'violet',
                                'description' => 'Для акценту обставину часу можна поставити <strong>на початок</strong>.',
                                'examples' => [
                                    ['en' => 'Yesterday, I met my friend.', 'ua' => 'Вчора я зустрів друга.'],
                                    ['en' => 'Every morning, he runs in the park.', 'ua' => 'Щоранку він бігає в парку.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Обставини місця',
                        'sections' => [
                            [
                                'label' => 'Після дієслова або додатка',
                                'color' => 'teal',
                                'description' => 'Обставини місця (<strong>at school, in the park, at home</strong>) стоять після дієслова або додатка.',
                                'examples' => [
                                    ['en' => 'She works at home.', 'ua' => 'Вона працює вдома.'],
                                    ['en' => 'They play football in the park.', 'ua' => 'Вони грають у футбол у парку.'],
                                    ['en' => 'I left my bag at school.', 'ua' => 'Я залишив сумку в школі.'],
                                ],
                            ],
                            [
                                'label' => 'Місце + Час',
                                'color' => 'orange',
                                'description' => 'Коли є і місце, і час — спочатку <strong>місце</strong>, потім <strong>час</strong>.',
                                'examples' => [
                                    ['en' => 'I study at the library every evening.', 'ua' => 'Я займаюся в бібліотеці щовечора.'],
                                    ['en' => 'We meet at the café on Fridays.', 'ua' => 'Ми зустрічаємося в кафе по п\'ятницях.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок підмета і дієслова',
                                'wrong' => 'Reads she books.',
                                'right' => '✅ <span class="font-mono">She reads books.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Прислівник частотності після дієслова',
                                'wrong' => 'She drinks always tea.',
                                'right' => '✅ <span class="font-mono">She always drinks tea.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Обставина часу між дієсловом і додатком',
                                'wrong' => 'I eat every day breakfast.',
                                'right' => '✅ <span class="font-mono">I eat breakfast every day.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            '<strong>S–V–O</strong> — базовий порядок: підмет, дієслово, додаток.',
                            '<strong>Прислівники частотності</strong> — перед основним дієсловом, після to be.',
                            '<strong>Обставини місця</strong> — після дієслова/додатка.',
                            '<strong>Обставини часу</strong> — на початку або в кінці речення.',
                            '<strong>Порядок:</strong> S + V + O + Place + Time.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Практика',
                        'select_title' => 'Вправа 1. Обери правильний порядок',
                        'select_intro' => 'Обери речення з правильним порядком слів.',
                        'selects' => [
                            ['label' => 'a) She always is happy. b) She is always happy.', 'prompt' => 'Яке речення правильне?'],
                            ['label' => 'a) I every day go to school. b) I go to school every day.', 'prompt' => 'Яке речення правильне?'],
                            ['label' => 'a) They football play in the park. b) They play football in the park.', 'prompt' => 'Яке речення правильне?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Розставте слова',
                        'input_intro' => 'Напиши речення у правильному порядку.',
                        'inputs' => [
                            ['before' => 'coffee / drinks / she / always', 'after' => ''],
                            ['before' => 'at home / works / he / every day', 'after' => ''],
                            ['before' => 'in the park / play / the children / often', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Виправ порядок слів у реченнях.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Reads she books.',
                                'example_target' => 'She reads books.',
                            ],
                            [
                                'original' => '1. Goes he to work every day.',
                                'placeholder' => 'Напиши правильний варіант',
                            ],
                            [
                                'original' => '2. I eat never fast food.',
                                'placeholder' => 'Напиши правильний варіант',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші теми базової граматики',
                        'items' => [
                            [
                                'label' => 'Basic Word Order (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
