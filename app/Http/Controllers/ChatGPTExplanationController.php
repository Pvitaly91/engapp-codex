<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatGPTExplanationController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'language' => ['required', 'string', 'max:10'],
            'correct_answer' => ['required', 'string'],
            'wrong_answer' => ['nullable', 'string'],
            'explanation' => ['required', 'string'],
        ]);

        $question = Question::findOrFail($data['question_id']);

        $language = Str::lower($data['language']);
        $wrongAnswer = $data['wrong_answer'] ?? '';

        ChatGPTExplanation::updateOrCreate(
            [
                'question' => trim((string) $question->question),
                'wrong_answer' => $wrongAnswer,
                'correct_answer' => $data['correct_answer'],
                'language' => $language,
            ],
            [
                'explanation' => $data['explanation'],
            ]
        );

        return $this->respondWithQuestion($request, $question);
    }
}
