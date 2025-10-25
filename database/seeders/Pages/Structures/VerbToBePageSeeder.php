<?php

namespace Database\Seeders\Pages\Structures;

class VerbToBePageSeeder extends StructurePageSeeder
{
    protected function slug(): string
    {
        return 'verb-to-be';
    }

    protected function page(): array
    {
        return [
            'title' => 'Verb to be — дієслово «бути»',
            'subtitle_html' => <<<'HTML'
<p>Дієслово <strong>to be</strong> має власні форми у кожному часі і є основою багатьох конструкцій: присудків, пасиву, тривалих часів.</p>
HTML,
            'subtitle_text' => 'Дієслово to be має особливі форми (am/is/are, was/were, been/being) і часто використовується як допоміжне.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми в теперішньому часі',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми to be">
<thead>
<tr>
<th>Особа</th>
<th>Форма</th>
<th>Скорочення</th>
</tr>
</thead>
<tbody>
<tr>
<td>I</td>
<td>am</td>
<td>I’m</td>
</tr>
<tr>
<td>he / she / it</td>
<td>is</td>
<td>he’s / she’s / it’s</td>
</tr>
<tr>
<td>you / we / they</td>
<td>are</td>
<td>you’re / we’re / they’re</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Минулий та майбутній час',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Past Simple</strong>: was (I/he/she/it), were (you/we/they).</li>
<li><strong>Future Simple</strong>: will be.</li>
<li><strong>Present Perfect</strong>: have/has been.</li>
<li><strong>Continuous</strong>: am/is/are + being; was/were + being.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Основні вживання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Повідомити стан чи опис: <span class="gw-en">She is happy.</span></li>
<li>Місцезнаходження: <span class="gw-en">The keys are on the table.</span></li>
<li>Вік та ціна: <span class="gw-en">I am 25.</span>, <span class="gw-en">It is $10.</span></li>
<li>Професія та національність: <span class="gw-en">They are engineers.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Як допоміжне дієслово',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Continuous</strong>: <span class="gw-en">She is studying.</span></li>
<li><strong>Passive</strong>: <span class="gw-en">The cake was baked yesterday.</span></li>
<li><strong>There is / there are</strong> та інші структури.</li>
<li>В інверсії та коротких відповідях: <span class="gw-en">Are you ready? — Yes, I am.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Пропускати <em>am/is/are</em> у ствердженнях.</li>
<li><span class="tag-warn">✗</span> Вживати <em>am</em> з <strong>you</strong> або <em>is</em> з <strong>I</strong>.</li>
<li><span class="tag-ok">✓</span> Запам’ятай скорочення: <strong>it’s</strong> = it is, але <strong>its</strong> — присвійний.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
