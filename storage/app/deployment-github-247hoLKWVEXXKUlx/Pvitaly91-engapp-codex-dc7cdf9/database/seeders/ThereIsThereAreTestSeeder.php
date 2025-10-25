<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ThereIsThereAreTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 2; // Present Simple
        $sourceId = Source::firstOrCreate(['name' => 'There is there are test'])->id;
        $grammarTag = Tag::firstOrCreate(['name' => 'There is there are'], ['category' => 'Structures']);

        $questions = [
            [
                'question' => '{a1} a nice restaurant nearby? It has great pizza!',
                'options'  => ['There is', 'There are', 'There be'],
                'answer'   => 'There is',
                'level'    => 'A1',
                'tag'      => ['name' => 'restaurant', 'category' => 'Places'],
            ],
            [
                'question' => 'Do you know if {a1} at least tomorrow?',
                'options'  => ['there is', 'there are'],
                'answer'   => 'there is',
                'level'    => 'A1',
                'tag'      => ['name' => 'tomorrow', 'category' => 'Time'],
            ],
            [
                'question' => 'Are you going to the parade? I hear {a1} going to be horses!',
                'options'  => ["there's", 'there are'],
                'answer'   => 'there are',
                'level'    => 'A1',
                'tag'      => ['name' => 'parade', 'category' => 'Events'],
            ],
            [
                'question' => '{a1} any rain in the forecast tomorrow?',
                'options'  => ['Is there', 'Are there'],
                'answer'   => 'Is there',
                'level'    => 'A1',
                'tag'      => ['name' => 'weather', 'category' => 'Weather'],
            ],
            [
                'question' => '{a1} any ice cream left. Let\'s get another pint.',
                'options'  => ['There is', "There isn't", 'There are'],
                'answer'   => "There isn't",
                'level'    => 'A1',
                'tag'      => ['name' => 'ice_cream', 'category' => 'Food'],
            ],
            [
                'question' => '{a1} any tickets left for the concert next week?',
                'options'  => ['Is there', 'Are there', 'Were there'],
                'answer'   => 'Are there',
                'level'    => 'A1',
                'tag'      => ['name' => 'concert', 'category' => 'Events'],
            ],
            [
                'question' => 'Please remind me {a1} a good chance I will forget on such a busy week.',
                'options'  => ['there is', 'there are'],
                'answer'   => 'there is',
                'level'    => 'A2',
                'tag'      => ['name' => 'reminder', 'category' => 'Time'],
            ],
            [
                'question' => 'How many cookies {a1} in a small batch?',
                'options'  => ['there is', 'there are'],
                'answer'   => 'there are',
                'level'    => 'A1',
                'tag'      => ['name' => 'cookies', 'category' => 'Food'],
            ],
            [
                'question' => 'No, {a1} any lemons in the cupboard. I don\'t see them anywhere!',
                'options'  => ["there aren't", "there isn't"],
                'answer'   => "there aren't",
                'level'    => 'A1',
                'tag'      => ['name' => 'lemons', 'category' => 'Food'],
            ],
            [
                'question' => 'Hey, {a1} any pizza left? let\'s share it so it doesn\'t go bad.',
                'options'  => ['is there', 'are there', "isn't there"],
                'answer'   => 'is there',
                'level'    => 'A1',
                'tag'      => ['name' => 'pizza', 'category' => 'Food'],
            ],
            [
                'question' => 'I don\'t think {a1} any students in the classroom.',
                'options'  => ["they're", 'there are', "there aren't"],
                'answer'   => 'there are',
                'level'    => 'A2',
                'tag'      => ['name' => 'classroom', 'category' => 'Education'],
            ],
            [
                'question' => 'There {a1} cans of soda in the cooler, but some water bottles.',
                'options'  => ["isn't any", "aren't any", "aren't some"],
                'answer'   => "aren't any",
                'level'    => 'A2',
                'tag'      => ['name' => 'soda', 'category' => 'Food'],
            ],
            [
                'question' => '{a1} going to be food at the party? Or should I eat before I go?',
                'options'  => ['Is there', 'Are there', 'Were there'],
                'answer'   => 'Is there',
                'level'    => 'A1',
                'tag'      => ['name' => 'party', 'category' => 'Events'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max  = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max) . '-' . $index;

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
