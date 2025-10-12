<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ZeroConditionalPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'zero-conditional';
    }

    protected function page(): array
    {
        return [
            'title' => 'Zero Conditional — загальні факти та рутини',
            'subtitle_html' => 'Використовуємо, коли результат завжди відбувається за певної умови: закони природи, інструкції, правила.',
            'subtitle_text' => 'Використовуємо, коли результат завжди відбувається за певної умови: закони природи, інструкції, правила.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Факти природи</strong>: вода кипить при 100°C.</li>
<li><strong>Загальні правила</strong> та <strong>інструкції</strong>.</li>
<li><strong>Рутинні дії</strong> та звички, що повторюються.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you heat ice, it melts.</div>
<div class="gw-ua">Якщо нагріти лід, він тане.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якщо</span>
<span class="gw-chip">when — коли</span>
<span class="gw-chip">whenever — щоразу як</span>
<span class="gw-chip">as soon as — щойно</span>
<span class="gw-chip">unless — якщо не</span>
<span class="gw-chip">as long as — за умови що</span>
</div>
<div class="gw-ex">
<div class="gw-en">When the sun sets, it gets dark.</div>
<div class="gw-ua">Коли сонце сідає, темніє.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Обидві частини зазвичай у <strong>Present Simple</strong>, бо говоримо про загальну істину.</p>
<p class="gw-ua">If water <u>reaches</u> 0°C, it <u>freezes</u>.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули та варіації',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Present Simple, Present Simple
If plants <span style="color:#86efac">don’t get</span> water, they <span style="color:#86efac">die</span>.</pre>
<div class="gw-code-badge">Імператив у результаті</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">імператив</span>
If the alarm <span style="color:#86efac">rings</span>, <span style="color:#93c5fd">leave</span> the building.</pre>
<div class="gw-code-badge">З теперішнім перфектом</div>
<pre class="gw-formula">If + Present Perfect, Present Simple
If he <span style="color:#86efac">has finished</span>, he <span style="color:#86efac">goes</span> home.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові контексти',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Наукові факти</strong>: If light hits a prism, it splits.</li>
<li><strong>Правила безпеки</strong>: If there is a fire, call 101.</li>
<li><strong>Розклади/інструкції</strong>: If the bus arrives, people queue.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If students hand in homework late, the teacher deducts points.</div>
<div class="gw-ua">Якщо учні здають домашку пізно, учитель знімає бали.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Zero vs First Conditional',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Zero vs First Conditional">
<thead>
<tr>
<th>Zero</th>
<th>First</th>
</tr>
</thead>
<tbody>
<tr>
<td>Факт, що завжди правдивий.</td>
<td>Реальний наслідок у конкретній ситуації.</td>
</tr>
<tr>
<td>Present Simple + Present Simple.</td>
<td>Present Simple + will/can/might + V1.</td>
</tr>
<tr>
<td>Не змінюється від контексту.</td>
<td>Залежить від ймовірності у майбутньому.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використання <em>will</em> у будь-якій частині.</li>
<li><span class="tag-warn">✗</span> Змішування з First Conditional, коли йдеться про разову майбутню подію.</li>
<li><span class="tag-ok">✓</span> Переконайся, що мова про <strong>звичний результат</strong>, а не про прогноз.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
