<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use App\Services\ChatGPTService;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestContiniusesSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Present, Past, and Future Continuous Tense Worksheet'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'continuous_tenses_worksheet']);

        $questions = [
            [
                'question' => "At 6 o'clock this morning, I {a1}.",
                'answers' => [
                    'a1' => ['answer' => 'was exercising', 'verb_hint' => 'exercise'],
                ],
                'options' => [],
            ],
            [
                'question' => "Don't disturb me. I {a1} my project at 6 o'clock tonight.",
                'answers' => [
                    'a1' => ['answer' => 'will be finishing', 'verb_hint' => 'finish'],
                ],
                'options' => [],
            ],
            [
                'question' => 'They {a1} soccer at this time last week.',
                'answers' => [
                    'a1' => ['answer' => 'were playing', 'verb_hint' => 'play'],
                ],
                'options' => [],
            ],
            [
                'question' => 'A girl {a1} her bike in the park at the moment.',
                'answers' => [
                    'a1' => ['answer' => 'is riding', 'verb_hint' => 'ride'],
                ],
                'options' => [],
            ],
            [
                'question' => 'At this time yesterday, Mr. Baker {a1} an essay.',
                'answers' => [
                    'a1' => ['answer' => 'was writing', 'verb_hint' => 'write'],
                ],
                'options' => [],
            ],
            [
                'question' => 'It {a1} when I got up this morning.',
                'answers' => [
                    'a1' => ['answer' => 'was raining', 'verb_hint' => 'rain'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Mary and her daughter {a1} shopping at 07:30 a.m. tomorrow.',
                'answers' => [
                    'a1' => ['answer' => 'will be going', 'verb_hint' => 'go'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Jojo {a1} his brother\'s toy right now.',
                'answers' => [
                    'a1' => ['answer' => 'is repairing', 'verb_hint' => 'repair'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The children {a1} in the classroom now.',
                'answers' => [
                    'a1' => ['answer' => "aren't sleeping", 'verb_hint' => 'not/sleep'],
                ],
                'options' => [],
            ],
            [
                'question' => 'John broke his leg when he {a1} last week.',
                'answers' => [
                    'a1' => ['answer' => 'was skiing', 'verb_hint' => 'ski'],
                ],
                'options' => [],
            ],
            [
                'question' => 'He {a1} to his boss at his office at present.',
                'answers' => [
                    'a1' => ['answer' => 'is talking', 'verb_hint' => 'talk'],
                ],
                'options' => [],
            ],
            [
                'question' => 'She {a1} in the kitchen when her phone rang.',
                'answers' => [
                    'a1' => ['answer' => 'was cooking', 'verb_hint' => 'cook'],
                ],
                'options' => [],
            ],
            [
                'question' => 'At 2 p.m. tomorrow, my parents {a1} to South Korea.',
                'answers' => [
                    'a1' => ['answer' => 'will be flying', 'verb_hint' => 'fly'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The teachers {a1} about students\' grades at this time next week.',
                'answers' => [
                    'a1' => ['answer' => 'will be discussing', 'verb_hint' => 'discuss'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The headmaster {a1} a speech right now.',
                'answers' => [
                    'a1' => ['answer' => 'is delivering', 'verb_hint' => 'deliver'],
                ],
                'options' => [],
            ],
            [
                'question' => 'The farmers {a1} on their field right now.',
                'answers' => [
                    'a1' => ['answer' => 'are working', 'verb_hint' => 'work'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Look! It {a1} outside.',
                'answers' => [
                    'a1' => ['answer' => 'is snowing', 'verb_hint' => 'snow'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Two little boys {a1} when their father arrived home.',
                'answers' => [
                    'a1' => ['answer' => 'were crying', 'verb_hint' => 'cry'],
                ],
                'options' => [],
            ],
            [
                'question' => 'I {a1} a birthday party tonight.',
                'answers' => [
                    'a1' => ['answer' => 'am attending', 'verb_hint' => 'attend'],
                ],
                'options' => [],
            ],
            [
                'question' => 'At 7:00 the previous evening, my grandmother {a1} a story.',
                'answers' => [
                    'a1' => ['answer' => 'was telling', 'verb_hint' => 'tell'],
                ],
                'options' => [],
            ],
        ];

        $service = new QuestionSeedingService;
        $items = [];
        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug = Str::slug(class_basename(self::class));
            $max = 36 - strlen((string) $index) - 1;
            $uuid = substr($slug, 0, $max).'-'.$index;

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

        $chatgpt = app(ChatGPTService::class);
        $questionTexts = array_column($questions, 'question');
        $description = $chatgpt->generateTestDescription($questionTexts);

        Test::updateOrCreate(
            ['slug' => 'continuous-tenses-worksheet'],
            [
                'name' => 'Continuous Tenses Worksheet',
                'filters' => [],
                'questions' => array_column($items, 'uuid'),
                'description' => $description,
            ]
        );
    }
}
