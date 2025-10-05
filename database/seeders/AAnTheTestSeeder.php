<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class AAnTheTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = 2; // Present
        $sourceId   = Source::firstOrCreate(['name' => 'A An The Test 1'])->id;

        $articlesTag = Tag::firstOrCreate(['name' => 'A An The'], ['category' => 'Articles']);

        $vocab = [
            'Animals'     => Tag::firstOrCreate(['name' => 'Animals'], ['category' => 'Vocabulary']),
            'Stationery'  => Tag::firstOrCreate(['name' => 'Stationery'], ['category' => 'Vocabulary']),
            'Objects'     => Tag::firstOrCreate(['name' => 'Objects'], ['category' => 'Vocabulary']),
            'Drinks'      => Tag::firstOrCreate(['name' => 'Drinks'], ['category' => 'Vocabulary']),
            'Fruit'       => Tag::firstOrCreate(['name' => 'Fruit'], ['category' => 'Vocabulary']),
            'Food'        => Tag::firstOrCreate(['name' => 'Food'], ['category' => 'Vocabulary']),
            'Toys'        => Tag::firstOrCreate(['name' => 'Toys'], ['category' => 'Vocabulary']),
            'Clothes'     => Tag::firstOrCreate(['name' => 'Clothes'], ['category' => 'Vocabulary']),
            'People'      => Tag::firstOrCreate(['name' => 'People'], ['category' => 'Vocabulary']),
            'Professions' => Tag::firstOrCreate(['name' => 'Professions'], ['category' => 'Vocabulary']),
            'Books'       => Tag::firstOrCreate(['name' => 'Books'], ['category' => 'Vocabulary']),
            'Nature'      => Tag::firstOrCreate(['name' => 'Nature'], ['category' => 'Vocabulary']),
            'Geography'   => Tag::firstOrCreate(['name' => 'Geography'], ['category' => 'Vocabulary']),
        ];

        $questions = [
            [
                'question' => 'This is {a1} cat. {a2} cat is big.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Animals']->id],
            ],
            [
                'question' => 'That is {a1} pen. {a2} pen is black.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Stationery']->id],
            ],
            [
                'question' => 'This is {a1} box. There is {a2} ring in {a3} box.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'a'],
                    ['marker' => 'a3', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Objects']->id],
            ],
            [
                'question' => 'This is {a1} cup. {a2} water is in {a3} cup.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                    ['marker' => 'a3', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Drinks']->id],
            ],
            [
                'question' => 'That is {a1} apple. {a2} apple is green.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'an'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Fruit']->id],
            ],
            [
                'question' => 'This is {a1} egg.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'an'] ],
                'tags'     => [$vocab['Food']->id],
            ],
            [
                'question' => 'Suwit has {a1} toy. {a2} toy is on his bed.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'the'],
                ],
                'tags'     => [$vocab['Toys']->id],
            ],
            [
                'question' => 'I have {a1} hat and {a2} umbrella.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'a'],
                    ['marker' => 'a2', 'answer' => 'an'],
                ],
                'tags'     => [$vocab['Clothes']->id],
            ],
            [
                'question' => 'Malee is {a1} girl.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$vocab['People']->id],
            ],
            [
                'question' => 'He is {a1} doctor.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$vocab['Professions']->id],
            ],
            [
                'question' => 'It is {a1} book.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$vocab['Books']->id],
            ],
            [
                'question' => '{a1} sun is big.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'the'] ],
                'tags'     => [$vocab['Nature']->id],
            ],
            [
                'question' => 'Thailand is in {a1} Asia.',
                'answers'  => [ ['marker' => 'a1', 'answer' => '-'] ],
                'tags'     => [$vocab['Geography']->id],
            ],
            [
                'question' => 'Bangkok is {a1} capital of Thailand.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'the'] ],
                'tags'     => [$vocab['Geography']->id],
            ],
            [
                'question' => '{a1} water is in his cup.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'the'] ],
                'tags'     => [$vocab['Drinks']->id],
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
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

        $service->seed($items);
    }
}

