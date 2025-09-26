<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\Question;
use App\Services\QuestionExportService;
use Illuminate\Http\Request;

trait ReturnsTechnicalQuestionResource
{
    protected function respondWithQuestion(Request $request, Question $question)
    {
        $question = $question->fresh() ?? $question;

        app(QuestionExportService::class)->export($question);

        if ($request->wantsJson()) {
            return TechnicalQuestionResource::make($question);
        }

        return redirect($request->input('from', url()->previous()));
    }
}
