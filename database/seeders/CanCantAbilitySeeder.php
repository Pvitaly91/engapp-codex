<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class CanCantAbilitySeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Choose the correct option to complete the sentences.';

        $data = [
            [
                'question' => '{a1} ask you a question?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can']],
                'options' => ['Can', "Can't"],
            ],
            [
                'question' => 'I {a1}. The music is too loud.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => '\'Can you play the piano?\' \'Yes, I {a1}.\'',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => 'He {a1} four languages.',
                'answers' => [['marker' => 'a1', 'answer' => 'can']],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => 'He says that he {a1} me.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => '{a1} a ham and cheese pizza, please?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can I have']],
                'options' => ['Can I have', "Can\'t I have"],
            ],
            [
                'question' => '\'Can I smoke here?\' \'No, you {a1}.\'',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => 'We {a1} use our phones in class.',
                'answers' => [['marker' => 'a1', 'answer' => "can't"]],
                'options' => ['can', "can\'t"],
            ],
            [
                'question' => 'He {a1} my car if he needs it.',
                'answers' => [['marker' => 'a1', 'answer' => 'can use']],
                'options' => ['can use', "can\'t use"],
            ],
            [
                'question' => '{a1} the window, please?',
                'answers' => [['marker' => 'a1', 'answer' => 'Can you open']],
                'options' => ['Can you open', "Can\'t you open"],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 1,
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
