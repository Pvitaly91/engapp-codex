<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question' => 'sometimes|required|string',
            'level' => 'sometimes|nullable|string|max:10',
        ]);

        if (! empty($data)) {
            $question->fill($data);

            if ($question->isDirty()) {
                $question->save();
            }
        }

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }
}
