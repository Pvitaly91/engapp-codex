<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ThirdConditionalPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'third-conditional';
    }

    protected function page(): array
    {
        return [
            'title' => 'Third Conditional — нереальне минуле',
            'subtitle_html' => <<<'HTML'
Описуємо ситуації, які <strong>не відбулися</strong> в минулому, та їх можливі наслідки. Використовуємо для
жалю, аналізу помилок та уявних альтернатив.
HTML,
            'subtitle_text' => <<<'HTML'
Описуємо ситуації, які не відбулися в минулому, та їх можливі наслідки. Використовуємо для
      жалю, аналізу помилок та уявних альтернатив.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Жаль</strong> про минуле: If I had left earlier, I wouldn’t have missed the flight.</li>
<li><strong>Альтернативні сценарії</strong>: If they had studied, they would have passed.</li>
<li><strong>Критика</strong>: If he had listened, we wouldn’t have been late.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If she had called me, I would have helped.</div>
<div class="gw-ua">Якби вона подзвонила, я б допоміг.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якби</span>
<span class="gw-chip">had — у зворотному порядку</span>
<span class="gw-chip">unless — якби не</span>
<span class="gw-chip">provided (that) — за умови що</span>
<span class="gw-chip">but for — якби не (форм.)</span>
</div>
<div class="gw-ex">
<div class="gw-en">But for your help, we would have failed.</div>
<div class="gw-ua">Якби не твоя допомога, ми б провалилися.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🕰️</div>
<div>
<p>Підрядна частина — <strong>Past Perfect</strong> (<em>had + V3</em>), головна —
<strong>would/could/might have + V3</strong>.</p>
<p class="gw-ua">If they <u>had left</u> on time, they <u>would have arrived</u> safely.</p>
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
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">would have</span> + V3
If I <span style="color:#86efac">had seen</span> her, I <span style="color:#93c5fd">would have said</span> hello.</pre>
<div class="gw-code-badge">Модальні варіації</div>
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">could/might have</span> + V3
If he <span style="color:#86efac">had tried</span> harder, he <span style="color:#93c5fd">might have succeeded</span>.</pre>
<div class="gw-code-badge">Inversion</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + підмет + V3, would have + V3
Had we <span style="color:#86efac">known</span>, we <span style="color:#93c5fd">would have acted</span> differently.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади сценаріїв',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Розбір помилок</strong>: If the team had trained more, they would have won.</li>
<li><strong>Пояснення причин</strong>: If she hadn’t forgotten the key, we wouldn’t have waited.</li>
<li><strong>Історичні «що якби»</strong>: If the weather had been better, the battle might have changed.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I had checked the schedule, I wouldn’t have missed the class.</div>
<div class="gw-ua">Якби я перевірив розклад, не пропустив би заняття.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Відмінність від Second Conditional',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Third vs Second Conditional">
<thead>
<tr>
<th>Third</th>
<th>Second</th>
</tr>
</thead>
<tbody>
<tr>
<td>Про минуле, що не сталося.</td>
<td>Про теперішнє/майбутнє уявне.</td>
</tr>
<tr>
<td>If + Past Perfect, would have + V3.</td>
<td>If + Past Simple, would + V1.</td>
</tr>
<tr>
<td>Наслідок також у минулому.</td>
<td>Наслідок у теперішньому/майбутньому.</td>
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
<li><span class="tag-warn">✗</span> Плутати порядок: <em>would have had</em> в підрядній частині.</li>
<li><span class="tag-warn">✗</span> Використовувати <em>Past Simple</em> замість <em>Past Perfect</em>.</li>
<li><span class="tag-ok">✓</span> Для меншої впевненості використовуйте <strong>might have/could have</strong>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
