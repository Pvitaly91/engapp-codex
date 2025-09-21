<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\QuestionVariant;
use Illuminate\Http\Request;

class QuestionVariantController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function update(Request $request, QuestionVariant $questionVariant)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $questionVariant->text = $data['text'];
        $questionVariant->save();

        return $this->respondWithQuestion($request, $questionVariant->question);
    }
}
