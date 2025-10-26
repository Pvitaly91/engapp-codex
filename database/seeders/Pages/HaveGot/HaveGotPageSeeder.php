<?php

namespace Database\Seeders\Pages\HaveGot;

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
            'title' => 'Have got — володіння й характеристики',
            'subtitle_html' => <<<'HTML'
<p><strong>Have/has got</strong> — розмовний спосіб говорити про володіння, родинні зв’язки та характеристики.
У теперішньому часі воно часто замінює звичайне <em>have</em>.</p>
HTML,
            'subtitle_text' => 'Have/has got — розмовна конструкція для володіння, характерних рис і стосунків.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'possession',
                'title' => 'Володіння та характеристики',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми теперішнього часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + have/has got
I <span style="color:#86efac">have got</span> a bike.
She <span style="color:#86efac">has got</span> a dog.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + haven’t / hasn’t got
He <span style="color:#fca5a5">hasn’t got</span> any money.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula">Have/Has + [підмет] + got?
<span style="color:#93c5fd">Have</span> you got a minute?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Фізичне володіння: <span class="gw-en">They’ve got two cars.</span></li>
<li>Риси зовнішності чи характеру: <span class="gw-en">She’s got blue eyes.</span></li>
<li>Частини тіла, хвороби: <span class="gw-en">I’ve got a headache.</span></li>
<li>Родинні та соціальні зв’язки: <span class="gw-en">We’ve got many relatives.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Часові форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Минулий час: <strong>had</strong> (без <em>got</em>): <span class="gw-en">She had a car.</span></li>
<li>Майбутній час: <strong>will have</strong> або <strong>be going to have</strong>.</li>
<li><strong>Have got</strong> не вживається в Continuous.</li>
<li>Для обов’язку використовуйте <strong>have to</strong>, а не <em>have got</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння з have',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Have vs have got">
<thead>
<tr>
<th>Конструкція</th>
<th>Використання</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>have got</strong></td>
<td>Розмовна мова, теперішній час</td>
<td><span class="gw-en">I’ve got a meeting.</span></td>
</tr>
<tr>
<td><strong>have</strong></td>
<td>Формальні тексти, усі часи</td>
<td><span class="gw-en">We have many clients.</span></td>
</tr>
<tr>
<td><strong>have to</strong></td>
<td>Обов’язок / необхідність</td>
<td><span class="gw-en">I have to leave early.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Скорочення <strong>have/has</strong> + got → <em>’ve got</em>, <em>’s got</em> обов’язкові в розмовній мові.</li>
<li>У коротких відповідях повторюємо <strong>have</strong>: <span class="gw-en">Yes, I have.</span> / <span class="gw-en">No, she hasn’t.</span></li>
<li>У формальних листах краще писати <strong>have</strong> без <em>got</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
