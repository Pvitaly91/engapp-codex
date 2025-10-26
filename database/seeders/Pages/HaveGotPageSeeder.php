<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class HaveGotPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'have-got';
    }

    protected function page(): array
    {
        return [
            'title' => 'Have got — володіння та характеристики',
            'subtitle_html' => <<<'HTML'
<p><strong>Have got</strong> вживаємо, щоб говорити про належність, риси зовнішності чи відчуття. У розмовній мові це дуже типова
форма замість <em>have</em>.</p>
HTML,
            'subtitle_text' => 'Have got описує володіння, характеристики та тимчасові стани.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-structures',
                'title' => 'Базові конструкції',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми have got">
<thead>
<tr>
<th>Тип</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Ствердження</td>
<td>have/has + got</td>
<td><span class="gw-en">I have got a bike.</span></td>
</tr>
<tr>
<td>Заперечення</td>
<td>haven’t / hasn’t + got</td>
<td><span class="gw-en">She hasn’t got any siblings.</span></td>
</tr>
<tr>
<td>Питання</td>
<td>Have/Has + підмет + got?</td>
<td><span class="gw-en">Have you got time?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли обираємо have got',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Фізичні характеристики: <span class="gw-en">He has got blue eyes.</span></li>
<li>Сім’я та відносини: <span class="gw-en">I have got two sisters.</span></li>
<li>Володіння: <span class="gw-en">We’ve got a new apartment.</span></li>
<li>Тимчасові стани/проблеми: <span class="gw-en">I’ve got a cold.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Скорочення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>I’ve got</strong>, <strong>you’ve got</strong>, <strong>he’s got</strong> тощо.</li>
<li>У запитаннях скорочення не використовуємо: <span class="gw-en">Have you got…?</span></li>
<li>У негативі частіше: <strong>haven’t got</strong>, <strong>hasn’t got</strong>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">We’ve got plenty of time.</div>
<div class="gw-ua">У нас достатньо часу.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Have vs Have got',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Have got</strong> — переважно в британській англійській, розмовний стиль.</li>
<li><strong>Have</strong> — універсально, вживається з допоміжним <em>do</em>: <span class="gw-en">Do you have a car?</span></li>
<li>У діловому листуванні краще використовувати форму <em>have</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Часові форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p><strong>Have got</strong> обмежене теперішнім часом. Для минулого/майбутнього використовуємо:</p>
<ul class="gw-list">
<li><span class="gw-en">I had</span> a car last year.</li>
<li><span class="gw-en">I will have</span> an exam tomorrow.</li>
<li>Для зобов’язання: <span class="gw-en">I have to</span> finish this.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Міні-порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Have got vs Have">
<thead>
<tr>
<th>Ситуація</th>
<th>Have got</th>
<th>Have</th>
</tr>
</thead>
<tbody>
<tr>
<td>Розмова з другом</td>
<td><span class="gw-en">I’ve got a new job!</span></td>
<td><span class="gw-en">I have a new job!</span></td>
</tr>
<tr>
<td>Опитування / анкета</td>
<td>—</td>
<td><span class="gw-en">Do you have allergies?</span></td>
</tr>
<tr>
<td>Минуле</td>
<td>—</td>
<td><span class="gw-en">I had a headache.</span></td>
</tr>
<tr>
<td>Зобов’язання</td>
<td>—</td>
<td><span class="gw-en">I have to leave.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Нагадування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">📝</div>
<div>
<p>У коротких відповідях:<br><span class="gw-en">Have you got a charger?</span> — <span class="gw-en">Yes, I have.</span></p>
<p>Не додаємо <em>got</em> у відповіді.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
