<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PublicWordsTestController extends Controller
{
    private const SESSION_QUEUE = 'public_words_test_queue';
    private const SESSION_CURRENT = 'public_words_test_current';
    private const SESSION_STATS = 'public_words_test_stats';
    private const SESSION_TOTAL = 'public_words_test_total_count';

    public function index()
    {
        $state = $this->buildState();

        return view('words.public-test', [
            'state' => $state,
        ]);
    }

    public function state()
    {
        return response()->json($this->buildState());
    }

    public function answer(Request $request)
    {
        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $current = session(self::SESSION_CURRENT);

        if (! $current || (int) $request->input('word_id') !== (int) $current['word_id'] || $request->input('questionType') !== $current['questionType']) {
            return response()->json([
                'message' => 'Поточне питання не знайдено. Спробуйте оновити сторінку.',
            ], 422);
        }

        $stats = $this->getStats();
        $stats['total']++;

        $userAnswer = trim($request->input('answer'));
        $isCorrect = $userAnswer === trim($current['correctAnswer']);
        $stats[$isCorrect ? 'correct' : 'wrong']++;

        session([self::SESSION_STATS => $stats]);
        session()->forget(self::SESSION_CURRENT);

        $nextQuestion = $this->prepareQuestion();
        $queue = $this->getQueue();
        $completed = ! $nextQuestion && empty($queue);

        return response()->json([
            'isCorrect' => $isCorrect,
            'correctAnswer' => $current['correctAnswer'],
            'question' => $nextQuestion,
            'stats' => $stats,
            'percentage' => $this->calculatePercentage($stats),
            'totalCount' => session(self::SESSION_TOTAL, 0),
            'completed' => $completed,
        ]);
    }

    public function reset()
    {
        session()->forget([
            self::SESSION_QUEUE,
            self::SESSION_CURRENT,
            self::SESSION_STATS,
            self::SESSION_TOTAL,
        ]);

        return response()->json(['status' => 'reset']);
    }

    private function buildState(): array
    {
        $queue = $this->getQueue();
        $stats = $this->getStats();
        $percentage = $this->calculatePercentage($stats);

        $question = $this->prepareQuestion();
        $queue = $this->getQueue();
        $completed = ! $question && empty($queue);

        return [
            'question' => $question,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session(self::SESSION_TOTAL, 0),
            'completed' => $completed,
        ];
    }

    private function prepareQuestion(): ?array
    {
        $current = session(self::SESSION_CURRENT);

        if ($current) {
            return $this->formatQuestion($current);
        }

        $queue = $this->getQueue();

        if (empty($queue)) {
            return null;
        }

        $wordId = array_shift($queue);
        session([self::SESSION_QUEUE => $queue]);

        $word = Word::with('translates')->find($wordId);
        $translation = $this->getTranslation($word);

        if (! $word || $translation === null) {
            return $this->prepareQuestion();
        }

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';
        $options = $this->buildOptions($word, $questionType, $translation);

        $current = [
            'word_id' => $word->id,
            'questionType' => $questionType,
            'prompt' => $questionType === 'en_to_uk' ? $word->word : $translation,
            'correctAnswer' => $questionType === 'en_to_uk' ? $translation : $word->word,
            'options' => $options,
        ];

        session([self::SESSION_CURRENT => $current]);

        return $this->formatQuestion($current);
    }

    private function getQueue(): array
    {
        $queue = session(self::SESSION_QUEUE);

        if (is_array($queue)) {
            return $queue;
        }

        $words = $this->wordsWithTranslation();

        $queue = $words->pluck('id')->shuffle()->values()->all();

        session([
            self::SESSION_QUEUE => $queue,
            self::SESSION_TOTAL => count($queue),
        ]);

        return $queue;
    }

    private function wordsWithTranslation(): Collection
    {
        return Word::with('translates')
            ->whereHas('translates', function ($query) {
                $query->where('lang', 'uk')
                    ->whereNotNull('translation')
                    ->where('translation', '<>', '');
            })
            ->get();
    }

    private function getTranslation(Word $word): ?string
    {
        $translation = optional($word->translates->firstWhere('lang', 'uk'))->translation;

        if ($translation && trim($translation) !== '') {
            return $translation;
        }

        $direct = $word->translate('uk')->first();

        return $direct && trim($direct->translation) !== '' ? $direct->translation : null;
    }

    private function buildOptions(Word $word, string $questionType, string $translation): array
    {
        if ($questionType === 'en_to_uk') {
            $otherWords = $this->wordsWithTranslation()
                ->where('id', '!=', $word->id)
                ->shuffle()
                ->take(6);

            $options = $otherWords
                ->map(fn ($item) => $this->getTranslation($item))
                ->filter(fn ($value) => $value && trim($value) !== '')
                ->take(4)
                ->values()
                ->all();

            $options[] = $translation;
        } else {
            $otherWords = Word::where('id', '!=', $word->id)
                ->whereHas('translates', function ($query) {
                    $query->where('lang', 'uk')
                        ->whereNotNull('translation')
                        ->where('translation', '<>', '');
                })
                ->inRandomOrder()
                ->take(6)
                ->get();

            $options = $otherWords
                ->pluck('word')
                ->filter(fn ($value) => $value && trim($value) !== '')
                ->take(4)
                ->values()
                ->all();

            $options[] = $word->word;
        }

        return collect($options)
            ->filter(fn ($option) => trim($option) !== '')
            ->unique()
            ->shuffle()
            ->values()
            ->all();
    }

    private function formatQuestion(array $current): array
    {
        return [
            'word_id' => $current['word_id'],
            'questionType' => $current['questionType'],
            'prompt' => $current['prompt'],
            'options' => $current['options'],
            'promptLabel' => $current['questionType'] === 'en_to_uk'
                ? 'Виберіть правильний український переклад'
                : 'Виберіть правильний англійський варіант',
        ];
    }

    private function getStats(): array
    {
        return session(self::SESSION_STATS, [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
    }

    private function calculatePercentage(array $stats): float
    {
        return $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;
    }
}
