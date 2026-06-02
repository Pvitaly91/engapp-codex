<?php

namespace Database\Seeders\V1\Tenses\Future;

use App\Support\Database\Seeder;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use Illuminate\Support\Str;

class FutureSimpleOrFutureContinuousSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Future Simple or Future Continuous'])->id;

        $detailTag = Tag::firstOrCreate(
            ['name' => 'future_simple_or_future_continuous'],
            ['category' => 'Details']
        );

        $tenseTags = [
            'Future Simple'     => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses']),
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses']),
        ];

        $questions = [
            [
                'question' => 'I want to tour the area today. {a1} the car?',
                'answers'  => [
                    'a1' => ['answer' => 'Will you be using', 'verb_hint' => 'you/use'],
                ],
                'options'  => ['Will you use', 'Will you be using', 'Do you use', 'Are you using'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "Please don't forget your tie because you {a1} the administers during your visit.",
                'answers'  => [
                    'a1' => ['answer' => 'will be meeting', 'verb_hint' => 'meet'],
                ],
                'options'  => ['will meet', 'will be meeting', 'meet', 'are meeting'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'The taxi driver {a1} you to the Savoy Hotel.',
                'answers'  => [
                    'a1' => ['answer' => 'will take', 'verb_hint' => 'take'],
                ],
                'options'  => ['will take', 'will be taking', 'takes', 'is taking'],
                'tenses'   => ['Future Simple'],
                'level'    => 'A2',
            ],
            [
                'question' => 'A shuttle {a1} for you outside the airport building at 8:30 p.m.',
                'answers'  => [
                    'a1' => ['answer' => 'will be waiting', 'verb_hint' => 'wait'],
                ],
                'options'  => ['will wait', 'will be waiting', 'waits', 'is waiting'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "There's a big sale at Selfridges tomorrow. I'm sure people {a1} up the street from early morning.",
                'answers'  => [
                    'a1' => ['answer' => 'will be queuing', 'verb_hint' => 'queue'],
                ],
                'options'  => ['will queue', 'will be queuing', 'queue', 'are queuing'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'Put on something nice for the party. The photographers {a1} pictures.',
                'answers'  => [
                    'a1' => ['answer' => 'will be taking', 'verb_hint' => 'take'],
                ],
                'options'  => ['will take', 'will be taking', 'take', 'are taking'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "You can't stay here if you've decided not to go. We {a1} for you.",
                'answers'  => [
                    'a1' => ['answer' => 'will be looking', 'verb_hint' => 'look'],
                ],
                'options'  => ['will look', 'will be looking', 'look', 'are looking'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "It's an acceptable suggestion. I {a1} it over.",
                'answers'  => [
                    'a1' => ['answer' => 'will think', 'verb_hint' => 'think'],
                ],
                'options'  => ['will think', 'will be thinking', 'think', 'am thinking'],
                'tenses'   => ['Future Simple'],
                'level'    => 'A2',
            ],
            [
                'question' => 'The price of petrol has gone up again. People {a1} their bicycles soon.',
                'answers'  => [
                    'a1' => ['answer' => 'will be riding', 'verb_hint' => 'ride'],
                ],
                'options'  => ['will ride', 'will be riding', 'ride', 'are riding'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "I'm sure this statue {a1} here in the year 2010.",
                'answers'  => [
                    'a1' => ['answer' => 'will be standing', 'verb_hint' => 'stand'],
                ],
                'options'  => ['will stand', 'will be standing', 'stands', 'is standing'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => '{a1} us at 3:00 p.m.? We {a2} the new plan.',
                'answers'  => [
                    'a1' => ['answer' => 'Will you be joining', 'verb_hint' => 'join'],
                    'a2' => ['answer' => 'will be discussing', 'verb_hint' => 'discuss'],
                ],
                'options'  => array_merge(
                    ['Will you join', 'Will you be joining', 'Do you join', 'Are you joining'],
                    ['will discuss', 'will be discussing', 'discuss', 'are discussing']
                ),
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'Come to the stadium at 4:00 p.m. The world-famous football player {a1} the T-shirts.',
                'answers'  => [
                    'a1' => ['answer' => 'will be signing', 'verb_hint' => 'sign'],
                ],
                'options'  => ['will sign', 'will be signing', 'signs', 'is signing'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'This time tomorrow I {a1} across the Pacific.',
                'answers'  => [
                    'a1' => ['answer' => 'will be flying', 'verb_hint' => 'fly'],
                ],
                'options'  => ['will fly', 'will be flying', 'fly', 'am flying'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "I don't want to call Janet just now. I'm sure she {a1} the baby and she {a2} the phone.",
                'answers'  => [
                    'a1' => ['answer' => 'will be bathing', 'verb_hint' => 'bathe'],
                    'a2' => ['answer' => "can't answer", 'verb_hint' => 'can/not answer'],
                ],
                'options'  => array_merge(
                    ['will bathe', 'will be bathing', 'bathes', 'is bathing'],
                    ["can't answer", "won't answer", "won't be answering", "doesn't answer"]
                ),
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'If you need me, you {a1} me at school. I {a2} in pavilion A until the lunch time.',
                'answers'  => [
                    'a1' => ['answer' => 'will find', 'verb_hint' => 'find'],
                    'a2' => ['answer' => 'will be teaching', 'verb_hint' => 'teach'],
                ],
                'options'  => array_merge(
                    ['will find', 'will be finding', 'find', 'are finding'],
                    ['will be teaching', 'will teach', 'teach', 'am teaching']
                ),
                'tenses'   => ['Future Simple', 'Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'The festival begins next Saturday. People {a1} and {a2} in the streets all week.',
                'answers'  => [
                    'a1' => ['answer' => 'will be dancing', 'verb_hint' => 'dance'],
                    'a2' => ['answer' => 'will be singing', 'verb_hint' => 'sing'],
                ],
                'options'  => array_merge(
                    ['will dance', 'will be dancing', 'dance', 'are dancing'],
                    ['will sing', 'will be singing', 'sing', 'are singing']
                ),
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "I'm sure you {a1} your driving test, but I {a2} my fingers crossed for you all the same.",
                'answers'  => [
                    'a1' => ['answer' => 'will pass', 'verb_hint' => 'pass'],
                    'a2' => ['answer' => 'will keep', 'verb_hint' => 'keep'],
                ],
                'options'  => array_merge(
                    ['will pass', 'will be passing', 'pass', 'are passing'],
                    ['will keep', 'will be keeping', 'keep', 'are keeping']
                ),
                'tenses'   => ['Future Simple'],
                'level'    => 'A2',
            ],
            [
                'question' => 'We {a1} English this month. Our teacher has left.',
                'answers'  => [
                    'a1' => ['answer' => 'will not be learning', 'verb_hint' => 'not learn'],
                ],
                'options'  => ["won't learn", 'will not be learning', "don't learn", "aren't learning"],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => 'Our neighbours are having a party tonight. They {a1} a noise all night as usual.',
                'answers'  => [
                    'a1' => ['answer' => 'will be making', 'verb_hint' => 'make'],
                ],
                'options'  => ['will make', 'will be making', 'make', 'are making'],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
            [
                'question' => "You can use John's computer. He {a1} here anymore.",
                'answers'  => [
                    'a1' => ['answer' => "won't be working", 'verb_hint' => 'not work'],
                ],
                'options'  => ["won't work", "won't be working", "doesn't work", "isn't working"],
                'tenses'   => ['Future Continuous'],
                'level'    => 'B1',
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $answers = [];
            foreach ($q['answers'] as $marker => $data) {
                $answers[] = [
                    'marker'    => $marker,
                    'answer'    => $data['answer'],
                    'verb_hint' => $data['verb_hint'],
                ];
            }

            $tagIds = [$detailTag->id];
            foreach ($q['tenses'] as $tense) {
                $tagIds[] = $tenseTags[$tense]->id;
            }

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => $tagIds,
                'level'       => $q['level'],
                'answers'     => $answers,
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
