<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PastPerfectMasteryV2Seeder extends Seeder
{
    public function run(): void
    {
        $service = new QuestionSeedingService();

        $categoryId = Category::firstOrCreate(['name' => 'Past Perfect Mastery'])->id;

        $sources = [
            'A' => Source::firstOrCreate(['name' => 'Past Perfect Mastery Test – Section A'])->id,
            'B' => Source::firstOrCreate(['name' => 'Past Perfect Mastery Test – Section B'])->id,
            'C' => Source::firstOrCreate(['name' => 'Past Perfect Mastery Test – Section C'])->id,
            'D' => Source::firstOrCreate(['name' => 'Past Perfect Mastery Test – Section D'])->id,
        ];

        $generalTagId = Tag::firstOrCreate(['name' => 'Past Perfect Mastery Test'], ['category' => 'English Grammar Tests'])->id;

        $tenseTags = [
            'Past Perfect Simple' => Tag::firstOrCreate(['name' => 'Past Perfect Simple'], ['category' => 'English Grammar Tenses'])->id,
            'Past Perfect Continuous' => Tag::firstOrCreate(['name' => 'Past Perfect Continuous'], ['category' => 'English Grammar Tenses'])->id,
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'English Grammar Tenses'])->id,
        ];

        $detailTagNames = [
            'A' => [
                'Past Perfect Simple – Completed action before time marker',
                'Past Perfect Simple – Question form focus',
                'Past Perfect Simple – Negative prior action',
                'Past Perfect Simple – No prior experience',
                'Past Perfect Simple – Finished action before returning',
            ],
            'B' => [
                'Past Perfect Continuous – Long-term employment duration',
                'Past Perfect Continuous – Question about waiting duration',
                'Past Perfect Continuous – Negative duration before event',
                'Past Perfect Continuous – Ongoing search before success',
            ],
            'C' => [
                'Past Perfect Simple – Gift given before loss',
                'Past Perfect Simple – Event already started before arrival',
                'Past Perfect Continuous – Cause of anger',
                'Past Perfect Simple – Lifelong friendship',
                'Past Perfect Continuous – Cause of exhaustion',
                'Past Perfect Simple – Weather evidence',
                'Past Perfect Simple – Recognition after long gap',
            ],
            'D' => [
                'Past Perfect Continuous – Relief after long search',
                'Past Perfect Simple – Hunger because of no meal',
                'Past Simple – Earthquake destruction',
                'Past Perfect Simple – Homework completed before playing',
                'Past Simple – Meal completely eaten',
                'Past Simple – Describing past state',
                'Past Simple – Interrupted activity',
                'Past Perfect Continuous – Evidence from recent activity',
                'Past Simple – Returning home after bedtime',
                'Past Perfect Continuous – Fatigue after long work',
            ],
        ];

        $detailTags = [];
        foreach ($detailTagNames as $sectionKey => $names) {
            foreach ($names as $index => $name) {
                $detailTags[$sectionKey][$index] = Tag::firstOrCreate(
                    ['name' => $name],
                    ['category' => 'Grammar Usage Detail']
                )->id;
            }
        }

        $questionsBySection = [
            'A' => [
                [
                    'question' => 'They {a1} their homework before 6 o’clock.',
                    'verb_hint' => ['a1' => '(finish)'],
                    'options' => ['had finished', 'finished', 'have finished'],
                    'answers' => ['a1' => 'had finished'],
                    'explanations' => [
                        'had finished' => '✅ Це **Past Perfect Simple**. Формула: had + V3. Використовується для дій, які завершилися до іншої минулої події. Тут: вони закінчили домашнє завдання ДО 6-ї години.',
                        'finished' => '❌ Це **Past Simple**. Формула: V2. Використовується для завершених дій у минулому, але не показує попередність.',
                        'have finished' => '❌ Це **Present Perfect**. Формула: have/has + V3. Використовується для теперішнього результату, але дія тут у минулому.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + V3).\n                 Приклад: *She had left before I arrived.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => '{a1} by the time he came back?',
                    'verb_hint' => ['a1' => '(you/leave)'],
                    'options' => ['Had you left', 'Did you leave', 'Have you left'],
                    'answers' => ['a1' => 'Had you left'],
                    'explanations' => [
                        'Had you left' => '✅ Це **Past Perfect Simple**. Формула питання: Had + subject + V3. Використовується, щоб дізнатися, чи дія завершилася до іншої минулої події.',
                        'Did you leave' => '❌ Це **Past Simple**. Формула: Did + subject + V1. Не показує попередність.',
                        'Have you left' => '❌ Це **Present Perfect**. Формула: Have + subject + V3. Використовується для теперішнього, а не минулого.',
                    ],
                    'hints' => [
                        'a1' => "Питання у **Past Perfect Simple**: Had + subject + V3?\n                 Приклад: *Had they finished the work before she arrived?*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'She {a1} a car before she learnt to drive.',
                    'verb_hint' => ['a1' => '(not/buy)'],
                    'options' => ['had not bought', 'did not buy', 'has not bought'],
                    'answers' => ['a1' => 'had not bought'],
                    'explanations' => [
                        'had not bought' => '✅ Це **Past Perfect Simple** (had + not + V3). Використовується, щоб показати, що дія не відбулася до іншої минулої події.',
                        'did not buy' => '❌ Це **Past Simple**. Формула: did not + V1. Не підкреслює попередність.',
                        'has not bought' => '❌ Це **Present Perfect**. Формула: has not + V3. Використовується для теперішнього результату.',
                    ],
                    'hints' => [
                        'a1' => "Заперечення у **Past Perfect Simple**: had not + V3.\n                 Приклад: *I hadn’t seen him before the party.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'We {a1} to their new CD before they gave us a copy.',
                    'verb_hint' => ['a1' => '(not/listen)'],
                    'options' => ['had not listened', 'did not listen', 'have not listened'],
                    'answers' => ['a1' => 'had not listened'],
                    'explanations' => [
                        'had not listened' => '✅ Це **Past Perfect Simple**. Формула: had not + V3. Показує, що дія не відбулася до іншої події.',
                        'did not listen' => '❌ Це **Past Simple**. Лише факт, без акценту на порядок дій.',
                        'have not listened' => '❌ Це **Present Perfect**. Використовується для теперішнього результату.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had not + V3).\n                 Приклад: *They hadn’t eaten before they went out.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'I {a1} the shopping before I returned home.',
                    'verb_hint' => ['a1' => '(do)'],
                    'options' => ['had done', 'did', 'have done'],
                    'answers' => ['a1' => 'had done'],
                    'explanations' => [
                        'had done' => '✅ Це **Past Perfect Simple**. Формула: had + V3. Використовується для дій, що завершилися перед іншими у минулому.',
                        'did' => '❌ Це **Past Simple**. Лише факт, без акценту на послідовність.',
                        'have done' => '❌ Це **Present Perfect**. Використовується для теперішнього результату.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + V3).\n                 Приклад: *She had finished the book before the exam.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
            ],
            'B' => [
                [
                    'question' => 'He {a1} in that factory for thirty years by the time he retired.',
                    'verb_hint' => ['a1' => '(work)'],
                    'options' => ['had been working', 'was working', 'worked'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'had been working' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для дій, які тривали протягом певного часу до іншої минулої події.',
                        'was working' => '❌ Це **Past Continuous**. Формула: was/were + V-ing. Показує дію у конкретний момент, а не тривалість до іншої події.',
                        'worked' => '❌ Це **Past Simple**. Лише факт роботи без акценту на тривалість.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *He had been studying for hours before the exam started.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => '{a1} for long before the doctor saw them?',
                    'verb_hint' => ['a1' => '(they/wait)'],
                    'options' => ['Had they been waiting', 'Were they waiting', 'Did they wait'],
                    'answers' => ['a1' => 'Had they been waiting'],
                    'explanations' => [
                        'Had they been waiting' => '✅ Це **Past Perfect Continuous**. Формула питання: Had + subject + been + V-ing? Використовується для тривалих дій до іншої минулої події.',
                        'Were they waiting' => '❌ Це **Past Continuous**. Формула: were + V-ing. Лише момент у минулому, без акценту на попередність.',
                        'Did they wait' => '❌ Це **Past Simple**. Формула: Did + subject + V1. Використовується для фактів, а не тривалості.',
                    ],
                    'hints' => [
                        'a1' => "Питання у **Past Perfect Continuous**: Had + subject + been + V-ing?\n                 Приклад: *Had they been working all day before the boss arrived?*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'Sarah {a1} for long before her children came back from school.',
                    'verb_hint' => ['a1' => '(not/cook)'],
                    'options' => ['had not been cooking', 'was not cooking', 'did not cook'],
                    'answers' => ['a1' => 'had not been cooking'],
                    'explanations' => [
                        'had not been cooking' => '✅ Це **Past Perfect Continuous**. Формула: had not been + V-ing. Використовується для заперечення тривалості дії до іншої події.',
                        'was not cooking' => '❌ Це **Past Continuous**. Лише дія у моменті, без акценту на тривалість.',
                        'did not cook' => '❌ Це **Past Simple**. Лише факт, без процесу.',
                    ],
                    'hints' => [
                        'a1' => "Заперечення у **Past Perfect Continuous**: had not been + V-ing.\n                 Приклад: *She hadn’t been sleeping long when the alarm rang.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'I {a1} for a flat for two months before I found one that I liked.',
                    'verb_hint' => ['a1' => '(look)'],
                    'options' => ['had been looking', 'was looking', 'looked'],
                    'answers' => ['a1' => 'had been looking'],
                    'explanations' => [
                        'had been looking' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для дій, що тривали протягом певного часу до іншої події.',
                        'was looking' => '❌ Це **Past Continuous**. Показує дію у моменті, але не тривалість перед іншою подією.',
                        'looked' => '❌ Це **Past Simple**. Лише факт без процесу.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *I had been looking for my keys for an hour before I found them.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
            ],
            'C' => [
                [
                    'question' => 'I lost the earrings she {a1} me.',
                    'verb_hint' => ['a1' => '(give)'],
                    'options' => ['had given', 'gave', 'has given'],
                    'answers' => ['a1' => 'had given'],
                    'explanations' => [
                        'had given' => '✅ Це **Past Perfect Simple**. Формула: had + V3. Використовується для дій, що сталися ДО іншої події. Тут: вона подарувала сережки ДО того, як я їх втратив.',
                        'gave' => '❌ Це **Past Simple**. Формула: V2. Позначає факт у минулому, але не передає попередність.',
                        'has given' => '❌ Це **Present Perfect**. Формула: have/has + V3. Використовується для теперішнього результату, але дія відбулася в минулому.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + V3).\n                 Приклад: *She had given me the book before I lost it.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'When I arrived at the theatre, the play {a1}.',
                    'verb_hint' => ['a1' => '(already/start)'],
                    'options' => ['had already started', 'already started', 'has already started'],
                    'answers' => ['a1' => 'had already started'],
                    'explanations' => [
                        'had already started' => '✅ Це **Past Perfect Simple**. Формула: had + already + V3. Використовується для дій, які завершилися ДО іншої події. Тут: вистава почалася ДО мого приходу.',
                        'already started' => '❌ Це **Past Simple**. Лише факт, без акценту на попередність.',
                        'has already started' => '❌ Це **Present Perfect**. Використовується для теперішнього результату, але дія у минулому.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + already + V3).\n                 Приклад: *The film had already started when we arrived.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'She was angry because she {a1} for me for more than two hours.',
                    'verb_hint' => ['a1' => '(wait)'],
                    'options' => ['had been waiting', 'was waiting', 'waited'],
                    'answers' => ['a1' => 'had been waiting'],
                    'explanations' => [
                        'had been waiting' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для тривалих дій до іншої події. Тут: вона чекала мене більше двох годин ДО того, як розсердилась.',
                        'was waiting' => '❌ Це **Past Continuous**. Лише дія у моменті, без акценту на попередність.',
                        'waited' => '❌ Це **Past Simple**. Лише факт, без тривалості.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *She had been waiting for hours before he arrived.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'Alex was Rita’s best friend. She {a1} him all her life.',
                    'verb_hint' => ['a1' => '(know)'],
                    'options' => ['had known', 'knew', 'has known'],
                    'answers' => ['a1' => 'had known'],
                    'explanations' => [
                        'had known' => '✅ Це **Past Perfect Simple**. Формула: had + V3. Використовується для позначення довготривалого досвіду ДО певного моменту. Тут: вона знала його все життя до того часу.',
                        'knew' => '❌ Це **Past Simple**. Лише факт у минулому, але не акцентує на тривалості.',
                        'has known' => '❌ Це **Present Perfect**. Використовується для теперішнього досвіду.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + V3).\n                 Приклад: *She had known him for years before they lost contact.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'I was exhausted because I {a1} the house all day.',
                    'verb_hint' => ['a1' => '(clean)'],
                    'options' => ['had been cleaning', 'was cleaning', 'cleaned'],
                    'answers' => ['a1' => 'had been cleaning'],
                    'explanations' => [
                        'had been cleaning' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для дій, які тривали і пояснюють стан у минулому. Тут: я був виснажений, бо прибирав увесь день.',
                        'was cleaning' => '❌ Це **Past Continuous**. Лише дія у моменті, без акценту на результаті.',
                        'cleaned' => '❌ Це **Past Simple**. Лише факт, без тривалості і результату.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *He was tired because he had been working all day.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'Everything was white because it {a1} the night before.',
                    'verb_hint' => ['a1' => '(snow)'],
                    'options' => ['had snowed', 'snowed', 'was snowing'],
                    'answers' => ['a1' => 'had snowed'],
                    'explanations' => [
                        'had snowed' => '✅ Це **Past Perfect Simple**. Формула: had + V3. Використовується для події, яка сталася ДО іншої. Тут: сніг випав ДО того, як ми побачили білий пейзаж.',
                        'snowed' => '❌ Це **Past Simple**. Факт без акценту на попередність.',
                        'was snowing' => '❌ Це **Past Continuous**. Показує процес, але не завершений результат.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + V3).\n                 Приклад: *The ground was wet because it had rained the night before.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'I {a1} Rebecca for years but I recognized her the moment I saw her.',
                    'verb_hint' => ['a1' => '(not/see)'],
                    'options' => ['had not seen', 'did not see', 'have not seen'],
                    'answers' => ['a1' => 'had not seen'],
                    'explanations' => [
                        'had not seen' => '✅ Це **Past Perfect Simple**. Формула: had not + V3. Використовується для відсутності дії ДО іншої події. Тут: я не бачив її роками ДО того, як зустрів.',
                        'did not see' => '❌ Це **Past Simple**. Лише факт, без тривалості та попередності.',
                        'have not seen' => '❌ Це **Present Perfect**. Використовується для теперішнього часу.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had not + V3).\n                 Приклад: *I hadn’t seen him for ages before yesterday.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
            ],
            'D' => [
                [
                    'question' => 'I was relieved when I found my keys. I {a1} for them for hours.',
                    'verb_hint' => ['a1' => '(look)'],
                    'options' => ['had looked', 'had been looking'],
                    'answers' => ['a1' => 'had been looking'],
                    'explanations' => [
                        'had looked' => '❌ Це **Past Perfect Simple**. Формула: had + V3. Підкреслює результат, але тут важливий процес пошуку.',
                        'had been looking' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для тривалого процесу ДО моменту знаходження ключів.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *I had been waiting for hours before the bus arrived.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'We were very hungry because we {a1} anything.',
                    'verb_hint' => ['a1' => '(eat/not)'],
                    'options' => ['hadn’t been eating', 'hadn’t eaten'],
                    'answers' => ['a1' => 'hadn’t eaten'],
                    'explanations' => [
                        'hadn’t been eating' => '❌ Це **Past Perfect Continuous**. Формула: had not been + V-ing. Може підкреслити тривалість, але тут важливий результат (ми були голодні).',
                        'hadn’t eaten' => '✅ Це **Past Perfect Simple**. Формула: had not + V3. Використовується, щоб показати відсутність результату ДО певного моменту.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had not + V3).\n                 Приклад: *They were tired because they hadn’t slept.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'The earthquake {a1} the house that they had built.',
                    'verb_hint' => ['a1' => '(destroy)'],
                    'options' => ['had destroyed', 'destroyed'],
                    'answers' => ['a1' => 'destroyed'],
                    'explanations' => [
                        'had destroyed' => '❌ Це **Past Perfect Simple**. Використовується для дій, що сталися раніше за іншу минулу подію. Але тут немає другої дії.',
                        'destroyed' => '✅ Це **Past Simple**. Формула: V2. Описує головну подію у минулому — землетрус зруйнував будинок.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Simple** (V2).\n                 Приклад: *The storm destroyed the roof.*",
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'When the kids went out to play, they {a1} their homework.',
                    'verb_hint' => ['a1' => '(already/do)'],
                    'options' => ['had already been doing', 'had already done'],
                    'answers' => ['a1' => 'had already done'],
                    'explanations' => [
                        'had already been doing' => '❌ Це **Past Perfect Continuous**. Формула: had been + V-ing. Підкреслює процес, але тут потрібен результат.',
                        'had already done' => '✅ Це **Past Perfect Simple**. Формула: had + already + V3. Використовується для дій, які завершилися ДО іншої події.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Simple** (had + already + V3).\n                 Приклад: *They had already eaten when we arrived.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Simple'],
                ],
                [
                    'question' => 'They {a1} all the food that I had prepared.',
                    'verb_hint' => ['a1' => '(eat)'],
                    'options' => ['ate', 'had been eating'],
                    'answers' => ['a1' => 'ate'],
                    'explanations' => [
                        'ate' => '✅ Це **Past Simple**. Формула: V2. Використовується для завершених дій у минулому. Тут: вони з’їли всю їжу.',
                        'had been eating' => '❌ Це **Past Perfect Continuous**. Підкреслює процес, але тут результат очевидний.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Simple** (V2).\n                 Приклад: *She ate all the cake yesterday.*",
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'The children {a1} wet because they had been playing in the lake.',
                    'verb_hint' => ['a1' => '(be)'],
                    'options' => ['were', 'had been'],
                    'answers' => ['a1' => 'were'],
                    'explanations' => [
                        'were' => '✅ Це **Past Simple**. Формула: was/were + complement. Використовується для опису стану у минулому.',
                        'had been' => '❌ Це **Past Perfect Simple**. Використовується для минулої дії перед іншою, але тут просто опис стану.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Simple** (was/were + adj/noun).\n                 Приклад: *They were tired after the trip.*",
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'We {a1} the film when the lights went out.',
                    'verb_hint' => ['a1' => '(watch)'],
                    'options' => ['had watched', 'watched'],
                    'answers' => ['a1' => 'watched'],
                    'explanations' => [
                        'had watched' => '❌ Це **Past Perfect Simple**. Показує, що дія завершилася ДО іншої, але тут вони ще дивилися.',
                        'watched' => '✅ Це **Past Simple**. Формула: V2. Використовується для подій, що відбулися у минулому.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Simple** (V2).\n                 Приклад: *We watched TV last night.*",
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'Little John’s face was dirty because he {a1} chocolate.',
                    'verb_hint' => ['a1' => '(eat)'],
                    'options' => ['ate', 'had been eating'],
                    'answers' => ['a1' => 'had been eating'],
                    'explanations' => [
                        'ate' => '❌ Це **Past Simple**. Лише факт, але не пояснює брудного обличчя.',
                        'had been eating' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Підкреслює процес, наслідки якого видно у минулому.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *He was dirty because he had been playing in the mud.*",
                    ],
                    'level' => 'B1',
                    'tense' => ['Past Perfect Continuous'],
                ],
                [
                    'question' => 'When he {a1}, she had already gone to bed.',
                    'verb_hint' => ['a1' => '(return)'],
                    'options' => ['had returned', 'returned'],
                    'answers' => ['a1' => 'returned'],
                    'explanations' => [
                        'had returned' => '❌ Це **Past Perfect Simple**. Використовується для дії, що сталася раніше, але тут він повернувся ПІСЛЯ того, як вона лягла спати.',
                        'returned' => '✅ Це **Past Simple**. Формула: V2. Використовується для подій у минулому без акценту на попередність.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Simple** (V2).\n                 Приклад: *He returned home late yesterday.*",
                    ],
                    'level' => 'A2',
                    'tense' => ['Past Simple'],
                ],
                [
                    'question' => 'They were tired because they {a1} for a long time.',
                    'verb_hint' => ['a1' => '(work)'],
                    'options' => ['had been working', 'worked'],
                    'answers' => ['a1' => 'had been working'],
                    'explanations' => [
                        'had been working' => '✅ Це **Past Perfect Continuous**. Формула: had been + V-ing. Використовується для дій, що тривали ДО певного моменту й пояснюють стан.',
                        'worked' => '❌ Це **Past Simple**. Лише факт, без акценту на процес.',
                    ],
                    'hints' => [
                        'a1' => "Використай **Past Perfect Continuous** (had been + V-ing).\n                 Приклад: *They were tired because they had been studying all night.*",
                    ],
                    'level' => 'B2',
                    'tense' => ['Past Perfect Continuous'],
                ],
            ],
        ];

        $items = [];
        $meta = [];

        foreach ($questionsBySection as $sectionKey => $questions) {
            $sourceId = $sources[$sectionKey];

            foreach ($questions as $index => $questionData) {
                $uuid = (string) Str::uuid();

                $answers = [];
                $answerMap = [];
                foreach ($questionData['answers'] as $marker => $value) {
                    $answers[] = [
                        'marker' => $marker,
                        'answer' => $value,
                        'verb_hint' => $this->normalizeVerbHint($questionData['verb_hint'][$marker] ?? null),
                    ];
                    $answerMap[$marker] = $value;
                }

                $tagIds = [$generalTagId];
                foreach ($questionData['tense'] as $tense) {
                    if (isset($tenseTags[$tense])) {
                        $tagIds[] = $tenseTags[$tense];
                    }
                }

                if (isset($detailTags[$sectionKey][$index])) {
                    $tagIds[] = $detailTags[$sectionKey][$index];
                }

                $options = $questionData['options'];
                $primaryMarker = array_key_first($questionData['answers']);
                $optionMarkerMap = [];
                foreach ($options as $option) {
                    $optionMarkerMap[$option] = $primaryMarker;
                }

                $variant = $this->buildVariant($questionData['question']);
                $variants = $variant !== null ? [$variant] : [];

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $questionData['question'],
                    'category_id' => $categoryId,
                    'difficulty' => $this->mapDifficulty($questionData['level'] ?? null),
                    'source_id' => $sourceId,
                    'flag' => 0,
                    'level' => $questionData['level'] ?? null,
                    'tag_ids' => array_values(array_unique($tagIds)),
                    'answers' => $answers,
                    'options' => $options,
                    'variants' => $variants,
                ];

                $meta[] = [
                    'uuid' => $uuid,
                    'answers' => $answerMap,
                    'option_markers' => $optionMarkerMap,
                    'hints' => $questionData['hints'] ?? [],
                    'explanations' => $questionData['explanations'] ?? [],
                ];
            }
        }

        $service->seed($items);

        foreach ($meta as $metaItem) {
            $question = Question::where('uuid', $metaItem['uuid'])->first();
            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($metaItem['hints']);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            foreach ($metaItem['explanations'] as $option => $text) {
                $marker = $metaItem['option_markers'][$option] ?? array_key_first($metaItem['answers']);
                $correct = $metaItem['answers'][$marker] ?? reset($metaItem['answers']);

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    private function normalizeVerbHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() \t\n\r");
    }

    private function mapDifficulty(?string $level): int
    {
        return match ($level) {
            'A1' => 1,
            'A2' => 1,
            'B1' => 2,
            'B2' => 3,
            'C1' => 4,
            'C2' => 5,
            default => 2,
        };
    }

    private function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $marker => $text) {
            $clean = ltrim($text);
            $parts[] = '{' . $marker . '} ' . $clean;
        }

        return implode("\n", $parts);
    }

    private function buildVariant(string $question): ?string
    {
        $variant = preg_replace('/\{a\d+\}/u', '____', $question);

        if ($variant === $question) {
            return null;
        }

        return $variant;
    }
}
