<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ToBeVerbPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'verb-to-be';
    }

    protected function page(): array
    {
        return [
            'title' => 'Verb to be — am, is, are',
            'subtitle_html' => <<<'HTML'
<p>Дієслово <strong>to be</strong> — основа англійської граматики. Воно служить зв’язкою, допоміжним дієсловом та позначає стан.
Форми відрізняються для особи та часу.</p>
HTML,
            'subtitle_text' => 'To be використовується як зв’язка та допоміжне дієслово, має форми am, is, are, was, were.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-structures',
                'title' => 'Базові конструкції',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми теперішнього',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми дієслова to be у теперішньому часі">
<thead>
<tr>
<th>Особа</th>
<th>Форма</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>I</td>
<td>am</td>
<td><span class="gw-en">I am happy.</span></td>
</tr>
<tr>
<td>he / she / it</td>
<td>is</td>
<td><span class="gw-en">She is a doctor.</span></td>
</tr>
<tr>
<td>you / we / they</td>
<td>are</td>
<td><span class="gw-en">They are ready.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Минуле та майбутнє',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>was</strong> для I/he/she/it: <span class="gw-en">I was tired.</span></li>
<li><strong>were</strong> для you/we/they: <span class="gw-en">We were at home.</span></li>
<li>Майбутнє: <span class="gw-en">will be</span> для всіх осіб.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'To be як зв’язка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Поєднує підмет з прикметниками або іменниками:</p>
<div class="gw-ex">
<div class="gw-en">The weather is cold.</div>
<div class="gw-ua">Погода холодна.</div>
</div>
<div class="gw-ex">
<div class="gw-en">My brother is a pilot.</div>
<div class="gw-ua">Мій брат — пілот.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Як допоміжне дієслово',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У Continuous: <span class="gw-en">She is studying.</span></li>
<li>У пасиві: <span class="gw-en">The house was built in 1990.</span></li>
<li>У питаннях та запереченнях: <span class="gw-en">Is he coming?</span>, <span class="gw-en">He isn’t coming.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Скорочення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>I’m</strong>, <strong>you’re</strong>, <strong>he’s</strong>, <strong>she’s</strong>, <strong>it’s</strong>.</li>
<li><strong>We’re</strong>, <strong>they’re</strong>.</li>
<li>У запереченні: <strong>I’m not</strong>, <strong>he isn’t</strong>, <strong>they aren’t</strong>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок у питаннях',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>To be стоїть перед підметом:</p>
<ul class="gw-list">
<li><span class="gw-en">Are you hungry?</span></li>
<li><span class="gw-en">Was she surprised?</span></li>
</ul>
<p>У коротких відповідях повторюємо форму: <span class="gw-en">Yes, I am.</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Пропуск <em>am/is/are</em>: <em>She happy</em> → <span class="tag-ok">✓</span> <em>She is happy</em>.</li>
<li><span class="tag-warn">✗</span> Подвійне дієслово: <em>Do you are?</em> → <span class="tag-ok">✓</span> <em>Are you?</em></li>
<li><span class="tag-warn">✗</span> Неправильна форма в минулому: <em>We was</em> → <span class="tag-ok">✓</span> <em>We were</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
