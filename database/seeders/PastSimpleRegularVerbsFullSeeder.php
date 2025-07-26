<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Source;

class PastSimpleRegularVerbsFullSeeder extends Seeder
{
    public function run()
    {
        $cat_past = 1;
        $source1 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs - 1'])->id;
        $source2 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs – Positive'])->id;
        $source3 = Source::firstOrCreate(['name' => 'Past Simple Regular Verbs – Negative'])->id;

        // A) Write the past forms of the regular verbs below.
        $verbs = [
            ['play', 'played'],
            ['study', 'studied'],
            ['wash', 'washed'],
            ['die', 'died'],
            ['paint', 'painted'],
            ['watch', 'watched'],
            ['try', 'tried'],
            ['start', 'started'],
            ['phone', 'phoned'],
            ['stay', 'stayed'],
            ['love', 'loved'],
            ['help', 'helped'],
            ['carry', 'carried'],
            ['walk', 'walked'],
            ['skip', 'skipped'],
            ['answer', 'answered'],
            ['enjoy', 'enjoyed'],
            ['cry', 'cried'],
            ['laugh', 'laughed'],
            ['invite', 'invited'],
            ['visit', 'visited'],
            ['move', 'moved'],
            ['tidy', 'tidied'],
            ['like', 'liked'],
        ];

        foreach ($verbs as $i => [$inf, $past]) {
            $q = Question::create([
                'question'    => ucfirst($inf) . ' → {a1} (past form)',
                'difficulty'  => 1,
                'category_id' => $cat_past,
                'flag'        => 0,
                'source_id'   => $source1,
            ]);
            $optionIds = [];
            foreach([$past, $inf] as $opt) {
                $optionIds[$opt] = QuestionOption::create([
                    'question_id' => $q->id,
                    'option'      => $opt,
                ])->id;
            }
            QuestionAnswer::firstOrCreate([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'option_id'   => $optionIds[$past],
            ]);
        }
        /*    
        // B) Rewrite these sentences making them positive.
        $positives = [
            "Robert didn’t wait for his friends this morning." => "Robert waited for his friends this morning.",
            "They didn’t listen to music at 11 p.m. last night." => "They listened to music at 11 p.m. last night.",
            "My mother didn’t clean the kitchen yesterday." => "My mother cleaned the kitchen yesterday.",
            "The students didn’t learn Japanese last year." => "The students learned Japanese last year.",
            "Mr. Smith didn’t arrive home before dinner." => "Mr. Smith arrived home before dinner.",
            "Her aunt didn’t cook the dinner on Tuesday." => "Her aunt cooked the dinner on Tuesday.",
        ];

        foreach ($positives as $neg => $pos) {
            $q = Question::create([
                'question'    => $neg . ' (positive form: {a1})',
                'difficulty'  => 2,
                'category_id' => $cat_past,
                'flag'        => 1,
                'source_id'   => $source2,
            ]);
            $opt = QuestionOption::create([
                'question_id' => $q->id,
                'option'      => $pos,
            ]);
            QuestionAnswer::firstOrCreate([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'option_id'   => $opt->id,
            ]);
        }

        // C) Rewrite these sentences making them negative.
        $negatives = [
            "Patricia watched a film at 10 p.m. last night." => "Patricia didn’t watch a film at 10 p.m. last night.",
            "I borrowed a book from the library last week." => "I didn’t borrow a book from the library last week.",
            "My classmates answered all the questions." => "My classmates didn’t answer all the questions.",
            "She talked to her English teacher five days ago." => "She didn’t talk to her English teacher five days ago.",
            "Mrs. Jackson repaired the chair last weekend." => "Mrs. Jackson didn’t repair the chair last weekend.",
            "Eric brushed his teeth before breakfast today." => "Eric didn’t brush his teeth before breakfast today.",
            "Debra and Rachel cycled to school yesterday." => "Debra and Rachel didn’t cycle to school yesterday.",
        ];

        foreach ($negatives as $pos => $neg) {
            $q = Question::create([
                'question'    => $pos . ' (negative form: {a1})',
                'difficulty'  => 2,
                'category_id' => $cat_past,
                'flag'        => 1,
                'source_id'   => $source3,
            ]);
            $opt = QuestionOption::create([
                'question_id' => $q->id,
                'option'      => $neg,
            ]);
            QuestionAnswer::firstOrCreate([
                'question_id' => $q->id,
                'marker'      => 'a1',
                'option_id'   => $opt->id,
            ]);
        }*/
    }
}
