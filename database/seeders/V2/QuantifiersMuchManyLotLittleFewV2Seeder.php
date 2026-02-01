<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class QuantifiersMuchManyLotLittleFewV2Seeder extends QuestionSeeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Quantifiers'])->id;

        $sourceIds = [
            'ex1' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Exercise 1',
            ])->id,
            'ex2' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Exercise 2',
            ])->id,
            'ex3' => Source::firstOrCreate([
                'name' => 'https://test-english.com/grammar-points/a1/much-many-lot-little-few/ - Exercise 3',
            ])->id,
        ];

        $themeTagId = Tag::firstOrCreate([
            'name' => 'Quantifiers Practice',
        ], ['category' => 'English Grammar Theme'])->id;

        $detailTagId = Tag::firstOrCreate([
            'name' => 'Much, Many, A Lot, Few, Little',
        ], ['category' => 'English Grammar Detail'])->id;

        $structureTagId = Tag::firstOrCreate([
            'name' => 'Quantifiers',
        ], ['category' => 'English Grammar Structure'])->id;

        $levelDifficulty = [
            'A1' => 1,
            'A2' => 2,
            'B1' => 3,
            'B2' => 4,
            'C1' => 5,
            'C2' => 5,
        ];

        $items = [];
        $meta = [];

        foreach ($this->questionEntries() as $entry) {
            $uuid = $this->generateQuestionUuid(
                'a1',
                'quantifiers',
                'much-many-lot-little-few',
                $entry['exercise'],
                $entry['index']
            );

            $answers = [
                [
                    'marker' => 'a1',
                    'answer' => $entry['answers']['a1'],
                    'verb_hint' => null,
                ],
            ];

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'difficulty' => $levelDifficulty[$entry['level']] ?? 1,
                'source_id' => $sourceIds[$entry['exercise']],
                'flag' => 0,
                'level' => $entry['level'],
                'tag_ids' => [$themeTagId, $detailTagId, $structureTagId],
                'answers' => $answers,
                'options' => $entry['options'],
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => $entry['hints'],
            ];
        }

        $this->seedQuestionData($items, $meta);
    }

    private function questionEntries(): array
    {
        $bank = ['much', 'How much', 'many', 'a lot of', 'a lot', 'a little', 'a few', 'any', 'no', 'none'];

        return [
            [
                'exercise' => 'ex1',
                'index' => 1,
                'question' => 'If I want to pass the exam, I need to study {a1}.',
                'options' => ['a lot', 'a lot of', 'many'],
                'answers' => ['a1' => 'a lot'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 2,
                'question' => 'There aren’t {a1} things to do in this village.',
                'options' => ['many', 'a lot', 'much'],
                'answers' => ['a1' => 'many'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 3,
                'question' => '{a1} sugar do you take in your tea?',
                'options' => ['How many', 'How little', 'How much'],
                'answers' => ['a1' => 'How much'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 4,
                'question' => 'There was {a1} tension at the meeting.',
                'options' => ['much', 'a lot of', 'many'],
                'answers' => ['a1' => 'a lot of'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 5,
                'question' => 'Dad, I need {a1} money for school.',
                'options' => ['a little', 'many', 'a few'],
                'answers' => ['a1' => 'a little'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 6,
                'question' => 'There are {a1} things that you can do to improve your writing.',
                'options' => ['any', 'a little', 'a few'],
                'answers' => ['a1' => 'a few'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 7,
                'question' => 'There is {a1} milk in the fridge. We need to buy some.',
                'options' => ['none', 'no', 'any'],
                'answers' => ['a1' => 'no'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 8,
                'question' => 'He doesn’t have {a1} hobbies.',
                'options' => ['none', 'no', 'any'],
                'answers' => ['a1' => 'any'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 9,
                'question' => '“How many computers do you have?” “{a1}.”',
                'options' => ['None', 'Any', 'No'],
                'answers' => ['a1' => 'None'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex1',
                'index' => 10,
                'question' => 'I can help you; I have {a1} time today.',
                'options' => ['a lot of', 'much', 'many'],
                'answers' => ['a1' => 'a lot of'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 1,
                'question' => '“How much water do you drink?” “{a1}.”',
                'options' => ['Much', 'A lot of', 'A lot'],
                'answers' => ['a1' => 'A lot'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 2,
                'question' => '{a1} goals did they score?',
                'options' => ['How many', 'How much', 'How little'],
                'answers' => ['a1' => 'How many'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 3,
                'question' => 'Nowadays we don’t use {a1} cash, because we use our credit cards.',
                'options' => ['many', 'much', 'a lot'],
                'answers' => ['a1' => 'much'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 4,
                'question' => 'There’s {a1} pressure on the players.',
                'options' => ['a lot of', 'much', 'many'],
                'answers' => ['a1' => 'a lot of'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 5,
                'question' => 'They got married {a1} months after they met for the first time.',
                'options' => ['much', 'a little', 'a few'],
                'answers' => ['a1' => 'a few'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 6,
                'question' => '“How much milk do you want in your coffee?” “Only {a1}.”',
                'options' => ['a little', 'a few', 'much'],
                'answers' => ['a1' => 'a little'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 7,
                'question' => 'There were {a1} problems during the festival.',
                'options' => ['any', 'no', 'none'],
                'answers' => ['a1' => 'no'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 8,
                'question' => 'I don’t want {a1} gifts.',
                'options' => ['none', 'no', 'any'],
                'answers' => ['a1' => 'any'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 9,
                'question' => '“How many gifts do you want?” “{a1}.”',
                'options' => ['None', 'Any', 'No'],
                'answers' => ['a1' => 'None'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex2',
                'index' => 10,
                'question' => 'I eat {a1} vegetables.',
                'options' => ['much', 'a lot of', 'many'],
                'answers' => ['a1' => 'a lot of'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 1,
                'question' => 'He has {a1} paintings. It’s a really big collection.',
                'options' => $bank,
                'answers' => ['a1' => 'a lot of'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 2,
                'question' => '{a1} salt do you put in your food?',
                'options' => $bank,
                'answers' => ['a1' => 'How much'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 3,
                'question' => 'I normally use {a1} makeup, but not much, only some lipstick.',
                'options' => $bank,
                'answers' => ['a1' => 'a little'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 4,
                'question' => 'It rained {a1} last week; it rained every day.',
                'options' => $bank,
                'answers' => ['a1' => 'a lot'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 5,
                'question' => 'They only scored one goal. They didn’t have {a1} opportunities to score; maybe two or three.',
                'options' => $bank,
                'answers' => ['a1' => 'many'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 6,
                'question' => 'Please, I just need 5 minutes of your time. I only have {a1} questions.',
                'options' => $bank,
                'answers' => ['a1' => 'a few'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 7,
                'question' => 'We had {a1} problems. It was 100% perfect.',
                'options' => $bank,
                'answers' => ['a1' => 'no'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 8,
                'question' => 'We need to be fast; we haven’t got {a1} time, only 20 minutes.',
                'options' => $bank,
                'answers' => ['a1' => 'much'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 9,
                'question' => 'I didn’t see {a1} people in the room. It was completely empty.',
                'options' => $bank,
                'answers' => ['a1' => 'any'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
            [
                'exercise' => 'ex3',
                'index' => 10,
                'question' => '“How many cups of coffee did you have?” “{a1}. I don’t drink coffee.”',
                'options' => $bank,
                'answers' => ['a1' => 'none'],
                'hints' => ['Quantifiers (A1)'],
                'level' => 'A1',
            ],
        ];
    }
}
