<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Category;
use App\Models\Source;

class GrammarQuizPastSimpleSeeder extends Seeder
{
    private function attachOption(Question $question, string $value)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);
        $question->options()->syncWithoutDetaching($option->id);
        return $option;
    }

    public function run()
    {
        $cat_past = Category::firstOrCreate(['name' => 'past'])->id;
        $sourceId = Source::firstOrCreate(['name' => 'Grammar Quiz: Past Simple'])->id;

        $data = [
            [
                'question' => 'My family and I {a1} in London when I was young.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'lived', 'verb_hint' => 'live'],
                ],
                'options' => ['lived', 'lives', 'was living'],
            ],
            [
                'question' => 'We {a1} some sandwiches and fresh fruit to eat for lunch.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'bought', 'verb_hint' => 'buy'],
                ],
                'options' => ['buy', 'buys', 'bought'],
            ],
            [
                'question' => 'They wanted to {a1} a movie but there were no more tickets.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'see', 'verb_hint' => 'see'],
                ],
                'options' => ['see', 'saw', 'seeing'],
            ],
            [
                'question' => 'Did you have a good time? — Yes, I {a1}.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had', 'verb_hint' => 'have'],
                ],
                'options' => ['had', 'did', 'have'],
            ],
            [
                'question' => 'He didn’t {a1} me because I was behind the tree.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'see', 'verb_hint' => 'see'],
                ],
                'options' => ['saw', 'see', 'sees'],
            ],
            [
                'question' => '{a1} you a good student in school? — Yes, I was.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Were', 'verb_hint' => 'be'],
                ],
                'options' => ['Did', 'Were', 'Was'],
            ],
            [
                'question' => 'When did they {a1} back to their country?',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'fly', 'verb_hint' => 'fly'],
                ],
                'options' => ['flew', 'flies', 'fly'],
            ],
            [
                'question' => 'They {a1} back to their country after a few weeks.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'flew', 'verb_hint' => 'fly'],
                ],
                'options' => ['fly', 'flies', 'flew'],
            ],
            [
                'question' => 'Did you {a1} lots of interesting photos on your holiday?',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'take', 'verb_hint' => 'take'],
                ],
                'options' => ['took', 'take', 'takes'],
            ],
            [
                'question' => 'We had a great time and we {a1} lots of fun and exciting things.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'did', 'verb_hint' => 'do'],
                ],
                'options' => ['did', 'do', 'was did'],
            ],
            [
                'question' => 'Why {a1} you finish your math homework last week?',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => "didn't", 'verb_hint' => 'do'],
                ],
                'options' => ["didn't", "wasn't", "weren't"],
            ],
            [
                'question' => 'He {a1} see a dentist yesterday because he had a toothache.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'had to', 'verb_hint' => 'have to'],
                ],
                'options' => ['has to', 'have to', 'had to'],
            ],
            [
                'question' => 'I wanted to {a1}, but I couldn’t. I had to stay and help my friend.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'go', 'verb_hint' => 'go'],
                ],
                'options' => ['went', 'go', 'going'],
            ],
            [
                'question' => '{a1} they late or on time yesterday afternoon?',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Were', 'verb_hint' => 'be'],
                ],
                'options' => ['Were', 'Did', 'Was'],
            ],
            [
                'question' => 'She didn’t answer the phone because she {a1} at home.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'wasn\'t', 'verb_hint' => 'be'],
                ],
                'options' => ["didn't", "weren't", "wasn't"],
            ],
            [
                'question' => '{a1} they tired after the long trip? — Yes, they {a2}.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'Were', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'were', 'verb_hint' => 'be'],
                ],
                'options' => ['Were / were', 'Was / were', 'Were / was'],
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
            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    $this->attachOption($q, $opt);
                }
            }
        }
    }
}
