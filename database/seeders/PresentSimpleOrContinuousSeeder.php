<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\{Category, Source, Tag};
use Illuminate\Support\Str;

class PresentSimpleOrContinuousSeeder extends Seeder
{
    public function run(): void
    {
        $catId = Category::firstOrCreate(['name' => 'present'])->id;
        $sourceId = Source::firstOrCreate([
            'name' => 'Present Simple or Present Continuous'
        ])->id;

        $simpleTag = Tag::firstOrCreate(['name' => 'Present Simple'], ['category' => 'Tenses']);
        $contTag   = Tag::firstOrCreate(['name' => 'Present Continuous'], ['category' => 'Tenses']);
        $themeTag  = Tag::firstOrCreate(['name' => 'present_simple_or_continuous']);

        $data = [
            [
                'question' => 'Listen! Somebody {a1} the violin.',
                'answers'  => [['marker' => 'a1', 'answer' => 'is playing', 'verb_hint' => 'play']],
                'options'  => ['is playing', 'plays', 'play'],
            ],
            [
                'question' => 'How often {a1} you {a2} to the cinema?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'do', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'go', 'verb_hint' => 'go'],
                ],
                'options'  => ['do', 'are', 'go', 'going'],
            ],
            [
                'question' => 'Why {a1} you {a2} at me now?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'shouting', 'verb_hint' => 'shout'],
                ],
                'options'  => ['are', 'do', 'shout', 'shouting'],
            ],
            [
                'question' => 'Shh! The baby {a1} asleep.',
                'answers'  => [['marker' => 'a1', 'answer' => 'is falling', 'verb_hint' => 'fall']],
                'options'  => ['is falling', 'falls', 'fall'],
            ],
            [
                'question' => '{a1} you {a2} who I am?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Do', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'know', 'verb_hint' => 'know'],
                ],
                'options'  => ['Do', 'Are', 'know', 'knowing'],
            ],
            [
                'question' => 'How much coffee {a1} you {a2}?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'do', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'drink', 'verb_hint' => 'drink'],
                ],
                'options'  => ['do', 'are', 'drink', 'drinking'],
            ],
            [
                'question' => 'Look! The boys {a1} football.',
                'answers'  => [['marker' => 'a1', 'answer' => 'are playing', 'verb_hint' => 'play']],
                'options'  => ['are playing', 'play', 'plays'],
            ],
            [
                'question' => 'Quick! They {a1}.',
                'answers'  => [['marker' => 'a1', 'answer' => "aren't looking", 'verb_hint' => 'not/look']],
                'options'  => ["aren't looking", "don't look", 'look'],
            ],
            [
                'question' => "My best friend {a1} meat. She's a vegetarian.",
                'answers'  => [['marker' => 'a1', 'answer' => "doesn't eat", 'verb_hint' => 'not/eat']],
                'options'  => ["doesn't eat", "isn't eating", 'eat'],
            ],
            [
                'question' => 'Who {a1} you {a2} to now?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'talking', 'verb_hint' => 'talk'],
                ],
                'options'  => ['are', 'do', 'talk', 'talking'],
            ],
            [
                'question' => 'Shh! Dad {a1} the news.',
                'answers'  => [['marker' => 'a1', 'answer' => 'is watching', 'verb_hint' => 'watch']],
                'options'  => ['is watching', 'watches', 'watch'],
            ],
            [
                'question' => '{a1} you {a2} chocolate?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'Do', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => 'like', 'verb_hint' => 'like'],
                ],
                'options'  => ['Do', 'Are', 'like', 'liking'],
            ],
            [
                'question' => 'We {a1} on a school trip today.',
                'answers'  => [['marker' => 'a1', 'answer' => 'are going', 'verb_hint' => 'go']],
                'options'  => ['are going', 'go', 'going'],
            ],
            [
                'question' => 'I {a1} a bike every day. Only at weekends.',
                'answers'  => [['marker' => 'a1', 'answer' => "don't ride", 'verb_hint' => 'not/ride']],
                'options'  => ["don't ride", 'ride', 'am riding'],
            ],
            [
                'question' => "I {a1} to school this week. I'm ill.",
                'answers'  => [['marker' => 'a1', 'answer' => 'am not going', 'verb_hint' => 'not/go']],
                'options'  => ['am not going', "don't go", 'go'],
            ],
            [
                'question' => 'We usually {a1} the weekends in the mountains in our summer house.',
                'answers'  => [['marker' => 'a1', 'answer' => 'spend', 'verb_hint' => 'spend']],
                'options'  => ['spend', 'are spending', 'spends'],
            ],
            [
                'question' => 'You {a1} to me again!',
                'answers'  => [['marker' => 'a1', 'answer' => "aren't listening", 'verb_hint' => 'not/listen']],
                'options'  => ["aren't listening", "don't listen", 'listen'],
            ],
            [
                'question' => 'Rebecca {a1} books, she {a2} films.',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => "doesn't read", 'verb_hint' => 'not/read'],
                    ['marker' => 'a2', 'answer' => 'prefers', 'verb_hint' => 'prefer'],
                ],
                'options'  => ["doesn't read", "isn't reading", 'prefer', 'prefers'],
            ],
            [
                'question' => 'Look! Who {a1} she {a2} to?',
                'answers'  => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'talking', 'verb_hint' => 'talk'],
                ],
                'options'  => ['is', 'does', 'talking', 'talks'],
            ],
            [
                'question' => 'They {a1} tennis at the moment.',
                'answers'  => [['marker' => 'a1', 'answer' => 'are playing', 'verb_hint' => 'play']],
                'options'  => ['are playing', 'play', 'plays'],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid' => $uuid,
                'question' => $d['question'],
                'difficulty' => 2,
                'category_id' => $catId,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => [$themeTag->id, $simpleTag->id, $contTag->id],
                'answers' => $d['answers'],
                'options' => $d['options'],
            ];
        }

        $service->seed($items);
    }
}
