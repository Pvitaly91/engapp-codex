<?php

namespace Database\Seeders\Pages;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class QuestionsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'questions';
    }

    protected function page(): array
    {
        return [
            'title' => 'Questions — побудова запитань в англійській',
            'subtitle_html' => <<<'HTML'
<p>Запитання в англійській будуються за допомогою <strong>допоміжних дієслів</strong>, інверсії та питальних слів. Важливо знати,
коли використовувати <em>do/does/did</em>, <em>to be</em>, модальні та <em>have</em>.</p>
HTML,
            'subtitle_text' => 'Англійські запитання вимагають правильного допоміжного дієслова та порядку слів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'question-forms',
                'title' => 'Питальні конструкції',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Загальні запитання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">[Допоміжне] + [підмет] + [основне дієслово]?</pre>
<ul class="gw-list">
<li><span class="gw-en">Do</span> you like coffee?</li>
<li><span class="gw-en">Does</span> she work here?</li>
<li><span class="gw-en">Did</span> they call you?</li>
</ul>
<p>У відповідях використовуємо короткі форми: <span class="gw-en">Yes, I do.</span></p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Wh-questions',
                    'css_class' => null,
                    'body' => <<<'HTML'
<pre class="gw-formula">[Wh-слово] + [допоміжне] + [підмет] + [дієслово]?</pre>
<ul class="gw-list">
<li><span class="gw-en">Where do</span> you live?</li>
<li><span class="gw-en">When did</span> they arrive?</li>
<li><span class="gw-en">Why does</span> he study French?</li>
</ul>
<p>Wh-слово може бути підметом: <span class="gw-en">Who lives here?</span> (без допоміжного).</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Be та модальні',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Коли дієслово <em>to be</em>, інверсія без <em>do</em>: <span class="gw-en">Are you ready?</span></li>
<li>Модальні дієслова стоять на початку: <span class="gw-en">Can you help?</span>, <span class="gw-en">Should we go?</span></li>
<li>З <em>have got</em>: <span class="gw-en">Have you got any money?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Короткі відповіді',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Короткі відповіді">
<thead>
<tr>
<th>Питання</th>
<th>Позитивна</th>
<th>Негативна</th>
</tr>
</thead>
<tbody>
<tr>
<td><span class="gw-en">Do you play?</span></td>
<td><span class="gw-en">Yes, I do.</span></td>
<td><span class="gw-en">No, I don’t.</span></td>
</tr>
<tr>
<td><span class="gw-en">Is she here?</span></td>
<td><span class="gw-en">Yes, she is.</span></td>
<td><span class="gw-en">No, she isn’t.</span></td>
</tr>
<tr>
<td><span class="gw-en">Can they come?</span></td>
<td><span class="gw-en">Yes, they can.</span></td>
<td><span class="gw-en">No, they can’t.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок слів',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-box--accent">
<p><strong>1</strong> — допоміжне, <strong>2</strong> — підмет, <strong>3</strong> — основне дієслово, <strong>4</strong> — обставини.</p>
<p><span class="gw-en">Did</span> (1) <span class="gw-en">you</span> (2) <span class="gw-en">finish</span> (3) <span class="gw-en">the report yesterday</span> (4)?</p>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Tag-questions',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Ствердження + коротке запитання: <span class="gw-en">You like tea, don’t you?</span></li>
<li>Якщо основна частина негативна, тег позитивний: <span class="gw-en">He isn’t late, is he?</span></li>
<li>Тон вгору — справжнє питання, тон вниз — підтвердження.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Indirect questions',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Непрямі запитання не мають інверсії:</p>
<ul class="gw-list">
<li><span class="gw-en">Could you tell me where the station is?</span></li>
<li><span class="gw-en">I wonder if he will come.</span></li>
</ul>
<p>Після <em>if/whether</em> порядок як у ствердженні.</p>
HTML,
                ],
            ],
        ];
    }
}
