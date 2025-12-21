<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublicWordsTestController extends Controller
{
    /**
     * Get words that have Ukrainian translations.
     */
    private function getWordsWithUkrainianTranslation(): Collection
    {
        return Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk'))
            ->get();
    }

    /**
     * Display the words test page.
     */
    public function index(): View
    {
        $words = $this->getWordsWithUkrainianTranslation();
        
        // Prepare word data for the test
        $wordData = $words->map(function ($word) {
            $translation = optional($word->translates->first())->translation ?? '';
            return [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => $translation,
            ];
        })->filter(fn ($item) => !empty($item['translation']))->values();

        // Get saved state from session
        $savedState = session('public_words_test_state');

        return view('words.public-test', [
            'wordData' => $wordData,
            'savedState' => $savedState,
        ]);
    }

    /**
     * Check the answer via AJAX.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'word_id' => 'required|exists:words,id',
            'answer' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->findOrFail($request->input('word_id'));

        $correct = $request->input('questionType') === 'en_to_uk'
            ? optional($word->translates->first())->translation ?? ''
            : $word->word;

        $isCorrect = mb_strtolower(trim($request->input('answer'))) === mb_strtolower(trim($correct));

        return response()->json([
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correct,
            'userAnswer' => $request->input('answer'),
            'word' => $word->word,
            'translation' => optional($word->translates->first())->translation ?? '',
        ]);
    }

    /**
     * Save test state via AJAX.
     */
    public function saveState(Request $request): JsonResponse
    {
        $state = $request->input('state');
        
        session(['public_words_test_state' => $state]);

        return response()->json(['success' => true]);
    }

    /**
     * Reset test progress.
     */
    public function reset(): JsonResponse
    {
        session()->forget('public_words_test_state');

        return response()->json(['success' => true]);
    }
}
