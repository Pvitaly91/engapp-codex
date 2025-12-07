<?php

namespace Database\Seeders\Page_v2\QuestionsNegations\TypesOfQuestions;

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
            'title' => 'Види питальних речень',
            'subtitle_html' => '<p><strong>Види питальних речень (Types of Questions)</strong> — важлива тема для опанування англійської мови. В англійській існує декілька типів питань, кожен з яких має свою структуру та призначення: загальні питання (Yes/No questions), спеціальні питання (Wh-questions), альтернативні питання, розділові питання та інші.</p>',
            'subtitle_text' => 'Види питальних речень в англійській мові: загальні, спеціальні, альтернативні, розділові питання та їх структура.',
            'locale' => 'uk',
            'tags' => [
                'Питальні речення',
                'Види питань',
                'Types of Questions',
                'Yes/No Questions',
                'Wh-Questions',
                'Загальні питання',
                'Спеціальні питання',
                'Question Forms',
                'Граматика',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A1–B1',
                        'intro' => 'У цьому розділі ти опануєш <strong>різні види питань</strong> в англійській мові: від простих Yes/No до складних розділових питань.',
                        'rules' => [
                            [
                                'label' => 'Загальні питання',
                                'color' => 'emerald',
                                'text' => '<strong>Yes/No Questions</strong> — відповідь так чи ні:',
                                'example' => 'Do you like coffee?',
                            ],
                            [
                                'label' => 'Спеціальні питання',
                                'color' => 'blue',
                                'text' => '<strong>Wh-Questions</strong> — питання з питальними словами:',
                                'example' => 'What do you want?',
                            ],
                            [
                                'label' => 'Розділові питання',
                                'color' => 'violet',
                                'text' => '<strong>Question Tags</strong> — питання в кінці речення:',
                                'example' => "You like tea, don't you?",
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Загальні питання (Yes/No Questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Загальні питання</strong> — відповідь "так" або "ні": <span class="gw-en">Do you speak English?</span></li>
<li><strong>З to be:</strong> <span class="gw-en">Are you ready? Is she home?</span></li>
<li><strong>З do/does/did:</strong> <span class="gw-en">Do you like pizza? Did he call?</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">Can you swim? Should I go?</span></li>
<li><strong>Структура:</strong> Auxiliary + Subject + Main Verb + ?</li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Спеціальні питання (Wh-Questions)',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Спеціальні питання</strong> — починаються з питальних слів: <span class="gw-en">What, Where, When, Who, Why, How</span></li>
<li><strong>Приклади:</strong> <span class="gw-en">What do you want? Where is he? When did it happen?</span></li>
<li><strong>Структура:</strong> Wh-word + Auxiliary + Subject + Main Verb + ?</li>
<li><strong>Питання до підмета:</strong> без допоміжного дієслова: <span class="gw-en">Who called? What happened?</span></li>
</ul>
HTML,
                ],
                [
                    'column' => 'left',
                    'heading' => 'Альтернативні питання',
                    'css_class' => null,
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Альтернативні питання</strong> — вибір між варіантами: <span class="gw-en">Do you want tea or coffee?</span></li>
<li><strong>Структура:</strong> Yes/No Question + or + Alternative</li>
<li><strong>Приклади:</strong> <span class="gw-en">Is she coming or staying? Did he call or text?</span></li>
<li><strong>Відповідь:</strong> одна з альтернатив, не "так" чи "ні".</li>
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
<td><strong>Wh-Questions</strong></td>
<td>A1–A2</td>
<td>Спеціальні питання (What, Where, When...)</td>
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
<td><strong>Subject Questions</strong></td>
<td>A2–B1</td>
<td>Питання до підмета (Who...? What...?)</td>
</tr>
<tr>
<td><strong>Indirect Questions</strong></td>
<td>B1</td>
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
                    'body' => <<<'HTML'
<ul class="gw-list">
<li><strong>Розділові питання</strong> — короткі питання в кінці речення: <span class="gw-en">You like tea, don't you?</span></li>
<li><strong>Правило:</strong> позитивне речення + негативний tag, негативне речення + позитивний tag</li>
<li><strong>Приклади:</strong> <span class="gw-en">She is happy, isn't she? They don't know, do they?</span></li>
<li><strong>З модальними:</strong> <span class="gw-en">You can swim, can't you?</span></li>
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
<p>Почни з <strong>загальних питань (Yes/No)</strong> — це найпростіший тип.</p>
<p>Вивчи <strong>питальні слова</strong> (What, Where, When, Who, Why, How) для спеціальних питань.</p>
<p>Запам'ятай: <strong>питання до підмета</strong> не потребують допоміжного дієслова.</p>
<p>Розділові питання використовуй для <strong>підтвердження</strong> інформації.</p>
</div>
</div>
HTML,
                ],
            ],
        ];
    }
}
