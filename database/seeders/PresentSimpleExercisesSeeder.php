<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Category;
use App\Models\Source;

class PresentSimpleExercisesSeeder extends Seeder
{
    public function run()
    {
        $cat_present = Category::firstOrCreate(['name' => 'present'])->id;
        $source1 = Source::firstOrCreate([
            'name' => 'Present Simple Exercises: Put the verbs in the correct form of the simple present.'
        ])->id;
        $source2 = Source::firstOrCreate([
            'name' => 'Present Simple Exercises: Match the verbs in the box with the sentences and rewrite them in the correct form.'
        ])->id;

        // SECTION 1
        $section1 = [
            [
                'question' => 'I {a1} cinnamon apple pie.',
                'answers' => [['marker' => 'a1', 'answer' => 'love', 'verb_hint' => 'love']],
                'options' => ['love', 'loves', 'am loving', 'loved'],
            ],
            [
                'question' => 'They {a1} many people in the village.',
                'answers' => [['marker' => 'a1', 'answer' => 'help', 'verb_hint' => 'help']],
                'options' => ['help', 'helps', 'helped', 'are helping'],
            ],
            [
                'question' => 'My brother {a1} nothing.',
                'answers' => [['marker' => 'a1', 'answer' => 'fears', 'verb_hint' => 'fear']],
                'options' => ['fears', 'fear', 'feared', 'is fearing'],
            ],
            [
                'question' => 'She {a1} tea with lemon.',
                'answers' => [['marker' => 'a1', 'answer' => 'drinks', 'verb_hint' => 'drink']],
                'options' => ['drinks', 'drink', 'is drinking', 'drank'],
            ],
            [
                'question' => 'He {a1} his children.',
                'answers' => [['marker' => 'a1', 'answer' => 'spoils', 'verb_hint' => 'spoil']],
                'options' => ['spoils', 'spoil', 'spoiled', 'is spoiling'],
            ],
            [
                'question' => 'I {a1} the housework for my mother.',
                'answers' => [['marker' => 'a1', 'answer' => 'do', 'verb_hint' => 'do']],
                'options' => ['do', 'does', 'did', 'done'],
            ],
            [
                'question' => 'My husband {a1} the laundry.',
                'answers' => [['marker' => 'a1', 'answer' => 'does', 'verb_hint' => 'do']],
                'options' => ['does', 'do', 'did', 'done'],
            ],
            [
                'question' => 'She {a1} to the Laundromat every week.',
                'answers' => [['marker' => 'a1', 'answer' => 'goes', 'verb_hint' => 'go']],
                'options' => ['goes', 'go', 'gone', 'going'],
            ],
            [
                'question' => 'We {a1} a cottage in the country.',
                'answers' => [['marker' => 'a1', 'answer' => 'own', 'verb_hint' => 'own']],
                'options' => ['own', 'owns', 'owned', 'are owning'],
            ],
            [
                'question' => 'The teacher often {a1} in the front of the room.',
                'answers' => [['marker' => 'a1', 'answer' => 'stands', 'verb_hint' => 'stand']],
                'options' => ['stands', 'stand', 'stood', 'is standing'],
            ],
            [
                'question' => 'It {a1} a lot in our town.',
                'answers' => [['marker' => 'a1', 'answer' => 'rains', 'verb_hint' => 'rain']],
                'options' => ['rains', 'rain', 'is raining', 'rained'],
            ],
            [
                'question' => 'It {a1} in New York City in the winter.',
                'answers' => [['marker' => 'a1', 'answer' => 'snows', 'verb_hint' => 'snow']],
                'options' => ['snows', 'snow', 'is snowing', 'snowed'],
            ],
            [
                'question' => 'Jessy {a1} downtown.',
                'answers' => [['marker' => 'a1', 'answer' => 'works', 'verb_hint' => 'work']],
                'options' => ['works', 'work', 'worked', 'is working'],
            ],
            [
                'question' => 'My uncle {a1} a restaurant.',
                'answers' => [['marker' => 'a1', 'answer' => 'manages', 'verb_hint' => 'manage']],
                'options' => ['manages', 'manage', 'managed', 'is managing'],
            ],
        ];

        // SECTION 2
        $section2 = [
            [
                'question' => 'She {a1} several foreign languages.',
                'answers' => [['marker' => 'a1', 'answer' => 'speaks', 'verb_hint' => 'speak']],
                'options' => ['speaks', 'speak', 'is speaking', 'spoken'],
            ],
            [
                'question' => 'My brother {a1} every night.',
                'answers' => [['marker' => 'a1', 'answer' => 'snores', 'verb_hint' => 'snore']],
                'options' => ['snores', 'snore', 'snored', 'is snoring'],
            ],
            [
                'question' => 'Jessica {a1} blue jeans every day.',
                'answers' => [['marker' => 'a1', 'answer' => 'wears', 'verb_hint' => 'wear']],
                'options' => ['wears', 'wear', 'wore', 'is wearing'],
            ],
            [
                'question' => 'Taylor {a1} at the library in the evening.',
                'answers' => [['marker' => 'a1', 'answer' => 'studies', 'verb_hint' => 'study']],
                'options' => ['studies', 'study', 'studied', 'is studying'],
            ],
            [
                'question' => 'The snow {a1} in the spring.',
                'answers' => [['marker' => 'a1', 'answer' => 'melts', 'verb_hint' => 'melt']],
                'options' => ['melts', 'melt', 'is melting', 'melted'],
            ],
            [
                'question' => 'Anna {a1} her baby in a carriage.',
                'answers' => [['marker' => 'a1', 'answer' => 'pushes', 'verb_hint' => 'push']],
                'options' => ['pushes', 'push', 'pushed', 'is pushing'],
            ],
            [
                'question' => 'Sandy always {a1} to class on time.',
                'answers' => [['marker' => 'a1', 'answer' => 'comes', 'verb_hint' => 'come']],
                'options' => ['comes', 'come', 'came', 'is coming'],
            ],
            [
                'question' => 'The boys {a1} the rules in class.',
                'answers' => [['marker' => 'a1', 'answer' => 'obey', 'verb_hint' => 'obey']],
                'options' => ['obey', 'obeys', 'obeyed', 'is obeying'],
            ],
            [
                'question' => 'The little kittens {a1} me everywhere.',
                'answers' => [['marker' => 'a1', 'answer' => 'follow', 'verb_hint' => 'follow']],
                'options' => ['follow', 'follows', 'followed', 'are following'],
            ],
            [
                'question' => 'You {a1} me money.',
                'answers' => [['marker' => 'a1', 'answer' => 'owe', 'verb_hint' => 'owe']],
                'options' => ['owe', 'owes', 'owed', 'are owing'],
            ],
            [
                'question' => 'Her father {a1} in the local post office.',
                'answers' => [['marker' => 'a1', 'answer' => 'works', 'verb_hint' => 'work']],
                'options' => ['works', 'work', 'worked', 'is working'],
            ],
            [
                'question' => 'My teacher {a1} everything.',
                'answers' => [['marker' => 'a1', 'answer' => 'explains', 'verb_hint' => 'explain']],
                'options' => ['explains', 'explain', 'explained', 'is explaining'],
            ],
            [
                'question' => 'Mary and Peter {a1} a lot of money.',
                'answers' => [['marker' => 'a1', 'answer' => 'earn', 'verb_hint' => 'earn']],
                'options' => ['earn', 'earns', 'earned', 'are earning'],
            ],
            [
                'question' => 'The little boy {a1} very high.',
                'answers' => [['marker' => 'a1', 'answer' => 'jumps', 'verb_hint' => 'jump']],
                'options' => ['jumps', 'jump', 'jumped', 'is jumping'],
            ],
            [
                'question' => 'Our grandmother {a1} raw onions.',
                'answers' => [['marker' => 'a1', 'answer' => 'eats', 'verb_hint' => 'eat']],
                'options' => ['eats', 'eat', 'ate', 'is eating'],
            ],
            [
                'question' => 'Carmen {a1} a lot of perfume.',
                'answers' => [['marker' => 'a1', 'answer' => 'buys', 'verb_hint' => 'buy']],
                'options' => ['buys', 'buy', 'bought', 'is buying'],
            ],
        ];

        foreach ($section1 as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 2,
                'source_id'   => $source1,
                'flag'        => 0,
            ]);
            foreach ($d['answers'] as $ans) {
                $option = QuestionOption::firstOrCreate([
                    'question_id' => $q->id,
                    'option'      => $ans['answer'],
                ]);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);
                if (!empty($ans['verb_hint'])) {
                    $hintOption = QuestionOption::firstOrCreate([
                        'question_id' => $q->id,
                        'option'      => $ans['verb_hint'],
                    ]);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }
            foreach ($d['options'] as $opt) {
                QuestionOption::firstOrCreate([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ]);
            }
        }

        foreach ($section2 as $d) {
            $q = Question::create([
                'question'    => $d['question'],
                'category_id' => $cat_present,
                'difficulty'  => 2,
                'source_id'   => $source2,
                'flag'        => 0,
            ]);
            foreach ($d['answers'] as $ans) {
                $option = QuestionOption::firstOrCreate([
                    'question_id' => $q->id,
                    'option'      => $ans['answer'],
                ]);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);
                if (!empty($ans['verb_hint'])) {
                    $hintOption = QuestionOption::firstOrCreate([
                        'question_id' => $q->id,
                        'option'      => $ans['verb_hint'],
                    ]);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }
            foreach ($d['options'] as $opt) {
                QuestionOption::firstOrCreate([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ]);
            }
        }
    }
}
