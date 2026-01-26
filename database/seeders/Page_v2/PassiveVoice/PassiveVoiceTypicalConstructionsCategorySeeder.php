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
            'title' => 'Passive Voice: Типові конструкції й "фішки"',
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
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Get Passive — розмовний пасив',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>get + Past Participle (V3)</strong>. Більш розмовний, ніж be + V3.',
                                'examples' => [
                                    ['en' => 'He got fired last week.', 'ua' => 'Його звільнили минулого тижня.'],
                                    ['en' => 'She got promoted to manager.', 'ua' => 'Її підвищили до менеджера.'],
                                    ['en' => 'They got married in June.', 'ua' => 'Вони одружилися в червні.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => '<strong>Get passive</strong> часто використовується для несподіваних або негативних подій.',
                                'examples' => [
                                    ['en' => 'The window got broken during the storm.', 'ua' => 'Вікно розбилося під час бурі.'],
                                    ['en' => 'I got stuck in traffic.', 'ua' => 'Я застряг у заторі.'],
                                    ['en' => 'She got hurt in the accident.', 'ua' => 'Вона постраждала в аварії.'],
                                ],
                            ],
                            [
                                'label' => 'Часові форми',
                                'color' => 'amber',
                                'description' => '<strong>Get</strong> змінюється за часами: get/gets/got/will get + V3.',
                                'examples' => [
                                    ['en' => 'He often gets invited to parties.', 'ua' => 'Його часто запрошують на вечірки.'],
                                    ['en' => 'She will get paid tomorrow.', 'ua' => 'Їй заплатять завтра.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Have/Get Something Done — каузативна конструкція',
                        'sections' => [
                            [
                                'label' => 'Have + object + V3',
                                'color' => 'emerald',
                                'description' => 'Коли хтось <strong>робить щось для нас</strong> (сервіс, послуга).',
                                'examples' => [
                                    ['en' => 'I had my car repaired.', 'ua' => 'Мені відремонтували машину (в сервісі).'],
                                    ['en' => 'She had her hair cut.', 'ua' => 'Вона підстриглася (у перукарні).'],
                                    ['en' => 'We had the house painted.', 'ua' => 'Нам пофарбували будинок.'],
                                ],
                            ],
                            [
                                'label' => 'Get + object + V3',
                                'color' => 'sky',
                                'description' => 'Більш розмовний варіант <strong>have something done</strong>.',
                                'examples' => [
                                    ['en' => 'I need to get my phone fixed.', 'ua' => 'Мені треба відремонтувати телефон.'],
                                    ['en' => 'She got her nails done.', 'ua' => 'Вона зробила манікюр.'],
                                    ['en' => 'We should get the roof checked.', 'ua' => 'Нам слід перевірити дах.'],
                                ],
                            ],
                            [
                                'label' => 'Негативний досвід',
                                'color' => 'rose',
                                'description' => 'Також використовується для <strong>неприємних ситуацій</strong>.',
                                'examples' => [
                                    ['en' => 'He had his wallet stolen.', 'ua' => 'У нього вкрали гаманець.'],
                                    ['en' => 'She had her car broken into.', 'ua' => 'Їй зламали машину.'],
                                    ['en' => 'They had their house flooded.', 'ua' => 'Їхній будинок затопило.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Пасив з двома додатками',
                        'sections' => [
                            [
                                'label' => 'Два варіанти',
                                'color' => 'emerald',
                                'description' => 'Дієслова як <strong>give, send, offer, tell, show</strong> мають два додатки. Обидва можуть стати підметом у пасиві.',
                                'examples' => [
                                    ['en' => 'Active: They gave me a book.', 'ua' => 'Вони дали мені книгу.'],
                                    ['en' => 'Passive 1: I was given a book.', 'ua' => 'Мені дали книгу. (фокус на мені)'],
                                    ['en' => 'Passive 2: A book was given to me.', 'ua' => 'Книгу дали мені. (фокус на книзі)'],
                                ],
                            ],
                            [
                                'label' => 'Перший варіант частіший',
                                'color' => 'sky',
                                'description' => 'В англійській мові <strong>особовий підмет</strong> у пасиві зазвичай природніший.',
                                'examples' => [
                                    ['en' => 'She was offered a job. (краще)', 'ua' => 'Їй запропонували роботу.'],
                                    ['en' => 'A job was offered to her. (рідше)', 'ua' => 'Роботу запропонували їй.'],
                                    ['en' => 'He was told the news.', 'ua' => 'Йому розповіли новину.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Безособові пасивні конструкції',
                        'sections' => [
                            [
                                'label' => 'It + passive + that',
                                'color' => 'emerald',
                                'description' => 'Формальний стиль: <strong>It is said/believed/known/reported that...</strong>',
                                'examples' => [
                                    ['en' => 'It is said that he is very rich.', 'ua' => 'Кажуть, що він дуже багатий.'],
                                    ['en' => 'It is believed that the Earth is flat. (помилкова думка)', 'ua' => 'Вважається, що Земля пласка.'],
                                    ['en' => 'It is known that smoking causes cancer.', 'ua' => 'Відомо, що куріння спричиняє рак.'],
                                ],
                            ],
                            [
                                'label' => 'Subject + passive + to',
                                'color' => 'sky',
                                'description' => 'Альтернативна структура: <strong>Subject + is said/believed + to + verb</strong>',
                                'examples' => [
                                    ['en' => 'He is said to be very rich.', 'ua' => 'Кажуть, що він дуже багатий.'],
                                    ['en' => 'She is believed to have left the country.', 'ua' => 'Вважається, що вона покинула країну.'],
                                    ['en' => 'They are reported to be negotiating.', 'ua' => 'Повідомляється, що вони ведуть переговори.'],
                                ],
                            ],
                            [
                                'label' => 'Типові дієслова',
                                'color' => 'amber',
                                'description' => 'Часто використовуються: <strong>say, believe, think, know, report, expect, suppose, consider</strong>.',
                                'examples' => [
                                    ['en' => 'The meeting is expected to start at 9.', 'ua' => 'Очікується, що зустріч почнеться о 9.'],
                                    ['en' => 'He is thought to be the best candidate.', 'ua' => 'Вважається, що він найкращий кандидат.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Born — особлива конструкція',
                        'sections' => [
                            [
                                'label' => 'Be born',
                                'color' => 'emerald',
                                'description' => '<strong>Be born</strong> завжди у пасиві. Минулий час: <strong>was/were born</strong>.',
                                'examples' => [
                                    ['en' => 'I was born in Kyiv.', 'ua' => 'Я народився в Києві.'],
                                    ['en' => 'She was born in 1990.', 'ua' => 'Вона народилася в 1990 році.'],
                                    ['en' => 'Where were you born?', 'ua' => 'Де ти народився?'],
                                ],
                            ],
                            [
                                'label' => 'Теперішній час',
                                'color' => 'sky',
                                'description' => 'Теперішній час <strong>am/is/are born</strong> — для загальних фактів.',
                                'examples' => [
                                    ['en' => 'Babies are born every day.', 'ua' => 'Діти народжуються щодня.'],
                                    ['en' => 'About 140 million babies are born each year.', 'ua' => 'Близько 140 мільйонів дітей народжується щороку.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Be Passive vs Get Passive',
                        'intro' => 'Порівняння стандартного пасиву з get passive:',
                        'rows' => [
                            [
                                'en' => 'Be + V3',
                                'ua' => 'Нейтральний, формальний',
                                'note' => 'The report was completed.',
                            ],
                            [
                                'en' => 'Get + V3',
                                'ua' => 'Розмовний, неформальний',
                                'note' => 'The report got completed.',
                            ],
                            [
                                'en' => 'Be + V3',
                                'ua' => 'Стан або результат',
                                'note' => 'The door is closed. (стан)',
                            ],
                            [
                                'en' => 'Get + V3',
                                'ua' => 'Процес, зміна',
                                'note' => 'The door got closed. (дія)',
                            ],
                        ],
                        'warning' => '📌 Get passive частіше для несподіваних, негативних або динамічних подій!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
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
