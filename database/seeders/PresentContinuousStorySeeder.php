<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PresentContinuousStorySeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Present Continuous Story'])->id;
        $tenseTag = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses']);

        $question = "It's Saturday. {a1} lunch for my family.\n".
            "My husband {a2} baseball.\n".
            "My daughter {a3} in her room.\n".
            "My son {a4} to music.\n".
            "Now, the dog {a5}.\n".
            "A delivery driver {a6} a package to the door.\n".
            "I {a7} the window.\n".
            "My neighbor {a8} outside.\n".
            "A family {a9} on the sidewalk.\n".
            "What’s that sound? Oh, the birds {a10} outside.";

        $answers = [
            ['marker' => 'a1', 'answer' => "I'm cooking",   'verb_hint' => 'cook'],
            ['marker' => 'a2', 'answer' => 'is playing',   'verb_hint' => 'play'],
            ['marker' => 'a3', 'answer' => 'is reading',   'verb_hint' => 'read'],
            ['marker' => 'a4', 'answer' => 'is listening', 'verb_hint' => 'listen'],
            ['marker' => 'a5', 'answer' => 'is barking',   'verb_hint' => 'bark'],
            ['marker' => 'a6', 'answer' => 'is bringing',  'verb_hint' => 'bring'],
            ['marker' => 'a7', 'answer' => 'am watching',  'verb_hint' => 'watch'],
            ['marker' => 'a8', 'answer' => 'is gardening', 'verb_hint' => 'garden'],
            ['marker' => 'a9', 'answer' => 'is walking',   'verb_hint' => 'walk'],
            ['marker' => 'a10','answer' => 'are singing',  'verb_hint' => 'sing'],
        ];

        $uuid = Str::slug(class_basename(self::class));

        $service = new QuestionSeedingService();
        $service->seed([
            [
                'uuid'        => $uuid,
                'question'    => $question,
                'category_id' => $categoryId,
                'difficulty'  => 1,
                'source_id'   => $sourceId,
                'flag'        => 0,
                'tag_ids'     => [$tenseTag->id],
                'answers'     => $answers,
                'options'     => [],
            ]
        ]);
    }
}
