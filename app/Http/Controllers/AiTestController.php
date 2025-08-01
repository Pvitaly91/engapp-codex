<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatGPTService;
use App\Models\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AiTestController extends Controller
{
    public function form()
    {
        $categories = Category::all();
        return view('ai-test-form', compact('categories'));
    }

    public function start(Request $request, ChatGPTService $gpt)
    {
        $request->validate([
            'categories' => 'required|array|min:1',
            'num_questions' => 'required|integer|min:1|max:3',
        ]);

        $tenseNames = Category::whereIn('id', $request->input('categories'))
            ->pluck('name')
            ->toArray();

        $questions = $gpt->generateGrammarQuestions(
            $tenseNames,
            (int) $request->input('num_questions'),
            1
        );

        session([
            'ai_questions' => $questions,
            'ai_queue' => array_keys($questions),
            'ai_total' => count($questions),
            'ai_stats' => ['correct' => 0, 'wrong' => 0, 'total' => 0],
            'ai_current' => null,
            'ai_feedback' => null,
        ]);

        return redirect()->route('ai-test.step');
    }

    public function step()
    {
        $questions = session('ai_questions', []);
        $stats = session('ai_stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;
        $queue = session('ai_queue', []);
        $totalCount = session('ai_total', 0);

        $currentIndex = session('ai_current');
        if ($currentIndex === null) {
            if (empty($queue)) {
                return view('ai-test-complete', [
                    'stats' => $stats,
                    'percentage' => $percentage,
                    'totalCount' => $totalCount,
                ]);
            }
            $currentIndex = array_shift($queue);
            session(['ai_queue' => $queue, 'ai_current' => $currentIndex]);
        }

        $question = $questions[$currentIndex];
        $feedback = session('ai_feedback');
        session()->forget('ai_feedback');

        return view('ai-test-step', [
            'question' => $question,
            'index' => $currentIndex,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'feedback' => $feedback,
        ]);
    }

    public function check(Request $request, ChatGPTService $gpt)
    {
        $request->validate([
            'index' => 'required',
            'answers' => 'required|array',
        ]);

        $questions = session('ai_questions', []);
        $idx = $request->input('index');
        if (!isset($questions[$idx])) {
            return redirect()->route('ai-test.step');
        }
        $question = $questions[$idx];
        $userAnswers = $request->input('answers', []);
        $correct = true;
        $explanations = [];

        foreach ($question['answers'] as $marker => $correctValue) {
            $given = $userAnswers[$marker] ?? '';
            if (is_array($given)) {
                $given = implode(' ', $given);
            }
            if (mb_strtolower(trim($given)) !== mb_strtolower($correctValue)) {
                $correct = false;
                $explanations[$marker] = $gpt->explainWrongAnswer($question['question'], $given, $correctValue);
            }
        }

        $stats = session('ai_stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $stats['total']++;
        if ($correct) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session([
            'ai_stats' => $stats,
            'ai_current' => null,
            'ai_feedback' => [
                'isCorrect' => $correct,
                'explanations' => $explanations,
            ],
        ]);

        return redirect()->route('ai-test.step');
    }

    public function reset()
    {
        session()->forget(['ai_questions', 'ai_queue', 'ai_total', 'ai_current', 'ai_stats', 'ai_feedback']);
        return redirect()->route('ai-test.form');
    }
}
