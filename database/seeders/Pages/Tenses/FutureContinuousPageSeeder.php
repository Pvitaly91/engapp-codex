<?php

namespace Database\Seeders\Pages\Tenses;

class FutureContinuousPageSeeder extends TensePageSeeder
{
    protected function slug(): string
    {
        return 'future-continuous';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Continuous — Майбутній тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>буде у процесі</strong> в певний момент у майбутньому.',
            'subtitle_text' => 'Показує, що дія буде у процесі в певний момент у майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Щоб описати дію, яка буде <strong>в процесі</strong> у конкретний майбутній момент.</li>
<li>Для ввічливих запитань про плани.</li>
<li>Для регулярних дій у майбутньому (нейтральний тон).</li>
</ul>
<div class="gw-ex">
<div class="gw-en">This time tomorrow, I <strong>will be travelling</strong>.</div>
<div class="gw-ua">Завтра в цей час я <strong>буду подорожувати</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will be</span> + <span style="color:#86efac">V-ing</span>
I <span style="color:#93c5fd">will be</span> <span style="color:#86efac">working</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) be + V-ing
She <span style="color:#93c5fd">won’t be</span> <span style="color:#86efac">sleeping</span> at 10 pm.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">be</span> + V-ing?
<span style="color:#93c5fd">Will</span> you <span style="color:#93c5fd">be</span> <span style="color:#86efac">using</span> the car tonight?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">at this time tomorrow</span>
<span class="gw-chip">at 8 pm next Friday</span>
<span class="gw-chip">soon</span>
<span class="gw-chip">all day tomorrow</span>
</div>
<div class="gw-ex">
<div class="gw-en">At 9 pm, we <strong>will be watching</strong> a movie.</div>
<div class="gw-ua">О 21:00 ми <strong>будемо дивитися</strong> фільм.</div>
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
<p><strong>Future Continuous</strong> = дія «у процесі» у конкретний момент у майбутньому.</p>
<p class="gw-ua">Використовуємо для опису ситуації «я буду робити щось у певний час».</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Simple та Future Continuous">
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
<td>Факт у майбутньому</td>
<td>will + V1</td>
<td><span class="gw-en">I will work tomorrow.</span></td>
</tr>
<tr>
<td><strong>Future Continuous</strong></td>
<td>Процес у конкретний момент у майбутньому</td>
<td>will be + V-ing</td>
<td><span class="gw-en">I will be working at 10 am tomorrow.</span></td>
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
<li><span class="tag-warn">✗</span> Використовувати Future Continuous для простих фактів (там треба Future Simple).</li>
<li><span class="tag-warn">✗</span> Забувати <b>be</b> після will: <em>*I will working</em>.</li>
<li><span class="tag-ok">✓</span> Завжди: <b>will be + V-ing</b>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
