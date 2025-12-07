<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use Database\Seeders\Pages\QuestionsNegations\QuestionsNegationsPageSeeder;

class TypesOfQuestionsAlternativeQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'alternative-questions-coffee-or-tea';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Alternative Questions — Альтернативні питання (coffee or tea?)',
            'subtitle_html' => '<p><strong>Alternative questions</strong> (альтернативні питання) — це питання, які пропонують вибір між двома або більше варіантами, зʼєднаними сполучником <strong>or</strong> (або). На відміну від Yes/No питань, тут потрібно обрати один із запропонованих варіантів.</p>',
            'subtitle_text' => 'Теоретичний огляд альтернативних питань в англійській мові: структура, правила формування та приклади використання питань з вибором варіантів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'types-of-questions',
                'title' => 'Види питальних речень',
                'language' => 'uk',
            ],
            'tags' => [
                'Alternative Questions',
                'Альтернативні питання',
                'Question Forms',
                'OR',
                'Choice Questions',
                'Питання з вибором',
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
                        'intro' => 'У цій темі ти вивчиш <strong>альтернативні питання (Alternative Questions)</strong> — питання, які пропонують вибір між варіантами за допомогою сполучника <strong>or</strong>.',
                        'rules' => [
                            [
                                'label' => 'ДВА ВАРІАНТИ',
                                'color' => 'emerald',
                                'text' => '<strong>Вибір між двома</strong> — з\'єднуємо через or:',
                                'example' => 'Do you prefer coffee or tea?',
                            ],
                            [
                                'label' => 'СТРУКТУРА',
                                'color' => 'blue',
                                'text' => '<strong>Як Yes/No питання</strong> + or + варіант:',
                                'example' => 'Is it black or white?',
                            ],
                            [
                                'label' => 'ВІДПОВІДЬ',
                                'color' => 'amber',
                                'text' => '<strong>Обираємо варіант</strong>, не відповідаємо Yes/No:',
                                'example' => 'Coffee, please. / I prefer tea.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Що таке альтернативні питання?',
                        'intro' => 'Alternative questions — це питання з вибором між варіантами:',
                        'items' => [
                            ['label' => 'Yes/No питання', 'title' => 'Do you like coffee?', 'subtitle' => 'Відповідь: Yes/No'],
                            ['label' => 'Альтернативне', 'title' => 'Do you like coffee or tea?', 'subtitle' => 'Відповідь: Coffee / Tea'],
                            ['label' => 'Структура', 'title' => 'Question + OR + Alternative', 'subtitle' => 'Питання + АБО + Альтернатива'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Базова структура альтернативних питань',
                        'sections' => [
                            [
                                'label' => 'З простими іменниками',
                                'color' => 'emerald',
                                'description' => 'Найпростіша форма — вибір між двома іменниками:',
                                'examples' => [
                                    ['en' => 'Do you prefer coffee or tea?', 'ua' => 'Ти віддаєш перевагу каві чи чаю?'],
                                    ['en' => 'Would you like meat or fish?', 'ua' => 'Ти б хотів мʼясо чи рибу?'],
                                    ['en' => 'Is she a teacher or a doctor?', 'ua' => 'Вона вчителька чи лікарка?'],
                                    ['en' => 'Are you from Spain or Italy?', 'ua' => 'Ти з Іспанії чи Італії?'],
                                ],
                            ],
                            [
                                'label' => 'З прикметниками',
                                'color' => 'sky',
                                'description' => 'Альтернатива між прикметниками — описом чогось:',
                                'examples' => [
                                    ['en' => 'Is it big or small?', 'ua' => 'Це велике чи маленьке?'],
                                    ['en' => 'Is the test easy or difficult?', 'ua' => 'Тест легкий чи складний?'],
                                    ['en' => 'Are you happy or sad?', 'ua' => 'Ти щасливий чи сумний?'],
                                    ['en' => 'Was the movie good or bad?', 'ua' => 'Фільм був хорошим чи поганим?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'purple',
                                'description' => 'Загальна формула альтернативного питання:',
                                'examples' => [
                                    ['en' => 'Auxiliary + Subject + Verb + Option A + OR + Option B?', 'ua' => 'Допоміжне + Підмет + Дієслово + Варіант А + АБО + Варіант Б?'],
                                ],
                                'note' => '📌 Структура як у Yes/No питанні, але додається OR + альтернатива',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Альтернативні питання з DO/DOES/DID',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'У Present Simple використовуємо <strong>do/does</strong> + or + альтернатива:',
                                'examples' => [
                                    ['en' => 'Do you walk or drive to work?', 'ua' => 'Ти йдеш пішки чи їдеш на роботу?'],
                                    ['en' => 'Does she like cats or dogs?', 'ua' => 'Їй подобаються кішки чи собаки?'],
                                    ['en' => 'Do they live in a house or an apartment?', 'ua' => 'Вони живуть у будинку чи квартирі?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'sky',
                                'description' => 'У Past Simple використовуємо <strong>did</strong> + or + альтернатива:',
                                'examples' => [
                                    ['en' => 'Did you go by bus or by train?', 'ua' => 'Ти їхав автобусом чи поїздом?'],
                                    ['en' => 'Did she buy a laptop or a tablet?', 'ua' => 'Вона купила ноутбук чи планшет?'],
                                    ['en' => 'Did they win or lose?', 'ua' => 'Вони виграли чи програли?'],
                                ],
                            ],
                            [
                                'label' => 'З різними дієсловами',
                                'color' => 'amber',
                                'description' => 'Можна ставити питання про різні дії:',
                                'examples' => [
                                    ['en' => 'Do you want to stay or leave?', 'ua' => 'Ти хочеш залишитися чи піти?'],
                                    ['en' => 'Did you call or text him?', 'ua' => 'Ти йому телефонував чи написав?'],
                                    ['en' => 'Does he work or study?', 'ua' => 'Він працює чи навчається?'],
                                ],
                                'note' => '📌 OR може зʼєднувати дієслова, іменники, прикметники',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Альтернативні питання з TO BE',
                        'sections' => [
                            [
                                'label' => 'Present — AM/IS/ARE',
                                'color' => 'blue',
                                'description' => 'З дієсловом <strong>to be</strong> робимо інверсію + or:',
                                'examples' => [
                                    ['en' => 'Are you a student or a teacher?', 'ua' => 'Ти студент чи вчитель?'],
                                    ['en' => 'Is it hot or cold outside?', 'ua' => 'Надворі жарко чи холодно?'],
                                    ['en' => 'Are they British or American?', 'ua' => 'Вони британці чи американці?'],
                                ],
                            ],
                            [
                                'label' => 'Past — WAS/WERE',
                                'color' => 'purple',
                                'description' => 'У минулому часі з <strong>was/were</strong>:',
                                'examples' => [
                                    ['en' => 'Was the book interesting or boring?', 'ua' => 'Книга була цікавою чи нудною?'],
                                    ['en' => 'Were you at home or at work?', 'ua' => 'Ти був вдома чи на роботі?'],
                                    ['en' => 'Was she happy or upset?', 'ua' => 'Вона була щаслива чи засмучена?'],
                                ],
                            ],
                            [
                                'label' => 'З займенниками THIS/THAT',
                                'color' => 'rose',
                                'description' => 'Альтернатива при ідентифікації предметів:',
                                'examples' => [
                                    ['en' => 'Is this your bag or hers?', 'ua' => 'Це твоя сумка чи її?'],
                                    ['en' => 'Is that a cat or a dog?', 'ua' => 'То кіт чи собака?'],
                                    ['en' => 'Are these apples or pears?', 'ua' => 'Це яблука чи груші?'],
                                ],
                                'note' => '📌 Відповідь: обираємо один варіант, НЕ Yes/No',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Альтернативні питання з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'CAN / COULD',
                                'color' => 'emerald',
                                'description' => 'З <strong>can/could</strong> для вибору можливостей або здібностей:',
                                'examples' => [
                                    ['en' => 'Can you swim or dive?', 'ua' => 'Ти вмієш плавати чи пірнати?'],
                                    ['en' => 'Could we meet today or tomorrow?', 'ua' => 'Ми могли б зустрітися сьогодні чи завтра?'],
                                    ['en' => 'Can she speak French or German?', 'ua' => 'Вона розмовляє французькою чи німецькою?'],
                                ],
                            ],
                            [
                                'label' => 'WILL / WOULD',
                                'color' => 'sky',
                                'description' => 'З <strong>will/would</strong> для майбутніх дій або бажань:',
                                'examples' => [
                                    ['en' => 'Will you come by car or by bus?', 'ua' => 'Ти приїдеш машиною чи автобусом?'],
                                    ['en' => 'Would you like wine or beer?', 'ua' => 'Ти б хотів вина чи пива?'],
                                    ['en' => 'Will they arrive in the morning or evening?', 'ua' => 'Вони приїдуть вранці чи ввечері?'],
                                ],
                            ],
                            [
                                'label' => 'SHOULD / MUST',
                                'color' => 'amber',
                                'description' => 'Для порад або обовʼязків з вибором:',
                                'examples' => [
                                    ['en' => 'Should I call or text you?', 'ua' => 'Мені тобі телефонувати чи написати?'],
                                    ['en' => 'Must we pay by cash or card?', 'ua' => 'Ми маємо платити готівкою чи карткою?'],
                                ],
                                'note' => '📌 Модальне дієслово + підмет + дієслово + or + альтернатива',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Складніші альтернативні питання',
                        'sections' => [
                            [
                                'label' => 'З фразами та реченнями',
                                'color' => 'purple',
                                'description' => 'OR може зʼєднувати цілі фрази або речення:',
                                'examples' => [
                                    ['en' => 'Do you want to go now or wait a bit?', 'ua' => 'Ти хочеш піти зараз чи трохи почекати?'],
                                    ['en' => 'Should we eat at home or go to a restaurant?', 'ua' => 'Нам їсти вдома чи піти в ресторан?'],
                                    ['en' => 'Will you study English or learn another language?', 'ua' => 'Ти будеш вчити англійську чи вивчати іншу мову?'],
                                ],
                            ],
                            [
                                'label' => 'Більше двох варіантів',
                                'color' => 'blue',
                                'description' => 'Можна запропонувати більше двох варіантів:',
                                'examples' => [
                                    ['en' => 'Do you prefer red, blue, or green?', 'ua' => 'Ти віддаєш перевагу червоному, синьому чи зеленому?'],
                                    ['en' => 'Should we meet on Monday, Tuesday, or Wednesday?', 'ua' => 'Нам зустрітися в понеділок, вівторок чи середу?'],
                                    ['en' => 'Is she from France, Spain, or Italy?', 'ua' => 'Вона з Франції, Іспанії чи Італії?'],
                                ],
                            ],
                            [
                                'label' => 'З питальними словами',
                                'color' => 'rose',
                                'description' => 'Альтернативні питання можуть починатися з Wh-слів:',
                                'examples' => [
                                    ['en' => 'Which do you prefer — coffee or tea?', 'ua' => 'Що ти більше любиш — каву чи чай?'],
                                    ['en' => 'Where should we go — to the park or to the beach?', 'ua' => 'Куди нам піти — в парк чи на пляж?'],
                                    ['en' => 'When will you come — today or tomorrow?', 'ua' => 'Коли ти прийдеш — сьогодні чи завтра?'],
                                ],
                                'note' => '📌 Which часто використовується для вибору між варіантами',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Як відповідати на альтернативні питання',
                        'sections' => [
                            [
                                'label' => 'Обираємо варіант',
                                'color' => 'emerald',
                                'description' => 'Відповідь — один із запропонованих варіантів, НЕ Yes/No:',
                                'examples' => [
                                    ['en' => 'Q: Coffee or tea? — A: Coffee, please.', 'ua' => 'П: Кава чи чай? — В: Каву, будь ласка.'],
                                    ['en' => 'Q: Is it black or white? — A: It\'s black.', 'ua' => 'П: Воно чорне чи біле? — В: Воно чорне.'],
                                    ['en' => 'Q: Do you walk or drive? — A: I usually drive.', 'ua' => 'П: Ти ходиш пішки чи їздиш? — В: Я зазвичай їжджу.'],
                                ],
                            ],
                            [
                                'label' => 'Відхилити обидва',
                                'color' => 'sky',
                                'description' => 'Можна відхилити обидва варіанти і запропонувати третій:',
                                'examples' => [
                                    ['en' => 'Q: Coffee or tea? — A: Neither. Water, please.', 'ua' => 'П: Кава чи чай? — В: Ні те, ні інше. Воду, будь ласка.'],
                                    ['en' => 'Q: Are you from Spain or Italy? — A: Neither. I\'m from France.', 'ua' => 'П: Ти з Іспанії чи Італії? — В: Ні. Я з Франції.'],
                                ],
                            ],
                            [
                                'label' => 'Обрати обидва',
                                'color' => 'amber',
                                'description' => 'Іноді можна обрати обидва варіанти:',
                                'examples' => [
                                    ['en' => 'Q: Do you like cats or dogs? — A: Both! I love animals.', 'ua' => 'П: Тобі подобаються кішки чи собаки? — В: Обидві! Я люблю тварин.'],
                                    ['en' => 'Q: Coffee or tea? — A: I like both.', 'ua' => 'П: Кава чи чай? — В: Мені подобається і те, і інше.'],
                                ],
                                'note' => '📌 Neither — жодне, Both — обидва',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Порівняння Yes/No та Alternative Questions',
                        'intro' => 'Основні відмінності між двома типами питань:',
                        'rows' => [
                            [
                                'en' => 'Yes/No Question',
                                'ua' => 'Загальне питання',
                                'note' => 'Do you like coffee? → Yes/No',
                            ],
                            [
                                'en' => 'Alternative Question',
                                'ua' => 'Альтернативне питання',
                                'note' => 'Do you like coffee or tea? → Coffee/Tea',
                            ],
                            [
                                'en' => 'Structure',
                                'ua' => 'Структура',
                                'note' => 'Aux + Subject + Verb + OR + Alternative',
                            ],
                            [
                                'en' => 'Answer type',
                                'ua' => 'Тип відповіді',
                                'note' => 'Обираємо один варіант, НЕ Yes/No',
                            ],
                            [
                                'en' => 'Intonation',
                                'ua' => 'Інтонація',
                                'note' => 'Падає на останньому варіанті',
                            ],
                        ],
                        'warning' => '📌 Основна відмінність: <strong>OR + альтернатива</strong>',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Відповідь Yes/No замість вибору варіанта.',
                                'wrong' => '❌ Q: Coffee or tea? A: Yes.',
                                'right' => '✅ <span class="font-mono">Q: Coffee or tea? A: Coffee, please.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок слів у питанні.',
                                'wrong' => '❌ You prefer coffee or tea?',
                                'right' => '✅ <span class="font-mono">Do you prefer coffee or tea?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Забути OR між варіантами.',
                                'wrong' => '❌ Do you want coffee, tea?',
                                'right' => '✅ <span class="font-mono">Do you want coffee or tea?</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Неправильна форма дієслова після допоміжного.',
                                'wrong' => '❌ Does she likes cats or dogs?',
                                'right' => '✅ <span class="font-mono">Does she like cats or dogs?</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            '<strong>Alternative questions</strong> — питання з вибором між варіантами через <strong>OR</strong>.',
                            '<strong>Структура</strong>: як Yes/No питання + or + альтернатива.',
                            '<strong>Відповідь</strong> — обираємо один варіант, НЕ Yes/No.',
                            '<strong>З do/does/did</strong>: Do you walk or drive? Did she buy A or B?',
                            '<strong>З to be</strong>: Is it hot or cold? Are you A or B?',
                            '<strong>З модальними</strong>: Can you swim or dive? Will you come today or tomorrow?',
                            '<strong>Більше варіантів</strong>: можна запропонувати 3+ варіанти через кому + or.',
                            '<strong>З Wh-словами</strong>: Which do you prefer — A or B?',
                            '<strong>Відповіді</strong>: один варіант / Neither (жодне) / Both (обидва).',
                            '<strong>Інтонація</strong> — падає на останньому варіанті.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '11. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери правильну форму альтернативного питання.',
                        'selects' => [
                            ['label' => 'a) You prefer coffee or tea? / b) Do you prefer coffee or tea? / c) Do you prefer coffee and tea?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Is it hot or cold? / b) It is hot or cold? / c) Does it hot or cold?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Can you swim and dive? / b) You can swim or dive? / c) Can you swim or dive?', 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b', 'c'],
                        'input_title' => 'Вправа 2. Постав альтернативне питання',
                        'input_intro' => 'Утвори альтернативне питання з поданих варіантів.',
                        'inputs' => [
                            ['before' => 'She likes (cats / dogs). → ', 'after' => ''],
                            ['before' => 'You are from (Spain / Italy). → ', 'after' => ''],
                            ['before' => 'They will come (today / tomorrow). → ', 'after' => ''],
                            ['before' => 'It is (big / small). → ', 'after' => ''],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => 'Знайди і виправ помилку в альтернативному питанні.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'You prefer coffee or tea?',
                                'example_target' => 'Do you prefer coffee or tea?',
                            ],
                            [
                                'original' => '1. Does she likes cats or dogs?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Is it hot cold?',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. You can swim or dive?',
                                'placeholder' => 'Виправ помилку',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші теми з розділу Види питальних речень',
                        'items' => [
                            [
                                'label' => 'Yes/No Questions — Загальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Wh-Questions — Спеціальні питання',
                                'current' => false,
                            ],
                            [
                                'label' => 'Alternative Questions — Альтернативні питання (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Subject Questions — Питання до підмета',
                                'current' => false,
                            ],
                            [
                                'label' => 'Question Tags — Розділові питання',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
