<?php

namespace Database\Seeders\Page_v2\QuestionsNegations;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class TypesOfQuestionsCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'types-of-questions';
    }

    protected function cleanupSeederClasses(): array
    {
        return [static::class];
    }

    public function previewCategorySlug(): string
    {
        return $this->slug();
    }

    public function run(): void
    {
        $slug = $this->slug();
        $description = $this->description();

        // Find parent category
        $parentCategory = PageCategory::where('slug', '8')->first();

        $category = PageCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $description['title'],
                'language' => $description['locale'],
                'parent_id' => $parentCategory?->id,
            ]
        );

        // Sync tags if provided
        if (! empty($description['tags'])) {
            $tagIds = [];
            foreach ($description['tags'] as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $tagIds[] = $tag->id;
            }
            $category->tags()->sync($tagIds);
        }

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $locale = $description['locale'];

        if (! empty($description['subtitle_html'])) {
            TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $description['subtitle_html'],
                'seeder' => static::class,
            ]);
        }

        foreach ($description['blocks'] ?? [] as $index => $block) {
            $blockType = $block['type'] ?? 'box';

            TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $block['locale'] ?? $locale,
                'type' => $blockType,
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'seeder' => static::class,
            ]);
        }
    }

    protected function description(): array
    {
        return [
            'title' => 'Types of questions — Види питальних речень',
            'subtitle_html' => '<p><strong>Види питальних речень (Types of questions)</strong> — це важлива частина англійської граматики. У цьому розділі ти вивчиш, як правильно формувати <em>різні типи питань</em>: загальні (Yes/No), спеціальні (Wh-questions), альтернативні, розділові (question tags) та заперечні питання, а також як давати <em>короткі та повні відповіді</em>.</p>',
            'subtitle_text' => 'Види питальних речень в англійській мові: загальні, спеціальні, альтернативні, розділові та заперечні питання, відповіді на питання.',
            'locale' => 'uk',
            'tags' => [
                'Питальні речення',
                'Types of questions',
                'Yes/No questions',
                'Wh-questions',
                'Загальні питання',
                'Спеціальні питання',
                'Альтернативні питання',
                'Question tags',
                'Розділові питання',
                'Заперечні питання',
                'Відповіді на питання',
                'Short answers',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цьому розділі ти опануєш <strong>всі види питальних речень</strong> в англійській мові: від простих Yes/No питань до складних розділових питань.',
                        'rules' => [
                            [
                                'label' => 'Yes/No питання',
                                'color' => 'emerald',
                                'text' => '<strong>Загальні питання</strong> — відповідь так або ні:',
                                'example' => 'Do you like coffee?',
                            ],
                            [
                                'label' => 'Wh-питання',
                                'color' => 'blue',
                                'text' => '<strong>Спеціальні питання</strong> з who, what, where, when, why, how:',
                                'example' => 'Where do you live?',
                            ],
                            [
                                'label' => 'Question tags',
                                'color' => 'violet',
                                'text' => '<strong>Розділові питання</strong> — підтвердження чи заперечення:',
                                'example' => "You're a student, aren't you?",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Yes/No questions — Загальні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Загальні питання</strong> — відповідь "так" або "ні": <span class="gw-en">Do you like pizza?</span></li>
<li><strong>Порядок слів:</strong> допоміжне дієслово + підмет + основне дієслово: <span class="gw-en">Are you ready?</span></li>
<li><strong>З do/does/did:</strong> <span class="gw-en">Do you speak English? Does she work here? Did they call you?</span></li>
<li><strong>З be:</strong> просто міняємо підмет і be місцями: <span class="gw-en">Is he tired? Are you happy?</span></li>
<li><strong>З модальними:</strong> модальне дієслово + підмет: <span class="gw-en">Can you help? Should we go?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Wh-questions — Спеціальні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Спеціальні питання</strong> починаються з питальних слів: <span class="gw-en">What do you want?</span></li>
<li><strong>Who</strong> (хто): <span class="gw-en">Who is calling?</span></li>
<li><strong>What</strong> (що): <span class="gw-en">What are you doing?</span></li>
<li><strong>Where</strong> (де, куди): <span class="gw-en">Where do you live?</span></li>
<li><strong>When</strong> (коли): <span class="gw-en">When does the train arrive?</span></li>
<li><strong>Why</strong> (чому): <span class="gw-en">Why are you late?</span></li>
<li><strong>How</strong> (як): <span class="gw-en">How do you know?</span></li>
<li><strong>Порядок слів:</strong> питальне слово + допоміжне дієслово + підмет + основне дієслово.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Alternative questions — Альтернативні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Альтернативні питання</strong> пропонують вибір між двома варіантами: <span class="gw-en">Do you prefer coffee or tea?</span></li>
<li><strong>Структура:</strong> загальне питання + <strong>or</strong> + альтернатива: <span class="gw-en">Is she a teacher or a doctor?</span></li>
<li><strong>З прикметниками:</strong> <span class="gw-en">Is it big or small?</span></li>
<li><strong>З дієсловами:</strong> <span class="gw-en">Do you walk or drive to work?</span></li>
<li><strong>Відповідь:</strong> вибираємо один з варіантів: <span class="gw-en">Coffee. / I prefer coffee.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Question tags — Розділові питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Розділові питання</strong> — короткі питання в кінці речення: <span class="gw-en">You like coffee, don't you?</span></li>
<li><strong>Правило:</strong> якщо речення позитивне — tag негативний, і навпаки: <span class="gw-en">She is nice, isn't she?</span> <span class="gw-en">They aren't here, are they?</span></li>
<li><strong>З do/does/did:</strong> <span class="gw-en">You know him, don't you? She doesn't work here, does she?</span></li>
<li><strong>З be:</strong> <span class="gw-en">He is happy, isn't he?</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">You can swim, can't you?</span></li>
<li><strong>Інтонація:</strong> падаюча — коли ми впевнені, зростаюча — коли сумніваємося.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Negative questions — Заперечні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Заперечні питання</strong> висловлюють здивування або підтвердження: <span class="gw-en">Don't you know him?</span></li>
<li><strong>Структура:</strong> негативне допоміжне дієслово + підмет + основне дієслово: <span class="gw-en">Didn't she call you?</span></li>
<li><strong>З be:</strong> <span class="gw-en">Isn't it beautiful?</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">Can't you swim?</span></li>
<li><strong>Значення:</strong> часто виражає здивування або очікування певної відповіді: <span class="gw-en">Don't you like pizza? (Я здивований, що ти не любиш піцу)</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Answers to questions — Відповіді на питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Короткі відповіді:</strong> Yes/No + підмет + допоміжне дієслово: <span class="gw-en">Yes, I do. / No, I don't.</span></li>
<li><strong>З be:</strong> <span class="gw-en">Yes, I am. / No, I'm not.</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">Yes, I can. / No, I can't.</span></li>
<li><strong>Повні відповіді:</strong> <span class="gw-en">Yes, I speak English. / No, I don't speak French.</span></li>
<li><strong>Уникай:</strong> <span class="gw-en">Yes, I speak.</span> ✗ — потрібно: <span class="gw-en">Yes, I do.</span> ✓</li>
<li><strong>На Wh-questions:</strong> даємо повну відповідь: <span class="gw-en">Where do you live? — I live in Kyiv.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Види питальних речень">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Yes/No Questions</strong></td>
<td>A1</td>
<td>Загальні питання з відповіддю так/ні</td>
</tr>
<tr>
<td><strong>Wh-questions</strong></td>
<td>A1–A2</td>
<td>Спеціальні питання (who, what, where, when, why, how)</td>
</tr>
<tr>
<td><strong>Alternative Questions</strong></td>
<td>A2</td>
<td>Питання з вибором між варіантами (coffee or tea?)</td>
</tr>
<tr>
<td><strong>Question Tags</strong></td>
<td>B1</td>
<td>Розділові питання (…, don't you? …, isn't it?)</td>
</tr>
<tr>
<td><strong>Negative Questions</strong></td>
<td>B1</td>
<td>Заперечні питання (Don't you know…?)</td>
</tr>
<tr>
<td><strong>Answers to Questions</strong></td>
<td>A1–A2</td>
<td>Короткі й повні відповіді (Yes, I do / No, I don't)</td>
</tr>
</tbody>
</table>
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
<p>Почни з <strong>Yes/No питань</strong> — вони найпростіші й найчастіші в розмовній мові.</p>
<p>Запам'ятай <strong>порядок слів у Wh-questions</strong>: питальне слово → допоміжне дієслово → підмет → основне дієслово.</p>
<p><strong>Question tags</strong> — чудовий спосіб зробити розмову більш природною: <span class="gw-en">It's cold today, isn't it?</span></p>
<p>У <strong>short answers</strong> завжди повторюй допоміжне дієслово з питання: <span class="gw-en">Do you? → Yes, I do.</span></p>
<p><strong>Альтернативні питання</strong> допомагають дати людині вибір: <span class="gw-en">Tea or coffee?</span></p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
