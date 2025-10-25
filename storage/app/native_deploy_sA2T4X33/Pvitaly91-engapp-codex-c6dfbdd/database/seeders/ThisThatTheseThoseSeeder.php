<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ThisThatTheseThoseSeeder extends Seeder
{

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Complete the sentences with this, that, these, those';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'this_that_these_those']);

        $data = [
            [
                'question' => '{a1} are my trousers.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Hi, Chris. {a1} is my friend Jona. "Hi, Jona. Nice to meet you."',
                'answers' => [['marker' => 'a1', 'answer' => 'This']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Look at {a1} birds in the sky.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'What are {a1}? "They are my books."',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Is {a1} hotel nice?',
                'answers' => [['marker' => 'a1', 'answer' => 'That']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Are {a1} your friends?',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Who is {a1} man over there?',
                'answers' => [['marker' => 'a1', 'answer' => 'That']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Isn\'t {a1} your friend Erik?',
                'answers' => [['marker' => 'a1', 'answer' => 'This']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Why are {a1} boxes here?',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => '{a1} are my glasses.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
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
                'tag_ids'     => [$themeTag->id],
                'answers'     => $d['answers'],
                'options'     => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
