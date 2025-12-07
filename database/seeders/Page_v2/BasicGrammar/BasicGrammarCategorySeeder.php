<?php

namespace Database\Seeders\Page_v2\BasicGrammar;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class BasicGrammarCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return 'basic-grammar';
    }

    protected function description(): array
    {
        return [
            'title' => 'Basic Grammar — Базова граматика',
            'subtitle_html' => <<<'HTML'
<p><strong>Базова граматика</strong> — це фундамент англійської мови. У цьому розділі ти вивчиш основні правила побудови речень:
<em>порядок слів</em>, питання та заперечення, прислівники та обставини, а також просунуті структури підсилення.</p>
HTML,
            'subtitle_text' => 'Базова граматика англійської мови: порядок слів, питання, заперечення, прислівники та просунуті структури.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Порядок слів (Word Order)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Базова структура S–V–O</strong> — підмет, дієслово, додаток: <span class="gw-en">She reads books.</span></li>
<li><strong>Питання</strong> — допоміжне дієслово перед підметом: <span class="gw-en">Do you like pizza?</span></li>
<li><strong>Заперечення</strong> — do/does/did + not: <span class="gw-en">I don't like apples.</span></li>
<li><strong>Wh-питання</strong> — Wh + Aux + S + V: <span class="gw-en">Where do you live?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники та обставини',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Прислівники частотності</strong> (always, often, never) — перед дієсловом: <span class="gw-en">She always drinks coffee.</span></li>
<li><strong>Прислівники способу дії</strong> (quickly, well) — в кінці речення: <span class="gw-en">He speaks English fluently.</span></li>
<li><strong>Обставини місця та часу</strong> — порядок Place → Time: <span class="gw-en">She works at home every day.</span></li>
<li><strong>Формула:</strong> Manner → Place → Time.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Дієслова та додатки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Допоміжні дієслова</strong> (be, do, have) — перед основним дієсловом.</li>
<li><strong>Модальні дієслова</strong> (can, must, should) — без to: <span class="gw-en">She can swim.</span></li>
<li><strong>Фразові дієслова</strong> — займенник між дієсловом і часткою: <span class="gw-en">Turn it off.</span></li>
<li><strong>Нерозділювані phrasal verbs</strong> — додаток після: <span class="gw-en">Look after the kids.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми базової граматики">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Basic Word Order</strong></td>
<td>A1–A2</td>
<td>Базова структура S–V–O у ствердженнях</td>
</tr>
<tr>
<td><strong>Questions & Negatives</strong></td>
<td>A1–A2</td>
<td>Питання та заперечення з do/does/did</td>
</tr>
<tr>
<td><strong>Adverbs & Adverbials</strong></td>
<td>A2–B1</td>
<td>Позиція прислівників та обставин</td>
</tr>
<tr>
<td><strong>Verbs & Objects</strong></td>
<td>A2–B1</td>
<td>Модальні та фразові дієслова</td>
</tr>
<tr>
<td><strong>Advanced Emphasis</strong></td>
<td>B1–B2</td>
<td>Інверсія та cleft-речення</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Просунуті структури підсилення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Інверсія</strong> — з негативними прислівниками: <span class="gw-en">Never have I seen...</span></li>
<li><strong>It-cleft</strong> — підсилення елемента: <span class="gw-en">It was you who called.</span></li>
<li><strong>What-cleft</strong> — підсилення дії: <span class="gw-en">What I need is rest.</span></li>
<li><strong>Emphatic do</strong> — підсилення ствердження: <span class="gw-en">I do like it!</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Почни з <strong>базового порядку слів S–V–O</strong> — це основа англійського речення.</p>
<p>Потім вивчи питання та заперечення з <strong>do/does/did</strong>.</p>
<p>Прислівники частотності ставляться <strong>перед дієсловом</strong>, а способу дії — <strong>в кінці</strong>.</p>
<p>Для просунутого рівня — інверсія та cleft-речення додають <strong>формальності та акценту</strong>.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'basic-grammar',
            'title' => 'Базова граматика',
            'language' => 'uk',
        ];
    }
}
