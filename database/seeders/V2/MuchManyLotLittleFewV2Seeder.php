<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MuchManyLotLittleFewV2Seeder extends QuestionSeeder
{
    private array $levelDifficulty = [
        'A1' => 1,
        'A2' => 2,
        'B1' => 3,
        'B2' => 4,
        'C1' => 5,
        'C2' => 5,
    ];

    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Quantifiers'])->id;
        $sourceIds = [
            'page1' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few V2 (Page 1)'])->id,
            'page2' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few V2 (Page 2)'])->id,
            'page3' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few V2 (Page 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Much / Many / Lot / Little / Few'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Quantifier Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $countableTagId = Tag::firstOrCreate(
            ['name' => 'Countable vs Uncountable'],
            ['category' => 'English Grammar Focus']
        )->id;

        $negativeTagId = Tag::firstOrCreate(
            ['name' => 'Negative Forms (no/none/any)'],
            ['category' => 'English Grammar Pattern']
        )->id;

        $questions = $this->questionEntries();

        $tagIds = [
            $themeTagId,
            $detailTagId,
            $structureTagId,
            $countableTagId,
            $negativeTagId,
        ];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 1,
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => $tagIds,
                'answers' => $answers,
                'options' => $options,
                'variants' => [$entry['question']],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'] ?? [],
                'explanations' => $entry['explanations'] ?? [],
            ];
        }

        $this->seedQuestionData($items, []);
        $this->attachHintsAndExplanations($meta);
    }

    private function attachHintsAndExplanations(array $meta): void
    {
        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'])->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            foreach ($data['explanations'] ?? [] as $marker => $options) {
                if (! isset($answers[$marker])) {
                    $fallback = reset($answers);
                    $answers[$marker] = is_string($fallback) ? $fallback : (string) $fallback;
                }

                $correct = $answers[$marker];
                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                foreach ($options as $option => $text) {
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
    }

    private function questionEntries(): array
    {
        return [
            // Page 1
            [
                'question' => 'If I want to pass the exam, I need to study _____.',
                'options' => [
                    'a1' => ['a lot', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**A lot** без of використовується наприкінці речення або після дієслова.',
                    '**A lot of** + іменник (злічуваний або незлічуваний).',
                    '**Many** + злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ A lot стоїть наприкінці речення без додавання іменника. Приклад: *I need to study a lot*.',
                        'a lot of' => '❌ A lot of потребує іменника після себе (a lot of books, a lot of time). Тут іменника немає.',
                        'many' => '❌ Many вживається тільки перед злічуваними іменниками у множині (many books, many hours).',
                    ],
                ],
            ],
            [
                'question' => "There aren't _____ things to do in this village.",
                'options' => [
                    'a1' => ['many', 'a lot', 'much'],
                ],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**Many** + злічуваний іменник у множині в запереченнях і питаннях.',
                    '**Much** + незлічуваний іменник в запереченнях і питаннях.',
                    '**A lot** стоїть окремо, без іменника після нього.',
                ],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Many використовується з злічуваними іменниками у множині: many things. Приклад: *There aren\'t many things to do*.',
                        'a lot' => '❌ A lot не може стояти між "aren\'t" та іменником; потрібно a lot of things або many things.',
                        'much' => '❌ Much вживається з незлічуваними іменниками (much water, much time), а "things" — злічуваний іменник.',
                    ],
                ],
            ],
            [
                'question' => '_____ sugar do you take in your tea?',
                'options' => [
                    'a1' => ['How many', 'How little', 'How much'],
                ],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**How much** + незлічуваний іменник у питаннях про кількість.',
                    '**How many** + злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ How much використовується з незлічуваними іменниками: How much sugar/water/time. Приклад: *How much sugar do you take?*',
                        'How many' => '❌ How many вживається зі злічуваними іменниками у множині (How many cups, How many spoons).',
                        'How little' => '❌ How little не є стандартною конструкцією для питань про кількість.',
                    ],
                ],
            ],
            [
                'question' => 'There was _____ tension at the meeting.',
                'options' => [
                    'a1' => ['much', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**A lot of** + будь-який іменник (злічуваний або незлічуваний) у стверджувальних реченнях.',
                    '**Much** + незлічуваний іменник, але рідко у стверджувальних реченнях.',
                    '**Many** + злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ A lot of використовується з будь-якими іменниками у стверджувальних реченнях. Приклад: *There was a lot of tension*.',
                        'much' => '❌ Much рідко використовується у стверджувальних реченнях; звичайно в запереченнях і питаннях.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "tension" — незлічуваний іменник.',
                    ],
                ],
            ],
            [
                'question' => 'Dad, I need _____ money for school.',
                'options' => [
                    'a1' => ['a little', 'many', 'a few'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**A little** + незлічуваний іменник (невелика кількість).',
                    '**A few** + злічуваний іменник у множині (невелика кількість).',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ A little використовується з незлічуваними іменниками: a little money/time/water. Приклад: *I need a little money*.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "money" — незлічуваний іменник.',
                        'a few' => '❌ A few використовується зі злічуваними іменниками у множині (a few books, a few dollars).',
                    ],
                ],
            ],
            [
                'question' => 'There are _____ things that you can do to improve your writing.',
                'options' => [
                    'a1' => ['any', 'a little', 'a few'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**A few** + злічуваний іменник у множині (невелика кількість у стверджувальному реченні).',
                    '**A little** + незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ A few використовується зі злічуваними іменниками у множині: a few things. Приклад: *There are a few things you can do*.',
                        'any' => '❌ Any зазвичай вживається в запереченнях і питаннях, не у стверджувальних реченнях.',
                        'a little' => '❌ A little використовується з незлічуваними іменниками, а "things" — злічуваний іменник у множині.',
                    ],
                ],
            ],
            [
                'question' => 'There is _____ milk in the fridge. We need to buy some.',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**No** + іменник = відсутність чогось (no milk, no time).',
                    '**None** стоїть окремо, без іменника після нього.',
                    '**Any** використовується в запереченнях з "not" (not any).',
                ],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ No + іменник означає відсутність: no milk, no books. Приклад: *There is no milk in the fridge*.',
                        'none' => '❌ None стоїть окремо як відповідь або наприкінці речення, без іменника після нього.',
                        'any' => '❌ Any вживається з "not" у запереченнях (There isn\'t any milk), а не з дієсловом "is".',
                    ],
                ],
            ],
            [
                'question' => "He doesn't have _____ hobbies.",
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**Any** + іменник у запереченнях з "not" (doesn\'t/don\'t/isn\'t + any).',
                    '**No** + іменник без "not" у дієслові.',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '✅ Any використовується з "not" у запереченнях: doesn\'t have any hobbies. Приклад: *He doesn\'t have any hobbies*.',
                        'none' => '❌ None стоїть окремо без іменника: "He has none" (а не "none hobbies").',
                        'no' => '❌ No + іменник потребує стверджувальної форми дієслова: "He has no hobbies", а не "doesn\'t have no".',
                    ],
                ],
            ],
            [
                'question' => '"How many computers do you have?" "_____."',
                'options' => [
                    'a1' => ['None', 'Any', 'No'],
                ],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**None** — самостійна відповідь на питання "How many..." (жодного).',
                    '**No** потребує іменника після себе (no computers).',
                ],
                'explanations' => [
                    'a1' => [
                        'None' => '✅ None — самостійна відповідь, що означає "жодного": How many computers? None. Приклад: *"None."*',
                        'Any' => '❌ Any не є відповіддю на питання про кількість; це означає "будь-який" або використовується у запереченнях.',
                        'No' => '❌ No потребує іменника після себе (No computers), не може стояти окремо як відповідь.',
                    ],
                ],
            ],
            [
                'question' => 'I can help you; I have _____ time today.',
                'options' => [
                    'a1' => ['a lot of', 'much', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    '**A lot of** + будь-який іменник у стверджувальних реченнях.',
                    '**Much** рідко вживається у стверджувальних реченнях.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ A lot of використовується з будь-якими іменниками у стверджувальних реченнях. Приклад: *I have a lot of time*.',
                        'much' => '❌ Much зазвичай вживається в запереченнях і питаннях, рідко у стверджувальних реченнях.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "time" — незлічуваний іменник.',
                    ],
                ],
            ],

            // Page 2
            [
                'question' => '"How much water do you drink?" "_____."',
                'options' => [
                    'a1' => ['Much', 'A lot of', 'A lot'],
                ],
                'answers' => ['a1' => 'A lot'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**A lot** — самостійна відповідь наприкінці речення, без іменника.',
                    '**A lot of** потребує іменника після себе.',
                ],
                'explanations' => [
                    'a1' => [
                        'A lot' => '✅ A lot стоїть окремо як відповідь на питання про кількість. Приклад: *"How much?" "A lot."*',
                        'A lot of' => '❌ A lot of потребує іменника після себе (a lot of water), не може стояти окремо.',
                        'Much' => '❌ Much зазвичай не використовується у коротких стверджувальних відповідях.',
                    ],
                ],
            ],
            [
                'question' => '_____ goals did they score?',
                'options' => [
                    'a1' => ['How many', 'How much', 'How little'],
                ],
                'answers' => ['a1' => 'How many'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**How many** + злічуваний іменник у множині для питань про кількість.',
                    '**How much** + незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'How many' => '✅ How many використовується зі злічуваними іменниками у множині: How many goals/books/people. Приклад: *How many goals did they score?*',
                        'How much' => '❌ How much вживається з незлічуваними іменниками (How much water, How much time).',
                        'How little' => '❌ How little не є стандартною конструкцією для питань про кількість.',
                    ],
                ],
            ],
            [
                'question' => "Nowadays we don't use _____ cash, because we use our credit cards.",
                'options' => [
                    'a1' => ['many', 'much', 'a lot'],
                ],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**Much** + незлічуваний іменник у запереченнях і питаннях.',
                    '**Many** + злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ Much використовується з незлічуваними іменниками у запереченнях: don\'t use much cash. Приклад: *We don\'t use much cash*.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "cash" — незлічуваний іменник.',
                        'a lot' => '❌ A lot не може стояти між "don\'t use" та іменником; можна "don\'t use a lot of cash".',
                    ],
                ],
            ],
            [
                'question' => "There's _____ pressure on the players.",
                'options' => [
                    'a1' => ['a lot of', 'much', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**A lot of** + будь-який іменник у стверджувальних реченнях.',
                    '**Much** рідко вживається у стверджувальних реченнях.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ A lot of використовується у стверджувальних реченнях з будь-якими іменниками. Приклад: *There\'s a lot of pressure*.',
                        'much' => '❌ Much зазвичай вживається в запереченнях і питаннях, не у стверджувальних реченнях.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "pressure" — незлічуваний іменник.',
                    ],
                ],
            ],
            [
                'question' => 'They got married _____ months after they met for the first time.',
                'options' => [
                    'a1' => ['much', 'a little', 'a few'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**A few** + злічуваний іменник у множині (невелика кількість).',
                    '**A little** + незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ A few використовується зі злічуваними іменниками у множині: a few months/days/years. Приклад: *They got married a few months after*.',
                        'much' => '❌ Much вживається з незлічуваними іменниками, а "months" — злічуваний іменник у множині.',
                        'a little' => '❌ A little використовується з незлічуваними іменниками (a little time, a little money).',
                    ],
                ],
            ],
            [
                'question' => '"How much milk do you want in your coffee?" "Only _____."',
                'options' => [
                    'a1' => ['a little', 'a few', 'much'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**A little** — невелика кількість незлічуваного іменника.',
                    '**A few** — невелика кількість злічуваного іменника у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ A little використовується з незлічуваними іменниками як відповідь: a little milk. Приклад: *"Only a little."*',
                        'a few' => '❌ A few вживається зі злічуваними іменниками у множині, а "milk" — незлічуваний іменник.',
                        'much' => '❌ Much не використовується у коротких стверджувальних відповідях з "only".',
                    ],
                ],
            ],
            [
                'question' => 'There were _____ problems during the festival.',
                'options' => [
                    'a1' => ['any', 'no', 'none'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**No** + іменник = відсутність чогось (стверджувальна форма дієслова).',
                    '**None** стоїть окремо без іменника.',
                ],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ No + іменник означає відсутність: no problems, no issues. Приклад: *There were no problems*.',
                        'any' => '❌ Any вживається з "not" у запереченнях (There weren\'t any problems), не зі стверджувальною формою "were".',
                        'none' => '❌ None стоїть окремо без іменника: "There were none" (а не "none problems").',
                    ],
                ],
            ],
            [
                'question' => "I don't want _____ gifts.",
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**Any** + іменник у запереченнях з "not" (don\'t want any).',
                    '**No** потребує стверджувальної форми дієслова (I want no gifts).',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '✅ Any використовується з "not" у запереченнях: don\'t want any gifts. Приклад: *I don\'t want any gifts*.',
                        'none' => '❌ None стоїть окремо без іменника, не може використовуватись з "don\'t want".',
                        'no' => '❌ No потребує стверджувальної форми: "I want no gifts", а не "don\'t want no".',
                    ],
                ],
            ],
            [
                'question' => '"How many gifts do you want?" "_____."',
                'options' => [
                    'a1' => ['None', 'Any', 'No'],
                ],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**None** — самостійна відповідь на "How many..." (жодного).',
                    '**No** потребує іменника після себе.',
                ],
                'explanations' => [
                    'a1' => [
                        'None' => '✅ None — самостійна відповідь, що означає "жодного": How many gifts? None. Приклад: *"None."*',
                        'Any' => '❌ Any означає "будь-який" і не відповідає на питання про кількість у такому контексті.',
                        'No' => '❌ No потребує іменника після себе (No gifts), не може стояти окремо.',
                    ],
                ],
            ],
            [
                'question' => 'I eat _____ vegetables.',
                'options' => [
                    'a1' => ['much', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    '**A lot of** — найкраще для стверджувальних речень з будь-якими іменниками.',
                    '**Many** також можливо зі злічуваними, але a lot of — природніше.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ A lot of — найприродніший вибір для стверджувальних речень. Приклад: *I eat a lot of vegetables*.',
                        'much' => '❌ Much вживається з незлічуваними іменниками, а "vegetables" — злічуваний іменник у множині.',
                        'many' => '❌ Many можливо, але у стверджувальних реченнях a lot of звучить природніше та частіше використовується.',
                    ],
                ],
            ],

            // Page 3
            [
                'question' => "He has _____ paintings. It's a really big collection.",
                'options' => [
                    'a1' => ['a lot of', 'many', 'a few'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**A lot of** — для великої кількості у стверджувальних реченнях.',
                    'Контекст: "really big collection" вказує на велику кількість.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ A lot of вказує на велику кількість: He has a lot of paintings. Контекст підтверджує: "big collection". Приклад: *He has a lot of paintings*.',
                        'many' => '❌ Many можливо, але у стверджувальних реченнях a lot of — природніше для великої кількості.',
                        'a few' => '❌ A few означає "кілька, небагато", що суперечить контексту "really big collection".',
                    ],
                ],
            ],
            [
                'question' => '_____ salt do you put in your food?',
                'options' => [
                    'a1' => ['How much', 'How many', 'How little'],
                ],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**How much** + незлічуваний іменник у питаннях.',
                    'Salt — незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ How much використовується з незлічуваними іменниками: How much salt/sugar/water. Приклад: *How much salt do you put?*',
                        'How many' => '❌ How many вживається зі злічуваними іменниками у множині, а "salt" — незлічуваний іменник.',
                        'How little' => '❌ How little не є стандартною конструкцією для питань про кількість.',
                    ],
                ],
            ],
            [
                'question' => 'I normally use _____ makeup, but not much, only some lipstick.',
                'options' => [
                    'a1' => ['a little', 'a few', 'much'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**A little** — невелика кількість незлічуваного іменника.',
                    'Makeup — незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ A little використовується з незлічуваними іменниками: a little makeup/water/time. Контекст: "not much". Приклад: *I use a little makeup*.',
                        'a few' => '❌ A few вживається зі злічуваними іменниками у множині, а "makeup" — незлічуваний іменник.',
                        'much' => '❌ Much не вживається у стверджувальних реченнях такого типу; зазвичай у запереченнях і питаннях.',
                    ],
                ],
            ],
            [
                'question' => 'It rained _____ last week; it rained every day.',
                'options' => [
                    'a1' => ['a lot', 'much', 'a few'],
                ],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**A lot** наприкінці речення без іменника.',
                    'Контекст: "every day" вказує на велику кількість.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ A lot стоїть наприкінці речення без іменника: It rained a lot. Контекст: "every day". Приклад: *It rained a lot*.',
                        'much' => '❌ Much не вживається у стверджувальних реченнях такого типу без іменника.',
                        'a few' => '❌ A few вживається тільки з іменниками (a few days), не може стояти окремо після дієслова.',
                    ],
                ],
            ],
            [
                'question' => "They only scored one goal. They didn't have _____ opportunities to score; maybe two or three.",
                'options' => [
                    'a1' => ['many', 'much', 'a lot'],
                ],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**Many** + злічуваний іменник у запереченнях.',
                    'Opportunities — злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Many використовується зі злічуваними іменниками у запереченнях: didn\'t have many opportunities. Приклад: *They didn\'t have many opportunities*.',
                        'much' => '❌ Much вживається з незлічуваними іменниками, а "opportunities" — злічуваний іменник у множині.',
                        'a lot' => '❌ A lot не може стояти між "didn\'t have" та іменником; можливо "didn\'t have a lot of opportunities".',
                    ],
                ],
            ],
            [
                'question' => 'Please, I just need 5 minutes of your time. I only have _____ questions.',
                'options' => [
                    'a1' => ['a few', 'a little', 'many'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**A few** — невелика кількість злічуваного іменника у множині.',
                    'Questions — злічуваний іменник у множині.',
                ],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ A few використовується зі злічуваними іменниками у множині: a few questions. Контекст: "only". Приклад: *I have a few questions*.',
                        'a little' => '❌ A little вживається з незлічуваними іменниками, а "questions" — злічуваний іменник у множині.',
                        'many' => '❌ Many не поєднується з "only" у такому контексті; "only a few" — правильна комбінація.',
                    ],
                ],
            ],
            [
                'question' => 'We had _____ problems. It was 100% perfect.',
                'options' => [
                    'a1' => ['no', 'none', 'any'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**No** + іменник = повна відсутність (стверджувальна форма дієслова).',
                    'Контекст: "100% perfect" підтверджує відсутність проблем.',
                ],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ No + іменник означає повну відсутність: We had no problems. Контекст: "100% perfect". Приклад: *We had no problems*.',
                        'none' => '❌ None стоїть окремо без іменника: "We had none" (а не "none problems").',
                        'any' => '❌ Any вживається з "not": "We didn\'t have any problems", не зі стверджувальною формою "had".',
                    ],
                ],
            ],
            [
                'question' => "We need to be fast; we haven't got _____ time, only 20 minutes.",
                'options' => [
                    'a1' => ['much', 'many', 'a lot of'],
                ],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**Much** + незлічуваний іменник у запереченнях.',
                    'Time — незлічуваний іменник.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ Much використовується з незлічуваними іменниками у запереченнях: haven\'t got much time. Приклад: *We haven\'t got much time*.',
                        'many' => '❌ Many вживається зі злічуваними іменниками у множині, а "time" — незлічуваний іменник.',
                        'a lot of' => '❌ A lot of можливо, але much природніше у запереченнях з незлічуваними іменниками.',
                    ],
                ],
            ],
            [
                'question' => "I didn't see _____ people in the room. It was completely empty.",
                'options' => [
                    'a1' => ['any', 'no', 'none'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**Any** + іменник у запереченнях з "not" (didn\'t see any).',
                    'Контекст: "completely empty" підтверджує відсутність.',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '✅ Any використовується з "not" у запереченнях: didn\'t see any people. Контекст: "empty". Приклад: *I didn\'t see any people*.',
                        'no' => '❌ No потребує стверджувальної форми дієслова: "I saw no people", а не "didn\'t see no".',
                        'none' => '❌ None стоїть окремо без іменника, не може використовуватись з "didn\'t see".',
                    ],
                ],
            ],
            [
                'question' => '"How many cups of coffee did you have?" "_____. I don\'t drink coffee."',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'none'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    '**None** — самостійна відповідь на "How many..." (жодного).',
                    'Контекст підтверджує: "I don\'t drink coffee".',
                ],
                'explanations' => [
                    'a1' => [
                        'none' => '✅ None — самостійна відповідь, що означає "жодного": How many cups? None. Контекст: "I don\'t drink coffee". Приклад: *"None."*',
                        'no' => '❌ No потребує іменника після себе (No cups), не може стояти окремо як відповідь.',
                        'any' => '❌ Any не є відповіддю на питання про кількість; використовується у запереченнях з "not".',
                    ],
                ],
            ],
        ];
    }

    private function flattenOptions(array $optionSets): array
    {
        $values = [];
        foreach ($optionSets as $options) {
            foreach ($options as $option) {
                $values[] = (string) $option;
            }
        }

        return array_values(array_unique($values));
    }
}
