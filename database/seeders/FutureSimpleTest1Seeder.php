<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Category;
use App\Models\Source;

class FutureSimpleTest1Seeder extends Seeder
{
    public function run()
    {
        $cat_future = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'FutureSimple'])->id;

        $data = [
            [
                'question' => '{a1} a good plan?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "Won't it be", 'verb_hint' => 'be'],
                ],
                'options' => [
                    "Won't it be",
                    "Isn't it",
                    "Will it be",
                ],
            ],
            [
                'question' => 'Think what it {a1} to him.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will mean', 'verb_hint' => 'mean'],
                ],
                'options' => [
                    'means',
                    'mean',
                    'will mean',
                ],
            ],
            [
                'question' => 'I {a1} to you again in the morning.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will speak', 'verb_hint' => 'speak'],
                ],
                'options' => [
                    'speaks',
                    'will speak',
                    'am speaking',
                ],
            ],
            [
                'question' => 'I think you {a1} a child again.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will turn', 'verb_hint' => 'turn'],
                ],
                'options' => [
                    'will turn',
                    'turns',
                    'turned',
                ],
            ],
            [
                'question' => 'But I {a1} even with him.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will be', 'verb_hint' => 'be'],
                ],
                'options' => [
                    'will be',
                    'am',
                    'was',
                ],
            ],
            [
                'question' => 'They {a1} to one another.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't listen", 'verb_hint' => 'listen'],
                ],
                'options' => [
                    "won't listen",
                    "doesn't listen",
                    "didn't listen",
                ],
            ],
            [
                'question' => 'I {a1}, if I live.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't fail", 'verb_hint' => 'fail'],
                ],
                'options' => [
                    "won't fail",
                    "don't fail",
                    "didn't fail",
                ],
            ],
            [
                'question' => '{a1} for them again?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "Will they go and look", 'verb_hint' => 'go, look'],
                ],
                'options' => [
                    "Will they go and look",
                    "Do they go and look",
                    "Did they go and look",
                ],
            ],
            [
                'question' => 'I {a1} this kind of letter.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't answer", 'verb_hint' => 'answer'],
                ],
                'options' => [
                    "won't answer",
                    "don't answer",
                    "didn't answer",
                ],
            ],
            [
                'question' => 'I {a1} her in other ways.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will help', 'verb_hint' => 'help'],
                ],
                'options' => [
                    "will help",
                    "helped",
                    "helps",
                ],
            ],
            [
                'question' => 'Now we {a1} who he is.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will see', 'verb_hint' => 'see'],
                ],
                'options' => [
                    "will see",
                    "saw",
                    "see",
                ],
            ],
            [
                'question' => 'They {a1} you, but they know.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't tell", 'verb_hint' => 'tell'],
                ],
                'options' => [
                    "won't tell",
                    "don't tell",
                    "didn't tell",
                ],
            ],
            [
                'question' => 'But he {a1} my opinion first.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't ask", 'verb_hint' => 'ask'],
                ],
                'options' => [
                    "won't ask",
                    "didn't ask",
                    "doesn't ask",
                ],
            ],
            [
                'question' => 'I {a1} you some of mine.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will tell', 'verb_hint' => 'tell'],
                ],
                'options' => [
                    "will tell",
                    "told",
                    "telling",
                ],
            ],
            [
                'question' => 'Now {a1} me that number?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Will you give', 'verb_hint' => 'give'],
                ],
                'options' => [
                    "Will you give",
                    "Did you give",
                    "Do you give",
                ],
            ],
            [
                'question' => 'But I {a1} for that next time.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will come', 'verb_hint' => 'come'],
                ],
                'options' => [
                    "will come",
                    "come",
                    "came",
                ],
            ],
            [
                'question' => '{a1} me have a line?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Will you let', 'verb_hint' => 'let'],
                ],
                'options' => [
                    "Will you let",
                    "Did you let",
                    "Do you let",
                ],
            ],
            [
                'question' => 'He {a1} that he has lost a wife.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't know", 'verb_hint' => 'know'],
                ],
                'options' => [
                    "won't know",
                    "doesn't know",
                    "didn't know",
                ],
            ],
            [
                'question' => '{a1} of us?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "What will those people think", 'verb_hint' => 'think'],
                ],
                'options' => [
                    "What will those people think",
                    "What do those people think",
                    "What did those people think",
                ],
            ],
            [
                'question' => 'You {a1} it go far!',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "won't find", 'verb_hint' => 'find'],
                ],
                'options' => [
                    "won't find",
                    "don't find",
                    "didn't find",
                ],
            ],
        ];

        foreach ($data as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $d['category_id'],
                'difficulty'  => $d['difficulty'],
                'source_id'   => $d['source_id'],
                'flag'        => $d['flag'],
            ]);
            $optionIds = [];
            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    $optionIds[$opt] = QuestionOption::create([
                        'question_id' => $q->id,
                        'option'      => $opt,
                    ])->id;
                }
            }
            foreach ($d['answers'] as $ans) {
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $optionIds[$ans['answer']] ?? null,
                ]);
            }
        }
    }
}
