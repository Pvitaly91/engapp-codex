<?php

namespace App\Http\Controllers;

use App\Http\Resources\TechnicalQuestionResource;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question' => 'sometimes|required|string',
            'level' => 'sometimes|nullable|string|in:A1,A2,B1,B2,C1,C2',
        ]);

        if (! empty($data)) {
            $question->fill($data);

            if ($question->isDirty()) {
                $question->save();
            }
        }

        if ($request->wantsJson()) {
            return TechnicalQuestionResource::make($question->refresh());
        }

        return redirect()->back();
    }
}
