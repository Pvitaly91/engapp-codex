<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class DemonstrativesPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'demonstratives';
    }

    protected function page(): array
    {
        return [
            'title' => 'Demonstratives — This, That, These, Those',
            'subtitle_html' => <<<'HTML'
<p>Вказівні займенники <strong>this/that</strong> та <strong>these/those</strong> допомагають показати, наскільки близький
предмет і чи він в однині чи множині.</p>
HTML,
            'subtitle_text' => 'Вказівні займенники позначають відстань і кількість предметів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pronouns-basics',
                'title' => 'Займенники',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Вживання this/that/these/those">
<thead>
<tr>
<th>Форма</th>
<th>Однина / множина</th>
<th>Відстань</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>this</strong></td>
<td>Однина</td>
<td>Близько до мовця</td>
<td><span class="gw-en">This book is great.</span></td>
</tr>
<tr>
<td><strong>that</strong></td>
<td>Однина</td>
<td>Далеко від мовця</td>
<td><span class="gw-en">That mountain looks high.</span></td>
</tr>
<tr>
<td><strong>these</strong></td>
<td>Множина</td>
<td>Близько до мовця</td>
<td><span class="gw-en">These cookies are fresh.</span></td>
</tr>
<tr>
<td><strong>those</strong></td>
<td>Множина</td>
<td>Далеко від мовця</td>
<td><span class="gw-en">Those houses are old.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Уживання з часом',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>this/these</strong> — сьогодення або майбутнє: <span class="gw-en">this morning</span>, <span class="gw-en">these days</span>.</li>
<li><strong>that/those</strong> — минуле або віддалене: <span class="gw-en">that day</span>, <span class="gw-en">those years</span>.</li>
<li>Часто для підкреслення близькості в часі/емоціях: <span class="gw-en">this idea</span> (схвально), <span class="gw-en">that idea</span> (віддалено).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'This/That + to be',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Коли представляємо людей/предмети:</p>
<div class="gw-ex">
<div class="gw-en">This is my friend Kate.</div>
<div class="gw-ua">Це моя подруга Кейт (поруч).</div>
</div>
<div class="gw-ex">
<div class="gw-en">That is our teacher.</div>
<div class="gw-ua">Це наш вчитель (далі).</div>
</div>
<p>У розмовній мові часто скорочуємо: <em>That’s</em>, <em>These are</em>.</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'These vs Those у порівнянні',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-box--accent">
<p><strong>These</strong> використовуємо для предметів, які ми тримаємо або можемо торкнутися.</p>
<p><strong>Those</strong> — для предметів на відстані або поза контекстом розмови.</p>
<p>В інтернет-магазинах: <span class="gw-en">These shoes</span> (на сторінці товару), <span class="gw-en">those shoes</span> (інша категорія).</p>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>this dogs</em> → <span class="tag-ok">✓</span> <em>these dogs</em>.</li>
<li><span class="tag-warn">✗</span> <em>these kind</em> → <span class="tag-ok">✓</span> <em>this kind / these kinds</em>.</li>
<li><span class="tag-warn">✗</span> Використовувати <em>that</em> для близького, коли жестом показуємо поруч — краще <em>this</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Міні-діалог',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-dialog">
<div class="gw-dialog__line"><span class="gw-dialog__speaker">A:</span> <span class="gw-en">Are these your headphones?</span></div>
<div class="gw-dialog__line"><span class="gw-dialog__speaker">B:</span> <span class="gw-en">No, those are mine over there.</span></div>
<div class="gw-dialog__note">Перший говорить про предмет поруч, другий уточнює про віддалений.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порада від Grammarway',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">📏</div>
<div>
<p>У вправах часто додають візуальні підказки (малюнки з близьким/далеким предметом). Завжди дивись на позицію людини і предмета.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
