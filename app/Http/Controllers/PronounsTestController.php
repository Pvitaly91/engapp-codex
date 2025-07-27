<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PronounsTestController extends Controller
{
    protected array $pronouns = [
        'personal_subject' => [
            ['en' => 'I', 'uk' => 'я'],
            ['en' => 'you', 'uk' => 'ти'],
            ['en' => 'he', 'uk' => 'він'],
            ['en' => 'she', 'uk' => 'вона'],
            ['en' => 'it', 'uk' => 'воно'],
            ['en' => 'we', 'uk' => 'ми'],
            ['en' => 'they', 'uk' => 'вони'],
        ],
        'personal_object' => [
            ['en' => 'me', 'uk' => 'мене'],
            ['en' => 'you', 'uk' => 'тебе'],
            ['en' => 'him', 'uk' => 'його'],
            ['en' => 'her', 'uk' => 'її'],
            ['en' => 'it', 'uk' => 'його'],
            ['en' => 'us', 'uk' => 'нас'],
            ['en' => 'them', 'uk' => 'їх'],
        ],
        'possessive_adjective' => [
            ['en' => 'my', 'uk' => 'мій'],
            ['en' => 'your', 'uk' => 'твій'],
            ['en' => 'his', 'uk' => 'його'],
            ['en' => 'her', 'uk' => 'її'],
            ['en' => 'its', 'uk' => 'його'],
            ['en' => 'our', 'uk' => 'наш'],
            ['en' => 'their', 'uk' => 'їх'],
        ],
        'possessive_pronoun' => [
            ['en' => 'mine', 'uk' => 'моє'],
            ['en' => 'yours', 'uk' => 'твоє'],
            ['en' => 'his', 'uk' => 'його'],
            ['en' => 'hers', 'uk' => 'її'],
            ['en' => 'its', 'uk' => 'його'],
            ['en' => 'ours', 'uk' => 'наш'],
            ['en' => 'theirs', 'uk' => 'їх'],
        ],
        'reflexive' => [
            ['en' => 'myself', 'uk' => 'себе'],
            ['en' => 'yourself', 'uk' => 'себе'],
            ['en' => 'himself', 'uk' => 'себе'],
            ['en' => 'herself', 'uk' => 'себе'],
            ['en' => 'itself', 'uk' => 'себе'],
            ['en' => 'ourselves', 'uk' => 'себе'],
            ['en' => 'themselves', 'uk' => 'себе'],
        ],
    ];

    private function allPronouns(): array
    {
        $list = [];
        foreach ($this->pronouns as $group => $items) {
            foreach ($items as $item) {
                $list[] = array_merge($item, ['group' => $group]);
            }
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

        $queue = session('pronouns_queue');
        if (! $queue) {
            $queue = $this->allPronouns();
            shuffle($queue);
            session(['pronouns_queue' => $queue, 'pronouns_test_stats' => $stats]);
        }

        if (empty($queue)) {
            return view('pronouns.complete', ['stats' => $stats]);
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
