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
            'title' => 'Passive Voice: Розширення граматики — Пасив у всіх часах',
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
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Present Continuous Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>am/is/are + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The house is being painted right now.', 'ua' => 'Будинок фарбується прямо зараз.'],
                                    ['en' => 'The documents are being prepared.', 'ua' => 'Документи готуються.'],
                                    ['en' => 'I am being interviewed at the moment.', 'ua' => 'Мене зараз інтерв\'юють.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>відбувається зараз</strong>, у процесі.',
                                'examples' => [
                                    ['en' => 'The road is being repaired this week.', 'ua' => 'Дорогу ремонтують цього тижня.'],
                                    ['en' => 'New software is being installed.', 'ua' => 'Нове програмне забезпечення встановлюється.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Past Continuous Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>was/were + being + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The car was being repaired when I arrived.', 'ua' => 'Машину ремонтували, коли я прийшов.'],
                                    ['en' => 'The rooms were being cleaned all morning.', 'ua' => 'Кімнати прибиралися весь ранок.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>тривала в минулому</strong> в певний момент.',
                                'examples' => [
                                    ['en' => 'While I was waiting, my application was being processed.', 'ua' => 'Поки я чекав, мою заявку обробляли.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Present Perfect Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>has/have + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The project has been completed.', 'ua' => 'Проєкт завершено.'],
                                    ['en' => 'All tickets have been sold.', 'ua' => 'Усі квитки продано.'],
                                    ['en' => 'I have been promoted!', 'ua' => 'Мене підвищили!'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія <strong>завершилась</strong>, результат важливий зараз.',
                                'examples' => [
                                    ['en' => 'The report has just been submitted.', 'ua' => 'Звіт щойно подано.'],
                                    ['en' => 'The files have already been deleted.', 'ua' => 'Файли вже видалено.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Past Perfect Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>had + been + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The work had been finished before the deadline.', 'ua' => 'Роботу було закінчено до дедлайну.'],
                                    ['en' => 'The tickets had been sold out before we arrived.', 'ua' => 'Квитки були розпродані до нашого приїзду.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Дія завершилась <strong>до іншої минулої</strong> дії.',
                                'examples' => [
                                    ['en' => 'By the time I got there, the problem had been solved.', 'ua' => 'На момент мого приходу проблему вже вирішили.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Future Simple Passive',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>will + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'The results will be announced tomorrow.', 'ua' => 'Результати оголосять завтра.'],
                                    ['en' => 'The meeting will be held next Monday.', 'ua' => 'Зустріч відбудеться наступного понеділка.'],
                                    ['en' => 'You will be contacted soon.', 'ua' => 'З вами скоро зв\'яжуться.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення та питання',
                                'color' => 'sky',
                                'description' => 'Заперечення: <strong>will not (won\'t) + be + V3</strong>. Питання: <strong>Will + S + be + V3?</strong>',
                                'examples' => [
                                    ['en' => 'The project will not be finished on time.', 'ua' => 'Проєкт не буде завершено вчасно.'],
                                    ['en' => 'Will the report be ready by Friday?', 'ua' => 'Звіт буде готовий до п\'ятниці?'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Passive з модальними дієсловами',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => 'Формула: <strong>modal + be + Past Participle (V3)</strong>',
                                'examples' => [
                                    ['en' => 'This task must be done today.', 'ua' => 'Це завдання має бути виконане сьогодні.'],
                                    ['en' => 'The rules should be followed.', 'ua' => 'Правила слід дотримуватися.'],
                                    ['en' => 'The document can be downloaded.', 'ua' => 'Документ можна завантажити.'],
                                ],
                            ],
                            [
                                'label' => 'Різні модальні',
                                'color' => 'sky',
                                'description' => 'Працює з усіма модальними: <strong>can, could, may, might, must, should, have to</strong>.',
                                'examples' => [
                                    ['en' => 'The work has to be finished.', 'ua' => 'Роботу треба закінчити.'],
                                    ['en' => 'Mistakes might be made.', 'ua' => 'Помилки можуть бути допущені.'],
                                    ['en' => 'This could be improved.', 'ua' => 'Це можна покращити.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Зведена таблиця часів у пасиві',
                        'intro' => 'Структури пасивного стану в різних часах:',
                        'rows' => [
                            [
                                'en' => 'Present Simple',
                                'ua' => 'am/is/are + V3',
                                'note' => 'The letter is written.',
                            ],
                            [
                                'en' => 'Past Simple',
                                'ua' => 'was/were + V3',
                                'note' => 'The letter was written.',
                            ],
                            [
                                'en' => 'Present Continuous',
                                'ua' => 'am/is/are + being + V3',
                                'note' => 'The letter is being written.',
                            ],
                            [
                                'en' => 'Past Continuous',
                                'ua' => 'was/were + being + V3',
                                'note' => 'The letter was being written.',
                            ],
                            [
                                'en' => 'Present Perfect',
                                'ua' => 'has/have + been + V3',
                                'note' => 'The letter has been written.',
                            ],
                            [
                                'en' => 'Past Perfect',
                                'ua' => 'had + been + V3',
                                'note' => 'The letter had been written.',
                            ],
                            [
                                'en' => 'Future Simple',
                                'ua' => 'will + be + V3',
                                'note' => 'The letter will be written.',
                            ],
                            [
                                'en' => 'Modals',
                                'ua' => 'modal + be + V3',
                                'note' => 'The letter must be written.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Короткий конспект',
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
