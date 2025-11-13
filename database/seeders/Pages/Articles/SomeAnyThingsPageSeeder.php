<?php

namespace Database\Seeders\Pages\Articles;

class SomeAnyThingsPageSeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'some-any-things';
    }

    protected function page(): array
    {
        return [
            'title' => 'Some / Any — Речі',
            'subtitle_html' => <<<'HTML'
<p><strong>Something / anything / nothing / everything</strong> описують невизначені предмети, явища або ідеї. Обираємо форму залежно від того, чи речення стверджувальне, заперечне або питання.</p>
HTML,
            'subtitle_text' => 'Щоб говорити про невизначені речі, використовуємо поєднання some-, any-, no-, every- зі словом thing.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'some-any',
                'title' => 'Some / Any',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Слова і значення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>something</strong> — щось; типово для стверджень: <span class="gw-en">I need something to eat.</span></li>
<li><strong>anything</strong> — щось / будь-що; в запереченні означає «ніщо»: <span class="gw-en">We don’t need anything.</span></li>
<li><strong>nothing</strong> — ніщо; уже містить заперечення: <span class="gw-en">Nothing happened.</span></li>
<li><strong>everything</strong> — все; підкреслює повноту: <span class="gw-en">Everything is ready.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли який префікс',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>some-</strong> → ствердження або пропозиції: <span class="gw-en">Would you like something hot?</span></li>
<li><strong>any-</strong> → запитання й заперечення: <span class="gw-en">Is there anything in the fridge?</span></li>
<li><strong>no-</strong> → ствердження з заперечним значенням: <span class="gw-en">There is nothing to add.</span></li>
<li><strong>every-</strong> → підкреслюємо повноту: <span class="gw-en">She packed everything.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові структури',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Заперечення</strong>: допоміжне дієслово + not + <em>anything</em> → <span class="gw-en">He doesn’t buy anything.</span></li>
<li><strong>Питання</strong>: інверсія + <em>anything</em> → <span class="gw-en">Does he buy anything every day?</span></li>
<li><strong>Формальне заперечення</strong>: <em>nothing</em> + дієслово без not → <span class="gw-en">Nothing was left.</span></li>
<li><strong>Відтінок «будь-що»</strong>: <em>anything</em> у ствердженнях → <span class="gw-en">Take anything you want.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">He buys something every day.</div>
<div class="gw-ua">Він щось купує щодня.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He doesn’t buy anything.</div>
<div class="gw-ua">Він нічого не купує.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He buys nothing.</div>
<div class="gw-ua">Він нічого не купує (формальніше).</div>
</div>
<div class="gw-ex">
<div class="gw-en">Does he buy anything every day?</div>
<div class="gw-ua">Він щось купує щодня?</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Пам’ятка',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Пам’ятка щодо thing-займенників">
<thead>
<tr>
<th>Форма</th>
<th>Ствердження</th>
<th>Заперечення</th>
<th>Питання</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>something</strong></td>
<td><span class="gw-en">There is something new.</span></td>
<td>–</td>
<td><span class="gw-en">Would you like something?</span></td>
</tr>
<tr>
<td><strong>anything</strong></td>
<td><span class="gw-en">Choose anything you like.</span></td>
<td><span class="gw-en">We didn’t see anything.</span></td>
<td><span class="gw-en">Is there anything left?</span></td>
</tr>
<tr>
<td><strong>nothing</strong></td>
<td><span class="gw-en">Nothing matters.</span></td>
<td>–</td>
<td>–</td>
</tr>
<tr>
<td><strong>everything</strong></td>
<td><span class="gw-en">Everything works.</span></td>
<td><span class="gw-en">Not everything works.</span></td>
<td><span class="gw-en">Is everything clear?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
            ],
        ];
    }
}
