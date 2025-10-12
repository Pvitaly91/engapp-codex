<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\GrammarPageSeeder;

class PastPerfectPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'past-perfect';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Perfect — Минулий доконаний час',
            'subtitle_html' => 'Використовуємо, щоб показати дію, яка сталася <strong>раніше іншої минулої події</strong>.',
            'subtitle_text' => 'Використовуємо, щоб показати дію, яка сталася раніше іншої минулої події.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Подія А</strong> відбулася, а потім сталася <strong>подія Б</strong> (обидві в минулому). Для події А — <em>Past Perfect</em>, для події Б — <em>Past Simple</em>.</li>
<li>Часто з маркерами: <em>before, after, by the time, already, when</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I had finished my homework <u>before</u> my friend called.</div>
<div class="gw-ua">Я закінчив домашнє завдання <u>перед тим</u>, як подзвонив друг.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had</span> + <span style="color:#86efac">V3 (дієслово у 3-й формі / Past Participle)</span>
I had <span style="color:#86efac">seen</span> / She had <span style="color:#86efac">gone</span> / They had <span style="color:#86efac">eaten</span></pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had not</span> (hadn’t) + V3
I hadn’t <span style="color:#86efac">seen</span> that movie before.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + [підмет] + V3?
Had you <span style="color:#86efac">studied</span> before the test?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">before — перед</span>
<span class="gw-chip">after — після</span>
<span class="gw-chip">by the time — до того часу як</span>
<span class="gw-chip">already — вже</span>
<span class="gw-chip">when — коли</span>
<span class="gw-chip">until/till — до (моменту)</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time we started, they <strong>had already prepared</strong> everything.</div>
<div class="gw-ua">До того, як ми почали, вони <strong>вже підготували</strong> все.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>A сталося перед B → A: Past Perfect, B: Past Simple.</strong></p>
<p class="gw-ua">Коли порядок подій і так зрозумілий (через <em>before/after</em>), <em>Past Perfect</em> інколи можна опустити. Але з ним зрозуміліше.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">When I arrived, she <strong>had left</strong>.</div>
<div class="gw-ua">Коли я прийшов, вона <strong>вже пішла</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Past Perfect">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Simple</strong></td>
<td>Звичайна минула дія/факт (B)</td>
<td>V2 (went, saw) / did + V1</td>
<td><span class="gw-en">My friend <strong>called</strong>.</span></td>
</tr>
<tr>
<td><strong>Past Perfect</strong></td>
<td>Раніша минула дія перед іншою (A)</td>
<td>had + V3</td>
<td><span class="gw-en">I <strong>had finished</strong> before he called.</span></td>
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
<li><span class="tag-warn">✗</span> Використовувати <em>had + V3</em> без другої минулої події/контексту.</li>
<li><span class="tag-warn">✗</span> Плутати з <em>Present Perfect</em> (це про зв’язок із теперішнім, а не з іншою минулою дією).</li>
<li><span class="tag-ok">✓</span> Думай: “<em>Що сталося раніше?</em>” — туди став <strong>Past Perfect</strong>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
