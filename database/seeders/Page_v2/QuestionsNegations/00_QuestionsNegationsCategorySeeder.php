<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder;

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
Тут ти вивчиш, як правильно формувати <em>питальні речення</em> (Yes/No questions, Wh-questions),
використовувати <em>короткі відповіді</em>, створювати <em>розділові питання</em> (question tags),
а також правильно будувати <em>заперечення</em> з різними типами дієслів.</p>
HTML,
            'subtitle_text' => 'Питальні речення та заперечення в англійській мові: загальні та спеціальні питання, короткі відповіді, розділові питання, заперечення з різними дієсловами.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Типи питань (Question Forms)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes/No Questions</strong> — загальні питання: <span class="gw-en">Do you like coffee? Are you ready?</span></li>
<li><strong>Wh-Questions</strong> — спеціальні питання: <span class="gw-en">What do you want? Where are you going?</span></li>
<li><strong>Question Words</strong> — питальні слова: <span class="gw-en">who, what, where, when, why, how</span>.</li>
<li><strong>Порядок слів</strong> — допоміжне дієслово перед підметом: <span class="gw-en">Do you...? Is he...? Can they...?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Короткі відповіді (Short Answers)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Короткі відповіді</strong> — відповідь за допомогою допоміжного дієслова: <span class="gw-en">Yes, I do. / No, I don't.</span></li>
<li><strong>З to be</strong> — використовуємо форму be: <span class="gw-en">Yes, he is. / No, they aren't.</span></li>
<li><strong>З модальними</strong> — повторюємо модальне дієслово: <span class="gw-en">Yes, you can. / No, I mustn't.</span></li>
<li><strong>Важливо</strong> — не можна відповідати просто "Yes" або "No" у формальному контексті.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Розділові питання (Question Tags)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Question Tags</strong> — питання в кінці речення: <span class="gw-en">You like tea, don't you?</span></li>
<li><strong>Правило</strong> — ствердження + негативний tag, або заперечення + позитивний tag.</li>
<li><strong>Приклади:</strong> <span class="gw-en">She is happy, isn't she? They don't know, do they?</span></li>
<li><strong>Інтонація</strong> — падаюча для підтвердження, зростаюча для справжнього питання.</li>
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
<td><strong>Wh-Questions</strong></td>
<td>A1–A2</td>
<td>Спеціальні питання (who, what, where...)</td>
</tr>
<tr>
<td><strong>Short Answers</strong></td>
<td>A1</td>
<td>Короткі відповіді на питання</td>
</tr>
<tr>
<td><strong>Question Word Order</strong></td>
<td>A1–A2</td>
<td>Порядок слів у питаннях</td>
</tr>
<tr>
<td><strong>Question Tags</strong></td>
<td>B1</td>
<td>Розділові питання (isn't it?, don't you?)</td>
</tr>
<tr>
<td><strong>Subject vs Object Questions</strong></td>
<td>B1</td>
<td>Питання до підмета та додатка</td>
</tr>
<tr>
<td><strong>Indirect Questions</strong></td>
<td>B1–B2</td>
<td>Непрямі питання (Can you tell me...?)</td>
</tr>
<tr>
<td><strong>Negation in Simple</strong></td>
<td>A1–A2</td>
<td>Заперечення з do/does/did + not</td>
</tr>
<tr>
<td><strong>Negation with be, modals and have got</strong></td>
<td>A1–A2</td>
<td>Заперечення з різними типами дієслів</td>
</tr>
<tr>
<td><strong>Negative Pronouns</strong></td>
<td>A2–B1</td>
<td>Заперечні займенники (nobody, nothing, nowhere)</td>
</tr>
<tr>
<td><strong>Double Negatives</strong></td>
<td>B1</td>
<td>Подвійні заперечення та як їх уникати</td>
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
<li><strong>З Simple Tenses</strong> — do/does/did + not: <span class="gw-en">I don't like it. He doesn't know.</span></li>
<li><strong>З to be</strong> — додаємо not: <span class="gw-en">I am not ready. They aren't here.</span></li>
<li><strong>З модальними</strong> — модальне + not: <span class="gw-en">You can't go. She won't come.</span></li>
<li><strong>Заперечні займенники</strong> — nobody, nothing, nowhere: <span class="gw-en">Nobody knows.</span></li>
<li><strong>Подвійні заперечення</strong> — помилка в англійській: <span class="gw-en">❌ I don't know nothing. ✓ I don't know anything.</span></li>
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
<p>Почни з <strong>базових Yes/No питань</strong> — вони найпростіші для засвоєння.</p>
<p>Запам'ятай порядок слів: <strong>допоміжне дієслово + підмет + основне дієслово</strong>.</p>
<p>Для заперечень використовуй <strong>do/does/did + not</strong> з основними дієсловами.</p>
<p><strong>Question tags</strong> — ствердження з негативним тегом, заперечення з позитивним.</p>
<p>Уникай подвійних заперечень — в англійській мові це помилка!</p>
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
            'slug' => 'pytalni-rechennia-ta-zaperechennia',
            'title' => 'Питальні речення та заперечення',
            'language' => 'uk',
        ];
    }
}
