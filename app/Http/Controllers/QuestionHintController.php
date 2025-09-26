<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\Question;
use App\Models\QuestionHint;
use Illuminate\Http\Request;

class QuestionHintController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'provider' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:5'],
            'hint' => ['required', 'string'],
        ]);

        $question = Question::findOrFail($data['question_id']);

        $question->hints()->create([
            'provider' => trim($data['provider']),
            'locale' => strtolower(trim($data['locale'])),
            'hint' => $data['hint'],
        ]);

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, QuestionHint $questionHint)
    {
        $data = $request->validate([
            'hint' => ['sometimes', 'required', 'string'],
            'provider' => ['sometimes', 'required', 'string', 'max:255'],
            'locale' => ['sometimes', 'required', 'string', 'max:5'],
        ]);

        if (array_key_exists('hint', $data)) {
            $questionHint->hint = $data['hint'];
        }

        if (array_key_exists('provider', $data)) {
            $questionHint->provider = trim($data['provider']);
        }

        if (array_key_exists('locale', $data)) {
            $questionHint->locale = strtolower(trim($data['locale']));
        }

        if ($questionHint->isDirty()) {
            $questionHint->save();
        }

        return $this->respondWithQuestion($request, $questionHint->question);
    }
}
