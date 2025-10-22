<?php

namespace Database\Seeders\Pages;

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
            'title' => 'Modal verbs — модальні дієслова',
            'subtitle_html' => <<<'HTML'
<p>Модальні дієслова показують <strong>можливість, обов’язок, дозвіл чи пораду</strong>. Вони не мають повної
дієвідмінювання і завжди стоять перед інфінітивом без <em>to</em> (крім <em>have to</em>, <em>need to</em>).</p>
HTML,
            'subtitle_text' => 'Модальні дієслова передають можливість, обов’язок, дозвіл чи пораду і вживаються перед базовою формою дієслова.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'modal-verbs',
                'title' => 'Модальні дієслова',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основні властивості',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Не додаємо <em>s</em> у третій особі однини: <span class="gw-en">She can swim.</span></li>
<li>Заперечення утворюємо за допомогою <em>not</em>: <span class="gw-en">must not</span>, <span class="gw-en">cannot</span>, <span class="gw-en">shouldn’t</span>.</li>
<li>Питання утворюємо інверсією: <span class="gw-en">Can you help?</span></li>
<li>Після модального йде інфінітив без <em>to</em> (крім <em>have to</em>, <em>need to</em>).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Головні групи',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-chips">
<span class="gw-chip">Ability — can / could</span>
<span class="gw-chip">Permission — may / can</span>
<span class="gw-chip">Possibility — might / could</span>
<span class="gw-chip">Obligation — must / have to</span>
<span class="gw-chip">Advice — should / ought to</span>
<span class="gw-chip">Lack of necessity — needn’t / don’t have to</span>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Can / Could',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Can</strong> — теперішня можливість або дозвіл. <span class="gw-en">I can drive.</span></li>
<li><strong>Could</strong> — минула здібність, ввічливі прохання чи гіпотетичні варіанти. <span class="gw-en">Could you open the window?</span></li>
<li>Умовні речення: <span class="gw-en">If we had time, we could travel more.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">Can I leave early?</div>
<div class="gw-ua">Можна я піду раніше?</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'May / Might',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>May</strong> — формальний дозвіл або ймовірність: <span class="gw-en">You may enter.</span></li>
<li><strong>Might</strong> — слабша ймовірність, припущення: <span class="gw-en">She might be late.</span></li>
<li>Обидва виражають припущення про теперішнє/майбутнє без часових форм.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Must / Have to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Must</strong> — внутрішній або суб’єктивний обов’язок. <span class="gw-en">I must call her.</span></li>
<li><strong>Have to</strong> — зовнішня вимога, правила. <span class="gw-en">You have to wear a uniform.</span></li>
<li>Заперечення різняться: <span class="gw-en">mustn’t</span> = заборонено, тоді як <span class="gw-en">don’t have to</span> = немає потреби.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Should / Ought to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Радимо або висловлюємо моральний обов’язок: <span class="gw-en">You should see a doctor.</span></li>
<li><strong>Ought to</strong> близьке за значенням, але формальніше.</li>
<li>Для припущень з високою впевненістю: <span class="gw-en">She should be home now.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Need / Need to',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Need</strong> як модальне вживається у запереченні та питанні: <span class="gw-en">Need you go?</span></li>
<li><strong>Need to</strong> — звичайне дієслово, що виражає необхідність. <span class="gw-en">I need to finish this today.</span></li>
<li><strong>Needn’t</strong> = немає потреби, на відміну від <em>mustn’t</em>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Швидка таблиця значень',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Значення модальних дієслів">
<thead>
<tr>
<th>Модальне</th>
<th>Значення</th>
<th>Приклад</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>can</strong></td>
<td>Здібність / дозвіл</td>
<td><span class="gw-en">We can stay here.</span></td>
</tr>
<tr>
<td><strong>could</strong></td>
<td>Минулі здібності, ввічлива форма</td>
<td><span class="gw-en">Could you wait a moment?</span></td>
</tr>
<tr>
<td><strong>may</strong></td>
<td>Формальний дозвіл, ймовірність</td>
<td><span class="gw-en">You may begin.</span></td>
</tr>
<tr>
<td><strong>might</strong></td>
<td>Невпевнене припущення</td>
<td><span class="gw-en">It might rain.</span></td>
</tr>
<tr>
<td><strong>must</strong></td>
<td>Сильний обов’язок / висновок</td>
<td><span class="gw-en">You must wear a helmet.</span></td>
</tr>
<tr>
<td><strong>have to</strong></td>
<td>Правила, зовнішній тиск</td>
<td><span class="gw-en">We have to submit the report.</span></td>
</tr>
<tr>
<td><strong>should</strong></td>
<td>Порада, очікуваний результат</td>
<td><span class="gw-en">You should try this.</span></td>
</tr>
<tr>
<td><strong>ought to</strong></td>
<td>Моральний обов’язок</td>
<td><span class="gw-en">We ought to help.</span></td>
</tr>
<tr>
<td><strong>needn’t</strong></td>
<td>Відсутність потреби</td>
<td><span class="gw-en">You needn’t hurry.</span></td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради щодо вживання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p><strong>Must</strong> для логічних висновків у теперішньому: <span class="gw-en">She must be at work.</span></p>
<p><strong>Can’t</strong> / <strong>couldn’t</strong> виражають заперечення припущення: <span class="gw-en">It can’t be true.</span></p>
<p>У минулому використовуйте <em>could have + V3</em>, <em>should have + V3</em> для нереалізованих дій.</p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><span class="tag-warn">✗</span> Додавати <em>s</em> або <em>to</em> після модального: <em>cans</em>, <em>must to</em>.</li>
<li><span class="tag-warn">✗</span> Плутати <em>mustn’t</em> (заборона) з <em>don’t have to</em> (не обов’язково).</li>
<li><span class="tag-ok">✓</span> Обирайте модальне за значенням: дозвіл, обов’язок, ймовірність чи порада.</li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
