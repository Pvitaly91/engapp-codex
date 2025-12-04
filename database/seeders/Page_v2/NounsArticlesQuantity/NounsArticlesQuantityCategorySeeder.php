<?php

namespace Database\Seeders\Page_v2\NounsArticlesQuantity;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class NounsArticlesQuantityCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '2';
    }

    protected function description(): array
    {
        return [
            'title' => 'Іменники, артиклі та кількість',
            'subtitle_html' => <<<'HTML'
<p><strong>Іменники, артиклі та кількість</strong> — це важливий розділ англійської граматики.
Тут ти вивчиш, як правильно використовувати <em>артиклі a/an/the</em>, <em>злічувані та незлічувані іменники</em>,
а також слова для вираження кількості: <em>some, any, much, many, a lot of</em> та інші.</p>
HTML,
            'subtitle_text' => 'Іменники, артиклі та кількість в англійській мові: артиклі a/an/the, злічувані та незлічувані іменники, слова кількості.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Артиклі (Articles)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>A / An</strong> — неозначений артикль для однини: <span class="gw-en">a book</span>, <span class="gw-en">an apple</span>.</li>
<li><strong>The</strong> — означений артикль для конкретних речей: <span class="gw-en">the sun</span>, <span class="gw-en">the book I bought</span>.</li>
<li><strong>Без артикля (Zero article)</strong> — з абстрактними та загальними поняттями: <span class="gw-en">I love music.</span></li>
<li><strong>A</strong> перед приголосним звуком, <strong>an</strong> перед голосним: <span class="gw-en">a university</span>, <span class="gw-en">an hour</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Злічувані та незлічувані іменники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Злічувані (Countable)</strong> — можна порахувати: <span class="gw-en">one apple, two apples, three apples</span>.</li>
<li><strong>Незлічувані (Uncountable)</strong> — не можна порахувати: <span class="gw-en">water, information, advice</span>.</li>
<li>Незлічувані не мають множини: <span class="gw-en">I need some information.</span> (не informations).</li>
<li>Для вимірювання: <span class="gw-en">a glass of water</span>, <span class="gw-en">a piece of advice</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Слова кількості (Quantifiers)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Some / Any</strong> — невизначена кількість: <span class="gw-en">some water</span>, <span class="gw-en">any questions?</span></li>
<li><strong>Much / Many</strong> — багато: <span class="gw-en">much time</span> (незлічувані), <span class="gw-en">many friends</span> (злічувані).</li>
<li><strong>A lot of / Lots of</strong> — багато (універсальне): <span class="gw-en">a lot of money</span>, <span class="gw-en">lots of people</span>.</li>
<li><strong>A few / A little</strong> — трохи: <span class="gw-en">a few books</span>, <span class="gw-en">a little sugar</span>.</li>
<li><strong>Few / Little</strong> — мало (негативне): <span class="gw-en">few friends</span>, <span class="gw-en">little hope</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Іменники, артиклі та кількість">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>A / An / The</strong></td>
<td>A1–A2</td>
<td>Означений та неозначений артиклі</td>
</tr>
<tr>
<td><strong>Countable & Uncountable</strong></td>
<td>A1–A2</td>
<td>Злічувані та незлічувані іменники</td>
</tr>
<tr>
<td><strong>Some / Any</strong></td>
<td>A1–A2</td>
<td>Кількість у ствердженнях і запереченнях</td>
</tr>
<tr>
<td><strong>Much / Many / A lot of</strong></td>
<td>A2–B1</td>
<td>Вираження великої кількості</td>
</tr>
<tr>
<td><strong>A few / A little</strong></td>
<td>A2–B1</td>
<td>Вираження невеликої кількості</td>
</tr>
<tr>
<td><strong>Plural Nouns</strong></td>
<td>A1–A2</td>
<td>Множина іменників</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Some vs Any — основне правило',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Some</strong> — у ствердженнях: <span class="gw-en">I have some money.</span></li>
<li><strong>Any</strong> — у питаннях та запереченнях: <span class="gw-en">Do you have any money?</span></li>
<li><strong>Some</strong> у питаннях — коли пропонуємо: <span class="gw-en">Would you like some tea?</span></li>
<li><strong>Any</strong> у ствердженнях — коли «будь-який»: <span class="gw-en">Take any seat.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Почни з <strong>артиклів a/an/the</strong> — вони використовуються в кожному реченні.</p>
<p>Запамʼятай список <strong>незлічуваних іменників</strong>: water, information, advice, news, furniture.</p>
<p><strong>Much/many</strong> — для питань і заперечень, <strong>a lot of</strong> — універсальний варіант.</p>
<p><strong>A few/a little</strong> = «трохи» (позитивно), <strong>few/little</strong> = «мало» (негативно).</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => '2',
            'title' => 'Іменники, артиклі та кількість',
            'language' => 'uk',
        ];
    }
}
