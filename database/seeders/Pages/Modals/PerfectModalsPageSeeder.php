<?php

namespace Database\Seeders\Pages\Modals;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class PerfectModalsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs-perfect-modals';
    }

    protected function page(): array
    {
        return [
            'title' => 'Perfect Modals, Had Better, Be Supposed To',
            'subtitle_html' => <<<'HTML'
<p>Перфектні модальні форми допомагають робити висновки про минуле, говорити про нереалізовані можливості та висловлювати докори. Додатково корисно пам’ятати про конструкції <em>had better</em>, <em>be supposed to</em> та різні вживання дієслова <em>mean</em>.</p>
HTML,
            'subtitle_text' => 'Perfect modals описують минулі висновки, можливості та докори; had better, be supposed to та mean мають власні нюанси вжитку.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => '1. PERFECT MODALS: повна мапа значень (у минулому)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Perfect modals мають форму <strong>modal + have + V3</strong> і виражають висновки, оцінки та гіпотези про минуле, а також нереалізовані можливості та докори.</p>
<table class="gw-table" aria-label="Perfect modals і значення">
<thead>
<tr>
<th>Форма</th>
<th>Значення</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>must have + V3</strong></td>
<td>Майже певний висновок</td>
<td><span class="gw-en">She must have left.</span></td>
</tr>
<tr>
<td><strong>will have + V3</strong></td>
<td>Ймовірно / напевно (часто в BrE)</td>
<td><span class="gw-en">You’ll have heard the news by now.</span></td>
</tr>
<tr>
<td><strong>should have + V3</strong></td>
<td>Очікування або докір</td>
<td><span class="gw-en">They should have arrived by now.</span> / <span class="gw-en">You should have told me.</span></td>
</tr>
<tr>
<td><strong>may / might / could have + V3</strong></td>
<td>Можливість різного ступеня</td>
<td><span class="gw-en">He might have missed the bus.</span></td>
</tr>
<tr>
<td><strong>can’t / couldn’t have + V3</strong></td>
<td>Неможливий або майже неможливий висновок</td>
<td><span class="gw-en">She can’t have eaten it.</span></td>
</tr>
<tr>
<td><strong>ought to have + V3</strong></td>
<td>= <em>should have</em>, трохи формальніше</td>
<td><span class="gw-en">You ought to have called.</span></td>
</tr>
<tr>
<td><strong>would have + V3</strong></td>
<td>Нереалізована умова (3-й conditional)</td>
<td><span class="gw-en">I would have gone if I had known.</span></td>
</tr>
</tbody>
</table>
<div class="gw-hint">
<div class="gw-emoji">⚠️</div>
<div>
<p>Для заперечних висновків частіше використовуємо <strong>can’t / couldn’t</strong>, а не <strong>mustn’t</strong>: <span class="gw-en">He can’t have seen us.</span></p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => '1.2. «Міс-нюанси», які плутають',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>needn’t have + V3</strong> — дія відбулася даремно: <span class="gw-en">You needn’t have bought so much.</span></li>
<li><strong>didn’t need to + base</strong> — не було потреби (і, ймовірно, не робили): <span class="gw-en">We didn’t need to buy any.</span></li>
<li><strong>could have + V3</strong>:
    <ul>
        <li>Нереалізована можливість: <span class="gw-en">You could have called.</span></li>
        <li>Минула можливість / гіпотеза: <span class="gw-en">It could have rained last night.</span></li>
        <li>М’який докір: <span class="gw-en">You could at least have texted.</span></li>
    </ul>
</li>
<li><strong>couldn’t have + V3</strong>:
    <ul>
        <li>Неможливий висновок: <span class="gw-en">He couldn’t have been there.</span></li>
        <li>Умовна нездатність: <span class="gw-en">I couldn’t have done it without you.</span></li>
    </ul>
</li>
<li><strong>shouldn’t have + V3</strong> — даремно зробив: <span class="gw-en">You shouldn’t have spent so much.</span></li>
<li><strong>might as well + base</strong> — «мабуть, варто»: <span class="gw-en">We might as well leave.</span></li>
<li><strong>might as well have + V3</strong> — іронія через поганий результат: <span class="gw-en">You were so late, we might as well have stayed home.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => '1.3. Форми, про які часто забувають, та 1.4. Ступінь впевненості',
                    'css_class' => null,
                    'body' => <<<'HTML'
<h4>Додаткові форми</h4>
<ul class="gw-list">
<li><strong>Perfect Continuous</strong>: <em>must / might / could / should have been V‑ing</em> — акцент на процесі: <span class="gw-en">She must have been sleeping when I called.</span></li>
<li><strong>Passive</strong>: <em>must / might / should have been done</em>: <span class="gw-en">The documents must have been sent yesterday.</span></li>
<li><strong>Питання</strong>: <span class="gw-en">Could he have left?</span> / <span class="gw-en">Should I have told her?</span> — нормальні, хоч і рідші.</li>
</ul>
<h4>Ступінь впевненості (про минуле)</h4>
<p><strong>will have / must have</strong> (висока) → <strong>should have</strong> (очікування) → <strong>may / might / could have</strong> (50/50) → <strong>can't / couldn't have</strong> (майже 0).</p>
<table class="gw-table" aria-label="Ступінь впевненості perfect modals">
<thead>
<tr>
<th>Рівень впевненості</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>will have / must have</strong> — висока впевненість</td>
<td><span class="gw-en">She must have got my message.</span></td>
</tr>
<tr>
<td><strong>should have</strong> — очікування</td>
<td><span class="gw-en">They should have arrived by now.</span></td>
</tr>
<tr>
<td><strong>may / might / could have</strong> — 50/50</td>
<td><span class="gw-en">He may have left early.</span></td>
</tr>
<tr>
<td><strong>can’t / couldn’t have</strong> — майже неможливо</td>
<td><span class="gw-en">It can’t have been her.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Швидка пам’ятка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">📌</div>
<div>
<p><strong>Perfect modals</strong> = modal + <em>have + V3</em> → висновки про минуле, нереалізовані можливості, докори.</p>
<p><strong>Had better</strong> → сильна порада з попередженням (теперішнє/майбутнє).</p>
<p><strong>Be supposed to</strong> → правило, очікування, план або чутка; <em>was supposed to (have V3)</em> — очікування в минулому, яке часто не справдилося.</p>
<p><strong>Mean</strong> → <em>mean + -ing</em> (наслідок), <em>mean to do</em> (намір), <em>be meant to</em> (очікується).</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '2. HAD BETTER: кілька критичних деталей',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Форма</strong>: <em>'d better + base</em>, <em>'d better not + base</em> (без <em>to</em>): <span class="gw-en">You'd better leave.</span> / <span class="gw-en">You'd better not be late.</span></li>
<li>Значення: сильна порада з попередженням (негативні наслідки, якщо ні).</li>
<li><strong>Час</strong>: про зараз/майбутнє; для минулого не вживаємо (<em>You'd better have…</em> — рідкісна спец-структура з відтінком вимоги, але не для базового вжитку).</li>
<li><strong>Порівняння</strong>: <em>should</em> = м'якше; <em>had better</em> = «краще б негайно».</li>
<li><strong>Питання</strong> — неприродні; зазвичай твердження/заперечення.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '3. BE SUPPOSED TO: повний спектр',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Обов’язок / правило / очікування: <span class="gw-en">You’re supposed to wear a uniform.</span></li>
<li>План або розклад: <span class="gw-en">The train is supposed to arrive at 6.</span></li>
<li>Чутки / загальна думка: <span class="gw-en">She is supposed to be rich.</span></li>
<li>Минуле нездійснене: <span class="gw-en">I was supposed to call her yesterday.</span></li>
<li>Очікування до ще більш раннього моменту: <span class="gw-en">She was supposed to have arrived by 7.</span></li>
<li>Заперечення = заборона: <span class="gw-en">You’re not supposed to park here.</span></li>
<li>Питання: <span class="gw-en">Are we supposed to bring ID?</span></li>
<li>Не плутати з прислівником <strong>supposedly</strong>: <span class="gw-en">She is supposedly rich.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '4. MEAN / MEANT: добудовуємо систему',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>mean + -ing</strong> — мати наслідком/означати (як результат): <span class="gw-en">Missing the bus means being late.</span> / <span class="gw-en">It meant losing everything.</span></li>
<li><strong>mean + to + V</strong> — мати намір: <span class="gw-en">I meant to call you.</span></li>
<li><strong>be meant to + V</strong> ≈ <em>be supposed to</em> / призначено/очікується: <span class="gw-en">This course is meant to help beginners.</span></li>
<li><strong>mean for sb to + V</strong>: <span class="gw-en">I didn’t mean for you to see that.</span></li>
<li><strong>mean + (that) clause</strong>: <span class="gw-en">This means (that) we'll need more time.</span></li>
<li><strong>Форми</strong>: <span class="gw-en">mean – meant – meant</span> (вимова <span class="gw-ua">/ment/</span> у 2-3 формах).</li>
<li><strong>Ідіома</strong>: <span class="gw-en">mean well</span> — «мати добрі наміри»: <span class="gw-en">He meant well, but…</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '5. Додатково до «перфектного інфінітива» (без модалів)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>seem / appear to have + V3</strong>: <span class="gw-en">He seems to have left.</span></li>
<li><strong>be likely / unlikely to have + V3</strong>: <span class="gw-en">They’re likely to have finished by now.</span></li>
<li><strong>was / were to have + V3</strong> (дуже формально): «мали (за планом) зробити, але…» <span class="gw-en">We were to have met at noon, but it was cancelled.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '6. Типові помилки → як виправити',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> <span class="gw-en">He mustn’t have seen us.</span> → <span class="tag-ok">✓</span> <span class="gw-en">He can’t have seen us.</span></li>
<li><span class="tag-warn">✗</span> <span class="gw-en">We didn’t need buy so much.</span> → <span class="tag-ok">✓</span> <span class="gw-en">We didn’t need to buy so much.</span></li>
<li><span class="tag-warn">✗</span> <span class="gw-en">You shouldn’t have to tell me earlier.</span> → <span class="tag-ok">✓</span> <span class="gw-en">You should have told me earlier.</span></li>
<li><span class="tag-warn">✗</span> <span class="gw-en">He might have to leave already.</span> (про минуле) → <span class="tag-ok">✓</span> <span class="gw-en">He might have left already.</span></li>
<li><span class="tag-warn">✗</span> <span class="gw-en">You had better to go.</span> → <span class="tag-ok">✓</span> <span class="gw-en">You’d better go.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => '7. Міні-вправи (з відповідями)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<h4>А. Вибери правильний варіант</h4>
<ol class="gw-list">
<li>I’m not sure where Anna is — she <strong>may have</strong> left early.</li>
<li>They <strong>should have</strong> arrived by now — the flight landed at 3.</li>
<li>You <strong>needn’t have</strong> brought dessert — we already had plenty.</li>
<li>He <strong>must have</strong> been sleeping when you called — he never answers at 2 a.m.</li>
<li>You <strong>’d better not</strong> be late again.</li>
</ol>
<h4>В. Переформулюй, зберігши зміст</h4>
<ol class="gw-list" start="6">
<li>Perhaps she finished the report. → She <strong>might / may have</strong> finished the report.</li>
<li>I’m certain he didn’t see us. → He <strong>can’t / couldn’t have</strong> seen us.</li>
<li>It was unnecessary, but he paid for the taxi. → He <strong>needn’t have</strong> paid for the taxi.</li>
<li>It was a mistake that you told her. → You <strong>shouldn’t have</strong> told her.</li>
<li>According to the schedule, the concert starts at 7. → The concert <strong>is supposed to</strong> start at 7.</li>
</ol>
<h4>С. Переклади коротко</h4>
<ol class="gw-list" start="11">
<li>He <strong>must have</strong> eaten already. (BrE також: He’ll have eaten already.)</li>
<li>You <strong>’d better not</strong> argue with the teacher.</li>
<li>She <strong>is supposed to have</strong> arrived already.</li>
<li>I <strong>couldn’t have</strong> done it without you.</li>
</ol>
HTML,
                ],
            ],
        ];
    }
}
