<?php

namespace Database\Seeders\Page_v2\PassiveVoice;

use App\Models\PageCategory;
use App\Models\Tag;
use App\Models\TextBlock;
use App\Support\Database\Seeder;

class PassiveVoiceAdvancedTheorySeeder extends Seeder
{
    protected function slug(): string
    {
        return 'passive-voice-advanced';
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
            'title' => 'Passive Voice: Просунутий рівень — Складні конструкції',
            'subtitle_html' => '<p><strong>Просунутий рівень пасивного стану</strong> — це складні та рідкісні конструкції, що використовуються в академічному, науковому та формальному письмі. Тут ти вивчиш пасив з інфінітивами, герундіями, перфектними формами та особливі випадки.</p>',
            'subtitle_text' => 'Складні конструкції пасивного стану: пасив з інфінітивами, герундіями, перфектними формами, need/want passive та стилістичні особливості.',
            'locale' => 'uk',
            'tags' => [
                'Passive Voice',
                'Пасивний стан',
                'Perfect Infinitive Passive',
                'Gerund Passive',
                'Advanced Grammar',
                'Academic Writing',
                'C1',
                'Theory',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'C1',
                        'intro' => 'У цій темі ти вивчиш <strong>складні конструкції пасивного стану</strong>: пасив з інфінітивами та герундіями, перфектні форми, need/want passive та стилістичні нюанси.',
                        'rules' => [
                            [
                                'label' => 'Infinitive',
                                'color' => 'emerald',
                                'text' => 'Пасивний інфінітив: <strong>to be + V3</strong>:',
                                'example' => 'The report needs to be finished.',
                            ],
                            [
                                'label' => 'Perfect',
                                'color' => 'blue',
                                'text' => 'Перфектний пасив: <strong>to have been + V3</strong>:',
                                'example' => 'He seems to have been promoted.',
                            ],
                            [
                                'label' => 'Gerund',
                                'color' => 'rose',
                                'text' => 'Пасивний герундій: <strong>being + V3</strong>:',
                                'example' => 'She hates being interrupted.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Пасивний інфінітив (Passive Infinitive)',
                        'sections' => [
                            [
                                'label' => 'To be + V3',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>to be + Past Participle</strong>. Використовується після модальних та деяких дієслів.',
                                'examples' => [
                                    ['en' => 'The report needs to be finished by Friday.', 'ua' => 'Звіт потрібно закінчити до п\'ятниці.'],
                                    ['en' => 'I want to be informed immediately.', 'ua' => 'Я хочу, щоб мене негайно повідомили.'],
                                    ['en' => 'The task is expected to be completed.', 'ua' => 'Очікується, що завдання буде виконано.'],
                                ],
                            ],
                            [
                                'label' => 'З модальними',
                                'color' => 'sky',
                                'description' => 'Після <strong>seem, appear, happen, turn out</strong> та модальних дієслів.',
                                'examples' => [
                                    ['en' => 'He appears to be well respected.', 'ua' => 'Він, здається, користується повагою.'],
                                    ['en' => 'The meeting seems to be postponed.', 'ua' => 'Схоже, зустріч перенесли.'],
                                    ['en' => 'This document ought to be signed.', 'ua' => 'Цей документ слід підписати.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Перфектний пасивний інфінітив',
                        'sections' => [
                            [
                                'label' => 'To have been + V3',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>to have been + Past Participle</strong>. Вказує на дію, що відбулася раніше.',
                                'examples' => [
                                    ['en' => 'He seems to have been promoted.', 'ua' => 'Здається, його підвищили.'],
                                    ['en' => 'The documents appear to have been lost.', 'ua' => 'Документи, схоже, були втрачені.'],
                                    ['en' => 'She claims to have been invited.', 'ua' => 'Вона стверджує, що її запросили.'],
                                ],
                            ],
                            [
                                'label' => 'Використання',
                                'color' => 'sky',
                                'description' => 'Для припущень про <strong>минулі події</strong> з точки зору теперішнього.',
                                'examples' => [
                                    ['en' => 'He is believed to have been kidnapped.', 'ua' => 'Вважається, що його викрали.'],
                                    ['en' => 'The painting is thought to have been stolen decades ago.', 'ua' => 'Вважається, що картину вкрали десятиліття тому.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Пасивний герундій (Passive Gerund)',
                        'sections' => [
                            [
                                'label' => 'Being + V3',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>being + Past Participle</strong>. Після прийменників та деяких дієслів.',
                                'examples' => [
                                    ['en' => 'She hates being interrupted.', 'ua' => 'Вона ненавидить, коли її перебивають.'],
                                    ['en' => 'I remember being taught this rule.', 'ua' => 'Я пам\'ятаю, як мене вчили цього правила.'],
                                    ['en' => 'He denied being involved.', 'ua' => 'Він заперечив свою причетність.'],
                                ],
                            ],
                            [
                                'label' => 'Після прийменників',
                                'color' => 'sky',
                                'description' => 'Часто після <strong>without, after, before, on, by</strong>.',
                                'examples' => [
                                    ['en' => 'He left without being noticed.', 'ua' => 'Він пішов непомітно.'],
                                    ['en' => 'After being rejected, she tried again.', 'ua' => 'Після того як її відхилили, вона спробувала знову.'],
                                    ['en' => 'The problem was solved by being discussed openly.', 'ua' => 'Проблему вирішили завдяки відкритому обговоренню.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Перфектний пасивний герундій',
                        'sections' => [
                            [
                                'label' => 'Having been + V3',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>having been + Past Participle</strong>. Для дій, що передували іншим.',
                                'examples' => [
                                    ['en' => 'Having been warned, he was more careful.', 'ua' => 'Отримавши попередження, він був обережнішим.'],
                                    ['en' => 'She regrets having been so rude.', 'ua' => 'Вона шкодує, що була такою грубою.'],
                                    ['en' => 'Having been told the truth, he felt relieved.', 'ua' => 'Дізнавшись правду, він відчув полегшення.'],
                                ],
                            ],
                            [
                                'label' => 'У формальному стилі',
                                'color' => 'sky',
                                'description' => 'Часто використовується в <strong>академічному та офіційному</strong> письмі.',
                                'examples' => [
                                    ['en' => 'Having been approved, the project will begin.', 'ua' => 'Після схвалення проєкт розпочнеться.'],
                                    ['en' => 'Having been elected, she took office immediately.', 'ua' => 'Після обрання вона негайно обійняла посаду.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Need/Want + Passive Gerund або Infinitive',
                        'sections' => [
                            [
                                'label' => 'Need + V-ing (passive meaning)',
                                'color' => 'emerald',
                                'description' => 'Структура: <strong>need + V-ing</strong> = <strong>need to be + V3</strong>. Пасивне значення.',
                                'examples' => [
                                    ['en' => 'The car needs washing. = The car needs to be washed.', 'ua' => 'Машину потрібно помити.'],
                                    ['en' => 'The report needs checking.', 'ua' => 'Звіт потребує перевірки.'],
                                    ['en' => 'Your hair needs cutting.', 'ua' => 'Тобі потрібно підстригтися.'],
                                ],
                            ],
                            [
                                'label' => 'Want + V-ing (British)',
                                'color' => 'sky',
                                'description' => 'У британській англійській: <strong>want + V-ing</strong> = пасивне значення.',
                                'examples' => [
                                    ['en' => 'The windows want cleaning. (British)', 'ua' => 'Вікна потребують миття.'],
                                    ['en' => 'This essay wants rewriting.', 'ua' => 'Цей есей потребує переписування.'],
                                ],
                            ],
                            [
                                'label' => 'Require + V-ing',
                                'color' => 'amber',
                                'description' => '<strong>Require + V-ing</strong> також має пасивне значення.',
                                'examples' => [
                                    ['en' => 'The problem requires investigating.', 'ua' => 'Проблема вимагає розслідування.'],
                                    ['en' => 'This matter requires careful handling.', 'ua' => 'Це питання вимагає обережного поводження.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Пасив у складнопідрядних реченнях',
                        'sections' => [
                            [
                                'label' => 'Relative clauses',
                                'color' => 'emerald',
                                'description' => 'Пасив у підрядних означальних реченнях.',
                                'examples' => [
                                    ['en' => 'The book that was written by Orwell...', 'ua' => 'Книга, що була написана Орвеллом...'],
                                    ['en' => 'The project which is being developed...', 'ua' => 'Проєкт, що розробляється...'],
                                    ['en' => 'The issues that have been raised...', 'ua' => 'Питання, що були підняті...'],
                                ],
                            ],
                            [
                                'label' => 'Reduced relative clauses',
                                'color' => 'sky',
                                'description' => 'Скорочення з <strong>Past Participle</strong> як означенням.',
                                'examples' => [
                                    ['en' => 'The book written by Orwell... (= that was written)', 'ua' => 'Книга, написана Орвеллом...'],
                                    ['en' => 'The issues raised in the meeting...', 'ua' => 'Питання, підняті на зустрічі...'],
                                    ['en' => 'Products made in Italy...', 'ua' => 'Товари, вироблені в Італії...'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Стилістичні особливості пасиву',
                        'sections' => [
                            [
                                'label' => 'Науковий стиль',
                                'color' => 'emerald',
                                'description' => 'У <strong>наукових текстах</strong> пасив підкреслює об\'єктивність.',
                                'examples' => [
                                    ['en' => 'The experiment was conducted over six months.', 'ua' => 'Експеримент проводився протягом шести місяців.'],
                                    ['en' => 'The data were collected from 500 participants.', 'ua' => 'Дані було зібрано від 500 учасників.'],
                                    ['en' => 'It has been demonstrated that...', 'ua' => 'Було продемонстровано, що...'],
                                ],
                            ],
                            [
                                'label' => 'Офіційний стиль',
                                'color' => 'sky',
                                'description' => 'У <strong>ділових документах</strong> — формальність та безособовість.',
                                'examples' => [
                                    ['en' => 'Your application has been received.', 'ua' => 'Вашу заявку отримано.'],
                                    ['en' => 'Payment is required within 30 days.', 'ua' => 'Оплата вимагається протягом 30 днів.'],
                                    ['en' => 'Applicants will be notified by email.', 'ua' => 'Заявників повідомлять електронною поштою.'],
                                ],
                            ],
                            [
                                'label' => 'Коли НЕ використовувати',
                                'color' => 'amber',
                                'description' => 'Уникайте надмірного пасиву — він може зробити текст <strong>важким для сприйняття</strong>.',
                                'examples' => [
                                    ['en' => '❌ The ball was kicked by the player.', 'ua' => '(краще: The player kicked the ball.)'],
                                    ['en' => '❌ Mistakes were made by me.', 'ua' => '(краще: I made mistakes.)'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '8. Зведена таблиця складних форм',
                        'intro' => 'Всі складні конструкції пасивного стану:',
                        'rows' => [
                            [
                                'en' => 'Passive Infinitive',
                                'ua' => 'to be + V3',
                                'note' => 'The work needs to be done.',
                            ],
                            [
                                'en' => 'Perfect Passive Infinitive',
                                'ua' => 'to have been + V3',
                                'note' => 'He seems to have been promoted.',
                            ],
                            [
                                'en' => 'Passive Gerund',
                                'ua' => 'being + V3',
                                'note' => 'She hates being interrupted.',
                            ],
                            [
                                'en' => 'Perfect Passive Gerund',
                                'ua' => 'having been + V3',
                                'note' => 'Having been warned, he left.',
                            ],
                            [
                                'en' => 'Need + V-ing (passive)',
                                'ua' => 'need + V-ing',
                                'note' => 'The car needs washing.',
                            ],
                            [
                                'en' => 'Reduced Relative',
                                'ua' => 'V3 як означення',
                                'note' => 'The book written by Orwell...',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '9. Короткий конспект',
                        'items' => [
                            '<strong>Пасивний інфінітив</strong>: to be + V3 — The report needs to be finished.',
                            '<strong>Перфектний пасивний інфінітив</strong>: to have been + V3 — He seems to have been promoted.',
                            '<strong>Пасивний герундій</strong>: being + V3 — She hates being interrupted.',
                            '<strong>Перфектний пасивний герундій</strong>: having been + V3 — Having been warned...',
                            '<strong>Need/want/require + V-ing</strong> має пасивне значення: The car needs washing.',
                            '<strong>Reduced relative clauses</strong>: V3 як означення — The book written by Orwell.',
                            'Пасив часто використовується в <strong>наукових та офіційних</strong> текстах.',
                            'Уникайте надмірного пасиву — він може ускладнити текст.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
