<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceBasicsCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-basics';
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
            'title' => 'База — Основи пасивного стану',
            'subtitle_html' => '<p><strong>Основи пасивного стану (Passive Voice Basics)</strong> — це базовий рівень вивчення пасивних конструкцій в англійській мові. Тут ти дізнаєшся, як утворюється пасивний стан у Present Simple та Past Simple, і коли його використовувати.</p>',
            'subtitle_text' => 'Основи пасивного стану: базова структура, утворення у Present Simple та Past Simple, порівняння з активним станом.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Present Simple Passive',
                'Past Simple Passive',
                'A2',
                'Theory',
                'Basics',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'A2',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти вивчиш <strong>основи пасивного стану</strong>: базову структуру, утворення в Present Simple та Past Simple, і коли використовувати пасив замість активу.',
                        'rules' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'text' => 'Базова формула: <strong>be + Past Participle (V3)</strong>:',
                                'example' => 'The letter is written.',
                            ],
                            [
                                'label' => 'Present Simple',
                                'color' => 'blue',
                                'text' => 'Теперішній час: <strong>am/is/are + V3</strong>:',
                                'example' => 'English is spoken here.',
                            ],
                            [
                                'label' => 'Past Simple',
                                'color' => 'rose',
                                'text' => 'Минулий час: <strong>was/were + V3</strong>:',
                                'example' => 'The house was built in 1990.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => 'Короткий конспект',
                        'items' => [
                            'Пасивний стан: фокус на <strong>дії або результаті</strong>, а не на виконавці.',
                            'Базова структура: <strong>be + Past Participle (V3)</strong>.',
                            'Present Simple Passive: <strong>am/is/are + V3</strong>.',
                            'Past Simple Passive: <strong>was/were + V3</strong>.',
                            'Заперечення: <strong>be + not + V3</strong>.',
                            'Питання: <strong>Be + Subject + V3?</strong>',
                            'Виконавець (agent) вказується через <strong>by</strong>, але часто опускається.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
