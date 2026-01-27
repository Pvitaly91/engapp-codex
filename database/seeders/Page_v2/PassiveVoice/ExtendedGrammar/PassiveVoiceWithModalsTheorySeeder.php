<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

use Database\Seeders\Pages\PassiveVoice\PassiveVoicePageSeeder;

class PassiveVoiceWithModalsTheorySeeder extends PassiveVoicePageSeeder
{
    protected function slug(): string
    {
        return 'passive-with-modals';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Passive with Modals — Пасив з модальними дієсловами',
            'subtitle_html' => '<p><strong>Пасив з модальними дієсловами</strong> використовується для вираження можливості, необхідності, обов\'язку чи дозволу щодо дії в пасивному стані. Формула проста: <em>modal + be + V3</em>. Тут ти вивчиш, як правильно поєднувати модальні дієслова з пасивним станом.</p>',
            'subtitle_text' => 'Теоретичний огляд пасиву з модальними дієсловами can, must, should, will, may, might, could, would: формула, приклади, питання та заперечення.',
            'subtitle_level' => 'B1',
            'locale' => 'uk',
            'category' => [
                'slug' => 'passive-voice-extended-grammar',
                'title' => 'Розширення граматики',
                'language' => 'uk',
            ],
            // Page anchor tags
            'tags' => [
                'Passive Voice',
                'Passive with Modals',
                'Modal Verbs',
                'Grammar',
                'Theory',
            ],
            // Base tags inherited by all blocks
            'base_tags' => [
                'Passive Voice',
                'Passive with Modals',
                'Modal Verbs',
            ],
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'hero',
                    'tags' => ['Introduction', 'Overview', 'Can', 'Must', 'Should', 'CEFR B1', 'CEFR B2'],
                    'body' => json_encode([
                        'level' => 'B1–B2',
                        'intro' => 'У цій темі ти вивчиш, як утворювати <strong>пасив з модальними дієсловами</strong>: can, must, should, will та іншими. Формула проста: <strong>modal + be + V3</strong>.',
                        'rules' => [
                            [
                                'label' => 'CAN / COULD',
                                'color' => 'emerald',
                                'text' => '<strong>Можливість</strong> — can/could + be + V3:',
                                'example' => 'It can be done. It could be fixed.',
                            ],
                            [
                                'label' => 'MUST / SHOULD',
                                'color' => 'blue',
                                'text' => '<strong>Обов\'язок/порада</strong> — must/should + be + V3:',
                                'example' => 'It must be done. It should be checked.',
                            ],
                            [
                                'label' => 'WILL / WOULD',
                                'color' => 'amber',
                                'text' => '<strong>Майбутнє/умовний</strong> — will/would + be + V3:',
                                'example' => 'It will be finished. It would be appreciated.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'forms-grid-formula',
                    'tags' => ['Formula', 'Structure', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '1. Формула пасиву з модальними дієсловами',
                        'intro' => 'Щоб утворити пасив з модальним дієсловом, використовуй формулу:',
                        'items' => [
                            ['label' => 'Формула', 'title' => 'Modal + be + Past Participle (V3)', 'subtitle' => 'Модальне + be + V3'],
                            ['label' => 'Приклад 1', 'title' => 'It can be done.', 'subtitle' => 'Це можна зробити.'],
                            ['label' => 'Приклад 2', 'title' => 'It must be finished.', 'subtitle' => 'Це має бути завершено.'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-can-could',
                    'tags' => ['Can', 'Could', 'Possibility', 'Ability', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '2. CAN / COULD — можливість та здатність',
                        'sections' => [
                            [
                                'label' => 'CAN + be + V3',
                                'color' => 'emerald',
                                'description' => '<strong>Can</strong> виражає можливість або здатність у теперішньому часі.',
                                'examples' => [
                                    ['en' => 'It can be done.', 'ua' => 'Це можна зробити.'],
                                    ['en' => 'The problem can be solved.', 'ua' => 'Проблему можна вирішити.'],
                                    ['en' => 'This car can be repaired.', 'ua' => 'Цю машину можна відремонтувати.'],
                                    ['en' => 'The document can be downloaded.', 'ua' => 'Документ можна завантажити.'],
                                ],
                            ],
                            [
                                'label' => 'COULD + be + V3',
                                'color' => 'sky',
                                'description' => '<strong>Could</strong> виражає можливість у минулому або ввічливість/припущення.',
                                'examples' => [
                                    ['en' => 'It could be fixed.', 'ua' => 'Це можна було б полагодити.'],
                                    ['en' => 'The mistake could be avoided.', 'ua' => 'Помилки можна було уникнути.'],
                                    ['en' => 'More information could be provided.', 'ua' => 'Можна було б надати більше інформації.'],
                                    ['en' => 'The meeting could be rescheduled.', 'ua' => 'Зустріч можна було б перенести.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Заперечення: <strong>can\'t / couldn\'t + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'It can\'t be done.', 'ua' => 'Це неможливо зробити.'],
                                    ['en' => 'It couldn\'t be explained.', 'ua' => 'Це не можна було пояснити.'],
                                ],
                                'note' => '📌 Cannot = can\'t — одне слово у запереченні!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-must-should',
                    'tags' => ['Must', 'Should', 'Obligation', 'Advice', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '3. MUST / SHOULD — обов\'язок та порада',
                        'sections' => [
                            [
                                'label' => 'MUST + be + V3',
                                'color' => 'blue',
                                'description' => '<strong>Must</strong> виражає сильний обов\'язок або необхідність.',
                                'examples' => [
                                    ['en' => 'It must be done.', 'ua' => 'Це має бути зроблено.'],
                                    ['en' => 'The rules must be followed.', 'ua' => 'Правила мають дотримуватися.'],
                                    ['en' => 'This form must be completed.', 'ua' => 'Ця форма має бути заповнена.'],
                                    ['en' => 'Safety measures must be taken.', 'ua' => 'Заходи безпеки мають бути вжиті.'],
                                ],
                            ],
                            [
                                'label' => 'SHOULD + be + V3',
                                'color' => 'amber',
                                'description' => '<strong>Should</strong> виражає пораду або рекомендацію.',
                                'examples' => [
                                    ['en' => 'It should be checked.', 'ua' => 'Це слід перевірити.'],
                                    ['en' => 'The report should be submitted.', 'ua' => 'Звіт слід подати.'],
                                    ['en' => 'More attention should be paid.', 'ua' => 'Слід приділити більше уваги.'],
                                    ['en' => 'The issue should be discussed.', 'ua' => 'Це питання слід обговорити.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Заперечення: <strong>mustn\'t / shouldn\'t + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'It mustn\'t be touched.', 'ua' => 'До цього не можна торкатися.'],
                                    ['en' => 'It shouldn\'t be ignored.', 'ua' => 'Це не слід ігнорувати.'],
                                ],
                                'note' => '📌 Mustn\'t = заборона, shouldn\'t = не рекомендується',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'uuid_key' => 'usage-panels-will-would',
                    'tags' => ['Will', 'Would', 'Future', 'Conditional', 'CEFR B2'],
                    'body' => json_encode([
                        'title' => '4. WILL / WOULD — майбутнє та умовний спосіб',
                        'sections' => [
                            [
                                'label' => 'WILL + be + V3',
                                'color' => 'purple',
                                'description' => '<strong>Will</strong> виражає майбутню дію в пасиві.',
                                'examples' => [
                                    ['en' => 'It will be done tomorrow.', 'ua' => 'Це буде зроблено завтра.'],
                                    ['en' => 'The project will be completed.', 'ua' => 'Проект буде завершено.'],
                                    ['en' => 'You will be notified.', 'ua' => 'Вас повідомлять.'],
                                    ['en' => 'The package will be delivered.', 'ua' => 'Посилка буде доставлена.'],
                                ],
                            ],
                            [
                                'label' => 'WOULD + be + V3',
                                'color' => 'indigo',
                                'description' => '<strong>Would</strong> виражає умовний спосіб або ввічливість.',
                                'examples' => [
                                    ['en' => 'It would be appreciated.', 'ua' => 'Це було б оцінено.'],
                                    ['en' => 'Your help would be needed.', 'ua' => 'Ваша допомога була б потрібна.'],
                                    ['en' => 'The offer would be considered.', 'ua' => 'Пропозиція була б розглянута.'],
                                    ['en' => 'Any feedback would be welcomed.', 'ua' => 'Будь-який відгук був би вітаним.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Заперечення: <strong>won\'t / wouldn\'t + be + V3</strong>',
                                'examples' => [
                                    ['en' => 'It won\'t be finished on time.', 'ua' => 'Це не буде завершено вчасно.'],
                                    ['en' => 'It wouldn\'t be accepted.', 'ua' => 'Це не було б прийнято.'],
                                ],
                                'note' => '📌 Won\'t = will not, wouldn\'t = would not',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B2',
                    'uuid_key' => 'usage-panels-may-might',
                    'tags' => ['May', 'Might', 'Probability', 'Permission', 'CEFR B2'],
                    'body' => json_encode([
                        'title' => '5. MAY / MIGHT — ймовірність та дозвіл',
                        'sections' => [
                            [
                                'label' => 'MAY + be + V3',
                                'color' => 'emerald',
                                'description' => '<strong>May</strong> виражає можливість або дозвіл.',
                                'examples' => [
                                    ['en' => 'It may be changed.', 'ua' => 'Це може бути змінено.'],
                                    ['en' => 'Smoking may not be allowed.', 'ua' => 'Куріння може бути заборонено.'],
                                    ['en' => 'The flight may be delayed.', 'ua' => 'Рейс може бути затриманий.'],
                                    ['en' => 'Personal data may be collected.', 'ua' => 'Персональні дані можуть збиратися.'],
                                ],
                            ],
                            [
                                'label' => 'MIGHT + be + V3',
                                'color' => 'sky',
                                'description' => '<strong>Might</strong> виражає меншу ймовірність, ніж may.',
                                'examples' => [
                                    ['en' => 'It might be cancelled.', 'ua' => 'Це може бути скасовано.'],
                                    ['en' => 'The meeting might be postponed.', 'ua' => 'Зустріч може бути перенесена.'],
                                    ['en' => 'Errors might be found.', 'ua' => 'Можуть бути знайдені помилки.'],
                                    ['en' => 'The price might be reduced.', 'ua' => 'Ціна може бути знижена.'],
                                ],
                            ],
                            [
                                'label' => 'Різниця',
                                'color' => 'purple',
                                'description' => 'May = більша ймовірність (~50%), Might = менша (~30%)',
                                'examples' => [
                                    ['en' => 'It may be approved. (likely)', 'ua' => 'Це може бути схвалено. (ймовірно)'],
                                    ['en' => 'It might be approved. (less likely)', 'ua' => 'Можливо, це буде схвалено. (менш ймовірно)'],
                                ],
                                'note' => '📌 У минулому для припущень: may/might have been + V3',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'usage-panels-questions',
                    'tags' => ['Questions', 'Inversion', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '6. Питання з модальними дієсловами в пасиві',
                        'sections' => [
                            [
                                'label' => 'Yes/No Questions',
                                'color' => 'blue',
                                'description' => 'Модальне дієслово переміщуємо на початок речення.',
                                'examples' => [
                                    ['en' => 'Can it be done?', 'ua' => 'Чи можна це зробити?'],
                                    ['en' => 'Must it be finished today?', 'ua' => 'Чи має це бути закінчено сьогодні?'],
                                    ['en' => 'Should it be checked?', 'ua' => 'Чи слід це перевірити?'],
                                    ['en' => 'Will it be delivered?', 'ua' => 'Чи буде це доставлено?'],
                                ],
                            ],
                            [
                                'label' => 'Wh-Questions',
                                'color' => 'amber',
                                'description' => 'Wh-слово + модальне + be + V3?',
                                'examples' => [
                                    ['en' => 'When can it be done?', 'ua' => 'Коли це можна зробити?'],
                                    ['en' => 'How should it be prepared?', 'ua' => 'Як це слід приготувати?'],
                                    ['en' => 'Where will it be held?', 'ua' => 'Де це буде проводитися?'],
                                    ['en' => 'Why must it be changed?', 'ua' => 'Чому це має бути змінено?'],
                                ],
                            ],
                            [
                                'label' => 'Структура',
                                'color' => 'rose',
                                'description' => 'Формула питання:',
                                'examples' => [
                                    ['en' => '(Wh-word) + Modal + Subject + be + V3 + ...?', 'ua' => '(Wh-слово) + Модальне + Підмет + be + V3 + ...?'],
                                ],
                                'note' => '📌 Be завжди залишається в базовій формі!',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'comparison-table',
                    'tags' => ['Summary', 'Comparison', 'All Modals', 'CEFR B1'],
                    'body' => json_encode([
                        'title' => '7. Порівняльна таблиця модальних дієслів',
                        'intro' => 'Усі модальні дієслова в пасиві:',
                        'rows' => [
                            [
                                'en' => 'can + be + V3',
                                'ua' => 'можливість',
                                'note' => 'It can be done.',
                            ],
                            [
                                'en' => 'could + be + V3',
                                'ua' => 'можливість (минуле/ввічливість)',
                                'note' => 'It could be fixed.',
                            ],
                            [
                                'en' => 'must + be + V3',
                                'ua' => 'обов\'язок',
                                'note' => 'It must be finished.',
                            ],
                            [
                                'en' => 'should + be + V3',
                                'ua' => 'порада',
                                'note' => 'It should be checked.',
                            ],
                            [
                                'en' => 'will + be + V3',
                                'ua' => 'майбутнє',
                                'note' => 'It will be done.',
                            ],
                            [
                                'en' => 'would + be + V3',
                                'ua' => 'умовний спосіб',
                                'note' => 'It would be appreciated.',
                            ],
                            [
                                'en' => 'may + be + V3',
                                'ua' => 'ймовірність',
                                'note' => 'It may be changed.',
                            ],
                            [
                                'en' => 'might + be + V3',
                                'ua' => 'менша ймовірність',
                                'note' => 'It might be cancelled.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'box',
                    'column' => 'right',
                    'seeder' => self::class,
                    'level' => 'B1',
                    'uuid_key' => 'tips',
                    'tags' => ['Tips', 'Learning'],
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Формула завжди однакова: <strong>Modal + be + V3</strong></p>
<p><strong>Be</strong> ніколи не змінюється — завжди базова форма!</p>
<p>Для <strong>питань</strong> — модальне на початок: <span class="gw-en">Can it be done?</span></p>
<p>Для <strong>заперечень</strong> — модальне + not: <span class="gw-en">It can't be done.</span></p>
<p>Вивчи <strong>значення кожного модального</strong> — вони визначають зміст речення.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
