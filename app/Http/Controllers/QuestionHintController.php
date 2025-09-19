<?php

namespace App\Http\Controllers;

use App\Models\QuestionHint;
use Illuminate\Http\Request;

class QuestionHintController extends Controller
{
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
}
