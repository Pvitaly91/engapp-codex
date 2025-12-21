<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicWordsTestController extends Controller
{
    /**
     * Display the public words test page.
     */
    public function index()
    {
        return view('public.words-test');
    }

    /**
     * Fetch words for the test via AJAX.
     * Only returns words that have a Ukrainian translation.
     */
    public function fetchWords(Request $request): JsonResponse
    {
        $lang = $request->input('lang', 'uk');

        // Only get words that have a translation for the active language
        $words = Word::with(['translates' => fn ($q) => $q->where('lang', $lang), 'tags'])
            ->whereHas('translates', fn ($q) => $q->where('lang', $lang))
            ->get();

        // Shuffle and prepare the questions
        $shuffled = $words->shuffle();

        $questions = $shuffled->map(function ($word) use ($shuffled, $lang) {
            $translation = $word->translates->first()?->translation ?? '';
            $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

            // Get more other words to ensure we have enough valid options
            $otherWords = $shuffled->where('id', '!=', $word->id)
                ->take(15)
                ->values();

            if ($questionType === 'en_to_uk') {
                $correctAnswer = $translation;
                $options = $otherWords->map(fn ($w) => $w->translates->first()?->translation ?? '')->filter()->toArray();
            } else {
                $correctAnswer = $word->word;
                $options = $otherWords->pluck('word')->filter()->toArray();
            }

            // Ensure we have up to 4 options total (pad if needed, with safety counter)
            $options = array_slice(array_unique(array_filter($options)), 0, 4);
            $maxAttempts = 50;
            $attempts = 0;
            while (count($options) < 4 && $attempts < $maxAttempts && $shuffled->count() > 1) {
                $attempts++;
                $randomWord = $shuffled->random();
                $newOption = $questionType === 'en_to_uk'
                    ? ($randomWord->translates->first()?->translation ?? '')
                    : $randomWord->word;
                if ($newOption && ! in_array($newOption, $options) && $newOption !== $correctAnswer) {
                    $options[] = $newOption;
                }
            }

            // Add correct answer and shuffle
            $options[] = $correctAnswer;
            $options = array_unique(array_filter($options));
            shuffle($options);

            return [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => $translation,
                'questionType' => $questionType,
                'correctAnswer' => $correctAnswer,
                'options' => array_values($options),
                'tags' => $word->tags->pluck('name')->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'questions' => $questions,
            'total' => count($questions),
        ]);
    }

    /**
     * Check the answer via AJAX.
     */
    public function checkAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'word_id' => 'required|exists:words,id',
            'answer' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $lang = $request->input('lang', 'uk');
        $word = Word::with(['translates' => fn ($q) => $q->where('lang', $lang)])->findOrFail($request->input('word_id'));

        $correctAnswer = $request->input('questionType') === 'en_to_uk'
            ? ($word->translates->first()?->translation ?? '')
            : $word->word;

        $isCorrect = mb_strtolower(trim($request->input('answer'))) === mb_strtolower(trim($correctAnswer));

        return response()->json([
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correctAnswer,
            'userAnswer' => $request->input('answer'),
            'word' => $word->word,
        ]);
    }
}
