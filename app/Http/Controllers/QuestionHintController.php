<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\Question;
use App\Models\QuestionHint;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class QuestionHintController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request, Question $question)
    {
        $data = $request->validate([
            'provider' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'hint' => 'required|string',
        ]);

        $exists = $question->hints()
            ->where('provider', $data['provider'])
            ->where('locale', $data['locale'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'provider' => 'Підказка з таким провайдером та мовою вже існує.',
            ]);
        }

        $question->hints()->create($data);

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, QuestionHint $questionHint)
    {
        $data = $request->validate([
            'hint' => 'required|string',
        ]);

        $questionHint->hint = $data['hint'];
        $questionHint->save();

        return $this->respondWithQuestion($request, $questionHint->question);
    }

    public function destroy(Request $request, QuestionHint $questionHint)
    {
        $question = $questionHint->question;
        $questionHint->delete();

        return $this->respondWithQuestion($request, $question);
    }
}
