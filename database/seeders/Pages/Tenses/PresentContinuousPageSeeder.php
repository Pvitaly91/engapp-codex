<?php

namespace Database\Seeders\Pages\Tenses;

class PresentContinuousPageSeeder extends TensePageSeeder
{
    protected function slug(): string
    {
        return 'present-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Continuous — Теперішній тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>відбувається зараз</strong>, навколо теперішнього моменту або є <strong>тимчасовою</strong>. Також — про <strong>узгоджені майбутні плани</strong>.',
            'subtitle_text' => 'Показує, що дія відбувається зараз, навколо теперішнього моменту або є тимчасовою. Також — про узгоджені майбутні плани.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Зараз/у цей період</strong>: дія відбувається у момент мовлення або близько до нього.</li>
<li><strong>Тимчасові ситуації</strong>, зміни, тренди: «працюю над проєктом цього тижня».</li>
<li><strong>Узгоджені плани</strong> на близьке майбутнє (квитки/домовленості): «Я зустрічаюсь о 7».</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>am working</strong> now.</div>
<div class="gw-ua">Я <strong>зараз працюю</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">am/is/are</span> + <span style="color:#86efac">V-ing</span>
I <span style="color:#93c5fd">am</span> <span style="color:#86efac">reading</span>.
She <span style="color:#93c5fd">is</span> <span style="color:#86efac">studying</span>.
They <span style="color:#93c5fd">are</span> <span style="color:#86efac">playing</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + am/is/are <b>not</b> + V-ing
He <span style="color:#93c5fd">isn’t</span> <span style="color:#86efac">sleeping</span>.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Am/Is/Are</span> + [підмет] + V-ing?
<span style="color:#93c5fd">Are</span> you <span style="color:#86efac">coming</span> tonight?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">now</span>
<span class="gw-chip">right now</span>
<span class="gw-chip">at the moment</span>
<span class="gw-chip">currently</span>
<span class="gw-chip">these days</span>
<span class="gw-chip">this week/month</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>isn’t watching</strong> TV at the moment.</div>
<div class="gw-ua">Вона <strong>не дивиться</strong> телевізор у цей момент.</div>
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
<p><strong>State verbs</strong> (know, like, love, believe, want тощо) зазвичай <b>не</b> вживаються у Continuous у звичайному значенні.</p>
<p>Для <b>узгоджених планів</b> на близьке майбутнє Present Continuous звучить природніше, ніж <em>will</em>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">We <strong>are meeting</strong> at 7 pm.</div>
<div class="gw-ua">Ми <strong>зустрічаємось</strong> о 19:00.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Simple та Present Continuous">
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
<td><strong>Present Simple</strong></td>
<td>Звички/факти/розклади</td>
<td>V1 / do/does + V1</td>
<td><span class="gw-en">She <strong>works</strong> from home.</span></td>
</tr>
<tr>
<td><strong>Present Continuous</strong></td>
<td>Дія «зараз» або тимчасова</td>
<td>am/is/are + V-ing</td>
<td><span class="gw-en">She <strong>is working</strong> now.</span></td>
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
<li><span class="tag-warn">✗</span> Пропускати <b>am/is/are</b>: <em>*I working</em>.</li>
<li><span class="tag-warn">✗</span> Вживати Continuous зі <em>state verbs</em> у прямому значенні: <em>*I am knowing</em>.</li>
<li><span class="tag-ok">✓</span> Формула завжди: <b>am/is/are + V-ing</b>. Для планів — додай конкретний час/домовленість.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
