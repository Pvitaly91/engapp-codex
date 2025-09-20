<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionAnswerController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'marker' => [
                'required',
                'string',
                'max:10',
                Rule::unique('question_answers', 'marker')->where(fn ($query) => $query->where('question_id', $request->input('question_id'))),
            ],
            'value' => 'required|string|max:255',
        ]);

        $answer = null;

        DB::transaction(function () use (&$answer, $data) {
            $question = Question::lockForUpdate()->findOrFail($data['question_id']);
            $marker = strtolower($data['marker']);
            $value = trim($data['value']);

            $option = QuestionOption::firstOrCreate(['option' => $value]);

            $answer = $question->answers()->create([
                'marker' => $marker,
                'option_id' => $option->id,
            ]);

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
        });

        if ($request->expectsJson()) {
            return response()->json(['id' => $answer->id], 201);
        }

        return redirect($request->input('from', url()->previous()));
    }

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

    public function destroy(Request $request, QuestionAnswer $questionAnswer)
    {
        DB::transaction(function () use ($questionAnswer) {
            $question = $questionAnswer->question()->lockForUpdate()->first();
            $option = $questionAnswer->option;

            $questionAnswer->delete();

            $otherAnswersUse = $question->answers()
                ->where('option_id', $option->id)
                ->exists();

            $verbHintsUse = $question->verbHints()
                ->where('option_id', $option->id)
                ->exists();

            if (! $otherAnswersUse && ! $verbHintsUse) {
                DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->where('option_id', $option->id)
                    ->where(function ($query) {
                        $query->whereNull('flag')->orWhere('flag', 0);
                    })
                    ->delete();
            }

            $optionUsedElsewhere = $option->answers()
                ->where('question_id', '!=', $question->id)
                ->exists()
                || $option->verbHints()
                    ->where('question_id', '!=', $question->id)
                    ->exists()
                || DB::table('question_option_question')
                    ->where('option_id', $option->id)
                    ->where('question_id', '!=', $question->id)
                    ->exists();

            if (! $otherAnswersUse && ! $verbHintsUse && ! $optionUsedElsewhere) {
                $option->delete();
            }
        });

        if ($request->expectsJson()) {
            return response()->noContent();
        }

        return redirect($request->input('from', url()->previous()));
    }
}
