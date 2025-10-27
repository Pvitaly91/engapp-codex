<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class NeedNeedToPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-need-need-to';
    }

    protected function page(): array
    {
        return [
            'title' => 'Need / Need to — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>Need</strong> може поводитися як модальне дієслово в запереченні та питанні, а <strong>need to</strong> — як звичайне
дієслово, що описує необхідність. Обидві форми показують, чи є потреба щось робити.</p>
HTML,
            'subtitle_text' => 'Need і need to говорять про необхідність, але граматично поводяться по-різному.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Need / Need to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Need</strong> як модальне вживається у запереченні та питанні: <span class="gw-en">Need you go?</span></li>
<li><strong>Need to</strong> — звичайне дієслово, що виражає необхідність: <span class="gw-en">I need to finish this today.</span></li>
<li><strong>Needn’t</strong> = немає потреби, на відміну від <em>mustn’t</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Форми та варіанти',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми need та need to">
<thead>
<tr>
<th>Тип</th>
<th>Форма</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Модальне need</td>
<td><span class="gw-en">Need + subject + V1?</span></td>
<td><span class="gw-en">Need I worry?</span></td>
</tr>
<tr>
<td>Заперечення</td>
<td><span class="gw-en">Subject + needn’t + V1</span></td>
<td><span class="gw-en">You needn’t hurry.</span></td>
</tr>
<tr>
<td>Звичайне дієслово</td>
<td><span class="gw-en">Subject + need(s) to + V1</span></td>
<td><span class="gw-en">She needs to rest.</span></td>
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
<div class="gw-en">You needn’t bring any food.</div>
<div class="gw-ua">Тобі не потрібно приносити їжу. (відсутність потреби)</div>
</div>
<div class="gw-ex">
<div class="gw-en">Do we need to book in advance?</div>
<div class="gw-ua">Чи потрібно бронювати заздалегідь?</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Не плутайте <strong>needn’t</strong> («немає потреби») з <strong>don’t need to</strong> — обидві форми можливі, але <strong>needn’t</strong> звучить формальніше.</li>
<li><span class="tag-ok">✓</span> У британській англійській модальне <strong>need</strong> частіше використовується, ніж у американській.</li>
<li><span class="tag-ok">✓</span> Для минулої відсутності потреби вживайте <em>didn’t need to</em> або <em>needn’t have + V3</em>.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
