<?php

namespace Database\Seeders\Pages\Translation;

use Database\Seeders\Pages\Concerns\GrammarPageSeeder;

class TranslationTechniquesPageSeeder extends GrammarPageSeeder
{
    protected function slug(): string
    {
        return 'translation-techniques';
    }

    protected function page(): array
    {
        return [
            'title' => 'Translation techniques — як перекладати ефективно',
            'subtitle_html' => <<<'HTML'
<p>Переклад — це не буквально слово в слово, а <strong>передача змісту</strong> з урахуванням граматики та стилю.</p>
HTML,
            'subtitle_text' => 'Переклад вимагає аналізу контексту, граматики і стилю, а не буквальної підстановки слів.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'translation',
                'title' => 'Переклад',
                'language' => 'uk',
            ],
            'blocks' => [
                [
                    'column' => 'left',
                    'heading' => 'Кроки ефективного перекладу',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ol class="gw-list">
<li>Зрозумій контекст: хто говорить, до кого, з якою метою.</li>
<li>Визнач час та граматичні конструкції оригіналу.</li>
<li>Знайди ключові слова та сталi вирази.</li>
<li>Склади чернетку українською, потім відредагуй під стиль.</li>
</ol>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Граматичні нюанси',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Звертай увагу на <strong>час дієслова</strong> та узгодження: <span class="gw-en">He has done</span> → <span class="gw-ua">Він уже зробив</span>.</li>
<li>Зберігай порядок слів, але адаптуй до природної української структури.</li>
<li>Не забувай про артиклі — часто їх потрібно передати займенником або порядком слів.</li>
<li>Перевір прийменники: <span class="gw-en">depend on</span> → <span class="gw-ua">залежати від</span>.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові пастки',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>False friends</strong>: <span class="gw-en">magazine</span> ≠ «магазин», а «журнал».</li>
<li>Ідіоми перекладай сенсом: <span class="gw-en">break the ice</span> → <span class="gw-ua">розрядити атмосферу</span>.</li>
<li>Слідкуй за родом і числом, особливо з колективними іменниками (<span class="gw-en">the police are</span>).</li>
<li>Не забувай про артиклі, які впливають на значення (a teacher vs. the teacher).</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Інструменти та перевірка',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>Використовуй словники Collins, Cambridge для прикладів.</li>
<li>Перевір текст у голос — так легше знайти незручні конструкції.</li>
<li>Застосовуй зворотний переклад: переклади назад і порівняй зі стартом.</li>
<li>Завжди перечитуй текст свіжим поглядом через кілька годин.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Практична порада',
                    'css_class' => null,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">📝</div>
<div>
<p>Роби власну таблицю типових помилок і правильних відповідників. Регулярно доповнюй її під час практики перекладу.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
