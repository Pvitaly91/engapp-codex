<?php

namespace App\Http\Controllers;

use App\Models\QuestionHint;
use Illuminate\Http\Request;

class QuestionHintController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'provider' => 'required|string|max:255',
            'locale' => 'required|string|max:5',
            'hint' => 'required|string',
        ]);

        $hint = QuestionHint::create($data);

        if ($request->expectsJson()) {
            return response()->json(['id' => $hint->id], 201);
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function update(Request $request, QuestionHint $questionHint)
    {
        $data = $request->validate([
            'provider' => 'required|string|max:255',
            'locale' => 'required|string|max:5',
            'hint' => 'required|string',
        ]);

        $questionHint->update($data);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function destroy(Request $request, QuestionHint $questionHint)
    {
        $questionHint->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
