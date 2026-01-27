<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PassiveVoiceCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '13';
    }

    protected function description(): array
    {
        return [
            'title' => 'Пасивний стан (Passive Voice)',
            'subtitle_html' => <<<'HTML'
<p><strong>Пасивний стан (Passive Voice)</strong> — це важлива граматична структура англійської мови,
яка дозволяє зосередити увагу на дії або об'єкті, а не на виконавці. У пасивному стані підмет
речення зазнає дії, а не виконує її. Тут ти вивчиш <em>утворення пасиву</em> в різних часах,
<em>питальні та заперечні форми</em>, а також <em>пасив з модальними дієсловами</em>.</p>
HTML,
            'subtitle_text' => 'Пасивний стан в англійській мові: утворення в різних часах, питальні та заперечні форми, пасив з модальними дієсловами, стилістичне використання.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Базове утворення пасиву',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Формула:</strong> <span class="gw-en">Subject + to be + Past Participle (V3)</span></li>
<li><strong>Present Simple:</strong> <span class="gw-en">It is made in China.</span></li>
<li><strong>Past Simple:</strong> <span class="gw-en">It was built in 1990.</span></li>
<li><strong>Виконавець (by):</strong> <span class="gw-en">The book was written by J.K. Rowling.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Пасив у різних часах',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Present Continuous:</strong> <span class="gw-en">It is being built now.</span></li>
<li><strong>Past Continuous:</strong> <span class="gw-en">It was being repaired.</span></li>
<li><strong>Present Perfect:</strong> <span class="gw-en">It has been done.</span></li>
<li><strong>Future Simple:</strong> <span class="gw-en">It will be finished tomorrow.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питання та заперечення в пасиві',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Питання:</strong> <span class="gw-en">Is it made in China? Was it built in 1990?</span></li>
<li><strong>Заперечення:</strong> <span class="gw-en">It isn't made here. It wasn't built by them.</span></li>
<li><strong>Короткі відповіді:</strong> <span class="gw-en">Yes, it is. / No, it wasn't.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Пасивний стан">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Present Simple Passive</strong></td>
<td>A2</td>
<td>It is made in China</td>
</tr>
<tr>
<td><strong>Past Simple Passive</strong></td>
<td>A2</td>
<td>It was built in 1990</td>
</tr>
<tr>
<td><strong>Passive: All Main Tenses</strong></td>
<td>B1–B2</td>
<td>Огляд утворення у всіх часах</td>
</tr>
<tr>
<td><strong>Negatives & Questions</strong></td>
<td>A2–B1</td>
<td>Питання та заперечення в пасиві</td>
</tr>
<tr>
<td><strong>Passive with Modals</strong></td>
<td>B1–B2</td>
<td>can/must/should + be + V3</td>
</tr>
<tr>
<td><strong>Get-passive</strong></td>
<td>B2</td>
<td>get married, get fired</td>
</tr>
<tr>
<td><strong>When to Use Passive</strong></td>
<td>B1–B2</td>
<td>Стилістика та типові помилки</td>
</tr>
<tr>
<td><strong>Causative Form</strong></td>
<td>B2</td>
<td>have / get something done</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Пасив з модальними дієсловами',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Формула:</strong> <span class="gw-en">modal + be + Past Participle (V3)</span></li>
<li><strong>can:</strong> <span class="gw-en">It can be done.</span></li>
<li><strong>must:</strong> <span class="gw-en">It must be finished by Friday.</span></li>
<li><strong>should:</strong> <span class="gw-en">It should be checked carefully.</span></li>
<li><strong>will:</strong> <span class="gw-en">It will be delivered tomorrow.</span></li>
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
<p>Почни з <strong>Present та Past Simple Passive</strong> — вони найпоширеніші.</p>
<p>Запам'ятай формулу: <strong>to be + Past Participle (V3)</strong>.</p>
<p>Пасив використовуй, коли <strong>виконавець невідомий або неважливий</strong>.</p>
<p>Вивчи <strong>неправильні дієслова</strong> — їхня третя форма (V3) потрібна для пасиву.</p>
<p>Практикуй трансформацію речень з <strong>Active в Passive</strong> і навпаки.</p>
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
            'slug' => 'pasyvnyi-stan',
            'title' => 'Пасивний стан (Passive Voice)',
            'language' => 'uk',
        ];
    }
}
