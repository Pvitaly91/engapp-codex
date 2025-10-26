<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class PronounsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'pronouns';
    }

    protected function page(): array
    {
        return [
            'title' => 'Pronouns — особові, присвійні та зворотні займенники',
            'subtitle_html' => <<<'HTML'
<p>Займенники замінюють іменники, щоб уникати повторів. У граматиці розрізняємо <strong>особові</strong>,
<strong>присвійні</strong>, <strong>зворотні</strong> та <strong>вказівні</strong> форми.</p>
HTML,
            'subtitle_text' => 'Займенники допомагають уникати повторень і показують, хто виконує дію або кому щось належить.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pronouns-basics',
                'title' => 'Займенники',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Особові займенники',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Особові займенники">
<thead>
<tr>
<th>Підмет</th>
<th>Доповнення</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>I</td>
<td>me</td>
<td><span class="gw-en">I see you. You see me.</span></td>
</tr>
<tr>
<td>you</td>
<td>you</td>
<td><span class="gw-en">She called you.</span></td>
</tr>
<tr>
<td>he</td>
<td>him</td>
<td><span class="gw-en">He thanked him.</span></td>
</tr>
<tr>
<td>she</td>
<td>her</td>
<td><span class="gw-en">I met her.</span></td>
</tr>
<tr>
<td>it</td>
<td>it</td>
<td><span class="gw-en">I like it.</span></td>
</tr>
<tr>
<td>we</td>
<td>us</td>
<td><span class="gw-en">They invited us.</span></td>
</tr>
<tr>
<td>they</td>
<td>them</td>
<td><span class="gw-en">We saw them.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Присвійні форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Присвійні прикметники</strong> стоять перед іменником: <span class="gw-en">my book</span>, <span class="gw-en">their house</span>.</li>
<li><strong>Присвійні займенники</strong> стоять самостійно: <span class="gw-en">Mine is red.</span>, <span class="gw-en">Yours is blue.</span></li>
<li>Важливо не додавати апостроф у <em>its</em>: <span class="gw-en">The cat licked its paw.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Зворотні (myself, yourself)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Використовуємо, коли дія повертається на діяча: <span class="gw-en">She taught herself.</span></li>
<li>Або для наголосу: <span class="gw-en">I made it myself.</span></li>
<li>Не плутати з <em>each other</em> (одне одного).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Неозначені займенники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>someone / somebody</strong> — хтось; <strong>something</strong> — щось.</li>
<li><strong>anyone / anything</strong> — будь-хто/будь-що, використовуємо у питаннях та запереченнях.</li>
<li><strong>no one / nothing</strong> — ніхто/нічого; утворюють заперечення без <em>not</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Хто та що',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-box--accent">
<p><strong>Who</strong> — про людей, <strong>which</strong> — про предмети, <strong>that</strong> — універсальне.</p>
<p>Відносні займенники вводять підрядні речення: <span class="gw-en">The girl who sings</span>.</p>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧭</div>
<div>
<p>Коли сумніваєшся між <em>me</em> та <em>I</em>, прибери іншу людину: <span class="gw-en">Tom and I</span> (I went), але <span class="gw-en">for Tom and me</span> (for me).</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>Me and my friend</em> на початку речення → <span class="tag-ok">✓</span> <em>My friend and I</em>.</li>
<li><span class="tag-warn">✗</span> <em>It’s tail</em> → <span class="tag-ok">✓</span> <em>Its tail</em>.</li>
<li><span class="tag-warn">✗</span> <em>Each other self</em> → <span class="tag-ok">✓</span> <em>each other</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
