<?php

namespace App\Http\Controllers;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use Illuminate\Http\Request;

class ChatGPTExplanationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'question' => 'nullable|string',
            'wrong_answer' => 'nullable|string|max:255',
            'correct_answer' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'explanation' => 'required|string',
        ]);

        $question = Question::findOrFail($data['question_id']);

        $explanation = ChatGPTExplanation::create([
            'question' => $data['question'] ?? $question->question,
            'wrong_answer' => $data['wrong_answer'] ?? null,
            'correct_answer' => $data['correct_answer'],
            'language' => $data['language'],
            'explanation' => $data['explanation'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $explanation->id], 201);
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function update(Request $request, ChatGPTExplanation $chatgptExplanation)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'wrong_answer' => 'nullable|string|max:255',
            'correct_answer' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'explanation' => 'required|string',
        ]);

        $chatgptExplanation->update($data);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function destroy(Request $request, ChatGPTExplanation $chatgptExplanation)
    {
        $chatgptExplanation->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
