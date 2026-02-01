<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceV2InfinitiveGerundCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-v2-infinitive-gerund';
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
            'title' => 'Інфінітив та герундій у пасиві',
            'subtitle_html' => '<p><strong>Пасивний інфінітив та герундій</strong> допомагають говорити про дію, яку отримує об’єкт, у формі to-infinitive або -ing.</p>',
            'subtitle_text' => 'Пасивні інфінітиви (to be done, to have been done) та герундії (being done, having been done).',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Passive Infinitive',
                'Passive Gerund',
                'Infinitive',
                'Gerund',
                'B2',
                'C1',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'level' => 'B2–C1',
                    'body' => json_encode([
                        'level' => 'B2–C1',
                        'intro' => 'Пасивні інфінітиви та герундії показують, що дія <strong>здійснюється над об’єктом</strong>, а не виконується ним.',
                        'rules' => [
                            [
                                'label' => 'Infinitive',
                                'color' => 'emerald',
                                'text' => 'Пасивний інфінітив: <strong>to be done</strong> / <strong>to have been done</strong>.',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Gerund',
                                'color' => 'blue',
                                'text' => 'Пасивний герундій: <strong>being done</strong> / <strong>having been done</strong>.',
                                'example' => 'He denied being invited.',
                            ],
                            [
                                'label' => 'Фокус',
                                'color' => 'rose',
                                'text' => 'Виконавець часто не важливий або невідомий.',
                                'example' => 'She expected to be told the truth.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => 'Форми пасивного інфінітива та герундія',
                        'intro' => 'Порівняй основні пасивні форми та їхні значення:',
                        'items' => [
                            [
                                'label' => 'Infinitive',
                                'title' => 'to be done',
                                'subtitle' => 'I want <strong>to be invited</strong> to the event.',
                            ],
                            [
                                'label' => 'Perfect Infinitive',
                                'title' => 'to have been done',
                                'subtitle' => 'She is happy <strong>to have been promoted</strong>.',
                            ],
                            [
                                'label' => 'Gerund',
                                'title' => 'being done',
                                'subtitle' => 'He dislikes <strong>being interrupted</strong>.',
                            ],
                            [
                                'label' => 'Perfect Gerund',
                                'title' => 'having been done',
                                'subtitle' => 'They admitted <strong>having been warned</strong>.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2–C1',
                    'body' => json_encode([
                        'title' => 'Коли використовувати',
                        'items' => [
                            '<strong>to be done</strong> — коли дія відбудеться в майбутньому або є метою.',
                            '<strong>to have been done</strong> — коли дія завершилась раніше.',
                            '<strong>being done</strong> — коли дію сприймаємо як процес.',
                            '<strong>having been done</strong> — коли важливо, що дія завершена до іншої події.',
                            'Пасивні форми часто йдуть після прикметників, дієслів бажання, очікування, уникнення.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'column' => 'left',
                    'heading' => 'Типові помилки',
                    'level' => 'B2–C1',
                    'body' => <<<'HTML'
<ul class="gw-list">
<li>❌ <strong>to be invite</strong> → ✅ <strong>to be invited</strong></li>
<li>❌ <strong>being do</strong> → ✅ <strong>being done</strong></li>
<li>❌ <strong>to have be done</strong> → ✅ <strong>to have been done</strong></li>
<li>❌ <strong>having been do</strong> → ✅ <strong>having been done</strong></li>
</ul>
HTML,
                ],
            ],
        ];
    }
}
