<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Services\{ChatGPTService, GeminiService};

class QuestionHelpController extends Controller
{
    public function hint(Request $request, ChatGPTService $gpt, GeminiService $gemini)
    {
        $data = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'provider' => 'required|in:chatgpt,gemini',
        ]);

        $question = Question::findOrFail($data['question_id']);
        $hint = $data['provider'] === 'gemini'
            ? $gemini->hintSentenceStructure($question->question)
            : $gpt->hintSentenceStructure($question->question);

        return response()->json(['hint' => $hint]);
    }
}
