<?php

namespace Database\Seeders\Pages\Tenses;

class PastPerfectContinuousPageSeeder extends TensePageSeeder
{
    protected function slug(): string
    {
        return 'past-perfect-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Perfect Continuous — Минулий доконано-тривалий час',
            'subtitle_html' => 'Показує, що тривала дія <strong>відбувалася певний час до іншої минулої події/моменту</strong> і часто має сліди/наслідок у той момент.',
            'subtitle_text' => 'Показує, що тривала дія відбувалася певний час до іншої минулої події/моменту і часто має сліди/наслідок у той момент.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Тривалість до B</strong>: дія почалась раніше і тривала до іншої минулої події (B).</li>
<li><strong>Причина стану</strong> у минулому: втомлений, мокрий, брудний тощо на момент B.</li>
<li><strong>Питання “скільки часу?”</strong> до B: <em>for/since, how long</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">He <strong>had been waiting</strong> for two hours <u>before</u> the bus came.</div>
<div class="gw-ua">Він <strong>чекав</strong> дві години, <u>перш ніж</u> автобус приїхав.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had been</span> + <span style="color:#86efac">V-ing</span>
They <span style="color:#93c5fd">had been</span> <span style="color:#86efac">working</span> all day.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + had not (hadn’t) been + V-ing
She <span style="color:#93c5fd">hadn’t been</span> <span style="color:#86efac">sleeping</span> well for weeks.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + [підмет] + <span style="color:#93c5fd">been</span> + V-ing?
<span style="color:#93c5fd">Had</span> you <span style="color:#93c5fd">been</span> <span style="color:#86efac">studying</span> long before the exam?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">for</span>
<span class="gw-chip">since</span>
<span class="gw-chip">before</span>
<span class="gw-chip">by the time</span>
<span class="gw-chip">until/till</span>
<span class="gw-chip">how long</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time I arrived, they <strong>had been working</strong> for hours.</div>
<div class="gw-ua">Коли я приїхав, вони <strong>працювали</strong> вже декілька годин.</div>
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
<p><strong>A тривало до B</strong>: A — Past Perfect Continuous, B — Past Simple/Continuous.</p>
<p class="gw-ua">Якщо важливий <b>результат</b> до B — використовуй <b>Past Perfect</b>. Якщо важлива <b>тривалість</b> — <b>Past Perfect Continuous</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She was tired because she <strong>had been running</strong>.</div>
<div class="gw-ua">Вона була втомлена, бо <strong>бігла</strong> (тривалість пояснює стан).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Continuous та Past Perfect Continuous">
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
<td><strong>Past Continuous</strong></td>
<td>Дія була у процесі в конкретний момент у минулому (без акценту на «до»)</td>
<td>was/were + V-ing</td>
<td><span class="gw-en">At 6 pm I <strong>was working</strong>.</span></td>
</tr>
<tr>
<td><strong>Past Perfect Continuous</strong></td>
<td>Тривала дія <u>до</u> іншої минулої події/моменту</td>
<td>had been + V-ing</td>
<td><span class="gw-en">I <strong>had been working</strong> for 3 hours before he called.</span></td>
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
<li><span class="tag-warn">✗</span> Ставити <em>had + V3</em> замість <em>had been + V-ing</em>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Використовувати без «точки B» у минулому (потрібен контекст другої події/моменту).</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <strong>had been + V-ing</strong> і зазвичай <em>for/since</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
