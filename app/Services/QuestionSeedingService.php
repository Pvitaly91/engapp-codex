<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\QuestionVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionSeedingService
{
    private function attachOption(Question $question, string $value, ?int $flag = null): QuestionOption
    {
        $option = QuestionOption::firstOrCreate(['option' => $value]);

        $exists = DB::table('question_option_question')
            ->where('question_id', $question->id)
            ->where('option_id', $option->id)
            ->where(function ($query) use ($flag) {
                if ($flag === null) {
                    $query->whereNull('flag');
                } else {
                    $query->where('flag', $flag);
                }
            })
            ->exists();

        if (! $exists) {
            $question->options()->attach($option->id, ['flag' => $flag]);
        }

        return $option;
    }

    public function seed(array $items): void
    {
        foreach ($items as $data) {
            if (Question::where('uuid', $data['uuid'])->exists()) {
                continue;
            }

            $attributes = [
                'uuid'        => $data['uuid'],
                'question'    => $data['question'],
                'category_id' => $data['category_id'] ?? null,
                'difficulty'  => $data['difficulty'] ?? 1,
                'source_id'   => $data['source_id'] ?? null,
                'flag'        => $data['flag'] ?? 0,
            ];

            if (Schema::hasColumn('questions', 'level')) {
                $attributes['level'] = $data['level'] ?? null;
            }

            $q = Question::create($attributes);

            foreach ($data['answers'] as $ans) {
                $option = $this->attachOption($q, $ans['answer']);
                QuestionAnswer::firstOrCreate([
                    'question_id' => $q->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $option->id,
                ]);
                if (!empty($ans['verb_hint'])) {
                    $hintOption = $this->attachOption($q, $ans['verb_hint'], 1);
                    VerbHint::firstOrCreate([
                        'question_id' => $q->id,
                        'marker'      => $ans['marker'],
                        'option_id'   => $hintOption->id,
                    ]);
                }
            }

            if (! empty($data['options'] ?? [])) {
                foreach ($data['options'] as $opt) {
                    $this->attachOption($q, $opt);
                }
            }

            if (! empty($data['variants'] ?? [])) {
                foreach ($data['variants'] as $variantText) {
                    if (! $variantText) {
                        continue;
                    }

                    QuestionVariant::firstOrCreate([
                        'question_id' => $q->id,
                        'text' => $variantText,
                    ]);
                }
            }

            if (! empty($data['tag_ids'] ?? [])) {
                $q->tags()->syncWithoutDetaching($data['tag_ids']);
            }
        }
    }
}
