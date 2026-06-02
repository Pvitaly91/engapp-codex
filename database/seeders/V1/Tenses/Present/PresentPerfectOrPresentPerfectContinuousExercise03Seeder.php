<?php

namespace Database\Seeders\V1\Tenses\Present;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class PresentPerfectOrPresentPerfectContinuousExercise03Seeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'Present Perfect'])->id;

        $sources = [
            'I'   => Source::firstOrCreate(['name' => 'Present Perfect or Present Perfect Continuous — Exercise 03 (Result or side effect)'])->id,
            'II'  => Source::firstOrCreate(['name' => 'Present Perfect or Present Perfect Continuous — Exercise 03 (Since the last time or since the beginning)'])->id,
            'III' => Source::firstOrCreate(['name' => 'Present Perfect or Present Perfect Continuous — Exercise 03 (Dialogue)'])->id,
        ];

        $tenseTags = [
            'perfect'            => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']),
            'perfect_continuous' => Tag::firstOrCreate(['name' => 'Present Perfect Continuous'], ['category' => 'Tenses']),
        ];

        $detailTags = [
            'I'   => Tag::firstOrCreate(['name' => 'present_perfect_result_or_side_effect'], ['category' => 'Details']),
            'II'  => Tag::firstOrCreate(['name' => 'present_perfect_since_last_time'], ['category' => 'Details']),
            'III' => Tag::firstOrCreate(['name' => 'present_perfect_dialogue'], ['category' => 'Details']),
        ];

        $sections = [
            'I' => [
                'title'     => 'Result or side effect',
                'questions' => [
                    [
                        'question' => 'Why are you out of breath? - I {a1}.',
                        'answers'  => [
                            'a1' => ['answer' => 'have been running', 'verb_hint' => 'run'],
                        ],
                        'options'  => ['have run', 'have been running', 'ran', 'was running'],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'The toaster is okay again. Dad {a1} it.',
                        'answers'  => [
                            'a1' => ['answer' => 'has repaired', 'verb_hint' => 'repair'],
                        ],
                        'options'  => ['has repaired', 'has been repairing', 'repaired', 'was repairing'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'I am so tired, I {a1} all day.',
                        'answers'  => [
                            'a1' => ['answer' => 'have been working', 'verb_hint' => 'work'],
                        ],
                        'options'  => ['have worked', 'have been working', 'worked', 'was working'],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'Your shirt is clean now. Maggie {a1} it.',
                        'answers'  => [
                            'a1' => ['answer' => 'has washed', 'verb_hint' => 'wash'],
                        ],
                        'options'  => ['has washed', 'has been washing', 'washed', 'was washing'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => "I'm afraid, I'm getting a cold. I {a1} home in the rain.",
                        'answers'  => [
                            'a1' => ['answer' => 'have been walking', 'verb_hint' => 'walk'],
                        ],
                        'options'  => ['have walked', 'have been walking', 'walked', 'was walking'],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'Your clothes smell awful! {a1}?',
                        'answers'  => [
                            'a1' => ['answer' => 'Have you been smoking', 'verb_hint' => 'you/smoke'],
                        ],
                        'options'  => ['Have you smoked', 'Have you been smoking', 'Did you smoke', 'Were you smoking'],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'Peggy is ready for her exam now. I {a1} her preparing for it.',
                        'answers'  => [
                            'a1' => ['answer' => 'have helped', 'verb_hint' => 'help'],
                        ],
                        'options'  => ['have helped', 'have been helping', 'helped', 'was helping'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'It is dark in here because we {a1} the curtains. We want to watch a film and that’s better in the dark.',
                        'answers'  => [
                            'a1' => ['answer' => 'have closed', 'verb_hint' => 'close'],
                        ],
                        'options'  => ['have closed', 'have been closing', 'closed', 'were closing'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'His voice is gone now because he {a1} all morning.',
                        'answers'  => [
                            'a1' => ['answer' => 'has been shouting', 'verb_hint' => 'shout'],
                        ],
                        'options'  => ['has shouted', 'has been shouting', 'shouted', 'was shouting'],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                ],
            ],
            'II' => [
                'title'     => 'Since the last time or since the beginning',
                'questions' => [
                    [
                        'question' => 'I {a1} the computer for half an hour, only for about 5 minutes.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't been playing", 'verb_hint' => 'play/not'],
                        ],
                        'options'  => ["haven't played", "haven't been playing", "didn't play", "wasn't playing"],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'Bob {a1} a car for eight years.',
                        'answers'  => [
                            'a1' => ['answer' => "hasn't driven", 'verb_hint' => 'drive/not'],
                        ],
                        'options'  => ["hasn't driven", "hasn't been driving", "didn't drive", "wasn't driving"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'Carla {a1} on holiday for three years.',
                        'answers'  => [
                            'a1' => ['answer' => "hasn't gone", 'verb_hint' => 'go/not'],
                        ],
                        'options'  => ["hasn't gone", "hasn't been going", "didn't go", "wasn't going"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'We {a1} for 40 minutes yet – there are still 10 minutes left.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't been running", 'verb_hint' => 'run/not'],
                        ],
                        'options'  => ["haven't run", "haven't been running", "didn't run", "weren't running"],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'They {a1} for 10 days now.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't smoked", 'verb_hint' => 'smoke/not'],
                        ],
                        'options'  => ["haven't smoked", "haven't been smoking", "didn't smoke", "weren't smoking"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'I {a1} anything since two o’clock.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't eaten", 'verb_hint' => 'eat/not'],
                        ],
                        'options'  => ["haven't eaten", "haven't been eating", "didn't eat", "wasn't eating"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'Anna {a1} here for five years, but for seven years.',
                        'answers'  => [
                            'a1' => ['answer' => "hasn't worked", 'verb_hint' => 'work/not'],
                        ],
                        'options'  => ["hasn't worked", "hasn't been working", "didn't work", "wasn't working"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'I {a1} for a long time – just 10 minutes, not more.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't been reading", 'verb_hint' => 'read/not'],
                        ],
                        'options'  => ["haven't read", "haven't been reading", "didn't read", "wasn't reading"],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'You {a1} for two hours. It was only about one hour.',
                        'answers'  => [
                            'a1' => ['answer' => "haven't been cycling", 'verb_hint' => 'cycle/not'],
                        ],
                        'options'  => ["haven't cycled", "haven't been cycling", "didn't cycle", "weren't cycling"],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous'],
                    ],
                    [
                        'question' => 'Catherine {a1} French for 10 years, so her French isn’t very good now.',
                        'answers'  => [
                            'a1' => ['answer' => "hasn't spoken", 'verb_hint' => 'speak/not'],
                        ],
                        'options'  => ["hasn't spoken", "hasn't been speaking", "didn't speak", "wasn't speaking"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                ],
            ],
            'III' => [
                'title'     => 'Dialogue',
                'questions' => [
                    [
                        'question' => 'A: {a1} the dog for a walk yet?',
                        'answers'  => [
                            'a1' => ['answer' => 'Have you taken', 'verb_hint' => 'you/take'],
                        ],
                        'options'  => ['Have you taken', 'Have you been taking', 'Did you take', 'Were you taking'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => 'B: I {a1} all day. I {a2} home from work and I {a3} the time yet to walk the dog.',
                        'answers'  => [
                            'a1' => ['answer' => 'have been working', 'verb_hint' => 'work'],
                            'a2' => ['answer' => 'have just come', 'verb_hint' => 'come/just'],
                            'a3' => ['answer' => "haven't had", 'verb_hint' => 'have/not'],
                        ],
                        'options'  => [
                            'a1' => ['have worked', 'have been working', 'worked', 'was working'],
                            'a2' => ['have just come', 'just came', 'just come', 'was just coming'],
                            'a3' => ["haven't had", "haven't been having", "didn't have", "wasn't having"],
                        ],
                        'level'    => 'B1',
                        'tenses'   => ['perfect_continuous', 'perfect'],
                    ],
                    [
                        'question' => 'A: How long the dog {a1} home alone?',
                        'answers'  => [
                            'a1' => ['answer' => 'has been', 'verb_hint' => 'be'],
                        ],
                        'options'  => ['has been', 'has been being', 'was', 'is'],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                    [
                        'question' => "B: For about 6 hours. You {a1} the dog for a long time. Don't you want to go?",
                        'answers'  => [
                            'a1' => ['answer' => "haven't walked", 'verb_hint' => 'walk/not'],
                        ],
                        'options'  => ["haven't walked", "haven't been walking", "didn't walk", "weren't walking"],
                        'level'    => 'A2',
                        'tenses'   => ['perfect'],
                    ],
                ],
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];
        $slug    = Str::slug(class_basename(self::class));
        $index   = 1;

        foreach ($sections as $key => $section) {
            foreach ($section['questions'] as $q) {
                $uuid = substr($slug, 0, 36 - strlen((string) $index) - 1) . '-' . $index;

                $answers = [];
                foreach ($q['answers'] as $marker => $answerData) {
                    $answers[] = [
                        'marker'    => $marker,
                        'answer'    => $answerData['answer'],
                        'verb_hint' => $answerData['verb_hint'],
                    ];
                }

                $options = $q['options'];
                if ($options && array_keys($options) !== range(0, count($options) - 1)) {
                    $flattened = [];
                    foreach ($options as $optionSet) {
                        foreach ($optionSet as $option) {
                            if (! in_array($option, $flattened, true)) {
                                $flattened[] = $option;
                            }
                        }
                    }
                    $options = $flattened;
                }

                $tagIds = [$detailTags[$key]->id];
                foreach ($q['tenses'] as $tenseKey) {
                    $tagIds[] = $tenseTags[$tenseKey]->id;
                }

                $items[] = [
                    'uuid'        => $uuid,
                    'question'    => $q['question'],
                    'difficulty'  => $q['level'] === 'A1' ? 1 : 2,
                    'category_id' => $categoryId,
                    'flag'        => 0,
                    'source_id'   => $sources[$key],
                    'tag_ids'     => $tagIds,
                    'level'       => $q['level'],
                    'answers'     => $answers,
                    'options'     => $options,
                ];

                $index++;
            }
        }

        $service->seed($items);
    }
}
