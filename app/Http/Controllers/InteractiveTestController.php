<?php

namespace App\Http\Controllers;

use App\Models\InteractiveTest;

class InteractiveTestController extends Controller
{
    public function show(InteractiveTest $interactiveTest)
    {
        if ($interactiveTest->type !== 'question_words') {
            abort(404);
        }

        $questions = collect($interactiveTest->data['questions'] ?? [])
            ->map(fn (array $question) => [
                'template' => $question['template'] ?? '_____',
                'answer' => $question['answer'] ?? '',
                'tail' => $question['tail'] ?? '',
            ])
            ->values()
            ->all();

        return view('question-words.drag-drop', [
            'test' => $interactiveTest,
            'questions' => $questions,
        ]);
    }
}
