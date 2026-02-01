<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2\InfinitivesGerund;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceV2InfinitivesGerundCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2-infinitives-gerund';
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
        $parentCategory = PageCategory::where('slug', 'passive-voice-v2')->first();

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
                'level' => 'B2',
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
                'level' => $block['level'] ?? null,
                'seeder' => static::class,
            ]);
        }
    }

    protected function description(): array
    {
        return [
            'title' => 'Інфінітив та герундій у пасиві',
            'subtitle_html' => '<p><strong>Інфінітив та герундій у пасиві</strong> — складні пасивні конструкції для формального та академічного стилю. Детальна інформація доступна на окремих сторінках.</p>',
            'subtitle_text' => 'Пасивний інфінітив та герундій: to be done, being done, to have been done, having been done.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Infinitive',
                'Passive Gerund',
                'B2',
                'C1',
                'Theory',
            ],
            'blocks' => [
                // Hero block - коротка інформація
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2–C1',
                        'intro' => 'У цьому розділі ти вивчиш <strong>пасивні форми інфінітива та герундія</strong> — ключові конструкції для академічного та формального письма.',
                        'rules' => [
                            [
                                'label' => 'Інфінітив',
                                'color' => 'emerald',
                                'text' => '<strong>to be done / to have been done</strong>:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Герундій',
                                'color' => 'blue',
                                'text' => '<strong>being done / having been done</strong>:',
                                'example' => 'She hates being interrupted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list - огляд тем
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => 'Теми розділу',
                        'items' => [
                            '<strong>Пасивний інфінітив (to be done)</strong> — The work needs to be done.',
                            '<strong>Перфектний пасивний інфінітив (to have been done)</strong> — He seems to have been promoted.',
                            '<strong>Пасивний герундій (being done)</strong> — She hates being interrupted.',
                            '<strong>Перфектний пасивний герундій (having been done)</strong> — Having been warned, he was careful.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
