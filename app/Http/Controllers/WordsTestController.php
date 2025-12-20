<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WordsTestController extends Controller
{
    private function getWords(array $tags): Collection
    {
        $query = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk'));

        if (! empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('name', $tags));
        }

        return $query->get();
    }

    /**
     * Shared logic for preparing test data (used by both admin and public).
     */
    private function prepareTestData(Request $request): array
    {
        if ($request->boolean('reset')) {
            $selectedTags = [];
            session()->forget(['words_selected_tags', 'words_test_stats', 'words_queue', 'words_total_count']);
        } elseif ($request->has('filter')) {
            $selectedTags = $request->input('tags', []);

            if ($selectedTags !== session('words_selected_tags')) {
                session()->forget(['words_test_stats', 'words_queue', 'words_total_count']);
            }
        } else {
            $selectedTags = session('words_selected_tags', []);
        }

        session(['words_selected_tags' => $selectedTags]);

        $feedback = session('feedback');
        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $queue = session('words_queue');
        $totalCount = session('words_total_count', 0);

        if (! $queue) {
            $words = $this->getWords($selectedTags);
            $queue = $words->pluck('id')->shuffle()->toArray();
            $totalCount = count($queue);
            session(['words_queue' => $queue, 'words_total_count' => $totalCount]);
        }

        // Calculate progress variables
        $remainingCount = count($queue);
        $currentIndex = $totalCount - $remainingCount;
        $progressPercent = $totalCount > 0 ? round(($currentIndex / $totalCount) * 100, 2) : 0;

        return [
            'selectedTags' => $selectedTags,
            'feedback' => $feedback,
            'stats' => $stats,
            'percentage' => $percentage,
            'queue' => $queue,
            'totalCount' => $totalCount,
            'currentIndex' => $currentIndex,
            'progressPercent' => $progressPercent,
            'allTags' => Tag::whereHas('words')->get(),
        ];
    }

    /**
     * Shared logic for generating question data.
     */
    private function generateQuestionData(array $queue, array $selectedTags): ?array
    {
        if (empty($queue)) {
            return null;
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->find($wordId);

        if (! $word) {
            return null;
        }

        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk'))
            ->when($selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $selectedTags)))
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        if ($questionType === 'en_to_uk') {
            $correct = optional($word->translates->first())->translation ?? '';
            $options = $otherWords->map(fn ($w) => optional($w->translates->first())->translation ?? '')->toArray();
        } else {
            $correct = $word->word;
            $options = $otherWords->pluck('word')->toArray();
        }

        $options[] = $correct;
        shuffle($options);

        return [
            'word' => $word,
            'translation' => optional($word->translates->first())->translation ?? '',
            'options' => $options,
            'questionType' => $questionType,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->prepareTestData($request);

        if (empty($data['queue']) || ($data['percentage'] >= 95 && $data['stats']['total'] >= $data['totalCount'])) {
            return view('words.complete', [
                'stats' => $data['stats'],
                'percentage' => $data['percentage'],
                'totalCount' => $data['totalCount'],
                'selectedTags' => $data['selectedTags'],
                'allTags' => $data['allTags'],
            ]);
        }

        $questionData = $this->generateQuestionData($data['queue'], $data['selectedTags']);

        return view('words.test', array_merge([
            'feedback' => $data['feedback'],
            'stats' => $data['stats'],
            'percentage' => $data['percentage'],
            'totalCount' => $data['totalCount'],
            'selectedTags' => $data['selectedTags'],
            'allTags' => $data['allTags'],
        ], $questionData ?? ['word' => null, 'translation' => '', 'options' => [], 'questionType' => 'en_to_uk']));
    }

    /**
     * Public index method for engram-styled view.
     */
    public function publicIndex(Request $request)
    {
        $data = $this->prepareTestData($request);

        if (empty($data['queue']) || ($data['percentage'] >= 95 && $data['stats']['total'] >= $data['totalCount'])) {
            return view('engram.words.complete', [
                'stats' => $data['stats'],
                'percentage' => $data['percentage'],
                'totalCount' => $data['totalCount'],
                'selectedTags' => $data['selectedTags'],
                'allTags' => $data['allTags'],
                'currentIndex' => $data['currentIndex'],
                'progressPercent' => $data['progressPercent'],
            ]);
        }

        $questionData = $this->generateQuestionData($data['queue'], $data['selectedTags']);

        // Recalculate currentIndex after question generation (queue was shifted)
        $remainingCount = count(session('words_queue', []));
        $currentIndex = $data['totalCount'] - $remainingCount;
        $progressPercent = $data['totalCount'] > 0 ? round(($currentIndex / $data['totalCount']) * 100, 2) : 0;

        return view('engram.words.test', array_merge([
            'feedback' => $data['feedback'],
            'stats' => $data['stats'],
            'percentage' => $data['percentage'],
            'totalCount' => $data['totalCount'],
            'selectedTags' => $data['selectedTags'],
            'allTags' => $data['allTags'],
            'currentIndex' => $currentIndex,
            'progressPercent' => $progressPercent,
        ], $questionData ?? ['word' => null, 'translation' => '', 'options' => [], 'questionType' => 'en_to_uk']));
    }

    public function check(Request $request)
    {
        $request->validate([
            'word_id' => 'required|exists:words,id',
            'answer' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $stats['total']++;

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])->findOrFail($request->input('word_id'));

        $correct = $request->input('questionType') === 'en_to_uk'
            ? optional($word->translates->first())->translation ?? ''
            : $word->word;

        $isCorrect = trim($request->input('answer')) === trim($correct);

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }
        session(['words_test_stats' => $stats]);

        session()->flash('feedback', [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correct,
            'userAnswer' => $request->input('answer'),
            'word' => $word->word,
            'questionType' => $request->input('questionType'),
        ]);

        // Support redirect_route for both admin and public views
        $redirectRoute = $request->input('redirect_route', 'words.test');

        // Validate route name to prevent open redirect
        $allowedRoutes = ['words.test', 'words.public.test'];
        if (! in_array($redirectRoute, $allowedRoutes, true)) {
            $redirectRoute = 'words.test';
        }

        return redirect()->route($redirectRoute);
    }

    /**
     * Public reset method that redirects to public route.
     */
    public function publicReset()
    {
        session()->forget('words_test_stats');

        return redirect()->route('words.public.test');
    }
}
