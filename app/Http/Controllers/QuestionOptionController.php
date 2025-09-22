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

class QuestionOptionController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request, Question $question)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value = trim($data['value']);

        DB::transaction(function () use ($question, $value) {
            $option = QuestionOption::firstOrCreate(['option' => $value]);

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
        });

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, Question $question, QuestionOption $option)
    {
        $data = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value = trim($data['value']);

        $pivotQuery = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id);

        if (Schema::hasColumn('question_option_question', 'flag')) {
            $pivotQuery->where(function ($query) {
                $query->whereNull('flag')->orWhere('flag', 0);
            });
        }

        if (! $pivotQuery->exists()) {
            abort(404);
        }

        if ($option->option === $value) {
            return $this->respondWithQuestion($request, $question);
        }

        DB::transaction(function () use ($question, $option, $value) {
            $newOption = QuestionOption::firstOrCreate(['option' => $value]);

            $attachQuery = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $newOption->id);

            if (Schema::hasColumn('question_option_question', 'flag')) {
                $attachQuery->where(function ($query) {
                    $query->whereNull('flag')->orWhere('flag', 0);
                });
            }

            if (! $attachQuery->exists()) {
                $question->options()->attach($newOption->id);
            }

            $hasOptionColumn = Schema::hasColumn('question_answers', 'option_id');

            if ($hasOptionColumn) {
                QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->where('option_id', $option->id)
                    ->update(['option_id' => $newOption->id]);
            }

            DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->when(Schema::hasColumn('question_option_question', 'flag'), function ($query) {
                    $query->where(function ($inner) {
                        $inner->whereNull('flag')->orWhere('flag', 0);
                    });
                })
                ->delete();

            $stillUsed = QuestionAnswer::query()->where('option_id', $option->id)->exists()
                || VerbHint::query()->where('option_id', $option->id)->exists()
                || DB::table('question_option_question')->where('option_id', $option->id)->exists();

            if (! $stillUsed) {
                $option->delete();
            }
        });

        return $this->respondWithQuestion($request, $question);
    }

    public function destroy(Request $request, Question $question, QuestionOption $option)
    {
        $pivotQuery = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id);

        if (Schema::hasColumn('question_option_question', 'flag')) {
            $pivotQuery->where(function ($query) {
                $query->whereNull('flag')->orWhere('flag', 0);
            });
        }

        if (! $pivotQuery->exists()) {
            abort(404);
        }

        $isAnswerOption = QuestionAnswer::query()
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->exists();

        if ($isAnswerOption) {
            throw ValidationException::withMessages([
                'value' => 'Неможливо видалити опцію, що використовується у відповіді.',
            ]);
        }

        $hasVerbHint = VerbHint::query()
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->exists();

        if ($hasVerbHint) {
            throw ValidationException::withMessages([
                'value' => 'Неможливо видалити опцію, що використовується у verb hint.',
            ]);
        }

        DB::transaction(function () use ($question, $option) {
            DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->where('option_id', $option->id)
                ->when(Schema::hasColumn('question_option_question', 'flag'), function ($query) {
                    $query->where(function ($inner) {
                        $inner->whereNull('flag')->orWhere('flag', 0);
                    });
                })
                ->delete();

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
