<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuantityComparisonsV2Seeder extends QuestionSeeder
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
        $sourceId = Source::firstOrCreate(['name' => 'AI generated: Quantity Comparisons (much/many/less/fewer) (SET 1)'])->id;

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantity Comparisons Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers (much/many/a lot/few/little)'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Quantifier Choice'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

        $tagIds = [$themeTagId, $detailTagId, $structureTagId];

        $items = [];
        $meta = [];

        foreach ($questions as $index => $entry) {
            $answers = [];
            foreach ($entry['answers'] as $marker => $answer) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => $entry['verb_hints'][$marker] ?? null,
                ];
            }

            $options = $this->flattenOptions($entry['options']);

            $uuid = $this->generateQuestionUuid($index + 1, $entry['question']);

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $this->levelDifficulty[$entry['level']] ?? 3,
                'source_id' => $sourceId,
                'flag' => 2,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => array_merge($tagIds, $entry['extra_tag_ids'] ?? []),
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

    private function flattenOptions(array $options): array
    {
        $flatOptions = [];
        foreach ($options as $markerOptions) {
            foreach ($markerOptions as $value) {
                $flatOptions[] = $value;
            }
        }

        return array_values(array_unique($flatOptions));
    }

    private function questionEntries(): array
    {
        return [
            // A1 Level - 12 questions
            [
                'question' => 'I really want a good mark, so I study {a1} every evening.',
                'answers' => ['a1' => 'a lot'],
                'options' => ['a1' => ['a lot', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях після дієслова часто використовуємо **a lot** (замість much/many). Приклад: *I study a lot*.'],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ Правильно: **a lot** використовується у стверджувальних реченнях для позначення великої кількості. Приклад: *She reads a lot*.',
                        'much' => '❌ **Much** зазвичай використовується у запереченнях та питаннях, не у стверджувальних реченнях.',
                        'many' => '❌ **Many** використовується зі злічуваними іменниками, а тут немає іменника після прислівника.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => "There aren't {a1} shops in this village.",
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['much', 'many', 'a lot']],
                'verb_hints' => [],
                'hints' => ['У запереченнях зі злічуваними іменниками використовуємо **many**. Приклад: *There aren\'t many people*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** використовується у запереченнях зі злічуваними іменниками (shops). Приклад: *There aren\'t many books*.',
                        'much' => '❌ **Much** використовується з незлічуваними іменниками, а shops — злічуваний іменник.',
                        'a lot' => '❌ **A lot** можна використовувати, але після нього потрібен прийменник **of**: *aren\'t a lot of shops*.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} sugar do you usually put in your tea?',
                'answers' => ['a1' => 'How much'],
                'options' => ['a1' => ['How much', 'How many', 'How little']],
                'verb_hints' => [],
                'hints' => ['У питаннях про кількість незлічуваних іменників використовуємо **How much**. Приклад: *How much water do you need?*.'],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ Правильно: **How much** використовується з незлічуваними іменниками (sugar). Приклад: *How much time do you have?*.',
                        'How many' => '❌ **How many** використовується зі злічуваними іменниками, а sugar — незлічуваний.',
                        'How little' => '❌ **How little** не є стандартною питальною конструкцією для кількості.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'There was {a1} noise outside last night.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['many', 'much', 'a lot of']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях використовуємо **a lot of** з будь-якими іменниками. Приклад: *There was a lot of rain*.'],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ Правильно: **a lot of** підходить для незлічуваних іменників у стверджувальних реченнях (noise). Приклад: *There was a lot of traffic*.',
                        'many' => '❌ **Many** не використовується з незлічуваними іменниками (noise).',
                        'much' => '❌ **Much** зазвичай не використовується у стверджувальних реченнях, краще **a lot of**.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I need {a1} money for the school trip, Mum.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a little', 'a few', 'many']],
                'verb_hints' => [],
                'hints' => ['З незлічуваними іменниками для позначення невеликої кількості використовуємо **a little**. Приклад: *I need a little help*.'],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ Правильно: **a little** використовується з незлічуваними іменниками (money). Приклад: *I need a little time*.',
                        'a few' => '❌ **A few** використовується зі злічуваними іменниками, а money — незлічуваний.',
                        'many' => '❌ **Many** використовується зі злічуваними іменниками у запереченнях/питаннях.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'There are {a1} interesting things you can do online.',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a few', 'a little', 'any']],
                'verb_hints' => [],
                'hints' => ['Зі злічуваними іменниками для позначення невеликої кількості використовуємо **a few**. Приклад: *There are a few apples*.'],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ Правильно: **a few** використовується зі злічуваними іменниками (things). Приклад: *There are a few students*.',
                        'a little' => '❌ **A little** використовується з незлічуваними іменниками, а things — злічуваний.',
                        'any' => '❌ **Any** зазвичай використовується у запереченнях та питаннях, не у стверджувальних реченнях.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'There is {a1} milk in the fridge, so we have to buy some.',
                'answers' => ['a1' => 'no'],
                'options' => ['a1' => ['no', 'none', 'any']],
                'verb_hints' => [],
                'hints' => ['Перед іменником для позначення відсутності використовуємо **no**. Приклад: *There is no water*.'],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ Правильно: **no** стоїть перед іменником (milk) і означає повну відсутність. Приклад: *There is no time*.',
                        'none' => '❌ **None** використовується самостійно без іменника після нього.',
                        'any' => '❌ **Any** використовується у запереченнях з **not**, але тут потрібне **no** без **not**.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => "He doesn't have {a1} hobbies.",
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'no']],
                'verb_hints' => [],
                'hints' => ['У запереченнях зі злічуваними іменниками використовуємо **many**. Приклад: *He doesn\'t have many friends*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** використовується у запереченнях зі злічуваними іменниками (hobbies). Приклад: *She doesn\'t have many books*.',
                        'much' => '❌ **Much** використовується з незлічуваними іменниками, а hobbies — злічуваний.',
                        'no' => '❌ **No** не використовується з **doesn\'t**, бо це подвійне заперечення.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => "'How many laptops do you have at home?' '{a1}'.",
                'answers' => ['a1' => 'None'],
                'options' => ['a1' => ['None', 'Any', 'No']],
                'verb_hints' => [],
                'hints' => ['У короткій відповіді для позначення нульової кількості використовуємо **None**. Приклад: *How many cars? None*.'],
                'explanations' => [
                    'a1' => [
                        'None' => '✅ Правильно: **None** використовується самостійно без іменника як коротка відповідь. Приклад: *How many books? None*.',
                        'Any' => '❌ **Any** не підходить для відповіді про нульову кількість.',
                        'No' => '❌ **No** потребує іменника після себе: *No laptops*.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => 'I can help you; I have {a1} free time today.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях використовуємо **a lot of** замість much/many. Приклад: *I have a lot of friends*.'],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ Правильно: **a lot of** використовується у стверджувальних реченнях з будь-якими іменниками (time). Приклад: *I have a lot of money*.',
                        'much' => '❌ **Much** зазвичай використовується у запереченнях та питаннях.',
                        'many' => '❌ **Many** використовується зі злічуваними іменниками у множині, а time — незлічуваний.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} water do we need for the soup?',
                'answers' => ['a1' => 'How much'],
                'options' => ['a1' => ['How much', 'How many', 'Much']],
                'verb_hints' => [],
                'hints' => ['У питаннях про кількість незлічуваних іменників використовуємо **How much**. Приклад: *How much milk?*.'],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ Правильно: **How much** для питань про незлічувані іменники (water). Приклад: *How much bread do you want?*.',
                        'How many' => '❌ **How many** для злічуваних іменників у множині.',
                        'Much' => '❌ **Much** без **How** не утворює питальної конструкції на початку речення.',
                    ],
                ],
                'level' => 'A1',
            ],
            [
                'question' => '{a1} students are in your class?',
                'answers' => ['a1' => 'How many'],
                'options' => ['a1' => ['How much', 'How many', 'Much']],
                'verb_hints' => [],
                'hints' => ['У питаннях про кількість злічуваних іменників використовуємо **How many**. Приклад: *How many books?*.'],
                'explanations' => [
                    'a1' => [
                        'How many' => '✅ Правильно: **How many** для питань про злічувані іменники (students). Приклад: *How many chairs are there?*.',
                        'How much' => '❌ **How much** для незлічуваних іменників.',
                        'Much' => '❌ **Much** без **How** не утворює питальної конструкції.',
                    ],
                ],
                'level' => 'A1',
            ],

            // A2 Level - 12 questions
            [
                'question' => "We don't use {a1} paper now; most things are digital.",
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'a few']],
                'verb_hints' => [],
                'hints' => ['У запереченнях з незлічуваними іменниками використовуємо **much**. Приклад: *We don\'t have much information*.'],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ Правильно: **much** у запереченнях з незлічуваними іменниками (paper). Приклад: *We don\'t have much experience*.',
                        'many' => '❌ **Many** для злічуваних іменників у множині.',
                        'a few' => '❌ **A few** для злічуваних іменників у стверджувальному контексті.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => "There's {a1} traffic this morning; the roads are full.",
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a few', 'a lot of', 'many']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях **a lot of** підходить для будь-яких іменників. Приклад: *There\'s a lot of work*.'],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ Правильно: **a lot of** у стверджувальних реченнях з незлічуваними іменниками (traffic). Приклад: *There\'s a lot of snow*.',
                        'a few' => '❌ **A few** для злічуваних іменників у множині.',
                        'many' => '❌ **Many** для злічуваних іменників.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'They moved here {a1} years ago.',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a little', 'a few', 'much']],
                'verb_hints' => [],
                'hints' => ['Зі злічуваними іменниками для невеликої кількості використовуємо **a few**. Приклад: *a few days ago*.'],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ Правильно: **a few** зі злічуваними іменниками (years). Приклад: *I met him a few months ago*.',
                        'a little' => '❌ **A little** для незлічуваних іменників.',
                        'much' => '❌ **Much** зазвичай у запереченнях та питаннях.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'I only want {a1} milk in my coffee.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a little', 'a few', 'many']],
                'verb_hints' => [],
                'hints' => ['З незлічуваними іменниками для невеликої кількості використовуємо **a little**. Приклад: *a little sugar*.'],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ Правильно: **a little** з незлічуваними іменниками (milk). Приклад: *Add a little salt*.',
                        'a few' => '❌ **A few** для злічуваних іменників.',
                        'many' => '❌ **Many** для злічуваних іменників у запереченнях/питаннях.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'We had {a1} problems with the project; everything was fine.',
                'answers' => ['a1' => 'no'],
                'options' => ['a1' => ['no', 'any', 'many']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях для відсутності використовуємо **no** перед іменником. Приклад: *We had no issues*.'],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ Правильно: **no** перед іменником (problems) означає повну відсутність. Приклад: *There were no mistakes*.',
                        'any' => '❌ **Any** зазвичай у запереченнях з **not** або у питаннях.',
                        'many' => '❌ **Many** не підходить за контекстом «все було добре».',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => "I don't have {a1} friends in this city yet.",
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'no']],
                'verb_hints' => [],
                'hints' => ['У запереченнях зі злічуваними іменниками використовуємо **many**. Приклад: *I don\'t know many people*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** у запереченнях зі злічуваними іменниками (friends). Приклад: *She doesn\'t have many relatives*.',
                        'much' => '❌ **Much** для незлічуваних іменників.',
                        'no' => '❌ **No** не використовується з **don\'t** (подвійне заперечення).',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'He spends {a1} time playing video games.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a1' => ['a lot of', 'many', 'a few']],
                'verb_hints' => [],
                'hints' => ['У стверджувальних реченнях **a lot of** підходить для будь-яких іменників. Приклад: *She spends a lot of money*.'],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ Правильно: **a lot of** у стверджувальних реченнях з незлічуваними іменниками (time). Приклад: *He has a lot of patience*.',
                        'many' => '❌ **Many** для злічуваних іменників у множині.',
                        'a few' => '❌ **A few** для злічуваних іменників.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'How {a1} emails did you send yesterday?',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'lot of']],
                'verb_hints' => [],
                'hints' => ['У питаннях зі злічуваними іменниками використовуємо **How many**. Приклад: *How many calls did you make?*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** у питаннях зі злічуваними іменниками (emails). Приклад: *How many times did you try?*.',
                        'much' => '❌ **Much** для незлічуваних іменників.',
                        'lot of' => '❌ **Lot of** не використовується у питаннях, потрібен артикль **a lot of**.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'He has {a1} cousins; his family is huge.',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['a few', 'many', 'a lot of']],
                'verb_hints' => [],
                'hints' => ['Для великої кількості злічуваних іменників можна використовувати **many** або **a lot of**. Приклад: *She has many/a lot of books*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** для великої кількості злічуваних іменників (cousins). Приклад: *He has many hobbies*.',
                        'a few' => '❌ **A few** означає «кілька, небагато», а контекст вказує на велику кількість.',
                        'a lot of' => '✅ Також правильно: **a lot of** підходить для великої кількості у стверджувальних реченнях.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => '{a1} homework do you have today?',
                'answers' => ['a1' => 'How much'],
                'options' => ['a1' => ['How much', 'How many', 'Much']],
                'verb_hints' => [],
                'hints' => ['У питаннях про незлічувані іменники використовуємо **How much**. Приклад: *How much work do you have?*.'],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ Правильно: **How much** для незлічуваних іменників (homework). Приклад: *How much experience do you have?*.',
                        'How many' => '❌ **How many** для злічуваних іменників.',
                        'Much' => '❌ **Much** без **How** не утворює питальної конструкції.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'Can I have {a1} water, please?',
                'answers' => ['a1' => 'a little'],
                'options' => ['a1' => ['a few', 'a little', 'much']],
                'verb_hints' => [],
                'hints' => ['У ввічливих проханнях з незлічуваними іменниками використовуємо **a little**. Приклад: *Can I have a little help?*.'],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ Правильно: **a little** з незлічуваними іменниками (water). Приклад: *Can I have a little more time?*.',
                        'a few' => '❌ **A few** для злічуваних іменників.',
                        'much' => '❌ **Much** зазвичай у запереченнях та питаннях, але не у проханнях.',
                    ],
                ],
                'level' => 'A2',
            ],
            [
                'question' => 'There are {a1} chairs in the room; we need more.',
                'answers' => ['a1' => 'a few'],
                'options' => ['a1' => ['a few', 'many', 'no']],
                'verb_hints' => [],
                'hints' => ['**A few** означає «кілька, але недостатньо» у контексті нестачі. Приклад: *There are a few options*.'],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ Правильно: **a few** означає невелику кількість злічуваних іменників (chairs), контекст вказує на нестачу. Приклад: *There are a few tickets left*.',
                        'many' => '❌ **Many** означає багато, але контекст каже про нестачу.',
                        'no' => '❌ **No** означає повну відсутність, але речення каже «we need more», тобто щось є.',
                    ],
                ],
                'level' => 'A2',
            ],

            // B1 Level - 12 questions
            [
                'question' => '{a1} people were at the match? Was it full?',
                'answers' => ['a1' => 'How many'],
                'options' => ['a1' => ['How many', 'How much', 'Many']],
                'verb_hints' => [],
                'hints' => ['**How many** для питань про кількість людей (злічуваних іменників). Приклад: *How many guests came to the party?*.'],
                'explanations' => [
                    'a1' => [
                        'How many' => '✅ Правильно: **How many** для злічуваних іменників (people). Приклад: *How many participants were there?*.',
                        'How much' => '❌ **How much** для незлічуваних іменників.',
                        'Many' => '❌ **Many** без **How** не утворює питання про кількість.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'We have {a1} time before the train leaves, so hurry up.',
                'answers' => ['a1' => 'no'],
                'options' => ['a1' => ['no', 'a little', 'much']],
                'verb_hints' => [],
                'hints' => ['**No** + іменник означає повну відсутність. Контекст «hurry up» підказує, що часу немає. Приклад: *We have no choice*.'],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ Правильно: **no time** означає відсутність часу, контекст підтверджує терміновість. Приклад: *We have no money left*.',
                        'a little' => '❌ **A little** означає «трохи є», але контекст вказує на відсутність часу.',
                        'much' => '❌ **Much** у стверджувальних реченнях не використовується.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => "They haven't got {a1} money this month.",
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['many', 'much', 'any']],
                'verb_hints' => [],
                'hints' => ['У запереченнях з незлічуваними іменниками використовуємо **much** або **any**. Приклад: *They haven\'t got much experience*.'],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ Правильно: **much** у запереченнях з незлічуваними іменниками (money). Приклад: *I haven\'t got much time*.',
                        'many' => '❌ **Many** для злічуваних іменників.',
                        'any' => '✅ Також правильно: **any** може використовуватися у запереченнях: *They haven\'t got any money*.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'I invited some friends, but {a1} of them could come.',
                'answers' => ['a1' => 'none'],
                'options' => ['a1' => ['no', 'none', 'any']],
                'verb_hints' => [],
                'hints' => ['**None of** використовується для позначення нульової кількості з множиною або групою. Приклад: *None of the students passed*.'],
                'explanations' => [
                    'a1' => [
                        'none' => '✅ Правильно: **none of them** означає «жоден із них». Приклад: *None of my colleagues agreed*.',
                        'no' => '❌ **No** потребує іменника безпосередньо після: *no friends*, а не *no of them*.',
                        'any' => '❌ **Any** не підходить у стверджувальних реченнях для нульової кількості.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'I like this singer {a1}; I listen to her every day.',
                'answers' => ['a1' => 'a lot'],
                'options' => ['a1' => ['much', 'a lot', 'many']],
                'verb_hints' => [],
                'hints' => ['Після дієслова у стверджувальних реченнях використовуємо **a lot** (не much/many). Приклад: *I study a lot*.'],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ Правильно: **a lot** після дієслова у стверджувальних реченнях. Приклад: *She travels a lot*.',
                        'much' => '❌ **Much** зазвичай у запереченнях та питаннях.',
                        'many' => '❌ **Many** для злічуваних іменників, не після дієслова без іменника.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'At the weekend we do {a1} different activities with the kids.',
                'answers' => ['a1' => 'many'],
                'options' => ['a1' => ['many', 'much', 'a little']],
                'verb_hints' => [],
                'hints' => ['Зі злічуваними іменниками у стверджувальних реченнях можна використовувати **many** для наголошення на кількості. Приклад: *We visited many places*.'],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ Правильно: **many** зі злічуваними іменниками (activities) для наголошення. Приклад: *Many people attended the event*.',
                        'much' => '❌ **Much** для незлічуваних іменників.',
                        'a little' => '❌ **A little** для незлічуваних іменників і означає «трохи».',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => "There isn't {a1} information available about this topic online.",
                'answers' => ['a1' => 'much'],
                'options' => ['a1' => ['much', 'many', 'few']],
                'verb_hints' => [],
                'hints' => ['У запереченнях з незлічуваними іменниками використовуємо **much**. Приклад: *There isn\'t much data*.'],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ Правильно: **much** у запереченнях з незлічуваними іменниками (information). Приклад: *There isn\'t much evidence*.',
                        'many' => '❌ **Many** для злічуваних іменників.',
                        'few' => '❌ **Few** для злічуваних іменників, і не з **isn\'t**.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'The restaurant was almost empty; there were {a1} customers.',
                'answers' => ['a1' => 'few'],
                'options' => ['a1' => ['few', 'little', 'a few']],
                'verb_hints' => [],
                'hints' => ['**Few** (без артикля) означає «мало, майже немає» зі злічуваними іменниками. Приклад: *Few people know this*.'],
                'explanations' => [
                    'a1' => [
                        'few' => '✅ Правильно: **few** означає недостатню кількість злічуваних іменників (customers). Приклад: *Few students passed the test*.',
                        'little' => '❌ **Little** для незлічуваних іменників.',
                        'a few' => '❌ **A few** має позитивне значення «кілька є», а контекст каже про майже порожній ресторан.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => "I'm disappointed; the book gave me {a1} new knowledge.",
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'a little']],
                'verb_hints' => [],
                'hints' => ['**Little** (без артикля) означає «мало, недостатньо» з незлічуваними іменниками. Приклад: *There is little hope*.'],
                'explanations' => [
                    'a1' => [
                        'little' => '✅ Правильно: **little** означає недостатню кількість незлічуваних іменників (knowledge). Приклад: *He has little experience*.',
                        'few' => '❌ **Few** для злічуваних іменників.',
                        'a little' => '❌ **A little** має позитивне значення «трохи є», а контекст показує розчарування.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'Could you give me {a1} advice on this matter?',
                'answers' => ['a1' => 'some'],
                'options' => ['a1' => ['some', 'any', 'many']],
                'verb_hints' => [],
                'hints' => ['У ввічливих проханнях використовуємо **some** замість **any**. Приклад: *Could you give me some help?*.'],
                'explanations' => [
                    'a1' => [
                        'some' => '✅ Правильно: **some** у ввічливих проханнях, навіть у питаннях (advice — незлічуваний). Приклад: *Could you lend me some money?*.',
                        'any' => '❌ **Any** у звичайних питаннях, але у проханнях краще **some**.',
                        'many' => '❌ **Many** для злічуваних іменників.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'We made {a1} mistakes during the presentation.',
                'answers' => ['a1' => 'several'],
                'options' => ['a1' => ['several', 'much', 'little']],
                'verb_hints' => [],
                'hints' => ['**Several** означає «кілька, декілька» зі злічуваними іменниками і має нейтральне значення. Приклад: *Several attempts were made*.'],
                'explanations' => [
                    'a1' => [
                        'several' => '✅ Правильно: **several** зі злічуваними іменниками (mistakes) означає «кілька». Приклад: *I called him several times*.',
                        'much' => '❌ **Much** для незлічуваних іменників.',
                        'little' => '❌ **Little** для незлічуваних іменників.',
                    ],
                ],
                'level' => 'B1',
            ],
            [
                'question' => 'Is there {a1} space left in the car for one more bag?',
                'answers' => ['a1' => 'any'],
                'options' => ['a1' => ['any', 'some', 'many']],
                'verb_hints' => [],
                'hints' => ['У загальних питаннях (так/ні) використовуємо **any**. Приклад: *Is there any milk?*.'],
                'explanations' => [
                    'a1' => [
                        'any' => '✅ Правильно: **any** у питаннях з незлічуваними іменниками (space). Приклад: *Is there any room available?*.',
                        'some' => '❌ **Some** у проханнях або коли очікуємо позитивну відповідь.',
                        'many' => '❌ **Many** для злічуваних іменників.',
                    ],
                ],
                'level' => 'B1',
            ],

            // B2 Level - 12 questions
            [
                'question' => 'The project requires {a1} resources than we initially anticipated.',
                'answers' => ['a1' => 'more'],
                'options' => ['a1' => ['more', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['**More** використовується у порівняннях для вказівки на більшу кількість. Приклад: *We need more time than expected*.'],
                'explanations' => [
                    'a1' => [
                        'more' => '✅ Правильно: **more** для порівняння кількості з **than**. Приклад: *This costs more money than I thought*.',
                        'many' => '❌ **Many** не утворює порівняння з **than**.',
                        'much' => '❌ **Much** не утворює порівняння з **than**, потрібно **more**.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The survey revealed that {a1} of respondents agreed.',
                'answers' => ['a1' => 'most'],
                'options' => ['a1' => ['most', 'more', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'most' => 'Правильна відповідь: most є найкращим варіантом для цього контексту.',
                        'more' => 'Неправильно: more не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'Surprisingly, {a1} attention was paid to the issue.',
                'answers' => ['a1' => 'little'],
                'options' => ['a1' => ['little', 'few', 'a little']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'little' => 'Правильна відповідь: little є найкращим варіантом для цього контексту.',
                        'few' => 'Неправильно: few не підходить у цьому контексті.',
                        'a little' => 'Неправильно: a little не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The research indicates that {a1} of people prefer online shopping.',
                'answers' => ['a1' => 'the majority'],
                'options' => ['a1' => ['the majority', 'most', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'the majority' => 'Правильна відповідь: the majority є найкращим варіантом для цього контексту.',
                        'most' => 'Неправильно: most не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'There has been {a1} investment in renewable energy.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'considerable' => 'Правильна відповідь: considerable є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The company made {a1} improvements to customer service.',
                'answers' => ['a1' => 'substantial'],
                'options' => ['a1' => ['substantial', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'substantial' => 'Правильна відповідь: substantial є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => '{a1} evidence suggests the theory is correct.',
                'answers' => ['a1' => 'Ample'],
                'options' => ['a1' => ['Ample', 'Many', 'Much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'Ample' => 'Правильна відповідь: Ample є найкращим варіантом для цього контексту.',
                        'Many' => 'Неправильно: Many не підходить у цьому контексті.',
                        'Much' => 'Неправильно: Much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The team demonstrated {a1} dedication throughout the project.',
                'answers' => ['a1' => 'tremendous'],
                'options' => ['a1' => ['tremendous', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'tremendous' => 'Правильна відповідь: tremendous є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'Despite {a1} setbacks, the company persevered.',
                'answers' => ['a1' => 'numerous'],
                'options' => ['a1' => ['numerous', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'numerous' => 'Правильна відповідь: numerous є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The policy has had {a1} impact on reducing pollution.',
                'answers' => ['a1' => 'minimal'],
                'options' => ['a1' => ['minimal', 'little', 'few']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'minimal' => 'Правильна відповідь: minimal є найкращим варіантом для цього контексту.',
                        'little' => 'Неправильно: little не підходить у цьому контексті.',
                        'few' => 'Неправильно: few не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'The legislation has had {a1} consequences for small businesses.',
                'answers' => ['a1' => 'far-reaching'],
                'options' => ['a1' => ['far-reaching', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'far-reaching' => 'Правильна відповідь: far-reaching є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'There is {a1} evidence to support the hypothesis.',
                'answers' => ['a1' => 'scant'],
                'options' => ['a1' => ['scant', 'little', 'few']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'scant' => 'Правильна відповідь: scant є найкращим варіантом для цього контексту.',
                        'little' => 'Неправильно: little не підходить у цьому контексті.',
                        'few' => 'Неправильно: few не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The report provided {a1} insight into underlying issues.',
                'answers' => ['a1' => 'scarcely any'],
                'options' => ['a1' => ['scarcely any', 'little', 'few']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'scarcely any' => 'Правильна відповідь: scarcely any є найкращим варіантом для цього контексту.',
                        'little' => 'Неправильно: little не підходить у цьому контексті.',
                        'few' => 'Неправильно: few не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The study revealed {a1} correlation between the two variables.',
                'answers' => ['a1' => 'negligible'],
                'options' => ['a1' => ['negligible', 'little', 'small']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'negligible' => 'Правильна відповідь: negligible є найкращим варіантом для цього контексту.',
                        'little' => 'Неправильно: little не підходить у цьому контексті.',
                        'small' => 'Неправильно: small не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The initiative garnered {a1} support from the community.',
                'answers' => ['a1' => 'overwhelming'],
                'options' => ['a1' => ['overwhelming', 'much', 'a lot of']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'overwhelming' => 'Правильна відповідь: overwhelming є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'a lot of' => 'Неправильно: a lot of не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The discourse encompasses {a1} perspectives on the matter.',
                'answers' => ['a1' => 'a plethora of'],
                'options' => ['a1' => ['a plethora of', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'a plethora of' => 'Правильна відповідь: a plethora of є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The phenomenon occurs with {a1} frequency in urban areas.',
                'answers' => ['a1' => 'increasing'],
                'options' => ['a1' => ['increasing', 'more', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'increasing' => 'Правильна відповідь: increasing є найкращим варіантом для цього контексту.',
                        'more' => 'Неправильно: more не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The methodology employed {a1} rigor in data collection.',
                'answers' => ['a1' => 'utmost'],
                'options' => ['a1' => ['utmost', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'utmost' => 'Правильна відповідь: utmost є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The findings warrant {a1} scrutiny before publication.',
                'answers' => ['a1' => 'rigorous'],
                'options' => ['a1' => ['rigorous', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'rigorous' => 'Правильна відповідь: rigorous є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The argument rests on {a1} assumptions that require validation.',
                'answers' => ['a1' => 'tenuous'],
                'options' => ['a1' => ['tenuous', 'few', 'weak']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'tenuous' => 'Правильна відповідь: tenuous є найкращим варіантом для цього контексту.',
                        'few' => 'Неправильно: few не підходить у цьому контексті.',
                        'weak' => 'Неправильно: weak не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The research encompasses {a1} array of methodologies.',
                'answers' => ['a1' => 'a diverse'],
                'options' => ['a1' => ['a diverse', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'a diverse' => 'Правильна відповідь: a diverse є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The theory has gained {a1} traction in academic circles.',
                'answers' => ['a1' => 'considerable'],
                'options' => ['a1' => ['considerable', 'much', 'many']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'considerable' => 'Правильна відповідь: considerable є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C1',
            ],
            [
                'question' => 'The phenomenon manifests with {a1} regularity in controlled environments.',
                'answers' => ['a1' => 'unfailing'],
                'options' => ['a1' => ['unfailing', 'much', 'great']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'unfailing' => 'Правильна відповідь: unfailing є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'great' => 'Неправильно: great не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The hypothesis withstood {a1} scrutiny from peer reviewers.',
                'answers' => ['a1' => 'withering'],
                'options' => ['a1' => ['withering', 'much', 'severe']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'withering' => 'Правильна відповідь: withering є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'severe' => 'Неправильно: severe не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The dissertation demonstrates {a1} command of the subject matter.',
                'answers' => ['a1' => 'an unparalleled'],
                'options' => ['a1' => ['an unparalleled', 'much', 'great']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'an unparalleled' => 'Правильна відповідь: an unparalleled є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'great' => 'Неправильно: great не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The paradigm shift encountered {a1} resistance from traditionalists.',
                'answers' => ['a1' => 'formidable'],
                'options' => ['a1' => ['formidable', 'much', 'strong']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'formidable' => 'Правильна відповідь: formidable є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'strong' => 'Неправильно: strong не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The methodology exhibited {a1} sophistication in its framework.',
                'answers' => ['a1' => 'unprecedented'],
                'options' => ['a1' => ['unprecedented', 'much', 'great']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'unprecedented' => 'Правильна відповідь: unprecedented є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'great' => 'Неправильно: great не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The treatise provoked {a1} debate among scholars.',
                'answers' => ['a1' => 'vociferous'],
                'options' => ['a1' => ['vociferous', 'much', 'heated']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'vociferous' => 'Правильна відповідь: vociferous є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'heated' => 'Неправильно: heated не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The proposition garnered {a1} acclaim from the scientific community.',
                'answers' => ['a1' => 'widespread'],
                'options' => ['a1' => ['widespread', 'much', 'great']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'widespread' => 'Правильна відповідь: widespread є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'great' => 'Неправильно: great не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The theory underwent {a1} revision before acceptance.',
                'answers' => ['a1' => 'exhaustive'],
                'options' => ['a1' => ['exhaustive', 'much', 'thorough']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'exhaustive' => 'Правильна відповідь: exhaustive є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'thorough' => 'Неправильно: thorough не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The empirical data yielded {a1} insights into the phenomenon.',
                'answers' => ['a1' => 'invaluable'],
                'options' => ['a1' => ['invaluable', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'invaluable' => 'Правильна відповідь: invaluable є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The dissertation exhibits {a1} erudition across disciplines.',
                'answers' => ['a1' => 'prodigious'],
                'options' => ['a1' => ['prodigious', 'much', 'great']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'prodigious' => 'Правильна відповідь: prodigious є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'great' => 'Неправильно: great не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The manuscript demonstrates {a1} attention to methodological rigor.',
                'answers' => ['a1' => 'meticulous'],
                'options' => ['a1' => ['meticulous', 'much', 'careful']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'meticulous' => 'Правильна відповідь: meticulous є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'careful' => 'Неправильно: careful не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The critique elicited {a1} response from the establishment.',
                'answers' => ['a1' => 'a vehement'],
                'options' => ['a1' => ['a vehement', 'much', 'strong']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'a vehement' => 'Правильна відповідь: a vehement є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'strong' => 'Неправильно: strong не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'C2',
            ],
            [
                'question' => 'The presentation contained {a1} errors that needed correction.',
                'answers' => ['a1' => 'several'],
                'options' => ['a1' => ['several', 'much', 'little']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'several' => 'Правильна відповідь: several є найкращим варіантом для цього контексту.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                        'little' => 'Неправильно: little не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
            [
                'question' => 'We observed {a1} improvement in the results over time.',
                'answers' => ['a1' => 'gradual'],
                'options' => ['a1' => ['gradual', 'many', 'much']],
                'verb_hints' => [],
                'hints' => ['Оберіть правильний квантіфікатор для завершення речення згідно з правилами англійської граматики.'],
                'explanations' => [
                    'a1' => [
                        'gradual' => 'Правильна відповідь: gradual є найкращим варіантом для цього контексту.',
                        'many' => 'Неправильно: many не підходить у цьому контексті.',
                        'much' => 'Неправильно: much не підходить у цьому контексті.',
                    ],
                ],
                'level' => 'B2',
            ],
        ];
    }
}
