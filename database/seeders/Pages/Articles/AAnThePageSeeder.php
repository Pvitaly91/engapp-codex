<?php

namespace Database\Seeders\Pages\Articles;

class AAnThePageSeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'a-an-the';
    }

    protected function page(): array
    {
        return [
            'title' => 'Articles A / An / The — Артиклі',
            'subtitle_html' => <<<'HTML'
<p>Артиклі допомагають показати, <strong>про що саме ми говоримо</strong>: про щось загальне чи конкретне.
В англійській є означений артикль <em>the</em> та неозначені <em>a/an</em>.</p>
HTML,
            'subtitle_text' => 'Артиклі показують, чи говоримо ми про конкретний предмет (the) або про щось уперше й загально (a/an).',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати a / an',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>a</strong> — перед приголосним звуком: <span class="gw-en">a book</span>, <span class="gw-en">a university</span> (/juː/).</li>
<li><strong>an</strong> — перед голосним звуком: <span class="gw-en">an apple</span>, <span class="gw-en">an hour</span> (/aʊ/).</li>
<li>Використовуємо, коли згадуємо предмет уперше або він один із багатьох.</li>
<li>Не ставимо з незлічуваними іменниками: <span class="gw-en">Ø information</span>, <span class="gw-en">Ø water</span>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I bought <strong>a</strong> new jacket.</div>
<div class="gw-ua">Я купив <strong>якусь</strong> нову куртку.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли потрібен the',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Коли предмет вже згадували або він зрозумілий зі ситуації: <span class="gw-en">the keys on the table</span>.</li>
<li>Унікальні об’єкти: <span class="gw-en">the sun</span>, <span class="gw-en">the Internet</span>.</li>
<li>Групи, сім’ї, музичні інструменти: <span class="gw-en">the Smiths</span>, <span class="gw-en">play the piano</span>.</li>
<li>Географія: моря, океани, пустелі, річки (<span class="gw-en">the Pacific</span>, <span class="gw-en">the Nile</span>).</li>
</ul>
<div class="gw-ex">
<div class="gw-en">Close <strong>the</strong> door, please.</div>
<div class="gw-ua">Закрий <strong>ті двері</strong>, будь ласка.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Нульовий артикль',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Перед множиною та незлічуваними поняттями загалом: <span class="gw-en">Books are expensive.</span></li>
<li>Перед мовами, видами спорту, прийомами їжі: <span class="gw-en">She speaks English.</span>, <span class="gw-en">We have dinner at seven.</span></li>
<li>З назвами вулиць, озер, більшості країн: <span class="gw-en">Oxford Street</span>, <span class="gw-en">Lake Victoria</span>, <span class="gw-en">Ukraine</span>.</li>
<li>Перед іменами власними: <span class="gw-en">Ø Doctor Brown</span>, <span class="gw-en">Ø Mount Everest</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння значень',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Використання артиклів">
<thead>
<tr>
<th>Форма</th>
<th>Значення</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>a/an</strong></td>
<td>Щось вперше, один із багатьох</td>
<td><span class="gw-en">We saw <strong>a</strong> comet.</span></td>
</tr>
<tr>
<td><strong>the</strong></td>
<td>Конкретний, вже відомий предмет</td>
<td><span class="gw-en">The comet was bright.</span></td>
</tr>
<tr>
<td><strong>Ø</strong></td>
<td>Поняття загалом</td>
<td><span class="gw-en">Space is fascinating.</span></td>
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
<li><span class="tag-warn">✗</span> <em>the Mount Everest</em> ❌ → <strong>Mount Everest</strong>.</li>
<li><span class="tag-warn">✗</span> <em>a information</em> ❌ → <strong>some information</strong>.</li>
<li><span class="tag-ok">✓</span> Використовуй <strong>a/an</strong> лише перед <em>звуком</em>, а не буквою.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
