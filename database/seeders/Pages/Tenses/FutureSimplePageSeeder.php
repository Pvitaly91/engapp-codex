<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\GrammarPageSeeder;

class FutureSimplePageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'future-simple';
    }

    protected function page(): array
    {
        return [
            'title' => 'Future Simple — Майбутній простий час',
            'subtitle_html' => 'Використовуємо для <strong>спонтанних рішень, обіцянок, прогнозів</strong> і простих дій у майбутньому.',
            'subtitle_text' => 'Використовуємо для спонтанних рішень, обіцянок, прогнозів і простих дій у майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Спонтанне рішення</strong> у момент розмови: «Я зроблю це зараз».</li>
<li><strong>Обіцянки, пропозиції, відмови</strong>.</li>
<li><strong>Прогнози</strong>, які ґрунтуються на думці (I think, probably, maybe).</li>
</ul>
<div class="gw-ex">
<div class="gw-en">It’s hot. I <strong>will open</strong> the window.</div>
<div class="gw-ua">Жарко. Я <strong>відкрию</strong> вікно.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will</span> + <span style="color:#86efac">V1</span>
I <span style="color:#93c5fd">will</span> <span style="color:#86efac">help</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) + V1
She <span style="color:#93c5fd">won’t</span> <span style="color:#86efac">come</span> today.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + V1?
<span style="color:#93c5fd">Will</span> you <span style="color:#86efac">join</span> us?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">tomorrow</span>
<span class="gw-chip">soon</span>
<span class="gw-chip">next week</span>
<span class="gw-chip">in 2030</span>
<span class="gw-chip">I think / probably / maybe</span>
</div>
<div class="gw-ex">
<div class="gw-en">I think they <strong>will win</strong> the match.</div>
<div class="gw-ua">Я думаю, вони <strong>виграють</strong> матч.</div>
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
<p><strong>Will</strong> — універсальний для простих майбутніх дій, але:</p>
<ul class="gw-list">
<li>Для <b>запланованих дій</b> частіше вживають <em>be going to</em> або Present Continuous.</li>
<li>Для <b>обіцянок/спонтанних рішень</b> — саме Future Simple.</li>
</ul>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Simple та be going to">
<thead>
<tr>
<th>Форма</th>
<th>Використання</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Simple (will)</strong></td>
<td>Спонтанне рішення, обіцянка</td>
<td><span class="gw-en">I’ll call you tonight.</span></td>
</tr>
<tr>
<td><strong>Be going to</strong></td>
<td>План/наміри (заздалегідь)</td>
<td><span class="gw-en">I’m going to visit grandma tomorrow.</span></td>
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
<li><span class="tag-warn">✗</span> Використовувати <em>will</em> для фіксованих розкладів (краще Present Simple).</li>
<li><span class="tag-warn">✗</span> Зловживати <em>will</em> там, де доречніше <em>be going to</em>.</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <strong>will</strong> = рішення/обіцянка прямо зараз.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
