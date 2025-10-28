<?php

namespace Database\Seeders\Pages\Pronouns;

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
            'title' => 'Pronouns — займенники',
            'subtitle_html' => <<<'HTML'
<p>Займенники замінюють іменники, щоб уникнути повторів та показати, <strong>хто діє і кому належить</strong>.</p>
HTML,
            'subtitle_text' => 'Займенники замінюють іменники, показують володіння та відносяться до вже згаданих предметів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'pronouns',
                'title' => 'Займенники',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Особові займенники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Особові займенники">
<thead>
<tr>
<th>Підмет</th>
<th>Додаток</th>
<th>Переклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>I</td>
<td>me</td>
<td>я / мене</td>
</tr>
<tr>
<td>you</td>
<td>you</td>
<td>ти, ви / тебе, вас</td>
</tr>
<tr>
<td>he</td>
<td>him</td>
<td>він / його</td>
</tr>
<tr>
<td>she</td>
<td>her</td>
<td>вона / її</td>
</tr>
<tr>
<td>it</td>
<td>it</td>
<td>воно / його</td>
</tr>
<tr>
<td>we</td>
<td>us</td>
<td>ми / нас</td>
</tr>
<tr>
<td>they</td>
<td>them</td>
<td>вони / їх</td>
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
<li><strong>Присвійні прикметники</strong> (my, your, his, her, its, our, their) стоять перед іменником: <span class="gw-en">my book</span>.</li>
<li><strong>Присвійні займенники</strong> (mine, yours, his, hers, ours, theirs) замінюють іменник: <span class="gw-en">The blue bag is mine.</span></li>
<li>В англійській немає апострофа у присвійних займенниках: <em>its</em> ≠ <em>it's</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Зворотні та взаємні',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Reflexive</strong> (myself, yourself, himself, herself, itself, ourselves, yourselves, themselves) використовуємо, коли дія повертається на підмет: <span class="gw-en">She taught herself Spanish.</span></li>
<li><strong>Each other / one another</strong> — взаємні дії: <span class="gw-en">They help each other.</span></li>
<li>Не плутай <em>by myself</em> (самостійно) та <em>for myself</em> (для себе).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Вказівні та означальні',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Demonstrative</strong>: this, that, these, those.</li>
<li><strong>Indefinite</strong>: some, any, someone, nobody, everybody.</li>
<li><strong>Relative</strong>: who, which, that — з’єднують частини речення.</li>
<li><strong>Interrogative</strong>: who, whose, what, which — ставлять запитання.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>Me and my friend...</em> → <strong>My friend and I...</strong> на початку речення.</li>
<li><span class="tag-warn">✗</span> Плутанина <strong>its</strong> та <strong>it’s</strong>: перше присвійне, друге = it is.</li>
<li><span class="tag-ok">✓</span> Завжди узгоджуй займенник зі своїм попередником за числом і родом, якщо це можливо.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
