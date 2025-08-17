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
            'question_id' => 'required|integer|exists:questions,id',
            'refresh' => 'sometimes|boolean',
        ]);

        $question = Question::findOrFail($data['question_id']);
        $lang = "uk";//app()->getLocale();
        $refresh = $data['refresh'] ?? false;

        $chatgptHint = QuestionHint::where('question_id', $question->id)
            ->where('provider', 'chatgpt')
            ->where('locale', $lang)
            ->first();
        if (!$chatgptHint || $refresh) {
            $text = $gpt->hintSentenceStructure($question->renderQuestionText(), $lang);
            $chatgptHint = QuestionHint::updateOrCreate(
                ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => $lang],
                ['hint' => $text]
            );
        }

        $geminiHint = QuestionHint::where('question_id', $question->id)
            ->where('provider', 'gemini')
            ->where('locale', $lang)
            ->first();
        if (!$geminiHint || $refresh) {
            $text = $gemini->hintSentenceStructure($question->renderQuestionText(), $lang);
            $geminiHint = QuestionHint::updateOrCreate(
                ['question_id' => $question->id, 'provider' => 'gemini', 'locale' => $lang],
                ['hint' => $text]
            );
        }

        return response()->json([
            'chatgpt' => $chatgptHint->hint,
            'gemini' => $geminiHint->hint,
        ]);
    }
}
