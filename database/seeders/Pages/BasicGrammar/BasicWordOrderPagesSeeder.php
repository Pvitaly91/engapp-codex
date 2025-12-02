<?php

namespace Database\Seeders\Pages\BasicGrammar;

class BasicWordOrderPagesSeeder extends BasicGrammarPageSeeder
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
<p>Англійські речення мають чітку структуру <strong>Підмет – Дієслово – Додаток (S–V–O)</strong>. Дотримуючись цього порядку, ви робите речення зрозумілими і природними.</p>
HTML,
            'subtitle_text' => 'В англійській мові слова стоять у певному порядку: підмет — дієслово — додаток. Дотримуйтесь цієї структури.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'basic-grammar',
                'title' => 'Базова граматика',
                'language' => 'uk',
            ],
            'tags' => [
                'Word Order',
                'Basic Grammar',
                'Sentence Structure',
                'S-V-O',
                'Adverbs of Frequency',
                'Time Adverbials',
                'Place Adverbials',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Основний порядок слів: S–V–O',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>S</strong> — Subject (підмет): хто виконує дію.</li>
<li><strong>V</strong> — Verb (дієслово): що робить підмет.</li>
<li><strong>O</strong> — Object (додаток): на кого/що спрямована дія.</li>
</ul>
<div class="gw-ex">
<div class="gw-en">She reads books.</div>
<div class="gw-ua">Вона читає книжки.</div>
</div>
<div class="gw-ex">
<div class="gw-en">Tom likes coffee.</div>
<div class="gw-ua">Том любить каву.</div>
</div>
<div class="gw-ex">
<div class="gw-en">We watch TV every evening.</div>
<div class="gw-ua">Ми дивимося телевізор щовечора.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Позиція прислівників частотності',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Прислівники частотності (<em>always, usually, often, sometimes, rarely, never</em>) зазвичай стоять <strong>перед основним дієсловом</strong>, але <strong>після дієслова to be</strong>.</p>
<ul class="gw-list">
<li><strong>Перед звичайним дієсловом:</strong> <span class="gw-en">She always drinks tea.</span></li>
<li><strong>Після to be:</strong> <span class="gw-en">He is usually late.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">They often play football.</div>
<div class="gw-ua">Вони часто грають у футбол.</div>
</div>
<div class="gw-ex">
<div class="gw-en">I never eat fast food.</div>
<div class="gw-ua">Я ніколи не їм фастфуд.</div>
</div>
<div class="gw-ex">
<div class="gw-en">She is sometimes tired after work.</div>
<div class="gw-ua">Вона іноді втомлена після роботи.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Позиція обставин часу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Обставини часу (<em>yesterday, every day, at 7 o'clock, last week, tomorrow</em>) зазвичай стоять <strong>на початку або в кінці речення</strong>.</p>
<ul class="gw-list">
<li><strong>У кінці:</strong> <span class="gw-en">I go to school every day.</span></li>
<li><strong>На початку:</strong> <span class="gw-en">Yesterday, I saw a movie.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">We have a meeting at 9 o'clock.</div>
<div class="gw-ua">У нас зустріч о 9 годині.</div>
</div>
<div class="gw-ex">
<div class="gw-en">Last week, they visited their grandparents.</div>
<div class="gw-ua">Минулого тижня вони відвідали бабусю й дідуся.</div>
</div>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Позиція обставин місця',
                    'css_class' => null,
                    'body' => <<<'HTML'
<p>Обставини місця (<em>at school, in the park, at home, in the kitchen</em>) зазвичай стоять <strong>після дієслова або додатка</strong>.</p>
<ul class="gw-list">
<li><span class="gw-en">She works at home.</span></li>
<li><span class="gw-en">We play football in the park.</span></li>
</ul>
<div class="gw-ex">
<div class="gw-en">The children are playing in the garden.</div>
<div class="gw-ua">Діти граються в саду.</div>
</div>
<div class="gw-ex">
<div class="gw-en">He left his keys at the office.</div>
<div class="gw-ua">Він залишив ключі в офісі.</div>
</div>
<p><strong>Порядок:</strong> спочатку місце, потім час: <span class="gw-en">I study at the library every evening.</span></p>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Типові помилки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">⚠️</div>
<div>
<p><strong>Помилка 1:</strong> Неправильний порядок підмета і дієслова.</p>
<p><span class="tag-warn">✗</span> <span class="gw-en">Reads she books.</span></p>
<p><span class="tag-ok">✓</span> <span class="gw-en">She reads books.</span></p>
</div>
</div>
<div class="gw-hint">
<div class="gw-emoji">⚠️</div>
<div>
<p><strong>Помилка 2:</strong> Прислівник частотності після дієслова.</p>
<p><span class="tag-warn">✗</span> <span class="gw-en">She drinks always tea.</span></p>
<p><span class="tag-ok">✓</span> <span class="gw-en">She always drinks tea.</span></p>
</div>
</div>
<div class="gw-hint">
<div class="gw-emoji">⚠️</div>
<div>
<p><strong>Помилка 3:</strong> Обставина часу між дієсловом і додатком.</p>
<p><span class="tag-warn">✗</span> <span class="gw-en">I eat every day breakfast.</span></p>
<p><span class="tag-ok">✓</span> <span class="gw-en">I eat breakfast every day.</span></p>
</div>
</div>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Підсумок',
                    'css_class' => null,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Порядок слів у реченні">
<thead>
<tr>
<th>Елемент</th>
<th>Позиція</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Підмет (S)</strong></td>
<td>На початку речення</td>
</tr>
<tr>
<td><strong>Дієслово (V)</strong></td>
<td>Після підмета</td>
</tr>
<tr>
<td><strong>Додаток (O)</strong></td>
<td>Після дієслова</td>
</tr>
<tr>
<td><strong>Прислівник частотності</strong></td>
<td>Перед основним дієсловом / після to be</td>
</tr>
<tr>
<td><strong>Обставина місця</strong></td>
<td>Після дієслова/додатка</td>
</tr>
<tr>
<td><strong>Обставина часу</strong></td>
<td>На початку або в кінці речення</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">💡</div>
<div>
<p>Запам'ятайте формулу: <strong>S + V + O + Place + Time</strong>.</p>
<p>Приклад: <span class="gw-en">I meet my friends at the café every Saturday.</span></p>
<p>Прислівники частотності стоять перед дієсловом, але після <em>am/is/are/was/were</em>.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
