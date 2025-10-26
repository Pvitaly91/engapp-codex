<?php

namespace Database\Seeders\Pages\Questions;

class QuestionFormsPageSeeder extends QuestionPageSeeder
{
    protected function slug(): string
    {
        return 'question-forms';
    }

    protected function page(): array
    {
        return [
            'title' => 'Question forms — як ставити запитання',
            'subtitle_html' => <<<'HTML'
<p>Питання в англійській мові будуються через <strong>інверсію допоміжного дієслова</strong> або спеціальні питальні слова.</p>
HTML,
            'subtitle_text' => 'Питання утворюємо інверсією допоміжного дієслова та використанням питальних слів.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Загальні питання (Yes/No)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Структура</div>
<pre class="gw-formula">[Допоміжне] + [підмет] + [смислове дієслово]?</pre>
<ul class="gw-list">
<li><strong>Do/Does</strong> у Present Simple: <span class="gw-en">Do you like coffee?</span></li>
<li><strong>Did</strong> у Past Simple: <span class="gw-en">Did they arrive?</span></li>
<li><strong>Am/Is/Are</strong>, <strong>Have/Has</strong>, <strong>Will</strong> самі утворюють питання.</li>
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
<li><strong>What</strong> — що? який?</li>
<li><strong>Where</strong> — де?</li>
<li><strong>When</strong> — коли?</li>
<li><strong>Why</strong> — чому?</li>
<li><strong>How</strong> + прикметник/прислівник: <span class="gw-en">How often?</span>, <span class="gw-en">How much?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питальні слова і порядок слів',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Якщо <strong>wh-слово</strong> — підмет, інверсія не потрібна: <span class="gw-en">Who called you?</span></li>
<li>З <strong>whose</strong> додаємо іменник: <span class="gw-en">Whose bag is this?</span></li>
<li>В непрямих питаннях порядок як у ствердженні: <span class="gw-en">I don’t know where he is.</span></li>
<li>Після <em>what/which</em> можливий іменник: <span class="gw-en">Which book do you prefer?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Скорочені відповіді',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes, I do.</strong> / <strong>No, she doesn’t.</strong> — повторюємо допоміжне дієслово.</li>
<li>З <strong>to be</strong>: <span class="gw-en">Yes, we are.</span> / <span class="gw-en">No, I’m not.</span></li>
<li>У минулому: <span class="gw-en">Yes, they did.</span> / <span class="gw-en">No, he didn’t.</span></li>
<li>У перфектних часах: <span class="gw-en">Yes, she has.</span> / <span class="gw-en">No, they haven’t.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Інтонація та ввічливість',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Питання без wh-слова підвищують інтонацію в кінці.</li>
<li>Для ввічливих питань додаємо <em>please</em> або використовуйте конструкцію <strong>Could you...?</strong></li>
<li>У формальних ситуаціях краще ставити допоміжне <strong>could/would</strong> замість <em>can/will</em>.</li>
<li>Не забувай про знак питання в письмі навіть після непрямих питань.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
