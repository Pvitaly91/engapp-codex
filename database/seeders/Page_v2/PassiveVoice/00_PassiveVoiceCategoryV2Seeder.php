<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceCategoryV2Seeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'passive-voice';
    }

    protected function description(): array
    {
        return [
            'title' => 'Passive Voice — Пасивний стан',
            'subtitle_html' => '<p><strong>Пасивний стан (Passive Voice)</strong> — це граматична конструкція, у якій підмет речення отримує дію, а не виконує її. Використовується, коли фокус на дії або її результаті, а не на виконавці.</p>',
            'subtitle_text' => 'Пасивний стан англійської мови: структура, правила утворення та використання у різних часах.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2–B2',
                        'intro' => 'У цьому розділі ти опануєш <strong>пасивний стан</strong> англійської мови: як його утворювати, коли використовувати та як він змінюється у різних часах.',
                        'rules' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>Object + be + Past Participle</strong>:',
                                'example' => 'The cake is made by my mother.',
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'blue',
                                'text' => 'Коли виконавець <strong>невідомий, неважливий</strong> або очевидний:',
                                'example' => 'The letter was sent yesterday.',
                            ],
                            [
                                'label' => 'Агент (by)',
                                'color' => 'rose',
                                'text' => 'Виконавець вказується через <strong>by + agent</strong> (необов\'язково):',
                                'example' => 'The book was written by J.K. Rowling.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => 'Ключові правила',
                        'items' => [
                            'Базова структура: <strong>Object + be (у потрібному часі) + Past Participle</strong>.',
                            'Тільки <strong>перехідні дієслова</strong> (з додатком) можуть утворювати пасивний стан.',
                            'Виконавець (агент) вказується через <strong>by</strong>, але часто опускається.',
                            'У модальних конструкціях: <strong>modal + be + Past Participle</strong>.',
                            'Пасивний стан часто використовується у <strong>науковому та офіційному</strong> стилях.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'passive-voice',
            'title' => 'Пасивний стан',
            'language' => 'uk',
        ];
    }
}
