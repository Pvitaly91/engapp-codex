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
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'question_text' => ['required', 'string'],
            'wrong_answer' => ['nullable', 'string'],
            'correct_answer' => ['required', 'string'],
            'language' => ['required', 'string', 'max:10'],
            'explanation' => ['required', 'string'],
        ]);

        $question = Question::findOrFail($data['question_id']);

        $questionText = trim($data['question_text']);
        $wrongAnswer = trim((string) ($data['wrong_answer'] ?? ''));
        $correctAnswer = trim($data['correct_answer']);
        $language = strtolower(trim($data['language']));
        $explanationText = trim($data['explanation']);

        if ($questionText === '' || $correctAnswer === '' || $explanationText === '' || $language === '') {
            return response()->json([
                'message' => 'Усі обовʼязкові поля мають бути заповнені.',
            ], 422);
        }

        $exists = ChatGPTExplanation::query()
            ->where('question', $questionText)
            ->where('correct_answer', $correctAnswer)
            ->where('wrong_answer', $wrongAnswer)
            ->where('language', $language)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Таке пояснення вже існує.',
            ], 422);
        }

        $explanation = ChatGPTExplanation::create([
            'question' => $questionText,
            'wrong_answer' => $wrongAnswer,
            'correct_answer' => $correctAnswer,
            'language' => $language,
            'explanation' => $explanationText,
        ]);

        return response()->json([
            'message' => 'Пояснення збережено.',
            'data' => [
                'id' => $explanation->id,
                'question_id' => $question->id,
                'question' => $explanation->question,
                'wrong_answer' => $explanation->wrong_answer,
                'correct_answer' => $explanation->correct_answer,
                'language' => $explanation->language,
                'explanation' => $explanation->explanation,
            ],
        ], 201);
    }
}
