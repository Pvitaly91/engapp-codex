<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Advanced;

class PassiveVoiceStyleMistakesTheorySeeder extends PassiveVoiceAdvancedPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-style-mistakes';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Стиль та типові помилки — де пасив доречний',
            'subtitle_html' => '<p><strong>Узагальнююча сторінка</strong> про стилістику пасивного стану: де пасив доречний (академічний стиль, інструкції, новини), де краще використовувати актив. Також розглянемо <strong>типові помилки</strong>: "by people", неправильний V3, зайвий пасив, узгодження часу.</p>',
            'subtitle_text' => 'Стилістика пасиву: де доречний (академічний стиль, інструкції, новини), де краще актив. Типові помилки: by people, неправильний V3, зайвий пасив.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-advanced',
                'title' => 'Просунутий рівень — Складні конструкції',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Style',
                'Common Mistakes',
                'Academic Writing',
                'Instructions',
                'News',
                'B2',
                'C1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2',
                        'intro' => 'У цій темі ти дізнаєшся, <strong>де пасив доречний</strong>, а де краще використовувати активний стан. Також розглянемо <strong>найпоширеніші помилки</strong>, яких допускаються при використанні пасиву.',
                        'rules' => [
                            [
                                'label' => 'Академічний',
                                'color' => 'emerald',
                                'text' => 'Пасив підкреслює <strong>об\'єктивність</strong>:',
                                'example' => 'The experiment was conducted...',
                            ],
                            [
                                'label' => 'Новини',
                                'color' => 'blue',
                                'text' => 'Пасив фокусує на <strong>події, а не виконавці</strong>:',
                                'example' => 'Three people were injured.',
                            ],
                            [
                                'label' => 'Помилка',
                                'color' => 'rose',
                                'text' => 'Зайвий пасив <strong>ускладнює</strong> текст:',
                                'example' => '❌ The ball was kicked by him.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Де пасив ДОРЕЧНИЙ',
                        'sections' => [
                            [
                                'label' => 'Академічний та науковий стиль',
                                'color' => 'emerald',
                                'description' => 'У <strong>наукових статтях та дослідженнях</strong> пасив створює враження об\'єктивності та відстороненості.',
                                'examples' => [
                                    ['en' => 'The experiment was conducted over six months.', 'ua' => 'Експеримент проводився протягом шести місяців.'],
                                    ['en' => 'The data were collected from 500 participants.', 'ua' => 'Дані було зібрано від 500 учасників.'],
                                    ['en' => 'It has been demonstrated that...', 'ua' => 'Було продемонстровано, що...'],
                                    ['en' => 'The results were analyzed using SPSS software.', 'ua' => 'Результати аналізували за допомогою програми SPSS.'],
                                ],
                            ],
                            [
                                'label' => 'Інструкції та рецепти',
                                'color' => 'sky',
                                'description' => 'У <strong>інструкціях</strong> пасив фокусує на процесі, а не на виконавці.',
                                'examples' => [
                                    ['en' => 'The mixture should be stirred for 5 minutes.', 'ua' => 'Суміш слід перемішувати 5 хвилин.'],
                                    ['en' => 'The device must be turned off before cleaning.', 'ua' => 'Пристрій необхідно вимкнути перед чищенням.'],
                                    ['en' => 'First, the ingredients are combined.', 'ua' => 'Спочатку інгредієнти змішуються.'],
                                ],
                            ],
                            [
                                'label' => 'Новини та репортажі',
                                'color' => 'blue',
                                'description' => 'У <strong>новинах</strong> пасив фокусує на події та постраждалих, коли виконавець невідомий або неважливий.',
                                'examples' => [
                                    ['en' => 'Three people were injured in the accident.', 'ua' => 'Троє людей постраждали в аварії.'],
                                    ['en' => 'The bank was robbed yesterday.', 'ua' => 'Банк пограбували вчора.'],
                                    ['en' => 'A new law has been passed by Parliament.', 'ua' => 'Парламент ухвалив новий закон.'],
                                ],
                            ],
                            [
                                'label' => 'Офіційні документи',
                                'color' => 'amber',
                                'description' => 'У <strong>діловому листуванні та документах</strong> пасив додає формальності.',
                                'examples' => [
                                    ['en' => 'Your application has been received.', 'ua' => 'Вашу заявку отримано.'],
                                    ['en' => 'Payment is required within 30 days.', 'ua' => 'Оплата вимагається протягом 30 днів.'],
                                    ['en' => 'Applicants will be notified by email.', 'ua' => 'Заявників повідомлять електронною поштою.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Де КРАЩЕ використовувати актив',
                        'sections' => [
                            [
                                'label' => 'Коли важливий виконавець',
                                'color' => 'emerald',
                                'description' => 'Якщо <strong>виконавець — ключова інформація</strong>, актив звучить природніше.',
                                'examples' => [
                                    ['en' => '❌ The goal was scored by Messi.', 'ua' => 'Гол було забито Мессі. (штучно)'],
                                    ['en' => '✅ Messi scored the goal.', 'ua' => 'Мессі забив гол. (природно)'],
                                    ['en' => '❌ The book was written by J.K. Rowling.', 'ua' => '(якщо про автора — краще актив)'],
                                    ['en' => '✅ J.K. Rowling wrote the book.', 'ua' => 'Дж. К. Роулінг написала книгу.'],
                                ],
                            ],
                            [
                                'label' => 'Розмовна мова',
                                'color' => 'sky',
                                'description' => 'У <strong>щоденному спілкуванні</strong> актив звучить простіше та природніше.',
                                'examples' => [
                                    ['en' => '❌ The cake was eaten by me.', 'ua' => 'Торт було з\'їдено мною. (дивно)'],
                                    ['en' => '✅ I ate the cake.', 'ua' => 'Я з\'їв торт. (природно)'],
                                    ['en' => '❌ The door was opened by Tom.', 'ua' => 'Двері було відчинено Томом.'],
                                    ['en' => '✅ Tom opened the door.', 'ua' => 'Том відчинив двері.'],
                                ],
                            ],
                            [
                                'label' => 'Коротші речення',
                                'color' => 'amber',
                                'description' => 'Актив часто <strong>коротший і чіткіший</strong>, особливо з by-phrase.',
                                'examples' => [
                                    ['en' => '❌ Mistakes were made by me.', 'ua' => 'Помилки були зроблені мною. (8 слів)'],
                                    ['en' => '✅ I made mistakes.', 'ua' => 'Я зробив помилки. (3 слова)'],
                                    ['en' => '❌ The report was completed by the team.', 'ua' => 'Звіт було завершено командою.'],
                                    ['en' => '✅ The team completed the report.', 'ua' => 'Команда завершила звіт.'],
                                ],
                            ],
                            [
                                'label' => 'Динамічні дії',
                                'color' => 'rose',
                                'description' => 'Для <strong>швидких, динамічних дій</strong> актив передає енергію краще.',
                                'examples' => [
                                    ['en' => '❌ The ball was kicked by the player.', 'ua' => 'М\'яч було вдарено гравцем.'],
                                    ['en' => '✅ The player kicked the ball.', 'ua' => 'Гравець вдарив м\'яч.'],
                                    ['en' => '❌ The car was driven by her.', 'ua' => 'Автомобіль вела вона.'],
                                    ['en' => '✅ She drove the car.', 'ua' => 'Вона вела автомобіль.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Помилка: "by people/someone" — зайвий агент',
                        'items' => [
                            [
                                'label' => 'Помилка',
                                'color' => 'rose',
                                'title' => 'Зайвий "by people" або "by someone".',
                                'wrong' => 'English is spoken by people here.',
                                'right' => '✅ English is spoken here.',
                            ],
                            [
                                'label' => 'Помилка',
                                'color' => 'amber',
                                'title' => 'Зайвий "by them" без конкретики.',
                                'wrong' => 'The decision was made by them.',
                                'right' => '✅ The decision was made. (якщо "вони" неважливі)',
                            ],
                            [
                                'label' => 'Помилка',
                                'color' => 'sky',
                                'title' => 'Загальний виконавець у пасиві.',
                                'wrong' => 'It is believed by everybody that...',
                                'right' => '✅ It is believed that... / Everybody believes that...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Помилка: Неправильний V3 (Past Participle)',
                        'items' => [
                            [
                                'label' => 'Неправильні дієслова',
                                'color' => 'rose',
                                'title' => 'Плутанина з неправильними формами.',
                                'wrong' => 'The window was breaked. / was broke.',
                                'right' => '✅ The window was broken.',
                            ],
                            [
                                'label' => 'Закінчення -ed',
                                'color' => 'amber',
                                'title' => 'Додавання -ed до неправильних дієслів.',
                                'wrong' => 'The letter was writed. / was sended.',
                                'right' => '✅ The letter was written. / was sent.',
                            ],
                            [
                                'label' => 'V2 замість V3',
                                'color' => 'sky',
                                'title' => 'Використання Past Simple замість V3.',
                                'wrong' => 'The book was wrote by her.',
                                'right' => '✅ The book was written by her.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Помилка: Узгодження часу',
                        'items' => [
                            [
                                'label' => 'Час be + V3',
                                'color' => 'rose',
                                'title' => 'Неправильний час допоміжного be.',
                                'wrong' => 'Yesterday, the report is written.',
                                'right' => '✅ Yesterday, the report was written.',
                            ],
                            [
                                'label' => 'Present vs Past',
                                'color' => 'amber',
                                'title' => 'Плутанина теперішнього та минулого.',
                                'wrong' => 'The project was completed tomorrow.',
                                'right' => '✅ The project will be completed tomorrow.',
                            ],
                            [
                                'label' => 'Perfect tenses',
                                'color' => 'sky',
                                'title' => 'Пропуск been у перфектних часах.',
                                'wrong' => 'The work has done.',
                                'right' => '✅ The work has been done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Помилка: Зайвий пасив',
                        'items' => [
                            [
                                'label' => 'Надмірна формальність',
                                'color' => 'rose',
                                'title' => 'Пасив там, де актив краще.',
                                'wrong' => 'The coffee was drunk by me this morning.',
                                'right' => '✅ I drank coffee this morning.',
                            ],
                            [
                                'label' => 'Складність',
                                'color' => 'amber',
                                'title' => 'Ускладнення простих речень.',
                                'wrong' => 'A good time was had by all.',
                                'right' => '✅ Everyone had a good time.',
                            ],
                            [
                                'label' => 'Втрата ясності',
                                'color' => 'sky',
                                'title' => 'Коли актив чіткіший.',
                                'wrong' => 'It was decided that the meeting would be postponed by the manager.',
                                'right' => '✅ The manager decided to postpone the meeting.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Коли пасив, а коли актив — швидка перевірка',
                        'intro' => 'Таблиця для вибору між активом та пасивом:',
                        'rows' => [
                            [
                                'en' => 'Виконавець невідомий',
                                'ua' => '→ ПАСИВ',
                                'note' => 'My car was stolen.',
                            ],
                            [
                                'en' => 'Виконавець неважливий',
                                'ua' => '→ ПАСИВ',
                                'note' => 'English is spoken here.',
                            ],
                            [
                                'en' => 'Виконавець важливий',
                                'ua' => '→ АКТИВ',
                                'note' => 'Messi scored the goal.',
                            ],
                            [
                                'en' => 'Науковий/офіційний стиль',
                                'ua' => '→ ПАСИВ',
                                'note' => 'The data were analyzed.',
                            ],
                            [
                                'en' => 'Розмовна мова',
                                'ua' => '→ АКТИВ',
                                'note' => 'I ate the cake.',
                            ],
                            [
                                'en' => 'З "by + очевидний agent"',
                                'ua' => '→ Краще АКТИВ',
                                'note' => 'Tom opened the door.',
                            ],
                        ],
                        'warning' => '📌 Запитай себе: <strong>Хто виконує дію?</strong> Якщо важливо — актив. Якщо ні — пасив.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '8. Поради для академічного письма',
                        'sections' => [
                            [
                                'label' => 'Коли пасив доречний',
                                'color' => 'emerald',
                                'description' => 'В <strong>академічному письмі</strong> пасив підкреслює процес та результат, а не автора.',
                                'examples' => [
                                    ['en' => 'The samples were collected from three locations.', 'ua' => 'Зразки було зібрано з трьох локацій.'],
                                    ['en' => 'Statistical analysis was performed using SPSS.', 'ua' => 'Статистичний аналіз виконано за допомогою SPSS.'],
                                    ['en' => 'It was observed that temperature affects growth.', 'ua' => 'Було помічено, що температура впливає на ріст.'],
                                ],
                            ],
                            [
                                'label' => 'Коли актив краще',
                                'color' => 'sky',
                                'description' => 'Навіть в академічному стилі, <strong>актив</strong> іноді чіткіший.',
                                'examples' => [
                                    ['en' => 'Previous studies have shown that... (актив)', 'ua' => 'Попередні дослідження показали, що...'],
                                    ['en' => 'We analyzed the data using... (актив)', 'ua' => 'Ми проаналізували дані за допомогою...'],
                                    ['en' => 'The authors conclude that... (актив)', 'ua' => 'Автори роблять висновок, що...'],
                                ],
                            ],
                            [
                                'label' => 'Баланс',
                                'color' => 'amber',
                                'description' => '<strong>Комбінуй</strong> актив і пасив для різноманітності та ясності.',
                                'examples' => [
                                    ['en' => 'We collected samples (актив), which were then analyzed (пасив).', 'ua' => 'Ми зібрали зразки, які потім проаналізували.'],
                                    ['en' => 'The experiment was designed (пасив) to test our hypothesis (актив).', 'ua' => 'Експеримент було розроблено для перевірки нашої гіпотези.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Пасив доречний</strong>: науковий стиль, інструкції, новини, офіційні документи.',
                            '<strong>Актив краще</strong>: коли виконавець важливий, у розмовній мові, для динамічних дій.',
                            '<strong>Уникай "by people/someone"</strong> — це зайва інформація.',
                            '<strong>Перевіряй V3</strong>: written (не writed), broken (не breaked).',
                            '<strong>Узгоджуй час</strong>: was/were для минулого, is/are для теперішнього.',
                            '<strong>Не перевантажуй</strong> текст пасивом — комбінуй з активом.',
                            'Запитай: <strong>Хто виконує дію?</strong> Важливо = актив. Ні = пасив.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
