<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class SimplePresentPastSeeder extends Seeder
{

    public function run()
    {
        // Категорія (створи/знайди): 2 = Present, 1 = Past
        $categoryPresent = 2;
        $categoryPast = 1;
        $sourceId = Source::firstOrCreate(['name' => 'Simple Present x Simple Past'])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'simple_present_past']);

        $questions = [
            [
                'question' => 'I always {a1} work in morning. It\'s relaxing.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'walk', 'verb_hint' => 'walk'],
                ],
                'options' => ['walk', 'walks', 'walked', 'walking'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Mark {a1} computers for three years before he graduated.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'studied', 'verb_hint' => 'study'],
                ],
                'options' => ['study', 'studies', 'studied', 'studying'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'They {a1} late for the party.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'did not arrive', 'verb_hint' => 'arrive / neg'],
                ],
                'options' => ['arrived', 'did not arrive', 'arrives', 'arriving'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'My parents {a1} in that church.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'married', 'verb_hint' => 'marry'],
                ],
                'options' => ['marry', 'married', 'marries', 'marrying'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Sorry! My classes {a1} 15 minutes late.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'end', 'verb_hint' => 'end'],
                ],
                'options' => ['end', 'ended', 'ends', 'ending'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'July {a1} for us.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'does not wait', 'verb_hint' => 'wait / neg'],
                ],
                'options' => ['waits', 'wait', 'does not wait', 'waited'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'You {a1} dogs. You have many.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'like', 'verb_hint' => 'like'],
                ],
                'options' => ['like', 'likes', 'liked', 'liking'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'I {a1} that book yesterday for the test.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'needed', 'verb_hint' => 'need'],
                ],
                'options' => ['need', 'needed', 'needs', 'needing'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'She {a1} my new dress last night.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'used', 'verb_hint' => 'use'],
                ],
                'options' => ['use', 'used', 'uses', 'using'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'The teacher {a1} the students after class. Now, he can\'t.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'helped', 'verb_hint' => 'help'],
                ],
                'options' => ['help', 'helped', 'helps', 'helping'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'It {a1} heavily last night.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'rained', 'verb_hint' => 'rain'],
                ],
                'options' => ['rains', 'rain', 'rained', 'raining'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'The police {a1} to the thief earlier.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'talked', 'verb_hint' => 'talk'],
                ],
                'options' => ['talk', 'talked', 'talks', 'talking'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'He {a1} the suitcase for me. It was too heavy.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'carried', 'verb_hint' => 'carry'],
                ],
                'options' => ['carry', 'carried', 'carries', 'carrying'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'The stores {a1} earlier today.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'closed', 'verb_hint' => 'close'],
                ],
                'options' => ['close', 'closed', 'closes', 'closing'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'The kids {a1} their homework.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not finish', 'verb_hint' => 'finish / neg'],
                ],
                'options' => ['finish', 'finishes', 'finished', 'do not finish'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'I {a1} some milk on the floor.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'dropped', 'verb_hint' => 'drop'],
                ],
                'options' => ['drop', 'dropped', 'drops', 'dropping'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'My kids {a1} video games every day, only on Sundays.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not play', 'verb_hint' => 'play / neg'],
                ],
                'options' => ['play', 'plays', 'played', 'do not play'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Sarah {a1} the box for him. It was difficult.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'opened', 'verb_hint' => 'open'],
                ],
                'options' => ['open', 'opened', 'opens', 'opening'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Patrick {a1} money to buy a house.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'saved', 'verb_hint' => 'save'],
                ],
                'options' => ['save', 'saved', 'saves', 'saving'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'My mother {a1} dinner when she arrives from work.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'cooks', 'verb_hint' => 'cook'],
                ],
                'options' => ['cook', 'cooks', 'cooked', 'cooking'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Last night I {a1} a noise in the garage.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'heard', 'verb_hint' => 'hear'],
                ],
                'options' => ['hear', 'hears', 'heard', 'hearing'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'I {a1} at night. I get home tired.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not cook', 'verb_hint' => 'cook / neg'],
                ],
                'options' => ['cook', 'cooks', 'do not cook', 'cooked'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'Renata {a1} to the gym yesterday morning.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'went', 'verb_hint' => 'go'],
                ],
                'options' => ['go', 'goes', 'went', 'going'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'The city {a1} crowded. Our event is a success.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['is', 'are', 'was', 'were'],
                'source_id' => $sourceId,
            ],
            [
                'question' => 'We {a1} help the man. We were afraid.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'did not help', 'verb_hint' => 'help / neg'],
                ],
                'options' => ['help', 'helped', 'did not help', 'helps'],
                'source_id' => $sourceId,
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($questions as $i => $data) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

           $data['uuid']       = $uuid;
           $data['difficulty'] = 2;
           $data['flag']       = 0;
            $data['tag_ids']   = [$themeTag->id];

            $items[] = $data;
        }

        $service->seed($items);
    }
}
