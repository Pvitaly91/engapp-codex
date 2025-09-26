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

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'marker' => ['required', 'regex:/^a\d+$/i'],
            'value' => ['required', 'string', 'max:255'],
            'verb_hint' => ['nullable', 'string', 'max:255'],
        ]);

        $question = Question::findOrFail($data['question_id']);
        $marker = strtolower($data['marker']);

        $existing = QuestionAnswer::query()
            ->where('question_id', $question->id)
            ->whereRaw('LOWER(marker) = ?', [$marker])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'marker' => __('Для вибраного маркера вже існує відповідь.'),
            ]);
        }

        $value = trim($data['value']);
        $verbHintValue = isset($data['verb_hint']) ? trim($data['verb_hint']) : '';

        DB::transaction(function () use ($question, $marker, $value, $verbHintValue) {
            $answerOption = QuestionOption::firstOrCreate(['option' => $value]);

            $answerPivot = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $answerOption->id);

            if (Schema::hasColumn('question_option_question', 'flag')) {
                $answerPivot->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                });
            }

            if (! $answerPivot->exists()) {
                $attributes = [];

                if (Schema::hasColumn('question_option_question', 'flag')) {
                    $attributes['flag'] = 0;
                }

                $question->options()->attach($answerOption->id, $attributes);
            }

            QuestionAnswer::create([
                'question_id' => $question->id,
                'marker' => $marker,
                'option_id' => $answerOption->id,
            ]);

            if ($verbHintValue === '') {
                return;
            }

            $existingVerbHint = VerbHint::query()
                ->where('question_id', $question->id)
                ->whereRaw('LOWER(marker) = ?', [$marker])
                ->first();

            if ($existingVerbHint) {
                return;
            }

            $verbHintOption = QuestionOption::firstOrCreate(['option' => $verbHintValue]);

            $verbPivot = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $verbHintOption->id);

            if ($verbPivot->exists()) {
                if (Schema::hasColumn('question_option_question', 'flag')) {
                    $verbPivot->update(['flag' => 1]);
                }
            } else {
                $attributes = [];

                if (Schema::hasColumn('question_option_question', 'flag')) {
                    $attributes['flag'] = 1;
                }

                $question->options()->attach($verbHintOption->id, $attributes);
            }

            VerbHint::create([
                'question_id' => $question->id,
                'marker' => $marker,
                'option_id' => $verbHintOption->id,
            ]);
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

        DB::transaction(function () use ($questionAnswer, $question) {
            $option = $questionAnswer->relationLoaded('option')
                ? $questionAnswer->option
                : $questionAnswer->option()->first();

            $questionAnswer->delete();

            if (! $option || ! Schema::hasColumn('question_answers', 'option_id')) {
                return;
            }

            $otherAnswersExist = QuestionAnswer::query()
                ->where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->exists();

            $verbHintsForOptionExist = VerbHint::query()
                ->where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->exists();

            if (! $otherAnswersExist && ! $verbHintsForOptionExist) {
                $pivotQuery = DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->where('option_id', $option->id);

                if (Schema::hasColumn('question_option_question', 'flag')) {
                    $pivotQuery->where(function ($query) {
                        $query->whereNull('flag')->orWhere('flag', 0);
                    });
                }

                $pivotQuery->delete();
            }

            $stillUsed = QuestionAnswer::query()->where('option_id', $option->id)->exists()
                || VerbHint::query()->where('option_id', $option->id)->exists()
                || DB::table('question_option_question')->where('option_id', $option->id)->exists();

            if (! $stillUsed) {
                $option->delete();
            }
        });

        return $this->respondWithQuestion($request, $question);
    }
}
