<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Services\{ChatGPTService, GeminiService};
use Illuminate\Support\Facades\Cache;

class QuestionHelpController extends Controller
{
    public function hint(Request $request, ChatGPTService $gpt, GeminiService $gemini)
    {
        $data = $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
        ]);

        $question = Question::findOrFail($data['question_id']);
        $lang = app()->getLocale();

        $ttl = now()->addDay();

        $chatgptHint = Cache::remember(
            "question_hint_chatgpt_{$question->id}",
            $ttl,
            fn () => $gpt->hintSentenceStructure($question->question, $lang)
        );

        $geminiHint = Cache::remember(
            "question_hint_gemini_{$lang}_{$question->id}",
            $ttl,
            fn () => $gemini->hintSentenceStructure($question->question, $lang)
        );

        return response()->json([
            'chatgpt' => $chatgptHint,
            'gemini' => $geminiHint,
        ]);
    }
}
