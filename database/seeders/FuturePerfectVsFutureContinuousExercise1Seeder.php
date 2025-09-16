<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class FuturePerfectVsFutureContinuousExercise1Seeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Future Perfect vs Future Continuous — Exercise 1'])->id;

        $detailTag = Tag::firstOrCreate(
            ['name' => 'future_perfect_vs_future_continuous_exercise_1'],
            ['category' => 'Details']
        );

        $tenseTags = [
            'Future Perfect'    => Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tenses']),
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses']),
        ];

        $questions = [
            [
                'question' => 'I am reading the book very fast because it is very interesting. By the evening, I {a1} half of it.',
                'answer'   => 'will have read',
                'verb'     => 'read',
                'options'  => ['will have read', 'will read', 'will be reading', 'read'],
                'tense'    => 'Future Perfect',
            ],
            [
                'question' => 'Nina is going on vacation. She {a1} on the beach at this time next week.',
                'answer'   => 'will be lying',
                'verb'     => 'lie',
                'options'  => ['will be lying', 'will lie', 'will have lain', 'lies'],
                'tense'    => 'Future Continuous',
            ],
            [
                'question' => 'I usually have my lunch at work. At 1 pm today, I {a1} my lunch too and won’t be able to talk to you.',
                'answer'   => 'will be having',
                'verb'     => 'have',
                'options'  => ['will be having', 'will have had', 'have', 'am having'],
                'tense'    => 'Future Continuous',
            ],
            [
                'question' => 'John started the project last week. By next Friday, he {a1} it.',
                'answer'   => 'will have completed',
                'verb'     => 'complete',
                'options'  => ['will have completed', 'will complete', 'will be completing', 'completed'],
                'tense'    => 'Future Perfect',
            ],
            [
                'question' => 'Pete has been driving the whole day. By 10 pm tonight, he {a1} his destination.',
                'answer'   => 'will have reached',
                'verb'     => 'reach',
                'options'  => ['will have reached', 'will reach', 'will be reaching', 'reached'],
                'tense'    => 'Future Perfect',
            ],
            [
                'question' => 'The film starts at 7 pm. At this time tonight, I {a1} it.',
                'answer'   => 'will be watching',
                'verb'     => 'watch',
                'options'  => ['will be watching', 'will have watched', 'watch', 'am watching'],
                'tense'    => 'Future Continuous',
            ],
        ];

        $service = new QuestionSeedingService();
        $items   = [];

        foreach ($questions as $i => $q) {
            $index = $i + 1;
            $slug  = Str::slug(class_basename(self::class));
            $max   = 36 - strlen((string) $index) - 1;
            $uuid  = substr($slug, 0, $max) . '-' . $index;

            $items[] = [
                'uuid'        => $uuid,
                'question'    => $q['question'],
                'difficulty'  => 2,
                'category_id' => $categoryId,
                'flag'        => 0,
                'source_id'   => $sourceId,
                'tag_ids'     => [$detailTag->id, $tenseTags[$q['tense']]->id],
                'level'       => 'B2',
                'answers'     => [
                    ['marker' => 'a' . $index, 'answer' => $q['answer'], 'verb_hint' => $q['verb']],
                ],
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
