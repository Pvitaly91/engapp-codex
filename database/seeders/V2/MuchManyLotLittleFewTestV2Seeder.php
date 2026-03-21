<?php

namespace Database\Seeders\V2;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;

class MuchManyLotLittleFewTestV2Seeder extends QuestionSeeder
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
            'exercise1' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few Test V2 (Exercise 1)'])->id,
            'exercise2' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few Test V2 (Exercise 2)'])->id,
            'exercise3' => Source::firstOrCreate(['name' => 'Custom: Much Many Lot Little Few Test V2 (Exercise 3)'])->id,
        ];

        $themeTagId = Tag::firstOrCreate(
            ['name' => 'Quantifiers Practice'],
            ['category' => 'English Grammar Theme']
        )->id;

        $detailTagId = Tag::firstOrCreate(
            ['name' => 'Much / Many / A Lot / Few / Little'],
            ['category' => 'English Grammar Detail']
        )->id;

        $structureTagId = Tag::firstOrCreate(
            ['name' => 'Quantifier Choice'],
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
                'source_id' => $sourceIds[$entry['source']] ?? reset($sourceIds),
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

        $this->seedQuestionData($items, $meta);
    }

    private function questionEntries(): array
    {
        $hint = ['Quantifiers (A1)'];
        $bank = ['much', 'How much', 'many', 'a lot of', 'a lot', 'a little', 'a few', 'any', 'no', 'none'];

        return [
            [
                'question' => 'If I want to pass the exam, I need to study {a1}.',
                'options' => ['a1' => ['a lot', 'a lot of', 'many']],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'There aren’t {a1} things to do in this village.',
                'options' => ['a1' => ['many', 'a lot', 'much']],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => '{a1} sugar do you take in your tea?',
                'options' => ['a1' => ['How many', 'How little', 'How much']],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'There was {a1} tension at the meeting.',
                'options' => ['a1' => ['much', 'a lot of', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'Dad, I need {a1} money for school.',
                'options' => ['a1' => ['a little', 'many', 'a few']],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'There are {a1} things that you can do to improve your writing.',
                'options' => ['a1' => ['any', 'a little', 'a few']],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'There is {a1} milk in the fridge. We need to buy some.',
                'options' => ['a1' => ['none', 'no', 'any']],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'He doesn’t have {a1} hobbies.',
                'options' => ['a1' => ['none', 'no', 'any']],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => '“How many computers do you have?” “{a1}.”',
                'options' => ['a1' => ['None', 'Any', 'No']],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => 'I can help you; I have {a1} time today.',
                'options' => ['a1' => ['a lot of', 'much', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'exercise1',
                'hints' => $hint,
            ],
            [
                'question' => '“How much water do you drink?” “{a1}.”',
                'options' => ['a1' => ['Much', 'A lot of', 'A lot']],
                'answers' => ['a1' => 'A lot'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => '{a1} goals did they score?',
                'options' => ['a1' => ['How many', 'How much', 'How little']],
                'answers' => ['a1' => 'How many'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'Nowadays we don’t use {a1} cash, because we use our credit cards.',
                'options' => ['a1' => ['many', 'much', 'a lot']],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'There’s {a1} pressure on the players.',
                'options' => ['a1' => ['a lot of', 'much', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'They got married {a1} months after they met for the first time.',
                'options' => ['a1' => ['much', 'a little', 'a few']],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => '“How much milk do you want in your coffee?” “Only {a1}.”',
                'options' => ['a1' => ['a little', 'a few', 'much']],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'There were {a1} problems during the festival.',
                'options' => ['a1' => ['any', 'no', 'none']],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'I don’t want {a1} gifts.',
                'options' => ['a1' => ['none', 'no', 'any']],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => '“How many gifts do you want?” “{a1}.”',
                'options' => ['a1' => ['None', 'Any', 'No']],
                'answers' => ['a1' => 'None'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'I eat {a1} vegetables.',
                'options' => ['a1' => ['much', 'a lot of', 'many']],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'exercise2',
                'hints' => $hint,
            ],
            [
                'question' => 'He has {a1} paintings. It’s a really big collection.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'a lot of'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => '{a1} salt do you put in your food?',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'How much'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'I normally use {a1} makeup, but not much, only some lipstick.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'a little'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'It rained {a1} last week; it rained every day.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'a lot'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'They only scored one goal. They didn’t have {a1} opportunities to score; maybe two or three.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'many'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'Please, I just need 5 minutes of your time. I only have {a1} questions.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'a few'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'We had {a1} problems. It was 100% perfect.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'no'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'We need to be fast; we haven’t got {a1} time, only 20 minutes.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'much'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => 'I didn’t see {a1} people in the room. It was completely empty.',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'any'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
            [
                'question' => '“How many cups of coffee did you have?” “{a1}. I don’t drink coffee.”',
                'options' => ['a1' => $bank],
                'answers' => ['a1' => 'none'],
                'level' => 'A1',
                'source' => 'exercise3',
                'hints' => $hint,
            ],
        ];
    }
}
