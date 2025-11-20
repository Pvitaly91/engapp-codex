<?php

namespace Database\Seeders\Pages\Adjectives;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class DegreesOfComparisonPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'degrees-of-comparison';
    }

    protected function page(): array
    {
        return [
            'title' => 'Degrees of Comparison — ступені порівняння прикметників і прислівників',
            'subtitle_html' => <<<'HTML'
<p>Ступені порівняння показують, <strong>як одні якості співвідносяться з іншими</strong>. Є три ступені: звичайний, вищий (comparative) і найвищий (superlative).</p>
HTML,
            'subtitle_text' => 'Comparative показує різницю між двома предметами, superlative — виділяє крайній ступінь у групі.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'adjectives',
                'title' => 'Прикметники',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли і як використовуємо',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Base form</strong>: опис без порівняння — <span class="gw-en">This road is narrow.</span></li>
<li><strong>Comparative</strong> + <em>than</em> — порівнюємо дві речі: <span class="gw-en">narrower than</span>, <span class="gw-en">more expensive than</span>.</li>
<li><strong>Superlative</strong> + <em>the</em> — виділяємо одну з групи: <span class="gw-en">the narrowest street</span>, <span class="gw-en">the most expensive hotel</span>.</li>
<li>Можна посилити/послабити: <span class="gw-en">much/way/a lot + comparative</span>; <span class="gw-en">by far/easily + the superlative</span>; <span class="gw-en">slightly/a bit + comparative</span>.</li>
<li>Для рівності: <span class="gw-en">as + adjective/adverb + as</span> (та negative <span class="gw-en">not as/so ... as</span>).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Короткі слова (1–2 склади)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Загальне правило</strong>: додаємо <em>-er</em> / <em>-est</em>: <span class="gw-en">fast → faster → the fastest</span>.</li>
<li><strong>-y → -ier/-iest</strong>: <span class="gw-en">happy → happier → the happiest</span>.</li>
<li><strong>Приголосний + голосний + приголосний</strong> → подвоюємо фінальний приголосний: <span class="gw-en">big → bigger → the biggest</span>.</li>
<li><strong>-e в кінці</strong> → додаємо лише <em>-r/-st</em>: <span class="gw-en">large → larger → the largest</span>.</li>
<li><strong>-ow, -er, -le</strong> часто приймають <em>-er/-est</em>: <span class="gw-en">narrow → narrower</span>, <span class="gw-en">clever → cleverer</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Довгі слова й прислівники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Прикметники з трьома і більше складами та більшість прислівників утворюють ступені з <strong>more / most</strong>: <span class="gw-en">interesting → more interesting → the most interesting</span>, <span class="gw-en">quickly → more quickly</span>.</li>
<li>Якщо закінчуються на -ly, практично завжди використовується <em>more/most</em>.</li>
<li>Для протилежного значення — <strong>less / least</strong>: <span class="gw-en">the least dangerous</span>.</li>
<li>Деякі двоскладові прикметники допускають обидві форми (особливо закінчення на -y, -ow, -er): <span class="gw-en">shallow → shallower/more shallow</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Неправильні форми',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Irregular comparatives and superlatives">
<thead>
<tr><th>Base form</th><th>Comparative</th><th>Superlative</th></tr>
</thead>
<tbody>
<tr><td>good / well</td><td>better</td><td>the best</td></tr>
<tr><td>bad / badly</td><td>worse</td><td>the worst</td></tr>
<tr><td>far</td><td>farther/further</td><td>the farthest/the furthest</td></tr>
<tr><td>little (amount)</td><td>less</td><td>the least</td></tr>
<tr><td>many / much</td><td>more</td><td>the most</td></tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Часті конструкції та застереження',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>the + superlative + in/of</strong> для груп: <span class="gw-en">the tallest in the class</span>, <span class="gw-en">the most useful of all</span>.</li>
<li><strong>One of the + plural noun</strong> з найвищим ступенем: <span class="gw-en">one of the best players</span>.</li>
<li>Не пропускайте <em>the</em> перед порядковими числівниками + comparative: <span class="gw-en">the second largest city</span>.</li>
<li>Уникайте подвійних маркерів (<em>more better</em>, <em>the most fastest</em>) — обираємо лише один спосіб утворення.</li>
<li>Порівняння з <strong>than</strong> вимагає узгодження: <span class="gw-en">My car is more reliable than yours</span>.</li>
</ul>
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p>Пам’ятайте про контекст: <em>further</em> частіше про «додатковий» або «далі в часі», а <em>farther</em> — про фізичну відстань.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
