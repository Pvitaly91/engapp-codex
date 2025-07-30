<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HaveGotExercise3Seeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'Complete the questions and short answers with have/has got.',
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'have_has_got_exercise_3']);
        $modalTag = Tag::firstOrCreate(['name' => 'Have Has Got'], ['category' => 'modal']);

        $data = [
            [
                'question' => '{a1} you got a pen?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have']],
                'options' => ['Have', 'Has'],
            ],
            [
                'question' => 'Yes, I {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => 'have']],
                'options' => ['have', 'has'],
            ],
            [
                'question' => '{a1} she got any brothers?',
                'answers' => [['marker' => 'a1', 'answer' => 'Has']],
                'options' => ['Have', 'Has'],
            ],
            [
                'question' => 'No, she {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => "hasn't"]],
                'options' => ["hasn't", "haven't"],
            ],
            [
                'question' => '{a1} they got a car?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have']],
                'options' => ['Have', 'Has'],
            ],
            [
                'question' => 'Yes, they {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => 'have']],
                'options' => ['have', 'has'],
            ],
            [
                'question' => '{a1} he got any sisters?',
                'answers' => [['marker' => 'a1', 'answer' => 'Has']],
                'options' => ['Have', 'Has'],
            ],
            [
                'question' => 'No, he {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => "hasn't"]],
                'options' => ["hasn't", "haven't"],
            ],
            [
                'question' => '{a1} you got my book?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have']],
                'options' => ['Have', 'Has'],
            ],
            [
                'question' => 'Yes, I {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => 'have']],
                'options' => ['have', 'has'],
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max).'-'.$index;

            $tagIds = [$themeTag->id, $modalTag->id];

            $items[] = [
                'uuid' => $uuid,
                'question' => $d['question'],
                'category_id' => $categoryId,
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => $tagIds,
                'answers' => $d['answers'],
                'options' => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
