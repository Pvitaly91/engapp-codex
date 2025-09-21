<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

trait ReturnsTechnicalQuestionResource
{
    protected function respondWithQuestion(Request $request, Question $question)
    {
        if ($request->wantsJson()) {
            return TechnicalQuestionResource::make($question->refresh());
        }

        return redirect($request->input('from', url()->previous()));
    }
}
