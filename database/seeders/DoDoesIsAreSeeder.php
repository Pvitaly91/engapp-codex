<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

class DoDoesIsAreSeeder extends Seeder
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

    public function run()
    {
        $cat_present = 2; // Present Simple (заміни під свою структуру)
        $sourceId = Source::firstOrCreate(['name' => 'Do Does Am Is Are'])->id;
        $options = ['do', 'does', 'am', 'is', 'are'];
        $questions = [
            ['What {a1} you like to eat for breakfast?',         'do'],
            ['Where {a1} my shoes?',                            'are'],
            ['{a1} your mom live in Aracaju?',                  'does'],
            ['{a1} your best friend live near your house?',      'does'],
            ['{a1} you at home now?',                            'are'],
            ['{a1} your sister older or younger than you?',      'is'],
            ['How old {a1} she?',                               'is'],
            ['{a1} she go to school in the morning or afternoon?', 'does'],
            ['{a1} you go to school in the morning or afternoon?', 'do'],
            ['{a1} you studying for your tests?',                'are'],
            ['What {a1} your favorite school subject?',          'is'],
            ['{a1} you have a nickname?',                        'do'],
            ['What {a1} it?',                                    'is'],
            ['{a1} your neighbor have a dog?',                   'does'],
            ['{a1} it raining now?',                             'is'],
            ['{a1} your parents at home?',                       'are'],
            ['How many times a month {a1} you go to your beach house?', 'do'],
            ['{a1} your sister like chocolate?',                 'does'],
            ['What {a1} her favorite kind of chocolate?',        'is'],
            ['{a1} you like chocolate?',                         'do'],
            ['What {a1} your favorite kind of chocolate?',       'is'],
            ['What {a1} you doing now?',                         'are'],
            ['What {a1} your mom doing now?',                    'is'],
            ['What time {a1} you usually go to bed?',            'do'],
        ];

        foreach ($questions as $data) {
            $q = Question::create([
                'question'    => $data[0],
                'difficulty'  => 1,
                'category_id' => $cat_present,
                'flag'        => 0,
                'source_id'   => $sourceId,
            ]);

            $answerOption = $this->attachOption($q, $data[1]);
            QuestionAnswer::firstOrCreate([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'option_id'   => $answerOption->id,
            ]);
            $hintOption = $this->attachOption($q, 'choose do/does/am/is/are', 1);
            VerbHint::firstOrCreate([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'option_id'   => $hintOption->id,
            ]);

            foreach($options as $opt) {
                $this->attachOption($q, $opt);
            }
        }
    }
}
