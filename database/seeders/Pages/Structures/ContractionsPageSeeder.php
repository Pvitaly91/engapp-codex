<?php

namespace Database\Seeders\Pages\Structures;

class ContractionsPageSeeder extends StructurePageSeeder
{
    protected function slug(): string
    {
        return 'contractions-short-forms';
    }

    protected function page(): array
    {
        return [
            'title' => 'Contractions — скорочені форми в англійській',
            'subtitle_html' => <<<'HTML'
<p>Contractions — це короткі форми, утворені зі злиття двох слів. Вони роблять мовлення природним і розмовним: <span class="gw-en">do not → don’t</span>, <span class="gw-en">we are → we’re</span>. Найчастіше скорочуємо допоміжні дієслова та <em>not</em>.</p>
HTML,
            'subtitle_text' => 'Contractions поєднують два слова в коротку форму (don’t, we’re) й характерні для розмовної англійської.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Як утворюються скорочення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Випадає одна або кілька літер, на місці ставимо апостроф: <span class="gw-en">is not → isn’t</span>.</li>
<li>Частіше скорочуємо допоміжні дієслова (<em>be, have, will</em>) та <em>not</em>.</li>
<li>У письмі contractions доречні в листах, чатах, неформальних текстах.</li>
<li>У формальному листі краще писати повні форми: <span class="gw-en">I am not</span> замість <span class="gw-en">I’m not</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові поєднання',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Поширені contractions">
<thead>
<tr>
<th>Повна форма</th>
<th>Contraction</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>do not</td>
<td>don’t</td>
<td><span class="gw-en">I don’t like coffee.</span></td>
</tr>
<tr>
<td>we are</td>
<td>we’re</td>
<td><span class="gw-en">We’re ready.</span></td>
</tr>
<tr>
<td>she will</td>
<td>she’ll</td>
<td><span class="gw-en">She’ll call later.</span></td>
</tr>
<tr>
<td>they have</td>
<td>they’ve</td>
<td><span class="gw-en">They’ve finished.</span></td>
</tr>
<tr>
<td>cannot</td>
<td>can’t</td>
<td><span class="gw-en">He can’t drive.</span></td>
</tr>
<tr>
<td>it is / it has</td>
<td>it’s</td>
<td><span class="gw-en">It’s been a long day.</span></td>
</tr>
<tr>
<td>will not</td>
<td>won’t</td>
<td><span class="gw-en">We won’t be late.</span></td>
</tr>
<tr>
<td>must not</td>
<td>mustn’t</td>
<td><span class="gw-en">You mustn’t smoke here.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Подвійні значення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="gw-en">He’s</span> = <span class="gw-en">he is</span> або <span class="gw-en">he has</span>. Дивіться на наступне слово: <span class="gw-en">He’s running</span> (is) / <span class="gw-en">He’s done</span> (has).</li>
<li><span class="gw-en">They’d</span> = <span class="gw-en">they had</span> або <span class="gw-en">they would</span>. Контекст підкаже час.</li>
<li><span class="gw-en">I’d</span> = <span class="gw-en">I had</span> / <span class="gw-en">I would</span>; <span class="gw-en">I’ve</span> = <span class="gw-en">I have</span>.</li>
<li>У запереченнях <span class="gw-en">is not</span>, <span class="gw-en">are not</span>, <span class="gw-en">have not</span> скорочуємо <em>not</em>: <span class="gw-en">isn’t</span>, <span class="gw-en">aren’t</span>, <span class="gw-en">haven’t</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради та вимова',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p>У вимові скорочення з <em>not</em> мають звук /n/ або /nt/: <span class="gw-en">can’t</span> /kɑːnt/.</p>
<p>Скорочення з <em>will</em> звучать як /l/: <span class="gw-en">she’ll</span> /ʃiːl/.</p>
<p><span class="gw-en">Let’s</span> завжди має апостроф — це скорочення від <span class="gw-en">let us</span>.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Уникаємо типових помилок',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Плутати <span class="gw-en">its</span> (присвійний займенник) та <span class="gw-en">it’s</span> (скорочення <em>it is / it has</em>).</li>
<li><span class="tag-warn">✗</span> Писати апостроф у присвійних займенниках: <span class="tag-warn">✗</span> <em>your’s</em>, <span class="tag-ok">✓</span> <span class="gw-en">yours</span>.</li>
<li><span class="tag-ok">✓</span> У навчальних тестах уважно добирайте пару: одна contraction — одна повна форма.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
