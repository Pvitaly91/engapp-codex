<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class MayMightPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-may-might';
    }

    protected function page(): array
    {
        return [
            'title' => 'May / Might — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>May</strong> та <strong>might</strong> допомагають говорити про дозвіл і ймовірність. Вони вживаються перед базовою
формою дієслова та показують різний рівень впевненості.</p>
HTML,
            'subtitle_text' => 'May і might виражають формальний дозвіл та припущення з різним рівнем впевненості.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'May / Might',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>May</strong> — формальний дозвіл або ймовірність: <span class="gw-en">You may enter.</span></li>
<li><strong>Might</strong> — слабша ймовірність, припущення: <span class="gw-en">She might be late.</span></li>
<li>У запереченні та питанні зазвичай використовуємо <em>may not</em>, <em>might not</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Рівень впевненості',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Рівень впевненості may та might">
<thead>
<tr>
<th>Модальне</th>
<th>Ймовірність</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>may</strong></td>
<td>≈ 50-60%</td>
<td><span class="gw-en">It may snow tonight.</span></td>
</tr>
<tr>
<td><strong>might</strong></td>
<td>≈ 30-40%</td>
<td><span class="gw-en">He might join us later.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">May I leave the room?</div>
<div class="gw-ua">Чи можу я вийти з кімнати? (формальний дозвіл)</div>
</div>
<div class="gw-ex">
<div class="gw-en">They might visit us this weekend.</div>
<div class="gw-ua">Можливо, вони навідаються на цих вихідних.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради та зауваги',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У розмовній мові дозвіл частіше виражають <strong>can</strong> / <strong>could</strong>, але <strong>may</strong> лишається більш офіційним.</li>
<li>Для припущень про минуле використовуємо <em>may/might have + V3</em>: <span class="gw-en">She might have forgotten.</span></li>
<li>У негативному припущенні — <em>may not</em>, але <em>might not</em> звучить дещо м’якше.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
