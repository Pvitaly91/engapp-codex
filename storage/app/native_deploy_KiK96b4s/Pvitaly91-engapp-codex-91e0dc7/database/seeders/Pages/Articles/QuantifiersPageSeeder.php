<?php

namespace Database\Seeders\Pages\Articles;

class QuantifiersPageSeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'quantifiers';
    }

    protected function page(): array
    {
        return [
            'title' => 'Quantifiers — Much, Many, A Lot, Few, Little',
            'subtitle_html' => <<<'HTML'
<p>Квантифікатори допомагають точніше сказати, <strong>скільки</strong> чогось ми маємо.
Вони залежать від того, чи іменник злічуваний.</p>
HTML,
            'subtitle_text' => 'Much, many, a lot of, few, little показують кількість і залежать від того, чи іменник злічуваний.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Much / Many / A lot of',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Much</strong> — незлічувані: <span class="gw-en">much water</span>, <span class="gw-en">much homework</span>. Частіше у запереченнях/питаннях.</li>
<li><strong>Many</strong> — злічувані множини: <span class="gw-en">many friends</span>.</li>
<li><strong>A lot of / lots of</strong> — універсальні, у розмовному стилі підходять і для злічуваних, і для незлічуваних.</li>
<li>У ствердженнях частіше використовуємо <strong>a lot of</strong>, ніж <em>much</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Few / A few',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Few</strong> — мало (з негативним відтінком): <span class="gw-en">Few people understood.</span></li>
<li><strong>A few</strong> — кілька (достатньо): <span class="gw-en">We have a few minutes.</span></li>
<li>Використовуємо лише зі злічуваними іменниками множини.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Little / A little',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Little</strong> — мало (недостатньо): <span class="gw-en">There is little time left.</span></li>
<li><strong>A little</strong> — трохи (достатньо): <span class="gw-en">I need a little sugar.</span></li>
<li>Використовуємо з незлічуваними іменниками.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">We have a little milk, enough for coffee.</div>
<div class="gw-ua">У нас трохи молока — вистачить на каву.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Зведена таблиця',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Використання квантифікаторів">
<thead>
<tr>
<th>Слово</th>
<th>Тип іменника</th>
<th>Відтінок</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>much</strong></td>
<td>Незлічувані</td>
<td>Велика кількість</td>
<td><span class="gw-en">How much money do you need?</span></td>
</tr>
<tr>
<td><strong>many</strong></td>
<td>Злічувані множини</td>
<td>Велика кількість</td>
<td><span class="gw-en">There aren’t many buses.</span></td>
</tr>
<tr>
<td><strong>a lot of</strong></td>
<td>Усі типи</td>
<td>Розмовне «багато»</td>
<td><span class="gw-en">We have a lot of work.</span></td>
</tr>
<tr>
<td><strong>few</strong></td>
<td>Злічувані множини</td>
<td>Замало</td>
<td><span class="gw-en">Few tourists visit in winter.</span></td>
</tr>
<tr>
<td><strong>a few</strong></td>
<td>Злічувані множини</td>
<td>Кілька, достатньо</td>
<td><span class="gw-en">I made a few notes.</span></td>
</tr>
<tr>
<td><strong>little</strong></td>
<td>Незлічувані</td>
<td>Замало</td>
<td><span class="gw-en">There’s little light here.</span></td>
</tr>
<tr>
<td><strong>a little</strong></td>
<td>Незлічувані</td>
<td>Трохи, достатньо</td>
<td><span class="gw-en">Add a little salt.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Корисні вирази',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>plenty of</strong> — більш ніж достатньо.</li>
<li><strong>no</strong> + іменник = повна відсутність: <span class="gw-en">There is no sugar left.</span></li>
<li><strong>either / neither</strong> — для двох елементів (<em>either option is fine</em>).</li>
<li><strong>each / every</strong> — говоримо про всі елементи по одному.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
