<?php

namespace Database\Seeders\Pages\BasicGrammar;

class AdvancedWordOrderEmphasisPageSeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'advanced-word-order-emphasis';
    }

    protected function page(): array
    {
        return [
            'title' => 'Advanced Word Order and Emphasis — Інверсія та підсилення',
            'subtitle_html' => <<<'HTML'
<p>В англійській мові <strong>інверсія</strong> та <strong>cleft-речення</strong> використовуються для підсилення (emphasis).
Ці структури допомагають виділити важливу інформацію або створити більш формальний стиль.</p>
HTML,
            'subtitle_text' => 'Інверсія, cleft-речення та інші способи підсилення в англійській мові: структура та приклади.',
            'locale' => 'uk',
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Inversion',
                'Emphasis',
                'Cleft Sentences',
                'Fronting',
                'B1',
                'B2',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Інверсія з негативними прислівниками',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Коли речення починається з <strong>негативних прислівників</strong>, порядок слів змінюється: <em>Auxiliary + Subject</em>.</p>
<ul class="gw-list">
<li><strong>Never, rarely, seldom, hardly, scarcely, not only, no sooner</strong></li>
</ul>
<div class="gw-ex">
<div class="gw-en"><strong>Never have I</strong> seen such a beautiful sunset.</div>
<div class="gw-ua">Ніколи я не бачив такого гарного заходу сонця.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Rarely does she</strong> make mistakes.</div>
<div class="gw-ua">Вона рідко помиляється.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Not only did he</strong> finish early, but he also helped others.</div>
<div class="gw-ua">Він не тільки закінчив рано, але й допоміг іншим.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Hardly had we</strong> arrived when it started raining.</div>
<div class="gw-ua">Ледве ми приїхали, як почався дощ.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'It-cleft речення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Структура <strong>It was/is ... who/that</strong> підсилює певну частину речення:</p>
<p class="mb-2"><strong>It + be + підсилений елемент + who/that + решта</strong></p>
<div class="gw-ex">
<div class="gw-en">You invited me. → <strong>It was you who</strong> invited me.</div>
<div class="gw-ua">Це саме ти запросив мене.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I need help. → <strong>It is help that</strong> I need.</div>
<div class="gw-ua">Саме допомога мені потрібна.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She left yesterday. → <strong>It was yesterday that</strong> she left.</div>
<div class="gw-ua">Саме вчора вона пішла.</div>
</div>
<p class="mt-2 text-slate-600">📌 Використовуй <strong>who</strong> для людей, <strong>that</strong> для решти.</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'What-cleft речення (Pseudo-cleft)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Структура <strong>What ... is/was</strong> підсилює дію або об'єкт:</p>
<p class="mb-2"><strong>What + clause + is/was + підсилений елемент</strong></p>
<div class="gw-ex">
<div class="gw-en">I like the park. → <strong>What I like</strong> is the park.</div>
<div class="gw-ua">Те, що мені подобається — це парк.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I need rest. → <strong>What I need</strong> is some rest.</div>
<div class="gw-ua">Те, що мені потрібно — це відпочинок.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She wants a vacation. → <strong>What she wants</strong> is a vacation.</div>
<div class="gw-ua">Те, чого вона хоче — це відпустка.</div>
</div>
<p class="mt-2 text-slate-600">📌 Можна використовувати також <strong>Where, Why, Who</strong>: <span class="gw-en">Where I grew up is far from here.</span></p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Fronting (винесення на початок)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Fronting</strong> — винесення елемента на початок для акценту:</p>
<div class="gw-ex">
<div class="gw-en">The kids left quickly. → <strong>Quickly</strong> the kids left.</div>
<div class="gw-ua">Швидко діти пішли.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I like this book very much. → <strong>This book</strong> I like very much.</div>
<div class="gw-ua">Цю книгу я дуже люблю.</div>
</div>
<div class="gw-ex">
<div class="gw-en">There goes the bus! → <strong>Away ran</strong> the children.</div>
<div class="gw-ua">Геть побігли діти!</div>
</div>
<p class="mt-2 text-slate-600">📌 Fronting часто використовується в літературі та formal English.</p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Емфатичне do/does/did',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Для підсилення у стверджувальних реченнях використовуй <strong>do/does/did</strong>:</p>
<div class="gw-ex">
<div class="gw-en">I like it. → I <strong>do</strong> like it!</div>
<div class="gw-ua">Мені це справді подобається!</div>
</div>
<div class="gw-ex">
<div class="gw-en">She finished. → She <strong>did</strong> finish her homework!</div>
<div class="gw-ua">Вона таки зробила домашнє завдання!</div>
</div>
<div class="gw-ex">
<div class="gw-en">He knows. → He <strong>does</strong> know the answer.</div>
<div class="gw-ua">Він справді знає відповідь.</div>
</div>
<p class="mt-2 text-slate-600">📌 Вимовляй do/does/did з наголосом!</p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Структури для підсилення',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Структури для підсилення">
<thead>
<tr>
<th>Тип</th>
<th>Структура</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Інверсія</td>
<td>Neg + Aux + S + V</td>
<td><span class="gw-en">Never have I seen...</span></td>
</tr>
<tr>
<td>It-cleft</td>
<td>It + be + X + who/that</td>
<td><span class="gw-en">It was you who...</span></td>
</tr>
<tr>
<td>What-cleft</td>
<td>What + clause + is/was</td>
<td><span class="gw-en">What I need is...</span></td>
</tr>
<tr>
<td>Fronting</td>
<td>Element + S + V</td>
<td><span class="gw-en">Quickly the kids left.</span></td>
</tr>
<tr>
<td>Emphatic do</td>
<td>S + do/does/did + V</td>
<td><span class="gw-en">I do like it!</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Негативні прислівники для інверсії',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Never</strong> — ніколи</li>
<li><strong>Rarely / Seldom</strong> — рідко</li>
<li><strong>Hardly / Scarcely</strong> — ледве</li>
<li><strong>Not only ... but also</strong> — не тільки ... але й</li>
<li><strong>No sooner ... than</strong> — щойно ... як</li>
<li><strong>Little</strong> — мало (у значенні "hardly")</li>
<li><strong>Only when/after/if</strong> — тільки коли/після/якщо</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>Never I have seen...</em> ❌ → <strong>Never have I seen...</strong> (Інверсія обов'язкова!)</li>
<li><span class="tag-warn">✗</span> <em>It was you which invited me.</em> ❌ → <strong>It was you who invited me.</strong> (who для людей)</li>
<li><span class="tag-warn">✗</span> <em>What I need is to rest.</em> ❌ → <strong>What I need is some rest.</strong> (іменник, не інфінітив)</li>
<li><span class="tag-warn">✗</span> <em>He do knows.</em> ❌ → <strong>He does know.</strong> (does + base form)</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підказки для запамʼятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> <strong>Інверсія:</strong> negative adverb → Aux + Subject.</li>
<li><span class="tag-ok">✓</span> <strong>It-cleft:</strong> It + be + що підсилюємо + who/that.</li>
<li><span class="tag-ok">✓</span> <strong>What-cleft:</strong> What + підрядне + is/was + основне.</li>
<li><span class="tag-ok">✓</span> <strong>Emphatic do:</strong> у стверджувальних + наголос.</li>
<li><span class="tag-ok">✓</span> Ці структури — для formal / written English.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
