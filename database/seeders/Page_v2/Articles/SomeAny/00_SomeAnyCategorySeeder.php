<?php

namespace Database\Seeders\Page_v2\Articles\SomeAny;

use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder;

class SomeAnyCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function someAnyContent(): array
    {
        return [
            'title' => 'Some / Any — Кількість у ствердженні та запереченні',
            'subtitle_html' => <<<'HTML'
<p><strong>Some</strong> та <strong>any</strong> допомагають говорити про невизначену кількість людей або речей.
Вони працюють з незлічуваними іменниками та множиною.</p>
HTML,
            'subtitle_text' => 'Some і any вживаються з незлічуваними іменниками та множиною, щоб говорити про невизначену кількість.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основне правило',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Some</strong> — зазвичай у ствердженнях: <span class="gw-en">We have some bread.</span></li>
<li><strong>Any</strong> — у запитаннях та запереченнях: <span class="gw-en">Do you have any milk?</span></li>
<li>Обидва вживаються з незлічуваними та множиною: <span class="gw-en">some apples</span>, <span class="gw-en">any furniture</span>.</li>
<li>Не ставимо з одниною злічуваних іменників: використовуємо <em>a/an</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Винятки для some',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У запитаннях із пропозицією/проханням: <span class="gw-en">Would you like some tea?</span></li>
<li>У запитаннях, коли очікуємо відповідь «так»: <span class="gw-en">Did you find some tickets?</span></li>
<li>У виразах із <strong>someone, somebody, somewhere</strong> — будь-які твердження.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">Could I have some water?</div>
<div class="gw-ua">Можна мені трохи води?</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Винятки для any',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У ствердженнях з відтінком «будь-який»: <span class="gw-en">You can take any seat.</span></li>
<li>Після <strong>hardly, without</strong> та подібних — передає заперечення: <span class="gw-en">She left without any luggage.</span></li>
<li>У стійких виразах: <span class="gw-en">any time</span>, <span class="gw-en">any day</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Пов’язані слова',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Похідні форми some / any">
<thead>
<tr>
<th>Форма</th>
<th>Значення</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>someone / somebody</strong></td>
<td>Хтось</td>
<td><span class="gw-en">Someone is waiting for you.</span></td>
</tr>
<tr>
<td><strong>anyone / anybody</strong></td>
<td>Будь-хто</td>
<td><span class="gw-en">Anybody can join.</span></td>
</tr>
<tr>
<td><strong>somewhere</strong></td>
<td>Десь</td>
<td><span class="gw-en">Let’s go somewhere warm.</span></td>
</tr>
<tr>
<td><strong>anywhere</strong></td>
<td>Будь-де, ніде</td>
<td><span class="gw-en">We can meet anywhere.</span></td>
</tr>
<tr>
<td><strong>something / anything</strong></td>
<td>Щось / будь-що</td>
<td><span class="gw-en">Is there anything to eat?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Для незлічуваних іменників використовуйте <strong>some/any</strong> та додаткові слова кількості: <em>a piece of advice</em>, <em>a bit of luck</em>.</p>
<p>Щоб зробити заперечення м’якшим, скористайтесь <strong>not any</strong> → <em>We don’t have any sugar.</em></p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }

    protected function slug(): string
    {
        return 'some-any';
    }

    protected function description(): array
    {
        return $this->someAnyContent();
    }

    protected function category(): array
    {
        return [
            'slug' => 'some-any',
            'title' => 'Some / Any',
            'language' => 'uk',
        ];
    }
}
