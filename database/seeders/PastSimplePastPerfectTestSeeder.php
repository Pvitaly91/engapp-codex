<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimplePastPerfectTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;

        $sources = [
            1 => Source::firstOrCreate(['name' => 'Read the order… choose past simple or past perfect'])->id,
            2 => Source::firstOrCreate(['name' => 'Choose the correct past simple or past perfect forms'])->id,
            3 => Source::firstOrCreate(['name' => 'Complete using past simple or past perfect'])->id,
        ];

        $generalTag = Tag::firstOrCreate(['name' => 'Past Simple or Past Perfect'], ['category' => 'Grammar']);

        $detailedTags = [
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']),
            'Past Perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses']),
        ];

        $sections = [
            1 => [
                'questions' => [
                    [
                        'q' => "I showed him a letter that I ____.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d written", 'verb_hint' => null],
                        ],
                        'options' => ["'d written", 'wrote'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "I wrote a letter and a few days later I ____ it to him.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'showed', 'verb_hint' => null],
                        ],
                        'options' => ["'d shown", 'showed'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When I looked through the window, it ____ raining.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'started', 'verb_hint' => null],
                        ],
                        'options' => ['had started', 'started'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When I looked through the window, it ____.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had rained', 'verb_hint' => null],
                        ],
                        'options' => ['rained', 'had rained'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When you called, I ____ dinner.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d had", 'verb_hint' => null],
                        ],
                        'options' => ["'d had", 'had'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When you called, I ____ dinner.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had', 'verb_hint' => null],
                        ],
                        'options' => ["'d had", 'had'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "She bought a T-shirt in the sales and ____ it to me.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'gave', 'verb_hint' => null],
                        ],
                        'options' => ['had given', 'gave'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "She gave me a T-shirt that she ____ in the sales.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had bought', 'verb_hint' => null],
                        ],
                        'options' => ['bought', 'had bought'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When the teacher arrived, I ____ my composition.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'finished', 'verb_hint' => null],
                        ],
                        'options' => ['finished', "'d finished"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "When the teacher arrived, I ____ my composition.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d finished", 'verb_hint' => null],
                        ],
                        'options' => ['finished', "'d finished"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                ],
            ],
            2 => [
                'questions' => [
                    [
                        'q' => '… I ____ my dinner.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't finished", 'verb_hint' => null],
                        ],
                        'options' => ["hadn't finished", "didn't finish", "hadn't finish"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => "I couldn't open the door because I ____ the keys at work.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d forgotten", 'verb_hint' => null],
                        ],
                        'options' => ["'d forget", "'d forgotten", 'forgot'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'When Rita travelled to France, she ____ the Louvre.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'visited', 'verb_hint' => null],
                        ],
                        'options' => ["'d visited", "'d visit", 'visited'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'There were no cookies. Somebody ____ them all.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had eaten', 'verb_hint' => null],
                        ],
                        'options' => ['had eaten', "'d eat", 'ate'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I saw some cookies and I ____ them all.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'ate', 'verb_hint' => null],
                        ],
                        'options' => ["'d eaten", "'d eat", 'ate'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I locked the door and then I ____ home.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'left', 'verb_hint' => null],
                        ],
                        'options' => ["'d left", 'left', "'d leave"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I saw that I ____ the door.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't locked", 'verb_hint' => null],
                        ],
                        'options' => ["hadn't locked", "hadn't lock", "didn't lock"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'The house was dirty because nobody ____ it.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had cleaned', 'verb_hint' => null],
                        ],
                        'options' => ["'d clean", 'had cleaned', 'cleaned'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'She gave me back my book because she ____ reading it.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d finished", 'verb_hint' => null],
                        ],
                        'options' => ["'d finish", "'d finished", 'finished'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'When she finished the book, she ____ it back to me.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'gave', 'verb_hint' => null],
                        ],
                        'options' => ["'d given", "'d give", 'gave'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                ],
            ],
            3 => [
                'questions' => [
                    [
                        'q' => 'Jim (not call) yet.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't called", 'verb_hint' => 'not/call'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I (call) Jim.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'called', 'verb_hint' => 'call'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'the trousers that I (buy) in Camden market.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had bought', 'verb_hint' => 'buy'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I (decide) to wear a skirt.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'decided', 'verb_hint' => 'decide'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'they (get) furious.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'got', 'verb_hint' => 'get'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => '(I/make) a terrible mistake?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'Had I made', 'verb_hint' => 'I/make'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'John asked me if I (see) his dog.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had seen', 'verb_hint' => 'see'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'the dog (run) away during some fireworks.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had run', 'verb_hint' => 'run'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'Where (I/put) the keys?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had I put', 'verb_hint' => 'I/put'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'I (try) to talk to my boss.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'tried', 'verb_hint' => 'try'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
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
                    'source_id'   => $sources[$section],
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
