<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;

class PronounsTestController extends Controller
{
    private function allPronouns(): array
    {
        $words = Word::with(['translates' => function ($q) {
            $q->where('lang', 'uk');
        }, 'tags'])
            ->whereHas('tags', fn ($q) => $q->where('name', 'pronouns'))
            ->get();

        $list = [];
        foreach ($words as $word) {
            $groupTag = $word->tags->firstWhere('name', '!=', 'pronouns');
            $list[] = [
                'en' => $word->word,
                'uk' => optional($word->translates->first())->translation,
                'group' => $groupTag?->name,
            ];
        }

        return $list;
    }

    public function index(Request $request)
    {
        $stats = session('pronouns_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $percentage = $stats['total'] > 0
            ? round(($stats['correct'] / $stats['total']) * 100, 2)
            : 0;

        $totalCount = count($this->allPronouns());

        $queue = session('pronouns_queue');
        if (! $queue) {
            $queue = $this->allPronouns();
            shuffle($queue);
            session([
                'pronouns_queue' => $queue,
                'pronouns_test_stats' => $stats,
            ]);
        }

        if (empty($queue)) {
            return view('pronouns.complete', [
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
            ]);
        }

        $item = array_shift($queue);
        session(['pronouns_queue' => $queue]);

        $all = $this->allPronouns();
        $others = array_filter($all, fn ($p) => $p['en'] !== $item['en']);
        shuffle($others);
        $others = array_slice($others, 0, 4);

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';
        if ($questionType === 'en_to_uk') {
            $correct = $item['uk'];
            $options = array_map(fn ($p) => $p['uk'], $others);
        } else {
            $correct = $item['en'];
            $options = array_map(fn ($p) => $p['en'], $others);
        }
        $options[] = $correct;
        shuffle($options);

        return view('pronouns.test', [
            'item' => $item,
            'options' => $options,
            'questionType' => $questionType,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'feedback' => session('pronouns_test_feedback'),
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'en' => 'required|string',
            'answer' => 'required|string',
            'questionType' => 'required|in:en_to_uk,uk_to_en',
        ]);

        $stats = session('pronouns_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $stats['total']++;

        $pronoun = collect($this->allPronouns())->firstWhere('en', $request->input('en'));
        $correct = $request->input('questionType') === 'en_to_uk' ? ($pronoun['uk'] ?? '') : ($pronoun['en'] ?? '');

        $isCorrect = trim($request->input('answer')) === trim($correct);
        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }
        session(['pronouns_test_stats' => $stats]);

        session()->flash('pronouns_test_feedback', [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correct,
            'userAnswer' => $request->input('answer'),
            'word' => $request->input('en'),
            'questionType' => $request->input('questionType'),
        ]);

        return redirect()->route('pronouns.test');
    }
}
