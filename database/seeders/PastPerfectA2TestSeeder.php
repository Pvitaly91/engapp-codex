<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastPerfectA2TestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;

        $sources = [
            1 => Source::firstOrCreate(['name' => 'Past perfect — Exercise 1'])->id,
            2 => Source::firstOrCreate(['name' => 'Past perfect — Exercise 2'])->id,
            3 => Source::firstOrCreate(['name' => 'Past perfect — Exercise 3'])->id,
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
                        'q' => 'First, I wrote a letter. Second, I showed him the letter. ⇒ I showed him a letter that I {a1}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had written', 'verb_hint' => null],
                        ],
                        'options' => ['had written', 'wrote'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I wrote a letter. Second, I showed him the letter. ⇒ I wrote a letter and a few days later I {a1} it to him.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'showed', 'verb_hint' => null],
                        ],
                        'options' => ['had shown', 'showed'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, it started raining. Second, I looked through the window. ⇒ When I looked through the window, it {a1} raining.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'started', 'verb_hint' => null],
                        ],
                        'options' => ['had started', 'started'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, it rained. Second, I looked through the window. ⇒ When I looked through the window, it {a1}.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had rained', 'verb_hint' => null],
                        ],
                        'options' => ['rained', 'had rained'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I had dinner. Second, you called. ⇒ When you called, I {a1} dinner.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had had', 'verb_hint' => null],
                        ],
                        'options' => ['had had', 'had'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, you called. Second, I had dinner. ⇒ When you called, I {a1} dinner.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had', 'verb_hint' => null],
                        ],
                        'options' => ['had had', 'had'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, she bought a T-shirt in the sales. Second, she gave it to me. ⇒ She bought a T-shirt in the sales and {a1} it to me as a birthday present.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'gave', 'verb_hint' => null],
                        ],
                        'options' => ['had given', 'gave'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, she bought a T-shirt in the sales. Second, she gave it to me. ⇒ She gave me a T-shirt that she {a1} in the sales.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had bought', 'verb_hint' => null],
                        ],
                        'options' => ['bought', 'had bought'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I finished my composition. Second, the teacher arrived. ⇒ When the teacher arrived, I {a1} my composition.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'finished', 'verb_hint' => null],
                        ],
                        'options' => ['finished', 'had finished'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, the teacher arrived. Second, I finished my composition. ⇒ When the teacher arrived, I {a1} my composition.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had finished', 'verb_hint' => null],
                        ],
                        'options' => ['finished', 'had finished'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                ],
            ],
            2 => [
                'questions' => [
                    [
                        'q' => 'First, Jim didn’t finish his dinner. Second, Mum got angry. ⇒ Mum was angry because when I sat down to watch TV, I {a1} my dinner.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't finished", 'verb_hint' => null],
                        ],
                        'options' => ["hadn't finished", "didn't finish", "hadn't finish"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I forgot the keys at work. Second, I tried to open the door. ⇒ I couldn’t open the door because I {a1} the keys at work.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d forgotten", 'verb_hint' => null],
                        ],
                        'options' => ["'d forget", "'d forgotten", 'forgot'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, Rita visited the Louvre. Second, she travelled to France. ⇒ When Rita travelled to France, she {a1} the Louvre.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'visited', 'verb_hint' => null],
                        ],
                        'options' => ["'d visited", "'d visit", 'visited'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, somebody ate all the cookies. Second, I opened the jar. ⇒ There were no cookies in the cookie jar. Somebody {a1} them all.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had eaten', 'verb_hint' => null],
                        ],
                        'options' => ['had eaten', "'d eat", 'ate'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I saw cookies in the jar. Second, I ate them. ⇒ I saw some cookies in the cookie jar and I {a1} them all.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'ate', 'verb_hint' => null],
                        ],
                        'options' => ["'d eaten", "'d eat", 'ate'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I locked the door. Second, I left home. ⇒ I locked the door and then I {a1} home.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'left', 'verb_hint' => null],
                        ],
                        'options' => ["'d left", 'left', "'d leave"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I left home. Second, I realised. ⇒ When I got home I saw that I {a1} the door.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't locked", 'verb_hint' => null],
                        ],
                        'options' => ["hadn't locked", "hadn't lock", "didn't lock"],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, nobody cleaned the house. Second, the house was dirty. ⇒ The house was dirty because nobody {a1} it.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had cleaned', 'verb_hint' => null],
                        ],
                        'options' => ["'d clean", 'had cleaned', 'cleaned'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, she finished reading my book. Second, she gave it back. ⇒ She gave me back my book because she {a1} reading it.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "'d finished", 'verb_hint' => null],
                        ],
                        'options' => ["'d finish", "'d finished", 'finished'],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, she finished the book. Second, she gave it back to me. ⇒ When she finished the book, she {a1} it back to me.',
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
                        'q' => 'First, Jim didn’t call. Second, I was worried. ⇒ I was worried because Jim {a1} yet.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => "hadn't called", 'verb_hint' => 'not call'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I was worried. Second, I called Jim. ⇒ I was so worried that I {a1} Jim.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'called', 'verb_hint' => 'call'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I bought trousers in Camden market. Second, I couldn’t find them. ⇒ I couldn’t find the trousers that I {a1} in Camden market.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had bought', 'verb_hint' => 'buy'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I couldn’t find my trousers. Second, I wore a skirt. ⇒ I couldn’t find my favourite trousers so I {a1} to wear a skirt.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'decided', 'verb_hint' => 'decide'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I lied to them. Second, they got angry. ⇒ I lied to them and they {a1} furious.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'got', 'verb_hint' => 'get'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I made a mistake. Second, they were angry. ⇒ They were angry, but why? {a1} a terrible mistake?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'Had I made', 'verb_hint' => 'I/make'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, John lost his dog. Second, he asked me. ⇒ John asked me if I {a1} his dog.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had seen', 'verb_hint' => 'see'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Simple', 'Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, fireworks scared the dog. Second, the dog disappeared. ⇒ John didn’t have his dog. Apparently the dog {a1} away during some fireworks.',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had run', 'verb_hint' => 'run'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, I put the keys somewhere. Second, I couldn’t find them. ⇒ I couldn’t find the keys. Where {a1} them?',
                        'answers' => [
                            ['marker' => 'a1', 'answer' => 'had I put', 'verb_hint' => 'I/put'],
                        ],
                        'options' => [],
                        'tenses' => ['Past Perfect'],
                        'level' => 'B1',
                    ],
                    [
                        'q' => 'First, my boss was upset. Second, I tried to talk. ⇒ My boss was upset, so I {a1} to talk to him.',
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
