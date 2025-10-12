<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\GrammarPageSeeder;

class SecondConditionalPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'second-conditional';
    }

    protected function page(): array
    {
        return [
            'title' => 'Second Conditional — уявні або малоймовірні ситуації',
            'subtitle_html' => <<<'HTML'
Використовуємо, щоб говорити про гіпотетичні події в теперішньому чи майбутньому. Часто
описуємо мрії, поради або наслідки, які малоймовірні.
HTML,
            'subtitle_text' => <<<'HTML'
Використовуємо, щоб говорити про гіпотетичні події в теперішньому чи майбутньому. Часто
      описуємо мрії, поради або наслідки, які малоймовірні.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Нереальні теперішні умови</strong>: If I were taller, I’d play basketball.</li>
<li><strong>Малоймовірне майбутнє</strong>: If we won the lottery, we’d travel the world.</li>
<li><strong>Поради</strong> у формі уявних ситуацій: If I were you, I’d call her.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I had more free time, I would learn Italian.</div>
<div class="gw-ua">Якби мав більше вільного часу, вивчив би італійську.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якби</span>
<span class="gw-chip">even if — навіть якби</span>
<span class="gw-chip">supposing — припустимо</span>
<span class="gw-chip">in case — на випадок</span>
<span class="gw-chip">unless — якби не</span>
</div>
<div class="gw-ex">
<div class="gw-en">Even if I had the money, I wouldn’t buy that car.</div>
<div class="gw-ua">Навіть якби в мене були гроші, я б не купив ту машину.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p>Підрядна частина — <strong>Past Simple</strong>, навіть якщо говоримо про теперішнє. У головній —
<strong>would/could/might</strong> + V1.</p>
<p class="gw-ua">If she <u>knew</u> the answer, she <u>would tell</u> us.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">would</span> + V1
If they <span style="color:#86efac">lived</span> closer, they <span style="color:#93c5fd">would visit</span> more often.</pre>
<div class="gw-code-badge">Модальні варіанти</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">could/might</span> + V1
If I <span style="color:#86efac">had</span> a bike, I <span style="color:#93c5fd">could ride</span> to work.</pre>
<div class="gw-code-badge">Inversion</div>
<pre class="gw-formula"><span style="color:#93c5fd">Were</span> + підмет + to + V1, would + V1
Were we <span style="color:#86efac">to find</span> a solution, we <span style="color:#93c5fd">would celebrate</span>.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені сценарії',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Мрії/бажання</strong>: If I were rich, I’d donate to charity.</li>
<li><strong>Поради</strong>: If I were in your shoes, I’d apologize.</li>
<li><strong>Гіпотетичні наслідки</strong>: If he moved abroad, we’d miss him.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If the weather were warmer, we would swim.</div>
<div class="gw-ua">Якби погода була теплішою, ми б поплавали.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння Second vs First',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Second vs First Conditional">
<thead>
<tr>
<th>Second</th>
<th>First</th>
</tr>
</thead>
<tbody>
<tr>
<td>Малоймовірно/уявно.</td>
<td>Реально/ймовірно.</td>
</tr>
<tr>
<td>If + Past, would + V1.</td>
<td>If + Present, will + V1.</td>
</tr>
<tr>
<td>Часто про поради: Were I you...</td>
<td>Часто про плани та обіцянки.</td>
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
<li><span class="tag-warn">✗</span> Вживання <em>would</em> в підрядній частині: <em>If I would know...</em></li>
<li><span class="tag-warn">✗</span> Використання <em>was</em> замість <em>were</em> у формальних висловах.</li>
<li><span class="tag-ok">✓</span> Використовуйте <strong>could/might</strong>, щоб показати різні ступені ймовірності.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
