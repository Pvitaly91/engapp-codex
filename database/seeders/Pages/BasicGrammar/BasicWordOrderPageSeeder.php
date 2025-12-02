<?php

namespace Database\Seeders\Pages\BasicGrammar;

class BasicWordOrderPageSeeder extends BasicGrammarPageSeeder
{
    protected function slug(): string
    {
        return 'basic-word-order';
    }

    protected function page(): array
    {
        return [
            'title' => 'Basic Word Order — Порядок слів у ствердженні',
            'subtitle_html' => <<<'HTML'
<p>Правильний <strong>порядок слів</strong> — основа зрозумілої англійської. 
У ствердних реченнях англійська використовує чітку послідовність: <em>підмет → дієслово → додаток</em> (S–V–O).</p>
HTML,
            'subtitle_text' => 'Базовий порядок слів в англійських ствердних реченнях: підмет, дієслово, додаток та розташування обставин.',
            'locale' => 'uk',
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Sentence Structure',
                'Affirmative Sentences',
                'Adverbs of Frequency',
                'Time Adverbials',
                'Place Adverbials',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Базова структура: S–V–O',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>S</strong> (Subject / Підмет) — хто або що виконує дію.</li>
<li><strong>V</strong> (Verb / Дієслово) — дія або стан.</li>
<li><strong>O</strong> (Object / Додаток) — на кого або що спрямована дія.</li>
</ul>
<div class="gw-ex">
<div class="gw-en"><strong>She</strong> (S) <strong>reads</strong> (V) <strong>books</strong> (O).</div>
<div class="gw-ua">Вона читає книжки.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><strong>Tom</strong> (S) <strong>drinks</strong> (V) <strong>coffee</strong> (O) every morning.</div>
<div class="gw-ua">Том п'є каву щоранку.</div>
</div>
<p class="mt-2 text-slate-600">На відміну від української, в англійській не можна змінювати порядок слів довільно.</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники частотності (Adverbs of Frequency)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Прислівники <em>always, usually, often, sometimes, rarely, never</em> ставляться:</p>
<ul class="gw-list">
<li><strong>Перед основним дієсловом:</strong> <span class="gw-en">She <u>always</u> eats breakfast.</span></li>
<li><strong>Після дієслова to be:</strong> <span class="gw-en">He is <u>usually</u> late.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <u>often</u> watch movies on Fridays.</div>
<div class="gw-ua">Я часто дивлюся фільми по п'ятницях.</div>
</div>
<div class="gw-ex">
<div class="gw-en">They are <u>never</u> on time.</div>
<div class="gw-ua">Вони ніколи не приходять вчасно.</div>
</div>
<div class="gw-ex">
<div class="gw-en">We <u>sometimes</u> go to the gym after work.</div>
<div class="gw-ua">Ми іноді ходимо до спортзалу після роботи.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Обставини часу (Time Adverbials)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Обставини часу (<em>yesterday, every day, at 7 o'clock, last week</em>) зазвичай стоять:</p>
<ul class="gw-list">
<li><strong>На початку речення</strong> — для акценту.</li>
<li><strong>В кінці речення</strong> — найчастіше.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I go to school <u>every day</u>.</div>
<div class="gw-ua">Я ходжу до школи щодня.</div>
</div>
<div class="gw-ex">
<div class="gw-en"><u>Yesterday</u>, we visited our grandparents.</div>
<div class="gw-ua">Вчора ми відвідали бабусю й дідуся.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She wakes up <u>at 7 o'clock</u>.</div>
<div class="gw-ua">Вона прокидається о сьомій годині.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Обставини місця (Place Adverbials)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p class="mb-2">Обставини місця (<em>at school, in the park, at home</em>) зазвичай стоять:</p>
<ul class="gw-list">
<li><strong>Після додатка</strong> або <strong>після дієслова</strong>, якщо додатка немає.</li>
<li>Перед обставинами часу, якщо обидва присутні: <strong>Place → Time</strong>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">He works <u>at the office</u>.</div>
<div class="gw-ua">Він працює в офісі.</div>
</div>
<div class="gw-ex">
<div class="gw-en">The children play <u>in the park</u> every afternoon.</div>
<div class="gw-ua">Діти граються в парку щодня після обіду.</div>
</div>
<div class="gw-ex">
<div class="gw-en">We eat dinner <u>at home</u> on Sundays.</div>
<div class="gw-ua">Ми вечеряємо вдома по неділях.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Повна структура речення',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порядок елементів у реченні">
<thead>
<tr>
<th>Позиція</th>
<th>Елемент</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>1</td>
<td>Підмет (Subject)</td>
<td><span class="gw-en">She</span></td>
</tr>
<tr>
<td>2</td>
<td>Прислівник частотності</td>
<td><span class="gw-en">always</span></td>
</tr>
<tr>
<td>3</td>
<td>Дієслово (Verb)</td>
<td><span class="gw-en">reads</span></td>
</tr>
<tr>
<td>4</td>
<td>Додаток (Object)</td>
<td><span class="gw-en">books</span></td>
</tr>
<tr>
<td>5</td>
<td>Місце (Place)</td>
<td><span class="gw-en">at school</span></td>
</tr>
<tr>
<td>6</td>
<td>Час (Time)</td>
<td><span class="gw-en">every day</span></td>
</tr>
</tbody>
</table>
<p class="mt-2"><strong>Повне речення:</strong> <span class="gw-en">She always reads books at school every day.</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <em>Reads she books.</em> ❌ → <strong>She reads books.</strong> (Підмет завжди перший!)</li>
<li><span class="tag-warn">✗</span> <em>She reads always books.</em> ❌ → <strong>She always reads books.</strong> (Прислівник частотності перед основним дієсловом.)</li>
<li><span class="tag-warn">✗</span> <em>I go every day to school.</em> ❌ → <strong>I go to school every day.</strong> (Час зазвичай в кінці.)</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підказки для запамʼятовування',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-ok">✓</span> Запамʼятай формулу: <strong>S + V + O + Place + Time</strong>.</li>
<li><span class="tag-ok">✓</span> Прислівники частотності «вбудовані» між підметом і дієсловом (або після <em>to be</em>).</li>
<li><span class="tag-ok">✓</span> Англійська не любить вільний порядок слів — тримайся структури!</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
