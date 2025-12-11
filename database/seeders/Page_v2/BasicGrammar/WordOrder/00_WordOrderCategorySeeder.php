<?php

namespace Database\Seeders\Page_v2\BasicGrammar\WordOrder;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class WordOrderCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'word-order';
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
        $parentCategory = PageCategory::where('slug', 'basic-grammar')->first();

        $category = PageCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $description['title'],
                'language' => $description['locale'],
                'type' => 'theory',
                'parent_id' => $parentCategory?->id,
                'seeder' => static::class,
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
                'uuid' => $this->makeTextBlockUuid($slug, 0, ['type' => 'subtitle']),
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
                'uuid' => $this->makeTextBlockUuid($slug, $index + 1, $block),
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
            'title' => 'Word Order — Порядок слів',
            'subtitle_html' => '<p><strong>Порядок слів (Word Order)</strong> — це один із найважливіших аспектів англійської граматики. На відміну від української мови, де порядок слів відносно вільний, англійська вимагає чіткої послідовності слів у реченні для передачі правильного значення.</p>',
            'subtitle_text' => 'Порядок слів в англійській мові: базова структура речення, питання, заперечення, прислівники та просунуті структури.',
            'locale' => 'uk',
            'tags' => [
                'Порядок слів',
                'Word Order',
                'Базова граматика',
                'Структура речення',
                'S-V-O',
                'Питання',
                'Заперечення',
                'Прислівники',
                'Інверсія',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–B2',
                        'intro' => 'У цьому розділі ти опануєш <strong>порядок слів</strong> в англійській мові: від базової структури S–V–O до інверсії та підсилення.',
                        'rules' => [
                            [
                                'label' => 'Базова структура',
                                'color' => 'emerald',
                                'text' => '<strong>Subject + Verb + Object</strong> — основа англійського речення:',
                                'example' => 'She reads books.',
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'text' => 'Допоміжне дієслово <strong>перед</strong> підметом:',
                                'example' => 'Do you like pizza?',
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'text' => 'Додаємо <strong>not</strong> після допоміжного дієслова:',
                                'example' => "I don't like apples.",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Базовий порядок слів',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>S–V–O</strong> — підмет, дієслово, додаток: <span class="gw-en">She reads books.</span></li>
<li><strong>Прислівники частотності</strong> перед основним дієсловом: <span class="gw-en">I always drink coffee.</span></li>
<li><strong>Обставини місця та часу</strong> в кінці (Place → Time): <span class="gw-en">She works at home every day.</span></li>
<li><strong>Формула:</strong> S + (Adv) + V + O + Place + Time.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питання та заперечення',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Yes/No питання:</strong> <span class="gw-en">Do you like pizza?</span></li>
<li><strong>Wh-питання:</strong> <span class="gw-en">Where do you live?</span></li>
<li><strong>Заперечення:</strong> <span class="gw-en">I don't like apples.</span></li>
<li><strong>Допоміжні дієслова:</strong> do/does/did + not.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Прислівники та обставини',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Прислівники частотності</strong> (always, often, never) — перед дієсловом: <span class="gw-en">She always arrives early.</span></li>
<li><strong>Прислівники способу дії</strong> (quickly, well) — в кінці речення: <span class="gw-en">He speaks English fluently.</span></li>
<li><strong>Порядок обставин:</strong> Manner → Place → Time.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Порядок слів">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Basic Word Order</strong></td>
<td>A1</td>
<td>Базова структура S–V–O у ствердженнях</td>
</tr>
<tr>
<td><strong>Questions & Negatives</strong></td>
<td>A1–A2</td>
<td>Питання та заперечення з do/does/did</td>
</tr>
<tr>
<td><strong>Adverbs & Adverbials</strong></td>
<td>A2–B1</td>
<td>Позиція прислівників та обставин</td>
</tr>
<tr>
<td><strong>Verbs & Objects</strong></td>
<td>A2–B1</td>
<td>Модальні та фразові дієслова</td>
</tr>
<tr>
<td><strong>Advanced Emphasis</strong></td>
<td>B1–B2</td>
<td>Інверсія та cleft-речення</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Просунуті структури',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Інверсія</strong> — з негативними прислівниками: <span class="gw-en">Never have I seen...</span></li>
<li><strong>It-cleft</strong> — підсилення елемента: <span class="gw-en">It was you who called.</span></li>
<li><strong>What-cleft</strong> — підсилення дії: <span class="gw-en">What I need is rest.</span></li>
<li><strong>Emphatic do</strong> — підсилення ствердження: <span class="gw-en">I do like it!</span></li>
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
<p>Почни з <strong>базового порядку слів S–V–O</strong> — це основа англійського речення.</p>
<p>Потім вивчи питання та заперечення з <strong>do/does/did</strong>.</p>
<p>Прислівники частотності ставляться <strong>перед дієсловом</strong>, а способу дії — <strong>в кінці</strong>.</p>
<p>Для просунутого рівня — інверсія та cleft-речення додають <strong>формальності та акценту</strong>.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
