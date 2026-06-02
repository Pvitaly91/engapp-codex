<?php
namespace Database\Seeders\V1\Tenses\Present;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Database\Seeders\V1\Questions\QuestionSeeder;

class PresentContinuousStorySeeder extends QuestionSeeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Present Continuous Story'])->id;
        $tenseTag = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses']);

        $question = "It's Saturday. {a1} lunch. My husband {a2} baseball. "
            . "My daughter {a3} in her room. My son {a4} to music. "
            . "The dog {a5}. A driver {a6} a package. I {a7} the window. "
            . "Neighbor {a8} outside. A family {a9} on the sidewalk. "
            . "What's that sound? The birds {a10}.";

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

        $uuid = $this->generateQuestionUuid();

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
