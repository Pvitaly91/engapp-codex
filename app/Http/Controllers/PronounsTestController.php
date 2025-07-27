<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PronounsTestController extends Controller
{
    protected array $pronouns = [
        ['en' => 'I', 'uk' => 'я'],
        ['en' => 'you', 'uk' => 'ти'],
        ['en' => 'he', 'uk' => 'він'],
        ['en' => 'she', 'uk' => 'вона'],
        ['en' => 'it', 'uk' => 'воно'],
        ['en' => 'we', 'uk' => 'ми'],
        ['en' => 'they', 'uk' => 'вони'],
    ];

    public function index(Request $request)
    {
        $feedback = session('pronouns_test_feedback');
        $stats = session('pronouns_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';
        $item = $this->pronouns[array_rand($this->pronouns)];

        $others = array_filter($this->pronouns, fn ($p) => $p['en'] !== $item['en']);
        shuffle($others);
        $others = array_slice($others, 0, 4);

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
            'feedback' => $feedback,
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

        $pronoun = collect($this->pronouns)->firstWhere('en', $request->input('en'));
        $correct = $request->input('questionType') === 'en_to_uk' ? $pronoun['uk'] ?? '' : $pronoun['en'] ?? '';

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
