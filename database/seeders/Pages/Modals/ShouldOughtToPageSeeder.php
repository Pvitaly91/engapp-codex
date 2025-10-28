<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ShouldOughtToPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-should-ought-to';
    }

    protected function page(): array
    {
        return [
            'title' => 'Should / Ought to — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>Should</strong> та <strong>ought to</strong> виражають пораду, рекомендацію або моральний обов’язок. Обидва стоять
перед інфінітивом з <em>to</em> та допомагають говорити про бажані дії.</p>
HTML,
            'subtitle_text' => 'Should і ought to радять, висловлюють очікування чи моральне зобов’язання.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Should / Ought to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Використовуємо для порад: <span class="gw-en">You should see a doctor.</span></li>
<li><strong>Ought to</strong> формальніше й звучить серйозніше.</li>
<li>Для припущень з високою впевненістю: <span class="gw-en">She should be home now.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Структури',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Структури should та ought to">
<thead>
<tr>
<th>Тип речення</th>
<th>Структура</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Ствердження</td>
<td><span class="gw-en">subject + should/ought to + V1</span></td>
<td><span class="gw-en">We should leave now.</span></td>
</tr>
<tr>
<td>Заперечення</td>
<td><span class="gw-en">should/ought not to + V1</span></td>
<td><span class="gw-en">You shouldn’t stay up late.</span></td>
</tr>
<tr>
<td>Питання</td>
<td><span class="gw-en">Should + subject + V1?</span></td>
<td><span class="gw-en">Should we call her?</span></td>
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
<div class="gw-en">You ought to apologize.</div>
<div class="gw-ua">Тобі слід вибачитися. (моральний обов’язок)</div>
</div>
<div class="gw-ex">
<div class="gw-en">Should I bring anything?</div>
<div class="gw-ua">Чи варто щось принести? (порада/порадження)</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради та помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> Для минулого жалю використовуємо <em>should/ought to have + V3</em>: <span class="gw-en">I should have called.</span></li>
<li><span class="tag-warn">✗</span> Не пропускайте <em>to</em> після <strong>ought</strong>.</li>
<li><span class="tag-ok">✓</span> <strong>Should</strong> часто пропонує пораду, а <strong>ought to</strong> звучить більш категорично.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
