<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WordsTestController extends Controller
{
    private const VALID_DIFFICULTIES = ['easy', 'medium', 'hard'];

    private function isFailed(array $stats): bool
    {
        return $stats['wrong'] >= 3;
    }

    private function activeLang(): string
    {
        $lang = session('locale', 'uk');

        if (! in_array($lang, ['uk', 'pl', 'en'], true)) {
            return 'uk';
        }

        return $lang;
    }

    private function wordsQuery(string $lang)
    {
        return Word::with(['translates' => fn ($q) => $q->where('lang', $lang), 'tags'])
            ->whereHas('translates', function ($q) use ($lang) {
                $q->where('lang', $lang)
                    ->whereNotNull('translation')
                    ->where('translation', '!=', '');
            });
    }

    private function getDifficultyFromRequest(Request $request): string
    {
        $difficulty = $request->route('difficulty', 'easy');

        if (! in_array($difficulty, self::VALID_DIFFICULTIES, true)) {
            return 'easy';
        }

        return $difficulty;
    }

    private function sessionKey(string $key, string $difficulty): string
    {
        return "words_test_{$difficulty}_{$key}";
    }

    private function getSessionKeys(string $difficulty): array
    {
        return [
            $this->sessionKey('stats', $difficulty),
            $this->sessionKey('queue', $difficulty),
            $this->sessionKey('total_count', $difficulty),
            $this->sessionKey('current_question', $difficulty),
        ];
    }

    private function initializeState(string $lang, string $difficulty): void
    {
        $statsKey = $this->sessionKey('stats', $difficulty);
        $queueKey = $this->sessionKey('queue', $difficulty);
        $totalCountKey = $this->sessionKey('total_count', $difficulty);

        if (! session()->has($statsKey)) {
            session([
                $statsKey => [
                    'correct' => 0,
                    'wrong' => 0,
                    'total' => 0,
                ],
            ]);
        }

        if (! session()->has($queueKey)) {
            $queue = $this->wordsQuery($lang)->pluck('id')->shuffle()->toArray();
            session([
                $queueKey => $queue,
                $totalCountKey => count($queue),
            ]);
        }
    }

    private function calculatePercentage(array $stats): float
    {
        if ($stats['total'] === 0) {
            return 0;
        }

        return round(($stats['correct'] / $stats['total']) * 100, 2);
    }

    private function buildQuestionPayload(int $wordId, string $lang, string $difficulty): ?array
    {
        $word = $this->wordsQuery($lang)->find($wordId);

        if (! $word) {
            return null;
        }

        $translation = optional($word->translates->first())->translation ?? '';

        // For medium and hard, always use uk_to_en (show translation, user types English word)
        if ($difficulty === 'medium' || $difficulty === 'hard') {
            return [
                'word_id' => $word->id,
                'word' => $word->word,
                'translation' => $translation,
                'tags' => $word->tags->pluck('name')->all(),
                'questionType' => 'uk_to_en',
                'prompt' => $translation,
                'options' => [],
                'correct_answer' => $word->word,
            ];
        }

        // For easy mode, use multiple choice with random question type
        $otherWords = $this->wordsQuery($lang)
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        if ($questionType === 'en_to_uk') {
            $correct = $translation;
            $options = $otherWords
                ->map(fn ($w) => optional($w->translates->first())->translation ?? '')
                ->filter()
                ->values();
        } else {
            $correct = $word->word;
            $options = $otherWords->pluck('word');
        }

        $options = $options
            ->filter()
            ->push($correct)
            ->unique()
            ->shuffle()
            ->values()
            ->all();

        return [
            'word_id' => $word->id,
            'word' => $word->word,
            'translation' => $translation,
            'tags' => $word->tags->pluck('name')->all(),
            'questionType' => $questionType,
            'prompt' => $questionType === 'en_to_uk' ? $word->word : $translation,
            'options' => $options,
            'correct_answer' => $correct,
        ];
    }

    private function ensureCurrentQuestion(string $lang, string $difficulty): ?array
    {
        $questionKey = $this->sessionKey('current_question', $difficulty);
        $queueKey = $this->sessionKey('queue', $difficulty);

        if ($question = session($questionKey)) {
            return $question;
        }

        $queue = session($queueKey, []);

        if (empty($queue)) {
            return null;
        }

        $question = null;

        while (! empty($queue) && ! $question) {
            $wordId = array_shift($queue);
            $question = $this->buildQuestionPayload($wordId, $lang, $difficulty);
        }

        session([$queueKey => $queue]);

        if (! $question) {
            return null;
        }

        session([$questionKey => $question]);

        return $question;
    }

    private function completionStatus(?array $question, string $difficulty): bool
    {
        $queueKey = $this->sessionKey('queue', $difficulty);
        $totalCountKey = $this->sessionKey('total_count', $difficulty);

        $queue = session($queueKey, []);
        $totalCount = session($totalCountKey, 0);

        if ($totalCount === 0) {
            return true;
        }

        return empty($queue) && ! $question;
    }

    private function statePayload(string $lang, string $difficulty): array
    {
        $this->initializeState($lang, $difficulty);

        $statsKey = $this->sessionKey('stats', $difficulty);
        $queueKey = $this->sessionKey('queue', $difficulty);
        $totalCountKey = $this->sessionKey('total_count', $difficulty);
        $questionKey = $this->sessionKey('current_question', $difficulty);

        $stats = session($statsKey);
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            session()->forget($questionKey);
            $question = null;
        } else {
            $question = $this->ensureCurrentQuestion($lang, $difficulty);
        }
        $percentage = $this->calculatePercentage($stats);

        return [
            'question' => $question ? Arr::except($question, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session($totalCountKey, 0),
            'completed' => $failed ? false : $this->completionStatus($question, $difficulty),
            'failed' => $failed,
            'difficulty' => $difficulty,
        ];
    }

    public function index(Request $request)
    {
        $lang = $this->activeLang();
        $difficulty = $this->getDifficultyFromRequest($request);
        $this->initializeState($lang, $difficulty);

        return view('words.test', [
            'activeLang' => $lang,
            'difficulty' => $difficulty,
        ]);
    }

    public function state(Request $request)
    {
        $difficulty = $this->getDifficultyFromRequest($request);

        return response()->json($this->statePayload($this->activeLang(), $difficulty));
    }

    public function check(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $difficulty = $this->getDifficultyFromRequest($request);

        $statsKey = $this->sessionKey('stats', $difficulty);
        $queueKey = $this->sessionKey('queue', $difficulty);
        $totalCountKey = $this->sessionKey('total_count', $difficulty);
        $questionKey = $this->sessionKey('current_question', $difficulty);

        $question = session($questionKey);

        if (! $question || $question['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'message' => 'Поточне питання не знайдено. Оновіть сторінку.',
            ], 422);
        }

        $stats = session($statsKey, [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $stats['total']++;

        // For medium/hard: case-insensitive comparison with trim
        $userAnswer = strtolower(trim($request->input('answer')));
        $correctAnswer = strtolower(trim($question['correct_answer']));

        if ($difficulty === 'easy') {
            // For easy mode, exact match (original behavior)
            $isCorrect = trim($request->input('answer')) === trim($question['correct_answer']);
        } else {
            // For medium/hard, case-insensitive
            $isCorrect = $userAnswer === $correctAnswer;
        }

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session([$statsKey => $stats]);
        session()->forget($questionKey);

        $lang = $this->activeLang();
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            $nextQuestion = null;
        } else {
            $nextQuestion = $this->ensureCurrentQuestion($lang, $difficulty);
        }

        return response()->json([
            'result' => [
                'isCorrect' => $isCorrect,
                'correctAnswer' => $question['correct_answer'],
                'word' => $question['word'],
                'questionType' => $question['questionType'],
                'translation' => $question['translation'],
            ],
            'question' => $nextQuestion ? Arr::except($nextQuestion, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $this->calculatePercentage($stats),
            'totalCount' => session($totalCountKey, 0),
            'completed' => $failed ? false : $this->completionStatus($nextQuestion, $difficulty),
            'failed' => $failed,
            'difficulty' => $difficulty,
        ]);
    }

    public function reset(Request $request)
    {
        $difficulty = $this->getDifficultyFromRequest($request);

        session()->forget($this->getSessionKeys($difficulty));

        $payload = $this->statePayload($this->activeLang(), $difficulty);

        return response()->json($payload);
    }
}
