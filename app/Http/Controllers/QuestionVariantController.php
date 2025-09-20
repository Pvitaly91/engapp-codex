<?php

namespace App\Http\Controllers;

use App\Models\QuestionVariant;
use Illuminate\Http\Request;

class QuestionVariantController extends Controller
{
    public function update(Request $request, QuestionVariant $questionVariant)
    {
        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $questionVariant->text = $data['text'];
        $questionVariant->save();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
