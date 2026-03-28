<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceExtendedGrammarCategorySeeder extends Seeder
{
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
            'title' => 'Розширення граматики — Пасив у всіх часах',
            'subtitle_html' => '<p><strong>Розширення граматики пасивного стану</strong> — це середній рівень вивчення пасивних конструкцій. Тут ти дізнаєшся, як утворюється пасивний стан у Continuous та Perfect часах, а також з модальними дієсловами.</p>',
            'subtitle_text' => 'Пасивний стан у Continuous, Perfect часах та з модальними дієсловами. Розширена граматика для рівня B1.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Continuous Passive',
                'Present Perfect Passive',
                'Past Perfect Passive',
                'Future Simple Passive',
                'Modal Passive',
                'B1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B1',
                    'body' => json_encode([
                        'level' => 'B1',
                        'intro' => 'У цій темі ти вивчиш <strong>пасивний стан у різних часах</strong>: Continuous, Perfect, Future та з модальними дієсловами.',
                        'rules' => [
                            [
                                'label' => 'Continuous',
                                'color' => 'emerald',
                                'text' => 'Тривалі часи: <strong>be + being + V3</strong>:',
                                'example' => 'The house is being painted.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'blue',
                                'text' => 'Завершені часи: <strong>have/had + been + V3</strong>:',
                                'example' => 'The letter has been sent.',
                            ],
                            [
                                'label' => 'Modals',
                                'color' => 'rose',
                                'text' => 'З модальними: <strong>modal + be + V3</strong>:',
                                'example' => 'This must be done today.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B1',
                    'body' => json_encode([
                        'title' => 'Короткий конспект',
                        'items' => [
                            'Continuous Passive: <strong>be + being + V3</strong> — дія в процесі.',
                            'Perfect Passive: <strong>have/had + been + V3</strong> — завершена дія.',
                            'Future Passive: <strong>will + be + V3</strong> — майбутня дія.',
                            'Modal Passive: <strong>modal + be + V3</strong> — з модальними дієсловами.',
                            'Компонент <strong>be</strong> змінюється за часом, а <strong>V3</strong> залишається незмінним.',
                            'Future Perfect Passive: <strong>will have been + V3</strong> (рідко використовується).',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
