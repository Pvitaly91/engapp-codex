<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Page_v2\PronounsDemonstratives\PronounsDemonstrativesPageSeeder;

class PronounsDemonstrativesPersonalObjectPronounsTheorySeeder extends PronounsDemonstrativesPageSeeder
{
    protected function slug(): string
    {
        return 'personal-object-pronouns';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => "Personal & Object Pronouns — Особові й об'єктні займенники",
            'subtitle_html' => "<p><strong>Personal pronouns</strong> (особові займенники) — це найважливіші займенники англійської мови. Вони мають дві форми: <strong>subject pronouns</strong> (підметові) для позначення того, хто виконує дію, та <strong>object pronouns</strong> (об'єктні) для позначення того, хто отримує дію.</p>",
            'subtitle_text' => "Теоретичний огляд особових та об'єктних займенників англійської мови: subject pronouns (I, you, he, she, it, we, they) та object pronouns (me, you, him, her, it, us, them).",
            'locale' => 'uk',
            'category' => [
                'slug' => 'zaimennyky-ta-vkazivni-slova',
                'title' => 'Займенники та вказівні слова',
                'language' => 'uk',
            ],
            'tags' => [
                'Personal Pronouns',
                'Object Pronouns',
                'Subject Pronouns',
                'I',
                'You',
                'He',
                'She',
                'It',
                'We',
                'They',
                'Me',
                'Him',
                'Her',
                'Us',
                'Them',
                'Pronouns',
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
                        'intro' => "У цій темі ти вивчиш <strong>особові та об'єктні займенники</strong> — дві основні форми, які показують, хто виконує дію і хто її отримує.",
                        'rules' => [
                            [
                                'label' => 'Subject',
                                'color' => 'emerald',
                                'text' => '<strong>Підметові займенники</strong> — хто діє:',
                                'example' => 'I work. She reads. They play.',
                            ],
                            [
                                'label' => 'Object',
                                'color' => 'blue',
                                'text' => "<strong>Об'єктні займенники</strong> — кого/що:",
                                'example' => 'Call me. I see him. Tell us.',
                            ],
                            [
                                'label' => 'Position',
                                'color' => 'amber',
                                'text' => '<strong>Позиція у реченні</strong>:',
                                'example' => 'Subject перед дієсловом, Object після.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Дві форми особових займенників',
                        'intro' => "Особові займенники мають дві форми залежно від їхньої ролі у реченні:",
                        'items' => [
                            ['label' => 'Subject', 'title' => 'Підметові', 'subtitle' => 'I, you, he, she, it, we, they — виконують дію (перед дієсловом)'],
                            ['label' => 'Object', 'title' => "Об'єктні", 'subtitle' => 'me, you, him, her, it, us, them — отримують дію (після дієслова)'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Subject Pronouns — Підметові займенники',
                        'sections' => [
                            [
                                'label' => 'Що таке підметові займенники?',
                                'color' => 'emerald',
                                'description' => "Підметові займенники (subject pronouns) використовуються як <strong>підмет речення</strong> — вони показують, хто виконує дію. Стоять <strong>перед дієсловом</strong>.",
                                'examples' => [
                                    ['en' => 'I work every day.', 'ua' => 'Я працюю щодня.'],
                                    ['en' => 'You are smart.', 'ua' => 'Ти розумний / Ви розумні.'],
                                    ['en' => 'He lives in Kyiv.', 'ua' => 'Він живе в Києві.'],
                                    ['en' => 'She speaks English.', 'ua' => 'Вона говорить англійською.'],
                                    ['en' => 'It is a cat.', 'ua' => 'Це кіт.'],
                                    ['en' => 'We study together.', 'ua' => 'Ми вчимося разом.'],
                                    ['en' => 'They play football.', 'ua' => 'Вони грають у футбол.'],
                                ],
                            ],
                            [
                                'label' => 'I — я',
                                'color' => 'sky',
                                'description' => "<strong>I</strong> (я) — завжди пишеться з великої літери, навіть усередині речення.",
                                'examples' => [
                                    ['en' => 'I like coffee.', 'ua' => 'Я люблю каву.'],
                                    ['en' => 'My friend and I went to the park.', 'ua' => 'Мій друг і я пішли в парк.'],
                                    ['en' => 'I am a student.', 'ua' => 'Я студент.'],
                                ],
                                'note' => "📌 Завжди I (велика літера), ніколи i (маленька)!",
                            ],
                            [
                                'label' => 'You — ти / ви',
                                'color' => 'purple',
                                'description' => "<strong>You</strong> (ти/ви) — універсальна форма для однини й множини, формального й неформального звертання.",
                                'examples' => [
                                    ['en' => 'You are my friend. (ти)', 'ua' => 'Ти мій друг.'],
                                    ['en' => 'You are welcome here. (ви)', 'ua' => 'Ви тут завжди раді.'],
                                    ['en' => 'Are you ready?', 'ua' => 'Ти готовий? / Ви готові?'],
                                ],
                            ],
                            [
                                'label' => 'He, She, It — він, вона, воно',
                                'color' => 'amber',
                                'description' => "<strong>He</strong> — чоловічий рід, <strong>she</strong> — жіночий рід, <strong>it</strong> — для тварин, предметів, абстрактних понять.",
                                'examples' => [
                                    ['en' => 'He is my brother.', 'ua' => 'Він мій брат.'],
                                    ['en' => 'She works in a bank.', 'ua' => 'Вона працює в банку.'],
                                    ['en' => 'It is raining.', 'ua' => 'Йде дощ.'],
                                    ['en' => 'It is a good book.', 'ua' => 'Це хороша книга.'],
                                ],
                                'note' => '📌 It використовуємо для тварин (the dog → it), погоди, часу, відстані.',
                            ],
                            [
                                'label' => 'We, They — ми, вони',
                                'color' => 'rose',
                                'description' => "<strong>We</strong> (ми) — група, що включає мене. <strong>They</strong> (вони) — група, що не включає мене.",
                                'examples' => [
                                    ['en' => 'We love this city.', 'ua' => 'Ми любимо це місто.'],
                                    ['en' => 'They are students.', 'ua' => 'Вони студенти.'],
                                    ['en' => 'We go to school together.', 'ua' => 'Ми ходимо до школи разом.'],
                                    ['en' => 'They live in London.', 'ua' => 'Вони живуть у Лондоні.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => "3. Object Pronouns — Об'єктні займенники",
                        'sections' => [
                            [
                                'label' => "Що таке об'єктні займенники?",
                                'color' => 'blue',
                                'description' => "Об'єктні займенники (object pronouns) використовуються як <strong>додаток</strong> — вони показують, на кого/що спрямована дія. Стоять <strong>після дієслова або прийменника</strong>.",
                                'examples' => [
                                    ['en' => 'Call me later.', 'ua' => 'Подзвони мені пізніше.'],
                                    ['en' => 'I see you.', 'ua' => 'Я бачу тебе / вас.'],
                                    ['en' => 'She loves him.', 'ua' => 'Вона любить його.'],
                                    ['en' => 'Tell her the truth.', 'ua' => 'Скажи їй правду.'],
                                    ['en' => 'Give it to me.', 'ua' => 'Дай це мені.'],
                                    ['en' => 'They invited us.', 'ua' => 'Вони запросили нас.'],
                                    ['en' => 'I know them well.', 'ua' => 'Я їх добре знаю.'],
                                ],
                            ],
                            [
                                'label' => 'Після дієслів',
                                'color' => 'sky',
                                'description' => "Об'єктні займенники стоять після дієслова як прямий або непрямий додаток.",
                                'examples' => [
                                    ['en' => 'I like him.', 'ua' => 'Він мені подобається.'],
                                    ['en' => 'Help me, please.', 'ua' => 'Допоможи мені, будь ласка.'],
                                    ['en' => 'She knows us.', 'ua' => 'Вона нас знає.'],
                                    ['en' => 'I told them everything.', 'ua' => 'Я їм сказав усе.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'purple',
                                'description' => "Після прийменників (with, for, to, about, at, тощо) завжди використовуємо <strong>об'єктні займенники</strong>.",
                                'examples' => [
                                    ['en' => 'Come with me.', 'ua' => 'Ходімо зі мною.'],
                                    ['en' => 'This is for you.', 'ua' => 'Це для тебе / вас.'],
                                    ['en' => 'Talk to him.', 'ua' => 'Поговори з ним.'],
                                    ['en' => 'She is thinking about her.', 'ua' => 'Вона думає про неї.'],
                                    ['en' => 'They are looking at us.', 'ua' => 'Вони дивляться на нас.'],
                                ],
                                'note' => "📌 Ніколи не I after with, for, to — завжди me!",
                            ],
                            [
                                'label' => 'У коротких відповідях',
                                'color' => 'amber',
                                'description' => "У коротких відповідях використовуємо <strong>об'єктні займенники</strong>.",
                                'examples' => [
                                    ['en' => "Who did it? — Me. (не I!)", 'ua' => 'Хто це зробив? — Я.'],
                                    ['en' => "Who wants coffee? — Me! (розмовне)", 'ua' => 'Хто хоче кави? — Я!'],
                                    ['en' => "Who is she talking to? — Him.", 'ua' => 'З ким вона розмовляє? — З ним.'],
                                ],
                                'note' => "📌 Формально правильно: It's I / It was he. Але розмовно: It's me / It was him.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => "4. Порівняльна таблиця: Subject vs Object",
                        'intro' => "Повна таблиця особових займенників у двох формах:",
                        'rows' => [
                            [
                                'en' => 'I',
                                'ua' => 'я (підмет)',
                                'note' => 'me — мене, мені (додаток)',
                            ],
                            [
                                'en' => 'you',
                                'ua' => 'ти/ви (підмет)',
                                'note' => 'you — тебе/вас (додаток)',
                            ],
                            [
                                'en' => 'he',
                                'ua' => 'він (підмет)',
                                'note' => 'him — його, йому (додаток)',
                            ],
                            [
                                'en' => 'she',
                                'ua' => 'вона (підмет)',
                                'note' => 'her — її, їй (додаток)',
                            ],
                            [
                                'en' => 'it',
                                'ua' => 'воно (підмет)',
                                'note' => 'it — його/її (додаток)',
                            ],
                            [
                                'en' => 'we',
                                'ua' => 'ми (підмет)',
                                'note' => 'us — нас, нам (додаток)',
                            ],
                            [
                                'en' => 'they',
                                'ua' => 'вони (підмет)',
                                'note' => 'them — їх, їм (додаток)',
                            ],
                        ],
                        'warning' => "📌 You та it мають однакову форму як підмет і як додаток!",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Як визначити, яку форму використовувати?',
                        'sections' => [
                            [
                                'label' => 'Запитай: Хто? Що?',
                                'color' => 'emerald',
                                'description' => "Якщо можна запитати <strong>Хто?</strong> або <strong>Що?</strong> (підмет) — використовуй subject pronoun.",
                                'examples' => [
                                    ['en' => 'He called. (Хто подзвонив? — Він)', 'ua' => 'Він подзвонив.'],
                                    ['en' => 'She is here. (Хто тут? — Вона)', 'ua' => 'Вона тут.'],
                                    ['en' => 'They won. (Хто виграв? — Вони)', 'ua' => 'Вони виграли.'],
                                ],
                            ],
                            [
                                'label' => 'Запитай: Кого? Кому? Що?',
                                'color' => 'blue',
                                'description' => "Якщо можна запитати <strong>Кого? Кому? Що?</strong> (додаток) — використовуй object pronoun.",
                                'examples' => [
                                    ['en' => 'He called me. (Кому подзвонив? — Мені)', 'ua' => 'Він подзвонив мені.'],
                                    ['en' => 'I see her. (Кого бачу? — Її)', 'ua' => 'Я бачу її.'],
                                    ['en' => 'Tell them. (Кому сказати? — Їм)', 'ua' => 'Скажи їм.'],
                                ],
                            ],
                            [
                                'label' => 'Позиція у реченні',
                                'color' => 'amber',
                                'description' => "<strong>Перед дієсловом</strong> — subject, <strong>після дієслова/прийменника</strong> — object.",
                                'examples' => [
                                    ['en' => 'I like him. (не He like I)', 'ua' => 'Я → subject, him → object'],
                                    ['en' => 'She helps us. (не Her help we)', 'ua' => 'She → subject, us → object'],
                                    ['en' => 'They know me. (не Them know I)', 'ua' => 'They → subject, me → object'],
                                ],
                                'note' => "📌 Якщо сумніваєшся — подумай про позицію у реченні!",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => "6. Типові помилки україномовних",
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => "Плутанина subject і object форм.",
                                'wrong' => 'Me and John went to the park. / Her is my friend.',
                                'right' => '✅ <span class="font-mono">John and I went to the park. / She is my friend.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => "Subject після прийменника.",
                                'wrong' => 'Come with I. / This is for he.',
                                'right' => '✅ <span class="font-mono">Come with me. / This is for him.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => "Object перед дієсловом.",
                                'wrong' => 'Him likes pizza. / Them are students.',
                                'right' => '✅ <span class="font-mono">He likes pizza. / They are students.</span>',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'purple',
                                'title' => "I з маленької літери.",
                                'wrong' => 'Yesterday i went to school.',
                                'right' => '✅ <span class="font-mono">Yesterday I went to school.</span>',
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
                            "<strong>Subject pronouns</strong> — перед дієсловом (хто діє): <em>I, you, he, she, it, we, they</em>.",
                            "<strong>Object pronouns</strong> — після дієслова/прийменника (кого/що): <em>me, you, him, her, it, us, them</em>.",
                            "<strong>I</strong> завжди пишеться з великої літери, навіть усередині речення.",
                            "<strong>You</strong> та <strong>it</strong> — однакова форма для subject і object.",
                            "Після прийменників (with, for, to, about) завжди <strong>object pronouns</strong>.",
                            "У коротких відповідях зазвичай <strong>object</strong>: Who did it? — <em>Me</em> (не I).",
                            "<strong>He/she/it</strong> — для людей та живих істот. <strong>It</strong> — для предметів, тварин, погоди.",
                            "Перевір себе: Хто? → subject. Кого/Кому? → object.",
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Практика',
                        'select_title' => 'Вправа 1. Обери правильний займенник',
                        'select_intro' => 'Заповни пропуски правильним займенником (subject або object).',
                        'selects' => [
                            ['label' => '___ am a student. (I / me)', 'prompt' => 'Який займенник?'],
                            ['label' => 'Call ___ later. (I / me)', 'prompt' => 'Який займенник?'],
                            ['label' => '___ is my friend. (She / Her)', 'prompt' => 'Який займенник?'],
                            ['label' => 'Come with ___. (we / us)', 'prompt' => 'Який займенник?'],
                            ['label' => '___ like pizza. (They / Them)', 'prompt' => 'Який займенник?'],
                        ],
                        'options' => ['I', 'me', 'he', 'him', 'she', 'her', 'we', 'us', 'they', 'them'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши правильний займенник.',
                        'inputs' => [
                            ['before' => '___ work every day. (я працюю)', 'after' => '→'],
                            ['before' => 'Tell ___ the truth. (скажи їй)', 'after' => '→'],
                            ['before' => '___ are friends. (ми друзі)', 'after' => '→'],
                            ['before' => 'I know ___. (я їх знаю)', 'after' => '→'],
                        ],
                        'rephrase_title' => 'Вправа 3. Виправ помилки',
                        'rephrase_intro' => "Знайди і виправ помилку із займенниками.",
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'Me like ice cream.',
                                'example_target' => 'I like ice cream.',
                            ],
                            [
                                'original' => '1. Her is my sister.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '2. Come with I.',
                                'placeholder' => 'Виправ помилку',
                            ],
                            [
                                'original' => '3. Them are students.',
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
                                'label' => "Personal & Object Pronouns — Особові й об'єктні (поточна)",
                                'current' => true,
                            ],
                            [
                                'label' => 'Possessive Forms — Присвійні форми',
                                'current' => false,
                            ],
                            [
                                'label' => 'Reflexive Pronouns — Зворотні займенники',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
