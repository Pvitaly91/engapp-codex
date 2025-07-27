<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

class PastSimpleRegularSeeder extends Seeder
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
        // Категорія Past Simple (1)
        $cat_past = 1;
        $sourceId = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs'])->id;

        $questions = [
            [
                'question' => 'We {a1} video games all evening yesterday.',
                'answers' => [['marker'=>'a1','answer'=>'played','verb_hint'=>'play']],
                'options' => ['played', 'plays', 'playing', 'play'],
            ],
            [
                'question' => 'They {a1} their new friends at their party.',
                'answers' => [['marker'=>'a1','answer'=>'invited','verb_hint'=>'invite']],
                'options' => ['invited', 'invites', 'invite', 'inviting'],
            ],
            [
                'question' => 'We {a1} to Adele’s latest CD which is really good.',
                'answers' => [['marker'=>'a1','answer'=>'listened','verb_hint'=>'listen']],
                'options' => ['listened', 'listens', 'listening', 'listen'],
            ],
            [
                'question' => 'I {a1} all the invitations to the wedding last Monday.',
                'answers' => [['marker'=>'a1','answer'=>'posted','verb_hint'=>'post']],
                'options' => ['posted', 'posts', 'posting', 'post'],
            ],
            [
                'question' => 'The neighbours {a1} new trees in their garden last weekend.',
                'answers' => [['marker'=>'a1','answer'=>'planted','verb_hint'=>'plant']],
                'options' => ['planted', 'plants', 'planting', 'plant'],
            ],
            [
                'question' => 'My cousin {a1} the Louvre museum when he was in Paris.',
                'answers' => [['marker'=>'a1','answer'=>'visited','verb_hint'=>'visit']],
                'options' => ['visited', 'visits', 'visiting', 'visit'],
            ],
            [
                'question' => 'Someone {a1} all the toilet paper before and here I am!!',
                'answers' => [['marker'=>'a1','answer'=>'used','verb_hint'=>'use']],
                'options' => ['used', 'uses', 'using', 'use'],
            ],
            [
                'question' => 'The British {a1} for Brexit in 2016.',
                'answers' => [['marker'=>'a1','answer'=>'voted','verb_hint'=>'vote']],
                'options' => ['voted', 'votes', 'voting', 'vote'],
            ],
            [
                'question' => 'My sister {a1} for the protection of the planet last Sunday.',
                'answers' => [['marker'=>'a1','answer'=>'demonstrated','verb_hint'=>'demonstrate']],
                'options' => ['demonstrated', 'demonstrates', 'demonstrating', 'demonstrate'],
            ],
            [
                'question' => 'We {a1} on the beach, but in the forest yesterday.',
                'answers' => [['marker'=>'a1','answer'=>'did not walk','verb_hint'=>'not walk']],
                'options' => ['did not walk', 'walked', 'walks', 'walking'],
            ],
            [
                'question' => 'Dad {a1} a very nice present for his birthday.',
                'answers' => [['marker'=>'a1','answer'=>'received','verb_hint'=>'receive']],
                'options' => ['received', 'receives', 'receiving', 'receive'],
            ],
            [
                'question' => 'My sister {a1} her car two days ago.',
                'answers' => [['marker'=>'a1','answer'=>'washed','verb_hint'=>'wash']],
                'options' => ['washed', 'washes', 'washing', 'wash'],
            ],
            [
                'question' => 'Sam {a1} his exam last June, now he is at university.',
                'answers' => [['marker'=>'a1','answer'=>'passed','verb_hint'=>'pass']],
                'options' => ['passed', 'passes', 'passing', 'pass'],
            ],
            [
                'question' => 'They {a1} all Saturday night!!',
                'answers' => [['marker'=>'a1','answer'=>'danced','verb_hint'=>'dance']],
                'options' => ['danced', 'dances', 'dancing', 'dance'],
            ],
            [
                'question' => 'I {a1} for my lost keys all day yesterday!',
                'answers' => [['marker'=>'a1','answer'=>'looked','verb_hint'=>'look']],
                'options' => ['looked', 'looks', 'looking', 'look'],
            ],
            [
                'question' => 'We really {a1} our trip to Italy last summer.',
                'answers' => [['marker'=>'a1','answer'=>'enjoyed','verb_hint'=>'enjoy']],
                'options' => ['enjoyed', 'enjoys', 'enjoying', 'enjoy'],
            ],
            [
                'question' => 'My Granddad {a1} in hospital for a long time last year.',
                'answers' => [['marker'=>'a1','answer'=>'stayed','verb_hint'=>'stay']],
                'options' => ['stayed', 'stays', 'staying', 'stay'],
            ],
            [
                'question' => 'I {a1} so much during that TV comedy show.',
                'answers' => [['marker'=>'a1','answer'=>'laughed','verb_hint'=>'laugh']],
                'options' => ['laughed', 'laughs', 'laughing', 'laugh'],
            ],
            [
                'question' => 'Christopher Columbus {a1} America.',
                'answers' => [['marker'=>'a1','answer'=>'discovered','verb_hint'=>'discover']],
                'options' => ['discovered', 'discovers', 'discovering', 'discover'],
            ],
            [
                'question' => 'We {a1} all our conversations for future use, just in case.',
                'answers' => [['marker'=>'a1','answer'=>'recorded','verb_hint'=>'record']],
                'options' => ['recorded', 'records', 'recording', 'record'],
            ],
            [
                'question' => 'The Earl of Sandwich {a1} the ... sandwich, of course!',
                'answers' => [['marker'=>'a1','answer'=>'invented','verb_hint'=>'invent']],
                'options' => ['invented', 'invents', 'inventing', 'invent'],
            ],
            [
                'question' => 'I {a1} after my baby brother while my parents were away.',
                'answers' => [['marker'=>'a1','answer'=>'looked','verb_hint'=>'look']],
                'options' => ['looked', 'looks', 'looking', 'look'],
            ],
            [
                'question' => 'They {a1} to go to New York but it was too expensive.',
                'answers' => [['marker'=>'a1','answer'=>'wanted','verb_hint'=>'want']],
                'options' => ['wanted', 'wants', 'wanting', 'want'],
            ],
            [
                'question' => 'She {a1} a good programme yesterday evening.',
                'answers' => [['marker'=>'a1','answer'=>'watched','verb_hint'=>'watch']],
                'options' => ['watched', 'watches', 'watching', 'watch'],
            ],
            [
                'question' => 'The Pilgrims {a1} the thirteen first colonies in the new world.',
                'answers' => [['marker'=>'a1','answer'=>'founded','verb_hint'=>'found']],
                'options' => ['founded', 'founds', 'founding', 'found'],
            ],
            [
                'question' => 'My brother {a1} the table everyday last week.',
                'answers' => [['marker'=>'a1','answer'=>'cleared','verb_hint'=>'clear']],
                'options' => ['cleared', 'clears', 'clearing', 'clear'],
            ],
            [
                'question' => 'Sandy {a1} for arriving too late this morning.',
                'answers' => [['marker'=>'a1','answer'=>'apologized','verb_hint'=>'apologize']],
                'options' => ['apologized', 'apologizes', 'apologizing', 'apologize'],
            ],
            [
                'question' => 'My teacher {a1} all the tests on Wednesday afternoon.',
                'answers' => [['marker'=>'a1','answer'=>'corrected','verb_hint'=>'correct']],
                'options' => ['corrected', 'corrects', 'correcting', 'correct'],
            ],
        ];

        foreach ($questions as $data) {
            $q = Question::create([
                'question'    => $data['question'],
                'difficulty'  => 2,
                'category_id' => $cat_past,
                'flag'        => 0,
                'source_id'   => $sourceId,
            ]);
            foreach ($data['answers'] as $ans) {
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
            foreach ($data['options'] as $opt) {
                $this->attachOption($q, $opt);
            }
        }
    }
}
