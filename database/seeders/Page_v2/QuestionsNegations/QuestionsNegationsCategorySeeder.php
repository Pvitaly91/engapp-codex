<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class QuestionsNegationsCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '8';
    }

    protected function description(): array
    {
        return [
            'title' => 'Питальні речення та заперечення',
            'subtitle_html' => <<<'HTML'
<p><strong>Питальні речення та заперечення</strong> — це важливий розділ англійської граматики.
Тут ти вивчиш, як правильно ставити <em>різні типи питань (Yes/No questions, Wh-questions, question tags)</em>,
використовувати <em>питальні слова (who, what, where, when, why, how)</em>,
а також формувати <em>заперечення з do/does/did, be, модальними дієсловами</em> та використовувати <em>заперечні займенники (nobody, nothing, nowhere)</em>.</p>
HTML,
            'subtitle_text' => 'Питальні речення та заперечення в англійській мові: типи питань, питальні слова, заперечення з do/does/did, заперечні займенники.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Типи питальних речень (Types of Questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes/No questions</strong> — загальні питання: <span class="gw-en">Do you like coffee?</span>, <span class="gw-en">Are you a student?</span></li>
<li><strong>Wh-questions</strong> — спеціальні питання з питальними словами: <span class="gw-en">What do you do?</span>, <span class="gw-en">Where do you live?</span></li>
<li><strong>Alternative questions</strong> — вибір між варіантами: <span class="gw-en">Do you prefer coffee or tea?</span></li>
<li><strong>Question tags</strong> — розділові питання: <span class="gw-en">You like coffee, don't you?</span>, <span class="gw-en">She is nice, isn't she?</span></li>
<li><strong>Negative questions</strong> — заперечні питання: <span class="gw-en">Don't you know him?</span>, <span class="gw-en">Isn't it beautiful?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питальні слова (Question Words)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Who</strong> — хто: <span class="gw-en">Who is calling?</span></li>
<li><strong>What</strong> — що: <span class="gw-en">What do you want?</span></li>
<li><strong>Where</strong> — де, куди: <span class="gw-en">Where do you live?</span></li>
<li><strong>When</strong> — коли: <span class="gw-en">When does the train arrive?</span></li>
<li><strong>Why</strong> — чому: <span class="gw-en">Why are you late?</span></li>
<li><strong>How</strong> — як, яким чином: <span class="gw-en">How do you do it?</span></li>
<li><strong>Which</strong> — який (з варіантів): <span class="gw-en">Which book do you prefer?</span></li>
<li><strong>Whose</strong> — чий: <span class="gw-en">Whose bag is this?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Порядок слів у питаннях (Question Word Order)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes/No questions:</strong> Допоміжне дієслово + підмет + основне дієслово: <span class="gw-en">Do you speak English?</span></li>
<li><strong>Wh-questions:</strong> Питальне слово + допоміжне дієслово + підмет + основне дієслово: <span class="gw-en">Where do you live?</span></li>
<li><strong>Subject questions:</strong> Коли питаємо про підмет, допоміжне дієслово не потрібне: <span class="gw-en">Who called you?</span> (не Who did call you?)</li>
<li><strong>З be/модальними:</strong> Просто міняємо підмет і дієслово місцями: <span class="gw-en">Are you ready?</span>, <span class="gw-en">Can you help?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Питальні речення та заперечення">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Question Forms</strong></td>
<td>A1</td>
<td>Як ставити запитання</td>
</tr>
<tr>
<td><strong>Wh-questions</strong></td>
<td>A1–A2</td>
<td>Спеціальні питання (who, what, where, when, why, how)</td>
</tr>
<tr>
<td><strong>Short Answers</strong></td>
<td>A1</td>
<td>Короткі відповіді (Yes, I do / No, I don't)</td>
</tr>
<tr>
<td><strong>Question Tags</strong></td>
<td>B1</td>
<td>Розділові питання (isn't it?, don't you?)</td>
</tr>
<tr>
<td><strong>Subject vs Object Questions</strong></td>
<td>B1</td>
<td>Питання про підмет і додаток</td>
</tr>
<tr>
<td><strong>Indirect Questions</strong></td>
<td>B1–B2</td>
<td>Непрямі питання (Can you tell me…?)</td>
</tr>
<tr>
<td><strong>Negation</strong></td>
<td>A1–A2</td>
<td>Заперечення з do/does/did, be, модальними</td>
</tr>
<tr>
<td><strong>Negative Pronouns</strong></td>
<td>A2–B1</td>
<td>Заперечні займенники (nobody, nothing, nowhere)</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Заперечення (Negation)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>З do/does/did:</strong> підмет + do/does/did + not + дієслово: <span class="gw-en">I don't like coffee.</span>, <span class="gw-en">She doesn't speak French.</span></li>
<li><strong>З be:</strong> підмет + be + not: <span class="gw-en">I am not ready.</span>, <span class="gw-en">They aren't here.</span></li>
<li><strong>З модальними:</strong> підмет + модальне + not: <span class="gw-en">I can't swim.</span>, <span class="gw-en">You mustn't worry.</span></li>
<li><strong>З have got:</strong> підмет + haven't/hasn't + got: <span class="gw-en">I haven't got time.</span></li>
<li><strong>Заперечні займенники:</strong> <span class="gw-en">nobody, nothing, nowhere, no one</span> — вже містять заперечення: <span class="gw-en">Nobody knows.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Короткі відповіді (Short Answers)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes/No answers:</strong> Yes/No + підмет + допоміжне дієслово: <span class="gw-en">Yes, I do. / No, I don't.</span></li>
<li><strong>З be:</strong> <span class="gw-en">Yes, I am. / No, I'm not.</span>, <span class="gw-en">Yes, she is. / No, she isn't.</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">Yes, I can. / No, I can't.</span></li>
<li><strong>Уникай:</strong> <span class="gw-en">Yes, I speak.</span> ✗ — потрібно: <span class="gw-en">Yes, I do.</span> ✓</li>
<li><strong>Повні відповіді:</strong> можна відповісти повним реченням: <span class="gw-en">Yes, I speak English.</span></li>
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
<p>Почни з <strong>базових питань з do/does/did</strong> — вони найчастіші в розмовній мові.</p>
<p>Запам'ятай порядок слів: <strong>питальне слово → допоміжне дієслово → підмет → основне дієслово</strong>.</p>
<p><strong>Question tags</strong> — це коротенькі хвостики в кінці речення для підтвердження: <span class="gw-en">You're tired, aren't you?</span></p>
<p><strong>Negative pronouns</strong> (nobody, nothing) вже містять заперечення — не додавай <em>not</em>: <span class="gw-en">Nobody knows.</span> (не Nobody doesn't know.)</p>
<p>У <strong>short answers</strong> повторюй допоміжне дієслово з питання: <span class="gw-en">Do you? → Yes, I do.</span></p>
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
            'slug' => '8',
            'title' => 'Питальні речення та заперечення',
            'language' => 'uk',
        ];
    }
}
