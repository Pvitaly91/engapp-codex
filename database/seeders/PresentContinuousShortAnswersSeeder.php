<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;

class PresentContinuousShortAnswersSeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Write questions and short answers in present continuous with the words in brackets. Use short forms when possible.';

        $data = [
            [
                'question' => 'A: {a1} (you/cry)? B: No, I {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are you crying', 'verb_hint' => 'cry'],
                    ['marker' => 'a2', 'answer' => "am not", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are you crying', 'Am I crying', 'Do you cry',
                    "am", "am not", "don\'t", "not"
                ],
            ],
            [
                'question' => 'A: {a1} (your father/recover) well after the operation? B: Yes, he {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Is your father recovering', 'verb_hint' => 'recover'],
                    ['marker' => 'a2', 'answer' => "is", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Is your father recovering', 'Does your father recover', 'Was your father recovering',
                    'is', 'isn\'t', 'does', 'doesn\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (they/try) to find a solution? B: Yes, they {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are they trying', 'verb_hint' => 'try'],
                    ['marker' => 'a2', 'answer' => "are", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are they trying', 'Do they try', 'Did they try',
                    'are', 'aren\'t', 'do', 'don\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (the baby/sleep)? B: No, she {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Is the baby sleeping', 'verb_hint' => 'sleep'],
                    ['marker' => 'a2', 'answer' => "isn\'t", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Is the baby sleeping', 'Does the baby sleep', 'Is she sleeping',
                    'is', 'isn\'t', 'does', 'is not'
                ],
            ],
            [
                'question' => 'A: {a1} (we/do) the right thing? B: Yes, we {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are we doing', 'verb_hint' => 'do'],
                    ['marker' => 'a2', 'answer' => "are", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are we doing', 'Do we do', 'Are we do',
                    'are', 'aren\'t', 'do', 'don\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (he/study) for his exams? B: No, he {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Is he studying', 'verb_hint' => 'study'],
                    ['marker' => 'a2', 'answer' => "isn\'t", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Is he studying', 'Does he study', 'Is he study',
                    'is', 'isn\'t', 'does', 'is not'
                ],
            ],
            [
                'question' => 'A: {a1} (you/eat) my pizza? B: Yes, I {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are you eating', 'verb_hint' => 'eat'],
                    ['marker' => 'a2', 'answer' => "am", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are you eating', 'Do you eat', 'Did you eat',
                    'am', 'am not', 'do', 'don\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (you/pay) by credit card? B: No, I {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are you paying', 'verb_hint' => 'pay'],
                    ['marker' => 'a2', 'answer' => "am not", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are you paying', 'Do you pay', 'Did you pay',
                    'am', 'am not', 'do', 'don\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (they/win) the match? B: Yes, they {a2}.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Are they winning', 'verb_hint' => 'win'],
                    ['marker' => 'a2', 'answer' => "are", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Are they winning', 'Do they win', 'Did they win',
                    'are', 'aren\'t', 'do', 'don\'t'
                ],
            ],
            [
                'question' => 'A: {a1} (Tom/run) in the race? B: No, he {a2}. He\'s here with me.',
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Is Tom running', 'verb_hint' => 'run'],
                    ['marker' => 'a2', 'answer' => "isn\'t", 'verb_hint' => 'be'],
                ],
                'options' => [
                    'Is Tom running', 'Does Tom run', 'Is he running',
                    'is', 'isn\'t', 'does', 'is not'
                ],
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
                    'verb_hint'   => $ans['verb_hint'] ?? null,
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
