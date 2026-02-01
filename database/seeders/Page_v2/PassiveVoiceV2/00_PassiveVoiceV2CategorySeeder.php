<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceV2CategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2';
    }

    protected function description(): array
    {
        return [
            'title' => 'Пасивний стан V2 — Passive Voice V2',
            'subtitle_html' => '<p><strong>Пасивний стан V2 (Passive Voice V2)</strong> — це повний курс вивчення пасивних конструкцій в англійській мові. Від базових правил до складних граматичних структур: пасив у всіх часах, модальні дієслова, інфінітиви, герундії, каузативи та багато іншого.</p>',
            'subtitle_text' => 'Комплексний курс пасивного стану англійської мови: всі часи, модальні дієслова, інфінітиви, герундії, каузативи, фразові дієслова та типові помилки.',
            'locale' => 'uk',
            'blocks' => [
                // Hero block with V3 JSON structure
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–C2',
                        'intro' => 'У цьому розділі ти опануєш <strong>пасивний стан</strong> англійської мови на всіх рівнях: від базових правил утворення до складних конструкцій із модальними дієсловами, інфінітивами, герундіями та каузативами.',
                        'rules' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>Object + be (у потрібному часі) + Past Participle</strong>:',
                                'example' => 'The report is being written.',
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'blue',
                                'text' => 'Коли виконавець <strong>невідомий, неважливий</strong> або коли фокус на дії/результаті:',
                                'example' => 'This building was constructed in 1850.',
                            ],
                            [
                                'label' => 'Агент (by)',
                                'color' => 'rose',
                                'text' => 'Виконавець вказується через <strong>by + agent</strong>, але часто опускається:',
                                'example' => 'The novel was written by Agatha Christie.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid block
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Основні правила утворення пасиву',
                        'intro' => 'Пасивний стан утворюється за допомогою дієслова be у потрібному часі та Past Participle (V3):',
                        'items' => [
                            [
                                'label' => 'Формула',
                                'title' => 'Структура пасиву',
                                'subtitle' => 'Subject + be (у потрібному часі) + Past Participle (V3)',
                            ],
                            [
                                'label' => 'Active → Passive',
                                'title' => 'Трансформація',
                                'subtitle' => 'Object активного речення стає Subject пасивного.',
                            ],
                            [
                                'label' => 'Обмеження',
                                'title' => 'Тільки перехідні дієслова',
                                'subtitle' => 'Лише дієслова, що мають додаток, можуть утворювати пасив.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Вказівка виконавця',
                                'subtitle' => 'by + виконавець (необов\'язково, якщо очевидно).',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels block - Passive in different tenses
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Пасив у різних часах',
                        'sections' => [
                            [
                                'label' => 'Present Simple',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>am/is/are + V3</strong>',
                                'examples' => [
                                    ['en' => 'English is spoken worldwide.', 'ua' => 'Англійською розмовляють у всьому світі. (A2)'],
                                    ['en' => 'The emails are sent daily.', 'ua' => 'Електронні листи надсилаються щодня. (A2)'],
                                ],
                            ],
                            [
                                'label' => 'Present Continuous',
                                'color' => 'sky',
                                'description' => 'Структура: <strong>am/is/are + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted now.', 'ua' => 'Будинок фарбується зараз. (B1)'],
                                    ['en' => 'New software is being installed.', 'ua' => 'Нове ПЗ встановлюється. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Present Perfect',
                                'color' => 'blue',
                                'description' => 'Структура: <strong>has/have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The work has been completed.', 'ua' => 'Робота завершена. (B1)'],
                                    ['en' => 'The documents have been signed.', 'ua' => 'Документи підписані. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'violet',
                                'description' => 'Структура: <strong>was/were + V3</strong>',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок побудований у 1990. (A2)'],
                                    ['en' => 'They were invited yesterday.', 'ua' => 'Їх запросили вчора. (A2)'],
                                ],
                            ],
                            [
                                'label' => 'Past Continuous',
                                'color' => 'indigo',
                                'description' => 'Структура: <strong>was/were + being + V3</strong>',
                                'examples' => [
                                    ['en' => 'The road was being repaired.', 'ua' => 'Дорогу ремонтували. (B1)'],
                                    ['en' => 'We were being followed.', 'ua' => 'За нами стежили. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Past Perfect',
                                'color' => 'purple',
                                'description' => 'Структура: <strong>had been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email had been sent before noon.', 'ua' => 'Лист надіслали до полудня. (B2)'],
                                    ['en' => 'The project had been completed.', 'ua' => 'Проєкт було завершено. (B2)'],
                                ],
                            ],
                            [
                                'label' => 'Future Simple',
                                'color' => 'amber',
                                'description' => 'Структура: <strong>will be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The report will be finished tomorrow.', 'ua' => 'Звіт буде завершено завтра. (B1)'],
                                    ['en' => 'You will be informed soon.', 'ua' => 'Вас повідомлять незабаром. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Future Continuous',
                                'color' => 'orange',
                                'description' => 'Структура: <strong>will be + being + V3</strong> (рідко)',
                                'examples' => [
                                    ['en' => 'The project will be being discussed at 3 PM.', 'ua' => 'Проєкт обговорюватимуть о 15:00. (C1)'],
                                ],
                            ],
                            [
                                'label' => 'Future Perfect',
                                'color' => 'rose',
                                'description' => 'Структура: <strong>will have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The task will have been completed by then.', 'ua' => 'Завдання буде завершено до того часу. (B2)'],
                                    ['en' => 'The building will have been demolished.', 'ua' => 'Будівлю буде знесено. (B2)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Modals in passive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Пасив з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>Modal + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'The task must be done today.', 'ua' => 'Завдання має бути виконано сьогодні. (B1)'],
                                    ['en' => 'This can be fixed easily.', 'ua' => 'Це можна легко виправити. (B1)'],
                                    ['en' => 'The meeting should be postponed.', 'ua' => 'Зустріч слід відкласти. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Perfect модальний',
                                'color' => 'blue',
                                'description' => 'Структура: <strong>Modal + have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The work should have been finished.', 'ua' => 'Робота мала бути завершена. (B2)'],
                                    ['en' => 'It might have been stolen.', 'ua' => 'Його, можливо, вкрали. (B2)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Infinitives and gerunds
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Інфінітив та герундій у пасиві',
                        'sections' => [
                            [
                                'label' => 'Пасивний інфінітив',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>to be + V3</strong> або <strong>to have been + V3</strong>',
                                'examples' => [
                                    ['en' => 'The problem needs to be solved.', 'ua' => 'Проблему потрібно вирішити. (B1)'],
                                    ['en' => 'The report seems to have been lost.', 'ua' => 'Звіт, схоже, загубили. (B2)'],
                                    ['en' => 'She wants to be invited to the party.', 'ua' => 'Вона хоче бути запрошеною на вечірку. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Пасивний герундій',
                                'color' => 'blue',
                                'description' => 'Структура: <strong>being + V3</strong> або <strong>having been + V3</strong>',
                                'examples' => [
                                    ['en' => 'He enjoys being praised.', 'ua' => 'Йому подобається, коли його хвалять. (B1)'],
                                    ['en' => 'I remember being told about it.', 'ua' => 'Я пам\'ятаю, як мені про це казали. (B1)'],
                                    ['en' => 'After having been warned, he left.', 'ua' => 'Після того, як його попередили, він пішов. (B2)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Special constructions
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Спеціальні конструкції',
                        'intro' => 'Окрім стандартного пасиву, існують спеціальні конструкції:',
                        'items' => [
                            [
                                'label' => 'Get-пасив',
                                'title' => 'Get + Past Participle',
                                'subtitle' => 'He got fired. — Його звільнили. (B2)',
                            ],
                            [
                                'label' => 'Безособовий пасив',
                                'title' => 'It is said / People say',
                                'subtitle' => 'It is believed that... — Вважається, що... (B2)',
                            ],
                            [
                                'label' => 'Каузатив',
                                'title' => 'Have/Get something done',
                                'subtitle' => 'I had my car repaired. — Мені відремонтували машину. (B2)',
                            ],
                            [
                                'label' => 'Фразові дієслова',
                                'title' => 'Phrasal Verbs в пасиві',
                                'subtitle' => 'The meeting was put off. — Зустріч відклали. (B2)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Questions and negatives
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Заперечення та питання у пасивному стані',
                        'sections' => [
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після першого допоміжного дієслова be',
                                'examples' => [
                                    ['en' => 'The letter was not sent.', 'ua' => 'Лист не був надісланий. (A2)'],
                                    ['en' => 'The house is not being painted.', 'ua' => 'Будинок не фарбується. (B1)'],
                                    ['en' => 'It has not been decided yet.', 'ua' => 'Це ще не вирішено. (B1)'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => 'Перше допоміжне дієслово виходить на початок речення',
                                'examples' => [
                                    ['en' => 'Was the email sent?', 'ua' => 'Лист був надісланий? (A2)'],
                                    ['en' => 'Is the project being finished?', 'ua' => 'Проєкт завершується? (B1)'],
                                    ['en' => 'Has the decision been made?', 'ua' => 'Рішення прийнято? (B1)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Limitations
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Обмеження вживання пасиву',
                        'items' => [
                            '<strong>Неперехідні дієслова</strong> (без додатка) не утворюють пасив: sleep, happen, arrive. (A2)',
                            '<strong>Деякі дієслова стану</strong> не використовуються в пасиві: have (володіти), resemble, lack. (B1)',
                            '<strong>Двооб\'єктні дієслова</strong>: можна зробити пасив від будь-якого додатка (I was given a book / A book was given to me). (B2)',
                            'Уникайте <strong>надмірного використання</strong> пасиву — це може зробити текст складним для сприйняття. (B2)',
                            'У розмовній мові пасив використовується <strong>менш формально</strong>, ніж у писемному та науковому мовленні. (B2)',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Style and formality
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Формальність та стиль пасиву',
                        'intro' => 'Пасивний стан часто використовується у формальному та науковому мовленні:',
                        'rows' => [
                            [
                                'en' => 'Informal: We found the solution.',
                                'ua' => 'Неформальне: Ми знайшли рішення.',
                                'note' => '→ Formal: The solution was found. (B2)',
                            ],
                            [
                                'en' => 'Informal: They conducted the experiment.',
                                'ua' => 'Неформальне: Вони провели експеримент.',
                                'note' => '→ Formal: The experiment was conducted. (B2)',
                            ],
                            [
                                'en' => 'Informal: People believe that...',
                                'ua' => 'Неформальне: Люди вважають, що...',
                                'note' => '→ Formal: It is believed that... (C1)',
                            ],
                        ],
                        'warning' => '📌 У науковій літературі та офіційних документах пасив використовується частіше.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Common mistakes
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => '❌ The letter written.',
                                'right' => '✅ The letter was written. (A2)',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна форма Past Participle.',
                                'wrong' => '❌ The house was builded.',
                                'right' => '✅ The house was built. (A2)',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'orange',
                                'title' => 'Використання активу замість пасиву з модальними.',
                                'wrong' => '❌ The work must do.',
                                'right' => '✅ The work must be done. (B1)',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'sky',
                                'title' => 'Неперехідні дієслова в пасиві.',
                                'wrong' => '❌ The accident was happened.',
                                'right' => '✅ The accident happened. (B1)',
                            ],
                            [
                                'label' => 'Помилка 5',
                                'color' => 'violet',
                                'title' => 'Неправильна структура Continuous Passive.',
                                'wrong' => '❌ The house is been painted.',
                                'right' => '✅ The house is being painted. (B1)',
                            ],
                            [
                                'label' => 'Помилка 6',
                                'color' => 'blue',
                                'title' => 'Зайвий by-агент.',
                                'wrong' => '❌ English is spoken by people everywhere.',
                                'right' => '✅ English is spoken everywhere. (B2)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            'Пасивний стан: <strong>be (у потрібному часі) + Past Participle (V3)</strong>.',
                            'Використовується, коли фокус на <strong>дії/результаті</strong>, а не на виконавці.',
                            'Виконавець вказується через <strong>by</strong>, але часто опускається.',
                            'Модальні: <strong>modal + be + V3</strong> (must be done).',
                            'Пасивний інфінітив: <strong>to be + V3</strong>, пасивний герундій: <strong>being + V3</strong>.',
                            'Спеціальні конструкції: <strong>get-пасив</strong>, <strong>каузатив (have/get sth done)</strong>, <strong>безособовий пасив</strong>.',
                            'Тільки <strong>перехідні дієслова</strong> утворюють пасив.',
                            'Пасив часто використовується у <strong>науковому та офіційному</strong> стилях.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'passive-voice-v2',
            'title' => 'Пасивний стан V2',
            'language' => 'uk',
        ];
    }
}
