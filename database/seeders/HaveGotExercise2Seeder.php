<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HaveGotExercise2Seeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => "Write affirmative (+) and negative (-) sentences using have/has got."
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'have_has_got_exercise_2']);

        $data = [
            [
                'question' => 'She / a brother. ⇒ She {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'has got a brother']],
                'options' => ['has got a brother', "hasn't got a brother"],
            ],
            [
                'question' => 'I / a new car. ⇒ I {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "haven't got a new car"]],
                'options' => ['have got a new car', "haven't got a new car"],
            ],
            [
                'question' => 'They / a big house. ⇒ They {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'have got a big house']],
                'options' => ['have got a big house', "haven't got a big house"],
            ],
            [
                'question' => 'He / any pets. ⇒ He {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "hasn't got any pets"]],
                'options' => ['has got any pets', "hasn't got any pets"],
            ],
            [
                'question' => 'We / a lot of time. ⇒ We {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'have got a lot of time']],
                'options' => ['have got a lot of time', "haven't got a lot of time"],
            ],
            [
                'question' => 'The car / four doors. ⇒ The car {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'has got four doors']],
                'options' => ['has got four doors', "hasn't got four doors"],
            ],
            [
                'question' => 'You / a cold. ⇒ You {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "haven't got a cold"]],
                'options' => ['have got a cold', "haven't got a cold"],
            ],
            [
                'question' => 'My friend / a bike. ⇒ My friend {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'has got a bike']],
                'options' => ['has got a bike', "hasn't got a bike"],
            ],
            [
                'question' => 'They / any money. ⇒ They {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "haven't got any money"]],
                'options' => ['have got any money', "haven't got any money"],
            ],
            [
                'question' => 'He / a new job. ⇒ He {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'has got a new job']],
                'options' => ['has got a new job', "hasn't got a new job"],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $d['question'],
                'category_id' => $categoryId,
                'difficulty'  => 2,
                'source_id'   => $sourceId,
                'flag'        => 0,
                'tag_ids'     => [$themeTag->id],
                'answers'     => $d['answers'],
                'options'     => $d['options'],
            ];
        }

        $service->seed($items);
    }
}