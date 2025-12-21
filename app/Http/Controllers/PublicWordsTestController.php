<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicWordsTestController extends Controller
{
    // Session keys for public words test (separate from admin)
    private const SESSION_QUEUE = 'public_words_test_queue';
    private const SESSION_CURRENT = 'public_words_test_current';
    private const SESSION_STATS = 'public_words_test_stats';
    private const SESSION_TOTAL = 'public_words_test_total_count';

    /**
     * Get words that have a valid (non-empty) Ukrainian translation.
     */
    private function getWordsWithValidTranslation(): Collection
    {
        return Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->whereHas('translates', fn ($q) => $q
                ->where('lang', 'uk')
                ->whereNotNull('translation')
                ->where('translation', '!=', '')
                ->whereRaw("TRIM(translation) != ''")
            )
            ->get();
    }

    /**
     * Initialize test queue if not exists.
     */
    private function initializeQueue(): void
    {
        if (!session()->has(self::SESSION_QUEUE)) {
            $words = $this->getWordsWithValidTranslation();
            $queue = $words->pluck('id')->shuffle()->toArray();
            $totalCount = count($queue);
            
            session([
                self::SESSION_QUEUE => $queue,
                self::SESSION_TOTAL => $totalCount,
                self::SESSION_STATS => [
                    'correct' => 0,
                    'wrong' => 0,
                    'total' => 0,
                ],
                self::SESSION_CURRENT => null,
            ]);
        }
    }

    /**
     * Get default stats structure.
     */
    private function getStats(): array
    {
        return session(self::SESSION_STATS, [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
    }

    /**
     * Get answer options (distractors) for a question.
     * Returns unique, non-empty options.
     */
    private function getDistractors(int $excludeWordId, string $questionType, int $count = 4): array
    {
        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->whereHas('translates', fn ($q) => $q
                ->where('lang', 'uk')
                ->whereNotNull('translation')
                ->where('translation', '!=', '')
                ->whereRaw("TRIM(translation) != ''")
            )
            ->where('id', '!=', $excludeWordId)
            ->inRandomOrder()
            ->limit($count * 2) // Get extra in case of duplicates
            ->get();

        if ($questionType === 'en_to_uk') {
            // Return Ukrainian translations
            $options = $otherWords
                ->map(fn ($w) => $w->translates->first()?->translation)
                ->filter(fn ($t) => !empty(trim($t ?? '')))
                ->unique()
                ->take($count)
                ->values()
                ->toArray();
        } else {
            // Return English words
            $options = $otherWords
                ->pluck('word')
                ->filter(fn ($w) => !empty(trim($w ?? '')))
                ->unique()
                ->take($count)
                ->values()
                ->toArray();
        }

        return $options;
    }

    /**
     * Prepare current question data.
     * If no current question is set, pop one from queue.
     * Uses iterative approach to avoid potential stack overflow.
     */
    private function prepareCurrentQuestion(): ?array
    {
        $current = session(self::SESSION_CURRENT);

        if ($current) {
            return $current;
        }

        // Use loop instead of recursion to avoid stack overflow
        while (true) {
            $queue = session(self::SESSION_QUEUE, []);

            if (empty($queue)) {
                return null;
            }

            // Pop the first word from queue
            $wordId = array_shift($queue);
            session([self::SESSION_QUEUE => $queue]);

            $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])->find($wordId);

            if (!$word || $word->translates->isEmpty()) {
                // Skip invalid word, try next
                continue;
            }

            $translation = $word->translates->first()->translation;

            if (empty(trim($translation ?? ''))) {
                // Skip word with empty translation
                continue;
            }

            $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

            // Get correct answer based on question type
            $correctAnswer = $questionType === 'en_to_uk' ? $translation : $word->word;

            // Get distractors
            $distractors = $this->getDistractors($wordId, $questionType, 4);

            // Build options array
            $options = $distractors;
            $options[] = $correctAnswer;
            
            // Ensure uniqueness and no empty values
            $options = array_values(array_filter(array_unique($options), fn ($o) => !empty(trim($o ?? ''))));
            shuffle($options);

            $current = [
                'word_id' => $wordId,
                'word' => $word->word,
                'translation' => $translation,
                'question_type' => $questionType,
                'correct_answer' => $correctAnswer,
                'options' => $options,
            ];

            session([self::SESSION_CURRENT => $current]);

            return $current;
        }
    }

    /**
     * GET /words/test - Main page.
     */
    public function index()
    {
        $this->initializeQueue();

        $stats = $this->getStats();
        $totalCount = session(self::SESSION_TOTAL, 0);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;
        $queue = session(self::SESSION_QUEUE, []);
        $current = session(self::SESSION_CURRENT);

        // Check if test is completed
        $isCompleted = empty($queue) && $current === null && $stats['total'] > 0;

        return view('words.public-test', [
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'isCompleted' => $isCompleted,
        ]);
    }

    /**
     * GET /words/test/state - Get current state (for initial load and after answer).
     */
    public function state()
    {
        $this->initializeQueue();

        $stats = $this->getStats();
        $totalCount = session(self::SESSION_TOTAL, 0);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $current = $this->prepareCurrentQuestion();

        if (!$current) {
            return response()->json([
                'completed' => true,
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
                'answeredCount' => $stats['total'],
            ]);
        }

        return response()->json([
            'completed' => false,
            'question' => [
                'word_id' => $current['word_id'],
                'question_type' => $current['question_type'],
                'question_text' => $current['question_type'] === 'en_to_uk' ? $current['word'] : $current['translation'],
                'options' => $current['options'],
            ],
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'answeredCount' => $stats['total'],
        ]);
    }

    /**
     * POST /words/test/answer - Submit an answer.
     */
    public function answer(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
            'question_type' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $current = session(self::SESSION_CURRENT);

        if (!$current) {
            return response()->json([
                'error' => 'No current question',
            ], 400);
        }

        // Validate that the answer is for the current question
        if ($current['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'error' => 'Question mismatch',
            ], 400);
        }

        $userAnswer = trim($request->input('answer'));
        $correctAnswer = $current['correct_answer'];
        $isCorrect = $userAnswer === trim($correctAnswer);

        // Update stats
        $stats = $this->getStats();
        $stats['total']++;
        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }
        session([self::SESSION_STATS => $stats]);

        // Clear current question (allow next question on next request)
        session([self::SESSION_CURRENT => null]);

        $totalCount = session(self::SESSION_TOTAL, 0);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        // Prepare next question
        $nextQuestion = $this->prepareCurrentQuestion();

        $response = [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correctAnswer,
            'userAnswer' => $userAnswer,
            'word' => $current['word'],
            'translation' => $current['translation'],
            'questionType' => $current['question_type'],
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'answeredCount' => $stats['total'],
        ];

        if ($nextQuestion) {
            $response['completed'] = false;
            $response['nextQuestion'] = [
                'word_id' => $nextQuestion['word_id'],
                'question_type' => $nextQuestion['question_type'],
                'question_text' => $nextQuestion['question_type'] === 'en_to_uk' ? $nextQuestion['word'] : $nextQuestion['translation'],
                'options' => $nextQuestion['options'],
            ];
        } else {
            $response['completed'] = true;
            $response['nextQuestion'] = null;
        }

        return response()->json($response);
    }

    /**
     * POST /words/test/reset - Reset test progress.
     */
    public function reset()
    {
        session()->forget([
            self::SESSION_QUEUE,
            self::SESSION_CURRENT,
            self::SESSION_STATS,
            self::SESSION_TOTAL,
        ]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('public.words.test');
    }
}
