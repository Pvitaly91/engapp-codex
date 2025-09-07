<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\QuestionSeedingService;
use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use Illuminate\Support\Str;

class FutureSimpleFutureContinuousFuturePerfectTestSeeder extends Seeder
{
    public function run()
    {
        $categoryId = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId   = Source::firstOrCreate(['name' => 'Future simple, future continuous, future perfect'])->id;

        $detailTag = Tag::firstOrCreate(
            ['name' => 'future_simple_future_continuous_future_perfect'],
            ['category' => 'Details']
        );

        $tenseTags = [
            'Future Simple'     => Tag::firstOrCreate(['name' => 'Future Simple'], ['category' => 'Tenses']),
            'Future Continuous' => Tag::firstOrCreate(['name' => 'Future Continuous'], ['category' => 'Tenses']),
            'Future Perfect'    => Tag::firstOrCreate(['name' => 'Future Perfect'], ['category' => 'Tenses']),
        ];

        $questions = [
            [
                'question' => 'I suppose the concert {a1} about 6.',
                'answer'   => 'will finish',
                'verb'     => 'finish',
                'options'  => ['will finish', 'will be finishing', 'will have finished', 'finishes'],
                'tense'    => 'Future Simple',
                'level'    => 'A2',
            ],
            [
                'question' => 'I {a2} very sad if you do that.',
                'answer'   => 'will be',
                'verb'     => 'be',
                'options'  => ['will be', 'am going to be', 'will have been', 'will be being'],
                'tense'    => 'Future Simple',
                'level'    => 'A2',
            ],
            [
                'question' => 'I {a3} you everything when I go back.',
                'answer'   => 'will tell',
                'verb'     => 'tell',
                'options'  => ['will tell', 'will be telling', 'will have told', 'am telling'],
                'tense'    => 'Future Simple',
                'level'    => 'A2',
            ],
            [
                'question' => 'This time tomorrow I {a4} to France.',
                'answer'   => 'will be flying',
                'verb'     => 'fly',
                'options'  => ['will be flying', 'will fly', 'will have flown', 'fly'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
            ],
            [
                'question' => 'Next month we {a5} for 25 years.',
                'answer'   => 'will have been married',
                'verb'     => 'be married',
                'options'  => ['will have been married', 'will be married', 'will marry', 'are going to be married'],
                'tense'    => 'Future Perfect',
                'level'    => 'B2',
            ],
            [
                'question' => 'Tomorrow she {a6} a very important exam.',
                'answer'   => 'will be writing',
                'verb'     => 'write',
                'options'  => ['will be writing', 'will write', 'will have written', 'writes'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
            ],
            [
                'question' => "Don't phone me between 8 a.m. and 12 p.m. I {a7}.",
                'answer'   => 'will be working',
                'verb'     => 'work',
                'options'  => ['will be working', 'will work', 'will have worked', 'am working'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
            ],
            [
                'question' => 'John is very upset today. I {a8} to talk to him.',
                'answer'   => 'will try',
                'verb'     => 'try',
                'options'  => ['will try', 'will be trying', 'try', 'will have tried'],
                'tense'    => 'Future Simple',
                'level'    => 'A2',
            ],
            [
                'question' => 'By the end of the week he {a9} all his money.',
                'answer'   => 'will have spent',
                'verb'     => 'spend',
                'options'  => ['will have spent', 'will spend', 'will be spending', 'spends'],
                'tense'    => 'Future Perfect',
                'level'    => 'B2',
            ],
            [
                'question' => 'By the time you arrive, she {a10}.',
                'answer'   => 'will have gone',
                'verb'     => 'go',
                'options'  => ['will have gone', 'will go', 'will be going', 'goes'],
                'tense'    => 'Future Perfect',
                'level'    => 'B2',
            ],
            [
                'question' => 'This time next week I {a11} at the beach.',
                'answer'   => 'will be relaxing',
                'verb'     => 'relax',
                'options'  => ['will be relaxing', 'will relax', 'will have relaxed', 'relax'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
            ],
            [
                'question' => "I think she {a12} tomorrow's exam.",
                'answer'   => 'will pass',
                'verb'     => 'pass',
                'options'  => ['will pass', 'will have passed', 'will be passing', 'passes'],
                'tense'    => 'Future Simple',
                'level'    => 'A2',
            ],
            [
                'question' => 'I can visit you at 5. We {a13} the game by then.',
                'answer'   => 'will have finished',
                'verb'     => 'finish',
                'options'  => ['will have finished', 'will finish', 'will be finishing', 'finish'],
                'tense'    => 'Future Perfect',
                'level'    => 'B2',
            ],
            [
                'question' => "Sorry, but I can't come at 5. I {a14} football with my mates.",
                'answer'   => 'will be playing',
                'verb'     => 'play',
                'options'  => ['will be playing', 'will play', 'will have played', 'play'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
            ],
            [
                'question' => 'In half an hour everybody {a15} the film.',
                'answer'   => 'will be watching',
                'verb'     => 'watch',
                'options'  => ['will be watching', 'will watch', 'will have watched', 'watches'],
                'tense'    => 'Future Continuous',
                'level'    => 'B1',
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
                'level'       => $q['level'],
                'answers'     => [
                    ['marker' => 'a' . $index, 'answer' => $q['answer'], 'verb_hint' => $q['verb']],
                ],
                'options'     => $q['options'],
            ];
        }

        $service->seed($items);
    }
}
