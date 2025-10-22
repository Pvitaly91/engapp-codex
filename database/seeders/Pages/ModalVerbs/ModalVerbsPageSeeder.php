<?php

namespace Database\Seeders\Pages\ModalVerbs;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class ModalVerbsPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'modal-verbs';
    }

    protected function page(): array
    {
        return [
            'title' => 'Modal Verbs — як і коли вживати модальні дієслова',
            'subtitle_html' => 'Коротко про <strong>can, could, may, might, must, have to, should, ought to, will, would</strong> — як вони передають можливість, необхідність, ймовірність та ввічливість.',
            'subtitle_text' => 'Коротко про can, could, may, might, must, have to, should, ought to, will, would — як вони передають можливість, необхідність, ймовірність та ввічливість.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Що таке модальні дієслова?',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Допоміжні дієслова, які <strong>додають відтінки значення</strong> (можливість, дозвіл, обов’язок, ймовірність).</li>
<li>Не мають закінчення <em>-s</em> у 3-й особі однини: <span class="gw-en">She <strong>can</strong> swim.</span></li>
<li>Після модального дієслова йде <strong>інфінітив без to</strong> (крім <em>ought to</em>, <em>have to</em>, <em>be able to</em>).</li>
<li>Не потребують <em>do/does/did</em> у питаннях та запереченнях (крім перефразованих форм <em>have to</em>, <em>be able to</em>).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Основні модальні дієслова',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">can / could</span>
<span class="gw-chip">may / might</span>
<span class="gw-chip">must / have to</span>
<span class="gw-chip">should / ought to</span>
<span class="gw-chip">will / would</span>
<span class="gw-chip">shall (формально)</span>
</div>
<p class="gw-ua">Ці слова не змінюються за особами, але можуть мати <strong>еквіваленти</strong> для минулого чи майбутнього часу (наприклад, <em>was able to</em>, <em>had to</em>, <em>will have to</em>).</p>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові значення',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Значення модальних дієслів">
<thead>
<tr>
<th>Значення</th>
<th>Модальні дієслова</th>
<th>Приклади</th>
</tr>
</thead>
<tbody>
<tr>
<td>Здібність / можливість</td>
<td><strong>can</strong>, <strong>could</strong>, be able to</td>
<td><span class="gw-en">I <strong>can</strong> play the piano.</span></td>
</tr>
<tr>
<td>Дозвіл / заборона</td>
<td><strong>can</strong>, <strong>may</strong>, <strong>could</strong>, mustn’t, can’t</td>
<td><span class="gw-en">You <strong>may</strong> leave now.</span></td>
</tr>
<tr>
<td>Порада</td>
<td><strong>should</strong>, <strong>ought to</strong>, had better</td>
<td><span class="gw-en">You <strong>should</strong> see a doctor.</span></td>
</tr>
<tr>
<td>Обов’язок / необхідність</td>
<td><strong>must</strong>, <strong>have to</strong>, need to</td>
<td><span class="gw-en">We <strong>have to</strong> wear uniforms.</span></td>
</tr>
<tr>
<td>Ймовірність / припущення</td>
<td><strong>must</strong>, <strong>might</strong>, <strong>could</strong>, <strong>can’t</strong></td>
<td><span class="gw-en">She <strong>must</strong> be home already.</span></td>
</tr>
<tr>
<td>Ввічливі прохання</td>
<td><strong>could</strong>, <strong>would</strong>, <strong>may</strong></td>
<td><span class="gw-en"><strong>Could</strong> you open the window?</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Побудова запитань і заперечень',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-code-badge">Питання</div>
<pre class="gw-formula">[Modal] + [підмет] + V1?
<strong>Can</strong> you help me?</pre>
<div class="gw-code-badge">Заперечення</div>
<pre class="gw-formula">[Підмет] + [Modal] + <span style="color:#f87171">not</span> + V1
We <strong>shouldn’t</strong> be late.</pre>
<p class="gw-ua">Для <em>have to</em> / <em>be able to</em> використовуємо допоміжне <em>do/does/did</em>: <span class="gw-en">Do you have to go?</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Ймовірність та припущення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Must</strong> — висока впевненість: <span class="gw-en">They <strong>must</strong> be at work.</span></li>
<li><strong>Might/Could/May</strong> — нейтральна можливість: <span class="gw-en">He <strong>might</strong> call later.</span></li>
<li><strong>Can’t/Couldn’t</strong> — впевнена відмова: <span class="gw-en">She <strong>can’t</strong> be serious.</span></li>
<li>У минулому використовуємо <strong>must have V3</strong>, <strong>might have V3</strong>, <strong>can’t have V3</strong>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Дозвіл та ввічливість',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🙏</div>
<div>
<p><strong>Can</strong> — неформально, <strong>May</strong> — офіційно, <strong>Could</strong> / <strong>Would</strong> — ввічливо.</p>
<p class="gw-en"><strong>Could</strong> I borrow your notes?</p>
<p class="gw-ua">У відповідях: <em>Yes, you can/may.</em> • <em>No, you can’t/may not.</em></p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради, обов’язок, заборона',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Should / Ought to</strong> — м’яка порада: <span class="gw-en">You <strong>should</strong> try meditation.</span></li>
<li><strong>Must</strong> — внутрішній обов’язок або правила: <span class="gw-en">Students <strong>must</strong> wear ID.</span></li>
<li><strong>Have to</strong> — зовнішня вимога: <span class="gw-en">I <strong>have to</strong> finish this report.</span></li>
<li><strong>Mustn’t</strong> — сувора заборона; <strong>don’t have to</strong> означає «немає потреби».</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Модальні дієслова в минулому',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Could</strong> / <strong>was able to</strong> — загальна або разова здібність.</li>
<li><strong>Had to</strong> — минулий обов’язок, <strong>didn’t have to</strong> — «не було потреби».</li>
<li><strong>Should have V3</strong> / <strong>ought to have V3</strong> — шкодуємо про те, що не зробили.</li>
<li><strong>Might have V3</strong>, <strong>could have V3</strong> — припущення про минуле; <strong>must have V3</strong> — впевнене припущення.</li>
</ul>
<p class="gw-ua">Такі конструкції допомагають описувати досвід і робити висновки про минулі ситуації.</p>
HTML,
                ],
            ],
        ];
    }
}
