<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Pages\QuestionsNegations\QuestionsNegationsPageSeeder;

class YesNoQuestionsGeneralQuestionsTheorySeeder extends QuestionsNegationsPageSeeder
{
    protected function slug(): string
    {
        return 'yesno-questions-general-questions';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Yes/No questions (general questions) — Загальні питання',
            'subtitle_html' => '<p><strong>Yes/No questions</strong> (загальні питання) — це питання, на які можна відповісти "так" або "ні". Вони утворюються за допомогою <strong>допоміжних дієслів (do/does/did, am/is/are, have/has, will, can, must тощо)</strong>, які ставляться перед підметом.</p>',
            'subtitle_text' => 'Загальні питання в англійській мові: утворення з do/does/did, be, модальними дієсловами, порядок слів та короткі відповіді.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'types-of-questions',
                'title' => 'Types of questions — Види питальних речень',
                'language' => 'uk',
            ],
            'tags' => [
                'Yes/No questions',
                'Загальні питання',
                'General questions',
                'Questions',
                'Питання',
                'Do Does Did',
                'Question forms',
                'Short answers',
                'Короткі відповіді',
                'Grammar',
                'Theory',
                'A1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1',
                        'intro' => 'У цій темі ти вивчиш, як утворювати <strong>загальні питання (Yes/No questions)</strong> — питання, на які можна відповісти "так" або "ні".',
                        'rules' => [
                            [
                                'label' => 'DO/DOES/DID',
                                'color' => 'emerald',
                                'text' => '<strong>Present/Past Simple</strong> — використовуємо do/does/did:',
                                'example' => 'Do you like coffee? Does she work here? Did they call?',
                            ],
                            [
                                'label' => 'AM/IS/ARE',
                                'color' => 'blue',
                                'text' => '<strong>Verb to be</strong> — міняємо місцями з підметом:',
                                'example' => 'Are you ready? Is he a student? Was it expensive?',
                            ],
                            [
                                'label' => 'CAN/WILL/MUST',
                                'color' => 'violet',
                                'text' => '<strong>Модальні дієслова</strong> — ставимо перед підметом:',
                                'example' => 'Can you swim? Will they come? Must I go?',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Як утворюються загальні питання',
                        'intro' => 'Загальні питання (Yes/No questions) утворюються трьома основними способами залежно від типу дієслова:',
                        'items' => [
                            ['label' => 'DO/DOES/DID', 'title' => 'З допоміжними do/does/did', 'subtitle' => 'Present Simple і Past Simple — Do you speak English?'],
                            ['label' => 'BE', 'title' => 'З дієсловом to be', 'subtitle' => 'Міняємо місцями підмет і be — Are you ready?'],
                            ['label' => 'MODAL', 'title' => 'З модальними дієсловами', 'subtitle' => 'Can, will, must, should тощо — Can you help me?'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Питання з do/does/did (Present/Past Simple)',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'У <strong>Present Simple</strong> використовуємо <strong>do</strong> (I, you, we, they) або <strong>does</strong> (he, she, it).',
                                'examples' => [
                                    ['en' => 'Do you like pizza?', 'ua' => 'Ти любиш піцу?'],
                                    ['en' => 'Does she speak English?', 'ua' => 'Вона розмовляє англійською?'],
                                    ['en' => 'Do they work here?', 'ua' => 'Вони тут працюють?'],
                                    ['en' => 'Does it cost much?', 'ua' => 'Це дорого коштує?'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'blue',
                                'description' => 'У <strong>Past Simple</strong> використовуємо <strong>did</strong> для всіх осіб.',
                                'examples' => [
                                    ['en' => 'Did you see the movie?', 'ua' => 'Ти бачив фільм?'],
                                    ['en' => 'Did she call you?', 'ua' => 'Вона тобі телефонувала?'],
                                    ['en' => 'Did they arrive on time?', 'ua' => 'Вони прибули вчасно?'],
                                    ['en' => 'Did it rain yesterday?', 'ua' => 'Вчора йшов дощ?'],
                                ],
                            ],
                            [
                                'label' => 'Порядок слів',
                                'color' => 'amber',
                                'description' => '<strong>Формула:</strong> Do/Does/Did + підмет + основне дієслово (без -s, -ed)',
                                'examples' => [
                                    ['en' => 'Do + you + like → Do you like?', 'ua' => 'Ти любиш?'],
                                    ['en' => 'Does + she + work → Does she work?', 'ua' => 'Вона працює?'],
                                    ['en' => 'Did + they + go → Did they go?', 'ua' => 'Вони пішли?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Питання з to be (am/is/are/was/were)',
                        'sections' => [
                            [
                                'label' => 'Present',
                                'color' => 'emerald',
                                'description' => 'З дієсловом <strong>to be</strong> просто <strong>міняємо місцями підмет і дієслово</strong>.',
                                'examples' => [
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                    ['en' => 'Is she a teacher?', 'ua' => 'Вона вчителька?'],
                                    ['en' => 'Are they at home?', 'ua' => 'Вони вдома?'],
                                    ['en' => 'Am I late?', 'ua' => 'Я запізнився?'],
                                ],
                            ],
                            [
                                'label' => 'Past',
                                'color' => 'blue',
                                'description' => 'У минулому часі використовуємо <strong>was</strong> або <strong>were</strong>.',
                                'examples' => [
                                    ['en' => 'Was it expensive?', 'ua' => 'Це було дорого?'],
                                    ['en' => 'Were you at the party?', 'ua' => 'Ти був на вечірці?'],
                                    ['en' => 'Was she happy?', 'ua' => 'Вона була щаслива?'],
                                    ['en' => 'Were they surprised?', 'ua' => 'Вони були здивовані?'],
                                ],
                            ],
                            [
                                'label' => 'Порядок слів',
                                'color' => 'amber',
                                'description' => '<strong>Формула:</strong> Am/Is/Are/Was/Were + підмет',
                                'examples' => [
                                    ['en' => 'You are ready → Are you ready?', 'ua' => 'Ти готовий → Ти готовий?'],
                                    ['en' => 'She is a student → Is she a student?', 'ua' => 'Вона студентка → Вона студентка?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '4. Питання з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'Can/Could',
                                'color' => 'emerald',
                                'description' => '<strong>Can</strong> (можу, вмію) та <strong>could</strong> (міг би, чи не міг би) ставимо перед підметом.',
                                'examples' => [
                                    ['en' => 'Can you swim?', 'ua' => 'Ти вмієш плавати?'],
                                    ['en' => 'Could you help me?', 'ua' => 'Чи не міг би ти мені допомогти?'],
                                    ['en' => 'Can she speak French?', 'ua' => 'Вона розмовляє французькою?'],
                                ],
                            ],
                            [
                                'label' => 'Will/Would',
                                'color' => 'blue',
                                'description' => '<strong>Will</strong> (буду) та <strong>would</strong> (чи не міг би) для майбутнього та ввічливих питань.',
                                'examples' => [
                                    ['en' => 'Will you come tomorrow?', 'ua' => 'Ти прийдеш завтра?'],
                                    ['en' => 'Would you like some tea?', 'ua' => 'Чи не бажаєте трохи чаю?'],
                                    ['en' => 'Will it rain today?', 'ua' => 'Сьогодні буде дощ?'],
                                ],
                            ],
                            [
                                'label' => 'Must/Should/May',
                                'color' => 'violet',
                                'description' => '<strong>Must</strong> (мушу), <strong>should</strong> (слід би), <strong>may</strong> (можна, дозволено).',
                                'examples' => [
                                    ['en' => 'Must I go now?', 'ua' => 'Мені треба йти зараз?'],
                                    ['en' => 'Should we call them?', 'ua' => 'Нам слід їм зателефонувати?'],
                                    ['en' => 'May I ask a question?', 'ua' => 'Можна запитати?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'body' => json_encode([
                        'title' => '5. Короткі відповіді (Short Answers)',
                        'sections' => [
                            [
                                'label' => 'З do/does/did',
                                'color' => 'emerald',
                                'description' => 'Відповідаємо <strong>Yes/No + підмет + do/does/did</strong> (або not).',
                                'examples' => [
                                    ['en' => 'Do you like pizza? — Yes, I do. / No, I don\'t.', 'ua' => 'Так, люблю. / Ні, не люблю.'],
                                    ['en' => 'Does she work here? — Yes, she does. / No, she doesn\'t.', 'ua' => 'Так, працює. / Ні, не працює.'],
                                    ['en' => 'Did they call? — Yes, they did. / No, they didn\'t.', 'ua' => 'Так, телефонували. / Ні, не телефонували.'],
                                ],
                            ],
                            [
                                'label' => 'З to be',
                                'color' => 'blue',
                                'description' => 'Відповідаємо <strong>Yes/No + підмет + am/is/are/was/were</strong>.',
                                'examples' => [
                                    ['en' => 'Are you ready? — Yes, I am. / No, I\'m not.', 'ua' => 'Так, готовий. / Ні, не готовий.'],
                                    ['en' => 'Is she a teacher? — Yes, she is. / No, she isn\'t.', 'ua' => 'Так, вчителька. / Ні, не вчителька.'],
                                    ['en' => 'Was it expensive? — Yes, it was. / No, it wasn\'t.', 'ua' => 'Так, дорого. / Ні, не дорого.'],
                                ],
                            ],
                            [
                                'label' => 'З модальними',
                                'color' => 'violet',
                                'description' => 'Відповідаємо <strong>Yes/No + підмет + модальне дієслово</strong>.',
                                'examples' => [
                                    ['en' => 'Can you swim? — Yes, I can. / No, I can\'t.', 'ua' => 'Так, вмію. / Ні, не вмію.'],
                                    ['en' => 'Will you come? — Yes, I will. / No, I won\'t.', 'ua' => 'Так, прийду. / Ні, не прийду.'],
                                    ['en' => 'Should we go? — Yes, we should. / No, we shouldn\'t.', 'ua' => 'Так, слід. / Ні, не слід.'],
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
<li>❌ <span class="gw-en">Does she likes pizza?</span> (зайве -s)<br>✅ <span class="gw-en">Does she like pizza?</span></li>
<li>❌ <span class="gw-en">Did they went?</span> (зайве -ed)<br>✅ <span class="gw-en">Did they go?</span></li>
<li>❌ <span class="gw-en">Yes, I like.</span> (без do)<br>✅ <span class="gw-en">Yes, I do.</span></li>
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
<p>Почни з <strong>Present Simple</strong> — це найчастіший тип питань: <span class="gw-en">Do you...? Does she...?</span></p>
<p>Запам'ятай: у питанні основне дієслово <strong>без -s, -es, -ed</strong> — за форму часу відповідає допоміжне дієслово.</p>
<p>З <strong>to be</strong> все простіше — просто міняємо підмет і дієслово місцями: <span class="gw-en">You are → Are you?</span></p>
<p>У коротких відповідях <strong>повторюй допоміжне дієслово з питання</strong>: <span class="gw-en">Do you? → Yes, I do.</span></p>
<p><strong>Модальні дієслова</strong> (can, will, must) самі утворюють питання — не потрібно додавати do/does/did.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
