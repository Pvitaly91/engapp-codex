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
        $query = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags']);

        if (! empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('name', $tags));
        }

        return $query->get();
    }

    public function index(Request $request)
    {
        if ($request->boolean('reset')) {
            $selectedTags = [];
            session()->forget(['words_selected_tags', 'words_test_stats', 'words_queue', 'words_total_count']);
        } else {
            $selectedTags = $request->input('tags', session('words_selected_tags', []));

            if ($request->has('tags') && $selectedTags !== session('words_selected_tags')) {
                session()->forget(['words_test_stats', 'words_queue', 'words_total_count']);
            }
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

        if (empty($queue) || $percentage >= 95) {
            return view('words.complete', [
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
                'selectedTags' => $selectedTags,
                'allTags' => Tag::all(),
            ]);
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->find($wordId);

        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
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

        return view('words.test', [
            'word' => $word,
            'translation' => optional($word->translates->first())->translation ?? '',
            'options' => $options,
            'questionType' => $questionType,
            'feedback' => $feedback,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'selectedTags' => $selectedTags,
            'allTags' => Tag::all(),
        ]);
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

        return redirect()->route('words.test');
    }
}
