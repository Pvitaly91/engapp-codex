<?php

namespace Database\Seeders;

use App\Models\{Category, Source, Tag, Test};
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FutConImageTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple and Continuous (Fut Con Image)'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'fut_con_image_test']);

        $questions = [
            [
                'question' => 'It {a1} when I {a2} home this morning.',
                'answers' => [
                    'a1' => ['answer' => 'was snowing', 'verb_hint' => 'snow'],
                    'a2' => ['answer' => 'left', 'verb_hint' => 'leave'],
                ],
                'options' => [],
            ],
            [
                'question' => 'It was a sunny afternoon and people {a1} on the grass in the park. Then suddenly it {a2} to rain.',
                'answers' => [
                    'a1' => ['answer' => 'were sitting', 'verb_hint' => 'sit'],
                    'a2' => ['answer' => 'started', 'verb_hint' => 'start'],
                ],
                'options' => [],
            ],
            [
                'question' => 'A: I tried to explain my problem to her. B: {a1}?',
                'answers' => [
                    'a1' => ['answer' => 'Was she listening', 'verb_hint' => 'listen'],
                ],
                'options' => [],
            ],
            [
                'question' => 'My brother {a1} on the phone when I arrived, but when he {a2} me, he {a3} the call. Perhaps he thought that I {a4} to his conversation.',
                'answers' => [
                    'a1' => ['answer' => 'was talking', 'verb_hint' => 'talk'],
                    'a2' => ['answer' => 'saw', 'verb_hint' => 'see'],
                    'a3' => ['answer' => 'finished', 'verb_hint' => 'finish'],
                    'a4' => ['answer' => 'was listening', 'verb_hint' => 'listen'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I nearly had an accident today. A car {a1} towards me, but I moved quickly out of the way and fortunately nothing {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'was coming', 'verb_hint' => 'come'],
                    'a2' => ['answer' => 'happened', 'verb_hint' => 'happen'],
                ],
                'options' => [],
            ],
            [
                'question' => 'A: Which hotel {a1} in when you lost your passport? B: I don\'t remember. I {a2} many places during my European tour and I {a3} in many different hotels.',
                'answers' => [
                    'a1' => ['answer' => 'were you staying', 'verb_hint' => 'stay'],
                    'a2' => ['answer' => 'visited', 'verb_hint' => 'visit'],
                    'a3' => ['answer' => 'stayed', 'verb_hint' => 'stay'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} to Hastings in 1999. I {a2} there when I met them.',
                'answers' => [
                    'a1' => ['answer' => 'moved', 'verb_hint' => 'move'],
                    'a2' => ['answer' => 'was living', 'verb_hint' => 'live'],
                ],
                'options' => [],
            ],
            [
                'question' => '{a1} for the 9:15 bus last night?',
                'answers' => [
                    'a1' => ['answer' => 'Were many people waiting', 'verb_hint' => 'many people/wait'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} with my girlfriend when the disc jockey played our favourite song.',
                'answers' => [
                    'a1' => ['answer' => 'was dancing', 'verb_hint' => 'dance'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} grammar when I {a2} asleep.',
                'answers' => [
                    'a1' => ['answer' => 'was studying', 'verb_hint' => 'study'],
                    'a2' => ['answer' => 'fell', 'verb_hint' => 'fall'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The scientists {a1} in their laboratory when they {a2} the new drug.',
                'answers' => [
                    'a1' => ['answer' => 'were working', 'verb_hint' => 'work'],
                    'a2' => ['answer' => 'discovered', 'verb_hint' => 'discover'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} the wall when the gardener {a2} us.',
                'answers' => [
                    'a1' => ['answer' => 'were climbing', 'verb_hint' => 'climb'],
                    'a2' => ['answer' => 'saw', 'verb_hint' => 'see'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Vicky {a1} a beautiful dream when the alarm clock {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'was having', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'rang', 'verb_hint' => 'ring'],
                ],
                'options' => [],
            ],
            [
                'question' => 'As he {a1} for the bus he {a2} with a street lamp.',
                'answers' => [
                    'a1' => ['answer' => 'was running', 'verb_hint' => 'run'],
                    'a2' => ['answer' => 'collided', 'verb_hint' => 'collide'],
                ],
                'options' => [],
            ],
            [
                'question' => 'When he {a1} a suitcase, he {a2} it on his foot.',
                'answers' => [
                    'a1' => ['answer' => 'was carrying', 'verb_hint' => 'carry'],
                    'a2' => ['answer' => 'dropped', 'verb_hint' => 'drop'],
                ],
                'options' => [],
            ],
            [
                'question' => '{a1} your homework on the bus while you {a2} to school?',
                'answers' => [
                    'a1' => ['answer' => 'Were you doing', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'were coming', 'verb_hint' => 'come'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The students {a1} the article when the last class {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'were reading', 'verb_hint' => 'read'],
                    'a2' => ['answer' => 'finished', 'verb_hint' => 'finish'],
                ],
                'options' => [],
            ],
            [
                'question' => 'When I {a1} at the cinema, my friends {a2} for me.',
                'answers' => [
                    'a1' => ['answer' => 'arrived', 'verb_hint' => 'arrive'],
                    'a2' => ['answer' => 'were waiting', 'verb_hint' => 'wait'],
                ],
                'options' => [],
            ],
            [
                'question' => '{a1} to the teacher when she {a2} this exercise?',
                'answers' => [
                    'a1' => ['answer' => 'Were you listening', 'verb_hint' => 'listen'],
                    'a2' => ['answer' => 'was explaining', 'verb_hint' => 'explain'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Susan {a1} the piano while Mary {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'was playing', 'verb_hint' => 'play'],
                    'a2' => ['answer' => 'was singing', 'verb_hint' => 'sing'],
                ],
                'options' => [],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max) . '-' . $index;

            $answers = [];
            foreach ($q['answers'] as $marker => $answerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'] ?? null,
                ];
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $q['question'],
                'difficulty' => 2,
                'category_id' => $categoryId,
                'source_id' => $sourceId,
                'flag' => 0,
                'tag_ids' => [$themeTag->id],
                'answers' => $answers,
                'options' => $q['options'],
            ];
        }

        $service->seed($items);

        Test::updateOrCreate(
            ['slug' => 'fut-con-image-test'],
            [
                'name' => 'Past Simple and Continuous (Fut Con Image)',
                'filters' => [],
                'questions' => array_column($items, 'uuid'),
            ]
        );
    }
}
