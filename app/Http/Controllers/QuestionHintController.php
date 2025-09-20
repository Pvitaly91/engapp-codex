<?php

namespace App\Http\Controllers;

use App\Models\QuestionHint;
use Illuminate\Http\Request;

class QuestionHintController extends Controller
{
    public function update(Request $request, QuestionHint $questionHint)
    {
        $data = $request->validate([
            'hint' => 'required|string',
        ]);

        $questionHint->hint = $data['hint'];
        $questionHint->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
