<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class TypesOfQuestionsCategorySeeder extends Seeder
{
    /**
     * Cache for Tag::firstOrCreate to avoid N+1 queries.
     *
     * @var array<string, int>
     */
    protected array $tagCache = [];

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
        $parentCategory = PageCategory::where('slug', 'pytalni-rechennia-ta-zaperechennia')->first();

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
                'seeder' => static::class,
            ]);
            // BLOCK-FIRST: Subtitle gets only very general tags to avoid winning matches
            // over more specific theory blocks. Use inherit_tags=false to exclude
            // category tags and only have Introduction/Overview tags.
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
            'title' => 'Види питальних речень',
            'subtitle_html' => '<p><strong>Види питальних речень</strong> — це різні типи запитань в англійській мові. Тут ти навчишся формувати загальні питання (Yes/No Questions), спеціальні питання (Wh-Questions), альтернативні та розділові питання, а також питання до підмета.</p>',
            'subtitle_text' => 'Види питальних речень в англійській мові: загальні, спеціальні, альтернативні, розділові питання та питання до підмета.',
            'locale' => 'uk',
            'tags' => [
                // Theme tags (canonical from QuestionsDifferentTypesClaudeSeeder)
                'Types of Questions',
                'Question Forms',
                'Grammar',
                'Theory',
                // All question type detail tags (canonical)
                'Yes/No Questions',
                'General Questions',
                'Wh-Questions',
                'Special Questions',
                'Subject Questions',
                'Indirect Questions',
                'Tag Questions',
                'Question Tags',
                'Disjunctive Questions',
                'Alternative Questions',
                'Choice Questions',
                'Negative Questions',
                'Negative Question Forms',
                // Auxiliary tags (canonical - common across all)
                'Do/Does/Did',
                'Be (am/is/are/was/were)',
                'Have/Has/Had',
                'Modal Verbs',
                // Level tags (canonical CEFR format)
                'CEFR A1',
                'CEFR A2',
                'CEFR B1',
                'CEFR B2',
            ],
            // Subtitle gets only Introduction/Overview tags - no category inheritance
            // to prevent it from winning over detailed theory blocks
            'subtitle_tags' => ['Introduction', 'Overview'],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    // BLOCK-FIRST: Hero block gets only general Overview tags
                    // to avoid competing with specific theory blocks
                    // No CEFR tags to prevent matching on level alone
                    'tags' => ['Introduction', 'Overview'],
                    'inherit_tags' => false,
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цьому розділі ти вивчиш <strong>різні види питальних речень</strong> в англійській мові: від простих загальних питань до складних розділових.',
                        'rules' => [
                            [
                                'label' => 'Yes/No Questions',
                                'color' => 'emerald',
                                'text' => '<strong>Загальні питання</strong> — відповідь "так" або "ні":',
                                'example' => 'Do you like coffee?',
                            ],
                            [
                                'label' => 'Wh-Questions',
                                'color' => 'blue',
                                'text' => '<strong>Спеціальні питання</strong> — з питальними словами:',
                                'example' => 'What do you want?',
                            ],
                            [
                                'label' => 'Question Tags',
                                'color' => 'rose',
                                'text' => '<strong>Розділові питання</strong> — для підтвердження:',
                                'example' => "You like tea, don't you?",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Загальні питання (Yes/No Questions)',
                    'css_class' => null,
                    // BLOCK-FIRST: Yes/No Questions summary block with specific tags
                    'tags' => ['Yes/No Questions', 'General Questions', 'Do/Does/Did', 'To Be', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Загальні питання</strong> — відповідь "так" або "ні": <span class="gw-en">Do you like coffee?</span></li>
<li><strong>Допоміжне дієслово</strong> ставимо перед підметом: <span class="gw-en">Does he know? Can they swim?</span></li>
<li><strong>З to be</strong> — просто інвертуємо: <span class="gw-en">Is she ready? Are you happy?</span></li>
<li><strong>Структура:</strong> Auxiliary + Subject + Main Verb + ...?</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Спеціальні питання (Wh-Questions)',
                    'css_class' => null,
                    // BLOCK-FIRST: Wh-Questions summary block with specific tags
                    'tags' => ['Wh-Questions', 'Special Questions', 'Subject Questions', 'Question Words', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Спеціальні питання</strong> — починаються з питальних слів: <span class="gw-en">What, Where, When, Why, Who, How</span></li>
<li><strong>Структура:</strong> Wh-word + Auxiliary + Subject + Main Verb + ...?</li>
<li><strong>Приклади:</strong> <span class="gw-en">What do you want? Where are you going? When did it happen?</span></li>
<li><strong>Питання до підмета</strong> — без допоміжного дієслова: <span class="gw-en">Who lives here? What happened?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Альтернативні питання',
                    'css_class' => null,
                    // BLOCK-FIRST: Alternative Questions summary block with specific tags
                    'tags' => ['Alternative Questions', 'Choice Questions', 'Or', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Альтернативні питання</strong> — вибір між варіантами з "or": <span class="gw-en">Do you prefer tea or coffee?</span></li>
<li><strong>Структура</strong> як у загальних питаннях + or: <span class="gw-en">Is it black or white?</span></li>
<li><strong>Відповідь</strong> — один із запропонованих варіантів: <span class="gw-en">Tea, please.</span></li>
<li>Можуть бути складнішими: <span class="gw-en">Would you like to go now or wait a bit?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Теми у цьому розділі',
                    'css_class' => 'gw-box--scroll',
                    // BLOCK-FIRST: Navigation/index table - no detailed matching tags
                    'tags' => ['Navigation', 'Index'],
                    'inherit_tags' => false,
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
<td><strong>Wh-Questions</strong></td>
<td>A1–A2</td>
<td>Спеціальні питання з what, where, when...</td>
</tr>
<tr>
<td><strong>Subject Questions</strong></td>
<td>A2–B1</td>
<td>Питання до підмета (who, what)</td>
</tr>
<tr>
<td><strong>Alternative Questions</strong></td>
<td>A2</td>
<td>Альтернативні питання з "or"</td>
</tr>
<tr>
<td><strong>Question Tags</strong></td>
<td>B1</td>
<td>Розділові питання (isn't it?, don't you?)</td>
</tr>
<tr>
<td><strong>Indirect Questions</strong></td>
<td>B1–B2</td>
<td>Непрямі питання (Can you tell me...?)</td>
</tr>
</tbody>
</table>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Розділові питання (Question Tags)',
                    'css_class' => null,
                    // BLOCK-FIRST: Question Tags summary block with specific tags
                    'tags' => ['Tag Questions', 'Question Tags', 'Disjunctive Questions', 'Summary'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Question Tags</strong> — коротке питання в кінці речення: <span class="gw-en">You like tea, don't you?</span></li>
<li><strong>Правило</strong> — ствердження + негативний tag, або заперечення + позитивний tag.</li>
<li><strong>Приклади:</strong> <span class="gw-en">She is happy, isn't she? They don't know, do they?</span></li>
<li><strong>Використання</strong> — для підтвердження інформації або початку розмови.</li>
</ul>
HTML,
                ],
                [
                    'column' => 'right',
                    'heading' => 'Поради для вивчення',
                    'css_class' => null,
                    // BLOCK-FIRST: Tips block - learning guidance, no matching tags
                    'tags' => ['Tips', 'Learning'],
                    'inherit_tags' => false,
                    'body' => <<<'HTML'
<div class="gw-hint">
<div class="gw-emoji">🧠</div>
<div>
<p>Почни з <strong>Yes/No питань</strong> — вони найпростіші для початківців.</p>
<p>Вивчи <strong>питальні слова</strong> (what, where, when, why, who, how) — вони допоможуть формувати спеціальні питання.</p>
<p>Запам'ятай <strong>порядок слів</strong>: питальне слово → допоміжне дієслово → підмет → основне дієслово.</p>
<p>Для <strong>question tags</strong> використовуй протилежну форму: позитив + негатив або навпаки.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
