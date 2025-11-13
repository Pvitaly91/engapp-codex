<?php

namespace Database\Seeders\Pages\Articles;

class SomeAnyPlacesPageSeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'some-any-places';
    }

    protected function page(): array
    {
        return [
            'title' => 'Some / Any — Місця',
            'subtitle_html' => <<<'HTML'
<p><strong>Somewhere / anywhere / nowhere / everywhere</strong> описують невизначені місця. Так само як і з людьми чи речами, префікс показує тип речення та ступінь визначеності.</p>
HTML,
            'subtitle_text' => 'Вибір між somewhere, anywhere, nowhere та everywhere залежить від того, чи твердження позитивне, заперечне чи питання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'some-any',
                'title' => 'Some / Any',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основні значення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>somewhere</strong> — десь; використовуємо в ствердженнях: <span class="gw-en">She lives somewhere in London.</span></li>
<li><strong>anywhere</strong> — будь-де / ніде (в запереченнях): <span class="gw-en">She can’t go anywhere.</span></li>
<li><strong>nowhere</strong> — ніде; містить заперечення: <span class="gw-en">There is nowhere to go.</span></li>
<li><strong>everywhere</strong> — всюди: <span class="gw-en">We looked everywhere.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Правила вживання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>somewhere</strong> у пропозиціях / проханнях: <span class="gw-en">Shall we go somewhere quiet?</span></li>
<li><strong>anywhere</strong> у питаннях: <span class="gw-en">Is there anywhere to park?</span></li>
<li><strong>nowhere</strong> не поєднується з додатковим <em>not</em>.</li>
<li><strong>everywhere</strong> вважається одниною: <span class="gw-en">Everywhere is busy in summer.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Живі приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">We went nowhere yesterday.</div>
<div class="gw-ua">Ми нікуди не ходили вчора.</div>
</div>
<div class="gw-ex">
<div class="gw-en">You can sit anywhere you like.</div>
<div class="gw-ua">Можеш сидіти де завгодно.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She travels everywhere.</div>
<div class="gw-ua">Вона подорожує всюди.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Карта рішень',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Вживання some / any з місцями">
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
<td><strong>somewhere</strong></td>
<td><span class="gw-en">We stayed somewhere near the sea.</span></td>
<td>–</td>
<td><span class="gw-en">Would you like to go somewhere else?</span></td>
</tr>
<tr>
<td><strong>anywhere</strong></td>
<td><span class="gw-en">You can go anywhere.</span></td>
<td><span class="gw-en">I can’t go anywhere.</span></td>
<td><span class="gw-en">Is there anywhere to sit?</span></td>
</tr>
<tr>
<td><strong>nowhere</strong></td>
<td><span class="gw-en">There is nowhere open.</span></td>
<td>–</td>
<td>–</td>
</tr>
<tr>
<td><strong>everywhere</strong></td>
<td><span class="gw-en">Everywhere is full of tourists.</span></td>
<td><span class="gw-en">Not everywhere is quiet.</span></td>
<td><span class="gw-en">Is everywhere closed?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порада',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧭</div>
<div>
<p>Якщо в українській хочеться сказати «ніде», в англійській обери <strong>nowhere</strong> або <strong>not anywhere</strong>, але не використовуй їх разом.</p>
<p>Для перекладу «де завгодно» використовуй <strong>anywhere</strong> у ствердженнях.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
