<?php 

namespace Database\Seeders\V1\Demonstratives;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class ThisThatTheseThoseExercise3Seeder extends Seeder
{

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Transform singular sentences into plural sentences and plural sentences into singular sentences using this, that, these, those and other missing words.';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'this_that_these_those_exercise_3']);

        $data = [
            [
                'question' => 'This is a flower. ⇒ {a1} are flowers.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'That is a box. ⇒ {a1} are boxes.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'That is my father. ⇒ {a1} are my parents.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'Are those your friends? ⇒ {a1} your friend?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is that']],
                'options' => ['Is this', 'Is that', 'Are these', 'Are those'],
            ],
            [
                'question' => 'What are those things? ⇒ What {a1} thing?',
                'answers' => [['marker' => 'a1', 'answer' => 'is that']],
                'options' => ['is this', 'is that', 'are these', 'are those'],
            ],
            [
                'question' => 'These documents are important. ⇒ {a1} important.',
                'answers' => [['marker' => 'a1', 'answer' => 'This document is']],
                'options' => ['This document is', 'That document is', 'These documents are', 'Those documents are'],
            ],
            [
                'question' => 'Are these your shoes? ⇒ {a1} your shoe?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is this']],
                'options' => ['Is this', 'Is that', 'Are these', 'Are those'],
            ],
            [
                'question' => "That's a good car. ⇒ {a1} good cars.",
                'answers' => [['marker' => 'a1', 'answer' => 'Those are']],
                'options' => ['Those are', 'These are', 'This is', 'That is'],
            ],
            [
                'question' => 'This is John. ⇒ {a1} John and Carla.',
                'answers' => [['marker' => 'a1', 'answer' => 'These are']],
                'options' => ['These are', 'Those are', 'This is', 'That is'],
            ],
            [
                'question' => 'These are cheap computers. ⇒ {a1} a cheap computer.',
                'answers' => [['marker' => 'a1', 'answer' => 'This is']],
                'options' => ['This is', 'That is', 'These are', 'Those are'],
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
