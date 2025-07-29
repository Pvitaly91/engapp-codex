<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class CanCantAbilityExercise2Seeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = "Write affirmative (+) and negative (-) sentences using can, can't with the words below.";

        $data = [
            [
                'question' => 'She / dance / very well. ⇒ She {a1}. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can dance']],
                'options' => ['can dance', "can\'t dance", 'dances', 'dance'],
            ],
            [
                'question' => 'I / finish / the composition for tomorrow. ⇒ I {a1} for tomorrow. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t finish"]],
                'options' => ['can finish', "can\'t finish", "won\'t finish", "don\'t finish"],
            ],
            [
                'question' => 'You / park / your car here. ⇒ You {a1} here. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t park"]],
                'options' => ['can park', "can\'t park", "don\'t park", "won\'t park"],
            ],
            [
                'question' => 'Jim / come / to the concert with us. ⇒ Jim {a1} with us. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can come']],
                'options' => ['can come', "can\'t come", 'comes', 'come'],
            ],
            [
                'question' => 'She / meet / you at 7. ⇒ She {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t meet"]],
                'options' => ['can meet', "can\'t meet", 'meet', 'meets'],
            ],
            [
                'question' => 'You / use / this app to view documents. ⇒ You {a1} to view documents. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can use']],
                'options' => ['can use', "can\'t use", 'use', 'uses'],
            ],
            [
                'question' => 'We / lose / this match. ⇒ We {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t lose"]],
                'options' => ['can lose', "can\'t lose", 'lose', 'loses'],
            ],
            [
                'question' => 'They / understand / my decision. ⇒ They {a1}. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t understand"]],
                'options' => ['can understand', "can\'t understand", 'understand', 'understands'],
            ],
            [
                'question' => 'She / stay / at my house tonight. ⇒ She {a1} tonight. (+)',
                'answers' => [['marker' => 'a1', 'answer' => 'can stay']],
                'options' => ['can stay', "can\'t stay", 'stay', 'stays'],
            ],
            [
                'question' => 'You / see / the sea from the balcony. ⇒ You {a1} from the balcony. (-)',
                'answers' => [['marker' => 'a1', 'answer' => "can\'t see"]],
                'options' => ['can see', "can\'t see", 'see', 'sees'],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 2,
                'source'      => $source,
                'flag'        => 0,
            ]);
            foreach ($d['answers'] as $ans) {
                QuestionAnswer::create([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                ]);
            }
            foreach ($d['options'] as $opt) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ]);
            }
        }
    }
}
