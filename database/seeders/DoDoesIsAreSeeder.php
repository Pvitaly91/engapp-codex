<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;

class DoDoesIsAreSeeder extends Seeder
{
    public function run()
    {
        $cat_present = 2; // Present Simple (заміни під свою структуру)
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
                'source'      => 'Do Does Am Is Are',
            ]);
            QuestionAnswer::create([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'answer'      => $data[1],
                'verb_hint'   => 'choose do/does/am/is/are',
            ]);
            foreach($options as $opt) {
                QuestionOption::create([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ]);
            }
        }
    }
}
