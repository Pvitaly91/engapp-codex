<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuantityComparisonsMuchManyV2Seeder extends QuestionSeeder
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
            'page1' => Source::firstOrCreate(['name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Page 1'])->id,
            'page2' => Source::firstOrCreate(['name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Page 2'])->id,
            'page3' => Source::firstOrCreate(['name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Page 3'])->id,
        ];
        $defaultSourceId = $sourceIds['page1'];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers Practice: Much / Many / A lot / Few / Little'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Much vs Many / A lot / Few vs Little'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Countable vs Uncountable Quantifiers'],
            ['category' => 'English Grammar Structure']
        )->id;

        $questions = $this->questionEntries();

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
                'source_id' => $sourceIds[$entry['source']] ?? $defaultSourceId,
                'flag' => 0,
                'type' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
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
            [
                'question' => 'If I want to pass the exam, I need to study {a1}.',
                'options' => [
                    'a1' => ['a lot', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Після *study* потрібен прислівник кількості → **a lot**. Для іменника було б *a lot of + noun*.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ **A lot** працює як прислівник і підсилює дієслово *study*.',
                        'a lot of' => '❌ *A lot of* вимагає іменника (a lot of time), а тут його немає.',
                        'many' => '❌ *Many* ставиться перед злічуваним іменником, а не після дієслова.',
                    ],
                ],
            ],
            [
                'question' => 'There aren’t {a1} things to do in this village.',
                'options' => [
                    'a1' => ['many', 'a lot', 'much'],
                ],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Злічуваний іменник *things* у запереченні → **many**.',
                ],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ **Many** вживаємо зі злічуваними іменниками у запереченнях.',
                        'a lot' => '❌ *A lot* потребує *of* перед іменником (a lot of things).',
                        'much' => '❌ *Much* уживається з незлічуваними іменниками.',
                    ],
                ],
            ],
            [
                'question' => '{a1} sugar do you take in your tea?',
                'options' => [
                    'a1' => ['How many', 'How little', 'How much'],
                ],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Sugar — незлічуваний іменник, тому питаємо **How much**.',
                ],
                'explanations' => [
                    'a1' => [
                        'How many' => '❌ *How many* лише для злічуваних іменників.',
                        'How little' => '❌ *How little* питає про дуже малу кількість і не є базовою формою.',
                        'How much' => '✅ **How much** правильно для незлічуваних іменників.',
                    ],
                ],
            ],
            [
                'question' => 'There was {a1} tension at the meeting.',
                'options' => [
                    'a1' => ['much', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => '*Tension* — незлічуване, потрібне **a lot of**.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '❌ *Much* частіше вживається у запереченнях/питаннях, тут ствердження.',
                        'a lot of' => '✅ **A lot of** підходить для ствердження з незлічуваним іменником.',
                        'many' => '❌ *Many* тільки для злічуваних іменників.',
                    ],
                ],
            ],
            [
                'question' => 'Dad, I need {a1} money for school.',
                'options' => [
                    'a1' => ['a little', 'many', 'a few'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => '*Money* — незлічуване, тому **a little**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ **A little** використовується з незлічуваними іменниками.',
                        'many' => '❌ *Many* лише зі злічуваними.',
                        'a few' => '❌ *A few* для злічуваних іменників.',
                    ],
                ],
            ],
            [
                'question' => 'There are {a1} things that you can do to improve your writing.',
                'options' => [
                    'a1' => ['any', 'a little', 'a few'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => '*Things* — злічувані, у ствердженні → **a few**.',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '❌ *Any* типово в запереченнях/питаннях.',
                        'a little' => '❌ *A little* для незлічуваних іменників.',
                        'a few' => '✅ **A few** означає «кілька» зі злічуваними.',
                    ],
                ],
            ],
            [
                'question' => 'There is {a1} milk in the fridge. We need to buy some.',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Перед іменником *milk* ставимо **no**: no milk.',
                ],
                'explanations' => [
                    'a1' => [
                        'none' => '❌ *None* уживається без іменника (There is none).',
                        'no' => '✅ **No** стоїть перед іменником і означає «немає».',
                        'any' => '❌ *Any* у запереченні потребує допоміжного дієслова (*isn’t any milk*).',
                    ],
                ],
            ],
            [
                'question' => 'He doesn’t have {a1} hobbies.',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Після заперечення *doesn’t have* використовуємо **any**.',
                ],
                'explanations' => [
                    'a1' => [
                        'none' => '❌ *None* не ставиться перед іменником.',
                        'no' => '❌ *No* створить подвійне заперечення з *doesn’t*.',
                        'any' => '✅ **Any** правильно після заперечення.',
                    ],
                ],
            ],
            [
                'question' => '“How many computers do you have?” “{a1}.”',
                'options' => [
                    'a1' => ['None', 'Any', 'No'],
                ],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Коротка відповідь на *How many...?* → **None**.',
                ],
                'explanations' => [
                    'a1' => [
                        'None' => '✅ **None** = «жодного/жодної» у короткій відповіді.',
                        'Any' => '❌ *Any* не є відповіддю на запит про кількість.',
                        'No' => '❌ *No* потребує іменника або повної фрази (*No, I don’t*).',
                    ],
                ],
            ],
            [
                'question' => 'I can help you; I have {a1} time today.',
                'options' => [
                    'a1' => ['a lot of', 'much', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page1',
                'hints' => [
                    'a1' => 'Ствердження + незлічуваний *time* → **a lot of**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ **A lot of** добре звучить у ствердженнях.',
                        'much' => '❌ *Much* у ствердженнях звучить неприродно без підсилювачів.',
                        'many' => '❌ *Many* тільки для злічуваних іменників.',
                    ],
                ],
            ],
            [
                'question' => '“How much water do you drink?” “{a1}.”',
                'options' => [
                    'a1' => ['Much', 'A lot of', 'A lot'],
                ],
                'answers' => ['a1' => 'A lot'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Коротка відповідь без іменника → **A lot**.',
                ],
                'explanations' => [
                    'a1' => [
                        'Much' => '❌ *Much* у короткій відповіді звучить неприродно.',
                        'A lot of' => '❌ *A lot of* потребує іменника після себе.',
                        'A lot' => '✅ **A lot** — природна коротка відповідь.',
                    ],
                ],
            ],
            [
                'question' => '{a1} goals did they score?',
                'options' => [
                    'a1' => ['How many', 'How much', 'How little'],
                ],
                'answers' => ['a1' => 'How many'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => '*Goals* — злічувані → **How many**.',
                ],
                'explanations' => [
                    'a1' => [
                        'How many' => '✅ **How many** ставимо з злічуваними іменниками.',
                        'How much' => '❌ *How much* лише для незлічуваних.',
                        'How little' => '❌ *How little* питає про малу кількість і не підходить тут.',
                    ],
                ],
            ],
            [
                'question' => 'Nowadays we don’t use {a1} cash, because we use our credit cards.',
                'options' => [
                    'a1' => ['many', 'much', 'a lot'],
                ],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Заперечення + незлічуваний *cash* → **much**.',
                ],
                'explanations' => [
                    'a1' => [
                        'many' => '❌ *Many* тільки для злічуваних.',
                        'much' => '✅ **Much** типово в запереченнях з незлічуваними.',
                        'a lot' => '❌ *A lot* без *of* не стоїть перед іменником.',
                    ],
                ],
            ],
            [
                'question' => 'There’s {a1} pressure on the players.',
                'options' => [
                    'a1' => ['a lot of', 'much', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Ствердження + незлічуваний *pressure* → **a lot of**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ **A lot of** найприродніше у ствердженні.',
                        'much' => '❌ *Much* рідко у ствердженнях без підсилювачів.',
                        'many' => '❌ *Many* тільки для злічуваних.',
                    ],
                ],
            ],
            [
                'question' => 'They got married {a1} months after they met for the first time.',
                'options' => [
                    'a1' => ['much', 'a little', 'a few'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => '*Months* — злічувані, отже **a few**.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '❌ *Much* не використовується зі злічуваними іменниками.',
                        'a little' => '❌ *A little* тільки для незлічуваних.',
                        'a few' => '✅ **A few** означає «кілька» зі злічуваними.',
                    ],
                ],
            ],
            [
                'question' => '“How much milk do you want in your coffee?” “Only {a1}.”',
                'options' => [
                    'a1' => ['a little', 'a few', 'much'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => '*Milk* — незлічуваний, тому **a little**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ **A little** правильно для незлічуваних.',
                        'a few' => '❌ *A few* тільки зі злічуваними.',
                        'much' => '❌ *Much* не підходить після *Only* у короткій відповіді.',
                    ],
                ],
            ],
            [
                'question' => 'There were {a1} problems during the festival.',
                'options' => [
                    'a1' => ['any', 'no', 'none'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Перед іменником *problems* ставимо **no**: no problems.',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '❌ *Any* потребує заперечення (*weren’t any problems*).',
                        'no' => '✅ **No** напряму заперечує наявність проблем.',
                        'none' => '❌ *None* використовується без іменника.',
                    ],
                ],
            ],
            [
                'question' => 'I don’t want {a1} gifts.',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Після заперечення *don’t want* → **any**.',
                ],
                'explanations' => [
                    'a1' => [
                        'none' => '❌ *None* не ставиться перед іменником.',
                        'no' => '❌ *No* створить подвійне заперечення.',
                        'any' => '✅ **Any** правильно після заперечення.',
                    ],
                ],
            ],
            [
                'question' => '“How many gifts do you want?” “{a1}.”',
                'options' => [
                    'a1' => ['None', 'Any', 'No'],
                ],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Коротка відповідь про кількість → **None**.',
                ],
                'explanations' => [
                    'a1' => [
                        'None' => '✅ **None** = «жодного» у відповіді.',
                        'Any' => '❌ *Any* не відповідає на питання про кількість.',
                        'No' => '❌ *No* без іменника або фрази звучить неповно.',
                    ],
                ],
            ],
            [
                'question' => 'I eat {a1} vegetables.',
                'options' => [
                    'a1' => ['much', 'a lot of', 'many'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page2',
                'hints' => [
                    'a1' => 'Ствердження + злічувані *vegetables* → **a lot of**.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '❌ *Much* не вживається зі злічуваними.',
                        'a lot of' => '✅ **A lot of** підходить для злічуваних у ствердженні.',
                        'many' => '❌ *Many* можливе, але в A1-варіанті очікують **a lot of**.',
                    ],
                ],
            ],
            [
                'question' => 'He has {a1} paintings. It’s a really big collection.',
                'options' => [
                    'a1' => ['a lot of', 'many', 'a few'],
                ],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Велика колекція → **a lot of** paintings.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot of' => '✅ **A lot of** підкреслює велику кількість.',
                        'many' => '❌ *Many* можливе, але тут очікують розмовне **a lot of**.',
                        'a few' => '❌ *A few* означає «мало/кілька», що суперечить «big collection».',
                    ],
                ],
            ],
            [
                'question' => '{a1} salt do you put in your food?',
                'options' => [
                    'a1' => ['How much', 'How many', 'How little'],
                ],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => '*Salt* — незлічуваний → **How much**.',
                ],
                'explanations' => [
                    'a1' => [
                        'How much' => '✅ **How much** для незлічуваних іменників.',
                        'How many' => '❌ *How many* лише зі злічуваними.',
                        'How little' => '❌ *How little* означає «як мало», не базовий варіант.',
                    ],
                ],
            ],
            [
                'question' => 'I normally use {a1} makeup, but not much, only some lipstick.',
                'options' => [
                    'a1' => ['a little', 'a few', 'much'],
                ],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => '*Makeup* — незлічуване, потрібне **a little**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a little' => '✅ **A little** правильно з незлічуваними.',
                        'a few' => '❌ *A few* тільки для злічуваних.',
                        'much' => '❌ *Much* не підходить у ствердженні з *normally use*.',
                    ],
                ],
            ],
            [
                'question' => 'It rained {a1} last week; it rained every day.',
                'options' => [
                    'a1' => ['a lot', 'much', 'a few'],
                ],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Після дієслова *rained* потрібен прислівник → **a lot**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a lot' => '✅ **A lot** як прислівник описує інтенсивність дощу.',
                        'much' => '❌ *Much* самостійно після дієслова звучить неприродно.',
                        'a few' => '❌ *A few* використовується зі злічуваними іменниками, не з дієсловами.',
                    ],
                ],
            ],
            [
                'question' => 'They only scored one goal. They didn’t have {a1} opportunities to score; maybe two or three.',
                'options' => [
                    'a1' => ['many', 'much', 'a lot'],
                ],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Злічуваний *opportunities* у запереченні → **many**.',
                ],
                'explanations' => [
                    'a1' => [
                        'many' => '✅ **Many** у запереченнях зі злічуваними.',
                        'much' => '❌ *Much* лише для незлічуваних.',
                        'a lot' => '❌ *A lot* без *of* не стоїть перед іменником.',
                    ],
                ],
            ],
            [
                'question' => 'Please, I just need 5 minutes of your time. I only have {a1} questions.',
                'options' => [
                    'a1' => ['a few', 'a little', 'many'],
                ],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Злічуваний *questions* + «лише» → **a few**.',
                ],
                'explanations' => [
                    'a1' => [
                        'a few' => '✅ **A few** означає «кілька» зі злічуваними.',
                        'a little' => '❌ *A little* тільки з незлічуваними.',
                        'many' => '❌ *Many* суперечить «only 5 minutes».',
                    ],
                ],
            ],
            [
                'question' => 'We had {a1} problems. It was 100% perfect.',
                'options' => [
                    'a1' => ['no', 'none', 'any'],
                ],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Перед іменником *problems* ставимо **no**.',
                ],
                'explanations' => [
                    'a1' => [
                        'no' => '✅ **No** + іменник = «жодних проблем».',
                        'none' => '❌ *None* без іменника.',
                        'any' => '❌ *Any* потребує заперечення (*didn’t have any*).',
                    ],
                ],
            ],
            [
                'question' => 'We need to be fast; we haven’t got {a1} time, only 20 minutes.',
                'options' => [
                    'a1' => ['much', 'many', 'a lot of'],
                ],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Заперечення + незлічуваний *time* → **much**.',
                ],
                'explanations' => [
                    'a1' => [
                        'much' => '✅ **Much** типово в запереченнях з незлічуваними.',
                        'many' => '❌ *Many* тільки для злічуваних.',
                        'a lot of' => '❌ *A lot of* суперечить фразі «only 20 minutes».',
                    ],
                ],
            ],
            [
                'question' => 'I didn’t see {a1} people in the room. It was completely empty.',
                'options' => [
                    'a1' => ['any', 'no', 'none'],
                ],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Після *didn’t see* вживаємо **any**.',
                ],
                'explanations' => [
                    'a1' => [
                        'any' => '✅ **Any** після заперечення.',
                        'no' => '❌ *No* створить подвійне заперечення.',
                        'none' => '❌ *None* не ставиться перед іменником.',
                    ],
                ],
            ],
            [
                'question' => '“How many cups of coffee did you have?” “{a1}. I don’t drink coffee.”',
                'options' => [
                    'a1' => ['none', 'no', 'any'],
                ],
                'answers' => ['a1' => 'none'],
                'level' => 'A1',
                'source' => 'page3',
                'hints' => [
                    'a1' => 'Коротка відповідь про кількість → **none**.',
                ],
                'explanations' => [
                    'a1' => [
                        'none' => '✅ **None** = «жодного».',
                        'no' => '❌ *No* без іменника звучить неповно.',
                        'any' => '❌ *Any* не є відповіддю на *How many...?*.',
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
