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
            'level' => 'nullable|string|max:10',
        ]);

        $question->question = $data['question'];

        if ($request->has('level')) {
            $question->level = $data['level'] !== '' ? $data['level'] : null;
        }

        $question->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->back();
    }
}
