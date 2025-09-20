<?php

namespace App\Http\Controllers;

use App\Models\QuestionVariant;
use Illuminate\Http\Request;

class QuestionVariantController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'text' => 'required|string',
        ]);

        $variant = QuestionVariant::create($data);

        if ($request->expectsJson()) {
            return response()->json(['id' => $variant->id], 201);
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function update(Request $request, QuestionVariant $questionVariant)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $questionVariant->text = $data['text'];
        $questionVariant->save();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }

    public function destroy(Request $request, QuestionVariant $questionVariant)
    {
        $questionVariant->delete();

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
