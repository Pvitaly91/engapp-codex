<?php

namespace Database\Seeders\Page_v2\PassiveVoice\InfinitivesGerund;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceInfinitivesGerundCategorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-infinitives-gerund';
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
            'subtitle_html' => '<p><strong>Інфінітив та герундій у пасиві (Passive Infinitives and Gerunds)</strong> — це складні пасивні конструкції, які часто використовуються в академічному, науковому та формальному письмі. Розуміння цих форм допоможе тобі краще сприймати складні тексти та писати більш професійно.</p>',
            'subtitle_text' => 'Пасивний інфінітив та герундій: to be done, being done, to have been done, having been done. Структура, використання та приклади.',
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
                // Hero block
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
                                'text' => '<strong>to be done</strong> — теперішнє/майбутнє:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Перф. інфінітив',
                                'color' => 'blue',
                                'text' => '<strong>to have been done</strong> — попередня дія:',
                                'example' => 'He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Герундій',
                                'color' => 'rose',
                                'text' => '<strong>being done</strong> — процес:',
                                'example' => 'She hates being interrupted.',
                            ],
                            [
                                'label' => 'Перф. герундій',
                                'color' => 'amber',
                                'text' => '<strong>having been done</strong> — попередня дія:',
                                'example' => 'Having been warned, he was careful.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - всі форми
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Чотири пасивні форми',
                        'intro' => 'Порівняння активних та пасивних форм інфінітива і герундія:',
                        'items' => [
                            [
                                'label' => 'Active Inf.',
                                'title' => 'to do',
                                'subtitle' => '→ Passive: to be done',
                            ],
                            [
                                'label' => 'Perf. Active Inf.',
                                'title' => 'to have done',
                                'subtitle' => '→ Passive: to have been done',
                            ],
                            [
                                'label' => 'Active Gerund',
                                'title' => 'doing',
                                'subtitle' => '→ Passive: being done',
                            ],
                            [
                                'label' => 'Perf. Active Ger.',
                                'title' => 'having done',
                                'subtitle' => '→ Passive: having been done',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Passive Infinitive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Пасивний інфінітив (to be done)',
                        'sections' => [
                            [
                                'label' => 'Після need/want/expect',
                                'color' => 'emerald',
                                'description' => 'Коли хтось хоче/очікує, що <strong>щось буде зроблено</strong>:',
                                'examples' => [
                                    ['en' => 'The report needs to be finished by Friday.', 'ua' => 'Звіт потрібно закінчити до п\'ятниці.'],
                                    ['en' => 'I want this issue to be resolved.', 'ua' => 'Я хочу, щоб це питання вирішили.'],
                                    ['en' => 'They expect the project to be approved.', 'ua' => 'Вони очікують, що проєкт схвалять.'],
                                ],
                            ],
                            [
                                'label' => 'Після seem/appear',
                                'color' => 'blue',
                                'description' => 'Для враження, здогадки:',
                                'examples' => [
                                    ['en' => 'The door seems to be locked.', 'ua' => 'Здається, двері замкнені.'],
                                    ['en' => 'He appears to be respected by everyone.', 'ua' => 'Здається, його всі поважають.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Perfect Passive Infinitive
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '3. Перфектний пасивний інфінітив (to have been done)',
                        'sections' => [
                            [
                                'label' => 'Попередня дія',
                                'color' => 'emerald',
                                'description' => 'Вказує на дію, що <strong>відбулася раніше</strong>:',
                                'examples' => [
                                    ['en' => 'He seems to have been promoted.', 'ua' => 'Здається, його підвищили.'],
                                    ['en' => 'The documents appear to have been lost.', 'ua' => 'Документи, схоже, були втрачені.'],
                                ],
                            ],
                            [
                                'label' => 'Reporting structures',
                                'color' => 'blue',
                                'description' => 'У безособових конструкціях з <strong>is said/believed/reported</strong>:',
                                'examples' => [
                                    ['en' => 'He is believed to have been kidnapped.', 'ua' => 'Вважається, що його викрали.'],
                                    ['en' => 'She is reported to have been seen in Paris.', 'ua' => 'Повідомляється, що її бачили в Парижі.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Passive Gerund
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Пасивний герундій (being done)',
                        'sections' => [
                            [
                                'label' => 'Після дієслів почуттів',
                                'color' => 'emerald',
                                'description' => 'Після <strong>like, hate, enjoy, avoid</strong>:',
                                'examples' => [
                                    ['en' => 'She hates being interrupted.', 'ua' => 'Вона ненавидить, коли її перебивають.'],
                                    ['en' => 'Nobody likes being criticized.', 'ua' => 'Ніхто не любить, коли його критикують.'],
                                    ['en' => 'He enjoys being praised.', 'ua' => 'Йому подобається, коли його хвалять.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'blue',
                                'description' => 'Після <strong>without, after, before, despite</strong>:',
                                'examples' => [
                                    ['en' => 'He left without being noticed.', 'ua' => 'Він пішов непомітно.'],
                                    ['en' => 'After being rejected, she tried again.', 'ua' => 'Після того як її відхилили, вона спробувала знову.'],
                                    ['en' => 'Despite being warned, he continued.', 'ua' => 'Попри попередження, він продовжив.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Usage panels - Perfect Passive Gerund
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '5. Перфектний пасивний герундій (having been done)',
                        'sections' => [
                            [
                                'label' => 'Причина → наслідок',
                                'color' => 'emerald',
                                'description' => 'Для дій, що <strong>передували іншим</strong>:',
                                'examples' => [
                                    ['en' => 'Having been warned, he was more careful.', 'ua' => 'Отримавши попередження, він був обережнішим.'],
                                    ['en' => 'Having been told the truth, she felt relieved.', 'ua' => 'Дізнавшись правду, вона відчула полегшення.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'blue',
                                'description' => 'Часто в <strong>академічному та офіційному</strong> письмі:',
                                'examples' => [
                                    ['en' => 'Having been approved, the project will begin.', 'ua' => 'Після схвалення проєкт розпочнеться.'],
                                    ['en' => 'Having been elected, she took office.', 'ua' => 'Після обрання вона обійняла посаду.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Зведена таблиця форм',
                        'intro' => 'Всі пасивні форми інфінітива та герундія:',
                        'rows' => [
                            [
                                'en' => 'to be + V3',
                                'ua' => 'Пасивний інфінітив',
                                'note' => 'The work needs to be done.',
                            ],
                            [
                                'en' => 'to have been + V3',
                                'ua' => 'Перфектний пасивний інфінітив',
                                'note' => 'He seems to have been promoted.',
                            ],
                            [
                                'en' => 'being + V3',
                                'ua' => 'Пасивний герундій',
                                'note' => 'She hates being interrupted.',
                            ],
                            [
                                'en' => 'having been + V3',
                                'ua' => 'Перфектний пасивний герундій',
                                'note' => 'Having been warned, he left.',
                            ],
                        ],
                        'warning' => '📌 Перфектні форми вказують на дію, що передувала іншій!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Ключові правила',
                        'items' => [
                            '<strong>to be + V3</strong> — пасивний інфінітив для теперішніх/майбутніх дій.',
                            '<strong>to have been + V3</strong> — перфектний пасивний інфінітив для попередніх дій.',
                            '<strong>being + V3</strong> — пасивний герундій після прийменників та дієслів почуттів.',
                            '<strong>having been + V3</strong> — перфектний пасивний герундій для причинно-наслідкових зв\'язків.',
                            'Ці форми типові для <strong>академічного та формального</strong> стилю.',
                            'Використовуй <strong>to be done</strong> після need, want, expect, seem, appear.',
                            'Використовуй <strong>being done</strong> після like, hate, enjoy, avoid та прийменників.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
