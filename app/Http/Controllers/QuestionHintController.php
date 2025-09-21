<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\QuestionHint;
use Illuminate\Http\Request;

class QuestionHintController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function update(Request $request, QuestionHint $questionHint)
    {
        $data = $request->validate([
            'hint' => 'required|string',
        ]);

        $questionHint->hint = $data['hint'];
        $questionHint->save();

        return $this->respondWithQuestion($request, $questionHint->question);
    }
}
