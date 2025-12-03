<?php

namespace Database\Seeders\Pages\BasicGrammar;

class WordOrderQuestionsNegativesPageSeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'word-order-questions-negatives';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order in Questions and Negatives — Питання та заперечення',
            'subtitle_html' => <<<'HTML'
<p>Правильний <strong>порядок слів</strong> у питаннях і запереченнях — важлива частина англійської граматики.
На відміну від стверджувальних речень, тут потрібні <em>допоміжні дієслова</em> та особливий порядок слів.</p>
HTML,
            'subtitle_text' => 'Порядок слів у питальних та заперечних реченнях англійської мови: структура, допоміжні дієслова та типові помилки.',
            'locale' => 'uk',
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Questions',
                'Negatives',
                'Auxiliary Verbs',
                'Do/Does/Did',
                'Wh-Questions',
                'A1',
                'A2',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Порядок слів у питаннях (Questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">В англійській мові питання будуються з <strong>допоміжним дієсловом</strong> перед підметом:</p>
<ul class="gw-list">
<li><strong>Do / Does / Did + Subject + Verb</strong> — для більшості дієслів.</li>
<li><strong>Auxiliary + Subject + Verb</strong> — для модальних дієслів та to be.</li>
</ul>
<div class="gw-ex">
<div class="gw-en"><strong>Do</strong> you <strong>like</strong> pizza?</div>
<div class="gw-ua">Ти любиш піцу?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Does</strong> she <strong>play</strong> tennis?</div>
<div class="gw-ua">Вона грає в теніс?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Did</strong> they <strong>watch</strong> the movie?</div>
<div class="gw-ua">Вони дивились фільм?</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питання з дієсловами to be, have та модальними',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Для <strong>to be</strong>, <strong>have</strong> та модальних дієслів <em>do/does/did</em> не потрібні — просто міняємо місцями підмет і дієслово:</p>
<div class="gw-ex">
<div class="gw-en"><strong>Are</strong> you ready?</div>
<div class="gw-ua">Ти готовий?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Is</strong> he coming?</div>
<div class="gw-ua">Він іде?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Have</strong> you finished?</div>
<div class="gw-ua">Ти закінчив?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Can</strong> you swim?</div>
<div class="gw-ua">Ти вмієш плавати?</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Wh-питання (Wh-Questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Питання з <strong>who, what, where, when, why, which, how</strong> будуються за схемою:</p>
<p class="mb-2"><strong>Wh-word + Auxiliary + Subject + Verb</strong></p>
<div class="gw-ex">
<div class="gw-en"><strong>Where</strong> do you live?</div>
<div class="gw-ua">Де ти живеш?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>What</strong> did she eat?</div>
<div class="gw-ua">Що вона їла?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Why</strong> are they running?</div>
<div class="gw-ua">Чому вони біжать?</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>How</strong> can I help?</div>
<div class="gw-ua">Як я можу допомогти?</div>
</div>
<p class="mt-2 text-slate-600">⚠️ Якщо Wh-слово є підметом, допоміжне дієслово не потрібне: <span class="gw-en">Who called you?</span></p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Порядок слів у запереченнях (Negatives)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Заперечення будуються з <strong>допоміжним дієсловом + not</strong>:</p>
<ul class="gw-list">
<li><strong>Do/Does/Did + not + Verb</strong> — для звичайних дієслів.</li>
<li><strong>Auxiliary + not</strong> — для to be, have, модальних.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>do not (don't)</strong> like apples.</div>
<div class="gw-ua">Я не люблю яблука.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>does not (doesn't)</strong> play football.</div>
<div class="gw-ua">Вона не грає у футбол.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They <strong>did not (didn't)</strong> see the movie.</div>
<div class="gw-ua">Вони не бачили фільм.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He <strong>is not (isn't)</strong> ready.</div>
<div class="gw-ua">Він не готовий.</div>
</div>
<div class="gw-ex">
<div class="gw-en">You <strong>must not (mustn't)</strong> touch it.</div>
<div class="gw-ua">Ти не повинен це чіпати.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Структура питань і заперечень',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порядок слів у питаннях і запереченнях">
<thead>
<tr>
<th>Тип</th>
<th>Структура</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Yes/No питання</td>
<td>Aux + S + V</td>
<td><span class="gw-en">Do you like pizza?</span></td>
</tr>
<tr>
<td>Wh-питання</td>
<td>Wh + Aux + S + V</td>
<td><span class="gw-en">Where do you live?</span></td>
</tr>
<tr>
<td>Заперечення</td>
<td>S + Aux + not + V</td>
<td><span class="gw-en">I don't like apples.</span></td>
</tr>
<tr>
<td>To be (питання)</td>
<td>Be + S</td>
<td><span class="gw-en">Are you ready?</span></td>
</tr>
<tr>
<td>To be (заперечення)</td>
<td>S + Be + not</td>
<td><span class="gw-en">He isn't ready.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Скорочені форми (Contractions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">У розмовній мові <strong>not</strong> часто скорочується:</p>
<ul class="gw-list">
<li><strong>do not → don't</strong></li>
<li><strong>does not → doesn't</strong></li>
<li><strong>did not → didn't</strong></li>
<li><strong>is not → isn't</strong></li>
<li><strong>are not → aren't</strong></li>
<li><strong>was not → wasn't</strong></li>
<li><strong>were not → weren't</strong></li>
<li><strong>will not → won't</strong></li>
<li><strong>cannot → can't</strong></li>
<li><strong>must not → mustn't</strong></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>You like pizza?</em> ❌ → <strong>Do you like pizza?</strong> (Потрібне допоміжне дієслово!)</li>
<li><span class="tag-warn">✗</span> <em>Where you live?</em> ❌ → <strong>Where do you live?</strong> (Aux + S у Wh-питаннях.)</li>
<li><span class="tag-warn">✗</span> <em>She don't like it.</em> ❌ → <strong>She doesn't like it.</strong> (He/She/It → does.)</li>
<li><span class="tag-warn">✗</span> <em>I no like apples.</em> ❌ → <strong>I don't like apples.</strong> (Заперечення з do + not.)</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підказки для запамʼятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> <strong>Питання:</strong> допоміжне дієслово виходить на перше місце.</li>
<li><span class="tag-ok">✓</span> <strong>Заперечення:</strong> допоміжне дієслово + not перед основним дієсловом.</li>
<li><span class="tag-ok">✓</span> <strong>To be / модальні:</strong> не потребують do/does/did.</li>
<li><span class="tag-ok">✓</span> <strong>Wh-підмет:</strong> Who/What як підмет — без допоміжного дієслова.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
