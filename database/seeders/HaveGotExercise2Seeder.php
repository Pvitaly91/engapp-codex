<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class HaveGotExercise2Seeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = "Transform these sentences using the verb have got like in the example below. Use short forms when possible: 've got, 's got, haven't got, hasn't got.";

        $data = [
            [
                'question' => 'I have a new computer. ⇒ I {a1} a new computer.',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ["'ve got", 'have', "haven't got", 'got'],
            ],
            [
                'question' => 'Do you have a new profile photo? ⇒ {a1} a new profile photo?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have you got']],
                'options' => ['Have you got', 'Do you got', 'Have you have'],
            ],
            [
                'question' => 'She has blonde hair and blue eyes. ⇒ She {a1} blonde hair and blue eyes.',
                'answers' => [['marker' => 'a1', 'answer' => "'s got"]],
                'options' => ["'s got", "has", "hasn't got"],
            ],
            [
                'question' => "I don't have a Twitter account. ⇒ I {a1} a Twitter account.",
                'answers' => [['marker' => 'a1', 'answer' => "haven't got"]],
                'options' => ["haven't got", "don't got", "haven't"],
            ],
            [
                'question' => "She doesn't have any money. ⇒ She {a1} any money.",
                'answers' => [['marker' => 'a1', 'answer' => "hasn't got"]],
                'options' => ["hasn't got", "doesn't have", "hasn't"],
            ],
            [
                'question' => 'A: Do you have a laptop? B: Yes, I do. ⇒ A: Have you got a laptop? B: Yes, I {a1}.',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ["'ve got", 'have', "haven't got"],
            ],
            [
                'question' => 'They have a lot of Youtube fans. ⇒ They {a1} a lot of Youtube fans.',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ["'ve got", "has got", 'have'],
            ],
            [
                'question' => "He doesn't have any interest in science. ⇒ He {a1} any interest in science.",
                'answers' => [['marker' => 'a1', 'answer' => "hasn't got"]],
                'options' => ["hasn't got", "doesn't have", "haven't got"],
            ],
            [
                'question' => 'Does she have a sister? ⇒ {a1} a sister?',
                'answers' => [['marker' => 'a1', 'answer' => 'Has she got']],
                'options' => ['Has she got', 'Does she got', 'Does she have got'],
            ],
            [
                'question' => 'I have a terrible headache. ⇒ I {a1} a terrible headache.',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ["'ve got", "have", "haven't got"],
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
