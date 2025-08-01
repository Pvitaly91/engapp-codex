<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatGPTService;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Services\QuestionSeedingService;
use App\Models\Source;

class AiTestController extends Controller
{
    public function form()
    {
        $categories = Category::all();
        return view('ai-test-form', compact('categories'));
    }

    public function start(Request $request)
    {
        $request->validate([
            'categories' => 'required|array|min:1',
            'answers_count' => 'required|integer|min:1|max:3',
        ]);

        session([
            'ai_step.categories' => $request->input('categories'),
            'ai_step.answers_count' => (int) $request->input('answers_count'),
            'ai_step.stats' => ['correct' => 0, 'wrong' => 0, 'total' => 0],
            'ai_step.current_question' => null,
            'ai_step.feedback' => null,
        ]);

        return redirect()->route('ai-test.step');
    }

    public function step(ChatGPTService $gpt)
    {
        $catIds = session('ai_step.categories');
        if (!$catIds) {
            return redirect()->route('ai-test.form');
        }

        $stats = session('ai_step.stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $question = session('ai_step.current_question');
        if (!$question) {
            $tenseNames = Category::whereIn('id', $catIds)->pluck('name')->toArray();
            $answersCount = session('ai_step.answers_count', 1);
            $question = $gpt->generateGrammarQuestion($tenseNames, $answersCount);
            if ($question) {
                $this->storeQuestion($question, $catIds[0]);
                session(['ai_step.current_question' => $question]);
            }
        }

        $feedback = session('ai_step.feedback');
        session()->forget('ai_step.feedback');

        return view('ai-test-step', [
            'question' => $question,
            'stats' => $stats,
            'percentage' => $percentage,
            'feedback' => $feedback,
        ]);
    }

    public function check(Request $request, ChatGPTService $gpt)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $question = session('ai_step.current_question');
        if (!$question) {
            return redirect()->route('ai-test.step');
        }

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

        $stats = session('ai_step.stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $stats['total']++;
        if ($correct) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session([
            'ai_step.stats' => $stats,
            'ai_step.current_question' => null,
            'ai_step.feedback' => [
                'isCorrect' => $correct,
                'explanations' => $explanations,
            ],
        ]);

        return redirect()->route('ai-test.step');
    }

    public function reset()
    {
        session()->forget([
            'ai_step.categories',
            'ai_step.answers_count',
            'ai_step.stats',
            'ai_step.current_question',
            'ai_step.feedback',
        ]);
        return redirect()->route('ai-test.form');
    }

    private function storeQuestion(array $question, int $categoryId): void
    {
        $service = app(QuestionSeedingService::class);
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated'])->id;

        $answers = [];
        $options = [];
        foreach ($question['answers'] as $marker => $val) {
            $ans = ['marker' => $marker, 'answer' => $val];
            if (!empty($question['verb_hints'][$marker] ?? null)) {
                $ans['verb_hint'] = $question['verb_hints'][$marker];
            }
            $answers[] = $ans;
            $options[] = $val;
        }

        $service->seed([
            [
                'uuid' => Str::uuid()->toString(),
                'question' => $question['question'],
                'difficulty' => 1,
                'category_id' => $categoryId,
                'flag' => 1,
                'source_id' => $sourceId,
                'answers' => $answers,
                'options' => $options,
            ],
        ]);
    }
}
