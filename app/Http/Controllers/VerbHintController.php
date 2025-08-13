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

        $redirectTo = $request->input('from', url()->previous());

        if ($newHint === $option->option) {
            return redirect($redirectTo);
        }

        $isShared = $option->questions()
            ->where('questions.id', '!=', $question->id)
            ->exists();

        if ($isShared) {
            $newOption = QuestionOption::firstOrCreate(['option' => $newHint]);

            $question->options()->updateExistingPivot($option->id, ['flag' => 1]);
            $question->options()->attach($newOption->id, ['flag' => 1]);

            $verbHint->option_id = $newOption->id;
            $verbHint->save();
        } else {
            $existingOption = QuestionOption::where('option', $newHint)->first();

            if ($existingOption) {
                $question->options()->updateExistingPivot($option->id, ['flag' => 1]);
                $question->options()->attach($existingOption->id, ['flag' => 1]);

                $verbHint->option_id = $existingOption->id;
                $verbHint->save();

                $option->delete();
            } else {
                $option->option = $newHint;
                $option->save();
            }
        }

        return redirect($redirectTo);
    }
}
