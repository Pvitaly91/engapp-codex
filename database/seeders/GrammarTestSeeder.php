<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionAnswer;

class GrammarTestSeeder extends Seeder
{
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
                    'a1' => ['answer' => 'went', 'verb_hint' => 'go'],
                ],
            ],
            [
                'question' => 'Why do you always {a1} in front of the TV?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['slept', 'sleep', 'sleeps', 'will sleep', 'sleeping'],
                'answers' => [
                    'a1' => ['answer' => 'sleep', 'verb_hint' => 'sleep'],
                ],
            ],
            [
                'question' => 'What will you {a1} next year?',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['did', 'do', 'done', 'does', 'doing'],
                'answers' => [
                    'a1' => ['answer' => 'do', 'verb_hint' => 'do'],
                ],
            ],
            [
                'question' => 'We will not {a1} our anniversary in 2050.',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['celebrate', 'celebrated', 'celebrates', 'celebrating', 'have celebrated'],
                'answers' => [
                    'a1' => ['answer' => 'celebrate', 'verb_hint' => 'not/celebrate'],
                ],
            ],
            [
                'question' => 'Tom {a1} at home on Mondays.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['stay', 'stays', 'stayed', 'staying', 'will stay'],
                'answers' => [
                    'a1' => ['answer' => 'stays', 'verb_hint' => 'stay'],
                ],
            ],
            [
                'question' => 'Where do you often {a1}?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['sat', 'sit', 'sitting', 'sits', 'will sit'],
                'answers' => [
                    'a1' => ['answer' => 'sit', 'verb_hint' => 'sit'],
                ],
            ],
            [
                'question' => 'How much money does your father {a1} every month?',
                'difficulty' => 3,
                'category' => 'present',
                'options' => ['earns', 'earned', 'earning', 'earn', 'will earn'],
                'answers' => [
                    'a1' => ['answer' => 'earn', 'verb_hint' => 'earn'],
                ],
            ],
            [
                'question' => 'Yesterday we did not {a1} birds.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['watch', 'watched', 'watches', 'watching', 'will watch'],
                'answers' => [
                    'a1' => ['answer' => 'watch', 'verb_hint' => 'not/watch'],
                ],
            ],
            [
                'question' => 'Where {a1} you two days ago?',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['was', 'were', 'are', 'is', 'will be'],
                'answers' => [
                    'a1' => ['answer' => 'were', 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'Who {a1} behind the curtains last night?',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['stand', 'stood', 'stands', 'standing', 'will stand'],
                'answers' => [
                    'a1' => ['answer' => 'stood', 'verb_hint' => 'stand'],
                ],
            ],
            [
                'question' => 'They rarely {a1} some milk in the supermarket.',
                'difficulty' => 3,
                'category' => 'present',
                'options' => ['buy', 'bought', 'buys', 'will buy', 'buying'],
                'answers' => [
                    'a1' => ['answer' => 'buy', 'verb_hint' => 'buy'],
                ],
            ],
            [
                'question' => 'When did you {a1} your best friend the last time?',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['met', 'meet', 'meets', 'meeting', 'will meet'],
                'answers' => [
                    'a1' => ['answer' => 'meet', 'verb_hint' => 'meet'],
                ],
            ],
            [
                'question' => 'My classmate always {a1} books.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['read', 'reads', 'reading', 'readed', 'will read'],
                'answers' => [
                    'a1' => ['answer' => 'reads', 'verb_hint' => 'read'],
                ],
            ],
            [
                'question' => 'What would you {a1}, if your brother {a2} your money?',
                'difficulty' => 5,
                'category' => 'present',
                'options' => ['think', 'thinks', 'thought', 'will think', 'thinking', 'stole', 'steals', 'steal', 'will steal', 'stolen'],
                'answers' => [
                    'a1' => ['answer' => 'think', 'verb_hint' => 'think'],
                    'a2' => ['answer' => 'stole', 'verb_hint' => 'steal'],
                ],
            ],
            [
                'question' => 'Why {a1} you here now?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['was', 'were', 'are', 'is', 'will be'],
                'answers' => [
                    'a1' => ['answer' => 'are', 'verb_hint' => 'be'],
                ],
            ],
            [
                'question' => 'I did not {a1} you because I was listening to music.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['hear', 'heard', 'hears', 'hearing', 'will hear'],
                'answers' => [
                    'a1' => ['answer' => 'hear', 'verb_hint' => 'not/hear'],
                ],
            ],
            [
                'question' => 'We will {a1} your apartment after you had a party there.',
                'difficulty' => 3,
                'category' => 'future',
                'options' => ['clean', 'cleaned', 'cleans', 'cleaning', 'will clean'],
                'answers' => [
                    'a1' => ['answer' => 'clean', 'verb_hint' => 'clean'],
                ],
            ],
            [
                'question' => 'Last night we could not {a1} the smoke.',
                'difficulty' => 3,
                'category' => 'past',
                'options' => ['stand', 'stood', 'stands', 'standing', 'will stand'],
                'answers' => [
                    'a1' => ['answer' => 'stand', 'verb_hint' => 'cannot/stand'],
                ],
            ],
            [
                'question' => 'Why do you always {a1} about me?',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['talk', 'talks', 'talked', 'talking', 'will talk'],
                'answers' => [
                    'a1' => ['answer' => 'talk', 'verb_hint' => 'talk'],
                ],
            ],
            [
                'question' => 'If you {a1}, I will {a2} you.',
                'difficulty' => 4,
                'category' => 'present',
                'options' => ['leave', 'leaves', 'left', 'leaving', 'miss', 'misses', 'missed', 'missing', 'will miss'],
                'answers' => [
                    'a1' => ['answer' => 'leave', 'verb_hint' => 'leave'],
                    'a2' => ['answer' => 'miss', 'verb_hint' => 'miss'],
                ],
            ],
            [
                'question' => 'We do not {a1} during lessons.',
                'difficulty' => 1,
                'category' => 'present',
                'options' => ['eat', 'eats', 'ate', 'eating', 'will eat'],
                'answers' => [
                    'a1' => ['answer' => 'eat', 'verb_hint' => 'not/eat'],
                ],
            ],
            [
                'question' => 'My sister {a1} the plants every day.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['waters', 'watered', 'water', 'watering', 'will water'],
                'answers' => [
                    'a1' => ['answer' => 'waters', 'verb_hint' => 'water'],
                ],
            ],
            [
                'question' => 'Last Christmas I did not {a1} any presents.',
                'difficulty' => 2,
                'category' => 'past',
                'options' => ['get', 'gets', 'got', 'getting', 'will get'],
                'answers' => [
                    'a1' => ['answer' => 'get', 'verb_hint' => 'not/get'],
                ],
            ],
            [
                'question' => 'In the evenings my parents do not {a1} cards.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['play', 'played', 'plays', 'playing', 'will play'],
                'answers' => [
                    'a1' => ['answer' => 'play', 'verb_hint' => 'not/play'],
                ],
            ],
            [
                'question' => 'Do I {a1} you? I haven’t met you before.',
                'difficulty' => 2,
                'category' => 'present',
                'options' => ['know', 'knows', 'knew', 'knowing', 'will know'],
                'answers' => [
                    'a1' => ['answer' => 'know', 'verb_hint' => 'know'],
                ],
            ],
            [
                'question' => 'Please, {a1} me.',
                'difficulty' => 1,
                'category' => 'present',
                'options' => ['help', 'helps', 'helped', 'helping', 'will help'],
                'answers' => [
                    'a1' => ['answer' => 'help', 'verb_hint' => 'help'],
                ],
            ],
        ];

        foreach ($questions as $q) {
            $question = Question::create([
                'question' => $q['question'],
                'difficulty' => $q['difficulty'],
                'category_id' => $cats[$q['category']]->id,
            ]);
            foreach ($q['options'] as $option) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option' => $option,
                ]);
            }
            foreach ($q['answers'] as $marker => $answerData) {
                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'marker' => $marker,
                    'answer' => $answerData['answer'],
                    'verb_hint' => $answerData['verb_hint'] ?? null,
                ]);
            }
        }
    }
}
