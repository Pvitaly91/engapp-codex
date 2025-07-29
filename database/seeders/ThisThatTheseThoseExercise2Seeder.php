<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Str;

class ThisThatTheseThoseExercise2Seeder extends Seeder
{

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Choose this, that, these, those to complete the following sentences.';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;

        $data = [
            [
                'question' => 'What\'s {a1}? "It\'s a calculator."',
                'answers' => [['marker' => 'a1', 'answer' => 'this']],
                'options' => ['this', 'these'],
            ],
            [
                'question' => '{a1} are John and Teresa.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'These'],
            ],
            [
                'question' => 'Hi, Richard, {a1} is Julia. (On the phone)',
                'answers' => [['marker' => 'a1', 'answer' => 'this']],
                'options' => ['this', 'that'],
            ],
            [
                'question' => 'Look at {a1} cloud. It\'s very strange.',
                'answers' => [['marker' => 'a1', 'answer' => 'that']],
                'options' => ['this', 'that'],
            ],
            [
                'question' => 'What are {a1}? "They are little cakes."',
                'answers' => [['marker' => 'a1', 'answer' => 'those']],
                'options' => ['that', 'those'],
            ],
            [
                'question' => '{a1} are my keys.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['That', 'Those'],
            ],
            [
                'question' => 'Is {a1} your mother?',
                'answers' => [['marker' => 'a1', 'answer' => 'this']],
                'options' => ['this', 'these'],
            ],
            [
                'question' => 'What is in {a1} box over there?',
                'answers' => [['marker' => 'a1', 'answer' => 'that']],
                'options' => ['this', 'that'],
            ],
            [
                'question' => '{a1} is my hand!',
                'answers' => [['marker' => 'a1', 'answer' => 'This']],
                'options' => ['This', 'That'],
            ],
            [
                'question' => '{a1} are my cats.',
                'answers' => [['marker' => 'a1', 'answer' => 'those']],
                'options' => ['this', 'those'],
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
                'category_id' => $cat_present,
                'difficulty'  => 1,
                'source_id'   => $sourceId,
                'flag'        => 0,
                'answers'     => $d['answers'],
                'options'     => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
