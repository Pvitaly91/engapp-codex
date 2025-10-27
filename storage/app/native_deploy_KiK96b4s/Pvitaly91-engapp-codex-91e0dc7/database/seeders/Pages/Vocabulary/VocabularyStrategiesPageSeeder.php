<?php

namespace Database\Seeders\Pages\Vocabulary;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class VocabularyStrategiesPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'vocabulary-building';
    }

    protected function page(): array
    {
        return [
            'title' => 'Vocabulary strategies — як розширювати словниковий запас',
            'subtitle_html' => <<<'HTML'
<p>Побудова словникового запасу — це <strong>системна робота</strong>: повторення, контекст і активне використання нових слів.</p>
HTML,
            'subtitle_text' => 'Використовуйте різні методи (контекст, асоціації, повторення), щоб зберігати нові слова в пам’яті.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'vocabulary',
                'title' => 'Словниковий запас',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Працюємо з контекстом',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Завжди записуйте <strong>фразу</strong>, а не окреме слово: <span class="gw-en">make a decision</span>.</li>
<li>Читайте короткі тексти і виписуйте корисні вирази з перекладом.</li>
<li>Слухайте подкасти та відмічайте нову лексику у нотатнику.</li>
<li>Формуйте тематичні набори (travel, work, food) для легшого повторення.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Повторення та закріплення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Використовуйте <strong>spaced repetition</strong> (Anki, Quizlet) — повтори через зростаючі інтервали.</li>
<li>Робіть міні-тести або пари «слово → переклад» раз на тиждень.</li>
<li>Говоріть і пишіть з новими словами протягом 24 годин після вивчення.</li>
<li>Ведіть словник помилок, щоб не повторювати їх.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Словотворення та синоніми',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Додавайте префікси/суфікси: <span class="gw-en">happy → unhappy, happiness</span>.</li>
<li>Знаходьте синоніми та антоніми, щоб варіювати мову: <span class="gw-en">big → huge, enormous; small → tiny</span>.</li>
<li>Вивчайте колокації: <span class="gw-en">strong coffee</span>, <span class="gw-en">heavy rain</span>.</li>
<li>Створюйте ментальні карти (mind maps) для тематичних груп.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Практика у вправах',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Заповнення пропусків (gap-fill) допомагає закріпити колокації.</li>
<li>Використовуйте вправи «match the definition» для перевірки розуміння.</li>
<li>Пишіть короткі історії, використовуючи 5–10 нових слів.</li>
<li>Створюйте власні флеш-карти з прикладами речень.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Мотивація та регулярність',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">✅</div>
<div>
<p>Плануйте мінімум 10 хвилин на день для словника. Менше, але регулярно — ефективніше за довгі, але рідкі заняття.</p>
<p>Відстежуйте прогрес у таблиці та нагороджуйте себе за досягнення.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
