<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class AdjectivesCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '9';
    }

    protected function description(): array
    {
        return [
            'title' => 'Прикметники та прислівники',
            'subtitle_html' => <<<'HTML'
<p><strong>Прикметники та прислівники</strong> — це важливий розділ англійської граматики.
Тут ти вивчиш, як правильно використовувати <em>прикметники</em> для опису предметів та людей,
<em>прислівники</em> для опису дій, утворювати <em>ступені порівняння</em> (comparative та superlative),
а також розрізняти <em>прикметники порядку</em> та інші типи описових слів.</p>
HTML,
            'subtitle_text' => 'Прикметники та прислівники в англійській мові: використання прикметників, прислівників, ступені порівняння, порядок прикметників.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Прикметники (Adjectives)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Прикметники</strong> описують іменники: <span class="gw-en">a beautiful flower, an interesting book</span>.</li>
<li><strong>Позиція:</strong> перед іменником або після to be: <span class="gw-en">The car is fast.</span></li>
<li><strong>Форма:</strong> не змінюються за числом або родом: <span class="gw-en">one big house, two big houses</span>.</li>
<li><strong>Порядок:</strong> Opinion → Size → Age → Color: <span class="gw-en">a beautiful big old blue car</span> (спрощена послідовність).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники (Adverbs)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Прислівники</strong> описують дієслова, прикметники або інші прислівники: <span class="gw-en">She speaks quickly.</span></li>
<li><strong>Утворення:</strong> часто додаємо -ly до прикметника: <span class="gw-en">slow → slowly, careful → carefully</span>.</li>
<li><strong>Позиція:</strong> зазвичай після дієслова або в кінці речення: <span class="gw-en">He drives carefully.</span></li>
<li><strong>Прислівники частотності</strong> (always, often, never) — перед основним дієсловом: <span class="gw-en">I always study.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Ступені порівняння (Degrees of Comparison)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Comparative</strong> — вищий ступінь для порівняння двох предметів: <span class="gw-en">bigger, more beautiful</span>.</li>
<li><strong>Superlative</strong> — найвищий ступінь для виділення в групі: <span class="gw-en">the biggest, the most beautiful</span>.</li>
<li><strong>Короткі прикметники:</strong> додаємо -er/-est: <span class="gw-en">fast → faster → fastest</span>.</li>
<li><strong>Довгі прикметники:</strong> використовуємо more/most: <span class="gw-en">interesting → more interesting → most interesting</span>.</li>
<li><strong>Неправильні форми:</strong> <span class="gw-en">good → better → best, bad → worse → worst</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Прикметники та прислівники">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Adjectives</strong></td>
<td>A1–A2</td>
<td>Основи використання прикметників</td>
</tr>
<tr>
<td><strong>Adverbs</strong></td>
<td>A2–B1</td>
<td>Прислівники та їх позиція</td>
</tr>
<tr>
<td><strong>Degrees of Comparison</strong></td>
<td>A2–B1</td>
<td>Ступені порівняння прикметників</td>
</tr>
<tr>
<td><strong>Order of Adjectives</strong></td>
<td>B1</td>
<td>Порядок прикметників перед іменником</td>
</tr>
<tr>
<td><strong>Comparative Structures</strong></td>
<td>A2–B1</td>
<td>Конструкції порівняння (as...as, than)</td>
</tr>
<tr>
<td><strong>Adjectives vs Adverbs</strong></td>
<td>A2–B1</td>
<td>Різниця між прикметниками та прислівниками</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Порядок прикметників',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Повний порядок прикметників:</strong> Opinion (думка) → Size (розмір) → Age (вік) → Shape (форма) → Color (колір) → Origin (походження) → Material (матеріал) → Purpose (призначення).</li>
<li>Приклад: <span class="gw-en">a beautiful small old round red Italian wooden dining table</span>.</li>
<li>На практиці рідко використовують більше 2-3 прикметників одночасно.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Почни з <strong>базових прикметників</strong> — вони використовуються постійно для опису предметів.</p>
<p>Запам'ятай: прислівники часто утворюються додаванням <strong>-ly</strong> до прикметника.</p>
<p>Для <strong>ступенів порівняння</strong>: короткі слова (1-2 склади) → -er/-est, довгі → more/most.</p>
<p><strong>Порядок прикметників:</strong> думка → розмір → вік → колір — найчастіша комбінація.</p>
<p>Не забудь про <strong>неправильні форми:</strong> good/better/best, bad/worse/worst, far/further/furthest.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }

    protected function category(): array
    {
        return [
            'slug' => 'prykmetniky-ta-pryslinknyky',
            'title' => 'Прикметники та прислівники',
            'language' => 'uk',
        ];
    }
}
