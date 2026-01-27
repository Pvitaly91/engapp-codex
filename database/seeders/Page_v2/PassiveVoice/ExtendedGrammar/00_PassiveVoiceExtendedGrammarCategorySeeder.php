<?php

namespace Database\Seeders\Page_v2\PassiveVoice\ExtendedGrammar;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceExtendedGrammarCategorySeeder extends Seeder
{
    /**
     * Cache for Tag::firstOrCreate to avoid N+1 queries.
     *
     * @var array<string, int>
     */
    protected array $tagCache = [];

    protected function slug(): string
    {
        return 'passive-voice-extended-grammar';
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

        // Find parent category (Passive Voice)
        $parentCategory = PageCategory::where('slug', 'pasyvnyi-stan')->first();

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

        // Resolve category tags (including category slug as identifier tag for matching)
        $categoryTags = $description['tags'] ?? [];
        if (! in_array($slug, $categoryTags, true)) {
            $categoryTags[] = $slug;
        }
        $categoryTagIds = $this->resolveTagIds($categoryTags);

        // Sync tags to category
        if (! empty($categoryTagIds)) {
            $category->tags()->sync($categoryTagIds);
        }

        TextBlock::query()
            ->where('page_category_id', $category->getKey())
            ->whereNull('page_id')
            ->whereIn('seeder', $this->cleanupSeederClasses())
            ->delete();

        $locale = $description['locale'];
        $createdTextBlocks = [];

        if (! empty($description['subtitle_html'])) {
            $textBlock = TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $locale,
                'type' => 'subtitle',
                'column' => 'header',
                'heading' => null,
                'css_class' => null,
                'sort_order' => 0,
                'body' => $description['subtitle_html'],
                'level' => $description['subtitle_level'] ?? null,
                'seeder' => static::class,
            ]);
            // BLOCK-FIRST: Subtitle gets only very general tags to avoid winning matches
            $createdTextBlocks[] = [
                'block' => $textBlock,
                'config' => [
                    'tags' => $description['subtitle_tags'] ?? ['Introduction', 'Overview'],
                    'inherit_tags' => false,
                ],
            ];
        }

        foreach ($description['blocks'] ?? [] as $index => $block) {
            $blockType = $block['type'] ?? 'box';

            $textBlock = TextBlock::create([
                'page_id' => null,
                'page_category_id' => $category->getKey(),
                'locale' => $block['locale'] ?? $locale,
                'type' => $blockType,
                'column' => $block['column'] ?? 'left',
                'heading' => $block['heading'] ?? null,
                'css_class' => $block['css_class'] ?? null,
                'sort_order' => $index + 1,
                'body' => $block['body'] ?? null,
                'level' => $block['level'] ?? null,
                'seeder' => static::class,
            ]);
            $createdTextBlocks[] = ['block' => $textBlock, 'config' => $block];
        }

        // Sync tags to each TextBlock (category tags + optional block-specific tags)
        foreach ($createdTextBlocks as $item) {
            $textBlock = $item['block'];
            $blockConfig = $item['config'];

            // Check if tag inheritance is disabled for this block
            $inheritTags = $blockConfig['inherit_tags'] ?? true;

            if ($inheritTags) {
                // Start with category tags
                $blockTagIds = $categoryTagIds;

                // Add block-specific tags if defined
                if (! empty($blockConfig['tags'])) {
                    $blockSpecificTagIds = $this->resolveTagIds($blockConfig['tags']);
                    $blockTagIds = array_unique(array_merge($blockTagIds, $blockSpecificTagIds));
                }
            } else {
                // Only use block-specific tags (no inheritance)
                $blockTagIds = ! empty($blockConfig['tags'])
                    ? $this->resolveTagIds($blockConfig['tags'])
                    : [];
            }

            if (! empty($blockTagIds)) {
                $textBlock->tags()->sync($blockTagIds);
            }
        }
    }

    /**
     * Resolve tag names to tag IDs with caching.
     *
     * @param  array<string>  $tagNames
     * @return array<int>
     */
    protected function resolveTagIds(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (isset($this->tagCache[$tagName])) {
                $tagIds[] = $this->tagCache[$tagName];
            } else {
                $tag = Tag::firstOrCreate(['name' => $tagName]);
                $this->tagCache[$tagName] = $tag->id;
                $tagIds[] = $tag->id;
            }
        }

        return $tagIds;
    }

    protected function description(): array
    {
        return [
            'title' => 'Розширення граматики (Extended Grammar)',
            'subtitle_html' => '<p><strong>Розширення граматики пасивного стану</strong> — це поглиблене вивчення пасиву: питальні та заперечні форми, пасив з модальними дієсловами, та огляд пасиву в різних часах. Ці теми допоможуть тобі вільно використовувати пасивні конструкції в різних контекстах.</p>',
            'subtitle_text' => 'Розширена граматика пасивного стану: питання та заперечення, модальні дієслова, огляд часів.',
            'subtitle_level' => 'A2',
            'locale' => 'uk',
            'tags' => [
                // Theme tags
                'Passive Voice',
                'Extended Grammar',
                'Grammar',
                'Theory',
                // Detail tags for all topics in this category
                'Negatives in Passive',
                'Questions in Passive',
                'Short Answers',
                'Passive with Modals',
                'Modal Verbs',
                'Present Continuous Passive',
                'Past Continuous Passive',
                'Present Perfect Passive',
                'Future Passive',
                // Level tags
                'CEFR A2',
                'CEFR B1',
                'CEFR B2',
            ],
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'tags' => ['Introduction', 'Overview'],
                    'inherit_tags' => false,
                    'body' => json_encode([
                        'level' => 'A2–B2',
                        'intro' => 'У цьому розділі ти поглибиш свої знання <strong>пасивного стану</strong>: навчишся ставити питання та заперечення, використовувати пасив з модальними дієсловами та в різних часах.',
                        'rules' => [
                            [
                                'label' => 'ПИТАННЯ & ЗАПЕРЕЧЕННЯ',
                                'color' => 'emerald',
                                'text' => '<strong>Інверсія to be</strong> для питань, <strong>not</strong> для заперечень:',
                                'example' => 'Is it made here? It isn\'t made here.',
                            ],
                            [
                                'label' => 'МОДАЛЬНІ ДІЄСЛОВА',
                                'color' => 'blue',
                                'text' => '<strong>Modal + be + V3</strong> — пасив з can, must, should:',
                                'example' => 'It must be done. It can be fixed.',
                            ],
                            [
                                'label' => 'РІЗНІ ЧАСИ',
                                'color' => 'amber',
                                'text' => '<strong>Continuous, Perfect, Future</strong> — складніші форми пасиву:',
                                'example' => 'It is being done. It has been done. It will be done.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Питання та заперечення в пасиві',
                    'css_class' => null,
                    'level' => 'A2',
                    'tags' => ['Questions in Passive', 'Negatives in Passive', 'Short Answers', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Питання:</strong> <span class="gw-en">Is it made? Was it built? Has it been done?</span></li>
<li><strong>Заперечення:</strong> <span class="gw-en">It isn't made. It wasn't built. It hasn't been done.</span></li>
<li><strong>Короткі відповіді:</strong> <span class="gw-en">Yes, it is. / No, it wasn't. / Yes, it has.</span></li>
<li><strong>Wh-питання:</strong> <span class="gw-en">When was it built? Where is it made?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Пасив з модальними дієсловами',
                    'css_class' => null,
                    'level' => 'B1',
                    'tags' => ['Passive with Modals', 'Modal Verbs', 'Can/Could', 'Must', 'Should', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Формула:</strong> <span class="gw-en">modal + be + Past Participle (V3)</span></li>
<li><strong>can:</strong> <span class="gw-en">It can be done. — Це можна зробити.</span></li>
<li><strong>must:</strong> <span class="gw-en">It must be finished. — Це має бути завершено.</span></li>
<li><strong>should:</strong> <span class="gw-en">It should be checked. — Це слід перевірити.</span></li>
<li><strong>Заперечення:</strong> <span class="gw-en">It can't be done. It mustn't be touched.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Пасив у ключових часах',
                    'css_class' => null,
                    'level' => 'B1',
                    'tags' => ['Present Continuous Passive', 'Past Continuous Passive', 'Present Perfect Passive', 'Future Passive', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Present Continuous:</strong> <span class="gw-en">It is being done. — Це робиться зараз.</span></li>
<li><strong>Past Continuous:</strong> <span class="gw-en">It was being repaired. — Це ремонтувалося.</span></li>
<li><strong>Present Perfect:</strong> <span class="gw-en">It has been done. — Це зроблено.</span></li>
<li><strong>Future Simple:</strong> <span class="gw-en">It will be finished. — Це буде завершено.</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    'level' => 'A2',
                    'tags' => ['Navigation', 'Index'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<table class="gw-table" aria-label="Теми розділу Розширення граматики">
<thead>
<tr>
<th>Тема</th>
<th>Рівень</th>
<th>Опис</th>
</tr>
</thead>
<tbody>
<tr>
<td><strong>Negatives & Questions</strong></td>
<td>A2–B1</td>
<td>Питання та заперечення в пасиві + короткі відповіді</td>
</tr>
<tr>
<td><strong>Passive with Modals</strong></td>
<td>B1–B2</td>
<td>can/must/should + be + V3</td>
</tr>
<tr>
<td><strong>Passive in Key Tenses</strong></td>
<td>B1–B2</td>
<td>Continuous, Perfect, Future пасив</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    'level' => 'A2',
                    'tags' => ['Tips', 'Learning'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Для <strong>питань</strong> — інвертуй to be: <span class="gw-en">Is it done? Was it built?</span></p>
<p>Для <strong>заперечень</strong> — додай not до to be: <span class="gw-en">It isn't done. It wasn't built.</span></p>
<p>З <strong>модальними</strong> — завжди be: <span class="gw-en">modal + be + V3</span></p>
<p>У <strong>Continuous</strong> — being: <span class="gw-en">is/was being + V3</span></p>
<p>У <strong>Perfect</strong> — been: <span class="gw-en">has/had been + V3</span></p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
