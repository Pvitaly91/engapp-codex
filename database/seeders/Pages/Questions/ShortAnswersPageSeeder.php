<?php

namespace Database\Seeders\Pages\Questions;

class ShortAnswersPageSeeder extends QuestionPageSeeder
{
    protected function slug(): string
    {
        return 'short-answers';
    }

    protected function page(): array
    {
        return [
            'title' => 'Short answers — короткі відповіді',
            'subtitle_html' => <<<'HTML'
<p>Короткі відповіді допомагають звучати природно: ми повторюємо <strong>допоміжне дієслово і займенник</strong>, а не все речення.</p>
HTML,
            'subtitle_text' => 'Короткі відповіді складаються з допоміжного дієслова та займенника, щоб уникати повторів.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Базова структура',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">Yes/No + , + [займенник] + [допоміжне (скорочене)]</pre>
<ul class="gw-list">
<li><span class="gw-en">Yes, I am.</span> / <span class="gw-en">No, he isn’t.</span></li>
<li><span class="gw-en">Yes, we do.</span> / <span class="gw-en">No, they don’t.</span></li>
<li><span class="gw-en">Yes, she has.</span> / <span class="gw-en">No, it hasn’t.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Коли обираємо займенник',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Завжди відповідаємо займенником, а не іменником: <span class="gw-en">Are the students ready? — Yes, they are.</span></li>
<li>Якщо питали про себе, відповідаємо <strong>I am / I do</strong>.</li>
<li>З питанням у множині — використовуйте <strong>we/they</strong>, якщо себе включаєте або ні відповідно.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Особливі випадки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Після <strong>Let’s...</strong> відповідаємо <span class="gw-en">Yes, let’s.</span> або <span class="gw-en">No, let’s not.</span></li>
<li>На питання з <strong>there is/are</strong> відповіді: <span class="gw-en">Yes, there is.</span> / <span class="gw-en">No, there aren’t.</span></li>
<li>З модальними дієсловами повторюємо модальне: <span class="gw-en">Can she drive? — Yes, she can.</span></li>
<li>У минулому часі: <span class="gw-en">Yes, they did.</span> / <span class="gw-en">No, we didn’t.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Інтонація',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>У стверджувальній відповіді інтонація знижується: <span class="gw-en">Yes, I do.</span></li>
<li>У заперечній — падає різкіше: <span class="gw-en">No, he doesn’t.</span></li>
<li>Ввічлива пауза перед відповіддю робить промову природнішою.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Повторювати всю фразу: <em>Yes, I like it.</em> → <strong>Yes, I do.</strong></li>
<li><span class="tag-warn">✗</span> Забувати <em>do/does/did</em> після смислових дієслів.</li>
<li><span class="tag-ok">✓</span> Слідкуй за узгодженням допоміжного дієслова з часом питання.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
