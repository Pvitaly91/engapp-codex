<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Category;
use App\Models\Source;

class ThisThatTheseThoseExercise3Seeder extends Seeder
{
    private function attachOption(Question $question, string $value, ?int $flag = null)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);
        $exists = $question->options()
            ->wherePivot('option_id', $option->id)
            ->wherePivot('flag', $flag)
            ->exists();
        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => $flag]);
        }
        return $option;
    }

    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source = 'Transform singular sentences into plural sentences and plural sentences into singular sentences using this, that, these, those and other missing words.';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;

        $data = [
            [
                'question' => 'This is a flower. ⇒ {a1} are flowers.',
                'answers' => [['marker' => 'a1', 'answer' => 'These']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'That is a box. ⇒ {a1} are boxes.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'That is my father. ⇒ {a1} are my parents.',
                'answers' => [['marker' => 'a1', 'answer' => 'Those']],
                'options' => ['These', 'Those', 'This', 'That'],
            ],
            [
                'question' => 'Are those your friends? ⇒ {a1} your friend?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is that']],
                'options' => ['Is this', 'Is that', 'Are these', 'Are those'],
            ],
            [
                'question' => 'What are those things? ⇒ What {a1} thing?',
                'answers' => [['marker' => 'a1', 'answer' => 'is that']],
                'options' => ['is this', 'is that', 'are these', 'are those'],
            ],
            [
                'question' => 'These documents are important. ⇒ {a1} important.',
                'answers' => [['marker' => 'a1', 'answer' => 'This document is']],
                'options' => ['This document is', 'That document is', 'These documents are', 'Those documents are'],
            ],
            [
                'question' => 'Are these your shoes? ⇒ {a1} your shoe?',
                'answers' => [['marker' => 'a1', 'answer' => 'Is this']],
                'options' => ['Is this', 'Is that', 'Are these', 'Are those'],
            ],
            [
                'question' => "That's a good car. ⇒ {a1} good cars.",
                'answers' => [['marker' => 'a1', 'answer' => 'Those are']],
                'options' => ['Those are', 'These are', 'This is', 'That is'],
            ],
            [
                'question' => 'This is John. ⇒ {a1} John and Carla.',
                'answers' => [['marker' => 'a1', 'answer' => 'These are']],
                'options' => ['These are', 'Those are', 'This is', 'That is'],
            ],
            [
                'question' => 'These are cheap computers. ⇒ {a1} a cheap computer.',
                'answers' => [['marker' => 'a1', 'answer' => 'This is']],
                'options' => ['This is', 'That is', 'These are', 'Those are'],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 2,
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
            }
            foreach ($d['options'] as $opt) {
                $this->attachOption($q, $opt);
            }
        }
    }
}
