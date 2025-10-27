<?php

namespace Database\Seeders\Pages\IrregularVerbs;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class IrregularVerbsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'irregular-verbs';
    }

    protected function page(): array
    {
        return [
            'title' => 'Irregular verbs — неправильні дієслова',
            'subtitle_html' => <<<'HTML'
<p>Неправильні дієслова мають <strong>особливі форми</strong> для минулого часу та третьої форми (V2/V3).
Їх потрібно запам’ятовувати у трійках: <em>go – went – gone</em>.</p>
HTML,
            'subtitle_text' => 'Неправильні дієслова утворюють V2/V3 без стандартного закінчення -ed, тому їх варто вчити групами.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'verbs',
                'title' => 'Дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Як вчити',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Записуйте трійки разом: <strong>infinitive – past simple – past participle</strong>.</li>
<li>Групуйте за подібною вимовою: <span class="gw-en">sing – sang – sung</span>, <span class="gw-en">drink – drank – drunk</span>.</li>
<li>Використовуйте рими та асоціації: <span class="gw-en">write – wrote – written</span>.</li>
<li>Повторюйте з картками (flashcards) та в реченнях.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Найпоширеніші групи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Однакові форми</strong>: <span class="gw-en">cut – cut – cut</span>, <span class="gw-en">put – put – put</span>.</li>
<li><strong>Зміна голосної</strong>: <span class="gw-en">drive – drove – driven</span>.</li>
<li><strong>Зміна приголосної</strong>: <span class="gw-en">send – sent – sent</span>.</li>
<li><strong>Унікальні</strong>: <span class="gw-en">be – was/were – been</span>, <span class="gw-en">go – went – gone</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Практика у реченнях',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">She <strong>went</strong> home early yesterday.</div>
<div class="gw-ua">Вона <strong>пішла</strong> додому рано вчора.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They have <strong>done</strong> their homework.</div>
<div class="gw-ua">Вони <strong>зробили</strong> домашнє завдання.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I have never <strong>ridden</strong> a horse.</div>
<div class="gw-ua">Я ніколи не <strong>катався</strong> верхи.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Міні-таблиця',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Приклади неправильних дієслів">
<thead>
<tr>
<th>Infinitive</th>
<th>Past Simple</th>
<th>Past Participle</th>
<th>Переклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>bring</td>
<td>brought</td>
<td>brought</td>
<td>приносити</td>
</tr>
<tr>
<td>buy</td>
<td>bought</td>
<td>bought</td>
<td>купувати</td>
</tr>
<tr>
<td>choose</td>
<td>chose</td>
<td>chosen</td>
<td>обирати</td>
</tr>
<tr>
<td>freeze</td>
<td>froze</td>
<td>frozen</td>
<td>замерзати</td>
</tr>
<tr>
<td>teach</td>
<td>taught</td>
<td>taught</td>
<td>викладати</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для запам’ятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Створюйте власні речення з кожним дієсловом.</li>
<li>Записуйте аудіо зі списком та слухайте під час руху.</li>
<li>Відпрацьовуйте у тестах типу <strong>fill-in-the-blank</strong>.</li>
<li>Вчіть по 5–7 дієслів на день, але повторюйте вчорашні.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
