<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{QuestionOption, Word};

class WordsFromOptionsSeeder extends Seeder
{
    public function run(): void
    {
        $options = QuestionOption::pluck('option');

        foreach ($options as $option) {
            $clean = strtolower($option);
            preg_match_all("/[a-zA-Z']+/", $clean, $matches);
            foreach ($matches[0] as $word) {
                Word::firstOrCreate(['word' => $word]);
            }
        }
    }
}
