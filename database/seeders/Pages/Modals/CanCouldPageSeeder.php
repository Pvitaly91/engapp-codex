<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class CanCouldPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-can-could';
    }

    protected function page(): array
    {
        return [
            'title' => 'Can / Could — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>Can</strong> та <strong>could</strong> показують уміння, дозвіл та ймовірність. Вони стоять перед основною формою
дієслова без <em>to</em> і допомагають говорити про теперішні та гіпотетичні ситуації.</p>
HTML,
            'subtitle_text' => 'Can та could виражають здібність, дозвіл і припущення та вживаються перед базовою формою дієслова.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основні властивості модальних дієслів',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Не додаємо <em>s</em> у третій особі однини: <span class="gw-en">She can swim.</span></li>
<li>Заперечення утворюємо за допомогою <em>not</em>: <span class="gw-en">cannot</span>, <span class="gw-en">couldn’t</span>.</li>
<li>Питання створюємо інверсією: <span class="gw-en">Can you help?</span></li>
<li>Після модального йде інфінітив без <em>to</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Can / Could',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Can</strong> — теперішня можливість або дозвіл. <span class="gw-en">I can drive.</span></li>
<li><strong>Could</strong> — минула здібність, ввічливі прохання чи гіпотетичні варіанти. <span class="gw-en">Could you open the window?</span></li>
<li>Умовні речення: <span class="gw-en">If we had time, we could travel more.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">Can I leave early?</div>
<div class="gw-ua">Можна я піду раніше?</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові вживання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Вживання can та could">
<thead>
<tr>
<th>Ситуація</th>
<th>Модальне</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Здібність зараз</td>
<td><strong>can</strong></td>
<td><span class="gw-en">She can speak Italian.</span></td>
</tr>
<tr>
<td>Минулі здібності</td>
<td><strong>could</strong></td>
<td><span class="gw-en">I could run fast when I was a kid.</span></td>
</tr>
<tr>
<td>Ввічливе прохання</td>
<td><strong>could</strong></td>
<td><span class="gw-en">Could you pass the salt?</span></td>
</tr>
<tr>
<td>Імовірність</td>
<td><strong>could</strong> / <strong>can</strong></td>
<td><span class="gw-en">It could rain later.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради щодо вживання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p><strong>Could</strong> звучить м’якше, ніж <strong>can</strong>, тому підходить для ввічливих прохань.</p>
<p>Для припущень у минулому використовуйте <em>could have + V3</em>: <span class="gw-en">She could have won.</span></p>
<p>Заперечення <em>cannot</em> часто скорочуємо до <em>can’t</em>.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Додавати <em>to</em>: <em>can to go</em>.</li>
<li><span class="tag-warn">✗</span> Вживати <em>could</em> для теперішньої здібності (краще <strong>can</strong>).</li>
<li><span class="tag-ok">✓</span> У минулому для конкретних дій краще <span class="gw-en">was/were able to</span> замість <strong>could</strong>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
