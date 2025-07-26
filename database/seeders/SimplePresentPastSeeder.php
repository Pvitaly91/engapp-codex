<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;

class SimplePresentPastSeeder extends Seeder
{
    public function run()
    {
        // Категорія (створи/знайди): 2 = Present, 1 = Past
        $categoryPresent = 2;
        $categoryPast = 1;

        $questions = [
            [
                'question' => 'I always {a1} work in morning. It\'s relaxing.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'walk', 'verb_hint' => 'walk'],
                ],
                'options' => ['walk', 'walks', 'walked', 'walking'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Mark {a1} computers for three years before he graduated.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'studied', 'verb_hint' => 'study'],
                ],
                'options' => ['study', 'studies', 'studied', 'studying'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'They {a1} late for the party.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'did not arrive', 'verb_hint' => 'arrive / neg'],
                ],
                'options' => ['arrived', 'did not arrive', 'arrives', 'arriving'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'My parents {a1} in that church.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'married', 'verb_hint' => 'marry'],
                ],
                'options' => ['marry', 'married', 'marries', 'marrying'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Sorry! My classes {a1} 15 minutes late.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'end', 'verb_hint' => 'end'],
                ],
                'options' => ['end', 'ended', 'ends', 'ending'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'July {a1} for us.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'does not wait', 'verb_hint' => 'wait / neg'],
                ],
                'options' => ['waits', 'wait', 'does not wait', 'waited'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'You {a1} dogs. You have many.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'like', 'verb_hint' => 'like'],
                ],
                'options' => ['like', 'likes', 'liked', 'liking'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'I {a1} that book yesterday for the test.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'needed', 'verb_hint' => 'need'],
                ],
                'options' => ['need', 'needed', 'needs', 'needing'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'She {a1} my new dress last night.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'used', 'verb_hint' => 'use'],
                ],
                'options' => ['use', 'used', 'uses', 'using'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'The teacher {a1} the students after class. Now, he can\'t.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'helped', 'verb_hint' => 'help'],
                ],
                'options' => ['help', 'helped', 'helps', 'helping'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'It {a1} heavily last night.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'rained', 'verb_hint' => 'rain'],
                ],
                'options' => ['rains', 'rain', 'rained', 'raining'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'The police {a1} to the thief earlier.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'talked', 'verb_hint' => 'talk'],
                ],
                'options' => ['talk', 'talked', 'talks', 'talking'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'He {a1} the suitcase for me. It was too heavy.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'carried', 'verb_hint' => 'carry'],
                ],
                'options' => ['carry', 'carried', 'carries', 'carrying'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'The stores {a1} earlier today.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'closed', 'verb_hint' => 'close'],
                ],
                'options' => ['close', 'closed', 'closes', 'closing'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'The kids {a1} their homework.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not finish', 'verb_hint' => 'finish / neg'],
                ],
                'options' => ['finish', 'finishes', 'finished', 'do not finish'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'I {a1} some milk on the floor.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'dropped', 'verb_hint' => 'drop'],
                ],
                'options' => ['drop', 'dropped', 'drops', 'dropping'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'My kids {a1} video games every day, only on Sundays.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not play', 'verb_hint' => 'play / neg'],
                ],
                'options' => ['play', 'plays', 'played', 'do not play'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Sarah {a1} the box for him. It was difficult.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'opened', 'verb_hint' => 'open'],
                ],
                'options' => ['open', 'opened', 'opens', 'opening'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Patrick {a1} money to buy a house.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'saved', 'verb_hint' => 'save'],
                ],
                'options' => ['save', 'saved', 'saves', 'saving'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'My mother {a1} dinner when she arrives from work.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'cooks', 'verb_hint' => 'cook'],
                ],
                'options' => ['cook', 'cooks', 'cooked', 'cooking'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Last night I {a1} a noise in the garage.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'heard', 'verb_hint' => 'hear'],
                ],
                'options' => ['hear', 'hears', 'heard', 'hearing'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'I {a1} at night. I get home tired.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'do not cook', 'verb_hint' => 'cook / neg'],
                ],
                'options' => ['cook', 'cooks', 'do not cook', 'cooked'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'Renata {a1} to the gym yesterday morning.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'went', 'verb_hint' => 'go'],
                ],
                'options' => ['go', 'goes', 'went', 'going'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'The city {a1} crowded. Our event is a success.',
                'category_id' => $categoryPresent,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['is', 'are', 'was', 'were'],
                'source' => 'Simple Present x Simple Past',
            ],
            [
                'question' => 'We {a1} help the man. We were afraid.',
                'category_id' => $categoryPast,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'did not help', 'verb_hint' => 'help / neg'],
                ],
                'options' => ['help', 'helped', 'did not help', 'helps'],
                'source' => 'Simple Present x Simple Past',
            ],
        ];

        foreach ($questions as $data) {
            $q = Question::create([
                'question'    => $data['question'],
                'difficulty'  => 2, // Або оціни самостійно, тут для всіх 2
                'category_id' => $data['category_id'],
                'flag'        => 0,
                'source'      => $data['source'],
            ]);
            foreach ($data['answers'] as $ans) {
                QuestionAnswer::create([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                    'verb_hint'   => $ans['verb_hint'] ?? null,
                ]);
            }
            foreach ($data['options'] as $opt) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ]);
            }
        }
    }
}
