<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimplePresentPerfectSimpleTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;

        $sourceId = Source::firstOrCreate([
            'name' => 'Use the past simple or the present perfect simple'
        ])->id;

        $generalTag = Tag::firstOrCreate(
            ['name' => 'Past Simple or Present Perfect'],
            ['category' => 'Grammar']
        );

        $detailedTags = [
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']),
            'Present Perfect' => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']),
        ];

        $sections = [
            1 => [
                'questions' => [
                    [
                        'q' => 'They {a1} three chocolates this week. They {a2} them yesterday.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'have eaten', 'verb_hint' => 'eat'],
                            ['marker' => 'a2', 'answer' => 'ate', 'verb_hint' => 'eat'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'I {a1} a lot when I {a2} younger, but since last year I {a3} only two books.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'read', 'verb_hint' => 'read'],
                            ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                            ['marker' => 'a3', 'answer' => 'have read', 'verb_hint' => 'read'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'She {a1} him for ages, ten years ago {a2} the last time she {a3} him.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hasn't seen", 'verb_hint' => 'not see'],
                            ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                            ['marker' => 'a3', 'answer' => 'saw', 'verb_hint' => 'see'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'Oh, finally! Where {a1}? I {a2} everywhere. You {a3} yourself really very well.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'have you been', 'verb_hint' => 'you/be'],
                            ['marker' => 'a2', 'answer' => 'have looked', 'verb_hint' => 'look'],
                            ['marker' => 'a3', 'answer' => 'have hidden', 'verb_hint' => 'hide'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'Maya and Jed {a1} in love three years ago. They {a2} married last year. They {a3} very happy ever since.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'fell', 'verb_hint' => 'fall'],
                            ['marker' => 'a2', 'answer' => 'got', 'verb_hint' => 'get'],
                            ['marker' => 'a3', 'answer' => 'have been', 'verb_hint' => 'be'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'Yesterday Mr. Smith {a1} a letter from a friend.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'received', 'verb_hint' => 'receive'],
                        ],
                        'tenses' => ['Past Simple'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'He just {a1} an answer.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has just written', 'verb_hint' => 'write'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'They {a1} from each other since last year.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "haven't heard", 'verb_hint' => 'not hear'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'She {a1} her new neighbours yet.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hasn't seen", 'verb_hint' => 'not see'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'Her husband already {a1} to them.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has already spoken', 'verb_hint' => 'speak'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'Tom and George {a1} golf as opponents since their childhood. Last year Tom {a2} the championship. He {a3} for four hours a day ever since.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'have played', 'verb_hint' => 'play'],
                            ['marker' => 'a2', 'answer' => 'won', 'verb_hint' => 'win'],
                            ['marker' => 'a3', 'answer' => 'has practised', 'verb_hint' => 'practise'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'People {a1} less sociable since internet {a2}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'have become', 'verb_hint' => 'become'],
                            ['marker' => 'a2', 'answer' => 'appeared', 'verb_hint' => 'appear'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => "Mrs. Brown's husband {a1} last year, so she {a2} very lonely ever since.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'died', 'verb_hint' => 'die'],
                            ['marker' => 'a2', 'answer' => 'has felt', 'verb_hint' => 'feel'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => 'She {a1} in the same house since 1999.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has lived', 'verb_hint' => 'live'],
                        ],
                        'tenses' => ['Present Perfect'],
                        'level'  => 'B1',
                    ],
                    [
                        'q' => "The Johnson's {a1} in our neighbourhood three years ago. Since then they already {a2} a lot of new friends.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'moved', 'verb_hint' => 'move'],
                            ['marker' => 'a2', 'answer' => 'have already made', 'verb_hint' => 'make'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                        'level'  => 'B1',
                    ],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        $i = 0;
        foreach ($sections as $section => $data) {
            foreach ($data['questions'] as $q) {
                $i++;
                $index = $i;
                $slug  = Str::slug(class_basename(self::class));
                $max   = 36 - strlen((string) $index) - 1;
                $uuid  = substr($slug, 0, $max) . '-' . $index;

                $tagIds = [$generalTag->id];
                foreach ($q['tenses'] as $tense) {
                    $tagIds[] = $detailedTags[$tense]->id;
                }

                $items[] = [
                    'uuid'        => $uuid,
                    'question'    => $q['q'],
                    'difficulty'  => $q['level'] === 'A1' ? 1 : 2,
                    'category_id' => $categoryId,
                    'flag'        => 0,
                    'source_id'   => $sourceId,
                    'tag_ids'     => $tagIds,
                    'level'       => $q['level'],
                    'answers'     => $q['answers'],
                    'options'     => $q['options'] ?? [],
                ];
            }
        }

        $service->seed($items);
    }
}
