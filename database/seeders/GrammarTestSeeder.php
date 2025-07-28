<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;
use App\Models\VerbHint;
use Illuminate\Support\Facades\DB;

class GrammarTestSeeder extends Seeder
{
    private function attachOption(Question $question, string $value, ?int $flag = null)
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);

        $exists = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where(function ($query) use ($flag) {
                if ($flag === null) {
                    $query->whereNull('flag');
                } else {
                    $query->where('flag', $flag);
                }
            })
            ->exists();

        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => $flag]);
        }

        return $option;
    }

    public function run(): void
    {
        $cats = [
            'past'    => Category::firstOrCreate(['name' => 'past']),
            'present' => Category::firstOrCreate(['name' => 'present']),
            'future'  => Category::firstOrCreate(['name' => 'future']),
        ];

        $questions = [
            [
                'question' => 'Walter {a1} to school last week.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['go', 'goes', 'went', 'will go', 'gone'],
                'answers' => [
                    'a1' => ['answer' => 'went', 'verb_hint' => 'Past simple: use the second form of the verb'],
                ],
            ],
            [
                'question' => 'Why do you always {a1} in front of the TV?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['slept', 'sleep', 'sleeps', 'will sleep', 'sleeping'],
                'answers' => [
                    'a1' => ['answer' => 'sleep', 'verb_hint' => 'Present simple: base verb after do/does'],
                ],
            ],
            [
                'question' => 'What will you {a1} next year?',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['did', 'do', 'done', 'does', 'doing'],
                'answers' => [
                    'a1' => ['answer' => 'do', 'verb_hint' => 'Future simple: will + base verb'],
                ],
            ],
            [
                'question' => 'We will not {a1} our anniversary in 2050.',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['celebrate', 'celebrated', 'celebrates', 'celebrating', 'have celebrated'],
                'answers' => [
                    'a1' => ['answer' => 'celebrate', 'verb_hint' => 'Future negative: will not + base verb'],
                ],
            ],
            [
                'question' => 'Tom {a1} at home on Mondays.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['stay', 'stays', 'stayed', 'staying', 'will stay'],
                'answers' => [
                    'a1' => ['answer' => 'stays', 'verb_hint' => 'Present simple: he/she/it takes -s'],
                ],
            ],
            [
                'question' => 'Where do you often {a1}?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['sat', 'sit', 'sitting', 'sits', 'will sit'],
                'answers' => [
                    'a1' => ['answer' => 'sit', 'verb_hint' => 'Question: use base verb after do'],
                ],
            ],
            [
                'question' => 'How much money does your father {a1} every month?',
                'difficulty' => 3,
                'category' => 'present',
                'options' => ['earns', 'earned', 'earning', 'earn', 'will earn'],
                'answers' => [
                    'a1' => ['answer' => 'earn', 'verb_hint' => 'Use base verb after does'],
                ],
            ],
            [
                'question' => 'Yesterday we did not {a1} birds.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['watch', 'watched', 'watches', 'watching', 'will watch'],
                'answers' => [
                    'a1' => ['answer' => 'watch', 'verb_hint' => 'Past negative: did not + base verb'],
                ],
            ],
            [
                'question' => 'Where {a1} you two days ago?',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['was', 'were', 'are', 'is', 'will be'],
                'answers' => [
                    'a1' => ['answer' => 'were', 'verb_hint' => "Past of 'be' is was/were"],
                ],
            ],
            [
                'question' => 'Who {a1} behind the curtains last night?',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['stand', 'stood', 'stands', 'standing', 'will stand'],
                'answers' => [
                    'a1' => ['answer' => 'stood', 'verb_hint' => 'Past simple form of stand'],
                ],
            ],
            [
                'question' => 'They rarely {a1} some milk in the supermarket.',
                'difficulty' => 3,
                'category' => 'present',
                'options' => ['buy', 'bought', 'buys', 'will buy', 'buying'],
                'answers' => [
                    'a1' => ['answer' => 'buy', 'verb_hint' => 'Present simple with adverbs of frequency'],
                ],
            ],
            [
                'question' => 'When did you {a1} your best friend the last time?',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['met', 'meet', 'meets', 'meeting', 'will meet'],
                'answers' => [
                    'a1' => ['answer' => 'meet', 'verb_hint' => 'Past question: did + subject + base verb'],
                ],
            ],
            [
                'question' => 'My classmate always {a1} books.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['read', 'reads', 'reading', 'readed', 'will read'],
                'answers' => [
                    'a1' => ['answer' => 'reads', 'verb_hint' => 'Present simple: he/she/it takes -s'],
                ],
            ],
            [
                'question' => 'What would you {a1}, if your brother {a2} your money?',
                'difficulty' => 5,
                'category' => 'present',
                'options' => ['think', 'thinks', 'thought', 'will think', 'thinking', 'stole', 'steals', 'steal', 'will steal', 'stolen'],
                'answers' => [
                    'a1' => ['answer' => 'think', 'verb_hint' => 'Conditional: would + base verb'],
                    'a2' => ['answer' => 'stole', 'verb_hint' => 'If-clause uses past simple'],
                ],
            ],
            [
                'question' => 'Why {a1} you here now?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['was', 'were', 'are', 'is', 'will be'],
                'answers' => [
                    'a1' => ['answer' => 'are', 'verb_hint' => "Use present form of 'be'"],
                ],
            ],
            [
                'question' => 'I did not {a1} you because I was listening to music.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['hear', 'heard', 'hears', 'hearing', 'will hear'],
                'answers' => [
                    'a1' => ['answer' => 'hear', 'verb_hint' => 'Past negative: did not + base verb'],
                ],
            ],
            [
                'question' => 'We will {a1} your apartment after you had a party there.',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['clean', 'cleaned', 'cleans', 'cleaning', 'will clean'],
                'answers' => [
                    'a1' => ['answer' => 'clean', 'verb_hint' => 'Future simple: will + base verb'],
                ],
            ],
            [
                'question' => 'Last night we could not {a1} the smoke.',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['stand', 'stood', 'stands', 'standing', 'will stand'],
                'answers' => [
                    'a1' => ['answer' => 'stand', 'verb_hint' => 'Modal could not + base verb'],
                ],
            ],
            [
                'question' => 'Why do you always {a1} about me?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['talk', 'talks', 'talked', 'talking', 'will talk'],
                'answers' => [
                    'a1' => ['answer' => 'talk', 'verb_hint' => 'Question: use base verb after do'],
                ],
            ],
            [
                'question' => 'If you {a1}, I will {a2} you.',
                'difficulty' => 4,
                'category' => 'present',
                'options' => ['leave', 'leaves', 'left', 'leaving', 'miss', 'misses', 'missed', 'missing', 'will miss'],
                'answers' => [
                    'a1' => ['answer' => 'leave', 'verb_hint' => 'If-clause: present simple'],
                    'a2' => ['answer' => 'miss', 'verb_hint' => 'Main clause: will + base verb'],
                ],
            ],
            [
                'question' => 'We do not {a1} during lessons.',
                'difficulty' => 1,
                'category' => 'present',
                'options' => ['eat', 'eats', 'ate', 'eating', 'will eat'],
                'answers' => [
                    'a1' => ['answer' => 'eat', 'verb_hint' => 'Present negative: do not + base verb'],
                ],
            ],
            [
                'question' => 'My sister {a1} the plants every day.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['waters', 'watered', 'water', 'watering', 'will water'],
                'answers' => [
                    'a1' => ['answer' => 'waters', 'verb_hint' => 'Present simple: he/she/it takes -s'],
                ],
            ],
            [
                'question' => 'Last Christmas I did not {a1} any presents.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['get', 'gets', 'got', 'getting', 'will get'],
                'answers' => [
                    'a1' => ['answer' => 'get', 'verb_hint' => 'Past negative: did not + base verb'],
                ],
            ],
            [
                'question' => 'In the evenings my parents do not {a1} cards.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['play', 'played', 'plays', 'playing', 'will play'],
                'answers' => [
                    'a1' => ['answer' => 'play', 'verb_hint' => 'Present negative: do not + base verb'],
                ],
            ],
            [
                'question' => 'Do I {a1} you? I haven’t met you before.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['know', 'knows', 'knew', 'knowing', 'will know'],
                'answers' => [
                    'a1' => ['answer' => 'know', 'verb_hint' => 'Question: use base verb after do'],
                ],
            ],
            [
                'question' => 'Please, {a1} me.',
                'difficulty' => 1,
                'category' => 'present',
                'options' => ['help', 'helps', 'helped', 'helping', 'will help'],
                'answers' => [
                    'a1' => ['answer' => 'help', 'verb_hint' => 'Imperative: base verb only'],
                ],
            ],
        ];

        foreach ($questions as $i => $q) {
            $question = Question::create([
                'uuid' => Str::slug(class_basename(self::class)) . '-' . ($i + 1),
                'question' => $q['question'],
                'difficulty' => $q['difficulty'],
                'category_id' => $cats[$q['category']]->id,
            ]);
            foreach ($q['options'] as $option) {
                $this->attachOption($question, $option);
            }
            foreach ($q['answers'] as $marker => $answerData) {
                $opt = $this->attachOption($question, $answerData['answer']);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $question->id,
                    'marker' => $marker,
                    'option_id' => $opt->id,
                ]);
                if (!empty($answerData['verb_hint'])) {
                    $hintOpt = $this->attachOption($question, $answerData['verb_hint'], 1);
                    VerbHint::firstOrCreate([
                        'question_id' => $question->id,
                        'marker' => $marker,
                        'option_id' => $hintOpt->id,
                    ]);
                }
            }
        }
    }
}
