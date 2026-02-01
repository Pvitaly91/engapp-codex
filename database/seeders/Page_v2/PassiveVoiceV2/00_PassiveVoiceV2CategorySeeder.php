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
            'title' => 'Пасивний стан V2',
            'subtitle_html' => '<p><strong>Пасивний стан V2</strong> — це повна теорія про Passive Voice: від базових правил і пасиву в різних часах до інфінітива, герундія та типових конструкцій.</p>',
            'subtitle_text' => 'Пасивний стан V2: правила утворення, часи, модальні дієслова, пасивні інфінітиви та герундії.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A1–C2',
                    'body' => json_encode([
                        'level' => 'A1–C2',
                        'intro' => 'У цьому розділі зібрана <strong>повна карта пасивного стану</strong>: базові правила, всі часи, модальні дієслова, інфінітиви та герундії у пасиві, а також типові конструкції й стилістика.',
                        'rules' => [
                            [
                                'label' => 'Формула',
                                'color' => 'emerald',
                                'text' => 'Базова структура: <strong>Object + be + Past Participle (V3)</strong>:',
                                'example' => 'The report is prepared every week.',
                            ],
                            [
                                'label' => 'Фокус',
                                'color' => 'blue',
                                'text' => 'Пасив підкреслює дію або результат, а не виконавця:',
                                'example' => 'The project was completed on time.',
                            ],
                            [
                                'label' => 'Агент',
                                'color' => 'rose',
                                'text' => 'Виконавець додається через <strong>by + agent</strong>, але часто опускається:',
                                'example' => 'The book was written by Orwell.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Основні правила утворення пасиву',
                    'level' => 'A1–B1',
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Перехідні дієслова</strong> можуть утворювати пасив (have, make, build).</li>
<li><strong>Be + V3</strong> змінюється за часом: is/are, was/were, has/have been, will be тощо.</li>
<li><strong>By + agent</strong> — необов’язково, якщо виконавець неважливий або очевидний.</li>
<li><strong>Порядок:</strong> об’єкт активного речення → підмет пасивного.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Структура розділу “Пасивний стан V2”',
                    'level' => 'A1–C2',
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Основні правила утворення пасиву</strong></li>
<li><strong>Пасив у різних часах</strong>
    <ul>
        <li>Теперішній простий пасив (Present Simple Passive)</li>
        <li>Теперішній тривалий пасив (Present Continuous Passive)</li>
        <li>Теперішній доконаний пасив (Present Perfect Passive)</li>
        <li>Минулий простий пасив (Past Simple Passive)</li>
        <li>Минулий тривалий пасив (Past Continuous Passive)</li>
        <li>Минулий доконаний пасив (Past Perfect Passive)</li>
        <li>Майбутній простий пасив (Future Simple Passive)</li>
        <li>Майбутній тривалий пасив (Future Continuous Passive)</li>
        <li>Майбутній доконаний пасив (Future Perfect Passive)</li>
    </ul>
</li>
<li><strong>Заперечення та питання у пасивному стані</strong></li>
<li><strong>Пасив з модальними дієсловами</strong></li>
<li><strong>Інфінітив та герундій у пасиві</strong>
    <ul>
        <li>Пасивний інфінітив (to be done, to have been done)</li>
        <li>Пасивний герундій (being done, having been done)</li>
    </ul>
</li>
<li><strong>Конструкція get-пасив</strong></li>
<li><strong>Безособовий пасив</strong></li>
<li><strong>Каузатив (have/get something done)</strong></li>
<li><strong>Пасив двооб’єктних дієслів</strong></li>
<li><strong>Пасив фразових дієслів</strong></li>
<li><strong>Обмеження вживання пасиву</strong></li>
<li><strong>Формальність та стиль пасиву</strong></li>
<li><strong>Приклади вживання та типові помилки</strong></li>
</ul>
HTML,
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2–C1',
                    'body' => json_encode([
                        'title' => 'Навіщо вивчати пасивний стан',
                        'items' => [
                            'Допомагає робити акцент на дії або результаті.',
                            'Потрібен для офіційного, академічного та наукового стилю.',
                            'Дозволяє уникати повтору виконавця, коли він очевидний.',
                            'Розширює можливості перефразування та стилістики.',
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
