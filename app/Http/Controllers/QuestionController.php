<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'question' => 'required|string',
        ]);

        $question->question = $data['question'];
        $question->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }
}
