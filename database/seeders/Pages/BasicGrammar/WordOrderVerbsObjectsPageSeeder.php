<?php

namespace Database\Seeders\Pages\BasicGrammar;

class WordOrderVerbsObjectsPageSeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'word-order-verbs-objects';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order with Verbs and Objects — Допоміжні, модальні, фразові дієслова',
            'subtitle_html' => <<<'HTML'
<p><strong>Порядок слів</strong> з допоміжними, модальними та фразовими дієсловами має свої особливості.
Важливо знати, де розташувати <em>додаток</em> і як поводитися з <em>фразовими дієсловами</em>.</p>
HTML,
            'subtitle_text' => 'Порядок слів з допоміжними, модальними та фразовими дієсловами: розташування додатків та особливості phrasal verbs.',
            'locale' => 'uk',
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Auxiliary Verbs',
                'Modal Verbs',
                'Phrasal Verbs',
                'Objects',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Допоміжні дієслова (Auxiliary Verbs)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Допоміжні дієслова <strong>be, do, have</strong> стоять <strong>перед</strong> основним дієсловом:</p>
<ul class="gw-list">
<li><strong>Subject + Auxiliary + Main Verb + Object</strong></li>
</ul>
<div class="gw-ex">
<div class="gw-en">She <strong>is</strong> reading a book.</div>
<div class="gw-ua">Вона читає книжку.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They <strong>have</strong> finished the project.</div>
<div class="gw-ua">Вони закінчили проєкт.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Do</strong> you understand the question?</div>
<div class="gw-ua">Ти розумієш питання?</div>
</div>
<div class="gw-ex">
<div class="gw-en">He <strong>doesn't</strong> like coffee.</div>
<div class="gw-ua">Він не любить каву.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Модальні дієслова (Modal Verbs)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Модальні дієслова <strong>can, should, must, might, will, would</strong> стоять <strong>перед</strong> основним дієсловом (без to):</p>
<ul class="gw-list">
<li><strong>Subject + Modal + Base Verb + Object</strong></li>
</ul>
<div class="gw-ex">
<div class="gw-en">She <strong>can</strong> speak English.</div>
<div class="gw-ua">Вона вміє розмовляти англійською.</div>
</div>
<div class="gw-ex">
<div class="gw-en">You <strong>should</strong> finish your homework.</div>
<div class="gw-ua">Тобі слід закінчити домашнє завдання.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He <strong>must</strong> call the doctor.</div>
<div class="gw-ua">Він повинен зателефонувати лікарю.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They <strong>might</strong> arrive late.</div>
<div class="gw-ua">Вони можуть запізнитися.</div>
</div>
<p class="mt-2 text-slate-600">📌 У запереченнях: <span class="gw-en">She <strong>cannot (can't)</strong> swim.</span></p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Фразові дієслова — розділювані (Separable)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">У <strong>розділюваних</strong> фразових дієсловах додаток може стояти <strong>між</strong> дієсловом і часткою або <strong>після</strong>:</p>
<div class="gw-ex">
<div class="gw-en"><strong>Turn off</strong> the light. = <strong>Turn</strong> the light <strong>off</strong>.</div>
<div class="gw-ua">Вимкни світло.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Pick up</strong> the package. = <strong>Pick</strong> the package <strong>up</strong>.</div>
<div class="gw-ua">Забери посилку.</div>
</div>
<p class="mt-2 text-rose-600">⚠️ <strong>Якщо додаток — займенник (it, him, her, them),</strong> він <strong>ОБОВ'ЯЗКОВО</strong> стоїть між дієсловом і часткою:</p>
<div class="gw-ex">
<div class="gw-en"><strong>Turn it off.</strong> <span class="text-red-500">(NOT: Turn off it.)</span></div>
<div class="gw-ua">Вимкни його.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Pick them up.</strong> <span class="text-red-500">(NOT: Pick up them.)</span></div>
<div class="gw-ua">Забери їх.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Фразові дієслова — нерозділювані (Inseparable)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">У <strong>нерозділюваних</strong> фразових дієсловах додаток <strong>завжди</strong> стоїть <strong>після</strong> всієї конструкції:</p>
<div class="gw-ex">
<div class="gw-en"><strong>Look after</strong> your dog.</div>
<div class="gw-ua">Доглядай за своїм собакою.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>got over</strong> the illness.</div>
<div class="gw-ua">Вона одужала від хвороби.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I <strong>came across</strong> an old photo.</div>
<div class="gw-ua">Я натрапив на стару фотографію.</div>
</div>
<p class="mt-2 text-slate-600">📌 Трислівні фразові дієслова завжди нерозділювані: <span class="gw-en">put up with, look forward to, run out of</span>.</p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок елементів у реченні',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порядок слів з дієсловами">
<thead>
<tr>
<th>Тип</th>
<th>Структура</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Допоміжне</td>
<td>S + Aux + V + O</td>
<td><span class="gw-en">She is reading a book.</span></td>
</tr>
<tr>
<td>Модальне</td>
<td>S + Modal + V + O</td>
<td><span class="gw-en">He can speak English.</span></td>
</tr>
<tr>
<td>Phrasal (розділ.)</td>
<td>V + O + Particle</td>
<td><span class="gw-en">Turn the light off.</span></td>
</tr>
<tr>
<td>Phrasal (займ.)</td>
<td>V + Pronoun + Particle</td>
<td><span class="gw-en">Turn it off.</span></td>
</tr>
<tr>
<td>Phrasal (нерозд.)</td>
<td>V + Particle + O</td>
<td><span class="gw-en">Look after the kids.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Популярні фразові дієслова',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Розділювані:</strong></p>
<ul class="gw-list">
<li><strong>turn on/off</strong> — вмикати/вимикати</li>
<li><strong>pick up</strong> — підбирати, забирати</li>
<li><strong>put on/off</strong> — надягати / відкладати</li>
<li><strong>take off</strong> — знімати</li>
<li><strong>throw away</strong> — викидати</li>
</ul>
<p class="mb-2 mt-3"><strong>Нерозділювані:</strong></p>
<ul class="gw-list">
<li><strong>look after</strong> — доглядати</li>
<li><strong>look for</strong> — шукати</li>
<li><strong>get over</strong> — одужати, подолати</li>
<li><strong>come across</strong> — натрапити</li>
<li><strong>run into</strong> — зустріти випадково</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>Turn off it.</em> ❌ → <strong>Turn it off.</strong> (Займенник між дієсловом і часткою!)</li>
<li><span class="tag-warn">✗</span> <em>She can to swim.</em> ❌ → <strong>She can swim.</strong> (Модальні без to!)</li>
<li><span class="tag-warn">✗</span> <em>Look your dog after.</em> ❌ → <strong>Look after your dog.</strong> (Нерозділюване!)</li>
<li><span class="tag-warn">✗</span> <em>He must calls.</em> ❌ → <strong>He must call.</strong> (Після модального — base form.)</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підказки для запамʼятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> <strong>Модальні:</strong> завжди + base form (без to, без -s).</li>
<li><span class="tag-ok">✓</span> <strong>Займенник:</strong> у розділюваних phrasal verbs — тільки посередині.</li>
<li><span class="tag-ok">✓</span> <strong>3-слівні phrasal verbs:</strong> завжди нерозділювані.</li>
<li><span class="tag-ok">✓</span> Не всі phrasal verbs розділювані — вивчай їх окремо!</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
