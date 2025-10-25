<?php
namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PresentSimpleSeeder extends Seeder
{

    public function run()
    {
        $categoryId = 2; // ID категорії для Present Simple
        $sourceId = Source::firstOrCreate([
            'name' => 'Present simple. Complete the sentences with the correct variant.'
        ])->id;

        $themeTag = Tag::firstOrCreate(['name' => 'present_simple']);

        $data = [
            [
                'question' => 'Bob always {a1} tea in the morning.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'drinks',
                        'verb_hint' => 'drink',
                    ]
                ],
                'options' => ['drink', 'drinks'],
            ],
            [
                'question' => 'We {a1} to the music every day.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'listen',
                        'verb_hint' => 'listen',
                    ]
                ],
                'options' => ['listen', 'listens'],
            ],
            [
                'question' => 'Mary usually {a1} TV in the evening.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'watches',
                        'verb_hint' => 'watch',
                    ]
                ],
                'options' => ['watch', 'watches'],
            ],
            [
                'question' => 'The girls often {a1} with the dolls.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'play',
                        'verb_hint' => 'play',
                    ]
                ],
                'options' => ['play', 'plays'],
            ],
            [
                'question' => 'They {a1} their homework every day.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => 'do',
                        'verb_hint' => 'do',
                    ]
                ],
                'options' => ['do', 'does'],
            ],
            [
                'question' => 'I {a1} watch TV after school.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "don't",
                        'verb_hint' => 'do',
                    ]
                ],
                'options' => ["don't", "doesn't"],
            ],
            [
                'question' => 'My sister {a1} play tennis.',
                'difficulty' => 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "doesn't",
                        'verb_hint' => 'do',
                    ]
                ],
                'options' => ["don't", "doesn't"],
            ],
            [
                'question' => 'Marta {a1} her dad’s car.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "washes",
                        'verb_hint' => 'wash',
                    ]
                ],
                'options' => ["wash", "washes"],
            ],
            [
                'question' => 'The baby {a1} every night.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "cries",
                        'verb_hint' => 'cry',
                    ]
                ],
                'options' => ["cry", "cries"],
            ],
            [
                'question' => 'The birds {a1} in the sky.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    [
                        'marker' => 'a1',
                        'answer' => "fly",
                        'verb_hint' => 'fly',
                    ]
                ],
                'options' => ["fly", "flies"],
            ],
        ];

        $service = new QuestionSeedingService();
        $items = [];
        foreach ($data as $i => $d) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $d['uuid']        = $uuid;
            $d['category_id'] = $categoryId;
            $d['tag_ids']     = [$themeTag->id];

            $items[] = $d;
        }

        $service->seed($items);
    }
}
