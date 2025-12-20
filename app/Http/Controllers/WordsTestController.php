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
        $query = Word::with([
            'translates' => fn ($q) => $q->where('lang', 'uk')
                ->whereNotNull('translation')
                ->where('translation', '!=', ''),
            'tags',
        ])->whereHas('translates', fn ($q) => $q->where('lang', 'uk')
            ->whereNotNull('translation')
            ->where('translation', '!=', ''));

        if (! empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('name', $tags));
        }

        return $query->get();
    }

    private function resolveSelectedTags(Request $request): array
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

        return $selectedTags;
    }

    private function getQueueAndTotalCount(array $selectedTags): array
    {
        $queue = session('words_queue');
        $totalCount = session('words_total_count', 0);

        if (! $queue) {
            $words = $this->getWords($selectedTags);
            $queue = $words->pluck('id')->shuffle()->toArray();
            $totalCount = count($queue);
            session(['words_queue' => $queue, 'words_total_count' => $totalCount]);
        }

        return [$queue, $totalCount];
    }

    private function renderIndex(Request $request, bool $isPublic)
    {
        $selectedTags = $this->resolveSelectedTags($request);
        $feedback = session('feedback');
        $stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        [$queue, $totalCount] = $this->getQueueAndTotalCount($selectedTags);
        $allTags = Tag::whereHas('words')->get();

        if ($totalCount === 0) {
            $baseData = [
                'word' => null,
                'translation' => '',
                'options' => [],
                'questionType' => null,
                'feedback' => $feedback,
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
                'selectedTags' => $selectedTags,
                'allTags' => $allTags,
            ];

            if ($isPublic) {
                $baseData['currentIndex'] = 0;
                $baseData['progressPercent'] = 0;
                $baseData['remainingCount'] = 0;
            }

            return view($isPublic ? 'engram.words.test' : 'words.complete', $baseData);
        }

        if (empty($queue) || ($percentage >= 95 && $stats['total'] >= $totalCount)) {
            $completionData = [
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
                'selectedTags' => $selectedTags,
                'allTags' => $allTags,
            ];

            if ($isPublic) {
                $remainingCount = count($queue ?? []);
                $currentIndex = max(0, $totalCount - $remainingCount);
                $progressPercent = $totalCount > 0 ? round(($currentIndex / $totalCount) * 100, 2) : 0;
                $completionData['currentIndex'] = $currentIndex;
                $completionData['progressPercent'] = $progressPercent;
                $completionData['remainingCount'] = $remainingCount;
            }

            return view($isPublic ? 'engram.words.complete' : 'words.complete', $completionData);
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')
            ->whereNotNull('translation')
            ->where('translation', '!=', ''), 'tags'])->find($wordId);

        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')
            ->whereNotNull('translation')
            ->where('translation', '!=', '')])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk')
                ->whereNotNull('translation')
                ->where('translation', '!=', ''))
            ->when($selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $selectedTags)))
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        if ($questionType === 'en_to_uk') {
            $correct = optional($word->translates->first())->translation ?? '';
            $options = $otherWords
                ->map(fn ($w) => optional($w->translates->first())->translation ?? '')
                ->filter(fn ($translation) => trim($translation) !== '')
                ->toArray();
        } else {
            $correct = $word->word;
            $options = $otherWords->pluck('word')->toArray();
        }

        $options[] = $correct;
        shuffle($options);

        $viewData = [
            'word' => $word,
            'translation' => optional($word->translates->first())->translation ?? '',
            'options' => $options,
            'questionType' => $questionType,
            'feedback' => $feedback,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'selectedTags' => $selectedTags,
            'allTags' => $allTags,
        ];

        if ($isPublic) {
            $remainingCount = count($queue);
            $currentIndex = max(1, $totalCount - $remainingCount);
            $progressPercent = $totalCount > 0 ? round(($currentIndex / $totalCount) * 100, 2) : 0;
            $viewData['currentIndex'] = $currentIndex;
            $viewData['progressPercent'] = $progressPercent;
            $viewData['remainingCount'] = $remainingCount;
        }

        return view($isPublic ? 'engram.words.test' : 'words.test', $viewData);
    }

    public function index(Request $request)
    {
        return $this->renderIndex($request, false);
    }

    public function publicIndex(Request $request)
    {
        return $this->renderIndex($request, true);
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

        return redirect()->route($request->input('redirect_route', 'words.test'));
    }

    public function reset(Request $request)
    {
        session()->forget('words_test_stats');

        return redirect()->route($request->input('redirect_route', 'words.test'));
    }
}
