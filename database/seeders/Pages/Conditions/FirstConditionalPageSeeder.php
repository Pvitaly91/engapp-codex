<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\GrammarPageSeeder;

class FirstConditionalPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'first-conditional';
    }

    protected function page(): array
    {
        return [
            'title' => 'First Conditional — реальні майбутні наслідки',
            'subtitle_html' => <<<'HTML'
Використовуємо, коли умова можлива, а результат — очікуваний у майбутньому. Говоримо про плани,
попередження та обіцянки.
HTML,
            'subtitle_text' => <<<'HTML'
Використовуємо, коли умова можлива, а результат — очікуваний у майбутньому. Говоримо про плани,
      попередження та обіцянки.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Реальні прогнози</strong>: «If it rains, we’ll stay home».</li>
<li><strong>Поради/попередження</strong>: «If you touch that, you’ll get burnt».</li>
<li><strong>Умовні обіцянки</strong> та угоди.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you study tonight, you will pass tomorrow.</div>
<div class="gw-ua">Якщо сьогодні повчишся, завтра складеш.</div>
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
<span class="gw-chip">unless — якщо не</span>
<span class="gw-chip">as soon as — щойно</span>
<span class="gw-chip">once — як тільки</span>
<span class="gw-chip">provided (that) — за умови що</span>
<span class="gw-chip">in case — про всяк випадок</span>
</div>
<div class="gw-ex">
<div class="gw-en">Unless you leave now, you will miss the bus.</div>
<div class="gw-ua">Якщо не підеш зараз, пропустиш автобус.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">⏳</div>
<div>
<p>У підрядній частині <strong>не вживаємо will</strong>. Майбутнє передаємо <strong>Present Simple</strong> або Present
Perfect.</p>
<p class="gw-ua">If she <u>finishes</u> early, she <u>will call</u> us.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">will</span> + V1
If the train <span style="color:#86efac">is</span> late, we <span style="color:#93c5fd">will wait</span>.</pre>
<div class="gw-code-badge">Модальні дієслова</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">can/may/might</span> + V1
If you <span style="color:#86efac">need</span> help, I <span style="color:#93c5fd">can send</span> you instructions.</pre>
<div class="gw-code-badge">Imperative + if</div>
<pre class="gw-formula"><span style="color:#93c5fd">Імператив</span> + if + Present Simple
Call me if anything <span style="color:#86efac">changes</span>.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади ситуацій',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Погода</strong>: If it clears up, we’ll go hiking.</li>
<li><strong>Робота/навчання</strong>: If they approve the budget, we’ll start the project.</li>
<li><strong>Повсякденні справи</strong>: If I’m free tonight, I’ll join you.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you don’t water the plant, it will dry out.</div>
<div class="gw-ua">Якщо не поливатимеш рослину, вона всохне.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння з іншими типами',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння First Conditional">
<thead>
<tr>
<th>Тип</th>
<th>Ключова ідея</th>
<th>Формула</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Zero</strong></td>
<td>Загальні факти, рутини.</td>
<td>If + Present, Present.</td>
</tr>
<tr>
<td><strong>First</strong></td>
<td>Реальна майбутня ситуація.</td>
<td>If + Present, will/can/might + V1.</td>
</tr>
<tr>
<td><strong>Second</strong></td>
<td>Уявна/малоймовірна ситуація.</td>
<td>If + Past, would + V1.</td>
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
<li><span class="tag-warn">✗</span> Використання <em>will</em> у підрядній частині.</li>
<li><span class="tag-warn">✗</span> Застосування Past Simple замість Present Simple.</li>
<li><span class="tag-ok">✓</span> Для наказів використовуйте імператив: <em>Call me if...</em></li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
