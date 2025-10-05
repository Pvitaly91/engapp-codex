<?php

namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class PastContinuousTenseTestSeeder extends Seeder
{
    public function run()
    { 
        $categoryId = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Past Continuous Tense Test'])->id;
        $themeTag = Tag::firstOrCreate(['name' => 'past_continuous_tense_test']);

        $questions = [
            [
                'question' => 'I {a1} TV when she called.',
                'answers' => [
                    'a1' => ['answer' => 'was watching', 'verb_hint' => 'watch'],
                ],
                'options' => ['was watching', 'watched', 'watching'],
            ],
            [
                'question' => 'They {a1} dinner when the phone rang.',
                'answers' => [
                    'a1' => ['answer' => 'were having', 'verb_hint' => 'have'],
                ],
                'options' => ['were having', 'had', 'have'],
            ],
            [
                'question' => 'He {a1} to the radio when I entered the room.',
                'answers' => [
                    'a1' => ['answer' => 'was listening', 'verb_hint' => 'listen'],
                ],
                'options' => ['was listening', 'listened', 'listens'],
            ],
            [
                'question' => 'We {a1} in the park all afternoon.',
                'answers' => [
                    'a1' => ['answer' => 'were sitting', 'verb_hint' => 'sit'],
                ],
                'options' => ['were sitting', 'sat', 'sit'],
            ],
            [
                'question' => 'It {a1} when we left the house.',
                'answers' => [
                    'a1' => ['answer' => 'was raining', 'verb_hint' => 'rain'],
                ],
                'options' => ['was raining', 'rained', 'rains'],
            ],
            [
                'question' => 'What {a1} you {a2} at 7 p.m. yesterday?',
                'answers' => [
                    'a1' => ['answer' => 'were', 'verb_hint' => 'be'],
                    'a2' => ['answer' => 'doing', 'verb_hint' => 'do'],
                ],
                'options' => ['were / doing', 'are / doing', 'were / did'],
            ],
            [
                'question' => 'She {a1} when he arrived.',
                'answers' => [
                    'a1' => ['answer' => 'was sleeping', 'verb_hint' => 'sleep'],
                ],
                'options' => ['was sleeping', 'slept', 'sleeps'],
            ],
            [
                'question' => 'They {a1} football while I {a2} my homework.',
                'answers' => [
                    'a1' => ['answer' => 'were playing', 'verb_hint' => 'play'],
                    'a2' => ['answer' => 'was doing', 'verb_hint' => 'do'],
                ],
                'options' => ['were playing / was doing', 'played / did', 'played / was doing'],
            ],
            [
                'question' => 'The children {a1} quietly when the teacher came in.',
                'answers' => [
                    'a1' => ['answer' => 'were talking', 'verb_hint' => 'talk'],
                ],
                'options' => ['were talking', 'talked', 'talk'],
            ],
            [
                'question' => 'I {a1} to the store when I saw an old friend.',
                'answers' => [
                    'a1' => ['answer' => 'was walking', 'verb_hint' => 'walk'],
                ],
                'options' => ['was walking', 'walked', 'am walking'],
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
    }
}

