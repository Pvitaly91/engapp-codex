<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\{VerbHint, QuestionOption, Question};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerbHintController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'marker' => 'required|string|max:255',
            'hint' => 'required|string|max:255',
        ]);
        $question = Question::findOrFail($data['question_id']);
        $option = QuestionOption::firstOrCreate(['option' => $data['hint']]);

        $pivot = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id);

        if ($pivot->exists()) {
            $pivot->update(['flag' => 1]);
        } else {
            $question->options()->attach($option->id, ['flag' => 1]);
        }

        $verbHint = VerbHint::create([
            'question_id' => $question->id,
            'marker' => $data['marker'],
            'option_id' => $option->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['id' => $verbHint->id], 201);
        }

        $redirectTo = $request->input('from', url()->previous());

        return redirect($redirectTo);
    }

    public function update(Request $request, VerbHint $verbHint)
    {
        $request->validate([
            'hint' => 'required|string|max:255',
        ]);
        $option = $verbHint->option;
        $question = $verbHint->question;
        $newHint = $request->input('hint');

        if ($newHint === $option->option) {
            return $this->respondWithQuestion($request, $question);
        }

        $isShared = $option->questions()
            ->where('questions.id', '!=', $question->id)
            ->exists();

        if ($isShared) {
            $newOption = QuestionOption::firstOrCreate(['option' => $newHint]);

            $question->options()->detach($option->id);

            $pivot = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $newOption->id);
            if ($pivot->exists()) {
                $pivot->update(['flag' => 1]);
            } else {
                $question->options()->attach($newOption->id, ['flag' => 1]);
            }

            $verbHint->option_id = $newOption->id;
            $verbHint->save();
        } else {
            $existingOption = QuestionOption::where('option', $newHint)->first();

            if ($existingOption) {
                $question->options()->detach($option->id);

                $pivot = DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->where('option_id', $existingOption->id);
                if ($pivot->exists()) {
                    $pivot->update(['flag' => 1]);
                } else {
                    $question->options()->attach($existingOption->id, ['flag' => 1]);
                }

                $verbHint->option_id = $existingOption->id;
                $verbHint->save();

                $option->delete();
            } else {
                $option->option = $newHint;
                $option->save();
            }
        }

        return $this->respondWithQuestion($request, $question);
    }

    public function destroy(Request $request, VerbHint $verbHint)
    {
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

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        $redirectTo = $request->input('from', url()->previous());

        return redirect($redirectTo);
    }
}
