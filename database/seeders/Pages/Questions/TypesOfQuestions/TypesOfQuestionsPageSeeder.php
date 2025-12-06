<?php

namespace Database\Seeders\Pages\Questions\TypesOfQuestions;

use Database\Seeders\Pages\Questions\QuestionPageSeeder;

class TypesOfQuestionsPageSeeder extends QuestionPageSeeder
{
    protected function slug(): string
    {
        return 'types-of-questions';
    }

    protected function page(): array
    {
        return [
            'title' => 'Types of questions — види запитань',
            'subtitle_html' => <<<'HTML'
<p>Питальні речення можуть мати різну мету: уточнити інформацію, запропонувати вибір або підтвердити припущення.</p>
HTML,
            'subtitle_text' => 'Питальні речення бувають загальні, спеціальні, альтернативні та уточнювальні (tag questions).',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Загальні питання (Yes/No)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Структура</div>
<pre class="gw-formula">[Допоміжне] + [підмет] + [решта]?</pre>
<ul class="gw-list">
<li>Очікуємо відповідь <strong>yes/no</strong>: <span class="gw-en">Do you play tennis?</span></li>
<li>Інверсія обов'язкова, якщо є допоміжне <em>do/does/did</em> або модальне.</li>
<li>З <strong>to be</strong> чи <strong>have</strong> як смисловим дієсловом допоміжне не додаємо: <span class="gw-en">Is she ready?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Спеціальні питання (Wh- questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">[Wh-слово] + [допоміжне] + [підмет] + [решта]?</pre>
<ul class="gw-list">
<li>Відповідь не так/ні, а конкретна інформація: <span class="gw-en">Where do you live?</span></li>
<li>Якщо wh-слово — підмет, інверсія не потрібна: <span class="gw-en">Who called you?</span></li>
<li>Завжди узгоджуйте час допоміжного з дієсловом питання.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Альтернативні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">[Допоміжне] + [підмет] + [варіант 1] <em>or</em> [варіант 2]?</pre>
<ul class="gw-list">
<li>Пропонують вибір: <span class="gw-en">Would you like tea or coffee?</span></li>
<li>Інтонація підвищується на першій опції та падає на останній.</li>
<li>Якщо варіантів більше, перед останнім ставимо <em>or</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Розділові питання (Tag questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">[Ствердження], [допоміжне] + [займенник] + ?</pre>
<ul class="gw-list">
<li>Використовуємо для підтвердження: <span class="gw-en">You are coming, <strong>aren't you</strong>?</span></li>
<li>Допоміжне узгоджується з часом та підметом головного речення.</li>
<li>Після заперечення в основній частині тег стверджувальний: <span class="gw-en">She <strong>isn't</strong> busy, <strong>is she</strong>?</span></li>
</ul>
HTML,
                ],
            ],
        ];
    }

    public function categorySlug(): string
    {
        return 'types-of-questions';
    }
}
