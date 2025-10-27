<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class IrregularVerbsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'irregular-verbs';
    }

    protected function page(): array
    {
        return [
            'title' => 'Irregular verbs — неправильні дієслова',
            'subtitle_html' => <<<'HTML'
<p>Неправильні дієслова мають особливі форми <strong>Past Simple</strong> та <strong>Past Participle</strong>, які треба вивчити.
Більшість з них використовуються щодня, тож корисно групувати їх за подібними змінами.</p>
HTML,
            'subtitle_text' => 'Неправильні дієслова змінюють форму в минулому та третьій формі за особливими правилами.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'verbs',
                'title' => 'Дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Що запам’ятати',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Є три основні форми: <strong>V1</strong> (інфінітив), <strong>V2</strong> (Past Simple), <strong>V3</strong> (Past Participle).</li>
<li>Форми V2 і V3 можуть бути однаковими або різними: <span class="gw-en">go — went — gone</span>.</li>
<li>Вживаємо V3 у Perfect та пасиві: <span class="gw-en">have gone</span>, <span class="gw-en">was written</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Поширені групи',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Групи неправильних дієслів">
<thead>
<tr>
<th>Шаблон</th>
<th>Приклади</th>
</tr>
</thead>
<tbody>
<tr>
<td>Однакові всі форми (V1=V2=V3)</td>
<td><span class="gw-en">put — put — put</span>, <span class="gw-en">cut — cut — cut</span></td>
</tr>
<tr>
<td>V2=V3, голосна змінюється</td>
<td><span class="gw-en">keep — kept — kept</span>, <span class="gw-en">sleep — slept — slept</span></td>
</tr>
<tr>
<td>V3 з -n</td>
<td><span class="gw-en">speak — spoke — spoken</span>, <span class="gw-en">write — wrote — written</span></td>
</tr>
<tr>
<td>Зміна голосних i-a-u</td>
<td><span class="gw-en">drink — drank — drunk</span>, <span class="gw-en">sing — sang — sung</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Стратегії запам’ятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Групуй дієслова за звуком або орфографією.</li>
<li>Створюй власні приклади: <span class="gw-en">He drank tea yesterday.</span></li>
<li>Користуйся картками або додатками з повторенням.</li>
<li>Повторюй у реченнях у різних часах.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Top-10 must know',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ol class="gw-list">
<li><span class="gw-en">be — was/were — been</span></li>
<li><span class="gw-en">have — had — had</span></li>
<li><span class="gw-en">go — went — gone</span></li>
<li><span class="gw-en">do — did — done</span></li>
<li><span class="gw-en">make — made — made</span></li>
<li><span class="gw-en">get — got — got/gotten</span></li>
<li><span class="gw-en">take — took — taken</span></li>
<li><span class="gw-en">come — came — come</span></li>
<li><span class="gw-en">see — saw — seen</span></li>
<li><span class="gw-en">say — said — said</span></li>
</ol>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порада Grammarway',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🎯</div>
<div>
<p>Вчіть 5–7 дієслів на тиждень у коротких серіях. Чередуйте активні вправи: переклад, заповнення пропусків, диктанти.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Нерегулярні у Perfect',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>У Present Perfect дієслово завжди в третій формі:</p>
<div class="gw-ex">
<div class="gw-en">She has written three articles.</div>
<div class="gw-ua">Вона написала три статті.</div>
</div>
<div class="gw-ex">
<div class="gw-en">We have gone home.</div>
<div class="gw-ua">Ми пішли додому.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Часті помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використовувати закінчення <em>-ed</em>: <em>goed</em> → <span class="tag-ok">✓</span> <em>went</em>.</li>
<li><span class="tag-warn">✗</span> Плутати V2 і V3: <em>He has went</em> → <span class="tag-ok">✓</span> <em>He has gone</em>.</li>
<li><span class="tag-warn">✗</span> Забувати про множину <em>was</em>/<em>were</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
