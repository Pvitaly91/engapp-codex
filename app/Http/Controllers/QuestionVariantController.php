<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\Question;
use App\Models\QuestionVariant;
use Illuminate\Http\Request;

class QuestionVariantController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request, Question $question)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $variant = new QuestionVariant([
            'text' => $data['text'],
        ]);
        $variant->question()->associate($question);
        $variant->save();

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, QuestionVariant $questionVariant)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $questionVariant->text = $data['text'];
        $questionVariant->save();

        return $this->respondWithQuestion($request, $questionVariant->question);
    }

    public function destroy(Request $request, QuestionVariant $questionVariant)
    {
        $question = $questionVariant->question;
        $questionVariant->delete();

        return $this->respondWithQuestion($request, $question);
    }
}
