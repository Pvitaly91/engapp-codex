<?php

namespace Database\Seeders\Page_v2\Questions\TypesOfQuestions;

use Database\Seeders\Pages\Questions\TypesOfQuestions\TypesOfQuestionsPageSeeder;

class WhQuestionsTheorySeeder extends TypesOfQuestionsPageSeeder
{
    public function slug(): string
    {
        return 'wh-questions-special-questions-who-what-where-when-why-how';
    }

    public function type(): string
    {
        return 'theory';
    }

    public function page(): array
    {
        return [
            'level' => 'A2-B1',
            'title' => 'Wh-questions — Спеціальні питання',
            'subtitle' => 'Питання з who, what, where, when, why, how',
            'tags' => [
                'Questions',
                'Wh-questions',
                'Special Questions',
                'Who',
                'What',
                'Where',
                'When',
                'Why',
                'How',
                'Question Words',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'title' => 'Wh-questions — Спеціальні питання',
                        'subtitle' => 'Як ставити питання з who, what, where, when, why, how',
                        'text' => 'Wh-questions (спеціальні питання) використовуються для отримання конкретної інформації. Вони починаються з питальних слів: who (хто), what (що), where (де), when (коли), why (чому), how (як). Розглянемо кожне питальне слово детально.',
                    ]),
                ],

