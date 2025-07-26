<?php 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\Category;
use App\Models\Source;

class ToBeTenseSeeder extends Seeder
{
    public function run()
    {
        // Категорії
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $cat_past = Category::firstOrCreate(['name' => 'past'])->id;
        $cat_future = Category::firstOrCreate(['name' => 'Future'])->id;
        $sourceId = Source::firstOrCreate(["name" => "To Be. Fill in the gaps with the verb 'to be' in Present, Past, Future forms."])->id;

        $data = [
            [
                'question' => 'Jordan and Alan {a1} basketball players. They {a2} tall.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'are', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'The pupils {a1} in the class now.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'There {a1} an interesting picture on the wall.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'I {a1} a sportsman when I grow up.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will be', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Julian {a1} five last year.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Dr. Jeremy {a1} a famous surgeon.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Yesterday we {a1} at the zoo.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'were', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Our class {a1} in Moscow next month.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will be', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'When my uncle {a1} young, he {a2} a singer.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'My friend {a1} at home now.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'She {a1} 18 in two days. She {a2} happy.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will be', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Where {a1} Lucy? She {a2} in the bathroom.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Will you {a1} at school tomorrow?',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'be', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'be', 'will be'],
            ],
            [
                'question' => 'Where {a1} you now? - I {a2} at the cinema.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'am', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Henry {a1} ill three weeks ago.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'The teacher {a1} in the classroom.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Where {a1} they yesterday? - They {a2} in the restaurant.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'were', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'were', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'I {a1} Mr. White. Nice to meet you!',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'am', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'This {a1} your apple. Take it!',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'There {a1} many books on the shelf.',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Where {a1} they from? - They {a2} from Wales.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'are', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Who {a1} you? - I {a2} Nick Green.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'am', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'What {a1} you? - I {a2} a bus-driver.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'are', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'am', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => '{a1} there any honey in the jar? - Yes, it {a2}.',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                    ['marker' => 'a2', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Why {a1} your brother here?',
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => "Don't phone him. He {a1} out.",
                'difficulty' => 2,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'What {a1} your favourite book?',
                'difficulty' => 1,
                'category_id' => $cat_present,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'is', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'My parents {a1} in Hawaii last month.',
                'difficulty' => 2,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'were', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'The boss {a1} here in a minute.',
                'difficulty' => 2,
                'category_id' => $cat_future,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'will be', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
            [
                'question' => 'Rosie {a1} at hairdresser\'s last week.',
                'difficulty' => 1,
                'category_id' => $cat_past,
                'source_id' => $sourceId,
                'flag' => 0,
                'answers' => [
                    ['marker' => 'a1', 'answer' => 'was', 'verb_hint' => 'be'],
                ],
                'options' => ['am', 'is', 'are', 'was', 'were', 'will be'],
            ],
        ];

        foreach ($data as $d) {
            $q = \App\Models\Question::create([
                'question'    => $d['question'],
                'category_id' => $d['category_id'],
                'difficulty'  => $d['difficulty'],
                'source_id'   => $d['source_id'],
                'flag'        => $d['flag'],
            ]);

            foreach ($d['answers'] as $ans) {
                \App\Models\QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'answer'      => $ans['answer'],
                    'verb_hint'   => $ans['verb_hint'] ?? null,
                ]);
            }

            if (!empty($d['options'])) {
                foreach ($d['options'] as $opt) {
                    \App\Models\QuestionOption::create([
                        'question_id' => $q->id,
                        'option'      => $opt,
                    ]);
                }
            }
        }
    }
}
