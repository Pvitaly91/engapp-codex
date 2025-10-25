<?php

namespace Database\Seeders\V1\ArticlesAndQuantifiers;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class AAnSomeAnyTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = 2; // Present
        $sourceId   = Source::firstOrCreate(['name' => 'A An Some Any Test'])->id;

        $articlesTag   = Tag::firstOrCreate(['name' => 'A or An'], ['category' => 'Articles']);
        $quantifierTag = Tag::firstOrCreate(['name' => 'Some or Any'], ['category' => 'Quantifiers']);
        $vocabTag      = Tag::firstOrCreate(['name' => 'Food'], ['category' => 'Vocabulary']);

        $questions = [
            [
                'question' => 'Give me {a1} banana, please.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => 'Are there {a1} grapes?',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => "I'd like to eat {a1} chips.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'some'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'I need {a1} cherries for the cake.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'some'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'Is there {a1} butter in the fridge?',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'I want {a1} hot dog, please.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => "We don't have {a1} milk left.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => "I don't want {a1} tea, thank you.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'Does she drink {a1} milk?',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'There is {a1} egg sandwich on your plate. Eat it, please.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'an'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => "I don't like {a1} vegetables.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'I need {a1} drink.',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => "I'm hungry. I'll take {a1} salad.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => 'Have we got {a1} potatoes?',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => "I'll make {a1} tuna sandwich for you.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'a'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => "Let's have {a1} orange juice.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'some'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => 'Do you want {a1} orange?',
                'answers'  => [ ['marker' => 'a1', 'answer' => 'an'] ],
                'tags'     => [$articlesTag->id],
            ],
            [
                'question' => "We've got {a1} strawberries so we can make {a2} dessert.",
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'some'],
                    ['marker' => 'a2', 'answer' => 'a'],
                ],
                'tags'     => [$quantifierTag->id, $articlesTag->id],
            ],
            [
                'question' => "There isn't {a1} sugar.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'any'] ],
                'tags'     => [$quantifierTag->id],
            ],
            [
                'question' => "I'll have {a1} chicken soup.",
                'answers'  => [ ['marker' => 'a1', 'answer' => 'some'] ],
                'tags'     => [$quantifierTag->id],
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
                'tag_ids'     => array_merge([$vocabTag->id], $q['tags']),
                'level'       => 'A1',
                'answers'     => $q['answers'],
                'options'     => ['a', 'an', 'some', 'any'],
            ];
        }

        $service->seed($items);
    }
}
