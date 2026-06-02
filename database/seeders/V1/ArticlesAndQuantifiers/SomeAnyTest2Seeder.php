<?php

namespace Database\Seeders\V1\ArticlesAndQuantifiers;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class SomeAnyTest2Seeder extends Seeder
{
    public function run(): void
    {
        $categoryId = 2; // Present

        $grammarTag    = Tag::firstOrCreate(['name' => 'Some or Any'], ['category' => 'Quantifiers']);
        $vocabularyTag = Tag::firstOrCreate(['name' => 'Food'], ['category' => 'Vocabulary']);

        $sectionASource = Source::firstOrCreate(['name' => 'Some Any Test 2 - A'])->id;
        $sectionBSource = Source::firstOrCreate(['name' => 'Some Any Test 2 - B'])->id;

        $sectionA = [
            ['question' => 'There is {a1} milk.', 'answer' => 'some'],
            ['question' => 'There is {a1} butter.', 'answer' => 'some'],
            ['question' => 'There is {a1} cheese.', 'answer' => 'some'],
            ['question' => 'There are {a1} eggs.', 'answer' => 'some'],
            ['question' => 'There are {a1} biscuits.', 'answer' => 'some'],
            ['question' => 'There are {a1} tomatoes.', 'answer' => 'some'],
            [
                'question' => "There aren't {a1} carrots.",
                'answer'   => 'any',
            ],
            [
                'question' => "There aren't {a1} onions.",
                'answer'   => 'any',
            ],
            [
                'question' => "There isn't {a1} water.",
                'answer'   => 'any',
            ],
            [
                'question' => "There aren't {a1} eggs.",
                'answer'   => 'any',
            ],
            [
                'question' => "There aren't {a1} mushrooms.",
                'answer'   => 'any',
            ],
            [
                'question' => "There aren't {a1} potatoes.",
                'answer'   => 'any',
            ],
        ];

        $sectionB = [
            ['question' => 'Is there {a1} water?', 'answer' => 'any'],
            ['question' => 'Is there {a1} juice?', 'answer' => 'any'],
            ['question' => 'Is there {a1} rice?', 'answer' => 'any'],
            ['question' => 'Is there {a1} jam?', 'answer' => 'any'],
            ['question' => 'Are there {a1} grapes?', 'answer' => 'any'],
            ['question' => 'Are there {a1} cherries?', 'answer' => 'any'],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        $all = [
            [$sectionA, $sectionASource],
            [$sectionB, $sectionBSource],
        ];

        foreach ($all as [$questions, $sourceId]) {
            foreach ($questions as $i => $q) {
                $index = count($items) + 1;
                $slug  = Str::slug(class_basename(self::class));
                $max   = 36 - strlen((string) $index) - 1;
                $uuid  = substr($slug, 0, $max) . '-' . $index;

                $items[] = [
                    'uuid'        => $uuid,
                    'question'    => $q['question'],
                    'difficulty'  => 1,
                    'category_id' => $categoryId,
                    'flag'        => 0,
                    'source_id'   => $sourceId,
                    'tag_ids'     => [$grammarTag->id, $vocabularyTag->id],
                    'level'       => 'A1',
                    'answers'     => [
                        ['marker' => 'a1', 'answer' => $q['answer']],
                    ],
                    'options'     => ['some', 'any'],
                ];
            }
        }

        $service->seed($items);
    }
}
