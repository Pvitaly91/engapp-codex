<?php

namespace Database\Seeders\Page_v2\BasicGrammar\WordOrder;

use Database\Seeders\Page_v2\BasicGrammar\BasicGrammarPageSeeder;

class WordOrderQuestionsNegativesTheorySeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'theory-word-order-questions-negatives';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order in Questions and Negatives — Питання та заперечення',
            'subtitle_html' => '<p><strong>Word order</strong> (порядок слів) у питаннях і запереченнях відрізняється від стверджувальних речень. Потрібні допоміжні дієслова та особливий порядок елементів.</p>',
            'subtitle_text' => 'Теоретичний огляд порядку слів у питальних та заперечних реченнях англійської мови з прикладами та практикою.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'word-order',
                'title' => 'Word Order — Порядок слів',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Questions',
                'Negatives',
                'Auxiliary Verbs',
                'Do/Does/Did',
                'Wh-Questions',
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
                        'intro' => 'У цій темі ти вивчиш <strong>порядок слів у питаннях і запереченнях</strong>: використання допоміжних дієслів, структуру Wh-питань та типові помилки.',
                        'rules' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'emerald',
                                'text' => '<strong>Do/Does/Did + Subject + Verb</strong> — базова структура питання:',
                                'example' => 'Do you like pizza?',
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'blue',
                                'text' => '<strong>Wh-word + Auxiliary + Subject + Verb</strong> — питання з where, what, why:',
                                'example' => 'Where do you live?',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'amber',
                                'text' => '<strong>Subject + Auxiliary + not + Verb</strong> — структура заперечення:',
                                'example' => "I don't like apples.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Допоміжні дієслова (Auxiliary Verbs)',
                        'intro' => 'Для побудови питань і заперечень потрібні допоміжні дієслова:',
                        'items' => [
                            ['label' => 'Do', 'title' => 'I / You / We / They', 'subtitle' => 'Do you like coffee? I do not know.'],
                            ['label' => 'Does', 'title' => 'He / She / It', 'subtitle' => 'Does she work? He does not play.'],
                            ['label' => 'Did', 'title' => 'Минулий час', 'subtitle' => 'Did they come? We did not see.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Yes/No питання',
                        'sections' => [
                            [
                                'label' => 'Звичайні дієслова',
                                'color' => 'emerald',
                                'description' => 'Для звичайних дієслів використовуй <strong>do/does/did</strong> перед підметом.',
                                'examples' => [
                                    ['en' => 'Do you like pizza?', 'ua' => 'Ти любиш піцу?'],
                                    ['en' => 'Does she play tennis?', 'ua' => 'Вона грає в теніс?'],
                                    ['en' => 'Did they watch the show?', 'ua' => 'Вони дивились шоу?'],
                                ],
                            ],
                            [
                                'label' => 'To be та модальні дієслова',
                                'color' => 'sky',
                                'description' => 'Для <strong>to be</strong> та модальних дієслів просто міняй місцями підмет і дієслово — do/does/did не потрібні.',
                                'examples' => [
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий?'],
                                    ['en' => 'Is he coming?', 'ua' => 'Він іде?'],
                                    ['en' => 'Can you swim?', 'ua' => 'Ти вмієш плавати?'],
                                    ['en' => 'Have you finished?', 'ua' => 'Ти закінчив?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Wh-питання (Wh-Questions)',
                        'sections' => [
                            [
                                'label' => 'Структура Wh-питань',
                                'color' => 'emerald',
                                'description' => '<strong>Wh-word + Auxiliary + Subject + Verb</strong> — питальне слово на початку, потім допоміжне дієслово.',
                                'examples' => [
                                    ['en' => 'Where do you live?', 'ua' => 'Де ти живеш?'],
                                    ['en' => 'What did she eat?', 'ua' => 'Що вона їла?'],
                                    ['en' => 'Why are they running?', 'ua' => 'Чому вони біжать?'],
                                    ['en' => 'How can I help?', 'ua' => 'Як я можу допомогти?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-слово як підмет',
                                'color' => 'amber',
                                'description' => 'Якщо <strong>Who</strong> або <strong>What</strong> є підметом, допоміжне дієслово <strong>не потрібне</strong>.',
                                'examples' => [
                                    ['en' => 'Who called you?', 'ua' => 'Хто тобі дзвонив?'],
                                    ['en' => 'What happened?', 'ua' => 'Що сталося?'],
                                    ['en' => 'Who lives here?', 'ua' => 'Хто тут живе?'],
                                ],
                                'note' => 'Порівняй: <strong>Who called you?</strong> (who — підмет) vs <strong>Who did you call?</strong> (you — підмет).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Заперечення (Negatives)',
                        'sections' => [
                            [
                                'label' => 'Звичайні дієслова',
                                'color' => 'emerald',
                                'description' => 'Додай <strong>do/does/did + not</strong> перед основним дієсловом.',
                                'examples' => [
                                    ['en' => "I do not (don't) like apples.", 'ua' => 'Я не люблю яблука.'],
                                    ['en' => "She does not (doesn't) play football.", 'ua' => 'Вона не грає у футбол.'],
                                    ['en' => "They did not (didn't) see the movie.", 'ua' => 'Вони не бачили фільм.'],
                                ],
                            ],
                            [
                                'label' => 'To be та модальні дієслова',
                                'color' => 'sky',
                                'description' => 'Додай <strong>not</strong> безпосередньо після to be або модального дієслова.',
                                'examples' => [
                                    ['en' => "He is not (isn't) ready.", 'ua' => 'Він не готовий.'],
                                    ['en' => "She was not (wasn't) there.", 'ua' => 'Її там не було.'],
                                    ['en' => "You must not (mustn't) touch it.", 'ua' => 'Ти не повинен це чіпати.'],
                                    ['en' => "They cannot (can't) come.", 'ua' => 'Вони не можуть прийти.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Скорочені форми (Contractions)',
                        'intro' => 'У розмовній мові not часто скорочується:',
                        'rows' => [
                            [
                                'en' => 'do not',
                                'ua' => "don't",
                                'note' => "I don't know.",
                            ],
                            [
                                'en' => 'does not',
                                'ua' => "doesn't",
                                'note' => "She doesn't like it.",
                            ],
                            [
                                'en' => 'did not',
                                'ua' => "didn't",
                                'note' => "We didn't go.",
                            ],
                            [
                                'en' => 'is not',
                                'ua' => "isn't",
                                'note' => "He isn't here.",
                            ],
                            [
                                'en' => 'are not',
                                'ua' => "aren't",
                                'note' => "They aren't coming.",
                            ],
                            [
                                'en' => 'will not',
                                'ua' => "won't",
                                'note' => "I won't forget.",
                            ],
                            [
                                'en' => 'cannot',
                                'ua' => "can't",
                                'note' => "She can't swim.",
                            ],
                        ],
                        'warning' => '📌 У формальному письмі використовуй повні форми.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Відсутнє допоміжне дієслово у питанні.',
                                'wrong' => 'You like pizza?',
                                'right' => '✅ <span class="font-mono">Do you like pizza?</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Відсутнє допоміжне дієслово у Wh-питанні.',
                                'wrong' => 'Where you live?',
                                'right' => '✅ <span class="font-mono">Where do you live?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний вибір do/does.',
                                'wrong' => "She don't like it.",
                                'right' => '✅ <span class="font-mono">She doesn\'t like it.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => 'Заперечення без допоміжного дієслова.',
                                'wrong' => 'I no like apples.',
                                'right' => '✅ <span class="font-mono">I don\'t like apples.</span>',
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
                            'Yes/No питання: <strong>Aux + Subject + Verb</strong> (Do you like...?)',
                            'Wh-питання: <strong>Wh-word + Aux + Subject + Verb</strong> (Where do you...?)',
                            'Wh-підмет: без допоміжного дієслова (<em>Who called you?</em>)',
                            'Заперечення: <strong>Subject + Aux + not + Verb</strong> (I don\'t like...)',
                            'To be / модальні: <strong>не потребують do/does/did</strong>.',
                            'У розмовній мові використовуй скорочення: <em>don\'t, doesn\'t, can\'t</em>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Практика',
                        'select_title' => 'Вправа 1. Обери правильний варіант',
                        'select_intro' => 'Обери речення з правильним порядком слів.',
                        'selects' => [
                            ['label' => 'a) Do you like coffee? / b) You like coffee?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => 'a) Where does she live? / b) Where she lives?', 'prompt' => 'Який варіант правильний?'],
                            ['label' => "a) He don't work here. / b) He doesn't work here.", 'prompt' => 'Який варіант правильний?'],
                        ],
                        'options' => ['a', 'b'],
                        'input_title' => 'Вправа 2. Побудуй питання',
                        'input_intro' => 'Перетвори ствердження на питання.',
                        'inputs' => [
                            ['before' => 'You speak English.', 'after' => '→ Do you...?'],
                            ['before' => 'She works in a bank.', 'after' => '→ Does she...?'],
                            ['before' => 'They went to the party.', 'after' => '→ Did they...?'],
                        ],
                        'rephrase_title' => 'Вправа 3. Побудуй заперечення',
                        'rephrase_intro' => 'Перетвори ствердження на заперечення.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I like apples.',
                                'example_target' => "I don't like apples.",
                            ],
                            [
                                'original' => '1. She plays tennis.',
                                'placeholder' => 'Напиши заперечення',
                            ],
                            [
                                'original' => '2. They watched the movie.',
                                'placeholder' => 'Напиши заперечення',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з базової граматики',
                        'items' => [
                            [
                                'label' => 'Basic Word Order — Порядок слів у ствердженні',
                                'current' => false,
                            ],
                            [
                                'label' => 'Word Order in Questions and Negatives — Питання та заперечення (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Word Order with Adverbs — Прислівники та обставини',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
