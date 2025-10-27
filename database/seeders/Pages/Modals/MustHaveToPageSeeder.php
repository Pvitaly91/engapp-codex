<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class MustHaveToPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-must-have-to';
    }

    protected function page(): array
    {
        return [
            'title' => 'Must / Have to — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>Must</strong> та <strong>have to</strong> виражають обов’язок, але відрізняються джерелом вимоги. <strong>Must</strong>
часто означає внутрішнє переконання, а <strong>have to</strong> — зовнішні правила.</p>
HTML,
            'subtitle_text' => 'Must передає внутрішній обов’язок, а have to — зовнішню необхідність або правила.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Must / Have to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Must</strong> — внутрішній або суб’єктивний обов’язок. <span class="gw-en">I must call her.</span></li>
<li><strong>Have to</strong> — зовнішня вимога, правила. <span class="gw-en">You have to wear a uniform.</span></li>
<li><strong>Mustn’t</strong> означає «заборонено», тоді як <strong>don’t have to</strong> = «не обов’язково».</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Форми в різних часах',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми must та have to">
<thead>
<tr>
<th>Час</th>
<th>Must</th>
<th>Have to</th>
</tr>
</thead>
<tbody>
<tr>
<td>Теперішній</td>
<td><span class="gw-en">must</span></td>
<td><span class="gw-en">have to / has to</span></td>
</tr>
<tr>
<td>Минулий</td>
<td><span class="gw-en">had to</span> (використовуємо замість must)</td>
<td><span class="gw-en">had to</span></td>
</tr>
<tr>
<td>Майбутній</td>
<td><span class="gw-en">will have to</span></td>
<td><span class="gw-en">will have to</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">You must wear a helmet.</div>
<div class="gw-ua">Ти мусиш вдягати шолом. (правило або сильний обов’язок)</div>
</div>
<div class="gw-ex">
<div class="gw-en">We have to submit the report by Monday.</div>
<div class="gw-ua">Ми повинні подати звіт до понеділка. (зовнішня вимога)</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради та помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Не плутайте <strong>mustn’t</strong> (заборона) з <strong>don’t have to</strong> (не обов’язково).</li>
<li><span class="tag-ok">✓</span> У минулому вживаємо <em>had to</em>, адже <em>must</em> не має минулої форми.</li>
<li><span class="tag-ok">✓</span> Для логічного висновку: <span class="gw-en">She must be at work.</span></li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
