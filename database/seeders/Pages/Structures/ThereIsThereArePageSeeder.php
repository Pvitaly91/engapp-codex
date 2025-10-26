<?php

namespace Database\Seeders\Pages\Structures;

class ThereIsThereArePageSeeder extends StructurePageSeeder
{
    protected function slug(): string
    {
        return 'there-is-there-are';
    }

    protected function page(): array
    {
        return [
            'title' => 'There is / There are — наявність предметів',
            'subtitle_html' => <<<'HTML'
<p>Конструкції <strong>there is / there are</strong> вказують, що щось <em>існує</em> або знаходиться в певному місці.
Вони не перекладаються як «там».</p>
HTML,
            'subtitle_text' => 'There is/are повідомляють про існування предметів чи людей у певному місці.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Форми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Теперішній час</div>
<pre class="gw-formula">There is + однина / незлічуване
There are + множина</pre>
<div class="gw-code-badge">Минулий час</div>
<pre class="gw-formula">There was / There were</pre>
<div class="gw-code-badge">Майбутнє</div>
<pre class="gw-formula">There will be / There is going to be</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Словопорядок',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Після конструкції ставимо <strong>іменник</strong> та обставину місця: <span class="gw-en">There is a café on the corner.</span></li>
<li>Заперечення: <span class="gw-en">There isn’t</span> / <span class="gw-en">There aren’t</span>.</li>
<li>Питання: <span class="gw-en">Is there...?</span>, <span class="gw-en">Are there...?</span></li>
<li>У відповіді повторюємо <em>there</em>: <span class="gw-en">Yes, there is.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Вживання з кількістю',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>There is</strong> використовується навіть із множиною, якщо перше слово незлічуване: <span class="gw-en">There is milk and eggs.</span></li>
<li>З кількістю ставимо <strong>there are + числівник</strong>: <span class="gw-en">There are two chairs.</span></li>
<li>Для відсутності — <strong>there is no / there are no</strong> або <strong>there isn’t any</strong>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-ex">
<div class="gw-en">There’s a new message for you.</div>
<div class="gw-ua">Є нове повідомлення для тебе.</div>
</div>
<div class="gw-ex">
<div class="gw-en">There were many tourists last summer.</div>
<div class="gw-ua">Минулого літа було багато туристів.</div>
</div>
<div class="gw-ex">
<div class="gw-en">Will there be enough time?</div>
<div class="gw-ua">Чи буде достатньо часу?</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порада',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">📍</div>
<div>
<p>Пам’ятай, що <strong>there</strong> у цій конструкції не перекладається. Основний наголос має бути на іменнику після <em>be</em>.</p>
<p>У розмові скорочуй: <strong>there’s</strong>, <strong>there’re</strong> (у неформальній мові).</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
