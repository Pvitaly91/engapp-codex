<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReturnsTechnicalQuestionResource;
use App\Models\{VerbHint, QuestionOption, Question};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class VerbHintController extends Controller
{
    use ReturnsTechnicalQuestionResource;

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'marker' => ['required', 'regex:/^a\d+$/i'],
            'hint' => ['required', 'string', 'max:255'],
        ]);

        $question = Question::findOrFail($data['question_id']);
        $marker = strtolower($data['marker']);

        $hasAnswerWithMarker = $question->answers()
            ->whereRaw('LOWER(marker) = ?', [$marker])
            ->exists();

        if (! $hasAnswerWithMarker) {
            throw ValidationException::withMessages([
                'marker' => __('Для вибраного маркера немає відповіді.'),
            ]);
        }

        $alreadyHasHint = $question->verbHints()
            ->whereRaw('LOWER(marker) = ?', [$marker])
            ->exists();

        if ($alreadyHasHint) {
            throw ValidationException::withMessages([
                'marker' => __('Verb hint для цього маркера вже існує.'),
            ]);
        }

        $hintValue = trim($data['hint']);

        $option = QuestionOption::firstOrCreate(['option' => $hintValue]);

        $pivotQuery = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id);

        $pivotExists = $pivotQuery->exists();
        $hasFlagColumn = Schema::hasColumn('question_option_question', 'flag');

        if (! $pivotExists) {
            $attributes = [];

            if ($hasFlagColumn) {
                $attributes['flag'] = 1;
            }

            $question->options()->attach($option->id, $attributes);
        } elseif ($hasFlagColumn) {
            $pivotQuery->update(['flag' => 1]);
        }

        VerbHint::query()->create([
            'question_id' => $question->id,
            'marker' => $marker,
            'option_id' => $option->id,
        ]);

        return $this->respondWithQuestion($request, $question);
    }

    public function update(Request $request, VerbHint $verbHint)
    {
        $data = $request->validate([
            'hint' => ['required', 'string', 'max:255'],
        ]);

        $option = $verbHint->option;
        $question = $verbHint->question;
        $newHint = trim($data['hint']);

        if ($newHint === $option->option) {
            return $this->respondWithQuestion($request, $question);
        }

        $hasFlagColumn = Schema::hasColumn('question_option_question', 'flag');

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
                if ($hasFlagColumn) {
                    $pivot->update(['flag' => 1]);
                }
            } else {
                $attributes = [];

                if ($hasFlagColumn) {
                    $attributes['flag'] = 1;
                }

                $question->options()->attach($newOption->id, $attributes);
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
                    if ($hasFlagColumn) {
                        $pivot->update(['flag' => 1]);
                    }
                } else {
                    $attributes = [];

                    if ($hasFlagColumn) {
                        $attributes['flag'] = 1;
                    }

                    $question->options()->attach($existingOption->id, $attributes);
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

        $pivotQuery = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id);

        if (Schema::hasColumn('question_option_question', 'flag')) {
            $pivotQuery->where('flag', 1);
        }

        $pivotQuery->delete();

        if (! $option->verbHints()->exists()
            && ! DB::table('question_option_question')->where('option_id', $option->id)->exists()) {
            $option->delete();
        }

        return $this->respondWithQuestion($request, $question);
    }
}
