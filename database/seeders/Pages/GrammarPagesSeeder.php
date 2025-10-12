<?php

namespace Database\Seeders\Pages;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class GrammarPagesSeeder extends Seeder
{
    protected const PAGES = [
        'future-perfect' => [
            'title' => 'Future Perfect — Майбутній доконаний час',
            'subtitle_html' => 'Використовуємо, щоб показати, що дія буде <strong>завершена до певного моменту в майбутньому</strong>.',
            'subtitle_text' => 'Використовуємо, щоб показати, що дія буде завершена до певного моменту в майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Завершення до дедлайну/події</strong>: «До п’ятниці вже зроблю».</li>
<li><strong>Прогноз про виконання</strong> до конкретного часу/моменту.</li>
<li><strong>У складних реченнях</strong> з <em>by (the time), before, until/till</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">By 6 pm, I <strong>will have finished</strong> the report.</div>
<div class="gw-ua">До 18:00 я <strong>вже закінчу</strong> звіт.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will have</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I <span style="color:#93c5fd">will have</span> <span style="color:#86efac">finished</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) have + V3
She <span style="color:#93c5fd">won’t have</span> <span style="color:#86efac">arrived</span> by noon.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">have</span> + V3?
<span style="color:#93c5fd">Will</span> they <span style="color:#93c5fd">have</span> <span style="color:#86efac">completed</span> it by then?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">by … (by Friday, by 2030)</span>
<span class="gw-chip">by the time …</span>
<span class="gw-chip">before …</span>
<span class="gw-chip">until/till …</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time you come, we <strong>will have prepared</strong> everything.</div>
<div class="gw-ua">До того часу, як ти прийдеш, ми <strong>вже підготуємо</strong> все.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Майбутня точка → до неї дія буде завершена.</strong></p>
<p class="gw-ua">У підрядних часу після <em>when, after, before, by the time, until</em> зазвичай <b>Present Simple</b>, а не <em>will</em>:</p>
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en">I will have finished <u>before you arrive</u>.</div>
<div class="gw-ua">Я закінчу <u>перш ніж ти приїдеш</u> (не “will arrive”).</div>
</div>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Simple та Future Perfect">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Simple</strong></td>
<td>Проста дія в майбутньому</td>
<td>will + V1</td>
<td><span class="gw-en">I will finish tomorrow.</span></td>
</tr>
<tr>
<td><strong>Future Perfect</strong></td>
<td>Дія завершиться <u>до</u> майбутньої точки</td>
<td>will have + V3</td>
<td><span class="gw-en">By tomorrow, I <strong>will have finished</strong>.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Ставити <em>will</em> після сполучників часу: <em>*when you will come</em>. Правильно: <em>when you come</em>.</li>
<li><span class="tag-warn">✗</span> Плутати з <em>Future Continuous</em> (той підкреслює процес у майбутній точці).</li>
<li><span class="tag-ok">✓</span> Думай про дедлайн у майбутньому: «Що <b>буде зроблено</b> до нього?»</li>
</ul>
HTML,
                ],
            ],
        ],
        'past-perfect' => [
            'title' => 'Past Perfect — Минулий доконаний час',
            'subtitle_html' => 'Використовуємо, щоб показати дію, яка сталася <strong>раніше іншої минулої події</strong>.',
            'subtitle_text' => 'Використовуємо, щоб показати дію, яка сталася раніше іншої минулої події.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Подія А</strong> відбулася, а потім сталася <strong>подія Б</strong> (обидві в минулому). Для події А — <em>Past Perfect</em>, для події Б — <em>Past Simple</em>.</li>
<li>Часто з маркерами: <em>before, after, by the time, already, when</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I had finished my homework <u>before</u> my friend called.</div>
<div class="gw-ua">Я закінчив домашнє завдання <u>перед тим</u>, як подзвонив друг.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had</span> + <span style="color:#86efac">V3 (дієслово у 3-й формі / Past Participle)</span>
I had <span style="color:#86efac">seen</span> / She had <span style="color:#86efac">gone</span> / They had <span style="color:#86efac">eaten</span></pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had not</span> (hadn’t) + V3
I hadn’t <span style="color:#86efac">seen</span> that movie before.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + [підмет] + V3?
Had you <span style="color:#86efac">studied</span> before the test?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">before — перед</span>
<span class="gw-chip">after — після</span>
<span class="gw-chip">by the time — до того часу як</span>
<span class="gw-chip">already — вже</span>
<span class="gw-chip">when — коли</span>
<span class="gw-chip">until/till — до (моменту)</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time we started, they <strong>had already prepared</strong> everything.</div>
<div class="gw-ua">До того, як ми почали, вони <strong>вже підготували</strong> все.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>A сталося перед B → A: Past Perfect, B: Past Simple.</strong></p>
<p class="gw-ua">Коли порядок подій і так зрозумілий (через <em>before/after</em>), <em>Past Perfect</em> інколи можна опустити. Але з ним зрозуміліше.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">When I arrived, she <strong>had left</strong>.</div>
<div class="gw-ua">Коли я прийшов, вона <strong>вже пішла</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Past Perfect">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Simple</strong></td>
<td>Звичайна минула дія/факт (B)</td>
<td>V2 (went, saw) / did + V1</td>
<td><span class="gw-en">My friend <strong>called</strong>.</span></td>
</tr>
<tr>
<td><strong>Past Perfect</strong></td>
<td>Раніша минула дія перед іншою (A)</td>
<td>had + V3</td>
<td><span class="gw-en">I <strong>had finished</strong> before he called.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використовувати <em>had + V3</em> без другої минулої події/контексту.</li>
<li><span class="tag-warn">✗</span> Плутати з <em>Present Perfect</em> (це про зв’язок із теперішнім, а не з іншою минулою дією).</li>
<li><span class="tag-ok">✓</span> Думай: “<em>Що сталося раніше?</em>” — туди став <strong>Past Perfect</strong>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'future-simple' => [
            'title' => 'Future Simple — Майбутній простий час',
            'subtitle_html' => 'Використовуємо для <strong>спонтанних рішень, обіцянок, прогнозів</strong> і простих дій у майбутньому.',
            'subtitle_text' => 'Використовуємо для спонтанних рішень, обіцянок, прогнозів і простих дій у майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Спонтанне рішення</strong> у момент розмови: «Я зроблю це зараз».</li>
<li><strong>Обіцянки, пропозиції, відмови</strong>.</li>
<li><strong>Прогнози</strong>, які ґрунтуються на думці (I think, probably, maybe).</li>
</ul>
<div class="gw-ex">
<div class="gw-en">It’s hot. I <strong>will open</strong> the window.</div>
<div class="gw-ua">Жарко. Я <strong>відкрию</strong> вікно.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will</span> + <span style="color:#86efac">V1</span>
I <span style="color:#93c5fd">will</span> <span style="color:#86efac">help</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) + V1
She <span style="color:#93c5fd">won’t</span> <span style="color:#86efac">come</span> today.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + V1?
<span style="color:#93c5fd">Will</span> you <span style="color:#86efac">join</span> us?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">tomorrow</span>
<span class="gw-chip">soon</span>
<span class="gw-chip">next week</span>
<span class="gw-chip">in 2030</span>
<span class="gw-chip">I think / probably / maybe</span>
</div>
<div class="gw-ex">
<div class="gw-en">I think they <strong>will win</strong> the match.</div>
<div class="gw-ua">Я думаю, вони <strong>виграють</strong> матч.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Will</strong> — універсальний для простих майбутніх дій, але:</p>
<ul class="gw-list">
<li>Для <b>запланованих дій</b> частіше вживають <em>be going to</em> або Present Continuous.</li>
<li>Для <b>обіцянок/спонтанних рішень</b> — саме Future Simple.</li>
</ul>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Simple та be going to">
<thead>
<tr>
<th>Форма</th>
<th>Використання</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Simple (will)</strong></td>
<td>Спонтанне рішення, обіцянка</td>
<td><span class="gw-en">I’ll call you tonight.</span></td>
</tr>
<tr>
<td><strong>Be going to</strong></td>
<td>План/наміри (заздалегідь)</td>
<td><span class="gw-en">I’m going to visit grandma tomorrow.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використовувати <em>will</em> для фіксованих розкладів (краще Present Simple).</li>
<li><span class="tag-warn">✗</span> Зловживати <em>will</em> там, де доречніше <em>be going to</em>.</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <strong>will</strong> = рішення/обіцянка прямо зараз.</li>
</ul>
HTML,
                ],
            ],
        ],
        'present-perfect' => [
            'title' => 'Present Perfect — Теперішній доконаний час',
            'subtitle_html' => 'Показує <strong>результат або досвід</strong> до теперішнього моменту. Час важливий зараз; конкретну минулу дату зазвичай <strong>не вказуємо</strong>.',
            'subtitle_text' => 'Показує результат або досвід до теперішнього моменту. Час важливий зараз; конкретну минулу дату зазвичай не вказуємо.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Досвід у житті</strong>: ever, never.</li>
<li><strong>Нещодавно завершено</strong>, ефект помітний зараз: just, already, yet.</li>
<li><strong>Тривалість до тепер</strong>: for, since.</li>
<li>Звіт/результат «на зараз»: «Я вже зробив звіт».</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>have finished</strong> the report.</div>
<div class="gw-ua">Я <strong>вже закінчив</strong> звіт (результат є зараз).</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">have/has</span> + <span style="color:#86efac">V3 (Past Participle)</span>
I/You/We/They <span style="color:#93c5fd">have</span> <span style="color:#86efac">seen</span>.
He/She/It <span style="color:#93c5fd">has</span> <span style="color:#86efac">seen</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + have/has not + V3
She <span style="color:#93c5fd">hasn’t</span> <span style="color:#86efac">visited</span> us since 2022.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Have/Has</span> + [підмет] + V3?
<span style="color:#93c5fd">Have</span> you <span style="color:#86efac">ever been</span> to Rome?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">already</span><span class="gw-chip">yet</span><span class="gw-chip">just</span>
<span class="gw-chip">ever</span><span class="gw-chip">never</span>
<span class="gw-chip">for</span><span class="gw-chip">since</span><span class="gw-chip">so far</span><span class="gw-chip">recently/lately</span>
</div>
<div class="gw-ex">
<div class="gw-en">We <strong>have lived</strong> here <u>since</u> 2020.</div>
<div class="gw-ua">Ми <strong>живемо</strong> тут <u>з</u> 2020 року (і досі).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Present Perfect ≠ конкретний минулий час.</strong> Якщо є «вчора», «у 2019» тощо — це вже <b>Past Simple</b>.</p>
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en"><span class="tag-warn">✗</span> I have finished it yesterday.</div>
<div class="gw-ua">Правильно: <b>I finished it yesterday.</b> (Past Simple)</div>
</div>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Perfect та Past Simple">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Present Perfect</strong></td>
<td>Результат/досвід «на зараз», без конкретної минулої дати</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have lost</strong> my keys.</span> <span class="gw-ua">Я загубив ключі (і досі без них).</span></td>
</tr>
<tr>
<td><strong>Past Simple</strong></td>
<td>Завершена дія в минулому з часом/контекстом</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">I <strong>lost</strong> my keys <u>yesterday</u>.</span> <span class="gw-ua">Учора загубив (факт у минулому).</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Додавати конкретну минулу дату: <em>*I have visited in 2019</em>.</li>
<li><span class="tag-warn">✗</span> Плутати <em>for</em> і <em>since</em>:
<div class="gw-ex" style="margin-top:6px">
<div class="gw-en"><b>for</b> + період: for 3 years; <b>since</b> + точка: since 2020.</div>
</div>
</li>
<li><span class="tag-ok">✓</span> Для 3-ї особи однини — <b>has</b>; іншим — <b>have</b>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'future-continuous' => [
            'title' => 'Future Continuous — Майбутній тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>буде у процесі</strong> в певний момент у майбутньому.',
            'subtitle_text' => 'Показує, що дія буде у процесі в певний момент у майбутньому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Щоб описати дію, яка буде <strong>в процесі</strong> у конкретний майбутній момент.</li>
<li>Для ввічливих запитань про плани.</li>
<li>Для регулярних дій у майбутньому (нейтральний тон).</li>
</ul>
<div class="gw-ex">
<div class="gw-en">This time tomorrow, I <strong>will be travelling</strong>.</div>
<div class="gw-ua">Завтра в цей час я <strong>буду подорожувати</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will be</span> + <span style="color:#86efac">V-ing</span>
I <span style="color:#93c5fd">will be</span> <span style="color:#86efac">working</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) be + V-ing
She <span style="color:#93c5fd">won’t be</span> <span style="color:#86efac">sleeping</span> at 10 pm.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">be</span> + V-ing?
<span style="color:#93c5fd">Will</span> you <span style="color:#93c5fd">be</span> <span style="color:#86efac">using</span> the car tonight?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">at this time tomorrow</span>
<span class="gw-chip">at 8 pm next Friday</span>
<span class="gw-chip">soon</span>
<span class="gw-chip">all day tomorrow</span>
</div>
<div class="gw-ex">
<div class="gw-en">At 9 pm, we <strong>will be watching</strong> a movie.</div>
<div class="gw-ua">О 21:00 ми <strong>будемо дивитися</strong> фільм.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Future Continuous</strong> = дія «у процесі» у конкретний момент у майбутньому.</p>
<p class="gw-ua">Використовуємо для опису ситуації «я буду робити щось у певний час».</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Simple та Future Continuous">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Simple</strong></td>
<td>Факт у майбутньому</td>
<td>will + V1</td>
<td><span class="gw-en">I will work tomorrow.</span></td>
</tr>
<tr>
<td><strong>Future Continuous</strong></td>
<td>Процес у конкретний момент у майбутньому</td>
<td>will be + V-ing</td>
<td><span class="gw-en">I will be working at 10 am tomorrow.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використовувати Future Continuous для простих фактів (там треба Future Simple).</li>
<li><span class="tag-warn">✗</span> Забувати <b>be</b> після will: <em>*I will working</em>.</li>
<li><span class="tag-ok">✓</span> Завжди: <b>will be + V-ing</b>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'present-perfect-continuous' => [
            'title' => 'Present Perfect Continuous — Теперішній доконано-тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>почалась у минулому і триває до тепер</strong> або має <strong>сліди/ефект</strong> зараз. Акцент на <b>тривалості</b>.',
            'subtitle_text' => 'Показує, що дія почалась у минулому і триває до тепер або має сліди/ефект зараз. Акцент на тривалості.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Дія триває донині</strong>: «Я вчуся вже 2 години».</li>
<li><strong>Є сліди зараз</strong> (задишка, бруд, втома): «Вона вся у фарбі — вона фарбувала».</li>
<li><strong>Питання про тривалість</strong>: <em>How long...?</em> з <em>for/since</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>have been studying</strong> for three hours.</div>
<div class="gw-ua">Я <strong>вчуся</strong> вже три години (і досі).</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">have/has been</span> + <span style="color:#86efac">V-ing</span>
I/We/You/They <span style="color:#93c5fd">have been</span> <span style="color:#86efac">working</span>.
He/She/It <span style="color:#93c5fd">has been</span> <span style="color:#86efac">working</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + have/has <b>not</b> been + V-ing
She <span style="color:#93c5fd">hasn’t been</span> <span style="color:#86efac">sleeping</span> well lately.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Have/Has</span> + [підмет] + <span style="color:#93c5fd">been</span> + V-ing?
<span style="color:#93c5fd">Have</span> you <span style="color:#93c5fd">been</span> <span style="color:#86efac">working</span> here long?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">for</span>
<span class="gw-chip">since</span>
<span class="gw-chip">lately / recently</span>
<span class="gw-chip">all day / all morning</span>
<span class="gw-chip">how long</span>
</div>
<div class="gw-ex">
<div class="gw-en">We <strong>have been living</strong> here <u>since</u> 2020.</div>
<div class="gw-ua">Ми <strong>живемо</strong> тут <u>з</u> 2020 року (й досі).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Тривалість важливіша за результат</strong> → Present Perfect Continuous.</p>
<p>Якщо важливий <b>результат «вже зроблено»</b> (без фокусу на процесі) → <b>Present Perfect</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She’s tired because she <strong>has been running</strong>.</div>
<div class="gw-ua">Вона втомлена, бо <strong>бігала</strong> (бачимо сліди дії).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Perfect та Present Perfect Continuous">
<thead>
<tr>
<th>Час</th>
<th>Акцент</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Present Perfect</strong></td>
<td>Результат «вже зроблено»</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have finished</strong> the report.</span></td>
</tr>
<tr>
<td><strong>Present Perfect Continuous</strong></td>
<td>Тривалість/сліди зараз</td>
<td>have/has been + V-ing</td>
<td><span class="gw-en">I <strong>have been working</strong> on the report all day.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Ставити <em>have/has + V3</em> замість <em>have/has been + V-ing</em>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Плутати <b>for</b> (період) і <b>since</b> (точка відліку).</li>
<li><span class="tag-ok">✓</span> Для 3-ї особи однини — <b>has been</b>; для інших — <b>have been</b>.</li>
<li><span class="tag-ok">✓</span> Зі <em>state verbs</em> (know, like) зазвичай не використовуємо форму Continuous.</li>
</ul>
HTML,
                ],
            ],
        ],
        'past-perfect-continuous' => [
            'title' => 'Past Perfect Continuous — Минулий доконано-тривалий час',
            'subtitle_html' => 'Показує, що тривала дія <strong>відбувалася певний час до іншої минулої події/моменту</strong> і часто має сліди/наслідок у той момент.',
            'subtitle_text' => 'Показує, що тривала дія відбувалася певний час до іншої минулої події/моменту і часто має сліди/наслідок у той момент.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Тривалість до B</strong>: дія почалась раніше і тривала до іншої минулої події (B).</li>
<li><strong>Причина стану</strong> у минулому: втомлений, мокрий, брудний тощо на момент B.</li>
<li><strong>Питання “скільки часу?”</strong> до B: <em>for/since, how long</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">He <strong>had been waiting</strong> for two hours <u>before</u> the bus came.</div>
<div class="gw-ua">Він <strong>чекав</strong> дві години, <u>перш ніж</u> автобус приїхав.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">had been</span> + <span style="color:#86efac">V-ing</span>
They <span style="color:#93c5fd">had been</span> <span style="color:#86efac">working</span> all day.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + had not (hadn’t) been + V-ing
She <span style="color:#93c5fd">hadn’t been</span> <span style="color:#86efac">sleeping</span> well for weeks.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + [підмет] + <span style="color:#93c5fd">been</span> + V-ing?
<span style="color:#93c5fd">Had</span> you <span style="color:#93c5fd">been</span> <span style="color:#86efac">studying</span> long before the exam?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">for</span>
<span class="gw-chip">since</span>
<span class="gw-chip">before</span>
<span class="gw-chip">by the time</span>
<span class="gw-chip">until/till</span>
<span class="gw-chip">how long</span>
</div>
<div class="gw-ex">
<div class="gw-en">By the time I arrived, they <strong>had been working</strong> for hours.</div>
<div class="gw-ua">Коли я приїхав, вони <strong>працювали</strong> вже декілька годин.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>A тривало до B</strong>: A — Past Perfect Continuous, B — Past Simple/Continuous.</p>
<p class="gw-ua">Якщо важливий <b>результат</b> до B — використовуй <b>Past Perfect</b>. Якщо важлива <b>тривалість</b> — <b>Past Perfect Continuous</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She was tired because she <strong>had been running</strong>.</div>
<div class="gw-ua">Вона була втомлена, бо <strong>бігла</strong> (тривалість пояснює стан).</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Continuous та Past Perfect Continuous">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Continuous</strong></td>
<td>Дія була у процесі в конкретний момент у минулому (без акценту на «до»)</td>
<td>was/were + V-ing</td>
<td><span class="gw-en">At 6 pm I <strong>was working</strong>.</span></td>
</tr>
<tr>
<td><strong>Past Perfect Continuous</strong></td>
<td>Тривала дія <u>до</u> іншої минулої події/моменту</td>
<td>had been + V-ing</td>
<td><span class="gw-en">I <strong>had been working</strong> for 3 hours before he called.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Ставити <em>had + V3</em> замість <em>had been + V-ing</em>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Використовувати без «точки B» у минулому (потрібен контекст другої події/моменту).</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <strong>had been + V-ing</strong> і зазвичай <em>for/since</em>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'past-simple' => [
            'title' => 'Past Simple — Минулий простий час',
            'subtitle_html' => 'Використовуємо, щоб розповісти про <strong>завершені дії чи факти в минулому</strong> з конкретним часом або контекстом.',
            'subtitle_text' => 'Використовуємо, щоб розповісти про завершені дії чи факти в минулому з конкретним часом або контекстом.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Події, які сталися і закінчились у минулому (yesterday, last week, in 2010).</li>
<li>Послідовність дій у минулому.</li>
<li>Факти чи звички, які більше не актуальні.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">We <strong>moved</strong> to Kyiv in 2019.</div>
<div class="gw-ua">Ми <strong>переїхали</strong> до Києва у 2019.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#86efac">V2</span> (правильні дієслова = +ed; неправильні = 2 форма)
I <span style="color:#86efac">watched</span> / She <span style="color:#86efac">went</span></pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + did not (didn’t) + V1
He <span style="color:#93c5fd">didn’t</span> <span style="color:#86efac">call</span> yesterday.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Did</span> + [підмет] + V1?
<span style="color:#93c5fd">Did</span> you <span style="color:#86efac">enjoy</span> the film?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">yesterday</span>
<span class="gw-chip">last week/month/year</span>
<span class="gw-chip">in 2010</span>
<span class="gw-chip">two days ago</span>
<span class="gw-chip">then</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>visited</strong> us last weekend.</div>
<div class="gw-ua">Вона <strong>відвідала</strong> нас минулих вихідних.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Якщо <strong>є чіткий час у минулому</strong> (yesterday, in 2010) — це <b>Past Simple</b>, а не Present Perfect.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Present Perfect">
<thead>
<tr>
<th>Час</th>
<th>Що виражає</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Simple</strong></td>
<td>Завершена дія з конкретним минулим часом</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">I <strong>visited</strong> Paris in 2020.</span></td>
</tr>
<tr>
<td><strong>Present Perfect</strong></td>
<td>Досвід/результат «до тепер», без вказаного часу</td>
<td>have/has + V3</td>
<td><span class="gw-en">I <strong>have visited</strong> Paris.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використовувати Past Simple без маркерів часу (тоді це плутають із Present Perfect).</li>
<li><span class="tag-warn">✗</span> Забувати форму <b>V2</b> для неправильних дієслів.</li>
<li><span class="tag-ok">✓</span> Пам’ятай: <b>V2</b> — минула форма; для заперечень і питань — <b>did + V1</b>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'present-simple' => [
            'title' => 'Present Simple — Теперішній простий час',
            'subtitle_html' => 'Використовуємо для <strong>фактів, звичок, розкладів</strong> та регулярних дій.',
            'subtitle_text' => 'Використовуємо для фактів, звичок, розкладів та регулярних дій.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Факти</strong>: закони природи, загальні істини.</li>
<li><strong>Звички</strong>: те, що робимо регулярно.</li>
<li><strong>Розклади</strong>: поїзди, уроки, кіносеанси.</li>
<li><strong>Стан</strong> (like, know, want) — не в Continuous.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">The sun <strong>rises</strong> in the east.</div>
<div class="gw-ua">Сонце <strong>сходить</strong> на сході.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + V1 (+s/es для he/she/it)
I <span style="color:#86efac">work</span>.
She <span style="color:#86efac">works</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + do/does not + V1
He <span style="color:#93c5fd">doesn’t</span> <span style="color:#86efac">like</span> coffee.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Do/Does</span> + [підмет] + V1?
<span style="color:#93c5fd">Do</span> you <span style="color:#86efac">play</span> chess?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">always</span>
<span class="gw-chip">usually</span>
<span class="gw-chip">often</span>
<span class="gw-chip">sometimes</span>
<span class="gw-chip">rarely</span>
<span class="gw-chip">never</span>
<span class="gw-chip">every day / week</span>
<span class="gw-chip">on Mondays</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>goes</strong> to the gym every Friday.</div>
<div class="gw-ua">Вона <strong>ходить</strong> у спортзал щоп’ятниці.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Для <strong>he/she/it</strong> додаємо <b>-s/-es</b>: works, watches.</p>
<p>В усіх інших випадках — дієслово у базовій формі.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Simple та Present Continuous">
<thead>
<tr>
<th>Час</th>
<th>Використання</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Present Simple</strong></td>
<td>Факти, звички, розклади</td>
<td>V1 / do/does + V1</td>
<td><span class="gw-en">She <strong>reads</strong> every evening.</span></td>
</tr>
<tr>
<td><strong>Present Continuous</strong></td>
<td>Дія у процесі зараз</td>
<td>am/is/are + V-ing</td>
<td><span class="gw-en">She <strong>is reading</strong> now.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Забувати додати <b>-s/-es</b> для he/she/it.</li>
<li><span class="tag-warn">✗</span> Використовувати Present Simple для дії «зараз» (там треба Present Continuous).</li>
<li><span class="tag-ok">✓</span> Використовуй Present Simple для <strong>звичок, фактів, розкладів</strong>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'future-perfect-continuous' => [
            'title' => 'Future Perfect Continuous — Майбутній доконано-тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>триватиме певний час</strong> і <strong>буде тривати/щойно завершиться</strong> до конкретної точки у майбутньому. Акцент на <b>тривалості</b> до дедлайну.',
            'subtitle_text' => 'Показує, що дія триватиме певний час і буде тривати/щойно завершиться до конкретної точки у майбутньому. Акцент на тривалості до дедлайну.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Скільки часу до майбутньої точки</strong>: до певного моменту дія вже триватиме N часу.</li>
<li><strong>Очікуваний стан/наслідок</strong> у майбутній точці (втома, досвід).</li>
<li>Часто з <em>for/since</em>, <em>by (then)</em>, <em>by the time</em>.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">By June, I <strong>will have been working</strong> here for a year.</div>
<div class="gw-ua">До червня я <strong>працюватиму</strong> тут вже рік.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">will have been</span> + <span style="color:#86efac">V-ing</span>
She <span style="color:#93c5fd">will have been</span> <span style="color:#86efac">studying</span> for hours by noon.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + will not (won’t) have been + V-ing
They <span style="color:#93c5fd">won’t have been</span> <span style="color:#86efac">waiting</span> long by 5 pm.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Will</span> + [підмет] + <span style="color:#93c5fd">have been</span> + V-ing?
<span style="color:#93c5fd">Will</span> you <span style="color:#93c5fd">have been</span> <span style="color:#86efac">working</span> here for a year by May?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">for</span>
<span class="gw-chip">since</span>
<span class="gw-chip">by then</span>
<span class="gw-chip">by the time</span>
<span class="gw-chip">before</span>
</div>
<div class="gw-ex">
<div class="gw-en">By 2030, they <strong>will have been living</strong> abroad for a decade.</div>
<div class="gw-ua">До 2030 року вони <strong>проживатимуть</strong> за кордоном уже десятиліття.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Є майбутня точка часу</strong> → до неї дія вже певний час триває.</p>
<p>Результат «буде зроблено до…» без акценту на процес — це <b>Future Perfect (will have + V3)</b>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">She’ll be exhausted because she <strong>will have been running</strong> all morning.</div>
<div class="gw-ua">Вона буде втомлена, бо <strong>бігатиме</strong> весь ранок.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Future Perfect та Future Perfect Continuous">
<thead>
<tr>
<th>Час</th>
<th>Акцент</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Future Perfect</strong></td>
<td>Результат до майбутньої точки</td>
<td>will have + V3</td>
<td><span class="gw-en">By 6 pm, I <strong>will have finished</strong> the report.</span></td>
</tr>
<tr>
<td><strong>Future Perfect Continuous</strong></td>
<td>Тривалість до майбутньої точки</td>
<td>will have been + V-ing</td>
<td><span class="gw-en">By 6 pm, I <strong>will have been working</strong> on it for 8 hours.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Плутати з Future Perfect: <em>*will have + V3</em> замість <b>will have been + V-ing</b>, коли важлива тривалість.</li>
<li><span class="tag-warn">✗</span> Використовувати без «майбутньої точки» (<em>by/before/by the time</em>), де потрібен контекст.</li>
<li><span class="tag-ok">✓</span> Завжди додавай <b>been</b>: <em>will have been + V-ing</em>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'past-continuous' => [
            'title' => 'Past Continuous — Минулий тривалий час',
            'subtitle_html' => 'Використовуємо, щоб описати дію, яка <strong>була у процесі</strong> в конкретний момент у минулому.',
            'subtitle_text' => 'Використовуємо, щоб описати дію, яка була у процесі в конкретний момент у минулому.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Дія в процесі</strong> у певний момент у минулому: «О 8-й я вечеряв».</li>
<li><strong>Фон для іншої дії</strong> (Past Simple): «Вона читала, коли він зайшов».</li>
<li><strong>Дві тривалі дії</strong>, що відбувались одночасно.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">At 9 pm yesterday, I <strong>was watching</strong> TV.</div>
<div class="gw-ua">Учора о 21:00 я <strong>дивився</strong> телевізор.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + was/were + V-ing
I <span style="color:#93c5fd">was</span> <span style="color:#86efac">reading</span>.
They <span style="color:#93c5fd">were</span> <span style="color:#86efac">playing</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + was/were + not + V-ing
She <span style="color:#93c5fd">wasn’t</span> <span style="color:#86efac">sleeping</span>.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Was/Were</span> + [підмет] + V-ing?
<span style="color:#93c5fd">Were</span> you <span style="color:#86efac">studying</span> at 10 pm?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">while</span>
<span class="gw-chip">when</span>
<span class="gw-chip">at 8 pm yesterday</span>
<span class="gw-chip">all evening</span>
<span class="gw-chip">the whole morning</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>was cooking</strong> while he <strong>was watching</strong> TV.</div>
<div class="gw-ua">Вона <strong>готувала</strong>, поки він <strong>дивився</strong> телевізор.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>Past Continuous</strong> = дія в процесі у конкретний момент у минулому.</p>
<p class="gw-ua">Часто йде разом із <b>Past Simple</b> — фонова дія + коротка подія.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">I <strong>was reading</strong> when he <strong>came</strong>.</div>
<div class="gw-ua">Я <strong>читав</strong>, коли він <strong>прийшов</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Past Simple та Past Continuous">
<thead>
<tr>
<th>Час</th>
<th>Використання</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Past Simple</strong></td>
<td>Коротка завершена дія</td>
<td>V2 / did + V1</td>
<td><span class="gw-en">He <strong>came</strong> at 9.</span></td>
</tr>
<tr>
<td><strong>Past Continuous</strong></td>
<td>Дія у процесі у той момент</td>
<td>was/were + V-ing</td>
<td><span class="gw-en">I <strong>was reading</strong> when he came.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Забувати <b>was/were</b>: <em>*I reading</em>.</li>
<li><span class="tag-warn">✗</span> Використовувати Past Continuous для послідовних дій (там треба Past Simple).</li>
<li><span class="tag-ok">✓</span> Пам’ятай: Past Continuous = фон, Past Simple = головна дія.</li>
</ul>
HTML,
                ],
            ],
        ],
        'present-continuous' => [
            'title' => 'Present Continuous — Теперішній тривалий час',
            'subtitle_html' => 'Показує, що дія <strong>відбувається зараз</strong>, навколо теперішнього моменту або є <strong>тимчасовою</strong>. Також — про <strong>узгоджені майбутні плани</strong>.',
            'subtitle_text' => 'Показує, що дія відбувається зараз, навколо теперішнього моменту або є тимчасовою. Також — про узгоджені майбутні плани.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Зараз/у цей період</strong>: дія відбувається у момент мовлення або близько до нього.</li>
<li><strong>Тимчасові ситуації</strong>, зміни, тренди: «працюю над проєктом цього тижня».</li>
<li><strong>Узгоджені плани</strong> на близьке майбутнє (квитки/домовленості): «Я зустрічаюсь о 7».</li>
</ul>
<div class="gw-ex">
<div class="gw-en">I <strong>am working</strong> now.</div>
<div class="gw-ua">Я <strong>зараз працюю</strong>.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формула',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Ствердження</div>
<pre class="gw-formula">[Підмет] + <span style="color:#93c5fd">am/is/are</span> + <span style="color:#86efac">V-ing</span>
I <span style="color:#93c5fd">am</span> <span style="color:#86efac">reading</span>.
She <span style="color:#93c5fd">is</span> <span style="color:#86efac">studying</span>.
They <span style="color:#93c5fd">are</span> <span style="color:#86efac">playing</span>.</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + am/is/are <b>not</b> + V-ing
He <span style="color:#93c5fd">isn’t</span> <span style="color:#86efac">sleeping</span>.</pre>
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula"><span style="color:#93c5fd">Am/Is/Are</span> + [підмет] + V-ing?
<span style="color:#93c5fd">Are</span> you <span style="color:#86efac">coming</span> tonight?</pre>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">now</span>
<span class="gw-chip">right now</span>
<span class="gw-chip">at the moment</span>
<span class="gw-chip">currently</span>
<span class="gw-chip">these days</span>
<span class="gw-chip">this week/month</span>
</div>
<div class="gw-ex">
<div class="gw-en">She <strong>isn’t watching</strong> TV at the moment.</div>
<div class="gw-ua">Вона <strong>не дивиться</strong> телевізор у цей момент.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p><strong>State verbs</strong> (know, like, love, believe, want тощо) зазвичай <b>не</b> вживаються у Continuous у звичайному значенні.</p>
<p>Для <b>узгоджених планів</b> на близьке майбутнє Present Continuous звучить природніше, ніж <em>will</em>.</p>
</div>
</div>
<div class="gw-ex" style="margin-top:10px">
<div class="gw-en">We <strong>are meeting</strong> at 7 pm.</div>
<div class="gw-ua">Ми <strong>зустрічаємось</strong> о 19:00.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння Present Simple та Present Continuous">
<thead>
<tr>
<th>Час</th>
<th>Використання</th>
<th>Формула</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Present Simple</strong></td>
<td>Звички/факти/розклади</td>
<td>V1 / do/does + V1</td>
<td><span class="gw-en">She <strong>works</strong> from home.</span></td>
</tr>
<tr>
<td><strong>Present Continuous</strong></td>
<td>Дія «зараз» або тимчасова</td>
<td>am/is/are + V-ing</td>
<td><span class="gw-en">She <strong>is working</strong> now.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Пропускати <b>am/is/are</b>: <em>*I working</em>.</li>
<li><span class="tag-warn">✗</span> Вживати Continuous зі <em>state verbs</em> у прямому значенні: <em>*I am knowing</em>.</li>
<li><span class="tag-ok">✓</span> Формула завжди: <b>am/is/are + V-ing</b>. Для планів — додай конкретний час/домовленість.</li>
</ul>
HTML,
                ],
            ],
        ],
        'zero-conditional' => [
            'title' => 'Zero Conditional — загальні факти та рутини',
            'subtitle_html' => 'Використовуємо, коли результат завжди відбувається за певної умови: закони природи, інструкції, правила.',
            'subtitle_text' => 'Використовуємо, коли результат завжди відбувається за певної умови: закони природи, інструкції, правила.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Факти природи</strong>: вода кипить при 100°C.</li>
<li><strong>Загальні правила</strong> та <strong>інструкції</strong>.</li>
<li><strong>Рутинні дії</strong> та звички, що повторюються.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you heat ice, it melts.</div>
<div class="gw-ua">Якщо нагріти лід, він тане.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якщо</span>
<span class="gw-chip">when — коли</span>
<span class="gw-chip">whenever — щоразу як</span>
<span class="gw-chip">as soon as — щойно</span>
<span class="gw-chip">unless — якщо не</span>
<span class="gw-chip">as long as — за умови що</span>
</div>
<div class="gw-ex">
<div class="gw-en">When the sun sets, it gets dark.</div>
<div class="gw-ua">Коли сонце сідає, темніє.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Обидві частини зазвичай у <strong>Present Simple</strong>, бо говоримо про загальну істину.</p>
<p class="gw-ua">If water <u>reaches</u> 0°C, it <u>freezes</u>.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули та варіації',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Present Simple, Present Simple
If plants <span style="color:#86efac">don’t get</span> water, they <span style="color:#86efac">die</span>.</pre>
<div class="gw-code-badge">Імператив у результаті</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">імператив</span>
If the alarm <span style="color:#86efac">rings</span>, <span style="color:#93c5fd">leave</span> the building.</pre>
<div class="gw-code-badge">З теперішнім перфектом</div>
<pre class="gw-formula">If + Present Perfect, Present Simple
If he <span style="color:#86efac">has finished</span>, he <span style="color:#86efac">goes</span> home.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові контексти',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Наукові факти</strong>: If light hits a prism, it splits.</li>
<li><strong>Правила безпеки</strong>: If there is a fire, call 101.</li>
<li><strong>Розклади/інструкції</strong>: If the bus arrives, people queue.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If students hand in homework late, the teacher deducts points.</div>
<div class="gw-ua">Якщо учні здають домашку пізно, учитель знімає бали.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Zero vs First Conditional',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Zero vs First Conditional">
<thead>
<tr>
<th>Zero</th>
<th>First</th>
</tr>
</thead>
<tbody>
<tr>
<td>Факт, що завжди правдивий.</td>
<td>Реальний наслідок у конкретній ситуації.</td>
</tr>
<tr>
<td>Present Simple + Present Simple.</td>
<td>Present Simple + will/can/might + V1.</td>
</tr>
<tr>
<td>Не змінюється від контексту.</td>
<td>Залежить від ймовірності у майбутньому.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використання <em>will</em> у будь-якій частині.</li>
<li><span class="tag-warn">✗</span> Змішування з First Conditional, коли йдеться про разову майбутню подію.</li>
<li><span class="tag-ok">✓</span> Переконайся, що мова про <strong>звичний результат</strong>, а не про прогноз.</li>
</ul>
HTML,
                ],
            ],
        ],
        'first-conditional' => [
            'title' => 'First Conditional — реальні майбутні наслідки',
            'subtitle_html' => <<<'HTML'
Використовуємо, коли умова можлива, а результат — очікуваний у майбутньому. Говоримо про плани,
попередження та обіцянки.
HTML,
            'subtitle_text' => <<<'HTML'
Використовуємо, коли умова можлива, а результат — очікуваний у майбутньому. Говоримо про плани,
      попередження та обіцянки.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Реальні прогнози</strong>: «If it rains, we’ll stay home».</li>
<li><strong>Поради/попередження</strong>: «If you touch that, you’ll get burnt».</li>
<li><strong>Умовні обіцянки</strong> та угоди.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you study tonight, you will pass tomorrow.</div>
<div class="gw-ua">Якщо сьогодні повчишся, завтра складеш.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якщо</span>
<span class="gw-chip">unless — якщо не</span>
<span class="gw-chip">as soon as — щойно</span>
<span class="gw-chip">once — як тільки</span>
<span class="gw-chip">provided (that) — за умови що</span>
<span class="gw-chip">in case — про всяк випадок</span>
</div>
<div class="gw-ex">
<div class="gw-en">Unless you leave now, you will miss the bus.</div>
<div class="gw-ua">Якщо не підеш зараз, пропустиш автобус.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">⏳</div>
<div>
<p>У підрядній частині <strong>не вживаємо will</strong>. Майбутнє передаємо <strong>Present Simple</strong> або Present
Perfect.</p>
<p class="gw-ua">If she <u>finishes</u> early, she <u>will call</u> us.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">will</span> + V1
If the train <span style="color:#86efac">is</span> late, we <span style="color:#93c5fd">will wait</span>.</pre>
<div class="gw-code-badge">Модальні дієслова</div>
<pre class="gw-formula">If + Present Simple, <span style="color:#93c5fd">can/may/might</span> + V1
If you <span style="color:#86efac">need</span> help, I <span style="color:#93c5fd">can send</span> you instructions.</pre>
<div class="gw-code-badge">Imperative + if</div>
<pre class="gw-formula"><span style="color:#93c5fd">Імператив</span> + if + Present Simple
Call me if anything <span style="color:#86efac">changes</span>.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади ситуацій',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Погода</strong>: If it clears up, we’ll go hiking.</li>
<li><strong>Робота/навчання</strong>: If they approve the budget, we’ll start the project.</li>
<li><strong>Повсякденні справи</strong>: If I’m free tonight, I’ll join you.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If you don’t water the plant, it will dry out.</div>
<div class="gw-ua">Якщо не поливатимеш рослину, вона всохне.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння з іншими типами',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порівняння First Conditional">
<thead>
<tr>
<th>Тип</th>
<th>Ключова ідея</th>
<th>Формула</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Zero</strong></td>
<td>Загальні факти, рутини.</td>
<td>If + Present, Present.</td>
</tr>
<tr>
<td><strong>First</strong></td>
<td>Реальна майбутня ситуація.</td>
<td>If + Present, will/can/might + V1.</td>
</tr>
<tr>
<td><strong>Second</strong></td>
<td>Уявна/малоймовірна ситуація.</td>
<td>If + Past, would + V1.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використання <em>will</em> у підрядній частині.</li>
<li><span class="tag-warn">✗</span> Застосування Past Simple замість Present Simple.</li>
<li><span class="tag-ok">✓</span> Для наказів використовуйте імператив: <em>Call me if...</em></li>
</ul>
HTML,
                ],
            ],
        ],
        'second-conditional' => [
            'title' => 'Second Conditional — уявні або малоймовірні ситуації',
            'subtitle_html' => <<<'HTML'
Використовуємо, щоб говорити про гіпотетичні події в теперішньому чи майбутньому. Часто
описуємо мрії, поради або наслідки, які малоймовірні.
HTML,
            'subtitle_text' => <<<'HTML'
Використовуємо, щоб говорити про гіпотетичні події в теперішньому чи майбутньому. Часто
      описуємо мрії, поради або наслідки, які малоймовірні.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Нереальні теперішні умови</strong>: If I were taller, I’d play basketball.</li>
<li><strong>Малоймовірне майбутнє</strong>: If we won the lottery, we’d travel the world.</li>
<li><strong>Поради</strong> у формі уявних ситуацій: If I were you, I’d call her.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I had more free time, I would learn Italian.</div>
<div class="gw-ua">Якби мав більше вільного часу, вивчив би італійську.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якби</span>
<span class="gw-chip">even if — навіть якби</span>
<span class="gw-chip">supposing — припустимо</span>
<span class="gw-chip">in case — на випадок</span>
<span class="gw-chip">unless — якби не</span>
</div>
<div class="gw-ex">
<div class="gw-en">Even if I had the money, I wouldn’t buy that car.</div>
<div class="gw-ua">Навіть якби в мене були гроші, я б не купив ту машину.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p>Підрядна частина — <strong>Past Simple</strong>, навіть якщо говоримо про теперішнє. У головній —
<strong>would/could/might</strong> + V1.</p>
<p class="gw-ua">If she <u>knew</u> the answer, she <u>would tell</u> us.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">would</span> + V1
If they <span style="color:#86efac">lived</span> closer, they <span style="color:#93c5fd">would visit</span> more often.</pre>
<div class="gw-code-badge">Модальні варіанти</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">could/might</span> + V1
If I <span style="color:#86efac">had</span> a bike, I <span style="color:#93c5fd">could ride</span> to work.</pre>
<div class="gw-code-badge">Inversion</div>
<pre class="gw-formula"><span style="color:#93c5fd">Were</span> + підмет + to + V1, would + V1
Were we <span style="color:#86efac">to find</span> a solution, we <span style="color:#93c5fd">would celebrate</span>.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені сценарії',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Мрії/бажання</strong>: If I were rich, I’d donate to charity.</li>
<li><strong>Поради</strong>: If I were in your shoes, I’d apologize.</li>
<li><strong>Гіпотетичні наслідки</strong>: If he moved abroad, we’d miss him.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If the weather were warmer, we would swim.</div>
<div class="gw-ua">Якби погода була теплішою, ми б поплавали.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння Second vs First',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Second vs First Conditional">
<thead>
<tr>
<th>Second</th>
<th>First</th>
</tr>
</thead>
<tbody>
<tr>
<td>Малоймовірно/уявно.</td>
<td>Реально/ймовірно.</td>
</tr>
<tr>
<td>If + Past, would + V1.</td>
<td>If + Present, will + V1.</td>
</tr>
<tr>
<td>Часто про поради: Were I you...</td>
<td>Часто про плани та обіцянки.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Вживання <em>would</em> в підрядній частині: <em>If I would know...</em></li>
<li><span class="tag-warn">✗</span> Використання <em>was</em> замість <em>were</em> у формальних висловах.</li>
<li><span class="tag-ok">✓</span> Використовуйте <strong>could/might</strong>, щоб показати різні ступені ймовірності.</li>
</ul>
HTML,
                ],
            ],
        ],
        'third-conditional' => [
            'title' => 'Third Conditional — нереальне минуле',
            'subtitle_html' => <<<'HTML'
Описуємо ситуації, які <strong>не відбулися</strong> в минулому, та їх можливі наслідки. Використовуємо для
жалю, аналізу помилок та уявних альтернатив.
HTML,
            'subtitle_text' => <<<'HTML'
Описуємо ситуації, які не відбулися в минулому, та їх можливі наслідки. Використовуємо для
      жалю, аналізу помилок та уявних альтернатив.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Жаль</strong> про минуле: If I had left earlier, I wouldn’t have missed the flight.</li>
<li><strong>Альтернативні сценарії</strong>: If they had studied, they would have passed.</li>
<li><strong>Критика</strong>: If he had listened, we wouldn’t have been late.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If she had called me, I would have helped.</div>
<div class="gw-ua">Якби вона подзвонила, я б допоміг.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якби</span>
<span class="gw-chip">had — у зворотному порядку</span>
<span class="gw-chip">unless — якби не</span>
<span class="gw-chip">provided (that) — за умови що</span>
<span class="gw-chip">but for — якби не (форм.)</span>
</div>
<div class="gw-ex">
<div class="gw-en">But for your help, we would have failed.</div>
<div class="gw-ua">Якби не твоя допомога, ми б провалилися.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Важливо про часи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🕰️</div>
<div>
<p>Підрядна частина — <strong>Past Perfect</strong> (<em>had + V3</em>), головна —
<strong>would/could/might have + V3</strong>.</p>
<p class="gw-ua">If they <u>had left</u> on time, they <u>would have arrived</u> safely.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Формули',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Основна</div>
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">would have</span> + V3
If I <span style="color:#86efac">had seen</span> her, I <span style="color:#93c5fd">would have said</span> hello.</pre>
<div class="gw-code-badge">Модальні варіації</div>
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">could/might have</span> + V3
If he <span style="color:#86efac">had tried</span> harder, he <span style="color:#93c5fd">might have succeeded</span>.</pre>
<div class="gw-code-badge">Inversion</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + підмет + V3, would have + V3
Had we <span style="color:#86efac">known</span>, we <span style="color:#93c5fd">would have acted</span> differently.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Приклади сценаріїв',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Розбір помилок</strong>: If the team had trained more, they would have won.</li>
<li><strong>Пояснення причин</strong>: If she hadn’t forgotten the key, we wouldn’t have waited.</li>
<li><strong>Історичні «що якби»</strong>: If the weather had been better, the battle might have changed.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I had checked the schedule, I wouldn’t have missed the class.</div>
<div class="gw-ua">Якби я перевірив розклад, не пропустив би заняття.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Відмінність від Second Conditional',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Third vs Second Conditional">
<thead>
<tr>
<th>Third</th>
<th>Second</th>
</tr>
</thead>
<tbody>
<tr>
<td>Про минуле, що не сталося.</td>
<td>Про теперішнє/майбутнє уявне.</td>
</tr>
<tr>
<td>If + Past Perfect, would have + V3.</td>
<td>If + Past Simple, would + V1.</td>
</tr>
<tr>
<td>Наслідок також у минулому.</td>
<td>Наслідок у теперішньому/майбутньому.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Плутати порядок: <em>would have had</em> в підрядній частині.</li>
<li><span class="tag-warn">✗</span> Використовувати <em>Past Simple</em> замість <em>Past Perfect</em>.</li>
<li><span class="tag-ok">✓</span> Для меншої впевненості використовуйте <strong>might have/could have</strong>.</li>
</ul>
HTML,
                ],
            ],
        ],
        'mixed-conditional' => [
            'title' => 'Mixed Conditionals — змішані часові комбінації',
            'subtitle_html' => <<<'HTML'
Поєднують частини різних типів, коли час умови та результату відрізняються. Найчастіше — минуле ↔
теперішнє/майбутнє.
HTML,
            'subtitle_text' => <<<'HTML'
Поєднують частини різних типів, коли час умови та результату відрізняються. Найчастіше — минуле ↔
      теперішнє/майбутнє.
HTML,
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Коли вживати?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Минуле → теперішнє</strong>: If I had studied medicine, I would be a doctor now.</li>
<li><strong>Теперішнє → минуле</strong>: If I weren’t afraid of flying, I would have travelled more.</li>
<li><strong>Логічні зв’язки</strong> між різними моментами часу.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If they had left on time, they wouldn’t be stuck in traffic now.</div>
<div class="gw-ua">Якби вони виїхали вчасно, зараз не стояли б у заторі.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Маркери та сполучники',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">if — якби</span>
<span class="gw-chip">had — у формі інверсії</span>
<span class="gw-chip">were — у формальних порадах</span>
<span class="gw-chip">but for — якби не</span>
<span class="gw-chip">assuming — припускаючи</span>
</div>
<div class="gw-ex">
<div class="gw-en">Had you taken the pill, you would feel better now.</div>
<div class="gw-ua">Якби ти прийняв пігулку, зараз почувався б краще.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Структури',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Минуле → теперішній результат</div>
<pre class="gw-formula">If + Past Perfect, <span style="color:#93c5fd">would</span> + V1
If she <span style="color:#86efac">had saved</span> money, she <span style="color:#93c5fd">would own</span> a car now.</pre>
<div class="gw-code-badge">Теперішнє → минулий результат</div>
<pre class="gw-formula">If + Past Simple, <span style="color:#93c5fd">would have</span> + V3
If he <span style="color:#86efac">were</span> more careful, he <span style="color:#93c5fd">wouldn’t have made</span> that mistake.</pre>
<div class="gw-code-badge">Інверсія</div>
<pre class="gw-formula"><span style="color:#93c5fd">Had</span> + підмет + V3, would + V1 / would have + V3
Had we <span style="color:#86efac">taken</span> a taxi, we <span style="color:#93c5fd">would be</span> on time now.</pre>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поширені комбінації',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Type 3 → Type 2</strong>: Минуле впливає на теперішнє.</li>
<li><strong>Type 2 → Type 3</strong>: Теперішній стан вплинув би на минуле.</li>
<li><strong>Інші комбінації</strong>: можливі, якщо логічні.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">If I knew French, I would have understood the guide.</div>
<div class="gw-ua">Якби я знав французьку, то зрозумів би гіда.</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порівняння структур',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Mixed Conditional Patterns">
<thead>
<tr>
<th>Комбінація</th>
<th>Умова</th>
<th>Результат</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td>Type 3 → Type 2</td>
<td>Past Perfect</td>
<td>would + V1</td>
<td class="gw-en">If you <strong>had listened</strong>, you <strong>would know</strong> the answer.</td>
</tr>
<tr>
<td>Type 2 → Type 3</td>
<td>Past Simple</td>
<td>would have + V3</td>
<td class="gw-en">If she <strong>were</strong> more patient, she <strong>would have finished</strong>.</td>
</tr>
<tr>
<td>Modal variation</td>
<td>Past Perfect / Past Simple</td>
<td>could/might + V1 або have + V3</td>
<td class="gw-en">If he <strong>had practised</strong>, he <strong>might be</strong> famous now.</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Використання однакових часів в обох частинах — тоді це вже не mixed.</li>
<li><span class="tag-warn">✗</span> Плутанина з порядком: Past Perfect має бути в умові, якщо говоримо про минуле.</li>
<li><span class="tag-ok">✓</span> Перевір, що час умови і наслідку логічно відповідають сенсу.</li>
</ul>
HTML,
                ],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::PAGES as $slug => $config) {
            $page = Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $config['title'],
                    'text' => $config['subtitle_text'] ?? null,
                ]
            );

            TextBlock::where('page_id', $page->id)
                ->where('seeder', static::class)
                ->delete();

            if (! empty($config['subtitle_html'])) {
                TextBlock::create([
                    'page_id' => $page->id,
                    'locale' => $config['locale'] ?? 'uk',
                    'type' => 'subtitle',
                    'column' => 'header',
                    'heading' => null,
                    'css_class' => null,
                    'sort_order' => 0,
                    'body' => $config['subtitle_html'],
                    'seeder' => static::class,
                ]);
            }

            foreach ($config['blocks'] ?? [] as $index => $block) {
                TextBlock::create([
                    'page_id' => $page->id,
                    'locale' => $config['locale'] ?? 'uk',
                    'type' => 'box',
                    'column' => $block['column'],
                    'heading' => $block['heading'] ?? null,
                    'css_class' => $block['css_class'] ?? null,
                    'sort_order' => $index + 1,
                    'body' => $block['body'] ?? null,
                    'seeder' => static::class,
                ]);
            }
        }
    }
}
