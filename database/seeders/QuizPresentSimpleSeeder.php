<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\Source;

class QuizPresentSimpleSeeder extends Seeder
{
    public function run()
    {
        $categoryId = 2; // Present Simple
        $source = 'Quiz – present simple. Вибери правильний варіант.';
        $sourceId = Source::firstOrCreate(['name' => $source])->id;

        $data = [
            [
                'question' => 'Ann usually {a1} shopping on Friday.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'goes', 'verb_hint' => 'go'],
                ],
                'options' => ['go', 'goes', 'jumps'],
            ],
            [
                'question' => 'Tom always {a1} after school.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'plays', 'verb_hint' => 'play'],
                ],
                'options' => ['plays', 'play', 'does'],
            ],
            [
                'question' => 'This girl sometimes {a1} her homework.',
                'difficulty' => 3,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'doesn’t do', 'verb_hint' => 'do'],
                ],
                'options' => ['don’t do', 'doesn’t does', 'doesn’t do'],
            ],
            [
                'question' => 'Lisa and Mike {a1} to the same school.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'go', 'verb_hint' => 'go'],
                ],
                'options' => ['listen', 'goes', 'go'],
            ],
            [
                'question' => 'Mary is a singer. She can {a1} beautiful songs.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'sing', 'verb_hint' => 'sing'],
                ],
                'options' => ['sing', 'sings', 'draw'],
            ],
            [
                'question' => 'Tom {a1} one book a week.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'reads', 'verb_hint' => 'read'],
                ],
                'options' => ['read', 'rides', 'reads'],
            ],
            [
                'question' => 'My rabbit always {a1} very fast.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'eats', 'verb_hint' => 'eat'],
                ],
                'options' => ['eats', 'writes', 'eat'],
            ],
            [
                'question' => 'These girls can {a1} very well. They are dancers.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'dance', 'verb_hint' => 'dance'],
                ],
                'options' => ['run', 'dances', 'dance'],
            ],
            [
                'question' => 'Martha {a1} four glasses of water a day.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'drinks', 'verb_hint' => 'drink'],
                ],
                'options' => ['drink', 'drinks', 'makes'],
            ],
            [
                'question' => 'Tom and his friends sometimes {a1} football.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'play', 'verb_hint' => 'play'],
                ],
                'options' => ['play', 'run', 'jump'],
            ],
            [
                'question' => 'Ellie {a1} one letter a week to her granny.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'writes', 'verb_hint' => 'write'],
                ],
                'options' => ['rides', 'write', 'writes'],
            ],
            [
                'question' => 'Peter {a1} to listen to music.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'likes', 'verb_hint' => 'like'],
                ],
                'options' => ['listens', 'likes', 'like'],
            ],
            [
                'question' => 'Paul and his friend {a1} English everyday.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'learn', 'verb_hint' => 'learn'],
                ],
                'options' => ['learn', 'learns', 'write'],
            ],
            [
                'question' => 'Jessika {a1} her new bike twice a week.',
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'rides', 'verb_hint' => 'ride'],
                ],
                'options' => ['writes', 'reads', 'rides'],
            ],
            [
                'question' => 'Michael {a1} got a blue parrot.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'has', 'verb_hint' => 'have'],
                ],
                'options' => ['have', 'haven’t', 'has'],
            ],
            [
                'question' => 'Harry {a1} the violin once a week.',
                'difficulty' => 2,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'plays', 'verb_hint' => 'play'],
                ],
                'options' => ['listens', 'plays', 'play'],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $categoryId,
                'difficulty'  => $d['difficulty'],
                'source_id'   => $d['source_id'],
                'flag'        => $d['flag'],
            ]);

            foreach ($d['answers'] as $ans) {
                QuestionAnswer::create([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                    'verb_hint'   => $ans['verb_hint'] ?? null,
                ]);
            }

            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'option'      => $opt,
                    ]);
                }
            }
        }
    }
}
