<?php
namespace Database\Seeders;

use App\Support\Database\Seeder;
use App\Models\Question;
use App\Models\Tag;

class QuestionTenseAssignmentSeeder extends Seeder
{
    public function run()
    {
        $map = [
            'past' => 'Past Simple',
            'present' => 'Present Simple',
            'present continuous' => 'Present Continuous',
            'future' => 'Future Simple',
            'present perfect' => 'Present Perfect',
        ];

        $questions = Question::with(['category', 'answers.option'])->get();
        foreach ($questions as $q) {
            $tagName = null;
            $catName = strtolower($q->category->name ?? '');
            if (isset($map[$catName])) {
                $tagName = $map[$catName];
            } else {
                $answersText = strtolower(implode(' ', $q->answers->map(fn($a) => $a->option->option)->toArray()));
                if (preg_match('/\\b(am|is|are)\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Present Continuous';
                } elseif (preg_match('/\\b(was|were)\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Past Continuous';
                } elseif (preg_match('/\\bwill\\s+be\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Future Continuous';
                } elseif (preg_match('/\\b(has|have)\\s+been\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Present Perfect Continuous';
                } elseif (preg_match('/\\bhad\\s+been\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Past Perfect Continuous';
                } elseif (preg_match('/\\bwill\\s+have\\s+been\\s+\\w+ing\\b/', $answersText)) {
                    $tagName = 'Future Perfect Continuous';
                } elseif (preg_match('/\\b(has|have)\\s+\\w+(ed|en|n)\\b/', $answersText)) {
                    $tagName = 'Present Perfect';
                } elseif (preg_match('/\\bhad\\s+\\w+(ed|en|n)\\b/', $answersText)) {
                    $tagName = 'Past Perfect';
                } elseif (preg_match('/\\bwill\\s+have\\s+\\w+(ed|en|n)\\b/', $answersText)) {
                    $tagName = 'Future Perfect';
                }
            }

            if (!$tagName) {
                // Fallback to Present Simple if nothing matched
                $tagName = 'Present Simple';
            }

            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $q->tags()->syncWithoutDetaching([$tag->id]);
        }
    }
}
