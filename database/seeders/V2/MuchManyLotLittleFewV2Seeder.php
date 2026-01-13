<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MuchManyLotLittleFewV2Seeder extends QuestionSeeder
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

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers (A1)'],
            ['category' => 'English Grammar Theme']
        )->id;

        $items = [];
        $meta = [];

        $exercise1 = [
            [
                'question' => 'If I want to pass the exam, I need to study {a1}.',
                'answers' => ['a1' => 'a lot'],
                'options' => ['a lot', 'a lot of', 'many'],
            ],
            [
                'question' => 'There aren’t {a1} things to do in this village.',
                'answers' => ['a1' => 'many'],
                'options' => ['many', 'a lot', 'much'],
            ],
            [
                'question' => '{a1} sugar do you take in your tea?',
                'answers' => ['a1' => 'How much'],
                'options' => ['How many', 'How little', 'How much'],
            ],
            [
                'question' => 'There was {a1} tension at the meeting.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['much', 'a lot of', 'many'],
            ],
            [
                'question' => 'Dad, I need {a1} money for school.',
                'answers' => ['a1' => 'a little'],
                'options' => ['a little', 'many', 'a few'],
            ],
            [
                'question' => 'There are {a1} things that you can do to improve your writing.',
                'answers' => ['a1' => 'a few'],
                'options' => ['any', 'a little', 'a few'],
            ],
            [
                'question' => 'There is {a1} milk in the fridge. We need to buy some.',
                'answers' => ['a1' => 'no'],
                'options' => ['none', 'no', 'any'],
            ],
            [
                'question' => 'He doesn’t have {a1} hobbies.',
                'answers' => ['a1' => 'any'],
                'options' => ['none', 'no', 'any'],
            ],
            [
                'question' => '“How many computers do you have?” “{a1}.”',
                'answers' => ['a1' => 'None'],
                'options' => ['None', 'Any', 'No'],
            ],
            [
                'question' => 'I can help you; I have {a1} time today.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a lot of', 'much', 'many'],
            ],
        ];

        $exercise2 = [
            [
                'question' => '“How much water do you drink?” “{a1}.”',
                'answers' => ['a1' => 'A lot'],
                'options' => ['Much', 'A lot of', 'A lot'],
            ],
            [
                'question' => '{a1} goals did they score?',
                'answers' => ['a1' => 'How many'],
                'options' => ['How many', 'How much', 'How little'],
            ],
            [
                'question' => 'Nowadays we don’t use {a1} cash, because we use our credit cards.',
                'answers' => ['a1' => 'much'],
                'options' => ['many', 'much', 'a lot'],
            ],
            [
                'question' => 'There’s {a1} pressure on the players.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['a lot of', 'much', 'many'],
            ],
            [
                'question' => 'They got married {a1} months after they met for the first time.',
                'answers' => ['a1' => 'a few'],
                'options' => ['much', 'a little', 'a few'],
            ],
            [
                'question' => '“How much milk do you want in your coffee?” “Only {a1}.”',
                'answers' => ['a1' => 'a little'],
                'options' => ['a little', 'a few', 'much'],
            ],
            [
                'question' => 'There were {a1} problems during the festival.',
                'answers' => ['a1' => 'no'],
                'options' => ['any', 'no', 'none'],
            ],
            [
                'question' => 'I don’t want {a1} gifts.',
                'answers' => ['a1' => 'any'],
                'options' => ['none', 'no', 'any'],
            ],
            [
                'question' => '“How many gifts do you want?” “{a1}.”',
                'answers' => ['a1' => 'None'],
                'options' => ['None', 'Any', 'No'],
            ],
            [
                'question' => 'I eat {a1} vegetables.',
                'answers' => ['a1' => 'a lot of'],
                'options' => ['much', 'a lot of', 'many'],
            ],
        ];

        $bank = ['much', 'How much', 'many', 'a lot of', 'a lot', 'a little', 'a few', 'any', 'no', 'none'];

        $exercise3 = [
            [
                'question' => 'He has {a1} paintings. It’s a really big collection.',
                'answers' => ['a1' => 'a lot of'],
            ],
            [
                'question' => '{a1} salt do you put in your food?',
                'answers' => ['a1' => 'How much'],
            ],
            [
                'question' => 'I normally use {a1} makeup, but not much, only some lipstick.',
                'answers' => ['a1' => 'a little'],
            ],
            [
                'question' => 'It rained {a1} last week; it rained every day.',
                'answers' => ['a1' => 'a lot'],
            ],
            [
                'question' => 'They only scored one goal. They didn’t have {a1} opportunities to score; maybe two or three.',
                'answers' => ['a1' => 'many'],
            ],
            [
                'question' => 'Please, I just need 5 minutes of your time. I only have {a1} questions.',
                'answers' => ['a1' => 'a few'],
            ],
            [
                'question' => 'We had {a1} problems. It was 100% perfect.',
                'answers' => ['a1' => 'no'],
            ],
            [
                'question' => 'We need to be fast; we haven’t got {a1} time, only 20 minutes.',
                'answers' => ['a1' => 'much'],
            ],
            [
                'question' => 'I didn’t see {a1} people in the room. It was completely empty.',
                'answers' => ['a1' => 'any'],
            ],
            [
                'question' => '“How many cups of coffee did you have?” “{a1}. I don’t drink coffee.”',
                'answers' => ['a1' => 'none'],
            ],
        ];

        $this->appendQuestions($items, $meta, $categoryId, $exercise1, $sourceIds['ex1'], 'ex1');
        $this->appendQuestions($items, $meta, $categoryId, $exercise2, $sourceIds['ex2'], 'ex2');
        $this->appendQuestions($items, $meta, $categoryId, $exercise3, $sourceIds['ex3'], 'ex3', $bank);

        foreach ($items as &$item) {
            $item['tag_ids'] = [$themeTagId];
        }
        unset($item);

        $this->seedQuestionData($items, $meta);
    }

    private function appendQuestions(
        array &$items,
        array &$meta,
        int $categoryId,
        array $entries,
        int $sourceId,
        string $exerciseKey,
        ?array $options = null
    ): void {
        foreach ($entries as $index => $entry) {
            $uuid = $this->generateQuestionUuid('a1', 'quantifiers', 'much-many-lot-little-few', $exerciseKey, $index + 1);

            $optionList = $options ?? $entry['options'];

            $items[] = [
                'uuid' => $uuid,
                'question' => $entry['question'],
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'level' => 'A1',
                'difficulty' => 1,
                'type' => null,
                'flag' => 0,
                'answers' => $this->mapAnswers($entry['answers']),
                'options' => $optionList,
                'variants' => [],
            ];

            $meta[] = [
                'uuid' => $uuid,
                'answers' => $entry['answers'],
                'hints' => ['Quantifiers (A1)'],
            ];
        }
    }

    private function mapAnswers(array $answers): array
    {
        $mapped = [];
        foreach ($answers as $marker => $answer) {
            $mapped[] = [
                'marker' => $marker,
                'answer' => $answer,
            ];
        }

        return $mapped;
    }
}
