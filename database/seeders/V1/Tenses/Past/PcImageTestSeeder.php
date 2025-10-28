<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Models\{Category, Source, Tag, Test};
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class PcImageTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple and Continuous (PC Image)'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'pc_image_test']);

        $questions = [
            [
                'question' => 'My granny {a1} her leg last week.',
                'answers' => [
                    'a1' => ['answer' => 'broke', 'verb_hint' => 'break'],
                ],
                'options' => [],
            ],
            [
                'question' => "I {a1} the match on TV at 9 o'clock.",
                'answers' => [
                    'a1' => ['answer' => "wasn't watching", 'verb_hint' => 'not/watch'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} a noise while I {a2} my homework.',
                'answers' => [
                    'a1' => ['answer' => 'heard', 'verb_hint' => 'hear'],
                    'a2' => ['answer' => 'was doing', 'verb_hint' => 'do'],
                ],
                'options' => [],
            ],
            [
                'question' => 'What time {a1} your parents {a2} home last night?',
                'answers' => [
                    'a1' => ['answer' => 'did', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'get', 'verb_hint' => 'get'],
                ],
                'options' => [],
            ],
            [
                'question' => 'What {a1} you {a2} this time last Friday?',
                'answers' => [
                    'a1' => ['answer' => 'were', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'doing', 'verb_hint' => 'do'],
                ],
                'options' => [],
            ],
            [
                'question' => 'My mum {a1} lunch while my brother and I {a2} the homework.',
                'answers' => [
                    'a1' => ['answer' => 'was preparing', 'verb_hint' => 'prepare'],
                    'a2' => ['answer' => 'were doing', 'verb_hint' => 'do'],
                ],
                'options' => [],
            ],
            [
                'question' => 'My father {a1} the plants in the garden last weekend.',
                'answers' => [
                    'a1' => ['answer' => 'watered', 'verb_hint' => 'water'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} a shower when someone {a2} on the door.',
                'answers' => [
                    'a1' => ['answer' => 'was having', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'knocked', 'verb_hint' => 'knock'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} to the cinema last weekend.',
                'answers' => [
                    'a1' => ['answer' => 'went', 'verb_hint' => 'go'],
                ],
                'options' => [],
            ],
            [
                'question' => 'How many birthday presents {a1} you {a2} last year?',
                'answers' => [
                    'a1' => ['answer' => 'did', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'get', 'verb_hint' => 'get'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} while my little sister {a2} with her dolls.',
                'answers' => [
                    'a1' => ['answer' => 'was studying', 'verb_hint' => 'study'],
                    'a2' => ['answer' => 'was playing', 'verb_hint' => 'play'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} London last summer.',
                'answers' => [
                    'a1' => ['answer' => 'visited', 'verb_hint' => 'visit'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} my homework last Monday and my teacher {a2} angry.',
                'answers' => [
                    'a1' => ['answer' => "didn't do", 'verb_hint' => 'not/do'],
                    'a2' => ['answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => [],
            ],
            [
                'question' => 'They {a1} this time yesterday.',
                'answers' => [
                    'a1' => ['answer' => "weren't skating", 'verb_hint' => 'not/skate'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} you last night, but you {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'phoned', 'verb_hint' => 'phone'],
                    'a2' => ['answer' => "didn't answer", 'verb_hint' => 'not/answer'],
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
            ['slug' => 'pc-image-test'],
            [
                'name' => 'Past Simple and Continuous (PC Image)',
                'filters' => [],
                'questions' => array_column($items, 'uuid'),
            ]
        );
    }
}
