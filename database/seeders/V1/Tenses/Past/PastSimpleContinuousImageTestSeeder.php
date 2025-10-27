<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Support\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\{Category, Source, Tag, Test};
use App\Services\QuestionSeedingService;

class PastSimpleContinuousImageTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple and Continuous (Image)'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'past_simple_continuous_image_test']);

        $questions = [
            [
                'question' => "Frank didn't {a1} the new movie very much.",
                'answers' => [
                    'a1' => ['answer' => 'like', 'verb_hint' => 'like'],
                ],
                'options' => ['liked', 'liking', 'like'],
            ],
            [
                'question' => 'My friends {a1} a tree in the forest yesterday.',
                'answers' => [
                    'a1' => ['answer' => 'climbed', 'verb_hint' => 'climb'],
                ],
                'options' => ['climbed', 'were climbing', 'not climbed'],
            ],
            [
                'question' => '{a1} she {a2} her trip to Miami last summer?',
                'answers' => [
                    'a1' => ['answer' => 'Did', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'enjoy', 'verb_hint' => 'enjoy'],
                ],
                'options' => ['Did / enjoy', 'Was / enjoying', 'Was / enjoyed'],
            ],
            [
                'question' => 'My sister {a1} the plants while I was sweeping the floor.',
                'answers' => [
                    'a1' => ['answer' => 'was watering', 'verb_hint' => 'water'],
                ],
                'options' => ['was watered', 'was watering', 'did watered'],
            ],
            [
                'question' => 'Johanna is a retired journalist. She {a1} many artists on TV.',
                'answers' => [
                    'a1' => ['answer' => 'interviewed', 'verb_hint' => 'interview'],
                ],
                'options' => ['interviewed', 'was interviewing', 'was interview'],
            ],
            [
                'question' => 'He {a1} any letters while he was living in Canada.',
                'answers' => [
                    'a1' => ['answer' => "didn't send", 'verb_hint' => 'not/send'],
                ],
                'options' => ['not sent', "didn't send", "wasn't sent"],
            ],
            [
                'question' => '{a1} you {a2} when I called?',
                'answers' => [
                    'a1' => ['answer' => 'Were', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'sleeping', 'verb_hint' => 'sleep'],
                ],
                'options' => ['Were / sleep', 'Did / sleep', 'Were / sleeping'],
            ],
            [
                'question' => 'Why {a1} she {a2} when I arrived?',
                'answers' => [
                    'a1' => ['answer' => 'was', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'crying', 'verb_hint' => 'cry'],
                ],
                'options' => ['did / cried', 'did / crying', 'was / crying'],
            ],
            [
                'question' => 'He {a1} his hand while he was cooking.',
                'answers' => [
                    'a1' => ['answer' => 'burnt', 'verb_hint' => 'burn'],
                ],
                'options' => ['burnt', 'was burning', 'was burnt'],
            ],
            [
                'question' => 'They {a1} when the police arrived.',
                'answers' => [
                    'a1' => ['answer' => "weren't fighting", 'verb_hint' => 'not/fight'],
                ],
                'options' => ["didn't fight", "weren't fighting", 'not fought'],
            ],
            [
                'question' => 'When I {a1}, my mum {a2} dinner.',
                'answers' => [
                    'a1' => ['answer' => 'got back', 'verb_hint' => 'get back'],
                    'a2' => ['answer' => 'was cooking', 'verb_hint' => 'cook'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Nick {a1} at a party when he {a2} his girlfriend.',
                'answers' => [
                    'a1' => ['answer' => 'was dancing', 'verb_hint' => 'dance'],
                    'a2' => ['answer' => 'met', 'verb_hint' => 'meet'],
                ],
                'options' => [],
            ],
            [
                'question' => 'A: What {a1} last weekend? B: Nothing special. I {a2} watching that series on Netflix. I {a3} the ending.',
                'answers' => [
                    'a1' => ['answer' => 'were you doing', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'finished', 'verb_hint' => 'finish'],
                    'a3' => ['answer' => "didn't like", 'verb_hint' => 'not/like'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Linda {a1} when the shark {a2} her.',
                'answers' => [
                    'a1' => ['answer' => 'was surfing', 'verb_hint' => 'surf'],
                    'a2' => ['answer' => 'attacked', 'verb_hint' => 'attack'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} a picnic when it {a2} to rain.',
                'answers' => [
                    'a1' => ['answer' => 'were having', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'started', 'verb_hint' => 'start'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} to my friend on the phone when the bus {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'was talking', 'verb_hint' => 'talk'],
                    'a2' => ['answer' => 'arrived', 'verb_hint' => 'arrive'],
                ],
                'options' => [],
            ],
            [
                'question' => 'When the earthquake {a1}, they {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'happened', 'verb_hint' => 'happen'],
                    'a2' => ['answer' => 'were sleeping', 'verb_hint' => 'sleep'],
                ],
                'options' => [],
            ],
            [
                'question' => 'What {a1} at 9:00 last night?',
                'answers' => [
                    'a1' => ['answer' => 'was she doing', 'verb_hint' => 'do'],
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
            ['slug' => 'past-simple-continuous-image'],
            [
                'name' => 'Past Simple and Continuous (Image)',
                'filters' => [],
                'questions' => array_column($items, 'uuid'),
            ]
        );
    }
}
