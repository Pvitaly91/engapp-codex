<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\Basics;

class PassiveVoiceV2FormalityStyleTheorySeeder extends PassiveVoiceV2BasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-formality-style';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Формальність та стиль пасиву',
            'subtitle_html' => '<p><strong>Формальність та стиль пасиву</strong> — пасивний стан типовий для формального, наукового та офіційного стилю. Вибір між активом і пасивом впливає на тон тексту та сприйняття інформації.</p>',
            'subtitle_text' => 'Формальність та стиль пасиву: науковий, діловий, офіційний стиль, коли використовувати пасив і коли уникати.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2',
                'title' => 'Пасивний стан V2',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Formality',
                'Style',
                'Formal',
                'Academic',
                'B2',
                'C1',
                'Theory',
            ],
            'blocks' => [
                // Hero block
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2–C1',
                        'intro' => 'У цій темі ти дізнаєшся, як <strong>стиль та формальність</strong> впливають на вибір між активом і пасивом, та коли використання пасиву є <strong>доречним</strong> або <strong>небажаним</strong>.',
                        'rules' => [
                            [
                                'label' => 'Формальний',
                                'color' => 'emerald',
                                'text' => 'Пасив надає тексту <strong>формальності та об\'єктивності</strong>:',
                                'example' => 'The experiment was conducted in 2023.',
                            ],
                            [
                                'label' => 'Науковий',
                                'color' => 'blue',
                                'text' => 'У <strong>наукових текстах</strong> пасив — норма:',
                                'example' => 'The data were analyzed using SPSS.',
                            ],
                            [
                                'label' => 'Розмовний',
                                'color' => 'amber',
                                'text' => 'У <strong>розмовній мові</strong> актив звучить природніше:',
                                'example' => 'We analyzed the data. (не: The data were analyzed by us)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - де типовий пасив
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Де пасив типовий і доречний',
                        'sections' => [
                            [
                                'label' => 'Наукові тексти',
                                'color' => 'emerald',
                                'description' => 'У <strong>наукових статтях</strong> та дослідженнях:',
                                'examples' => [
                                    ['en' => 'The experiment was conducted under controlled conditions.', 'ua' => 'Експеримент проводився в контрольованих умовах.'],
                                    ['en' => 'The samples were collected from five locations.', 'ua' => 'Зразки були зібрані з п\'яти місць.'],
                                    ['en' => 'It was observed that temperature affects the results.', 'ua' => 'Було виявлено, що температура впливає на результати.'],
                                ],
                            ],
                            [
                                'label' => 'Офіційні документи',
                                'color' => 'blue',
                                'description' => 'У <strong>законах, контрактах, інструкціях</strong>:',
                                'examples' => [
                                    ['en' => 'Smoking is prohibited in this area.', 'ua' => 'Куріння заборонено в цій зоні.'],
                                    ['en' => 'Applications must be submitted by Friday.', 'ua' => 'Заявки мають бути подані до п\'ятниці.'],
                                    ['en' => 'This contract is governed by UK law.', 'ua' => 'Цей контракт регулюється законодавством Великобританії.'],
                                ],
                            ],
                            [
                                'label' => 'Новини та медіа',
                                'color' => 'amber',
                                'description' => 'У <strong>новинних повідомленнях</strong>:',
                                'examples' => [
                                    ['en' => 'The suspect was arrested yesterday.', 'ua' => 'Підозрюваного заарештували вчора.'],
                                    ['en' => 'The new law has been approved.', 'ua' => 'Новий закон схвалено.'],
                                    ['en' => 'Several people were injured in the accident.', 'ua' => 'Кілька людей постраждали в аварії.'],
                                ],
                            ],
                            [
                                'label' => 'Технічні інструкції',
                                'color' => 'rose',
                                'description' => 'У <strong>технічній документації</strong>:',
                                'examples' => [
                                    ['en' => 'The device should be charged for 2 hours.', 'ua' => 'Пристрій слід заряджати 2 години.'],
                                    ['en' => 'Cookies are used to improve user experience.', 'ua' => 'Cookies використовуються для покращення користувацького досвіду.'],
                                    ['en' => 'Updates are installed automatically.', 'ua' => 'Оновлення встановлюються автоматично.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - formal vs informal
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Формальний vs Неформальний стиль',
                        'intro' => 'Порівняння одного повідомлення у різних стилях:',
                        'rows' => [
                            [
                                'en' => 'Formal: The report was submitted on time.',
                                'ua' => 'Звіт було подано вчасно.',
                                'note' => 'Офіційний документ, email.',
                            ],
                            [
                                'en' => 'Informal: I submitted the report on time.',
                                'ua' => 'Я подав звіт вчасно.',
                                'note' => 'Розмова з колегою.',
                            ],
                            [
                                'en' => 'Formal: Mistakes were made.',
                                'ua' => 'Були допущені помилки.',
                                'note' => 'Офіційна заява (без вказівки винуватця).',
                            ],
                            [
                                'en' => 'Informal: We made mistakes.',
                                'ua' => 'Ми допустили помилки.',
                                'note' => 'Визнання відповідальності.',
                            ],
                        ],
                        'warning' => '📌 Пасив може використовуватися для уникнення відповідальності або звинувачень!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - коли уникати пасиву
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '3. Коли краще уникати пасиву',
                        'sections' => [
                            [
                                'label' => 'Розмовна мова',
                                'color' => 'rose',
                                'description' => 'У <strong>повсякденному спілкуванні</strong> пасив звучить неприродно:',
                                'examples' => [
                                    ['en' => 'Unnatural: The ball was kicked by John.', 'ua' => 'Неприродно: М\'яч був вдарений Джоном.'],
                                    ['en' => 'Natural: John kicked the ball.', 'ua' => 'Природно: Джон вдарив м\'яч.'],
                                ],
                            ],
                            [
                                'label' => 'Коли важливий виконавець',
                                'color' => 'amber',
                                'description' => 'Якщо <strong>важливо, хто виконав дію</strong>:',
                                'examples' => [
                                    ['en' => 'Awkward: The book was written by Shakespeare.', 'ua' => 'Зайвий пасив для відомого автора.'],
                                    ['en' => 'Better: Shakespeare wrote the book.', 'ua' => 'Краще: Шекспір написав книгу.'],
                                ],
                            ],
                            [
                                'label' => 'Надмірний пасив',
                                'color' => 'blue',
                                'description' => 'Занадто багато пасиву <strong>втомлює читача</strong>:',
                                'examples' => [
                                    ['en' => 'Too much passive: The report was written, was reviewed, and was submitted.', 'ua' => 'Перевантажено пасивом.'],
                                    ['en' => 'Better: We wrote, reviewed, and submitted the report.', 'ua' => 'Краще: Ми написали, перевірили і подали звіт.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - Be-passive vs Get-passive за формальністю
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Be-пасив vs Get-пасив за формальністю',
                        'intro' => 'Вибір між <strong>be</strong> та <strong>get</strong> залежить від стилю:',
                        'items' => [
                            [
                                'label' => 'be + V3',
                                'title' => 'Формальний, нейтральний',
                                'subtitle' => 'The document was signed. (офіційно)',
                            ],
                            [
                                'label' => 'get + V3',
                                'title' => 'Неформальний, розмовний',
                                'subtitle' => 'The document got signed. (розмовно)',
                            ],
                            [
                                'label' => 'Наукові тексти',
                                'title' => 'Тільки be-пасив',
                                'subtitle' => 'The data were analyzed. ✅',
                            ],
                            [
                                'label' => 'Щоденне мовлення',
                                'title' => 'Get-пасив допустимий',
                                'subtitle' => 'He got promoted! (позитивна новина)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - стилістичні ефекти пасиву
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '5. Стилістичні ефекти пасиву',
                        'sections' => [
                            [
                                'label' => 'Об\'єктивність',
                                'color' => 'emerald',
                                'description' => 'Пасив створює враження <strong>об\'єктивності та неупередженості</strong>:',
                                'examples' => [
                                    ['en' => 'It is believed that climate change is accelerating.', 'ua' => 'Вважається, що зміна клімату прискорюється.'],
                                    ['en' => 'The results were verified independently.', 'ua' => 'Результати перевірені незалежно.'],
                                ],
                            ],
                            [
                                'label' => 'Знеособлення',
                                'color' => 'blue',
                                'description' => 'Пасив <strong>приховує виконавця</strong> (іноді навмисно):',
                                'examples' => [
                                    ['en' => 'Mistakes were made.', 'ua' => 'Були допущені помилки. (хто — невідомо)'],
                                    ['en' => 'It has been decided to close the factory.', 'ua' => 'Вирішено закрити завод. (хто вирішив?)'],
                                ],
                            ],
                            [
                                'label' => 'Фокус на дії/об\'єкті',
                                'color' => 'amber',
                                'description' => 'Пасив <strong>підкреслює дію або результат</strong>:',
                                'examples' => [
                                    ['en' => 'The Mona Lisa was painted in the early 16th century.', 'ua' => 'Мона Ліза написана на початку 16 століття.'],
                                    ['en' => 'The building was destroyed in the fire.', 'ua' => 'Будівля знищена пожежею.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - академічні рекомендації
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '6. Академічний стиль: рекомендації',
                        'intro' => 'Сучасні рекомендації щодо пасиву в академічних текстах:',
                        'rows' => [
                            [
                                'en' => 'Traditional: The results were analyzed.',
                                'ua' => 'Традиційно: пасив у методології.',
                                'note' => 'Все ще широко використовується.',
                            ],
                            [
                                'en' => 'Modern: We analyzed the results.',
                                'ua' => 'Сучасно: актив з "we".',
                                'note' => 'Рекомендовано багатьма журналами.',
                            ],
                            [
                                'en' => 'Mixed: The samples were collected. We analyzed them.',
                                'ua' => 'Змішаний стиль.',
                                'note' => 'Баланс об\'єктивності та ясності.',
                            ],
                        ],
                        'warning' => '📌 Перевіряй вимоги конкретного журналу або установи!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Get-пасив у формальному тексті.',
                                'wrong' => 'The experiment got conducted.',
                                'right' => '✅ The experiment was conducted.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Надмірний пасив без потреби.',
                                'wrong' => 'The ball was kicked by John to Mary.',
                                'right' => '✅ John kicked the ball to Mary.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Пасив з очевидним виконавцем.',
                                'wrong' => 'I was asked a question by the teacher.',
                                'right' => '✅ The teacher asked me a question.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Пасив у неформальному контексті.',
                                'wrong' => 'A good time was had by all at the party.',
                                'right' => '✅ Everyone had a good time at the party.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
                        'items' => [
                            'Пасив типовий для <strong>формального, наукового, офіційного</strong> стилю.',
                            'У <strong>розмовній мові</strong> актив звучить природніше.',
                            '<strong>Be-пасив</strong> — формальний; <strong>get-пасив</strong> — розмовний.',
                            'Пасив створює <strong>об\'єктивність</strong> і <strong>знеособлення</strong>.',
                            'Уникай пасиву, коли <strong>важливий виконавець</strong>.',
                            'Не зловживай пасивом — <strong>надмірність втомлює</strong> читача.',
                            'У наукових текстах — <strong>перевіряй вимоги</strong> видання.',
                            'Пасив може <strong>приховувати відповідальність</strong> — використовуй свідомо.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
