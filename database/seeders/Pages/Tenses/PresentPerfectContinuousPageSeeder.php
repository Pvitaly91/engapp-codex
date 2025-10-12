<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class PresentPerfectContinuousPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'present-perfect-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Perfect Continuous — Теперішній доконано-тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>почалась у минулому і триває до тепер</strong> або має <strong>сліди/ефект</strong> зараз. Акцент на <b>тривалості</b>.',
            'subtitle_text' => 'Показує, що дія почалась у минулому і триває до тепер або має сліди/ефект зараз. Акцент на тривалості.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Дія триває донині</strong>: «Я вчуся вже 2 години».</li>
<li><strong>Є сліди зараз</strong> (задишка, бруд, втома): «Вона вся у фарбі — вона фарбувала».</li>
<li><strong>Питання про тривалість</strong>: <em>How long...?</em> з <em>for/since</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>have been studying</strong> for three hours.</div>
<div class="gw-ua">Я <strong>вчуся</strong> вже три години (і досі).</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">have/has been</span> + <span style="color:#86efac">V-ing</span>
I/We/You/They <span style="color:#93c5fd">have been</span> <span style="color:#86efac">working</span>.
He/She/It <span style="color:#93c5fd">has been</span> <span style="color:#86efac">working</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + have/has <b>not</b> been + V-ing
She <span style="color:#93c5fd">hasn’t been</span> <span style="color:#86efac">sleeping</span> well lately.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Have/Has</span> + [підмет] + <span style="color:#93c5fd">been</span> + V-ing?
<span style="color:#93c5fd">Have</span> you <span style="color:#93c5fd">been</span> <span style="color:#86efac">working</span> here long?</pre>
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
<span class="gw-chip">lately / recently</span>
<span class="gw-chip">all day / all morning</span>
<span class="gw-chip">how long</span>
</div>
<div class="gw-ex">
<div class="gw-en">We <strong>have been living</strong> here <u>since</u> 2020.</div>
<div class="gw-ua">Ми <strong>живемо</strong> тут <u>з</u> 2020 року (й досі).</div>
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
<p><strong>Тривалість важливіша за результат</strong> → Present Perfect Continuous.</p>
<p>Якщо важливий <b>результат «вже зроблено»</b> (без фокусу на процесі) → <b>Present Perfect</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She’s tired because she <strong>has been running</strong>.</div>
<div class="gw-ua">Вона втомлена, бо <strong>бігала</strong> (бачимо сліди дії).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Perfect та Present Perfect Continuous">
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
<td><strong>Present Perfect</strong></td>
<td>Результат «вже зроблено»</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have finished</strong> the report.</span></td>
</tr>
<tr>
<td><strong>Present Perfect Continuous</strong></td>
<td>Тривалість/сліди зараз</td>
<td>have/has been + V-ing</td>
<td><span class="gw-en">I <strong>have been working</strong> on the report all day.</span></td>
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
<li><span class="tag-warn">✗</span> Ставити <em>have/has + V3</em> замість <em>have/has been + V-ing</em>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Плутати <b>for</b> (період) і <b>since</b> (точка відліку).</li>
<li><span class="tag-ok">✓</span> Для 3-ї особи однини — <b>has been</b>; для інших — <b>have been</b>.</li>
<li><span class="tag-ok">✓</span> Зі <em>state verbs</em> (know, like) зазвичай не використовуємо форму Continuous.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
