<?php

namespace Database\Seeders\Pages\Tenses;

class FuturePerfectContinuousPageSeeder extends TensePageSeeder
{
    protected function slug(): string
    {
        return 'future-perfect-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Perfect Continuous — Майбутній доконано-тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>триватиме певний час</strong> і <strong>буде тривати/щойно завершиться</strong> до конкретної точки у майбутньому. Акцент на <b>тривалості</b> до дедлайну.',
            'subtitle_text' => 'Показує, що дія триватиме певний час і буде тривати/щойно завершиться до конкретної точки у майбутньому. Акцент на тривалості до дедлайну.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Скільки часу до майбутньої точки</strong>: до певного моменту дія вже триватиме N часу.</li>
<li><strong>Очікуваний стан/наслідок</strong> у майбутній точці (втома, досвід).</li>
<li>Часто з <em>for/since</em>, <em>by (then)</em>, <em>by the time</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">By June, I <strong>will have been working</strong> here for a year.</div>
<div class="gw-ua">До червня я <strong>працюватиму</strong> тут вже рік.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will have been</span> + <span style="color:#86efac">V-ing</span>
She <span style="color:#93c5fd">will have been</span> <span style="color:#86efac">studying</span> for hours by noon.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) have been + V-ing
They <span style="color:#93c5fd">won’t have been</span> <span style="color:#86efac">waiting</span> long by 5 pm.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">have been</span> + V-ing?
<span style="color:#93c5fd">Will</span> you <span style="color:#93c5fd">have been</span> <span style="color:#86efac">working</span> here for a year by May?</pre>
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
<span class="gw-chip">by then</span>
<span class="gw-chip">by the time</span>
<span class="gw-chip">before</span>
</div>
<div class="gw-ex">
<div class="gw-en">By 2030, they <strong>will have been living</strong> abroad for a decade.</div>
<div class="gw-ua">До 2030 року вони <strong>проживатимуть</strong> за кордоном уже десятиліття.</div>
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
<p><strong>Є майбутня точка часу</strong> → до неї дія вже певний час триває.</p>
<p>Результат «буде зроблено до…» без акценту на процес — це <b>Future Perfect (will have + V3)</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She’ll be exhausted because she <strong>will have been running</strong> all morning.</div>
<div class="gw-ua">Вона буде втомлена, бо <strong>бігатиме</strong> весь ранок.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Perfect та Future Perfect Continuous">
<thead>
<tr>
<th>Час</th>
<th>Акцент</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Perfect</strong></td>
<td>Результат до майбутньої точки</td>
<td>will have + V3</td>
<td><span class="gw-en">By 6 pm, I <strong>will have finished</strong> the report.</span></td>
</tr>
<tr>
<td><strong>Future Perfect Continuous</strong></td>
<td>Тривалість до майбутньої точки</td>
<td>will have been + V-ing</td>
<td><span class="gw-en">By 6 pm, I <strong>will have been working</strong> on it for 8 hours.</span></td>
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
<li><span class="tag-warn">✗</span> Плутати з Future Perfect: <em>*will have + V3</em> замість <b>will have been + V-ing</b>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Використовувати без «майбутньої точки» (<em>by/before/by the time</em>), де потрібен контекст.</li>
<li><span class="tag-ok">✓</span> Завжди додавай <b>been</b>: <em>will have been + V-ing</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
