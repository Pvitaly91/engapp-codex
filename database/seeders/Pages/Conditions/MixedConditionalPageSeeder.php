<?php

namespace Database\Seeders\Pages\Conditions;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class MixedConditionalPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'mixed-conditional';
    }

    protected function page(): array
    {
        return [
            'title' => 'Mixed Conditionals — змішані часові комбінації',
            'subtitle_html' => <<<'HTML'
Поєднують частини різних типів, коли час умови та результату відрізняються. Найчастіше — минуле ↔
теперішнє/майбутнє.
HTML,
            'subtitle_text' => <<<'HTML'
Поєднують частини різних типів, коли час умови та результату відрізняються. Найчастіше — минуле ↔
      теперішнє/майбутнє.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Минуле → теперішнє</strong>: If I had studied medicine, I would be a doctor now.</li>
<li><strong>Теперішнє → минуле</strong>: If I weren’t afraid of flying, I would have travelled more.</li>
<li><strong>Логічні зв’язки</strong> між різними моментами часу.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If they had left on time, they wouldn’t be stuck in traffic now.</div>
<div class="gw-ua">Якби вони виїхали вчасно, зараз не стояли б у заторі.</div>
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
<span class="gw-chip">had — у формі інверсії</span>
<span class="gw-chip">were — у формальних порадах</span>
<span class="gw-chip">but for — якби не</span>
<span class="gw-chip">assuming — припускаючи</span>
</div>
<div class="gw-ex">
<div class="gw-en">Had you taken the pill, you would feel better now.</div>
<div class="gw-ua">Якби ти прийняв пігулку, зараз почувався б краще.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Структури',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Минуле → теперішній результат</div>
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">would</span> + V1
If she <span style="color:#86efac">had saved</span> money, she <span style="color:#93c5fd">would own</span> a car now.</pre>
<div class="gw-code-badge">Теперішнє → минулий результат</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">would have</span> + V3
If he <span style="color:#86efac">were</span> more careful, he <span style="color:#93c5fd">wouldn’t have made</span> that mistake.</pre>
<div class="gw-code-badge">Інверсія</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + підмет + V3, would + V1 / would have + V3
Had we <span style="color:#86efac">taken</span> a taxi, we <span style="color:#93c5fd">would be</span> on time now.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені комбінації',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Type 3 → Type 2</strong>: Минуле впливає на теперішнє.</li>
<li><strong>Type 2 → Type 3</strong>: Теперішній стан вплинув би на минуле.</li>
<li><strong>Інші комбінації</strong>: можливі, якщо логічні.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I knew French, I would have understood the guide.</div>
<div class="gw-ua">Якби я знав французьку, то зрозумів би гіда.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння структур',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Mixed Conditional Patterns">
<thead>
<tr>
<th>Комбінація</th>
<th>Умова</th>
<th>Результат</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Type 3 → Type 2</td>
<td>Past Perfect</td>
<td>would + V1</td>
<td class="gw-en">If you <strong>had listened</strong>, you <strong>would know</strong> the answer.</td>
</tr>
<tr>
<td>Type 2 → Type 3</td>
<td>Past Simple</td>
<td>would have + V3</td>
<td class="gw-en">If she <strong>were</strong> more patient, she <strong>would have finished</strong>.</td>
</tr>
<tr>
<td>Modal variation</td>
<td>Past Perfect / Past Simple</td>
<td>could/might + V1 або have + V3</td>
<td class="gw-en">If he <strong>had practised</strong>, he <strong>might be</strong> famous now.</td>
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
<li><span class="tag-warn">✗</span> Використання однакових часів в обох частинах — тоді це вже не mixed.</li>
<li><span class="tag-warn">✗</span> Плутанина з порядком: Past Perfect має бути в умові, якщо говоримо про минуле.</li>
<li><span class="tag-ok">✓</span> Перевір, що час умови і наслідку логічно відповідають сенсу.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