                // Introduction
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Що таке Wh-questions?',
                        'content' => "Wh-questions — це спеціальні питання, які починаються з питальних слів (question words). На відміну від yes/no questions, які вимагають відповіді \"так\" або \"ні\", wh-questions вимагають детальної відповіді з конкретною інформацією.",
                        'examples' => [
                            [
                                'en' => 'Are you a student? — Yes, I am.',
                                'ua' => 'Ти студент? — Так.',
                                'note' => 'Yes/No question',
                            ],
                            [
                                'en' => 'What do you do? — I\'m a teacher.',
                                'ua' => 'Чим ти займаєшся? — Я вчитель.',
                                'note' => 'Wh-question (потребує детальної відповіді)',
                            ],
                        ],
                    ]),
                ],

                // Structure
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Структура Wh-questions',
                        'content' => "Загальна структура: **Wh-word + допоміжне дієслово + підмет + основне дієслово**\n\nВиняток: коли who/what є підметом питання, допоміжне дієслово не потрібне.",
                        'examples' => [
                            [
                                'en' => 'Where do you live?',
                                'ua' => 'Де ти живеш?',
                                'note' => 'Where + do + you + live',
                            ],
                            [
                                'en' => 'What did she say?',
                                'ua' => 'Що вона сказала?',
                                'note' => 'What + did + she + say',
                            ],
                            [
                                'en' => 'Who lives here?',
                                'ua' => 'Хто тут живе?',
                                'note' => 'Who є підметом — без допоміжного дієслова',
                            ],
                        ],
                    ]),
                ],

                // WHO
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHO — Хто',
                        'content' => "**Who** використовується для питань про людей.\n\n• Як підмет: Who lives here? (Хто тут живе?)\n• Як об'єкт: Who did you see? (Кого ти бачив?)",
                        'examples' => [
                            [
                                'en' => 'Who is your teacher?',
                                'ua' => 'Хто твій вчитель?',
                            ],
                            [
                                'en' => 'Who called you yesterday?',
                                'ua' => 'Хто тобі вчора дзвонив?',
                            ],
                            [
                                'en' => 'Who are you talking to?',
                                'ua' => 'З ким ти розмовляєш?',
                            ],
                        ],
                    ]),
                ],

                // WHAT
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHAT — Що / Який',
                        'content' => "**What** використовується для питань про речі, дії, інформацію.\n\n• What time...? — О котрій годині?\n• What kind/type...? — Який тип/вид?\n• What color...? — Якого кольору?",
                        'examples' => [
                            [
                                'en' => 'What do you want?',
                                'ua' => 'Що ти хочеш?',
                            ],
                            [
                                'en' => 'What time is it?',
                                'ua' => 'Котра година?',
                            ],
                            [
                                'en' => 'What color is your car?',
                                'ua' => 'Якого кольору твоя машина?',
                            ],
                        ],
                    ]),
                ],

                // WHERE
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHERE — Де / Куди / Звідки',
                        'content' => "**Where** використовується для питань про місце.\n\n• Where...? — Де?\n• Where...from? — Звідки?\n• Where...to? — Куди?",
                        'examples' => [
                            [
                                'en' => 'Where do you live?',
                                'ua' => 'Де ти живеш?',
                            ],
                            [
                                'en' => 'Where are you from?',
                                'ua' => 'Звідки ти?',
                            ],
                            [
                                'en' => 'Where are you going?',
                                'ua' => 'Куди ти йдеш?',
                            ],
                        ],
                    ]),
                ],

                // WHEN
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHEN — Коли',
                        'content' => "**When** використовується для питань про час.",
                        'examples' => [
                            [
                                'en' => 'When did you arrive?',
                                'ua' => 'Коли ти приїхав?',
                            ],
                            [
                                'en' => 'When is your birthday?',
                                'ua' => 'Коли у тебе день народження?',
                            ],
                            [
                                'en' => 'When will you finish?',
                                'ua' => 'Коли ти закінчиш?',
                            ],
                        ],
                    ]),
                ],

                // WHY
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHY — Чому / Навіщо',
                        'content' => "**Why** використовується для питань про причину.\n\nВідповідь часто починається з **because** (тому що).",
                        'examples' => [
                            [
                                'en' => 'Why are you late?',
                                'ua' => 'Чому ти спізнився?',
                            ],
                            [
                                'en' => 'Why did she leave?',
                                'ua' => 'Чому вона пішла?',
                            ],
                            [
                                'en' => 'Why do you study English? — Because I need it for work.',
                                'ua' => 'Навіщо ти вивчаєш англійську? — Тому що вона мені потрібна для роботи.',
                            ],
                        ],
                    ]),
                ],

                // HOW
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'HOW — Як / Яким чином',
                        'content' => "**How** використовується для питань про спосіб, метод, стан.\n\n• How old...? — Скільки років?\n• How much/many...? — Скільки?\n• How long...? — Як довго?\n• How far...? — Як далеко?\n• How often...? — Як часто?",
                        'examples' => [
                            [
                                'en' => 'How do you go to work?',
                                'ua' => 'Як ти добираєшся на роботу?',
                            ],
                            [
                                'en' => 'How old are you?',
                                'ua' => 'Скільки тобі років?',
                            ],
                            [
                                'en' => 'How much does it cost?',
                                'ua' => 'Скільки це коштує?',
                            ],
                            [
                                'en' => 'How often do you exercise?',
                                'ua' => 'Як часто ти займаєшся спортом?',
                            ],
                        ],
                    ]),
                ],

                // WHICH and WHOSE
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'WHICH — Який / Котрий  |  WHOSE — Чий',
                        'content' => "**Which** — вибір між обмеженою кількістю варіантів\n**Whose** — питання про власника",
                        'examples' => [
                            [
                                'en' => 'Which color do you prefer — blue or green?',
                                'ua' => 'Який колір ти віддаєш перевагу — синій чи зелений?',
                            ],
                            [
                                'en' => 'Which bus goes to the city center?',
                                'ua' => 'Котрий автобус їде в центр міста?',
                            ],
                            [
                                'en' => 'Whose book is this?',
                                'ua' => 'Чия це книжка?',
                            ],
                            [
                                'en' => 'Whose turn is it?',
                                'ua' => 'Чия черга?',
                            ],
                        ],
                    ]),
                ],

                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Порівняння питальних слів',
                        'rows' => [
                            [
                                'en' => 'Who — Хто',
                                'ua' => 'Питання про людей',
                                'note' => 'Who is he? / Who did you meet?',
                            ],
                            [
                                'en' => 'What — Що / Який',
                                'ua' => 'Питання про речі, дії, інформацію',
                                'note' => 'What is this? / What do you do?',
                            ],
                            [
                                'en' => 'Where — Де / Куди',
                                'ua' => 'Питання про місце',
                                'note' => 'Where do you live?',
                            ],
                            [
                                'en' => 'When — Коли',
                                'ua' => 'Питання про час',
                                'note' => 'When did it happen?',
                            ],
                            [
                                'en' => 'Why — Чому / Навіщо',
                                'ua' => 'Питання про причину',
                                'note' => 'Why are you late?',
                            ],
                            [
                                'en' => 'How — Як',
                                'ua' => 'Питання про спосіб, стан',
                                'note' => 'How do you feel?',
                            ],
                            [
                                'en' => 'Which — Який / Котрий',
                                'ua' => 'Вибір між варіантами',
                                'note' => 'Which one do you want?',
                            ],
                            [
                                'en' => 'Whose — Чий',
                                'ua' => 'Питання про власника',
                                'note' => 'Whose car is this?',
                            ],
                        ],
                    ]),
                ],

                // Common mistakes
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Типові помилки',
                        'content' => "**1. Неправильний порядок слів**\n❌ Where you live?\n✅ Where do you live?\n\n**2. Забули допоміжне дієслово**\n❌ What you want?\n✅ What do you want?\n\n**3. Зайве допоміжне дієслово з who/what як підметом**\n❌ Who does live here?\n✅ Who lives here?\n\n**4. Плутання what і which**\n❌ What color do you prefer — red or blue? (краще which)\n✅ Which color do you prefer — red or blue?\n\n**5. Плутання who і whose**\n❌ Who book is this?\n✅ Whose book is this?",
                    ]),
                ],

                // Summary
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Підсумок',
                        'content' => "**Ключові моменти:**\n\n✓ Wh-questions починаються з питальних слів (who, what, where, when, why, how)\n✓ Структура: Wh-word + допоміжне дієслово + підмет + основне дієслово\n✓ Виняток: who/what як підмет не потребує допоміжного дієслова\n✓ Who — про людей, What — про речі, Where — про місце\n✓ When — про час, Why — про причину, How — про спосіб\n✓ Which — вибір між варіантами, Whose — про власника\n✓ Відповіді на wh-questions містять конкретну інформацію, а не просто yes/no",
                    ]),
                ],

                // Practice
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Вправа: Виберіть правильне питальне слово',
                        'content' => "**Доповніть питання правильним wh-словом:**\n\n1. ___ is your name? (Як тебе звати?)\n2. ___ do you live? (Де ти живеш?)\n3. ___ are you sad? (Чому ти сумний?)\n4. ___ is your birthday? (Коли у тебе день народження?)\n5. ___ are you? (Як ти? / Як твої справи?)\n6. ___ called you? (Хто тобі дзвонив?)\n7. ___ bag is this? (Чия це сумка?)\n8. ___ book do you want — this one or that one? (Яку книжку ти хочеш?)\n\n**Відповіді:**\n1. What, 2. Where, 3. Why, 4. When, 5. How, 6. Who, 7. Whose, 8. Which",
                    ]),
                ],

                // Navigation block
                [
                    'type' => 'navigation',
                    'column' => 'footer',
                    'body' => json_encode([
                        'previous' => null,
                        'next' => null,
                    ]),
                ],
            ],
        ];
    }
}
