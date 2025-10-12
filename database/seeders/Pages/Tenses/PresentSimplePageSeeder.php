<?php

namespace Database\Seeders\Pages\Tenses;

use Database\Seeders\Pages\GrammarPageSeeder;

class PresentSimplePageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'present-simple';
    }

    protected function page(): array
    {
        return [
            'title' => 'Present Simple — Теперішній простий час',
            'subtitle_html' => 'Використовуємо для <strong>фактів, звичок, розкладів</strong> та регулярних дій.',
            'subtitle_text' => 'Використовуємо для фактів, звичок, розкладів та регулярних дій.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Факти</strong>: закони природи, загальні істини.</li>
<li><strong>Звички</strong>: те, що робимо регулярно.</li>
<li><strong>Розклади</strong>: поїзди, уроки, кіносеанси.</li>
<li><strong>Стан</strong> (like, know, want) — не в Continuous.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">The sun <strong>rises</strong> in the east.</div>
<div class="gw-ua">Сонце <strong>сходить</strong> на сході.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + V1 (+s/es для he/she/it)
I <span style="color:#86efac">work</span>.
She <span style="color:#86efac">works</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + do/does not + V1
He <span style="color:#93c5fd">doesn’t</span> <span style="color:#86efac">like</span> coffee.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Do/Does</span> + [підмет] + V1?
<span style="color:#93c5fd">Do</span> you <span style="color:#86efac">play</span> chess?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">always</span>
<span class="gw-chip">usually</span>
<span class="gw-chip">often</span>
<span class="gw-chip">sometimes</span>
<span class="gw-chip">rarely</span>
<span class="gw-chip">never</span>
<span class="gw-chip">every day / week</span>
<span class="gw-chip">on Mondays</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>goes</strong> to the gym every Friday.</div>
<div class="gw-ua">Вона <strong>ходить</strong> у спортзал щоп’ятниці.</div>
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
<p>Для <strong>he/she/it</strong> додаємо <b>-s/-es</b>: works, watches.</p>
<p>В усіх інших випадках — дієслово у базовій формі.</p>
</div>
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
<td>Факти, звички, розклади</td>
<td>V1 / do/does + V1</td>
<td><span class="gw-en">She <strong>reads</strong> every evening.</span></td>
</tr>
<tr>
<td><strong>Present Continuous</strong></td>
<td>Дія у процесі зараз</td>
<td>am/is/are + V-ing</td>
<td><span class="gw-en">She <strong>is reading</strong> now.</span></td>
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
<li><span class="tag-warn">✗</span> Забувати додати <b>-s/-es</b> для he/she/it.</li>
<li><span class="tag-warn">✗</span> Використовувати Present Simple для дії «зараз» (там треба Present Continuous).</li>
<li><span class="tag-ok">✓</span> Використовуй Present Simple для <strong>звичок, фактів, розкладів</strong>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
