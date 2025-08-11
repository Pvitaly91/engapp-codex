<?php

namespace App\Http\Controllers;

use App\Models\VerbHint;
use App\Models\QuestionOption;
use Illuminate\Http\Request;

class VerbHintController extends Controller
{
    public function edit(Request $request, VerbHint $verbHint)
    {
        $verbHint->load('option');
        $from = $request->query('from', url()->previous());

        return view('verb-hint-edit', compact('verbHint', 'from'));
    }

    public function update(Request $request, VerbHint $verbHint)
    {
        $request->validate([
            'hint' => 'required|string|max:255',
        ]);
        $option = $verbHint->option;
        $question = $verbHint->question;
        $newHint = $request->input('hint');

        $isShared = $option->questions()
            ->where('questions.id', '!=', $question->id)
            ->exists();

        if ($isShared) {
            $newOption = new QuestionOption(['option' => $newHint]);
            $newOption->question_id = $question->id;
            $newOption->save();

            $question->options()->updateExistingPivot($option->id, ['flag' => 1]);
            $question->options()->attach($newOption->id);

            $verbHint->option_id = $newOption->id;
            $verbHint->save();
        } else {
            $option->option = $newHint;
            $option->save();
        }

        $redirectTo = $request->input('from', url()->previous());
        return redirect($redirectTo);
    }
}
