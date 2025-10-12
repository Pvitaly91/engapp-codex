<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class PresentPerfectPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'present-perfect';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Perfect — Теперішній доконаний час',
            'subtitle_html' => 'Показує <strong>результат або досвід</strong> до теперішнього моменту. Час важливий зараз; конкретну минулу дату зазвичай <strong>не вказуємо</strong>.',
            'subtitle_text' => 'Показує результат або досвід до теперішнього моменту. Час важливий зараз; конкретну минулу дату зазвичай не вказуємо.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Досвід у житті</strong>: ever, never.</li>
<li><strong>Нещодавно завершено</strong>, ефект помітний зараз: just, already, yet.</li>
<li><strong>Тривалість до тепер</strong>: for, since.</li>
<li>Звіт/результат «на зараз»: «Я вже зробив звіт».</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>have finished</strong> the report.</div>
<div class="gw-ua">Я <strong>вже закінчив</strong> звіт (результат є зараз).</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">have/has</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I/You/We/They <span style="color:#93c5fd">have</span> <span style="color:#86efac">seen</span>.
He/She/It <span style="color:#93c5fd">has</span> <span style="color:#86efac">seen</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + have/has not + V3
She <span style="color:#93c5fd">hasn’t</span> <span style="color:#86efac">visited</span> us since 2022.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Have/Has</span> + [підмет] + V3?
<span style="color:#93c5fd">Have</span> you <span style="color:#86efac">ever been</span> to Rome?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">already</span><span class="gw-chip">yet</span><span class="gw-chip">just</span>
<span class="gw-chip">ever</span><span class="gw-chip">never</span>
<span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">so far</span><span class="gw-chip">recently/lately</span>
</div>
<div class="gw-ex">
<div class="gw-en">We <strong>have lived</strong> here <u>since</u> 2020.</div>
<div class="gw-ua">Ми <strong>живемо</strong> тут <u>з</u> 2020 року (і досі).</div>
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
<p><strong>Present Perfect ≠ конкретний минулий час.</strong> Якщо є «вчора», «у 2019» тощо — це вже <b>Past Simple</b>.</p>
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en"><span class="tag-warn">✗</span> I have finished it yesterday.</div>
<div class="gw-ua">Правильно: <b>I finished it yesterday.</b> (Past Simple)</div>
</div>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Perfect та Past Simple">
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
<td><strong>Present Perfect</strong></td>
<td>Результат/досвід «на зараз», без конкретної минулої дати</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have lost</strong> my keys.</span> <span class="gw-ua">Я загубив ключі (і досі без них).</span></td>
</tr>
<tr>
<td><strong>Past Simple</strong></td>
<td>Завершена дія в минулому з часом/контекстом</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">I <strong>lost</strong> my keys <u>yesterday</u>.</span> <span class="gw-ua">Учора загубив (факт у минулому).</span></td>
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
<li><span class="tag-warn">✗</span> Додавати конкретну минулу дату: <em>*I have visited in 2019</em>.</li>
<li><span class="tag-warn">✗</span> Плутати <em>for</em> і <em>since</em>:
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en"><b>for</b> + період: for 3 years; <b>since</b> + точка: since 2020.</div>
</div>
</li>
<li><span class="tag-ok">✓</span> Для 3-ї особи однини — <b>has</b>; іншим — <b>have</b>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
