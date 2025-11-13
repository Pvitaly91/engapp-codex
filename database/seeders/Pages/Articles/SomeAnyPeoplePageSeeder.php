<?php

namespace Database\Seeders\Pages\Articles;

class SomeAnyPeoplePageSeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'some-any-people';
    }

    protected function page(): array
    {
        return [
            'title' => 'Some / Any — Люди',
            'subtitle_html' => <<<'HTML'
<p>Слова <strong>somebody / anybody / nobody / everybody</strong> допомагають говорити про людей, коли ми не називаємо їх конкретно. Вони показують, чи йдеться про «когось», «будь-кого», «нікого» або «всіх».</p>
HTML,
            'subtitle_text' => 'Невизначені займенники для людей показують, чи говоримо про когось конкретного, будь-кого, нікого або всіх.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'some-any',
                'title' => 'Some / Any',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Ключові слова',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>somebody / someone</strong> — хтось; використовуємо у ствердженнях або чемних запитаннях.</li>
<li><strong>anybody / anyone</strong> — хтось / будь-хто; у запереченнях означає «ніхто».</li>
<li><strong>nobody / no one</strong> — ніхто; саме слово несе заперечення, тому додаткове <em>don’t</em> не потрібне.</li>
<li><strong>everybody / everyone</strong> — усі; граматично вважається одниною → <span class="gw-en">Everybody is here.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Як обрати форму',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Somebody</strong> → ствердження: <span class="gw-en">Somebody came yesterday.</span></li>
<li><strong>Anybody</strong> → запитання та заперечення: <span class="gw-en">Did anybody call?</span>, <span class="gw-en">We didn’t see anybody.</span></li>
<li><strong>Nobody</strong> → заперечення без допоміжного дієслова: <span class="gw-en">Nobody answered.</span></li>
<li><strong>Everybody</strong> → підкреслюємо, що дія стосується всіх: <span class="gw-en">Everybody knows the rule.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Поради й приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">Could I ask you something? — Sure, somebody will help.</div>
<div class="gw-ua">Можна запитати? — Звичайно, хтось допоможе.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I didn’t hear anybody at the door.</div>
<div class="gw-ua">Я нікого не почув біля дверей.</div>
</div>
<div class="gw-ex">
<div class="gw-en">Everybody is ready, so nobody will be late.</div>
<div class="gw-ua">Усі готові, тому ніхто не запізниться.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Міні-табличка',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Вживання some / any з людьми">
<thead>
<tr>
<th>Тип</th>
<th>Ствердження</th>
<th>Заперечення</th>
<th>Питання</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>some-</strong></td>
<td><span class="gw-en">Somebody is calling.</span></td>
<td>–</td>
<td><span class="gw-en">Would somebody like tea?</span></td>
</tr>
<tr>
<td><strong>any-</strong></td>
<td><span class="gw-en">You can invite anybody.</span></td>
<td><span class="gw-en">We didn’t meet anybody.</span></td>
<td><span class="gw-en">Did anybody come?</span></td>
</tr>
<tr>
<td><strong>no-</strong></td>
<td><span class="gw-en">Nobody called.</span></td>
<td>–</td>
<td>–</td>
</tr>
<tr>
<td><strong>every-</strong></td>
<td><span class="gw-en">Everybody knows.</span></td>
<td><span class="gw-en">Not everybody agrees.</span></td>
<td><span class="gw-en">Does everybody agree?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Уникай подвійного заперечення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">⚠️</div>
<div>
<p>У англійській одне заперечення на речення. Кажемо <strong>Nobody came</strong>, а не <em>Anybody didn’t come</em>.</p>
<p>Якщо потрібне емоційне питання, використай інверсію: <span class="gw-en">Didn’t anybody come?</span></p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
