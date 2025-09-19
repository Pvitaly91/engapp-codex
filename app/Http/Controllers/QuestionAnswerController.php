<?php

namespace App\Http\Controllers;

use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionAnswerController extends Controller
{
    public function update(Request $request, QuestionAnswer $questionAnswer)
    {
        $data = $request->validate([
            'marker' => 'required|string|max:10',
            'value' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($questionAnswer, $data) {
            $question = $questionAnswer->question()->lockForUpdate()->first();
            $oldOption = $questionAnswer->option;

            $marker = strtolower($data['marker']);
            $value = trim($data['value']);

            $option = QuestionOption::firstOrCreate(['option' => $value]);

            $questionAnswer->marker = $marker;
            $questionAnswer->option_id = $option->id;
            $questionAnswer->save();

            $pivotExists = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                })
                ->exists();

            if (! $pivotExists) {
                $question->options()->attach($option->id, ['flag' => 0]);
            }

            if ($oldOption && $oldOption->id !== $option->id) {
                $otherAnswersUse = $question->answers()
                    ->where('id', '!=', $questionAnswer->id)
                    ->where('option_id', $oldOption->id)
                    ->exists();

                $verbHintsUse = $question->verbHints()
                    ->where('option_id', $oldOption->id)
                    ->exists();

                if (! $otherAnswersUse && ! $verbHintsUse) {
                    DB::table('question_option_question')
                        ->where('question_id', $question->id)
                        ->where('option_id', $oldOption->id)
                        ->where(function ($query) {
                            $query->whereNull('flag')->orWhere('flag', 0);
                        })
                        ->delete();
                }

                $optionUsedElsewhere = $oldOption->answers()
                    ->where('question_id', '!=', $question->id)
                    ->exists()
                    || $oldOption->verbHints()
                        ->where('question_id', '!=', $question->id)
                        ->exists()
                    || DB::table('question_option_question')
                        ->where('option_id', $oldOption->id)
                        ->where('question_id', '!=', $question->id)
                        ->exists();

                if (! $otherAnswersUse && ! $verbHintsUse && ! $optionUsedElsewhere) {
                    $oldOption->delete();
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
