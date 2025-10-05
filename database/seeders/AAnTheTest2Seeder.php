<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class AAnTheTest2Seeder extends Seeder
{
    public function run(): void
    {
        $categoryId = 2; // Present

        $articlesTag = Tag::firstOrCreate(['name' => 'A An The'], ['category' => 'Articles']);

        $tags = [
            'Animals'    => Tag::firstOrCreate(['name' => 'Animals'], ['category' => 'Vocabulary']),
            'People'     => Tag::firstOrCreate(['name' => 'People'], ['category' => 'Vocabulary']),
            'Transport'  => Tag::firstOrCreate(['name' => 'Transport'], ['category' => 'Vocabulary']),
            'Nature'     => Tag::firstOrCreate(['name' => 'Nature'], ['category' => 'Vocabulary']),
            'Objects'    => Tag::firstOrCreate(['name' => 'Objects'], ['category' => 'Vocabulary']),
            'Family'     => Tag::firstOrCreate(['name' => 'Family'], ['category' => 'Vocabulary']),
            'School'     => Tag::firstOrCreate(['name' => 'School'], ['category' => 'Vocabulary']),
            'Stationery' => Tag::firstOrCreate(['name' => 'Stationery'], ['category' => 'Vocabulary']),
            'Fruit'      => Tag::firstOrCreate(['name' => 'Fruit'], ['category' => 'Vocabulary']),
            'Clothes'    => Tag::firstOrCreate(['name' => 'Clothes'], ['category' => 'Vocabulary']),
            'Geography'  => Tag::firstOrCreate(['name' => 'Geography'], ['category' => 'Vocabulary']),
            'House'      => Tag::firstOrCreate(['name' => 'House'], ['category' => 'Vocabulary']),
        ];

        $sources = [
            1 => Source::firstOrCreate(['name' => 'A An The Test 2 - 1'])->id,
            2 => Source::firstOrCreate(['name' => 'A An The Test 2 - 2'])->id,
            3 => Source::firstOrCreate(['name' => 'A An The Test 2 - 3'])->id,
            4 => Source::firstOrCreate(['name' => 'A An The Test 2 - 4'])->id,
            5 => Source::firstOrCreate(['name' => 'A An The Test 2 - 5'])->id,
        ];

        $section1 = [
            [
                'question' => 'He has got {a1} puppy.',
                'answers'  => [['marker' => 'a1', 'answer' => 'a']],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'Kate is {a1} good girl.',
                'answers'  => [['marker' => 'a1', 'answer' => 'a']],
                'tags'     => [$tags['People']->id],
            ],
            [
                'question' => 'John has got {a1} old boat.',
                'answers'  => [['marker' => 'a1', 'answer' => 'an']],
                'tags'     => [$tags['Transport']->id],
            ],
            [
                'question' => 'That dog is {a1} very lazy.',
                'answers'  => [['marker' => 'a1', 'answer' => 'a']],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'There is {a1} star in the sky.',
                'answers'  => [['marker' => 'a1', 'answer' => 'a']],
                'tags'     => [$tags['Nature']->id],
            ],
            [
                'question' => 'Mary has got {a1} uncle.',
                'answers'  => [['marker' => 'a1', 'answer' => 'an']],
                'tags'     => [$tags['Family']->id],
            ],
            [
                'question' => "We've got {a1} yellow bikes.",
                'answers'  => [['marker' => 'a1', 'answer' => '-']],
                'tags'     => [$tags['Transport']->id],
            ],
            [
                'question' => 'The basket is {a1} small.',
                'answers'  => [['marker' => 'a1', 'answer' => '-']],
                'tags'     => [$tags['Objects']->id],
            ],
        ];

        $section2 = [
            [
                'question' => 'Kate is {a1} nice girl. She is {a2} pupil. She is {a3} very clever. She has got {a4} schoolbag. It is {a5} blue. She has got {a6} books in her bag. She has got {a7} pencil and {a8} pen in the bag, too. The pencil is {a9} black and the pen is {a10} green.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'a'],
                    ['marker' => 'a3', 'answer' => '-'],
                    ['marker' => 'a4', 'answer' => 'a'],
                    ['marker' => 'a5', 'answer' => '-'],
                    ['marker' => 'a6', 'answer' => '-'],
                    ['marker' => 'a7', 'answer' => 'a'],
                    ['marker' => 'a8', 'answer' => 'a'],
                    ['marker' => 'a9', 'answer' => '-'],
                    ['marker' => 'a10', 'answer' => '-'],
                ],
                'tags'     => [$tags['School']->id, $tags['Stationery']->id],
            ],
        ];

        $section3 = [
            [
                'question' => 'There is {a1} bird in the tree. {a2} bird is yellow.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'Where are {a1} shoes? They are under {a2} bed.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Clothes']->id],
            ],
            [
                'question' => 'I can see {a1} fish in {a2} water.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'There is {a1} old bus at {a2} bus-stop.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Transport']->id],
            ],
            [
                'question' => '{a1} Moon is in {a2} sky.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Nature']->id],
            ],
            [
                'question' => 'There is {a1} red pen in {a2} box.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Stationery']->id],
            ],
            [
                'question' => 'What colour is {a1} orange?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                ],
                'tags'     => [$tags['Fruit']->id],
            ],
            [
                'question' => 'Look at {a1} dog. It has got {a2} long tail.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => 'a'],
                ],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'There is {a1} elephant in {a2} picture.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Animals']->id],
            ],
            [
                'question' => 'I can see {a1} ant on {a2} wall.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Animals']->id],
            ],
        ];

        $section4 = [
            [
                'question' => '{a1} London is {a2} big city. I\'ve got {a3} friend there.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => '-'],
                    ['marker' => 'a2', 'answer' => 'a'],
                    ['marker' => 'a3', 'answer' => 'a'],
                ],
                'tags'     => [$tags['Geography']->id],
            ],
            [
                'question' => '{a1} Jane is {a2} English girl but she lives in {a3} Portugal.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => '-'],
                    ['marker' => 'a2', 'answer' => 'an'],
                    ['marker' => 'a3', 'answer' => '-'],
                ],
                'tags'     => [$tags['People']->id, $tags['Geography']->id],
            ],
            [
                'question' => 'Listen to {a1} woman. She is singing {a2} nice song.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => 'a'],
                ],
                'tags'     => [$tags['People']->id],
            ],
            [
                'question' => '{a1} Sun is in {a2} sky.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Nature']->id],
            ],
            [
                'question' => '{a1} colour of this flower is {a2} purple.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'the'],
                    ['marker' => 'a2', 'answer' => '-'],
                ],
                'tags'     => [$tags['Nature']->id],
            ],
            [
                'question' => '{a1} Mr Hill has got {a2} new car.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => '-'],
                    ['marker' => 'a2', 'answer' => 'a'],
                ],
                'tags'     => [$tags['Transport']->id],
            ],
        ];

        $section5 = [
            [
                'question' => 'Grandma is {a1} old lady. She\'s got {a2} house near {a3} sea. {a4} house has got {a5} beautiful garden. There are {a6} flowers and {a7} tree in {a8} garden. {a9} tree is very tall. It\'s {a10} apple tree. Grandma has got {a11} cat, too. {a12} cat is {a13} white but it has got {a14} black tail. It is {a15} old cat but {a16} grandma loves it very much.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                    ['marker' => 'a2', 'answer' => 'a'],
                    ['marker' => 'a3', 'answer' => 'the'],
                    ['marker' => 'a4', 'answer' => 'the'],
                    ['marker' => 'a5', 'answer' => 'a'],
                    ['marker' => 'a6', 'answer' => '-'],
                    ['marker' => 'a7', 'answer' => 'a'],
                    ['marker' => 'a8', 'answer' => 'the'],
                    ['marker' => 'a9', 'answer' => 'the'],
                    ['marker' => 'a10', 'answer' => 'an'],
                    ['marker' => 'a11', 'answer' => 'a'],
                    ['marker' => 'a12', 'answer' => 'the'],
                    ['marker' => 'a13', 'answer' => '-'],
                    ['marker' => 'a14', 'answer' => 'a'],
                    ['marker' => 'a15', 'answer' => 'an'],
                    ['marker' => 'a16', 'answer' => 'the'],
                ],
                'tags'     => [$tags['Family']->id, $tags['House']->id, $tags['Animals']->id, $tags['Nature']->id],
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        $sections = [
            [$section1, $sources[1]],
            [$section2, $sources[2]],
            [$section3, $sources[3]],
            [$section4, $sources[4]],
            [$section5, $sources[5]],
        ];

        foreach ($sections as [$questions, $sourceId]) {
            foreach ($questions as $q) {
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
                    'tag_ids'     => array_merge([$articlesTag->id], $q['tags']),
                    'level'       => 'A1',
                    'answers'     => $q['answers'],
                    'options'     => ['a', 'an', 'the', '-'],
                ];
            }
        }

        $service->seed($items);
    }
}
