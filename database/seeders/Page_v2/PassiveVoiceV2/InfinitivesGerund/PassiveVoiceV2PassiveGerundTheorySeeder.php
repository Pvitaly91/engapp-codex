<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\InfinitivesGerund;

class PassiveVoiceV2PassiveGerundTheorySeeder extends PassiveVoiceV2InfinitivesGerundPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-v2-passive-gerund';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Пасивний герундій — being done / having been done',
            'subtitle_html' => '<p><strong>Пасивний герундій (Passive Gerund)</strong> — це форма герундія, яка показує, що дія виконується над підметом, а не ним. Існують дві основні форми: <strong>being done</strong> (простий пасивний герундій) та <strong>having been done</strong> (перфектний пасивний герундій).</p>',
            'subtitle_text' => 'Пасивний герундій: being done (простий) та having been done (перфектний). Утворення, використання після прийменників та дієслів почуттів, приклади.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-v2-infinitives-gerund',
                'title' => 'Інфінітив та герундій у пасиві',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Gerund',
                'Пасивний герундій',
                'being done',
                'having been done',
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
                        'intro' => 'У цій темі ти детально вивчиш <strong>пасивний герундій</strong>: як утворювати простий (being done) та перфектний (having been done) пасивний герундій, коли їх використовувати та після яких слів вони вживаються.',
                        'rules' => [
                            [
                                'label' => 'Простий',
                                'color' => 'emerald',
                                'text' => '<strong>being + V3 (Past Participle)</strong> — одночасна дія:',
                                'example' => 'She hates being interrupted.',
                            ],
                            [
                                'label' => 'Перфектний',
                                'color' => 'blue',
                                'text' => '<strong>having been + V3</strong> — попередня дія:',
                                'example' => 'Having been warned, he was careful.',
                            ],
                            [
                                'label' => 'Порівняння',
                                'color' => 'rose',
                                'text' => 'Active: <strong>doing / having done</strong> → Passive: <strong>being done / having been done</strong>',
                                'example' => 'writing → being written → having been written',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - утворення
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Утворення пасивного герундія',
                        'intro' => 'Порівняння активних та пасивних форм герундія:',
                        'items' => [
                            [
                                'label' => 'Active Simple',
                                'title' => 'doing / writing',
                                'subtitle' => 'I enjoy writing letters. — Мені подобається писати листи.',
                            ],
                            [
                                'label' => 'Passive Simple',
                                'title' => 'being done / being written',
                                'subtitle' => 'He dislikes being criticized. — Йому не подобається, коли його критикують.',
                            ],
                            [
                                'label' => 'Active Perfect',
                                'title' => 'having done / having written',
                                'subtitle' => 'Having finished the work, she left. — Закінчивши роботу, вона пішла.',
                            ],
                            [
                                'label' => 'Passive Perfect',
                                'title' => 'having been done / having been written',
                                'subtitle' => 'Having been rejected, she tried again. — Отримавши відмову, вона спробувала знову.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - простий пасивний герундій
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Простий пасивний герундій (being done)',
                        'sections' => [
                            [
                                'label' => 'Після дієслів почуттів',
                                'color' => 'emerald',
                                'description' => 'Після <strong>like, hate, enjoy, love, dislike, avoid, mind</strong>:',
                                'examples' => [
                                    ['en' => 'She hates being interrupted.', 'ua' => 'Вона ненавидить, коли її перебивають.'],
                                    ['en' => 'Nobody likes being criticized.', 'ua' => 'Ніхто не любить, коли його критикують.'],
                                    ['en' => 'He enjoys being praised.', 'ua' => 'Йому подобається, коли його хвалять.'],
                                    ['en' => 'I don\'t mind being asked questions.', 'ua' => 'Я не проти, коли мені ставлять запитання.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'blue',
                                'description' => 'Після <strong>without, after, before, despite, instead of, on</strong>:',
                                'examples' => [
                                    ['en' => 'He left without being noticed.', 'ua' => 'Він пішов непомітно.'],
                                    ['en' => 'After being rejected, she applied again.', 'ua' => 'Після відмови вона подала заявку знову.'],
                                    ['en' => 'Despite being warned, he continued.', 'ua' => 'Попри попередження, він продовжив.'],
                                    ['en' => 'Before being hired, she had to pass a test.', 'ua' => 'Перед тим як її найняли, вона мусила скласти тест.'],
                                ],
                            ],
                            [
                                'label' => 'Після remember / forget / deny',
                                'color' => 'amber',
                                'description' => 'Коли підмет є <strong>об\'єктом дії</strong>:',
                                'examples' => [
                                    ['en' => 'I remember being told about it.', 'ua' => 'Я пам\'ятаю, що мені про це казали.'],
                                    ['en' => 'She denies being involved.', 'ua' => 'Вона заперечує свою причетність.'],
                                    ['en' => 'He will never forget being helped by her.', 'ua' => 'Він ніколи не забуде, як вона йому допомогла.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - перфектний пасивний герундій
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '3. Перфектний пасивний герундій (having been done)',
                        'sections' => [
                            [
                                'label' => 'Причина → наслідок',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>передували іншим</strong> і стали їхньою причиною:',
                                'examples' => [
                                    ['en' => 'Having been warned, he was more careful.', 'ua' => 'Отримавши попередження, він був обережнішим.'],
                                    ['en' => 'Having been told the truth, she felt relieved.', 'ua' => 'Дізнавшись правду, вона відчула полегшення.'],
                                    ['en' => 'Having been rejected twice, he gave up.', 'ua' => 'Отримавши відмову двічі, він здався.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'blue',
                                'description' => 'Часто в <strong>академічному та офіційному</strong> письмі:',
                                'examples' => [
                                    ['en' => 'Having been approved by the board, the project will begin.', 'ua' => 'Після схвалення радою проєкт розпочнеться.'],
                                    ['en' => 'Having been elected, she took office immediately.', 'ua' => 'Після обрання вона негайно обійняла посаду.'],
                                    ['en' => 'Having been tested, the product was released.', 'ua' => 'Після тестування продукт був випущений.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'rose',
                                'description' => 'Для підкреслення <strong>попередньої дії</strong>:',
                                'examples' => [
                                    ['en' => 'After having been informed, he took action.', 'ua' => 'Після того як його поінформували, він вжив заходів.'],
                                    ['en' => 'Despite having been warned, she ignored the advice.', 'ua' => 'Попри попередження, вона проігнорувала пораду.'],
                                    ['en' => 'On having been asked, he explained everything.', 'ua' => 'На запитання він усе пояснив.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - дієслова та вирази з пасивним герундієм
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Слова та вирази з пасивним герундієм',
                        'sections' => [
                            [
                                'label' => 'Дієслова почуттів',
                                'color' => 'emerald',
                                'description' => '<strong>like, love, hate, enjoy, dislike, prefer, avoid, mind, can\'t stand</strong>:',
                                'examples' => [
                                    ['en' => 'I can\'t stand being ignored.', 'ua' => 'Я не виношу, коли мене ігнорують.'],
                                    ['en' => 'She avoids being seen by him.', 'ua' => 'Вона уникає того, щоб він її бачив.'],
                                    ['en' => 'They prefer being given clear instructions.', 'ua' => 'Вони віддають перевагу чітким інструкціям.'],
                                ],
                            ],
                            [
                                'label' => 'Дієслова пам\'яті / мовлення',
                                'color' => 'blue',
                                'description' => '<strong>remember, forget, deny, admit, recall, mention</strong>:',
                                'examples' => [
                                    ['en' => 'He admitted being wrong.', 'ua' => 'Він визнав, що помилився.'],
                                    ['en' => 'She recalls being praised by her teacher.', 'ua' => 'Вона пригадує, як її хвалила вчителька.'],
                                    ['en' => 'He mentioned being offered a job.', 'ua' => 'Він згадав, що йому пропонували роботу.'],
                                ],
                            ],
                            [
                                'label' => 'Стійкі вирази',
                                'color' => 'amber',
                                'description' => '<strong>be worth, be used to, look forward to, be afraid of</strong>:',
                                'examples' => [
                                    ['en' => 'The book is worth being read.', 'ua' => 'Цю книгу варто прочитати.'],
                                    ['en' => 'She is used to being watched.', 'ua' => 'Вона звикла, що за нею спостерігають.'],
                                    ['en' => 'I\'m afraid of being punished.', 'ua' => 'Я боюся, що мене покарають.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - Simple vs Perfect Gerund
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Порівняння: being done vs having been done',
                        'intro' => 'Коли використовувати простий чи перфектний пасивний герундій:',
                        'rows' => [
                            [
                                'en' => 'being done',
                                'ua' => 'Одночасна дія або загальне твердження',
                                'note' => 'She hates being interrupted. — Вона ненавидить, коли її перебивають.',
                            ],
                            [
                                'en' => 'having been done',
                                'ua' => 'Попередня (раніша) дія',
                                'note' => 'Having been warned, he was careful. — Отримавши попередження, він був обережнішим.',
                            ],
                            [
                                'en' => 'being done (прийменник)',
                                'ua' => 'Процес / одночасність',
                                'note' => 'Without being noticed, he left. — Непомітно він пішов.',
                            ],
                            [
                                'en' => 'having been done (причина)',
                                'ua' => 'Причинно-наслідковий зв\'язок',
                                'note' => 'Having been rejected, she tried again. — Отримавши відмову, вона спробувала знову.',
                            ],
                        ],
                        'warning' => '📌 Перфектний герундій підкреслює, що дія сталася РАНІШЕ за основну дію в реченні!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison - Active vs Passive Gerund
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Порівняння: Active vs Passive Gerund',
                        'intro' => 'Коли підмет виконує дію (active) чи зазнає дії (passive):',
                        'rows' => [
                            [
                                'en' => 'Active: He likes helping others.',
                                'ua' => 'Йому подобається допомагати іншим.',
                                'note' => 'Він (підмет) допомагає.',
                            ],
                            [
                                'en' => 'Passive: He likes being helped.',
                                'ua' => 'Йому подобається, коли йому допомагають.',
                                'note' => 'Йому (підмету) допомагають.',
                            ],
                            [
                                'en' => 'Active: I remember telling her.',
                                'ua' => 'Я пам\'ятаю, як сказав їй.',
                                'note' => 'Я сказав.',
                            ],
                            [
                                'en' => 'Passive: I remember being told.',
                                'ua' => 'Я пам\'ятаю, що мені сказали.',
                                'note' => 'Мені сказали.',
                            ],
                        ],
                        'warning' => '📌 Якщо підмет є виконавцем дії — active gerund. Якщо підмет зазнає дії — passive gerund.',
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
                                'title' => 'Пропуск being у пасивному герундії.',
                                'wrong' => 'She hates criticized.',
                                'right' => '✅ She hates being criticized.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'He enjoys being praise.',
                                'right' => '✅ He enjoys being praised.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутання простого та перфектного.',
                                'wrong' => 'Being warned yesterday, he was careful.',
                                'right' => '✅ Having been warned yesterday, he was careful.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Неправильний порядок слів у перфектному.',
                                'wrong' => 'been having done',
                                'right' => '✅ having been done',
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
                            'Простий пасивний герундій: <strong>being + V3</strong> (being done, being written).',
                            'Перфектний пасивний герундій: <strong>having been + V3</strong> (having been done).',
                            '<strong>being done</strong> — для одночасних дій або загальних тверджень.',
                            '<strong>having been done</strong> — для дій, що сталися раніше (причина → наслідок).',
                            'Використовується після дієслів почуттів: <strong>like, hate, enjoy, avoid, mind</strong>.',
                            'Використовується після прийменників: <strong>without, after, before, despite, on</strong>.',
                            'У стійких виразах: <strong>be worth, be used to, look forward to, be afraid of</strong>.',
                            'Перфектний герундій типовий для <strong>академічного та формального</strong> стилю.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
