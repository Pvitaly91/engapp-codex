<?php

namespace Database\Seeders\Page_v2\Articles\SomeAny;

use Database\Seeders\Pages\Articles\ArticlePageSeeder;

class SomeAnyPlacesTheorySeeder extends ArticlePageSeeder
{
    protected function slug(): string
    {
        return 'theory-some-any-places';
    }

    protected function type(): ?string
    {
        return 'theory';
    }

    protected function page(): array
    {
        return [
            'title' => 'Somewhere / Anywhere / Nowhere / Everywhere — місця з Some / Any',
            'subtitle_html' => '<p><strong>Somewhere / anywhere / nowhere / everywhere</strong> — готовий набір, щоб говорити про невизначені місця.</p>',
            'subtitle_text' => 'Як правильно вживати somewhere, anywhere, nowhere та everywhere у різних типах речень.',
            'locale' => 'uk',
            'category' => [
                'slug' => 'some-any',
                'title' => 'Some / Any',
                'language' => 'uk',
            ],
            'tags' => [
                'Some or Any',
                'Some/Any/No/Every Compounds (AI)',
                'Something / Anything / Nothing Exercises',
                'Indefinite Pronoun Compounds',
                'Somewhere anywhere nowhere forms',
                'Places-focused compounds',
                'Location reference nuance',
            ],
            'blocks' => [
                [
                    'type' => 'hero',
                    'column' => 'header',
                    'body' => json_encode([
                        'level' => 'A2',
                        'intro' => 'У цій темі ти навчишся правильно вживати <strong>somewhere, anywhere, nowhere, everywhere</strong> у ствердженнях, запереченнях та питаннях.',
                        'rules' => [
                            [
                                'label' => 'Ствердження',
                                'color' => 'emerald',
                                'text' => '<strong>Somewhere</strong> — «десь» у ствердженнях:',
                                'example' => 'I live somewhere near here.',
                            ],
                            [
                                'label' => 'Заперечення / питання',
                                'color' => 'blue',
                                'text' => '<strong>Anywhere</strong> — у запереченнях і питаннях:',
                                'example' => 'I don’t live anywhere near here.',
                            ],
                            [
                                'label' => 'Заперечення одним словом',
                                'color' => 'rose',
                                'text' => '<strong>Nowhere</strong> — заперечення одним словом:',
                                'example' => 'I live nowhere near here.',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'forms-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '1. Форми та переклад',
                        'intro' => 'Ось чотири основні слова для позначення місця з <code class="font-mono text-xs">some / any</code>:',
                        'items' => [
                            ['label' => 'Ствердження', 'title' => 'somewhere', 'subtitle' => 'десь'],
                            ['label' => 'Заперечення / питання', 'title' => 'anywhere', 'subtitle' => 'десь / ніде (в запереченнях та питаннях)'],
                            ['label' => 'Заперечення', 'title' => 'nowhere', 'subtitle' => 'ніде'],
                            ['label' => 'Узагальнення', 'title' => 'everywhere', 'subtitle' => 'всюди'],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'usage-panels',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '2. Вживання у ствердженнях, запереченнях і питаннях',
                        'sections' => [
                            [
                                'label' => 'Ствердження (Affirmative)',
                                'color' => 'emerald',
                                'description' => 'У ствердженнях частіше вживаємо <strong>somewhere</strong> та <strong>everywhere</strong>.',
                                'examples' => [
                                    ['en' => 'He is somewhere in the building.', 'ua' => 'Він десь у будівлі.'],
                                    ['en' => 'You can see flowers everywhere in spring.', 'ua' => 'Навесні квіти видно всюди.'],
                                ],
                            ],
                            [
                                'label' => 'Заперечення (Negative)',
                                'color' => 'rose',
                                'description' => 'У запереченні вживаємо <strong>anywhere</strong> з <code class="font-mono text-xs">don’t/doesn’t</code> або <strong>nowhere</strong> без додаткового not.',
                                'examples' => [
                                    ['en' => 'I don’t go anywhere on Sundays.', 'ua' => 'Я нікуди не ходжу по неділях.'],
                                    ['en' => 'I go nowhere on Sundays.', 'ua' => 'Я нікуди не ходжу по неділях.'],
                                ],
                                'note' => '❗ Уникай подвійного заперечення типу <span class="line-through">I don’t go nowhere</span>.',
                            ],
                            [
                                'label' => 'Питання (Questions)',
                                'color' => 'sky',
                                'description' => 'У питаннях зазвичай використовуємо <strong>anywhere</strong>.',
                                'examples' => [
                                    ['en' => 'Do you go anywhere on Sundays?', 'ua' => 'Ти кудись ходиш по неділях?'],
                                    ['en' => 'Is there anywhere to eat near here?', 'ua' => 'Тут поруч є десь поїсти?'],
                                ],
                                'note' => '<span class="font-semibold">Somewhere</span> у питанні звучить, ніби ти очікуєш «якесь конкретне місце».',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'comparison-table',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '3. Anywhere vs Nowhere',
                        'intro' => '<strong>Дві різні граматичні форми — один і той самий зміст</strong> у нормальній англійській:',
                        'rows' => [
                            [
                                'en' => 'I don’t go anywhere on Sundays.',
                                'ua' => 'Я нікуди не ходжу по неділях.',
                                'note' => 'Заперечення через <code class="font-mono text-xs">don’t</code> + <strong>anywhere</strong>.',
                            ],
                            [
                                'en' => 'I go nowhere on Sundays.',
                                'ua' => 'Я нікуди не ходжу по неділях.',
                                'note' => 'Заперечення одним словом <strong>nowhere</strong>, без <code class="font-mono text-xs">don’t</code>.',
                            ],
                        ],
                        'warning' => '❗ <span class="line-through">I don’t go nowhere</span> — граматично неправильно в стандартній англійській (це подвійне заперечення).',
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'mistakes-grid',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '4. Типові помилки україномовних',
                        'items' => [
                            [
                                'label' => 'Помилка 1',
                                'color' => 'rose',
                                'title' => 'Подвійне заперечення як в українській.',
                                'wrong' => 'I don’t go nowhere.',
                                'right' => '✅ <span class="font-mono">I don’t go anywhere.</span> / <span class="font-mono">I go nowhere.</span>',
                            ],
                            [
                                'label' => 'Помилка 2',
                                'color' => 'amber',
                                'title' => 'Плутанина між <strong>somewhere</strong> та <strong>anywhere</strong> у питаннях.',
                                'hint' => '<span class="font-mono">Do you go somewhere on Sundays?</span> — звучить як «у якесь конкретне місце».<br>Зазвичай краще: <span class="font-mono">Do you go anywhere on Sundays?</span>',
                            ],
                            [
                                'label' => 'Помилка 3',
                                'color' => 'sky',
                                'title' => 'Використання <strong>everywhere</strong> замість <strong>somewhere</strong>.',
                                'hint' => '<span class="font-mono">I want to go everywhere in London.</span> — я хочу побувати всюди в Лондоні.<br>Якщо просто «кудись»: <span class="font-mono">I want to go somewhere in London.</span>',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'summary-list',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '5. Короткий конспект',
                        'items' => [
                            '<strong>Somewhere</strong> — «десь» у ствердженнях.',
                            '<strong>Anywhere</strong> — «десь / ніде» у запереченнях та питаннях.',
                            '<strong>Nowhere</strong> — «ніде», заперечення одним словом.',
                            '<strong>Everywhere</strong> — «всюди».',
                            'Уникай подвійного заперечення: не <span class="line-through">I don’t go nowhere</span>, а <span class="font-mono text-xs">I don’t go anywhere / I go nowhere</span>.',
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'practice-set',
                    'column' => 'left',
                    'body' => json_encode([
                        'title' => '6. Практика',
                        'select_title' => 'Вправа 1. Обери правильне слово',
                        'select_intro' => 'Обери <strong>somewhere / anywhere / nowhere / everywhere</strong>.',
                        'selects' => [
                            ['label' => 'I can’t find my keys <span class="text-slate-500">(ніде)</span>.', 'prompt' => 'I can’t find my keys ______.'],
                            ['label' => 'She wants to live <span class="text-slate-500">(десь біля моря)</span>.', 'prompt' => 'She wants to live ______ near the sea.'],
                            ['label' => 'Do you want to go <span class="text-slate-500">(кудись)</span> this evening?', 'prompt' => 'Do you want to go ______ this evening?'],
                        ],
                        'options' => ['somewhere', 'anywhere', 'nowhere', 'everywhere'],
                        'input_title' => 'Вправа 2. Заповни пропуски',
                        'input_intro' => 'Напиши <strong>somewhere / anywhere / nowhere / everywhere</strong>.',
                        'inputs' => [
                            ['before' => 'I don’t want to go', 'after' => 'tonight.'],
                            ['before' => 'There is', 'after' => 'to sit in this park. It’s full.'],
                            ['before' => 'He travels', 'after' => 'for work.'],
                        ],
                        'rephrase_title' => 'Вправа 3. Перефразуй',
                        'rephrase_intro' => 'Перепиши речення, використовуючи <strong>anywhere / nowhere</strong>.',
                        'rephrase' => [
                            [
                                'example_label' => 'Приклад:',
                                'example_original' => 'I don’t go anywhere on Saturdays.',
                                'example_target' => 'I go nowhere on Saturdays.',
                            ],
                            [
                                'original' => '1. I go nowhere on Friday evenings.',
                                'placeholder' => 'Напиши те саме, але з don’t... anywhere',
                            ],
                            [
                                'original' => '2. She doesn’t go anywhere without her phone.',
                                'placeholder' => 'Спробуй перефразувати з nowhere',
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
                [
                    'type' => 'navigation-chips',
                    'column' => 'footer',
                    'body' => json_encode([
                        'title' => 'Інші сторінки з серії Some / Any',
                        'items' => [
                            [
                                'label' => 'Some / Any — Люди',
                                'url' => '/pages/some-any/some-any-people',
                            ],
                            [
                                'label' => 'Some / Any — Речі',
                                'url' => '/pages/some-any/some-any-things',
                            ],
                            [
                                'label' => 'Some / Any — Місця (поточна)',
                                'current' => true,
                            ],
                        ],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];
    }
}
