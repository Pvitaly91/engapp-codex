<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WordsTestController extends Controller
{
    private const SESSION_KEYS = [
        'words_selected_tags',
        'words_test_stats',
        'words_queue',
        'words_total_count',
        'words_current_question',
    ];

    private const DIFFICULTIES = ['easy', 'medium', 'hard'];

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

    private function difficulty(?string $difficulty = null): string
    {
        $difficulty = strtolower($difficulty ?? 'easy');

        if (! in_array($difficulty, self::DIFFICULTIES, true)) {
            return 'easy';
        }

        return $difficulty;
    }

    private function sessionKey(string $key, string $difficulty): string
    {
        return sprintf('%s_%s', $key, $difficulty);
    }

    private function initializeState(string $lang, string $difficulty): void
    {
        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

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
                $totalKey => count($queue),
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

        if ($difficulty === 'easy') {
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

    private function ensureCurrentQuestion(string $lang, string $difficulty): ?array
    {
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);

        if ($question = session($currentQuestionKey)) {
            return $question;
        }

        $queueKey = $this->sessionKey('words_queue', $difficulty);
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

        session([$currentQuestionKey => $question]);

        return $question;
    }

    private function completionStatus(?array $question, string $difficulty): bool
    {
        $queue = session($this->sessionKey('words_queue', $difficulty), []);
        $totalCount = session($this->sessionKey('words_total_count', $difficulty), 0);

        if ($totalCount === 0) {
            return true;
        }

        return empty($queue) && ! $question;
    }

    private function statePayload(string $lang, string $difficulty): array
    {
        $this->initializeState($lang, $difficulty);

        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

        $stats = session($statsKey);
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            session()->forget($currentQuestionKey);
            $question = null;
        } else {
            $question = $this->ensureCurrentQuestion($lang, $difficulty);
        }
        $percentage = $this->calculatePercentage($stats);

        return [
            'question' => $question ? Arr::except($question, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session($totalKey, 0),
            'completed' => $failed ? false : $this->completionStatus($question, $difficulty),
            'failed' => $failed,
            'difficulty' => $difficulty,
        ];
    }

    public function index(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $lang = $this->activeLang();
        $this->initializeState($lang, $difficulty);

        return view('words.test', [
            'activeLang' => $lang,
            'difficulty' => $difficulty,
            'stateUrl' => route($this->routeName('state', $difficulty)),
            'checkUrl' => route($this->routeName('check', $difficulty)),
            'resetUrl' => route($this->routeName('reset', $difficulty)),
        ]);
    }

    public function state(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);

        return response()->json($this->statePayload($this->activeLang(), $difficulty));
    }

    public function check(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);

        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);
        $question = session($currentQuestionKey);

        if (! $question || $question['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'message' => 'Поточне питання не знайдено. Оновіть сторінку.',
            ], 422);
        }

        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

        $stats = session($statsKey, [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $stats['total']++;

        $userAnswer = trim($request->input('answer'));
        $correctAnswer = trim($question['correct_answer']);
        $isCorrect = $difficulty === 'easy'
            ? $userAnswer === $correctAnswer
            : strcasecmp($userAnswer, $correctAnswer) === 0;

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session([$statsKey => $stats]);
        session()->forget($currentQuestionKey);

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
            'totalCount' => session($totalKey, 0),
            'completed' => $failed ? false : $this->completionStatus($nextQuestion, $difficulty),
            'failed' => $failed,
        ]);
    }

    public function reset(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);

        $keys = array_map(fn ($key) => $this->sessionKey($key, $difficulty), self::SESSION_KEYS);
        session()->forget($keys);

        $payload = $this->statePayload($this->activeLang(), $difficulty);

        return response()->json($payload);
    }

    private function routeName(string $action, string $difficulty): string
    {
        if ($difficulty === 'medium') {
            return "words.test.{$action}.medium";
        }

        if ($difficulty === 'hard') {
            return "words.test.{$action}.hard";
        }

        return "words.test.{$action}";
    }
}
