<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Question, Sentence, Word};

class WordsFromSentencesSeeder extends Seeder
{
    public function run(): void
    {
        $texts = array_merge(
            Question::pluck('question')->toArray(),
            Sentence::pluck('text_en')->toArray()
        );

        foreach ($texts as $text) {
            $clean = strtolower(preg_replace('/\{[^}]+\}/', '', $text));
            preg_match_all("/[a-zA-Z']+/",$clean, $matches);
            foreach ($matches[0] as $word) {
                Word::firstOrCreate(['word' => $word]);
            }
        }
    }
}
