<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Word;

class WordScanningService
{
    /**
     * Scan questions and options for words and insert missing ones into words table.
     *
     * @return int Number of new words inserted.
     */
    public function scan(): int
    {
        // Collect all text from questions and options
        $texts = Question::pluck('question')
            ->merge(QuestionOption::pluck('option'));

        $words = [];
        foreach ($texts as $text) {
            $parts = preg_split('/[^\p{L}]+/u', mb_strtolower($text));
            foreach ($parts as $part) {
                if ($part !== '') {
                    $words[$part] = true;
                }
            }
        }

        $words = array_keys($words);
        if (empty($words)) {
            return 0;
        }

        $existing = Word::whereIn('word', $words)->pluck('word')->all();
        $newWords = array_diff($words, $existing);

        foreach ($newWords as $word) {
            Word::create(['word' => $word]);
        }

        return count($newWords);
    }
}
