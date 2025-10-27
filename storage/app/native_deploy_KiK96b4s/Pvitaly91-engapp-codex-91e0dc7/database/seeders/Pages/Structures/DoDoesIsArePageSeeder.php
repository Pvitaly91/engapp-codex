<?php

namespace Database\Seeders\Pages\Structures;

class DoDoesIsArePageSeeder extends StructurePageSeeder
{
    protected function slug(): string
    {
        return 'do-does-is-are';
    }

    protected function page(): array
    {
        return [
            'title' => 'Do / Does vs. Is / Are — вибір допоміжного дієслова',
            'subtitle_html' => <<<'HTML'
<p>У питаннях і запереченнях важливо розрізняти, коли потрібні <strong>do / does</strong>, а коли — <strong>is / are</strong>.
Це залежить від смислового дієслова та ролі <em>to be</em> у реченні.</p>
HTML,
            'subtitle_text' => 'Do/does використовуємо зі смисловими дієсловами, а is/are — коли to be виступає присудком або допоміжним.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли використовуємо do/does',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У Present Simple з усіма дієсловами, крім <em>to be</em> та модальних: <span class="gw-en">Do you work here?</span></li>
<li><strong>Does</strong> для третьої особи однини: <span class="gw-en">Does she play tennis?</span></li>
<li>У запереченнях: <span class="gw-en">They do not (don’t) like milk.</span></li>
<li>У коротких відповідях: <span class="gw-en">Yes, I do.</span> / <span class="gw-en">No, he doesn’t.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли використовуємо is/are',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Коли <strong>to be</strong> — основне дієслово: <span class="gw-en">Is he at home?</span></li>
<li>У конструкціях <strong>there is/are</strong>, <strong>be going to</strong>, тривалих часах.</li>
<li>У запереченнях: <span class="gw-en">He isn’t tired.</span></li>
<li>У коротких відповідях: <span class="gw-en">Yes, we are.</span> / <span class="gw-en">No, I’m not.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Як розпізнати',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Перевір, чи після підмета стоїть прикметник/іменник без смислового дієслова. Якщо так — використовуй <strong>is/are</strong>.</li>
<li>Якщо є смислове дієслово (work, live, like), потрібне <strong>do/does</strong>.</li>
<li>Модальні дієслова (can, must) не потребують допоміжних.</li>
<li>У питальному слові <em>where, when</em> дивись на решту речення: <span class="gw-en">Where do you live?</span> / <span class="gw-en">Where is he?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняльна таблиця',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Do/Does vs Is/Are">
<thead>
<tr>
<th>Запитання</th>
<th>Допоміжне</th>
<th>Чому</th>
</tr>
</thead>
<tbody>
<tr>
<td>Do you like jazz?</td>
<td>do</td>
<td>Смислове дієслово <em>like</em></td>
</tr>
<tr>
<td>Is she ready?</td>
<td>is</td>
<td><em>be</em> = присудок</td>
</tr>
<tr>
<td>Are they coming?</td>
<td>are</td>
<td>Present Continuous (be + V-ing)</td>
</tr>
<tr>
<td>Does he know French?</td>
<td>does</td>
<td>Смислове дієслово <em>know</em></td>
</tr>
<tr>
<td>Is there a problem?</td>
<td>is</td>
<td>Конструкція there is/are</td>
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
<ul class="gw-list">
<li>Якщо сумніваєшся, заміни дієслово на <em>be</em>: якщо фраза звучить дивно, використовуй <strong>do/does</strong>.</li>
<li>Пам’ятай про скорочення: <strong>don’t / doesn’t</strong>, <strong>isn’t / aren’t</strong>.</li>
<li>У розмові <strong>Doesn’t she?</strong> звучить природно для уточнення.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
