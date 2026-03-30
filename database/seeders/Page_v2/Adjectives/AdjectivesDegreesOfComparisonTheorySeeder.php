<?php

namespace Database\Seeders\Page_v2\Adjectives;

use Database\Seeders\Page_v2\Adjectives\AdjectivePageSeeder;

class AdjectivesDegreesOfComparisonTheorySeeder extends AdjectivePageSeeder
{
    protected function slug(): string
    {
        return 'theory-degrees-of-comparison';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function category(): array
    {
        return [
            'slug' => 'prykmetniky-ta-pryslinknyky',
            'title' => 'Прикметники та прислівники',
            'language' => 'uk',
        ];
    }

    protected function page(): array
    {
        return [
            'title' => 'Degrees of Comparison — ступені порівняння прикметників і прислівників',
            'subtitle_html' => '<p>Ступені порівняння показують, <strong>як одні якості співвідносяться з іншими</strong>. Є три ступені: звичайний, вищий (comparative) і найвищий (superlative).</p>',
            'subtitle_text' => 'Comparative показує різницю між двома предметами, superlative — виділяє крайній ступінь у групі.',
            'locale' => 'uk',
            'tags' => [
                'Degrees of Comparison',
                'Comparative / Superlative Choice',
                'Comparative + than Pattern',
                'As ... as Equality',
                'Superlative Formation (-est / most / least)',
                'Irregular Comparative Forms (good/bad/far)',
                'Quantity Comparisons (much/many/less/fewer)',
                'Comparatives and Superlatives Practice',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти навчишся правильно утворювати та вживати <strong>comparative</strong> (вищий ступінь) і <strong>superlative</strong> (найвищий ступінь) прикметників і прислівників.',
                        'rules' => [
                            [
                                'label' => 'Base form',
                                'color' => 'emerald',
                                'text' => 'Опис без порівняння:',
                                'example' => 'This road is narrow.',
                            ],
                            [
                                'label' => 'Comparative',
                                'color' => 'blue',
                                'text' => 'Порівняння двох речей з <em>than</em>:',
                                'example' => 'This road is narrower than that one.',
                            ],
                            [
                                'label' => 'Superlative',
                                'color' => 'rose',
                                'text' => 'Виділення одного з групи з <em>the</em>:',
                                'example' => 'This is the narrowest street in town.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Короткі слова (1–2 склади)',
                        'intro' => 'Для коротких прикметників додаємо закінчення <code class="font-mono text-xs">-er</code> / <code class="font-mono text-xs">-est</code>:',
                        'items' => [
                            ['label' => 'Загальне правило', 'title' => '-er / -est', 'subtitle' => 'fast → faster → the fastest'],
                            ['label' => 'Закінчення на -y', 'title' => '-ier / -iest', 'subtitle' => 'happy → happier → the happiest'],
                            ['label' => 'Подвоєння', 'title' => 'big → bigger', 'subtitle' => 'Якщо: приголосний + голосний + приголосний'],
                            ['label' => 'Закінчення на -e', 'title' => '-r / -st', 'subtitle' => 'large → larger → the largest'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Довгі слова й прислівники',
                        'sections' => [
                            [
                                'label' => 'More / Most',
                                'color' => 'emerald',
                                'description' => 'Прикметники з 3+ складами та більшість прислівників використовують <strong>more / most</strong>.',
                                'examples' => [
                                    ['en' => 'interesting → more interesting → the most interesting', 'ua' => 'цікавий'],
                                    ['en' => 'quickly → more quickly → the most quickly', 'ua' => 'швидко'],
                                ],
                            ],
                            [
                                'label' => 'Less / Least',
                                'color' => 'rose',
                                'description' => 'Для протилежного значення використовуємо <strong>less / least</strong>.',
                                'examples' => [
                                    ['en' => 'dangerous → less dangerous → the least dangerous', 'ua' => 'небезпечний'],
                                ],
                            ],
                            [
                                'label' => 'Двоскладові слова',
                                'color' => 'sky',
                                'description' => 'Деякі допускають обидві форми (закінчення на -y, -ow, -er):',
                                'examples' => [
                                    ['en' => 'shallow → shallower / more shallow', 'ua' => 'мілкий'],
                                    ['en' => 'clever → cleverer / more clever', 'ua' => 'розумний'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Неправильні форми',
                        'intro' => 'Деякі прикметники та прислівники мають особливі форми ступенів порівняння:',
                        'rows' => [
                            [
                                'en' => 'good / well',
                                'ua' => 'хороший / добре',
                                'note' => 'better → the best',
                            ],
                            [
                                'en' => 'bad / badly',
                                'ua' => 'поганий / погано',
                                'note' => 'worse → the worst',
                            ],
                            [
                                'en' => 'far',
                                'ua' => 'далекий / далеко',
                                'note' => 'farther/further → the farthest/furthest',
                            ],
                            [
                                'en' => 'little (amount)',
                                'ua' => 'мало',
                                'note' => 'less → the least',
                            ],
                            [
                                'en' => 'many / much',
                                'ua' => 'багато',
                                'note' => 'more → the most',
                            ],
                        ],
                        'warning' => '💡 <em>Further</em> частіше про «додатковий» або «далі в часі», а <em>farther</em> — про фізичну відстань.',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Підсилення та послаблення',
                        'sections' => [
                            [
                                'label' => 'Підсилення comparative',
                                'color' => 'emerald',
                                'description' => 'Використовуй <strong>much / way / a lot / far</strong> перед comparative:',
                                'examples' => [
                                    ['en' => 'This is much better than before.', 'ua' => 'Це набагато краще, ніж раніше.'],
                                    ['en' => 'He runs way faster than me.', 'ua' => 'Він бігає набагато швидше за мене.'],
                                ],
                            ],
                            [
                                'label' => 'Підсилення superlative',
                                'color' => 'blue',
                                'description' => 'Використовуй <strong>by far / easily</strong> перед superlative:',
                                'examples' => [
                                    ['en' => 'She is by far the best student.', 'ua' => 'Вона безперечно найкраща студентка.'],
                                ],
                            ],
                            [
                                'label' => 'Послаблення',
                                'color' => 'sky',
                                'description' => 'Використовуй <strong>slightly / a bit / a little</strong> для незначної різниці:',
                                'examples' => [
                                    ['en' => 'This is slightly cheaper.', 'ua' => 'Це трохи дешевше.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Конструкції порівняння',
                        'sections' => [
                            [
                                'label' => 'As ... as',
                                'color' => 'emerald',
                                'description' => 'Для рівності використовуй <strong>as + adjective/adverb + as</strong>:',
                                'examples' => [
                                    ['en' => 'She is as tall as her brother.', 'ua' => 'Вона така ж висока, як її брат.'],
                                    ['en' => 'He runs as fast as me.', 'ua' => 'Він бігає так само швидко, як я.'],
                                ],
                            ],
                            [
                                'label' => 'Not as/so ... as',
                                'color' => 'rose',
                                'description' => 'Для нерівності використовуй <strong>not as/so + adjective + as</strong>:',
                                'examples' => [
                                    ['en' => 'This book is not as interesting as that one.', 'ua' => 'Ця книга не така цікава, як та.'],
                                ],
                            ],
                            [
                                'label' => 'The + superlative + in/of',
                                'color' => 'sky',
                                'description' => 'Для виділення з групи:',
                                'examples' => [
                                    ['en' => 'He is the tallest in the class.', 'ua' => 'Він найвищий у класі.'],
                                    ['en' => 'This is the most useful of all.', 'ua' => 'Це найкорисніше з усіх.'],
                                ],
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Подвійні маркери порівняння.',
                                'wrong' => 'more better / the most fastest',
                                'right' => '✅ <span class="font-mono">better / the fastest</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Пропуск <strong>the</strong> перед superlative.',
                                'wrong' => 'He is tallest in the class.',
                                'right' => '✅ <span class="font-mono">He is the tallest in the class.</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Неправильне узгодження з <strong>than</strong>.',
                                'wrong' => 'My car is more reliable than your.',
                                'right' => '✅ <span class="font-mono">My car is more reliable than yours.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '7. Короткий конспект',
                        'items' => [
                            '<strong>Короткі слова (1–2 склади)</strong> — додаємо <span class="font-mono text-xs">-er / -est</span>: fast → faster → the fastest.',
                            '<strong>Довгі слова (3+ складів)</strong> — використовуємо <span class="font-mono text-xs">more / most</span>: interesting → more interesting.',
                            '<strong>Неправильні форми</strong> — good/better/best, bad/worse/worst, far/farther/farthest.',
                            '<strong>Підсилення</strong> — much/way/a lot + comparative; by far + superlative.',
                            '<strong>Рівність</strong> — as + adjective + as; нерівність — not as/so ... as.',
                            '<strong>Уникай</strong> подвійних маркерів та не забувай <em>the</em> перед superlative.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з категорії Прикметники',
                        'items' => [
                            [
                                'label' => 'Degrees of Comparison',
                                'url' => '/theory/adjectives/theory-degrees-of-comparison',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
