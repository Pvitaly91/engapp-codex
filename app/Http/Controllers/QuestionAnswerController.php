<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class QuestionAnswerController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request, Question $question)
    {
        $data = $request->validate([
            'marker' => 'required|string|max:10',
            'value' => 'required|string|max:255',
        ]);

        $marker = strtoupper(trim($data['marker']));
        $value = trim($data['value']);

        $supportsOptionReference = Schema::hasColumn('question_answers', 'option_id');

        DB::transaction(function () use ($question, $marker, $value, $supportsOptionReference) {
            $answer = new QuestionAnswer([
                'marker' => $marker,
            ]);

            $answer->question()->associate($question);

            if ($supportsOptionReference) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);

                $exists = QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->where('marker', $marker)
                    ->where('option_id', $option->id)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'value' => 'Така відповідь вже існує.',
                    ]);
                }

                $pivotQuery = DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->where('option_id', $option->id);

                if (Schema::hasColumn('question_option_question', 'flag')) {
                    $pivotQuery->where(function ($query) {
                        $query->whereNull('flag')->orWhere('flag', 0);
                    });
                }

                if (! $pivotQuery->exists()) {
                    $question->options()->attach($option->id);
                }

                $answer->option()->associate($option);
            } else {
                $answer->answer = $value;
            }

            $answer->save();
        });

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, QuestionAnswer $questionAnswer)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value = trim($data['value']);

        $question = $questionAnswer->question;
        $currentOption = $questionAnswer->relationLoaded('option')
            ? $questionAnswer->option
            : $questionAnswer->option()->first();

        $supportsOptionReference = Schema::hasColumn('question_answers', 'option_id');

        if (! $supportsOptionReference) {
            if ($questionAnswer->answer === $value) {
                return $this->respondWithQuestion($request, $question);
            }

            $questionAnswer->answer = $value;
            $questionAnswer->save();

            return $this->respondWithQuestion($request, $question);
        }

        if ($currentOption && $currentOption->option === $value) {
            return $this->respondWithQuestion($request, $question);
        }

        DB::transaction(function () use ($questionAnswer, $question, $currentOption, $value) {
            $newOption = QuestionOption::firstOrCreate(['option' => $value]);

            $pivotQuery = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $newOption->id);

            if (Schema::hasColumn('question_option_question', 'flag')) {
                $pivotQuery->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                });
            }

            if (! $pivotQuery->exists()) {
                $question->options()->attach($newOption->id);
            }

            $questionAnswer->option()->associate($newOption);
            $questionAnswer->save();

            if ($currentOption && $currentOption->isNot($newOption)) {
                $otherAnswersExist = QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->where('id', '!=', $questionAnswer->id)
                    ->where('option_id', $currentOption->id)
                    ->exists();

                if (! $otherAnswersExist) {
                    $cleanupQuery = DB::table('question_option_question')
                        ->where('question_id', $question->id)
                        ->where('option_id', $currentOption->id);

                    if (Schema::hasColumn('question_option_question', 'flag')) {
                        $cleanupQuery->where(function ($query) {
                            $query->whereNull('flag')->orWhere('flag', 0);
                        });
                    }

                    $cleanupQuery->delete();
                }

                $stillUsed = QuestionAnswer::query()
                    ->where('option_id', $currentOption->id)
                    ->exists()
                    || VerbHint::query()->where('option_id', $currentOption->id)->exists()
                    || DB::table('question_option_question')
                        ->where('option_id', $currentOption->id)
                        ->exists();

                if (! $stillUsed) {
                    $currentOption->delete();
                }
            }
        });

        return $this->respondWithQuestion($request, $question);
    }

    public function destroy(Request $request, QuestionAnswer $questionAnswer)
    {
        $question = $questionAnswer->question;
        $option = $questionAnswer->relationLoaded('option')
            ? $questionAnswer->option
            : $questionAnswer->option()->first();

        DB::transaction(function () use ($questionAnswer, $question, $option) {
            $questionAnswer->delete();

            if ($option) {
                $otherAnswersExist = QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->where('option_id', $option->id)
                    ->exists();

                if (! $otherAnswersExist) {
                    DB::table('question_option_question')
                        ->where('question_id', $question->id)
                        ->where('option_id', $option->id)
                        ->when(Schema::hasColumn('question_option_question', 'flag'), function ($query) {
                            $query->where(function ($inner) {
                                $inner->whereNull('flag')->orWhere('flag', 0);
                            });
                        })
                        ->delete();
                }

                $stillUsed = QuestionAnswer::query()->where('option_id', $option->id)->exists()
                    || VerbHint::query()->where('option_id', $option->id)->exists()
                    || DB::table('question_option_question')->where('option_id', $option->id)->exists();

                if (! $stillUsed) {
                    $option->delete();
                }
            }
        });

        return $this->respondWithQuestion($request, $question);
    }
}
