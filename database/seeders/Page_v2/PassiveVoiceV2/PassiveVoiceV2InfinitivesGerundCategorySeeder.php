<?php

namespace Database\Seeders\Page_v2\PassiveVoiceV2;

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
            'subtitle_html' => '<p><strong>Інфінітив та герундій у пасиві (Passive Infinitives and Gerunds)</strong> — складні пасивні конструкції, що використовуються у формальному, академічному та діловому стилі. У цій темі ти вивчиш форми <strong>to be done, to have been done, being done, having been done</strong>.</p>',
            'subtitle_text' => 'Пасивний інфінітив та герундій: to be done, to have been done, being done, having been done. Структура, використання та типові помилки.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Passive Infinitive',
                'Passive Gerund',
                'to be done',
                'to have been done',
                'being done',
                'having been done',
                'Advanced Grammar',
                'Academic Writing',
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
                        'intro' => 'У цій темі ти вивчиш <strong>пасивні форми інфінітива та герундія</strong>. Це ключові конструкції для академічного, наукового та формального письма.',
                        'rules' => [
                            [
                                'label' => 'to be done',
                                'color' => 'emerald',
                                'text' => '<strong>Пасивний інфінітив</strong> — теперішнє/майбутнє:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'to have been done',
                                'color' => 'blue',
                                'text' => '<strong>Перфектний пасивний інфінітив</strong> — попередня дія:',
                                'example' => 'He seems to have been promoted.',
                            ],
                            [
                                'label' => 'being done',
                                'color' => 'rose',
                                'text' => '<strong>Пасивний герундій</strong> — процес:',
                                'example' => 'She hates being interrupted.',
                            ],
                            [
                                'label' => 'having been done',
                                'color' => 'amber',
                                'text' => '<strong>Перфектний пасивний герундій</strong> — попередня дія:',
                                'example' => 'Having been warned, he was careful.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Forms grid - overview - B2
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '1. Чотири основні пасивні форми',
                        'intro' => 'Порівняння чотирьох ключових конструкцій:',
                        'items' => [
                            [
                                'label' => 'to be + V3',
                                'title' => 'Пасивний інфінітив',
                                'subtitle' => 'Теперішня/майбутня дія в пасиві',
                            ],
                            [
                                'label' => 'to have been + V3',
                                'title' => 'Перфектний пасивний інфінітив',
                                'subtitle' => 'Попередня дія, завершена до моменту мовлення',
                            ],
                            [
                                'label' => 'being + V3',
                                'title' => 'Пасивний герундій',
                                'subtitle' => 'Процес, триваюча дія в пасиві',
                            ],
                            [
                                'label' => 'having been + V3',
                                'title' => 'Перфектний пасивний герундій',
                                'subtitle' => 'Дія, що передувала іншій',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Passive Infinitive - B2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '2. Пасивний інфінітив (to be done)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>to be + Past Participle (V3)</strong>. Використовується після багатьох дієслів та в модальних конструкціях.',
                                'examples' => [
                                    ['en' => 'The report needs to be finished by Friday.', 'ua' => 'Звіт потрібно закінчити до п\'ятниці.'],
                                    ['en' => 'I want to be informed immediately.', 'ua' => 'Я хочу, щоб мене негайно повідомили.'],
                                    ['en' => 'This task has to be completed today.', 'ua' => 'Це завдання має бути виконане сьогодні.'],
                                ],
                            ],
                            [
                                'label' => 'Після seem, appear',
                                'color' => 'sky',
                                'description' => 'Після дієслів <strong>seem, appear, happen, turn out</strong>.',
                                'examples' => [
                                    ['en' => 'He appears to be well respected.', 'ua' => 'Він, здається, користується повагою.'],
                                    ['en' => 'The meeting seems to be postponed.', 'ua' => 'Схоже, зустріч перенесли.'],
                                    ['en' => 'She happened to be chosen for the role.', 'ua' => 'Так сталося, що її обрали на цю роль.'],
                                ],
                            ],
                            [
                                'label' => 'Після want, expect',
                                'color' => 'amber',
                                'description' => 'Коли хтось хоче/очікує, щоб <strong>щось було зроблено</strong>.',
                                'examples' => [
                                    ['en' => 'I want this issue to be resolved.', 'ua' => 'Я хочу, щоб це питання вирішили.'],
                                    ['en' => 'They expect the project to be approved.', 'ua' => 'Вони очікують, що проєкт схвалять.'],
                                    ['en' => 'We would like the order to be delivered.', 'ua' => 'Ми б хотіли, щоб замовлення доставили.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Perfect Passive Infinitive - C1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '3. Перфектний пасивний інфінітив (to have been done)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>to have been + Past Participle (V3)</strong>. Вказує на дію, що <strong>відбулася раніше</strong> відносно моменту мовлення.',
                                'examples' => [
                                    ['en' => 'He seems to have been promoted.', 'ua' => 'Здається, його підвищили.'],
                                    ['en' => 'The documents appear to have been lost.', 'ua' => 'Документи, схоже, були втрачені.'],
                                    ['en' => 'She claims to have been invited.', 'ua' => 'Вона стверджує, що її запросили.'],
                                ],
                            ],
                            [
                                'label' => 'Reporting structures',
                                'color' => 'sky',
                                'description' => 'У безособових пасивних конструкціях для <strong>припущень про минуле</strong>.',
                                'examples' => [
                                    ['en' => 'He is believed to have been kidnapped.', 'ua' => 'Вважається, що його викрали.'],
                                    ['en' => 'The painting is thought to have been stolen.', 'ua' => 'Вважається, що картину вкрали.'],
                                    ['en' => 'She is reported to have been seen in Paris.', 'ua' => 'Повідомляється, що її бачили в Парижі.'],
                                ],
                            ],
                            [
                                'label' => 'Після modal + have',
                                'color' => 'amber',
                                'description' => 'Після <strong>must have, could have, should have</strong> — припущення про минуле.',
                                'examples' => [
                                    ['en' => 'The email must have been sent.', 'ua' => 'Імейл, мабуть, було відправлено.'],
                                    ['en' => 'The mistake could have been avoided.', 'ua' => 'Помилки можна було уникнути.'],
                                    ['en' => 'This should have been done earlier.', 'ua' => 'Це мали зробити раніше.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Passive Gerund - B2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '4. Пасивний герундій (being done)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>being + Past Participle (V3)</strong>. Використовується після прийменників та дієслів, що вимагають герундія.',
                                'examples' => [
                                    ['en' => 'She hates being interrupted.', 'ua' => 'Вона ненавидить, коли її перебивають.'],
                                    ['en' => 'I remember being taught this rule.', 'ua' => 'Я пам\'ятаю, як мене вчили цього правила.'],
                                    ['en' => 'He denied being involved in the incident.', 'ua' => 'Він заперечив свою причетність до інциденту.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'sky',
                                'description' => 'Після <strong>without, after, before, on, by, despite</strong>.',
                                'examples' => [
                                    ['en' => 'He left without being noticed.', 'ua' => 'Він пішов непомітно.'],
                                    ['en' => 'After being rejected, she tried again.', 'ua' => 'Після того як її відхилили, вона спробувала знову.'],
                                    ['en' => 'Despite being warned, he continued.', 'ua' => 'Попри попередження, він продовжив.'],
                                ],
                            ],
                            [
                                'label' => 'Після like, enjoy, avoid',
                                'color' => 'amber',
                                'description' => 'Після дієслів, що вимагають <strong>-ing форми</strong>.',
                                'examples' => [
                                    ['en' => 'Nobody likes being criticized.', 'ua' => 'Ніхто не любить, коли його критикують.'],
                                    ['en' => 'He enjoys being praised.', 'ua' => 'Йому подобається, коли його хвалять.'],
                                    ['en' => 'She avoids being seen in public.', 'ua' => 'Вона уникає публічності.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Perfect Passive Gerund - C1
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'C1',
                    'body' => json_encode([
                        'title' => '5. Перфектний пасивний герундій (having been done)',
                        'sections' => [
                            [
                                'label' => 'Структура',
                                'color' => 'emerald',
                                'description' => '<strong>having been + Past Participle (V3)</strong>. Для дій, що <strong>передували іншим</strong> (причина → наслідок).',
                                'examples' => [
                                    ['en' => 'Having been warned, he was more careful.', 'ua' => 'Отримавши попередження, він був обережнішим.'],
                                    ['en' => 'Having been told the truth, she felt relieved.', 'ua' => 'Дізнавшись правду, вона відчула полегшення.'],
                                    ['en' => 'Having been approved, the project will begin.', 'ua' => 'Після схвалення проєкт розпочнеться.'],
                                ],
                            ],
                            [
                                'label' => 'Формальний стиль',
                                'color' => 'sky',
                                'description' => 'Часто в <strong>академічному, офіційному</strong> та письмовому мовленні.',
                                'examples' => [
                                    ['en' => 'Having been elected, she took office.', 'ua' => 'Після обрання вона обійняла посаду.'],
                                    ['en' => 'Having been reviewed, the document was signed.', 'ua' => 'Після перегляду документ підписали.'],
                                    ['en' => 'Having been trained properly, the team succeeded.', 'ua' => 'Отримавши належне навчання, команда досягла успіху.'],
                                ],
                            ],
                            [
                                'label' => 'Після дієслів пам\'яті',
                                'color' => 'amber',
                                'description' => 'Після <strong>remember, regret, deny, admit</strong>.',
                                'examples' => [
                                    ['en' => 'She regrets having been so rude.', 'ua' => 'Вона шкодує, що була такою грубою.'],
                                    ['en' => 'He admits having been mistaken.', 'ua' => 'Він визнає, що помилявся.'],
                                    ['en' => 'I remember having been told about this.', 'ua' => 'Я пам\'ятаю, що мені про це говорили.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Common patterns - B2
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '6. Найчастіші шаблони використання',
                        'sections' => [
                            [
                                'label' => 'need/want/require + to be done',
                                'color' => 'emerald',
                                'description' => '<strong>Need/want/require + to be + V3</strong> — потреба у виконанні.',
                                'examples' => [
                                    ['en' => 'This problem needs to be solved.', 'ua' => 'Цю проблему потрібно вирішити.'],
                                    ['en' => 'The contract needs to be signed.', 'ua' => 'Контракт потрібно підписати.'],
                                    ['en' => 'The issue requires to be addressed.', 'ua' => 'Це питання вимагає вирішення.'],
                                ],
                            ],
                            [
                                'label' => 'seem/appear + to be done',
                                'color' => 'blue',
                                'description' => '<strong>Seem/appear + to be + V3</strong> — враження, здогадка.',
                                'examples' => [
                                    ['en' => 'The door seems to be locked.', 'ua' => 'Здається, двері замкнені.'],
                                    ['en' => 'He appears to be respected by everyone.', 'ua' => 'Здається, його всі поважають.'],
                                ],
                            ],
                            [
                                'label' => 'is said/believed + to be/have been',
                                'color' => 'sky',
                                'description' => '<strong>Reporting passive</strong> — повідомлення, чутки.',
                                'examples' => [
                                    ['en' => 'He is said to be a genius.', 'ua' => 'Кажуть, що він геній.'],
                                    ['en' => 'She is believed to have left the country.', 'ua' => 'Вважається, що вона покинула країну.'],
                                ],
                            ],
                            [
                                'label' => 'hate/like/enjoy + being done',
                                'color' => 'amber',
                                'description' => '<strong>Feeling verbs + being + V3</strong> — реакція на дію.',
                                'examples' => [
                                    ['en' => 'She hates being ignored.', 'ua' => 'Вона ненавидить, коли її ігнорують.'],
                                    ['en' => 'Children love being praised.', 'ua' => 'Діти люблять, коли їх хвалять.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Comparison table - B2
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '7. Зведена таблиця форм',
                        'intro' => 'Порівняння всіх пасивних форм з інфінітивами та герундіями:',
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
                        'warning' => '📌 <strong>Перфектні форми</strong> (to have been, having been) вказують на дію, що передувала іншій!',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Active vs Passive infinitive/gerund - B2
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '8. Active vs Passive: інфінітив та герундій',
                        'intro' => 'Порівняння активних та пасивних форм:',
                        'rows' => [
                            [
                                'en' => 'Active Infinitive',
                                'ua' => 'to do',
                                'note' => 'I want to finish the work.',
                            ],
                            [
                                'en' => 'Passive Infinitive',
                                'ua' => 'to be done',
                                'note' => 'The work needs to be finished.',
                            ],
                            [
                                'en' => 'Perfect Active Infinitive',
                                'ua' => 'to have done',
                                'note' => 'He seems to have finished.',
                            ],
                            [
                                'en' => 'Perfect Passive Infinitive',
                                'ua' => 'to have been done',
                                'note' => 'The work seems to have been finished.',
                            ],
                            [
                                'en' => 'Active Gerund',
                                'ua' => 'doing',
                                'note' => 'I hate interrupting people.',
                            ],
                            [
                                'en' => 'Passive Gerund',
                                'ua' => 'being done',
                                'note' => 'I hate being interrupted.',
                            ],
                            [
                                'en' => 'Perfect Active Gerund',
                                'ua' => 'having done',
                                'note' => 'Having finished, he left.',
                            ],
                            [
                                'en' => 'Perfect Passive Gerund',
                                'ua' => 'having been done',
                                'note' => 'Having been finished, the work was submitted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Mistakes grid - B2
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '9. Типові помилки',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Плутанина to be done та being done.',
                                'wrong' => 'I enjoy to be praised.',
                                'right' => '✅ I enjoy being praised.',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск been у перфектних формах.',
                                'wrong' => 'He seems to have promoted.',
                                'right' => '✅ He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильний порядок слів.',
                                'wrong' => 'Having warned been, he was careful.',
                                'right' => '✅ Having been warned, he was careful.',
                            ],
                            [
                                'label' => 'Помилка 4',
                                'color' => 'rose',
                                'title' => 'Активна форма замість пасивної.',
                                'wrong' => 'The report needs finishing.',
                                'right' => '✅ The report needs to be finished.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                // Summary list - B2
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'level' => 'B2',
                    'body' => json_encode([
                        'title' => '10. Короткий конспект',
                        'items' => [
                            '<strong>to be + V3</strong> (пасивний інфінітив): The report needs to be finished.',
                            '<strong>to have been + V3</strong> (перфектний пасивний інфінітив): He seems to have been promoted.',
                            '<strong>being + V3</strong> (пасивний герундій): She hates being interrupted.',
                            '<strong>having been + V3</strong> (перфектний пасивний герундій): Having been warned...',
                            '<strong>Перфектні форми</strong> вказують на попередню дію.',
                            'Використовуй <strong>being + V3</strong> після прийменників та дієслів з -ing.',
                            'Використовуй <strong>to be + V3</strong> після need, want, expect, seem, appear.',
                            'Ці форми типові для <strong>академічного та формального</strong> стилю.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
