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

    private function activeLang(): string
    {
        $lang = session('locale', app()->getLocale() ?? 'uk');

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

    private function initializeState(string $lang): void
    {
        if (! session()->has('words_test_stats')) {
            session([
                'words_test_stats' => [
                    'correct' => 0,
                    'wrong' => 0,
                    'total' => 0,
                ],
            ]);
        }

        if (! session()->has('words_queue')) {
            $queue = $this->wordsQuery($lang)->pluck('id')->shuffle()->toArray();
            session([
                'words_queue' => $queue,
                'words_total_count' => count($queue),
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

    private function buildQuestionPayload(int $wordId, string $lang): ?array
    {
        $word = $this->wordsQuery($lang)->find($wordId);

        if (! $word) {
            return null;
        }

        $otherWords = $this->wordsQuery($lang)
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        $translation = optional($word->translates->first())->translation ?? '';

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

    private function ensureCurrentQuestion(string $lang): ?array
    {
        if ($question = session('words_current_question')) {
            return $question;
        }

        $queue = session('words_queue', []);

        if (empty($queue)) {
            return null;
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $question = $this->buildQuestionPayload($wordId, $lang);

        if (! $question) {
            return null;
        }

        session(['words_current_question' => $question]);

        return $question;
    }

    private function completionStatus(?array $question): bool
    {
        $queue = session('words_queue', []);
        $totalCount = session('words_total_count', 0);

        if ($totalCount === 0) {
            return true;
        }

        return empty($queue) && ! $question;
    }

    private function statePayload(string $lang): array
    {
        $this->initializeState($lang);

        $stats = session('words_test_stats');
        $question = $this->ensureCurrentQuestion($lang);
        $percentage = $this->calculatePercentage($stats);

        return [
            'question' => $question ? Arr::except($question, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session('words_total_count', 0),
            'completed' => $this->completionStatus($question),
        ];
    }

    public function index(Request $request)
    {
        $lang = $this->activeLang();
        $this->initializeState($lang);

        return view('words.test', [
            'activeLang' => $lang,
        ]);
    }

    public function state(Request $request)
    {
        return response()->json($this->statePayload($this->activeLang()));
    }

    public function check(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $question = session('words_current_question');

        if (! $question || $question['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'message' => 'Поточне питання не знайдено. Оновіть сторінку.',
            ], 422);
        }

        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $stats['total']++;

        $isCorrect = trim($request->input('answer')) === trim($question['correct_answer']);

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session(['words_test_stats' => $stats]);
        session()->forget('words_current_question');

        $lang = $this->activeLang();
        $nextQuestion = $this->ensureCurrentQuestion($lang);

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
            'totalCount' => session('words_total_count', 0),
            'completed' => $this->completionStatus($nextQuestion),
        ]);
    }

    public function reset(Request $request)
    {
        session()->forget(self::SESSION_KEYS);

        $payload = $this->statePayload($this->activeLang());

        return response()->json($payload);
    }
}
