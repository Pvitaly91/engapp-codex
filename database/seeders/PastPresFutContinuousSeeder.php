<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Category, Source, Tag};
use App\Services\QuestionSeedingService;

class PastPresFutContinuousSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;

        $sources = [
            'A' => Source::firstOrCreate(['name' => 'Past/Present/Future Continuous Test A'])->id,
            'B' => Source::firstOrCreate(['name' => 'Past/Present/Future Continuous Test B'])->id,
            'C' => Source::firstOrCreate(['name' => 'Past/Present/Future Continuous Test C'])->id,
        ];

        $tags = [
            'A' => Tag::firstOrCreate(['name' => 'past_present_future_cont_test_a']),
            'B' => Tag::firstOrCreate(['name' => 'past_present_future_cont_test_b']),
            'C' => Tag::firstOrCreate(['name' => 'past_present_future_cont_test_c']),
        ];

        $tests = [
            'A' => [
                [
                    'question' => 'Ann: Hi, Jane. What {a1}? Jane: Hallo, Ann.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'are you doing', 'verb_hint' => 'what/do'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => "Jane: Hallo, Ann. I {a1} for my sister to pick me up. Ann: Why doesn't your dad pick you up like usual? Jane: Oh, he had an accident two days ago. Ann: Really? I'm sorry to hear that. How is your dad's condition now?",
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'am waiting', 'verb_hint' => 'wait'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => "Jane: When the doctor {a1} and {a2} him, I {a3} and my mom was very restless. Praise God, it's better now. Ann: I pray he gets well soon. Jane: Thanks, Ann.",
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'was examining', 'verb_hint' => 'examine'],
                        ['marker' => 'a2', 'answer' => 'was treating', 'verb_hint' => 'treat'],
                        ['marker' => 'a3', 'answer' => 'was crying', 'verb_hint' => 'cry'],
                    ],
                    'options' => [],
                ],
            ],
            'B' => [
                [
                    'question' => 'Daniel and his cousin {a1} some equipment that they will bring for camping right now. They {a2} the mountain to get to their campsite early, so they can see the sunrise tomorrow. Uncle John {a3} them. They {a4} their uncle\'s house yesterday, while his uncle {a5} a Camping for Nature Lovers TV program. It inspired them to also go camping.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'are preparing', 'verb_hint' => 'prepare'],
                        ['marker' => 'a2', 'answer' => 'will be climbing', 'verb_hint' => 'climb'],
                        ['marker' => 'a3', 'answer' => 'will be accompanying', 'verb_hint' => 'accompany'],
                        ['marker' => 'a4', 'answer' => 'visited', 'verb_hint' => 'visit'],
                        ['marker' => 'a5', 'answer' => 'was watching', 'verb_hint' => 'watch'],
                    ],
                    'options' => [],
                ],
            ],
            'C' => [
                [
                    'question' => 'Yoshi - sit - in a cafe - when - Palm - call.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'Yoshi was sitting in a cafe when Palm called.'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'When - you - arrive - at the party last night - who - be - there?',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'When you arrived at the party last night, who was there?'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'Cath - watch - a film - at the moment. (negative)',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => "Cath isn't watching a film at the moment."],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'you - submit - your - assignment - next week? (question)',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'Will you be submitting your assignment next week?'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'When - my mom - arrive - I - play - a video game - this afternoon. (negative)',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => "When my mom arrives this afternoon, I won't be playing a video game."],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'He - live - in Bangkok - for 3 years.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'He has been living in Bangkok for 3 years.'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'While - I - do - my homework - Louis - cook - for dinner.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'While I was doing my homework, Louis was cooking for dinner.'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'What - you - do - when - I - call - you - last night?',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'What were you doing when I called you last night?'],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'At 8 p.m. tomorrow - my brother - sleep - because - our grandparents - will - arrive. (negative)',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => "At 8 p.m. tomorrow, my brother won't be sleeping because our grandparents will arrive."],
                    ],
                    'options' => [],
                ],
                [
                    'question' => 'The children - exercise - at the hall - while - the accident - happen.',
                    'answers' => [
                        ['marker' => 'a1', 'answer' => 'The children were exercising at the hall while the accident happened.'],
                    ],
                    'options' => [],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($tests as $key => $questions) {
            $sourceId = $sources[$key];
            $tagId = $tags[$key]->id;
            foreach ($questions as $i => $q) {
                $index = $i + 1;
                $slug = Str::slug(class_basename(self::class) . '-' . strtolower($key));
                $max = 36 - strlen((string) $index) - 1;
                $uuid = substr($slug, 0, $max) . '-' . $index;

                $items[] = [
                    'uuid' => $uuid,
                    'question' => $q['question'],
                    'difficulty' => 2,
                    'category_id' => $categoryId,
                    'source_id' => $sourceId,
                    'flag' => 0,
                    'tag_ids' => [$tagId],
                    'answers' => $q['answers'],
                    'options' => $q['options'],
                ];
            }
        }

        $service->seed($items);
    }
}
