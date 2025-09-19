<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionOptionController extends Controller
{
    public function update(Request $request, QuestionOption $questionOption)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option' => 'required|string|max:255',
        ]);

        $question = Question::findOrFail($data['question_id']);
        $newValue = trim($data['option']);

        if ($questionOption->option === $newValue) {
            return $request->expectsJson()
                ? response()->noContent()
                : redirect($request->input('from', url()->previous()));
        }

        DB::transaction(function () use ($question, $questionOption, $newValue) {
            $isExclusive = ! $questionOption->answers()
                    ->where('question_id', '!=', $question->id)
                    ->exists()
                && ! $questionOption->verbHints()
                    ->where('question_id', '!=', $question->id)
                    ->exists()
                && ! DB::table('question_option_question')
                    ->where('option_id', $questionOption->id)
                    ->where('question_id', '!=', $question->id)
                    ->exists();

            if ($isExclusive) {
                $questionOption->option = $newValue;
                $questionOption->save();

                return;
            }

            $targetOption = QuestionOption::firstOrCreate(['option' => $newValue]);

            if ($targetOption->id === $questionOption->id) {
                $questionOption->option = $newValue;
                $questionOption->save();

                return;
            }

            QuestionAnswer::where('question_id', $question->id)
                ->where('option_id', $questionOption->id)
                ->update(['option_id' => $targetOption->id]);

            VerbHint::where('question_id', $question->id)
                ->where('option_id', $questionOption->id)
                ->update(['option_id' => $targetOption->id]);

            DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $questionOption->id)
                ->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                })
                ->delete();

            $pivotExists = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $targetOption->id)
                ->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                })
                ->exists();

            if (! $pivotExists) {
                $question->options()->attach($targetOption->id, ['flag' => 0]);
            }

            $stillUsed = $questionOption->answers()->exists()
                || $questionOption->verbHints()->exists()
                || DB::table('question_option_question')
                    ->where('option_id', $questionOption->id)
                    ->exists();

            if (! $stillUsed) {
                $questionOption->delete();
            }
        });

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
