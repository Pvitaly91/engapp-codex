<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Question, QuestionHint};
use App\Services\{ChatGPTService, GeminiService};

class QuestionHelpController extends Controller
{
    public function hint(Request $request, ChatGPTService $gpt, GeminiService $gemini)
    {
        $data = $request->validate([
            'question_id' => 'sometimes|integer|exists:questions,id',
            'question' => 'required_without:question_id|string',
            'refresh' => 'sometimes|boolean',
        ]);

        $lang = "uk"; // app()->getLocale();

        if (isset($data['question_id'])) {
            $question = Question::findOrFail($data['question_id']);
            $refresh = $data['refresh'] ?? false;

            $chatgptHint = QuestionHint::where('question_id', $question->id)
                ->where('provider', 'chatgpt')
                ->where('locale', $lang)
                ->first();
            if (! $chatgptHint || $refresh) {
                $text = $gpt->hintSentenceStructure($question->renderQuestionText(), $lang);
                $chatgptHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => $lang],
                    ['hint' => $text]
                );
            }

            /*
            $geminiHint = QuestionHint::where('question_id', $question->id)
                ->where('provider', 'gemini')
                ->where('locale', $lang)
                ->first();
            if (! $geminiHint || $refresh) {
                $text = $gemini->hintSentenceStructure($question->renderQuestionText(), $lang);
                $geminiHint = QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'gemini', 'locale' => $lang],
                    ['hint' => $text]
                );
            }*/

            return response()->json([
                'chatgpt' => $chatgptHint->hint,
                //'gemini' => //$geminiHint->hint,
            ]);
        }

        $text = $data['question'];
        return response()->json([
            'chatgpt' => $gpt->hintSentenceStructure($text, $lang),
            'gemini' => $gemini->hintSentenceStructure($text, $lang),
        ]);
    }

    public function explain(Request $request, ChatGPTService $gpt)
    {
        $data = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'answer' => 'required|string',
        ]);

        $lang = 'uk'; // app()->getLocale();
        $question = Question::with('answers.option')->findOrFail($data['question_id']);
        $correct = $question->answers->first()->option->option ?? $question->answers->first()->answer ?? '';
        $given = trim($data['answer']);

        if (mb_strtolower($given) === mb_strtolower($correct)) {
            return response()->json(['correct' => true, 'explanation' => '']);
        }

        $explanation = $gpt->explainWrongAnswer($question->question, $given, $correct, $lang);

        return response()->json([
            'correct' => false,
            'explanation' => $explanation,
        ]);
    }
}
