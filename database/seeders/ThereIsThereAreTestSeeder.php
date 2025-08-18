<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\{Category, Source, Tag};
use Illuminate\Support\Str;

class ThereIsThereAreTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'There is/There are worksheet'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'there_is_there_are']);

        $questions = [
            [
                'question' => '{a1} a nice restaurant nearby. It has great pizza!',
                'answer'   => 'There is',
                'options'  => ['There is', 'There are', 'There'],
            ],
            [
                'question' => 'Do you know if {a1} a test tomorrow?',
                'answer'   => 'there is',
                'options'  => ['there is', 'there', 'there are'],
            ],
            [
                'question' => 'Are you going to the parade? I hear {a1} going to be horses!',
                'answer'   => 'there are',
                'options'  => ['there is', 'there are', 'there'],
            ],
            [
                'question' => '{a1} any rain in the forecast tomorrow?',
                'answer'   => 'Is there',
                'options'  => ['Is there', 'Are there', 'There are'],
            ],
            [
                'question' => '{a1} any ice cream left. Let\'s get some.',
                'answer'   => 'There isn\'t',
                'options'  => ['They aren\'t', 'There aren\'t', 'There isn\'t'],
            ],
            [
                'question' => '{a1} any tickets left for the concert tonight?',
                'answer'   => 'Are there',
                'options'  => ['Is there', 'Are there', 'Were there'],
            ],
            [
                'question' => 'Please remind me. {a1} a good chance I will forget to pick up milk.',
                'answer'   => 'There is',
                'options'  => ['There is', 'There are', 'Were there'],
            ],
            [
                'question' => 'How many cookies {a1} in the jar?',
                'answer'   => 'are there',
                'options'  => ['are there', 'is there', 'there were'],
            ],
            [
                'question' => 'No, {a1} any apples in the cupboard.',
                'answer'   => 'there aren\'t',
                'options'  => ['there aren\'t', 'there isn\'t', 'there is'],
            ],
            [
                'question' => 'How many floors {a1} in the new building downtown?',
                'answer'   => 'are there',
                'options'  => ['are there', 'there are', 'is there'],
            ],
            [
                'question' => '{a1} cans of soda in the cooler, but we have lots of water.',
                'answer'   => 'There are',
                'options'  => ['There are', 'There is', 'Is there'],
            ],
            [
                'question' => '{a1} going to be food at the party? Or should I eat before I go?',
                'answer'   => 'Is there',
                'options'  => ['Is there', 'Are there', 'Was there'],
            ],
            [
                'question' => '{a1} the food going to be good at the party? Or should I eat before I go?',
                'answer'   => 'Is',
                'options'  => ['Is', 'Are', 'Was'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'category_id' => $categoryId,
                'difficulty'  => 1,
                'source_id'   => $sourceId,
                'flag'        => 0,
                'tag_ids'     => [$themeTag->id],
                'answers'     => [
                    ['marker' => 'a1', 'answer' => $q['answer']],
                ],
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
