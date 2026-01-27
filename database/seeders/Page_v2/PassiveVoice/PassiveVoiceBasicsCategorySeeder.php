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
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '1. Що таке пасивний стан?',
                        'intro' => 'У пасивному стані фокус на дії або її результаті, а не на виконавці:',
                        'items' => [
                            [
                                'label' => 'Active',
                                'title' => 'Активний стан',
                                'subtitle' => 'Підмет виконує дію: Tom writes letters.',
                            ],
                            [
                                'label' => 'Passive',
                                'title' => 'Пасивний стан',
                                'subtitle' => 'Підмет отримує дію: Letters are written by Tom.',
                            ],
                            [
                                'label' => 'Коли?',
                                'title' => 'Використання',
                                'subtitle' => 'Виконавець невідомий, неважливий або очевидний.',
                            ],
                            [
                                'label' => 'Agent',
                                'title' => 'Вказівка виконавця',
                                'subtitle' => 'by + виконавець (необов\'язково): by Tom.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '2. Present Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'English is spoken in many countries.', 'ua' => 'Англійська мова розмовляється в багатьох країнах.'],
                                    ['en' => 'The rooms are cleaned every day.', 'ua' => 'Кімнати прибираються щодня.'],
                                    ['en' => 'I am paid monthly.', 'ua' => 'Мені платять щомісяця.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після be: <strong>am/is/are + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The door is not locked.', 'ua' => 'Двері не замкнені.'],
                                    ['en' => 'These cars are not made in Japan.', 'ua' => 'Ці машини не виготовляються в Японії.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Am/Is/Are</strong> виходить на перше місце: <strong>Is/Are + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Is English spoken here?', 'ua' => 'Тут розмовляють англійською?'],
                                    ['en' => 'Are the letters sent daily?', 'ua' => 'Листи надсилаються щодня?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '3. Past Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house was built in 1990.', 'ua' => 'Будинок був побудований у 1990 році.'],
                                    ['en' => 'The thieves were caught yesterday.', 'ua' => 'Злодіїв зловили вчора.'],
                                    ['en' => 'I was invited to the party.', 'ua' => 'Мене запросили на вечірку.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення',
                                'color' => 'rose',
                                'description' => 'Додаємо <strong>not</strong> після was/were: <strong>was/were + not + V3</strong>',
                                'examples' => [
                                    ['en' => 'The email was not sent.', 'ua' => 'Електронний лист не був надісланий.'],
                                    ['en' => 'We were not informed about it.', 'ua' => 'Нас не повідомили про це.'],
                                ],
                            ],
                            [
                                'label' => 'Питання',
                                'color' => 'blue',
                                'description' => '<strong>Was/Were</strong> виходить на перше місце: <strong>Was/Were + S + V3?</strong>',
                                'examples' => [
                                    ['en' => 'Was the car repaired?', 'ua' => 'Машину відремонтували?'],
                                    ['en' => 'Were the documents signed?', 'ua' => 'Документи були підписані?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '4. Порівняння Active vs Passive',
                        'intro' => 'Як трансформувати речення з активного стану в пасивний:',
                        'rows' => [
                            [
                                'en' => 'Active: Tom writes letters.',
                                'ua' => 'Том пише листи.',
                                'note' => '→ Passive: Letters are written by Tom.',
                            ],
                            [
                                'en' => 'Active: She cleaned the room.',
                                'ua' => 'Вона прибрала кімнату.',
                                'note' => '→ Passive: The room was cleaned by her.',
                            ],
                            [
                                'en' => 'Active: They make cars in Germany.',
                                'ua' => 'Вони виробляють машини в Німеччині.',
                                'note' => '→ Passive: Cars are made in Germany.',
                            ],
                        ],
                        'warning' => '📌 Object активного речення стає Subject пасивного!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '5. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Пропуск дієслова be.',
                                'wrong' => 'The letter written yesterday.',
                                'right' => '✅ The letter was written yesterday.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Неправильна форма be.',
                                'wrong' => 'The letters was sent.',
                                'right' => '✅ The letters were sent.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання V2 замість V3.',
                                'wrong' => 'The house was builded.',
                                'right' => '✅ The house was built.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'A2',
                    'body' => json_encode([
                        'title' => '6. Короткий конспект',
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
