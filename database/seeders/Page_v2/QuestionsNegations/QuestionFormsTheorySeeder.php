<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Pages\QuestionsNegations\QuestionsNegationsPageSeeder;

class QuestionFormsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'question-forms';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Question forms — Як ставити запитання',
            'subtitle_html' => '<p><strong>Question forms</strong> (як ставити запитання) — це базова тема англійської граматики. Щоб поставити питання, потрібно знати <strong>порядок слів</strong> і правильно використовувати <strong>допоміжні дієслова (do/does/did, be, have, модальні)</strong>. У цій темі ти вивчиш основні принципи утворення питань різних типів.</p>',
            'subtitle_text' => 'Як ставити запитання в англійській мові: порядок слів, допоміжні дієслова, типи питань та основні правила.',
            'locale' => 'uk',
            'category' => [
                'slug' => '8',
                'title' => 'Питальні речення та заперечення',
                'language' => 'uk',
            ],
            'tags' => [
                'Question forms',
                'Питання',
                'Як ставити запитання',
                'Questions',
                'Порядок слів',
                'Word order',
                'Do Does Did',
                'Допоміжні дієслова',
                'Auxiliary verbs',
                'Grammar',
                'Theory',
                'A1',
                'A2',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–A2',
                        'intro' => 'У цій темі ти вивчиш <strong>основні принципи утворення питань</strong> в англійській мові — від простих Yes/No питань до спеціальних Wh-questions.',
                        'rules' => [
                            [
                                'label' => 'Інверсія',
                                'color' => 'emerald',
                                'text' => '<strong>Допоміжне дієслово перед підметом</strong>:',
                                'example' => 'You like coffee → Do you like coffee?',
                            ],
                            [
                                'label' => 'Wh-слова',
                                'color' => 'blue',
                                'text' => '<strong>Питальні слова на початку</strong>:',
                                'example' => 'Where do you live? What are you doing?',
                            ],
                            [
                                'label' => 'Порядок',
                                'color' => 'violet',
                                'text' => '<strong>Wh-слово + допоміжне + підмет + дієслово</strong>:',
                                'example' => 'Why did they leave? How can I help?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні типи питань',
                        'intro' => 'В англійській мові є два основні типи питань, які утворюються по-різному:',
                        'items' => [
                            ['label' => 'YES/NO', 'title' => 'Загальні питання', 'subtitle' => 'Відповідь так або ні — Do you like it? Are you ready?'],
                            ['label' => 'WH-', 'title' => 'Спеціальні питання', 'subtitle' => 'З питальними словами — What do you want? Where is he?'],
                            ['label' => 'SUBJECT', 'title' => 'Питання до підмета', 'subtitle' => 'Без допоміжного дієслова — Who called? What happened?'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Загальні питання (Yes/No questions)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>Формула:</strong> Допоміжне дієслово + підмет + основне дієслово + решта',
                                'examples' => [
                                    ['en' => 'Do you speak English?', 'ua' => 'Ти розмовляєш англійською?'],
                                    ['en' => 'Does she work here?', 'ua' => 'Вона тут працює?'],
                                    ['en' => 'Did they call you?', 'ua' => 'Вони тобі телефонували?'],
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                ],
                            ],
                            [
                                'label' => 'З Present Simple',
                                'color' => 'blue',
                                'description' => 'Використовуємо <strong>do/does</strong> на початку питання.',
                                'examples' => [
                                    ['en' => 'You like pizza → Do you like pizza?', 'ua' => 'Ти любиш піцу?'],
                                    ['en' => 'She works here → Does she work here?', 'ua' => 'Вона тут працює?'],
                                    ['en' => 'They know him → Do they know him?', 'ua' => 'Вони його знають?'],
                                ],
                            ],
                            [
                                'label' => 'З Past Simple',
                                'color' => 'amber',
                                'description' => 'Використовуємо <strong>did</strong> для всіх осіб.',
                                'examples' => [
                                    ['en' => 'You went home → Did you go home?', 'ua' => 'Ти пішов додому?'],
                                    ['en' => 'She called → Did she call?', 'ua' => 'Вона телефонувала?'],
                                    ['en' => 'They arrived → Did they arrive?', 'ua' => 'Вони приїхали?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Спеціальні питання (Wh-questions)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>Формула:</strong> Wh-слово + допоміжне дієслово + підмет + основне дієслово',
                                'examples' => [
                                    ['en' => 'What do you want?', 'ua' => 'Що ти хочеш?'],
                                    ['en' => 'Where does she live?', 'ua' => 'Де вона живе?'],
                                    ['en' => 'When did they arrive?', 'ua' => 'Коли вони приїхали?'],
                                    ['en' => 'Why are you late?', 'ua' => 'Чому ти спізнився?'],
                                ],
                            ],
                            [
                                'label' => 'Питальні слова',
                                'color' => 'blue',
                                'description' => 'Основні <strong>Wh-слова</strong> для спеціальних питань.',
                                'examples' => [
                                    ['en' => 'What — що, який', 'ua' => 'What do you do? — Чим ти займаєшся?'],
                                    ['en' => 'Where — де, куди', 'ua' => 'Where is he? — Де він?'],
                                    ['en' => 'When — коли', 'ua' => 'When does it start? — Коли це починається?'],
                                    ['en' => 'Who — хто', 'ua' => 'Who is that? — Хто це?'],
                                    ['en' => 'Why — чому', 'ua' => 'Why did you leave? — Чому ти пішов?'],
                                    ['en' => 'How — як', 'ua' => 'How are you? — Як справи?'],
                                ],
                            ],
                            [
                                'label' => 'How + слово',
                                'color' => 'violet',
                                'description' => '<strong>How</strong> може комбінуватися з іншими словами.',
                                'examples' => [
                                    ['en' => 'How much — скільки (незлічуване)', 'ua' => 'How much does it cost?'],
                                    ['en' => 'How many — скільки (злічуване)', 'ua' => 'How many books do you have?'],
                                    ['en' => 'How often — як часто', 'ua' => 'How often do you exercise?'],
                                    ['en' => 'How long — як довго', 'ua' => 'How long does it take?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '4. Питання з різними дієсловами',
                        'sections' => [
                            [
                                'label' => 'З to be',
                                'color' => 'emerald',
                                'description' => 'З дієсловом <strong>to be</strong> просто міняємо місцями підмет і дієслово.',
                                'examples' => [
                                    ['en' => 'You are busy → Are you busy?', 'ua' => 'Ти зайнятий?'],
                                    ['en' => 'She is a teacher → Is she a teacher?', 'ua' => 'Вона вчителька?'],
                                    ['en' => 'They were late → Were they late?', 'ua' => 'Вони запізнилися?'],
                                    ['en' => 'Where are you? What is it?', 'ua' => 'Де ти? Що це?'],
                                ],
                            ],
                            [
                                'label' => 'З have got',
                                'color' => 'blue',
                                'description' => 'З <strong>have got</strong> теж просто міняємо місцями.',
                                'examples' => [
                                    ['en' => 'You have got a car → Have you got a car?', 'ua' => 'У тебе є машина?'],
                                    ['en' => 'She has got a dog → Has she got a dog?', 'ua' => 'У неї є собака?'],
                                    ['en' => 'What have you got? How many have they got?', 'ua' => 'Що у тебе є?'],
                                ],
                            ],
                            [
                                'label' => 'З модальними',
                                'color' => 'violet',
                                'description' => '<strong>Модальні дієслова</strong> (can, must, will, should) ставимо перед підметом.',
                                'examples' => [
                                    ['en' => 'You can swim → Can you swim?', 'ua' => 'Ти вмієш плавати?'],
                                    ['en' => 'She will come → Will she come?', 'ua' => 'Вона прийде?'],
                                    ['en' => 'They must go → Must they go?', 'ua' => 'Їм треба йти?'],
                                    ['en' => 'Where can I find it? What should I do?', 'ua' => 'Де я можу це знайти?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '5. Питання до підмета (Subject questions)',
                        'sections' => [
                            [
                                'label' => 'Особливість',
                                'color' => 'emerald',
                                'description' => 'Коли питаємо про <strong>підмет (хто? що?)</strong>, допоміжне дієслово <strong>не потрібне</strong>.',
                                'examples' => [
                                    ['en' => 'Who called you?', 'ua' => 'Хто тобі телефонував?'],
                                    ['en' => 'What happened?', 'ua' => 'Що сталося?'],
                                    ['en' => 'Who lives here?', 'ua' => 'Хто тут живе?'],
                                    ['en' => 'Which team won?', 'ua' => 'Яка команда виграла?'],
                                ],
                            ],
                            [
                                'label' => 'Контраст',
                                'color' => 'blue',
                                'description' => 'Порівняння: <strong>питання до підмета</strong> vs <strong>питання до додатка</strong>.',
                                'examples' => [
                                    ['en' => 'Who called you? (підмет)', 'ua' => 'Хто тобі телефонував?'],
                                    ['en' => 'Who did you call? (додаток)', 'ua' => 'Кому ти телефонував?'],
                                    ['en' => 'What happened? (підмет)', 'ua' => 'Що сталося?'],
                                    ['en' => 'What did you see? (додаток)', 'ua' => 'Що ти бачив?'],
                                ],
                            ],
                            [
                                'label' => 'Форма дієслова',
                                'color' => 'amber',
                                'description' => 'У питаннях до підмета дієслово має форму <strong>третьої особи однини</strong> (he/she/it).',
                                'examples' => [
                                    ['en' => 'Who works here? (not work)', 'ua' => 'Хто тут працює?'],
                                    ['en' => 'What makes this noise? (not make)', 'ua' => 'Що створює цей шум?'],
                                    ['en' => 'Which student knows the answer? (not know)', 'ua' => 'Який студент знає відповідь?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>❌ <span class="gw-en">You like pizza?</span> (без do)<br>✅ <span class="gw-en">Do you like pizza?</span></li>
<li>❌ <span class="gw-en">What you want?</span> (без do)<br>✅ <span class="gw-en">What do you want?</span></li>
<li>❌ <span class="gw-en">Where you live?</span> (без do)<br>✅ <span class="gw-en">Where do you live?</span></li>
<li>❌ <span class="gw-en">Who did call?</span> (зайве did у питанні до підмета)<br>✅ <span class="gw-en">Who called?</span></li>
<li>❌ <span class="gw-en">Does she likes pizza?</span> (зайве -s)<br>✅ <span class="gw-en">Does she like pizza?</span></li>
<li>❌ <span class="gw-en">Are you can swim?</span> (зайве are)<br>✅ <span class="gw-en">Can you swim?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Запам'ятай <strong>основну формулу питання</strong>: допоміжне дієслово (do/does/did, be, have, модальне) → підмет → основне дієслово.</p>
<p>У <strong>Present Simple</strong> і <strong>Past Simple</strong> потрібне допоміжне дієслово: <span class="gw-en">Do you...? Does she...? Did they...?</span></p>
<p>З <strong>to be</strong>, <strong>have got</strong> та <strong>модальними</strong> допоміжне дієслово не потрібне — просто міняємо місцями: <span class="gw-en">Are you...? Have you got...? Can you...?</span></p>
<p><strong>Wh-питання</strong> починаються з питального слова, потім — звичайний порядок питання: <span class="gw-en">Where do you live? What does she want?</span></p>
<p><strong>Виняток:</strong> у питаннях до підмета (Who? What?) допоміжне дієслово не потрібне: <span class="gw-en">Who called? What happened?</span></p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
