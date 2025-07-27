<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Category;
use App\Models\Source;

class ThisThatTheseThoseSeeder extends Seeder
{
    private function attachOption(Question $question, string $value)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);
        $question->options()->syncWithoutDetaching($option->id);
        return $option;
    }

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Complete the sentences with this, that, these, those';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;

        $data = [
            [
                'question' => '{a1} are my trousers.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Hi, Chris. {a1} is my friend Jona. "Hi, Jona. Nice to meet you."',
                'answers' => [['marker' => 'a1', 'answer' => 'This']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Look at {a1} birds in the sky.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'What are {a1}? "They are my books."',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Is {a1} hotel nice?',
                'answers' => [['marker' => 'a1', 'answer' => 'That']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Are {a1} your friends?',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Who is {a1} man over there?',
                'answers' => [['marker' => 'a1', 'answer' => 'That']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Isn\'t {a1} your friend Erik?',
                'answers' => [['marker' => 'a1', 'answer' => 'This']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => 'Why are {a1} boxes here?',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
            [
                'question' => '{a1} are my glasses.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['This', 'That', 'These', 'Those'],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 1,
                'source_id'   => $sourceId,
                'flag'        => 0,
            ]);
            foreach ($d['answers'] as $ans) {
                $option = $this->attachOption($q, $ans['answer']);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);
                if (!empty($ans['verb_hint'])) {
                    $hintOption = $this->attachOption($q, $ans['verb_hint']);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }
            foreach ($d['options'] as $opt) {
                $this->attachOption($q, $opt);
            }
        }
    }
}
