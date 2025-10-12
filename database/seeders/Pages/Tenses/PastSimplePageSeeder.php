<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\GrammarPageSeeder;

class PastSimplePageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'past-simple';
    }

    protected function page(): array
    {
        return [
            'title' => 'Past Simple — Минулий простий час',
            'subtitle_html' => 'Використовуємо, щоб розповісти про <strong>завершені дії чи факти в минулому</strong> з конкретним часом або контекстом.',
            'subtitle_text' => 'Використовуємо, щоб розповісти про завершені дії чи факти в минулому з конкретним часом або контекстом.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Події, які сталися і закінчились у минулому (yesterday, last week, in 2010).</li>
<li>Послідовність дій у минулому.</li>
<li>Факти чи звички, які більше не актуальні.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">We <strong>moved</strong> to Kyiv in 2019.</div>
<div class="gw-ua">Ми <strong>переїхали</strong> до Києва у 2019.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#86efac">V2</span> (правильні дієслова = +ed; неправильні = 2 форма)
I <span style="color:#86efac">watched</span> / She <span style="color:#86efac">went</span></pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + did not (didn’t) + V1
He <span style="color:#93c5fd">didn’t</span> <span style="color:#86efac">call</span> yesterday.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Did</span> + [підмет] + V1?
<span style="color:#93c5fd">Did</span> you <span style="color:#86efac">enjoy</span> the film?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">yesterday</span>
<span class="gw-chip">last week/month/year</span>
<span class="gw-chip">in 2010</span>
<span class="gw-chip">two days ago</span>
<span class="gw-chip">then</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>visited</strong> us last weekend.</div>
<div class="gw-ua">Вона <strong>відвідала</strong> нас минулих вихідних.</div>
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
<p>Якщо <strong>є чіткий час у минулому</strong> (yesterday, in 2010) — це <b>Past Simple</b>, а не Present Perfect.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Present Perfect">
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
<td>Завершена дія з конкретним минулим часом</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">I <strong>visited</strong> Paris in 2020.</span></td>
</tr>
<tr>
<td><strong>Present Perfect</strong></td>
<td>Досвід/результат «до тепер», без вказаного часу</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have visited</strong> Paris.</span></td>
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
<li><span class="tag-warn">✗</span> Використовувати Past Simple без маркерів часу (тоді це плутають із Present Perfect).</li>
<li><span class="tag-warn">✗</span> Забувати форму <b>V2</b> для неправильних дієслів.</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <b>V2</b> — минула форма; для заперечень і питань — <b>did + V1</b>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
