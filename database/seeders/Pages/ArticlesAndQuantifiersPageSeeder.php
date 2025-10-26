<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ArticlesAndQuantifiersPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'articles-and-quantifiers';
    }

    protected function page(): array
    {
        return [
            'title' => 'Articles & Quantifiers — A/An, The, Some/Any',
            'subtitle_html' => <<<'HTML'
<p>В англійській статті <strong>a/an</strong>, <strong>the</strong> і кількісні слова <strong>some/any</strong> допомагають
показати, чи говоримо ми про щось вперше, наскільки це конкретно і чи підраховується предмет.</p>
HTML,
            'subtitle_text' => 'Статті та кількісні слова показують, чи є предмет конкретним та підраховуваним.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'articles-quantifiers',
                'title' => 'Артиклі та кількісні слова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'A / An — невизначені артиклі',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>a</strong> перед приголосним звуком: <span class="gw-en">a cat</span>, <span class="gw-en">a university</span> (звук /juː/).</li>
<li><strong>an</strong> перед голосним звуком: <span class="gw-en">an hour</span>, <span class="gw-en">an umbrella</span>.</li>
<li>Вживаємо, коли згадуємо предмет <strong>вперше</strong> або він не конкретний: <span class="gw-en">I saw a bird.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">She bought <strong>a</strong> new dress.</div>
<div class="gw-ua">Вона купила <strong>якусь</strong> нову сукню.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'The — визначений артикль',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Вживаємо, коли предмет уже відомий або згадувався: <span class="gw-en">the book we discussed</span>.</li>
<li>Для єдиних у своєму роді об’єктів: <span class="gw-en">the sun</span>, <span class="gw-en">the Earth</span>.</li>
<li>Для найвищих ступенів та порядкових числівників: <span class="gw-en">the best day</span>, <span class="gw-en">the first time</span>.</li>
</ul>
<div class="gw-hint">
<div class="gw-emoji">👀</div>
<div>
<p>Після другої згадки: <span class="gw-en">I bought a cake. The cake was delicious.</span></p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Zero article — без артикля',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Перед множиною та незлічуваними, коли говоримо взагалі: <span class="gw-en">Cats love milk.</span></li>
<li>Перед назвами прийомів їжі, днів, місяців, ігор: <span class="gw-en">We have dinner at seven.</span></li>
<li>Перед мовами та абстрактними поняттями: <span class="gw-en">Life is beautiful.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Some / Any',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>some</strong> — ствердження, пропозиції та прохання: <span class="gw-en">Can I have some water?</span></li>
<li><strong>any</strong> — заперечення та питання: <span class="gw-en">Do you have any questions?</span></li>
<li>Для невизначеної кількості злічуваних у множині або незлічуваних іменників.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Much / Many / A lot of',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>many</strong> — злічувані у множині: <span class="gw-en">many books</span>.</li>
<li><strong>much</strong> — незлічувані: <span class="gw-en">much time</span>; частіше у запитаннях/запереченнях.</li>
<li><strong>a lot of / lots of</strong> — універсальні у ствердженнях для обох типів іменників.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Countable vs Uncountable',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-box--accent">
<p><strong>Злічувані:</strong> можна порахувати штуками (<span class="gw-en">apples, chairs</span>).</p>
<p><strong>Незлічувані:</strong> речовини та абстракції (<span class="gw-en">water, advice, furniture</span>).</p>
<p>Незлічувані беремо в однині та з <em>much</em>/<em>little</em>. Для кількості додаємо <em>a bottle of water</em>, <em>a piece of advice</em>.</p>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Кількість та порції',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>a few / few</strong> — злічувані. <em>A few</em> = «кілька», <em>few</em> = «майже немає».</li>
<li><strong>a little / little</strong> — незлічувані. <em>A little</em> = «трохи», <em>little</em> = «майже немає».</li>
<li><strong>too much / too many</strong> — надмір; <strong>enough</strong> — достатньо.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Таблиця-порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Вибір артиклів та кількісних слів">
<thead>
<tr>
<th>Ситуація</th>
<th>Приклад</th>
<th>Коментар</th>
</tr>
</thead>
<tbody>
<tr>
<td>Предмет згадується вперше</td>
<td><span class="gw-en">I bought a laptop.</span></td>
<td>Невизначений артикль.</td>
</tr>
<tr>
<td>Предмет уже відомий</td>
<td><span class="gw-en">The laptop is fast.</span></td>
<td>Визначений артикль.</td>
</tr>
<tr>
<td>Говоримо загально про множину</td>
<td><span class="gw-en">Computers are expensive.</span></td>
<td>Без артикля.</td>
</tr>
<tr>
<td>Невизначена кількість незлічуваного</td>
<td><span class="gw-en">some water</span></td>
<td>Ствердження, пропозиції.</td>
</tr>
<tr>
<td>Питання/заперечення про кількість</td>
<td><span class="gw-en">any money</span></td>
<td>Вживаємо <em>any</em>.</td>
</tr>
</tbody>
</table>
HTML,
                ],
            ],
        ];
    }
}
