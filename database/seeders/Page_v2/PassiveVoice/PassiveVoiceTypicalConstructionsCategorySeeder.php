<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceTypicalConstructionsCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-typical-constructions';
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
            'title' => 'Типові конструкції й "фішки"',
            'subtitle_html' => '<p><strong>Типові конструкції пасивного стану</strong> — це особливі випадки та ідіоматичні вирази з пасивом. Тут ти вивчиш конструкції з <em>get</em>, <em>have something done</em>, пасив з двома додатками, безособові пасивні конструкції та інші "фішки".</p>',
            'subtitle_text' => 'Типові конструкції пасивного стану: get passive, have something done, пасив з двома додатками, безособові конструкції.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Get Passive',
                'Have Something Done',
                'Causative',
                'Impersonal Passive',
                'B2',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2',
                    'body' => json_encode([
                        'level' => 'B2',
                        'intro' => 'У цій темі ти вивчиш <strong>особливі конструкції пасивного стану</strong>: get passive, have something done, пасив з двома додатками та безособові пасивні структури.',
                        'rules' => [
                            [
                                'label' => 'Get Passive',
                                'color' => 'emerald',
                                'text' => 'Розмовний варіант: <strong>get + V3</strong>:',
                                'example' => 'He got fired last week.',
                            ],
                            [
                                'label' => 'Causative',
                                'color' => 'blue',
                                'text' => 'Каузатив: <strong>have/get + object + V3</strong>:',
                                'example' => 'I had my car repaired.',
                            ],
                            [
                                'label' => 'Impersonal',
                                'color' => 'rose',
                                'text' => 'Безособові: <strong>It is said that...</strong>:',
                                'example' => 'It is believed that he is innocent.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => 'Короткий конспект',
                        'items' => [
                            '<strong>Get passive</strong> (get + V3) — розмовний варіант, часто для несподіваних подій.',
                            '<strong>Have/Get something done</strong> — коли хтось робить щось для нас.',
                            '<strong>Пасив з двома додатками</strong> — обидва додатки можуть стати підметом.',
                            '<strong>It is said/believed that...</strong> — формальні безособові конструкції.',
                            '<strong>Subject + is said + to...</strong> — альтернативна структура безособового пасиву.',
                            '<strong>Be born</strong> — завжди пасив: I was born in...',
                            '<strong>Типові дієслова</strong>: say, believe, think, know, report, expect, suppose.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
