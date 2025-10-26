<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ThereIsThereArePageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'there-is-there-are';
    }

    protected function page(): array
    {
        return [
            'title' => 'There is / There are — конструкція існування',
            'subtitle_html' => <<<'HTML'
<p><strong>There is/are</strong> використовуємо, щоб сказати, що щось існує або знаходиться десь. Після конструкції йде підмет
(те, що існує), а не місце.</p>
HTML,
            'subtitle_text' => 'Конструкція there is/are показує існування предметів у певному місці чи часі.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-structures',
                'title' => 'Базові конструкції',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Форми there is / there are">
<thead>
<tr>
<th>Час</th>
<th>Однина</th>
<th>Множина</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Present</td>
<td>There is</td>
<td>There are</td>
<td><span class="gw-en">There is a park nearby.</span></td>
</tr>
<tr>
<td>Past</td>
<td>There was</td>
<td>There were</td>
<td><span class="gw-en">There were many people.</span></td>
</tr>
<tr>
<td>Future</td>
<td colspan="2">There will be</td>
<td><span class="gw-en">There will be a concert.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Заперечення та питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Заперечення: <span class="gw-en">There isn’t</span>, <span class="gw-en">There aren’t</span>, <span class="gw-en">There wasn’t</span>.</li>
<li>Питання: <span class="gw-en">Is there</span> a bank near here? <span class="gw-en">Are there</span> any seats?</li>
<li>У коротких відповідях: <span class="gw-en">Yes, there is.</span> / <span class="gw-en">No, there aren’t.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Some/Any з there is/are',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У ствердженнях з множиною та незлічуваними: <span class="gw-en">There is some milk.</span></li>
<li>У питаннях/запереченнях: <span class="gw-en">Are there any buses?</span></li>
<li>Для нульової кількості: <span class="gw-en">There isn’t any time left.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Порівняння з have',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-box--accent">
<p><strong>There is/are</strong> — говоримо про наявність у місці: <span class="gw-en">There is a fridge in the kitchen.</span></p>
<p><strong>Have</strong> — говоримо про власність: <span class="gw-en">We have a fridge.</span></p>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок слів',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Після <em>there</em> йде дієслово <em>to be</em>, далі підмет, потім обставини місця:</p>
<div class="gw-ex">
<div class="gw-en">There are two cafes on this street.</div>
<div class="gw-ua">На цій вулиці є два кафе.</div>
</div>
<p>Не плутай: <em>There live</em> — неправильно; має бути <em>There live</em> лише в поезії.</p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Розширені конструкції',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>There seems to be / There appear to be — для припущень.</li>
<li>There used to be — про минулі звички чи ситуації.</li>
<li>There has been / There have been — Present Perfect.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>There is many cars</em> → <span class="tag-ok">✓</span> <em>There are many cars</em>.</li>
<li><span class="tag-warn">✗</span> Подвійний підмет: <em>There is a problem it is...</em></li>
<li><span class="tag-warn">✗</span> Зайвий <em>do</em>: <em>Do there is?</em> → <span class="tag-ok">✓</span> <em>Is there?</em></li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
