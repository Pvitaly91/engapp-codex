<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastSimplePresentPerfectPastPerfectTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;

        $sources = [
            1 => Source::firstOrCreate(['name' => 'Past Simple or Present Perfect'])->id,
            2 => Source::firstOrCreate(['name' => 'Past Simple or Past Perfect'])->id,
        ];

        $generalTags = [
            1 => Tag::firstOrCreate(['name' => 'Past Simple or Present Perfect'], ['category' => 'Grammar']),
            2 => Tag::firstOrCreate(['name' => 'Past Simple or Past Perfect'], ['category' => 'Grammar']),
        ];

        $detailedTags = [
            'Past Simple' => Tag::firstOrCreate(['name' => 'Past Simple'], ['category' => 'Tenses']),
            'Present Perfect' => Tag::firstOrCreate(['name' => 'Present Perfect'], ['category' => 'Tenses']),
            'Present Perfect Continuous' => Tag::firstOrCreate(['name' => 'Present Perfect Continuous'], ['category' => 'Tenses']),
            'Past Perfect' => Tag::firstOrCreate(['name' => 'Past Perfect'], ['category' => 'Tenses']),
        ];

        $sections = [
            1 => [
                'level' => 'B1',
                'questions' => [
                    [
                        'q' => "Mary {a1} to Paris, so she's very excited about her journey.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has never been', 'verb_hint' => 'never/go'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'McDonalds {a1} a new restaurant near my house, but I {a2} there yet.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has opened', 'verb_hint' => 'open'],
                            ['marker' => 'a2', 'answer' => "haven't gone", 'verb_hint' => 'not/go'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'My father {a1} smoking two years ago and he {a2} a cigarette since then.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'gave up', 'verb_hint' => 'give up'],
                            ['marker' => 'a2', 'answer' => "hasn't smoked", 'verb_hint' => 'not/smoke'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                    ],
                    [
                        'q' => "It's ten years since I last {a1} Jim.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'saw', 'verb_hint' => 'see'],
                        ],
                        'tenses' => ['Past Simple'],
                    ],
                    [
                        'q' => 'She {a1} in Rome for ten years, but she {a2} there since she {a3} to Florence.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'lived', 'verb_hint' => 'live'],
                            ['marker' => 'a2', 'answer' => "hasn't returned", 'verb_hint' => 'not/return'],
                            ['marker' => 'a3', 'answer' => 'moved', 'verb_hint' => 'move'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                    ],
                    [
                        'q' => 'I need the car, Sue. Where {a1} it?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'have you parked', 'verb_hint' => 'you/park'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'The postman {a1} the letters. Here you are, Sam!',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has just delivered', 'verb_hint' => 'just/deliver'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'I {a1} to drive when I {a2} 20, but I {a3} driving.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'learned', 'verb_hint' => 'learn'],
                            ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                            ['marker' => 'a3', 'answer' => 'have never enjoyed', 'verb_hint' => 'never/enjoy'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                    ],
                    [
                        'q' => 'It {a1} since this morning.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has been raining', 'verb_hint' => 'rain'],
                        ],
                        'tenses' => ['Present Perfect Continuous'],
                    ],
                    [
                        'q' => 'Mum {a1} a new type of cheese. Do you want to taste it?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has bought', 'verb_hint' => 'buy'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'Luke {a1} a motorbike since his accident.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hasn't ridden", 'verb_hint' => 'not/ride'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => "I suppose you {a1} that we're meeting Terry tonight, {a2}?",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "haven't forgotten", 'verb_hint' => 'not/forget'],
                            ['marker' => 'a2', 'answer' => 'have you'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'My mother {a1} as a teacher until 2009, but since last year she {a2} a cook.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'worked', 'verb_hint' => 'work'],
                            ['marker' => 'a2', 'answer' => 'has been', 'verb_hint' => 'be'],
                        ],
                        'tenses' => ['Past Simple', 'Present Perfect'],
                    ],
                    [
                        'q' => 'It {a1} very hard to get used to living in a village after having lived in a big city for years.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'has been', 'verb_hint' => 'be'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'I {a1} much of Tom lately.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "haven't heard", 'verb_hint' => 'not/hear'],
                        ],
                        'tenses' => ['Present Perfect'],
                    ],
                    [
                        'q' => 'What time {a1} to the gym yesterday? I {a2} you.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'did you go', 'verb_hint' => 'you/go'],
                            ['marker' => 'a2', 'answer' => "didn't see", 'verb_hint' => 'not/see'],
                        ],
                        'tenses' => ['Past Simple'],
                    ],
                ],
            ],
            2 => [
                'level' => 'B2',
                'questions' => [
                    [
                        'q' => 'Dave finally {a1} the promotion that his manager {a2} him due to his hard work.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'rejected', 'verb_hint' => 'reject'],
                            ['marker' => 'a2', 'answer' => 'had offered', 'verb_hint' => 'offer'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'I {a1} abroad until I {a2} to Germany.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had never travelled', 'verb_hint' => 'never/travel'],
                            ['marker' => 'a2', 'answer' => 'moved', 'verb_hint' => 'move'],
                        ],
                        'tenses' => ['Past Perfect', 'Past Simple'],
                    ],
                    [
                        'q' => 'As soon as Patrick {a1} the telegram, he {a2} what {a3}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'got', 'verb_hint' => 'get'],
                            ['marker' => 'a2', 'answer' => 'knew', 'verb_hint' => 'know'],
                            ['marker' => 'a3', 'answer' => 'had happened', 'verb_hint' => 'happen'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Once the party {a1} everybody {a2} except for Sue that {a3} us to tidy the house.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had finished', 'verb_hint' => 'finish'],
                            ['marker' => 'a2', 'answer' => 'left', 'verb_hint' => 'leave'],
                            ['marker' => 'a3', 'answer' => 'helped', 'verb_hint' => 'help'],
                        ],
                        'tenses' => ['Past Perfect', 'Past Simple'],
                    ],
                    [
                        'q' => 'Carl {a1} me about our economic problems until everything {a2}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "didn't tell", 'verb_hint' => 'not/tell'],
                            ['marker' => 'a2', 'answer' => 'had been settled', 'verb_hint' => 'be settled'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Alice finally {a1} her wedding dress after she {a2} at least twenty of them.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'bought', 'verb_hint' => 'buy'],
                            ['marker' => 'a2', 'answer' => 'had tried on', 'verb_hint' => 'try on'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Carol {a1} her exam in September since she {a2} it in June.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'resat', 'verb_hint' => 'resit'],
                            ['marker' => 'a2', 'answer' => 'had failed', 'verb_hint' => 'fail'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Lewis {a1} as much as his wife! I {a2} them for five years.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't changed", 'verb_hint' => 'not/change'],
                            ['marker' => 'a2', 'answer' => "hadn't seen", 'verb_hint' => 'not/see'],
                        ],
                        'tenses' => ['Past Perfect'],
                    ],
                    [
                        'q' => "When Tina's mother {a1}, she {a2} a fortune and a fabulous mansion in Los Angeles.",
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'died', 'verb_hint' => 'die'],
                            ['marker' => 'a2', 'answer' => 'inherited', 'verb_hint' => 'inherit'],
                        ],
                        'tenses' => ['Past Simple'],
                    ],
                    [
                        'q' => 'Kim {a1} the job because she {a2} herself for the interview.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "didn't get", 'verb_hint' => 'not/get'],
                            ['marker' => 'a2', 'answer' => "hadn't prepared", 'verb_hint' => 'not/prepare'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'When I {a1} this morning, everything {a2} white. Definitely, it {a3} during the night.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'woke up', 'verb_hint' => 'wake up'],
                            ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                            ['marker' => 'a3', 'answer' => 'had snowed', 'verb_hint' => 'snow'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Pam {a1} that they {a2} her to their wedding reception.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'never forgot', 'verb_hint' => 'never/forget'],
                            ['marker' => 'a2', 'answer' => "hadn't invited", 'verb_hint' => 'not/invite'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Our daughter {a1} an e-mail as soon as she {a2} in London. She {a3} to phone us, but she {a4}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'sent', 'verb_hint' => 'send'],
                            ['marker' => 'a2', 'answer' => 'arrived', 'verb_hint' => 'arrive'],
                            ['marker' => 'a3', 'answer' => 'had promised', 'verb_hint' => 'promise'],
                            ['marker' => 'a4', 'answer' => "didn't", 'verb_hint' => 'not'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                    ],
                    [
                        'q' => 'Dick {a1} that he {a2} as hard as his teacher {a3}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'recognised', 'verb_hint' => 'recognise'],
                            ['marker' => 'a2', 'answer' => "hadn't worked", 'verb_hint' => 'not/work'],
                            ['marker' => 'a3', 'answer' => 'expected', 'verb_hint' => 'expect'],
                        ],
                        'tenses' => ['Past Simple', 'Past Perfect'],
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

                $tagIds = [$generalTags[$section]->id];
                foreach ($q['tenses'] as $tense) {
                    $tagIds[] = $detailedTags[$tense]->id;
                }

                $items[] = [
                    'uuid'        => $uuid,
                    'question'    => $q['q'],
                    'difficulty'  => 2,
                    'category_id' => $categoryId,
                    'flag'        => 0,
                    'source_id'   => $sources[$section],
                    'tag_ids'     => $tagIds,
                    'level'       => $data['level'],
                    'answers'     => $q['answers'],
                    'options'     => [],
                ];
            }
        }

        $service->seed($items);
    }
}
