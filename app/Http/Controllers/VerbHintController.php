<?php

namespace App\Http\Controllers;

use App\Models\{VerbHint, QuestionOption, Question};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerbHintController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'marker' => 'required|string|max:255',
        ]);

        $from = $request->query('from', url()->previous());
        $verbHint = new VerbHint([
            'question_id' => $request->query('question_id'),
            'marker' => $request->query('marker'),
        ]);

        return view('verb-hint-edit', compact('verbHint', 'from'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'marker' => 'required|string|max:255',
            'hint' => 'required|string|max:255',
        ]);
        $redirectTo = $request->input('from', url()->previous());

        $question = Question::findOrFail($data['question_id']);
        $option = QuestionOption::firstOrCreate(['option' => $data['hint']]);

        $exists = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where('flag', 1)
            ->exists();
        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => 1]);
        }

        VerbHint::create([
            'question_id' => $question->id,
            'marker' => $data['marker'],
            'option_id' => $option->id,
        ]);

        return redirect($redirectTo);
    }

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

    public function destroy(Request $request, VerbHint $verbHint)
    {
        $redirectTo = $request->input('from', url()->previous());

        $option = $verbHint->option;
        $question = $verbHint->question;

        $verbHint->delete();

        DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where('flag', 1)
            ->delete();

        if (! $option->verbHints()->exists()
            && ! DB::table('question_option_question')->where('option_id', $option->id)->exists()) {
            $option->delete();
        }

        return redirect($redirectTo);
    }
}
