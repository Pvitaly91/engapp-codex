<?php

namespace Database\Seeders\Page_v2\PronounsDemonstratives;

use Database\Seeders\Pages\Concerns\PageCategoryDescriptionSeeder;

class PronounsDemonstrativesCategorySeeder extends PageCategoryDescriptionSeeder
{
    protected function slug(): string
    {
        return '3';
    }

    protected function description(): array
    {
        return [
            'title' => 'Займенники та вказівні слова',
            'subtitle_html' => <<<'HTML'
<p><strong>Займенники та вказівні слова</strong> — це важливий розділ англійської граматики.
Тут ти вивчиш, як правильно використовувати <em>особові займенники (I, you, he, she, it, we, they)</em>, 
<em>присвійні форми (my, mine, your, yours)</em>, <em>зворотні займенники (myself, yourself)</em>, 
а також <em>вказівні слова (this, that, these, those)</em> та інші види займенників.</p>
HTML,
            'subtitle_text' => 'Займенники та вказівні слова в англійській мові: особові, присвійні, зворотні, вказівні, неозначені та відносні займенники.',
            'locale' => 'uk',
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Особові займенники (Personal Pronouns)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Subject pronouns</strong> — займенники-підмети: <span class="gw-en">I, you, he, she, it, we, they</span>.</li>
<li><strong>Object pronouns</strong> — займенники-додатки: <span class="gw-en">me, you, him, her, it, us, them</span>.</li>
<li>Підметові займенники виконують дію: <span class="gw-en">She reads books.</span></li>
<li>Об'єктні займенники отримують дію: <span class="gw-en">He called me.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Присвійні форми (Possessive Forms)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Possessive adjectives</strong> — присвійні прикметники: <span class="gw-en">my, your, his, her, its, our, their</span>.</li>
<li><strong>Possessive pronouns</strong> — присвійні займенники: <span class="gw-en">mine, yours, his, hers, ours, theirs</span>.</li>
<li>Присвійні прикметники стоять перед іменником: <span class="gw-en">This is my book.</span></li>
<li>Присвійні займенники заміняють іменник: <span class="gw-en">This book is mine.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Зворотні займенники (Reflexive Pronouns)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Форми:</strong> <span class="gw-en">myself, yourself, himself, herself, itself, ourselves, yourselves, themselves</span>.</li>
<li>Використовуються, коли підмет і об'єкт — одна й та сама особа: <span class="gw-en">She taught herself Spanish.</span></li>
<li><strong>Emphatic use</strong> — для підсилення: <span class="gw-en">I did it myself.</span></li>
<li><strong>By + reflexive</strong> = самостійно: <span class="gw-en">He lives by himself.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Займенники та вказівні слова">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Personal Pronouns</strong></td>
<td>A1</td>
<td>Особові та об'єктні займенники</td>
</tr>
<tr>
<td><strong>Possessive Forms</strong></td>
<td>A1–A2</td>
<td>Присвійні прикметники та займенники</td>
</tr>
<tr>
<td><strong>Reflexive Pronouns</strong></td>
<td>A2–B1</td>
<td>Зворотні займенники (myself, yourself)</td>
</tr>
<tr>
<td><strong>Demonstrative Pronouns</strong></td>
<td>A1</td>
<td>Вказівні слова (this, that, these, those)</td>
</tr>
<tr>
<td><strong>Indefinite Pronouns</strong></td>
<td>A2–B1</td>
<td>Неозначені займенники (someone, anybody, nothing)</td>
</tr>
<tr>
<td><strong>Relative Pronouns</strong></td>
<td>B1</td>
<td>Відносні займенники (who, which, that, whose)</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Вказівні слова (Demonstratives)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>This / These</strong> — для близьких предметів: <span class="gw-en">This is my pen. These are my books.</span></li>
<li><strong>That / Those</strong> — для віддалених предметів: <span class="gw-en">That is your car. Those are your keys.</span></li>
<li><strong>This/That</strong> — однина, <strong>these/those</strong> — множина.</li>
<li>Можуть бути і прикметниками, і займенниками: <span class="gw-en">I like this book. I like this.</span></li>
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
<p>Почни з <strong>особових займенників</strong> — вони використовуються в кожному реченні.</p>
<p>Запам'ятай різницю між <strong>my (присвійний прикметник)</strong> та <strong>mine (присвійний займенник)</strong>.</p>
<p><strong>Зворотні займенники</strong> завжди закінчуються на <em>-self</em> (однина) або <em>-selves</em> (множина).</p>
<p><strong>This/that</strong> для однини, <strong>these/those</strong> для множини — простий спосіб не помилитись.</p>
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
            'slug' => 'zaimennyky-ta-vkazivni-slova',
            'title' => 'Займенники та вказівні слова',
            'language' => 'uk',
        ];
    }
}
