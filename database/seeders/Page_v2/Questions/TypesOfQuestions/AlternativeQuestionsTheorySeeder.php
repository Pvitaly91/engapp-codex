<?php

namespace Database\Seeders\Page_v2\Questions\TypesOfQuestions;

use Database\Seeders\Pages\Questions\TypesOfQuestions\TypesOfQuestionsPageSeeder;

class AlternativeQuestionsTheorySeeder extends TypesOfQuestionsPageSeeder
{
    public function slug(): string
    {
        return 'alternative-questions-coffee-or-tea';
    }

    public function type(): string
    {
        return 'theory';
    }

    public function page(): array
    {
        return [
            'level' => 'A2',
            'title' => 'Alternative Questions — Альтернативні питання',
            'subtitle' => 'Питання з вибором: coffee or tea?',
            'tags' => [
                'Questions',
                'Alternative Questions',
                'Or Questions',
                'Choice Questions',
                'Question Types',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'title' => 'Alternative Questions — Альтернативні питання',
                        'subtitle' => 'Питання з вибором між варіантами',
                        'text' => "Альтернативні питання (alternative questions) пропонують вибір між двома або більше варіантами. Вони завжди містять сполучник **or** (або). Відповідь на такі питання не може бути просто Yes/No — потрібно обрати один із запропонованих варіантів.",
                    ]),
                ],

                // Structure
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '📋 Структура альтернативного питання',
                        'content' => "Альтернативні питання будуються так само, як загальні питання (general questions), але містять **or** між варіантами вибору:\n\n**Auxiliary verb + subject + main verb + option 1 + or + option 2?**\n\n**Приклади:**\n- Do you want **coffee or tea**? — Ти хочеш каву чи чай?\n- Is she a teacher **or a doctor**? — Вона вчителька чи лікар?\n- Will you go **by car or by train**? — Ти поїдеш машиною чи поїздом?\n- Can you speak **English or French**? — Ти розмовляєш англійською чи французькою?",
                    ]),
                ],

                // Intonation
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '🎵 Інтонація',
                        'content' => "У альтернативних питаннях **інтонація піднімається** на першому варіанті і **опускається** на другому:\n\n- Do you want coffee ↗ or tea ↘?\n- Is it black ↗ or white ↘?\n- Should I call ↗ or text ↘?\n\nЦе відрізняє альтернативні питання від загальних, де інтонація тільки піднімається.",
                    ]),
                ],

                // Types of choices
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '🔀 Типи вибору',
                        'content' => "**1. Вибір між іменниками:**\n- Would you like **tea or coffee**?\n- Is this your pen **or his pen**?\n\n**2. Вибір між прикметниками:**\n- Is the answer right **or wrong**?\n- Is the water hot **or cold**?\n\n**3. Вибір між дієсловами:**\n- Do you want to stay **or leave**?\n- Should we walk **or take a bus**?\n\n**4. Вибір між фразами:**\n- Will you come **in the morning or in the evening**?\n- Do you prefer **working alone or in a team**?\n\n**5. Вибір з більше ніж двох варіантів:**\n- Do you want tea, coffee, **or juice**?\n- Should we meet on Monday, Tuesday, **or Wednesday**?",
                    ]),
                ],

                // With different tenses
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '⏰ У різних часах',
                        'content' => "**Present Simple:**\n- Do you **live** in Kyiv or Lviv?\n- Does she **work** or study?\n\n**Present Continuous:**\n- Are you **reading** or writing?\n- Is he **coming** by car or by bus?\n\n**Past Simple:**\n- Did you **go** to the cinema or the theatre?\n- Was it expensive or cheap?\n\n**Future Simple:**\n- Will you **call** or text me?\n- Will they arrive today or tomorrow?\n\n**Modal verbs:**\n- Can you **swim** or dive?\n- Should I wear a dress or jeans?",
                    ]),
                ],

                // Answering
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '💬 Як відповідати',
                        'content' => "На альтернативні питання НЕ можна відповісти Yes/No. Потрібно обрати один з варіантів або назвати третій:\n\n**Вибір першого варіанта:**\n- Do you want tea or coffee? — **Tea, please.** / **I want tea.**\n\n**Вибір другого варіанта:**\n- Will you go by car or by train? — **By train.** / **I'll go by train.**\n\n**Третій варіант (якщо жоден не підходить):**\n- Do you want tea or coffee? — **Neither. I'd like water.** / **Actually, juice.**\n\n**Обидва варіанти:**\n- Do you like cats or dogs? — **Both!** / **I like both.**",
                    ]),
                ],

                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'rows' => [
                            [
                                'en' => 'Do you like coffee?',
                                'ua' => 'Тобі подобається кава?',
                                'note' => 'General question (Yes/No)',
                            ],
                            [
                                'en' => 'Do you like coffee or tea?',
                                'ua' => 'Тобі подобається кава чи чай?',
                                'note' => 'Alternative question (coffee/tea/both/neither)',
                            ],
                            [
                                'en' => 'Is he a student?',
                                'ua' => 'Він студент?',
                                'note' => 'General question (Yes/No)',
                            ],
                            [
                                'en' => 'Is he a student or a teacher?',
                                'ua' => 'Він студент чи вчитель?',
                                'note' => 'Alternative question (student/teacher)',
                            ],
                        ],
                    ]),
                ],

                // Common mistakes
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '⚠️ Поширені помилки',
                        'content' => "**1. Відповідь Yes/No:**\n❌ Do you want tea or coffee? — Yes.\n✅ Do you want tea or coffee? — Tea, please.\n\n**2. Пропуск 'or':**\n❌ Do you want tea, coffee?\n✅ Do you want tea or coffee?\n\n**3. Неправильний порядок слів:**\n❌ You want tea or coffee?\n✅ Do you want tea or coffee?\n\n**4. Забули допоміжне дієслово:**\n❌ You prefer cats or dogs?\n✅ Do you prefer cats or dogs?\n\n**5. Плутанина з інтонацією:**\n- Alternative: Do you want coffee ↗ or tea ↘?\n- General (Yes/No): Do you want coffee ↗?",
                    ]),
                ],

                // Summary
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '📝 Підсумок',
                        'content' => "**Альтернативні питання:**\n- Пропонують вибір між варіантами\n- Завжди містять **or**\n- Будуються як загальні питання + or + варіанти\n- Інтонація: ↗ на першому варіанті, ↘ на другому\n- Відповідь: обрати варіант, НЕ Yes/No\n- Можна обрати третій варіант або обидва (both)\n- Можна відмовитись від обох (neither)",
                    ]),
                ],

                // Practice
                [
                    'type' => 'usage-panel',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '✍️ Практика',
                        'content' => "**Утворіть альтернативні питання:**\n\n1. You like tea. (coffee)\n   → Do you like tea **or coffee**?\n\n2. She is a doctor. (nurse)\n   → Is she a doctor **or a nurse**?\n\n3. They will come tomorrow. (today)\n   → Will they come tomorrow **or today**?\n\n4. He can speak English. (French)\n   → Can he speak English **or French**?\n\n5. You are going by bus. (by metro)\n   → Are you going by bus **or by metro**?",
                    ]),
                ],

                // Navigation
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
