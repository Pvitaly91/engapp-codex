<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class SomeAnyTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 2; // Present Simple
        $sourceId   = Source::firstOrCreate(['name' => 'Some Any Test 1'])->id;
        $grammarTag = Tag::firstOrCreate(['name' => 'Some or Any'], ['category' => 'Quantifiers']);

        $questions = [
            [
                'question' => 'Are there {a1} eggs in the bag?',
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'eggs', 'category' => 'Food'],
            ],
            [
                'question' => 'There is {a1} milk.',
                'options'  => ['some', 'any'],
                'answer'   => 'some',
                'level'    => 'A1',
                'tag'      => ['name' => 'milk', 'category' => 'Food'],
            ],
            [
                'question' => 'There are {a1} grapes in the fridge.',
                'options'  => ['some', 'any'],
                'answer'   => 'some',
                'level'    => 'A1',
                'tag'      => ['name' => 'grapes', 'category' => 'Food'],
            ],
            [
                'question' => "I don't have {a1} tomatoes.",
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'tomatoes', 'category' => 'Food'],
            ],
            [
                'question' => "There aren't {a1} oranges in the cupboard.",
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'oranges', 'category' => 'Food'],
            ],
            [
                'question' => 'There are {a1} carrots on the table.',
                'options'  => ['some', 'any'],
                'answer'   => 'some',
                'level'    => 'A1',
                'tag'      => ['name' => 'carrots', 'category' => 'Food'],
            ],
            [
                'question' => 'Have you got {a1} avocados?',
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'avocados', 'category' => 'Food'],
            ],
            [
                'question' => "We haven't got {a1} peppers.",
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'peppers', 'category' => 'Food'],
            ],
            [
                'question' => 'There is {a1} milk in the bottle.',
                'options'  => ['some', 'any'],
                'answer'   => 'some',
                'level'    => 'A1',
                'tag'      => ['name' => 'milk', 'category' => 'Food'],
            ],
            [
                'question' => "We don't have {a1} pineapples.",
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'pineapples', 'category' => 'Food'],
            ],
            [
                'question' => "I don't need {a1} flour.",
                'options'  => ['some', 'any'],
                'answer'   => 'any',
                'level'    => 'A1',
                'tag'      => ['name' => 'flour', 'category' => 'Food'],
            ],
            [
                'question' => 'I have {a1} cherries.',
                'options'  => ['some', 'any'],
                'answer'   => 'some',
                'level'    => 'A1',
                'tag'      => ['name' => 'cherries', 'category' => 'Food'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $themeTag = Tag::firstOrCreate(
                ['name' => $q['tag']['name']],
                ['category' => $q['tag']['category']]
            );

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => $q['level'] === 'A1' ? 1 : 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => [$grammarTag->id, $themeTag->id],
                'level'       => $q['level'],
                'answers'     => [
                    ['marker' => 'a1', 'answer' => $q['answer']],
                ],
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
