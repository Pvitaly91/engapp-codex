<?php 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class HaveGotExercise3Seeder extends Seeder
{
    public function run()
    {
        $cat_past = Category::firstOrCreate(['name' => 'past'])->id;
        $source = "Change the following sentences or questions into the past tense.";

        $data = [
            [
                'question' => "He's got a girlfriend. ⇒ He {a1} a girlfriend.",
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["didn't have", "hadn't got", "wasn't having", "doesn't have"],
            ],
            [
                'question' => "They haven't got any problems. ⇒ They {a1} any problems.",
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["didn't have", "hadn't got", "haven't had", "don't have"],
            ],
            [
                'question' => "Have you got a pen? ⇒ {a1} a pen?",
                'answers' => [['marker' => 'a1', 'answer' => "Did you have"]],
                'options' => ["Did you have", "Had you got", "Have you had", "Did you got"],
            ],
            [
                'question' => "She hasn't got a sister. ⇒ She {a1} a sister.",
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["didn't have", "hadn't got", "doesn't have", "haven't got"],
            ],
            [
                'question' => "He's got an exam. ⇒ He {a1} an exam.",
                'answers' => [['marker' => 'a1', 'answer' => "had"]],
                'options' => ["had", "did have", "have had", "has had"],
            ],
            [
                'question' => "Has she got a cold? ⇒ {a1} a cold?",
                'answers' => [['marker' => 'a1', 'answer' => "Did she have"]],
                'options' => ["Did she have", "Had she got", "Did she got", "Was she have"],
            ],
            [
                'question' => "Have you got a headache? ⇒ {a1} a headache?",
                'answers' => [['marker' => 'a1', 'answer' => "Did you have"]],
                'options' => ["Did you have", "Had you got", "Have you had", "Were you have"],
            ],
            [
                'question' => "I haven't got time. ⇒ I {a1} time.",
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["didn't have", "hadn't got", "don't have", "haven't had"],
            ],
            [
                'question' => "She's got a nice smile. ⇒ She {a1} a nice smile.",
                'answers' => [['marker' => 'a1', 'answer' => "had"]],
                'options' => ["had", "did have", "has had", "was having"],
            ],
            [
                'question' => "He hasn't got much experience. ⇒ He {a1} much experience.",
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["didn't have", "hadn't got", "doesn't have", "wasn't having"],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_past,
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
