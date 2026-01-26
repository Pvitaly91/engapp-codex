<?php

namespace Database\Seeders\Page_v2\PassiveVoice\Basics;

class PassiveVoiceBasicsFormV3TheorySeeder extends PassiveVoiceBasicsPageSeeder
{
    protected function slug(): string
    {
        return 'theory-passive-voice-form-v3';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive Voice — Форма: be + V3 (Past Participle)',
            'subtitle_html' => '<p><strong>Form: be + V3</strong> — це базова формула пасивного стану. Дієслово <strong>be</strong> змінюється за часами, а <strong>V3 (Past Participle)</strong> залишається незмінним. Тут ти вивчиш утворення V3 та порядок слів у пасиві.</p>',
            'subtitle_text' => 'Формула пасивного стану: be + V3, таблиця Past Participle, регулярні та нерегулярні дієслова, порядок слів у пасиві.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-basics',
                'title' => 'Passive Voice: База — Основи пасивного стану',
                'language' => 'uk',
            ],
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Past Participle',
                'V3',
                'Irregular Verbs',
                'Form',
                'A2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти вивчиш <strong>формулу пасивного стану</strong>: як утворюється V3 (Past Participle), різницю між регулярними та нерегулярними дієсловами, і порядок слів у пасивних реченнях.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>be + Past Participle (V3)</strong>:',
                                'example' => 'The letter is written.',
                            ],
                            [
                                'label' => 'Regular V3',
                                'color' => 'blue',
                                'text' => 'Регулярні дієслова: <strong>V + -ed</strong>:',
                                'example' => 'clean → cleaned, open → opened',
                            ],
                            [
                                'label' => 'Irregular V3',
                                'color' => 'rose',
                                'text' => 'Нерегулярні дієслова: <strong>особлива форма</strong>:',
                                'example' => 'write → written, make → made',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Формула пасивного стану',
                        'intro' => 'Пасивний стан утворюється за формулою:',
                        'items' => [
                            [
                                'label' => 'be',
                                'title' => 'Дієслово be',
                                'subtitle' => 'Змінюється за часом: am/is/are, was/were, will be, has been...',
                            ],
                            [
                                'label' => '+',
                                'title' => 'Плюс',
                                'subtitle' => '',
                            ],
                            [
                                'label' => 'V3',
                                'title' => 'Past Participle',
                                'subtitle' => 'Третя форма дієслова: written, made, cleaned, built...',
                            ],
                            [
                                'label' => '(by)',
                                'title' => 'Agent (необов\'язково)',
                                'subtitle' => 'Виконавець дії: by Tom, by the company...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Регулярні дієслова (V3 = V2 = V + ed)',
                        'sections' => [
                            [
                                'label' => 'Базове правило',
                                'color' => 'emerald',
                                'description' => 'Для регулярних дієслів <strong>Past Participle (V3) = Past Simple (V2)</strong>: додаємо <strong>-ed</strong>',
                                'examples' => [
                                    ['en' => 'clean → cleaned → cleaned', 'ua' => 'The room is cleaned.'],
                                    ['en' => 'open → opened → opened', 'ua' => 'The door is opened.'],
                                    ['en' => 'paint → painted → painted', 'ua' => 'The wall is painted.'],
                                ],
                            ],
                            [
                                'label' => 'Правила написання -ed',
                                'color' => 'sky',
                                'description' => 'Особливості додавання <strong>-ed</strong>:',
                                'examples' => [
                                    ['en' => 'Після -e: live → lived', 'ua' => 'Просто додаємо -d'],
                                    ['en' => 'Після приголосної + y: study → studied', 'ua' => 'y → i + ed'],
                                    ['en' => 'Короткі з CVC: stop → stopped', 'ua' => 'Подвоюємо кінцеву приголосну'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Нерегулярні дієслова — Таблиця V3',
                        'intro' => 'Найчастіші нерегулярні дієслова для пасиву:',
                        'rows' => [
                            [
                                'en' => 'write',
                                'ua' => 'wrote',
                                'note' => 'written — The letter is written.',
                            ],
                            [
                                'en' => 'make',
                                'ua' => 'made',
                                'note' => 'made — Cars are made in Germany.',
                            ],
                            [
                                'en' => 'build',
                                'ua' => 'built',
                                'note' => 'built — The house was built in 1990.',
                            ],
                            [
                                'en' => 'break',
                                'ua' => 'broke',
                                'note' => 'broken — The window was broken.',
                            ],
                            [
                                'en' => 'take',
                                'ua' => 'took',
                                'note' => 'taken — The photo was taken yesterday.',
                            ],
                            [
                                'en' => 'give',
                                'ua' => 'gave',
                                'note' => 'given — The prize was given to him.',
                            ],
                            [
                                'en' => 'see',
                                'ua' => 'saw',
                                'note' => 'seen — The film was seen by millions.',
                            ],
                            [
                                'en' => 'do',
                                'ua' => 'did',
                                'note' => 'done — The work is done.',
                            ],
                            [
                                'en' => 'send',
                                'ua' => 'sent',
                                'note' => 'sent — The email was sent.',
                            ],
                            [
                                'en' => 'buy',
                                'ua' => 'bought',
                                'note' => 'bought — The car was bought last week.',
                            ],
                            [
                                'en' => 'sell',
                                'ua' => 'sold',
                                'note' => 'sold — All tickets were sold.',
                            ],
                            [
                                'en' => 'steal',
                                'ua' => 'stole',
                                'note' => 'stolen — My bike was stolen.',
                            ],
                        ],
                        'warning' => '📌 Формат: <strong>V1 — V2 — V3</strong> (Infinitive — Past — Past Participle)',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Порядок слів у пасиві',
                        'sections' => [
                            [
                                'label' => 'Стверджувальне',
                                'color' => 'emerald',
                                'description' => 'Порядок: <strong>Subject + be + V3 (+ by agent)</strong>',
                                'examples' => [
                                    ['en' => 'The letter is written.', 'ua' => 'Лист написаний.'],
                                    ['en' => 'The letter is written by Tom.', 'ua' => 'Лист написаний Томом.'],
                                    ['en' => 'Cars are made in Japan.', 'ua' => 'Машини виробляються в Японії.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Порядок: <strong>Subject + be + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'The work was not finished.', 'ua' => 'Робота не була закінчена.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => 'Порядок: <strong>Be + Subject + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is the letter written?', 'ua' => 'Лист написаний?'],
                                    ['en' => 'Was the work finished?', 'ua' => 'Робота була закінчена?'],
                                    ['en' => 'Are cars made here?', 'ua' => 'Тут виробляють машини?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => 'The letter written by Tom.',
                                'right' => '✅ The letter is written by Tom.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Використання V2 замість V3.',
                                'wrong' => 'The house was builded.',
                                'right' => '✅ The house was built. (build-built-built)',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Плутанина V2 і V3 для нерегулярних.',
                                'wrong' => 'The window was broke.',
                                'right' => '✅ The window was broken. (break-broke-broken)',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Короткий конспект',
                        'items' => [
                            'Формула пасиву: <strong>be + V3 (Past Participle)</strong>.',
                            'Дієслово <strong>be</strong> змінюється за часами, <strong>V3</strong> — ні.',
                            'Регулярні дієслова: V3 = V2 = <strong>V + ed</strong> (cleaned, opened).',
                            'Нерегулярні дієслова: особлива форма V3 (written, made, built).',
                            'Заперечення: <strong>be + not + V3</strong>.',
                            'Питання: <strong>Be + Subject + V3?</strong>',
                            'Запам\'ятай найчастіші нерегулярні V3 для пасиву!',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з основ пасивного стану',
                        'items' => [
                            [
                                'label' => 'Що таке Passive Voice?',
                                'current' => false,
                            ],
                            [
                                'label' => 'Form: be + V3 (поточна)',
                                'current' => true,
                            ],
                            [
                                'label' => 'Present Simple Passive',
                                'current' => false,
                            ],
                            [
                                'label' => 'Past Simple Passive',
                                'current' => false,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
