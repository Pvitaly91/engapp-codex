<?php

namespace Database\Seeders\V1\Tenses\Past;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Test;
use App\Services\QuestionSeedingService;
use App\Support\Database\Seeder;
use Illuminate\Support\Str;

class PastSimpleOrPastContinuousTestSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple or Past Continuous'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'past_simple_or_past_continuous_test']);

        $questions = [
            [
                'question' => 'I {a1} down the street when I {a2} a new shop.',
                'answers' => [
                    'a1' => ['answer' => 'was walking', 'verb_hint' => 'walk'],
                    'a2' => ['answer' => 'noticed', 'verb_hint' => 'notice'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Where {a1} yesterday at 5 pm? - They {a2} dinner with their friends.',
                'answers' => [
                    'a1' => ['answer' => 'were they', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'were having', 'verb_hint' => 'have'],
                ],
                'options' => [],
            ],
            [
                'question' => 'While she {a1} in the garden, her brother {a2} computer games.',
                'answers' => [
                    'a1' => ['answer' => 'was working', 'verb_hint' => 'work'],
                    'a2' => ['answer' => 'was playing', 'verb_hint' => 'play'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Imagine the situation that {a1} to me yesterday. I {a2} up early in the morning, {a3} my teeth, {a4} breakfast, and while I {a5} it, somebody suddenly {a6} out and I {a7} scared. Then I {a8} out it {a9} a cat.',
                'answers' => [
                    'a1' => ['answer' => 'happened', 'verb_hint' => 'happen'],
                    'a2' => ['answer' => 'got', 'verb_hint' => 'get'],
                    'a3' => ['answer' => 'brushed', 'verb_hint' => 'brush'],
                    'a4' => ['answer' => 'prepared', 'verb_hint' => 'prepare'],
                    'a5' => ['answer' => 'was eating', 'verb_hint' => 'eat'],
                    'a6' => ['answer' => 'screamed', 'verb_hint' => 'scream'],
                    'a7' => ['answer' => 'got', 'verb_hint' => 'get'],
                    'a8' => ['answer' => 'found', 'verb_hint' => 'find'],
                    'a9' => ['answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => [],
            ],
            [
                'question' => 'What {a1} from 4 pm to 6 pm last Monday? - Well, I\'m not sure but I think I {a2} an essay about the environment.',
                'answers' => [
                    'a1' => ['answer' => 'were you doing', 'verb_hint' => 'do'],
                    'a2' => ['answer' => 'was writing', 'verb_hint' => 'write'],
                ],
                'options' => [],
            ],
            [
                'question' => 'He {a1} for an exam when his mom {a2} in.',
                'answers' => [
                    'a1' => ['answer' => 'was studying', 'verb_hint' => 'study'],
                    'a2' => ['answer' => 'came', 'verb_hint' => 'come'],
                ],
                'options' => [],
            ],
            [
                'question' => 'They {a1} on the sofa the whole afternoon yesterday. Would you believe it?',
                'answers' => [
                    'a1' => ['answer' => 'were lying', 'verb_hint' => 'lie'],
                ],
                'options' => [],
            ],
            [
                'question' => 'We {a1} lunch when the bell {a2}.',
                'answers' => [
                    'a1' => ['answer' => 'were cooking', 'verb_hint' => 'cook'],
                    'a2' => ['answer' => 'rang', 'verb_hint' => 'ring'],
                ],
                'options' => [],
            ],
            [
                'question' => 'She {a1} a weird-looking man while she {a2} by a new shopping centre.',
                'answers' => [
                    'a1' => ['answer' => 'saw', 'verb_hint' => 'see'],
                    'a2' => ['answer' => 'was passing', 'verb_hint' => 'pass'],
                ],
                'options' => [],
            ],
            [
                'question' => 'While I {a1} a bath, my husband {a2} back home.',
                'answers' => [
                    'a1' => ['answer' => 'was having', 'verb_hint' => 'have'],
                    'a2' => ['answer' => 'arrived', 'verb_hint' => 'arrive'],
                ],
                'options' => [],
            ],
            [
                'question' => 'He {a1} for his wife when he {a2} a stain on his jacket.',
                'answers' => [
                    'a1' => ['answer' => 'was waiting', 'verb_hint' => 'wait'],
                    'a2' => ['answer' => 'spotted', 'verb_hint' => 'spot'],
                ],
                'options' => [],
            ],
            [
                'question' => 'When we {a1} home, we {a2} that our water pipes {a3} water. We {a4} to fix it immediately because the kitchen {a5} full of water.',
                'answers' => [
                    'a1' => ['answer' => 'got', 'verb_hint' => 'get'],
                    'a2' => ['answer' => 'discovered', 'verb_hint' => 'discover'],
                    'a3' => ['answer' => 'were leaking', 'verb_hint' => 'leak'],
                    'a4' => ['answer' => 'had', 'verb_hint' => 'have'],
                    'a5' => ['answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => [],
            ],
            [
                'question' => 'She {a1} a new toy to her younger sister.',
                'answers' => [
                    'a1' => ['answer' => 'gave', 'verb_hint' => 'give'],
                ],
                'options' => [],
            ],
            [
                'question' => 'Birds {a1} while we {a2} last weekend.',
                'answers' => [
                    'a1' => ['answer' => 'were singing', 'verb_hint' => 'sing'],
                    'a2' => ['answer' => 'were cycling', 'verb_hint' => 'cycle'],
                ],
                'options' => [],
            ],
            [
                'question' => 'They {a1} in the ocean when a big shark {a2} close to them.',
                'answers' => [
                    'a1' => ['answer' => 'were swimming', 'verb_hint' => 'swim'],
                    'a2' => ['answer' => 'appeared', 'verb_hint' => 'appear'],
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

        Test::updateOrCreate(
            ['slug' => 'past-simple-or-past-continuous'],
            [
                'name' => 'Past Simple or Past Continuous',
                'filters' => [],
                'questions' => array_column($items, 'uuid'),
            ]
        );
    }
}
