<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class PastContinuousPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'past-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Continuous — Минулий тривалий час',
            'subtitle_html' => 'Використовуємо, щоб описати дію, яка <strong>була у процесі</strong> в конкретний момент у минулому.',
            'subtitle_text' => 'Використовуємо, щоб описати дію, яка була у процесі в конкретний момент у минулому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Дія в процесі</strong> у певний момент у минулому: «О 8-й я вечеряв».</li>
<li><strong>Фон для іншої дії</strong> (Past Simple): «Вона читала, коли він зайшов».</li>
<li><strong>Дві тривалі дії</strong>, що відбувались одночасно.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">At 9 pm yesterday, I <strong>was watching</strong> TV.</div>
<div class="gw-ua">Учора о 21:00 я <strong>дивився</strong> телевізор.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + was/were + V-ing
I <span style="color:#93c5fd">was</span> <span style="color:#86efac">reading</span>.
They <span style="color:#93c5fd">were</span> <span style="color:#86efac">playing</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + was/were + not + V-ing
She <span style="color:#93c5fd">wasn’t</span> <span style="color:#86efac">sleeping</span>.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Was/Were</span> + [підмет] + V-ing?
<span style="color:#93c5fd">Were</span> you <span style="color:#86efac">studying</span> at 10 pm?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">while</span>
<span class="gw-chip">when</span>
<span class="gw-chip">at 8 pm yesterday</span>
<span class="gw-chip">all evening</span>
<span class="gw-chip">the whole morning</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>was cooking</strong> while he <strong>was watching</strong> TV.</div>
<div class="gw-ua">Вона <strong>готувала</strong>, поки він <strong>дивився</strong> телевізор.</div>
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
<p><strong>Past Continuous</strong> = дія в процесі у конкретний момент у минулому.</p>
<p class="gw-ua">Часто йде разом із <b>Past Simple</b> — фонова дія + коротка подія.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">I <strong>was reading</strong> when he <strong>came</strong>.</div>
<div class="gw-ua">Я <strong>читав</strong>, коли він <strong>прийшов</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Past Continuous">
<thead>
<tr>
<th>Час</th>
<th>Використання</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Simple</strong></td>
<td>Коротка завершена дія</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">He <strong>came</strong> at 9.</span></td>
</tr>
<tr>
<td><strong>Past Continuous</strong></td>
<td>Дія у процесі у той момент</td>
<td>was/were + V-ing</td>
<td><span class="gw-en">I <strong>was reading</strong> when he came.</span></td>
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
<li><span class="tag-warn">✗</span> Забувати <b>was/were</b>: <em>*I reading</em>.</li>
<li><span class="tag-warn">✗</span> Використовувати Past Continuous для послідовних дій (там треба Past Simple).</li>
<li><span class="tag-ok">✓</span> Пам’ятай: Past Continuous = фон, Past Simple = головна дія.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
