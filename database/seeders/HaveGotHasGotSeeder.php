<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class HaveGotHasGotSeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Вибери правильний варіант для заповнення пропуску.';

        $data = [
            [
                'question' => '{a1} a pen?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have you got']],
                'options' => ['Do you have got', 'Have you got', 'You have got'],
            ],
            [
                'question' => 'They {a1} any children.',
                'answers' => [['marker' => 'a1', 'answer' => "haven't got"]],
                'options' => ["don't have got", "don't got", "haven't got"],
            ],
            [
                'question' => 'A: "Have you got a car?" B: "Yes, I {a1}."',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ['have', 'do', "'ve got"],
            ],
            [
                'question' => 'When we were kids, we {a1} many toys.',
                'answers' => [['marker' => 'a1', 'answer' => "didn't have"]],
                'options' => ["hadn't got", "didn't have", "hadn't"],
            ],
            [
                'question' => 'He {a1} a very nice family.',
                'answers' => [['marker' => 'a1', 'answer' => "'s got"]],
                'options' => ["'s got", "have got", "'ve got"],
            ],
            [
                'question' => 'There is a door here. {a1} the key?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have you got']],
                'options' => ['Have you got', 'Do you got', 'Do you have got'],
            ],
            [
                'question' => 'They {a1} two cats and two dogs.',
                'answers' => [['marker' => 'a1', 'answer' => "'ve got"]],
                'options' => ['has got', "'ve", "'ve got"],
            ],
            [
                'question' => '{a1} a question?',
                'answers' => [['marker' => 'a1', 'answer' => 'Have you']],
                'options' => ['Have you', 'Do you have got', 'Do you have'],
            ],
            [
                'question' => 'I {a1} the answer to your question.',
                'answers' => [['marker' => 'a1', 'answer' => "haven't got"]],
                'options' => ["haven't", "don't have got", "haven't got"],
            ],
            [
                'question' => 'I {a1} a lot of money at the moment.',
                'answers' => [['marker' => 'a1', 'answer' => "don't have"]],
                'options' => ["don't have got", "don't have", "haven't"],
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
