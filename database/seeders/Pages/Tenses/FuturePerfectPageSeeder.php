<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class FuturePerfectPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'future-perfect';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Perfect — Майбутній доконаний час',
            'subtitle_html' => 'Використовуємо, щоб показати, що дія буде <strong>завершена до певного моменту в майбутньому</strong>.',
            'subtitle_text' => 'Використовуємо, щоб показати, що дія буде завершена до певного моменту в майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Завершення до дедлайну/події</strong>: «До п’ятниці вже зроблю».</li>
<li><strong>Прогноз про виконання</strong> до конкретного часу/моменту.</li>
<li><strong>У складних реченнях</strong> з <em>by (the time), before, until/till</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">By 6 pm, I <strong>will have finished</strong> the report.</div>
<div class="gw-ua">До 18:00 я <strong>вже закінчу</strong> звіт.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will have</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I <span style="color:#93c5fd">will have</span> <span style="color:#86efac">finished</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) have + V3
She <span style="color:#93c5fd">won’t have</span> <span style="color:#86efac">arrived</span> by noon.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">have</span> + V3?
<span style="color:#93c5fd">Will</span> they <span style="color:#93c5fd">have</span> <span style="color:#86efac">completed</span> it by then?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">by … (by Friday, by 2030)</span>
<span class="gw-chip">by the time …</span>
<span class="gw-chip">before …</span>
<span class="gw-chip">until/till …</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time you come, we <strong>will have prepared</strong> everything.</div>
<div class="gw-ua">До того часу, як ти прийдеш, ми <strong>вже підготуємо</strong> все.</div>
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
<p><strong>Майбутня точка → до неї дія буде завершена.</strong></p>
<p class="gw-ua">У підрядних часу після <em>when, after, before, by the time, until</em> зазвичай <b>Present Simple</b>, а не <em>will</em>:</p>
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en">I will have finished <u>before you arrive</u>.</div>
<div class="gw-ua">Я закінчу <u>перш ніж ти приїдеш</u> (не “will arrive”).</div>
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
<table class="gw-table" aria-label="Порівняння Future Simple та Future Perfect">
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
<td><strong>Future Simple</strong></td>
<td>Проста дія в майбутньому</td>
<td>will + V1</td>
<td><span class="gw-en">I will finish tomorrow.</span></td>
</tr>
<tr>
<td><strong>Future Perfect</strong></td>
<td>Дія завершиться <u>до</u> майбутньої точки</td>
<td>will have + V3</td>
<td><span class="gw-en">By tomorrow, I <strong>will have finished</strong>.</span></td>
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
<li><span class="tag-warn">✗</span> Ставити <em>will</em> після сполучників часу: <em>*when you will come</em>. Правильно: <em>when you come</em>.</li>
<li><span class="tag-warn">✗</span> Плутати з <em>Future Continuous</em> (той підкреслює процес у майбутній точці).</li>
<li><span class="tag-ok">✓</span> Думай про дедлайн у майбутньому: «Що <b>буде зроблено</b> до нього?»</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
