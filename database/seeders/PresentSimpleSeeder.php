<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\VerbHint;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresentSimpleSeeder extends Seeder
{
    private function attachOption(Question $question, string $value, ?int $flag = null)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);

        $exists = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where(function ($query) use ($flag) {
                if ($flag === null) {
                    $query->whereNull('flag');
                } else {
                    $query->where('flag', $flag);
                }
            })
            ->exists();

        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => $flag]);
        }

        return $option;
    }

    public function run()
    {
        $categoryId = 2; // ID категорії для Present Simple
        $sourceId = Source::firstOrCreate([
            'name' => 'Present simple. Complete the sentences with the correct variant.'
        ])->id;

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

        foreach ($data as $d) {
            $q = Question::create([
                'uuid'        => (string) Str::uuid(),
                'question'    => $d['question'],
                'category_id' => $categoryId,
                'difficulty'  => $d['difficulty'],
                'source_id'   => $d['source_id'],
                'flag'        => $d['flag'],
            ]);

            foreach ($d['answers'] as $ans) {
                $option = $this->attachOption($q, $ans['answer']);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);

                if (!empty($ans['verb_hint'])) {
                    $hintOption = $this->attachOption($q, $ans['verb_hint'], 1);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }

            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    $this->attachOption($q, $opt);
                }
            }
        }
    }
}
