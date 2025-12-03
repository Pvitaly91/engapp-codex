<?php

namespace Database\Seeders\Pages\BasicGrammar;

class WordOrderAdverbsAdverbialsPageSeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'word-order-adverbs-adverbials';
    }

    protected function page(): array
    {
        return [
            'title' => 'Word Order with Adverbs and Adverbials — Прислівники та обставини',
            'subtitle_html' => <<<'HTML'
<p><strong>Прислівники</strong> (adverbs) та <strong>обставини</strong> (adverbials) мають своє чітке місце в англійському реченні.
Їхнє розташування залежить від типу: <em>частотності, способу дії, місця, часу</em>.</p>
HTML,
            'subtitle_text' => 'Позиція прислівників та обставин в англійських реченнях: правила розташування для різних типів.',
            'locale' => 'uk',
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Adverbs',
                'Adverbials',
                'Manner',
                'Place',
                'Time',
                'Frequency',
                'A2',
                'B1',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Три позиції прислівників',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Прислівники можуть займати <strong>три позиції</strong> в реченні:</p>
<ul class="gw-list">
<li><strong>Front (на початку)</strong> — перед підметом.</li>
<li><strong>Mid (посередині)</strong> — між підметом і дієсловом або після допоміжного.</li>
<li><strong>End (в кінці)</strong> — після дієслова або додатка.</li>
</ul>
<div class="gw-ex">
<div class="gw-en"><u>Yesterday</u>, I met my friend. (Front)</div>
<div class="gw-ua">Вчора я зустрів друга.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She <u>always</u> drinks coffee. (Mid)</div>
<div class="gw-ua">Вона завжди пʼє каву.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He speaks English <u>fluently</u>. (End)</div>
<div class="gw-ua">Він вільно розмовляє англійською.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники частотності (Adverbs of Frequency)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Always, usually, often, sometimes, rarely, never</strong> — стоять у позиції <em>Mid</em>:</p>
<ul class="gw-list">
<li><strong>Перед основним дієсловом:</strong> <span class="gw-en">She <u>always</u> eats breakfast.</span></li>
<li><strong>Після дієслова to be:</strong> <span class="gw-en">He is <u>usually</u> late.</span></li>
<li><strong>Після допоміжного:</strong> <span class="gw-en">I have <u>never</u> been to Paris.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">They <u>often</u> go to the gym.</div>
<div class="gw-ua">Вони часто ходять до спортзалу.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She is <u>rarely</u> angry.</div>
<div class="gw-ua">Вона рідко сердиться.</div>
</div>
<p class="mt-2 text-slate-600">⚠️ <em>Sometimes, usually, often</em> можуть стояти на початку для акценту: <span class="gw-en">Sometimes I walk to work.</span></p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники способу дії (Adverbs of Manner)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Quickly, slowly, carefully, well, badly</strong> — зазвичай стоять у позиції <em>End</em>:</p>
<div class="gw-ex">
<div class="gw-en">She sings <u>beautifully</u>.</div>
<div class="gw-ua">Вона гарно співає.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He finished the test <u>quickly</u>.</div>
<div class="gw-ua">Він швидко закінчив тест.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They work <u>hard</u> every day.</div>
<div class="gw-ua">Вони щодня наполегливо працюють.</div>
</div>
<p class="mt-2 text-slate-600">📌 Прислівник способу дії стоїть <strong>після додатка</strong>, якщо він є.</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Обставини місця (Adverbs of Place)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Here, there, at home, in the park, at school</strong> — стоять у позиції <em>End</em>:</p>
<div class="gw-ex">
<div class="gw-en">She lives <u>here</u>.</div>
<div class="gw-ua">Вона живе тут.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He works <u>at the office</u>.</div>
<div class="gw-ua">Він працює в офісі.</div>
</div>
<div class="gw-ex">
<div class="gw-en">The children play <u>in the park</u>.</div>
<div class="gw-ua">Діти граються в парку.</div>
</div>
<p class="mt-2 text-slate-600">📌 На початку — для акценту або у formal style: <span class="gw-en">Here is your book.</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Обставини часу (Adverbs of Time)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Yesterday, today, tomorrow, last week, every day</strong> — зазвичай <em>End</em> або <em>Front</em>:</p>
<div class="gw-ex">
<div class="gw-en">I will call you <u>tomorrow</u>.</div>
<div class="gw-ua">Я зателефоную тобі завтра.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><u>Yesterday</u>, I saw Tom at the park.</div>
<div class="gw-ua">Вчора я бачив Тома в парку.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She studies English <u>every day</u>.</div>
<div class="gw-ua">Вона вчить англійську щодня.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок: Manner → Place → Time',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<p class="mb-2">Якщо є кілька обставин у кінці речення, порядок такий:</p>
<p class="mb-2"><strong>Manner (як) → Place (де) → Time (коли)</strong></p>
<table class="gw-table" aria-label="Порядок обставин">
<thead>
<tr>
<th>Позиція</th>
<th>Тип</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>Manner</td>
<td><span class="gw-en">hard</span></td>
</tr>
<tr>
<td>2</td>
<td>Place</td>
<td><span class="gw-en">in London</span></td>
</tr>
<tr>
<td>3</td>
<td>Time</td>
<td><span class="gw-en">last year</span></td>
</tr>
</tbody>
</table>
<div class="gw-ex mt-2">
<div class="gw-en">She worked <u>hard</u> <u>in London</u> <u>last year</u>.</div>
<div class="gw-ua">Вона наполегливо працювала в Лондоні минулого року.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Прислівники ступеня (Adverbs of Degree)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2"><strong>Very, quite, really, extremely</strong> — стоять <strong>перед</strong> прикметником або прислівником:</p>
<div class="gw-ex">
<div class="gw-en">She is <u>very</u> smart.</div>
<div class="gw-ua">Вона дуже розумна.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He runs <u>quite</u> fast.</div>
<div class="gw-ua">Він бігає досить швидко.</div>
</div>
<div class="gw-ex">
<div class="gw-en">The movie was <u>extremely</u> boring.</div>
<div class="gw-ua">Фільм був надзвичайно нудним.</div>
</div>
<p class="mt-2 text-slate-600">📌 <em>A lot, much</em> — в кінці: <span class="gw-en">We travel a lot.</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>She speaks fluently English.</em> ❌ → <strong>She speaks English fluently.</strong> (Manner після додатка.)</li>
<li><span class="tag-warn">✗</span> <em>Always I drink coffee.</em> ❌ → <strong>I always drink coffee.</strong> (Frequency — Mid позиція.)</li>
<li><span class="tag-warn">✗</span> <em>He goes often to the gym.</em> ❌ → <strong>He often goes to the gym.</strong> (Frequency перед основним дієсловом.)</li>
<li><span class="tag-warn">✗</span> <em>I yesterday saw him.</em> ❌ → <strong>I saw him yesterday.</strong> або <strong>Yesterday, I saw him.</strong></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підказки для запамʼятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> <strong>Frequency:</strong> Mid (перед дієсловом, після to be).</li>
<li><span class="tag-ok">✓</span> <strong>Manner:</strong> End (після дієслова/додатка).</li>
<li><span class="tag-ok">✓</span> <strong>Place, Time:</strong> End (Place перед Time).</li>
<li><span class="tag-ok">✓</span> <strong>Degree:</strong> перед прикметником/прислівником.</li>
<li><span class="tag-ok">✓</span> <strong>Формула кінця:</strong> Manner → Place → Time.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
