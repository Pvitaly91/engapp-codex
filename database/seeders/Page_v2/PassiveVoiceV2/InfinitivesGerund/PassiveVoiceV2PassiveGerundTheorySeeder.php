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
            'title' => 'Пасивний герундій — Passive Gerund',
            'subtitle_html' => '<p><strong>Пасивний герундій (Passive Gerund)</strong> — це форма герундія, яка підкреслює дію, що виконується над об\'єктом. Використовується після дієслів почуттів, прийменників та у складних граматичних конструкціях.</p>',
            'subtitle_text' => 'Пасивний герундій: being done, having been done. Структура, використання та приклади.',
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
                        'intro' => 'У цьому розділі ти вивчиш <strong>пасивні форми герундія</strong> — важливі конструкції для природного англійського мовлення.',
                        'rules' => [
                            [
                                'label' => 'Простий пасивний герундій',
                                'color' => 'rose',
                                'text' => '<strong>being + V3</strong> — процес у пасиві:',
                                'example' => 'She hates being interrupted.',
                            ],
                            [
                                'label' => 'Перфектний пасивний герундій',
                                'color' => 'amber',
                                'text' => '<strong>having been + V3</strong> — попередня дія:',
                                'example' => 'Having been warned, he was careful.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - дві форми
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Дві форми пасивного герундія',
                        'intro' => 'Порівняння активних та пасивних форм герундія:',
                        'items' => [
                            [
                                'label' => 'Active Gerund',
                                'title' => 'doing',
                                'subtitle' => '→ Passive: being done (процес)',
                            ],
                            [
                                'label' => 'Perfect Active Ger.',
                                'title' => 'having done',
                                'subtitle' => '→ Passive: having been done (попередня дія)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Simple Passive Gerund
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
                                'description' => 'Після <strong>like, love, hate, enjoy, dislike, avoid, mind</strong>:',
                                'examples' => [
                                    ['en' => 'She hates being interrupted during meetings.', 'ua' => 'Вона ненавидить, коли її перебивають під час зустрічей.'],
                                    ['en' => 'Nobody likes being criticized in public.', 'ua' => 'Ніхто не любить, коли його критикують прилюдно.'],
                                    ['en' => 'He enjoys being praised for his work.', 'ua' => 'Йому подобається, коли його хвалять за роботу.'],
                                    ['en' => 'I don\'t mind being asked questions.', 'ua' => 'Я не проти, коли мене запитують.'],
                                    ['en' => 'She avoids being seen with him.', 'ua' => 'Вона уникає, щоб її бачили з ним.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'blue',
                                'description' => 'Після <strong>without, after, before, despite, instead of, by</strong>:',
                                'examples' => [
                                    ['en' => 'He left without being noticed.', 'ua' => 'Він пішов непомітно (не будучи поміченим).'],
                                    ['en' => 'After being rejected, she tried again.', 'ua' => 'Після того як її відхилили, вона спробувала знову.'],
                                    ['en' => 'Despite being warned, he continued.', 'ua' => 'Попри попередження, він продовжив.'],
                                    ['en' => 'Before being hired, she passed many tests.', 'ua' => 'Перед тим як її найняли, вона пройшла багато тестів.'],
                                ],
                            ],
                            [
                                'label' => 'Після дієслів',
                                'color' => 'amber',
                                'description' => 'Після <strong>remember, forget, regret, deny, admit, risk</strong>:',
                                'examples' => [
                                    ['en' => 'I remember being told about it.', 'ua' => 'Я пам\'ятаю, як мені про це казали.'],
                                    ['en' => 'She denied being involved in the scandal.', 'ua' => 'Вона заперечувала свою причетність до скандалу.'],
                                    ['en' => 'He risks being caught.', 'ua' => 'Він ризикує бути спійманим.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Perfect Passive Gerund
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
                                'description' => 'Для дій, що <strong>передували іншим</strong> і є їх причиною:',
                                'examples' => [
                                    ['en' => 'Having been warned, he was more careful.', 'ua' => 'Отримавши попередження, він був обережнішим.'],
                                    ['en' => 'Having been told the truth, she felt relieved.', 'ua' => 'Дізнавшись правду, вона відчула полегшення.'],
                                    ['en' => 'Having been rejected twice, he gave up.', 'ua' => 'Після двох відмов він здався.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'blue',
                                'description' => 'Часто в <strong>академічному та офіційному</strong> письмі:',
                                'examples' => [
                                    ['en' => 'Having been approved, the project will begin soon.', 'ua' => 'Після схвалення проєкт незабаром розпочнеться.'],
                                    ['en' => 'Having been elected, she took office immediately.', 'ua' => 'Після обрання вона негайно обійняла посаду.'],
                                    ['en' => 'Having been informed, they took action.', 'ua' => 'Отримавши інформацію, вони вжили заходів.'],
                                ],
                            ],
                            [
                                'label' => 'Після дієслів пам\'яті та почуттів',
                                'color' => 'rose',
                                'description' => 'З <strong>remember, regret, deny</strong> для попередніх дій:',
                                'examples' => [
                                    ['en' => 'He denies having been involved.', 'ua' => 'Він заперечує свою причетність (у минулому).'],
                                    ['en' => 'She regrets having been so harsh.', 'ua' => 'Вона шкодує, що була такою суворою.'],
                                    ['en' => 'I don\'t remember having been asked.', 'ua' => 'Я не пам\'ятаю, щоб мене питали.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Порівняння двох форм',
                        'intro' => 'Коли використовувати кожну форму:',
                        'rows' => [
                            [
                                'en' => 'being + V3',
                                'ua' => 'Процес у теперішньому',
                                'note' => 'She hates being interrupted.',
                            ],
                            [
                                'en' => 'having been + V3',
                                'ua' => 'Попередня дія (причина)',
                                'note' => 'Having been warned, he left.',
                            ],
                        ],
                        'warning' => '📌 Перфектна форма вказує на дію, що передувала іншій і часто є її причиною!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - Структура
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '5. Структура та формула',
                        'intro' => 'Як утворюються пасивні герундії:',
                        'items' => [
                            [
                                'label' => 'Простий пасивний',
                                'title' => 'being + V3',
                                'subtitle' => 'Verb + being done / Preposition + being done',
                            ],
                            [
                                'label' => 'Перфектний пасивний',
                                'title' => 'having been + V3',
                                'subtitle' => 'Having been done, ... (причинно-наслідковий зв\'язок)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Типові конструкції
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Типові конструкції з пасивним герундієм',
                        'sections' => [
                            [
                                'label' => 'Прийменник + being + V3',
                                'color' => 'emerald',
                                'description' => 'Після прийменників завжди використовується герундій:',
                                'examples' => [
                                    ['en' => 'He insists on being heard.', 'ua' => 'Він наполягає, щоб його вислухали.'],
                                    ['en' => 'She was tired of being ignored.', 'ua' => 'Вона втомилася від того, що її ігнорували.'],
                                    ['en' => 'They are interested in being invited.', 'ua' => 'Їм цікаво отримати запрошення.'],
                                ],
                            ],
                            [
                                'label' => 'Дієслово + being + V3',
                                'color' => 'blue',
                                'description' => 'Після певних дієслів, що вимагають герундій:',
                                'examples' => [
                                    ['en' => 'She keeps being promoted.', 'ua' => 'Її постійно підвищують.'],
                                    ['en' => 'He stopped being consulted.', 'ua' => 'З ним перестали радитися.'],
                                    ['en' => 'They suggest being tested first.', 'ua' => 'Вони пропонують спочатку пройти тестування.'],
                                ],
                            ],
                            [
                                'label' => 'Прикметник + preposition + being + V3',
                                'color' => 'amber',
                                'description' => 'Після прикметників з прийменниками:',
                                'examples' => [
                                    ['en' => 'She is afraid of being fired.', 'ua' => 'Вона боїться, що її звільнять.'],
                                    ['en' => 'He is proud of being chosen.', 'ua' => 'Він пишається тим, що його обрали.'],
                                    ['en' => 'They are worried about being left behind.', 'ua' => 'Вони хвилюються, що їх залишать позаду.'],
                                ],
                            ],
                        ],
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
                                'title' => 'Пропуск "being" у простій формі.',
                                'wrong' => 'She hates interrupted.',
                                'right' => '✅ She hates being interrupted.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильний порядок у перфектній формі.',
                                'wrong' => 'Having warned been, he left.',
                                'right' => '✅ Having been warned, he left.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання to + infinitive після прийменників.',
                                'wrong' => 'He left without to be noticed.',
                                'right' => '✅ He left without being noticed.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Використання V1 замість V3.',
                                'wrong' => 'She hates being interrupt.',
                                'right' => '✅ She hates being interrupted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Різниця між інфінітивом та герундієм
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Коли герундій, а коли інфінітив?',
                        'sections' => [
                            [
                                'label' => 'Використовуй герундій (being done)',
                                'color' => 'emerald',
                                'description' => 'У цих випадках:',
                                'examples' => [
                                    ['en' => 'Після прийменників: interested in being hired', 'ua' => 'Після прийменників завжди герундій!'],
                                    ['en' => 'Після like/hate/enjoy: hates being criticized', 'ua' => 'З дієсловами почуттів — герундій.'],
                                    ['en' => 'Після remember/forget (минула дія): remember being told', 'ua' => 'Для дій, що вже відбулися.'],
                                ],
                            ],
                            [
                                'label' => 'Використовуй інфінітив (to be done)',
                                'color' => 'blue',
                                'description' => 'У цих випадках:',
                                'examples' => [
                                    ['en' => 'Після need/want/expect: needs to be finished', 'ua' => 'Для необхідності та очікувань.'],
                                    ['en' => 'Після seem/appear: seems to be locked', 'ua' => 'Для здогадок про стан.'],
                                    ['en' => 'З модальними: must be done, should be checked', 'ua' => 'З модальними дієсловами — інфінітив.'],
                                ],
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
                        'title' => '9. Ключові правила',
                        'items' => [
                            '<strong>being + V3</strong> — простий пасивний герундій для процесу у теперішньому.',
                            '<strong>having been + V3</strong> — перфектний пасивний герундій для попередніх дій.',
                            'Після прийменників <strong>завжди герундій</strong>, ніколи інфінітив!',
                            'Використовується після <strong>like, hate, enjoy, avoid, deny, risk, remember</strong>.',
                            'Після прикметників з прийменниками: <strong>afraid of, interested in, proud of</strong>.',
                            'Перфектна форма часто вказує на <strong>причинно-наслідковий зв\'язок</strong>.',
                            'Типові для <strong>природного розмовного</strong> та формального стилів.',
                            'Завжди використовуй <strong>V3 (Past Participle)</strong>, не V1!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
