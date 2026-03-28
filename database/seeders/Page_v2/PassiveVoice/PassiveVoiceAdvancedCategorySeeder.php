<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceAdvancedCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-advanced';
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
        $parentCategory = PageCategory::where('slug', 'passive-voice')->first();

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
            'title' => 'Просунутий рівень — Складні конструкції',
            'subtitle_html' => '<p><strong>Просунутий рівень пасивного стану</strong> — це складні та рідкісні конструкції, що використовуються в академічному, науковому та формальному письмі. Тут ти вивчиш пасив з інфінітивами, герундіями, перфектними формами та особливі випадки.</p>',
            'subtitle_text' => 'Складні конструкції пасивного стану: пасив з інфінітивами, герундіями, перфектними формами, need/want passive та стилістичні особливості.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Perfect Infinitive Passive',
                'Gerund Passive',
                'Advanced Grammar',
                'Academic Writing',
                'C1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'C1',
                    'body' => json_encode([
                        'level' => 'C1',
                        'intro' => 'У цій темі ти вивчиш <strong>складні конструкції пасивного стану</strong>: пасив з інфінітивами та герундіями, перфектні форми, need/want passive та стилістичні нюанси.',
                        'rules' => [
                            [
                                'label' => 'Infinitive',
                                'color' => 'emerald',
                                'text' => 'Пасивний інфінітив: <strong>to be + V3</strong>:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'blue',
                                'text' => 'Перфектний пасив: <strong>to have been + V3</strong>:',
                                'example' => 'He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Gerund',
                                'color' => 'rose',
                                'text' => 'Пасивний герундій: <strong>being + V3</strong>:',
                                'example' => 'She hates being interrupted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => 'Короткий конспект',
                        'items' => [
                            '<strong>Пасивний інфінітив</strong>: to be + V3 — The report needs to be finished.',
                            '<strong>Перфектний пасивний інфінітив</strong>: to have been + V3 — He seems to have been promoted.',
                            '<strong>Пасивний герундій</strong>: being + V3 — She hates being interrupted.',
                            '<strong>Перфектний пасивний герундій</strong>: having been + V3 — Having been warned...',
                            '<strong>Need/want/require + V-ing</strong> має пасивне значення: The car needs washing.',
                            '<strong>Reduced relative clauses</strong>: V3 як означення — The book written by Orwell.',
                            'Пасив часто використовується в <strong>наукових та офіційних</strong> текстах.',
                            'Уникайте надмірного пасиву — він може ускладнити текст.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
