<?php

namespace Database\Seeders\Pages\Demonstratives;

class ThisThatTheseThosePageSeeder extends DemonstrativePageSeeder
{
    protected function slug(): string
    {
        return 'this-that-these-those';
    }

    protected function page(): array
    {
        return [
            'title' => 'This / That / These / Those — Вказівні займенники',
            'subtitle_html' => <<<'HTML'
<p>Ці слова допомагають уточнити <strong>відстань і число</strong> предметів, про які йдеться.
Вони замінюють іменник або стоять перед ним.</p>
HTML,
            'subtitle_text' => 'This, that, these, those показують, наскільки предмет близько, і чи він у однині чи множині.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основні значення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Відстань і число">
<thead>
<tr>
<th>Форма</th>
<th>Число</th>
<th>Відстань</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>this</strong></td>
<td>Однина</td>
<td>Близько</td>
<td><span class="gw-en">This book is great.</span></td>
</tr>
<tr>
<td><strong>that</strong></td>
<td>Однина</td>
<td>Далеко</td>
<td><span class="gw-en">That mountain looks high.</span></td>
</tr>
<tr>
<td><strong>these</strong></td>
<td>Множина</td>
<td>Близько</td>
<td><span class="gw-en">These shoes fit well.</span></td>
</tr>
<tr>
<td><strong>those</strong></td>
<td>Множина</td>
<td>Далеко</td>
<td><span class="gw-en">Those clouds mean rain.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'This / That як займенники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Можуть стояти самостійно: <span class="gw-en">This is my seat.</span></li>
<li>Вживаємо для представлення людей: <span class="gw-en">This is Anna.</span></li>
<li>Для реакції на ідею співрозмовника: <span class="gw-en">That sounds amazing!</span></li>
<li><strong>That</strong> часто вживається у виразах: <em>That’s it!</em>, <em>That’s right.</em></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Граматичні дрібниці',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Перед іменником завжди стоїть без артикля: <span class="gw-en">these students</span>.</li>
<li>З дієсловом <strong>to be</strong> узгоджуємо число: <span class="gw-en">These are my notes.</span></li>
<li>Комбінація <em>this/that + of + possessive</em>: <span class="gw-en">those of you who...</span></li>
<li>У письмі <strong>this</strong> часто вводить нову ідею, <strong>that</strong> — повертається до попередньої.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Часті вирази',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="gw-en">This way, please.</span></li>
<li><span class="gw-en">That’s why...</span></li>
<li><span class="gw-en">At this time of year...</span></li>
<li><span class="gw-en">In those days...</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порада',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">👆</div>
<div>
<p>Під час говоріння супроводжуйте слова <strong>this/these</strong> жестом на близькі предмети, а <strong>that/those</strong> — на віддалені. Це допомагає співрозмовнику краще зрозуміти контекст.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
