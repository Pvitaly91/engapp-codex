<?php

namespace App\Http\Controllers;

use App\Models\ChatGPTExplanation;
use Illuminate\Http\Request;

class ChatGPTExplanationController extends Controller
{
    public function update(Request $request, ChatGPTExplanation $chatgptExplanation)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'wrong_answer' => 'nullable|string|max:255',
            'correct_answer' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'explanation' => 'required|string',
        ]);

        $chatgptExplanation->update($data);

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
