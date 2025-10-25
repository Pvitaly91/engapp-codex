<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\QuestionVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionSeedingService
{
    /**
     * Mapping of legacy category identifiers to human readable names.
     *
     * Older seeders – especially those living under the Database\Seeders\V1
     * namespace – reference hard coded category IDs without creating the
     * underlying records. When we run those seeders in isolation (for example
     * while preparing the preview in /admin/seed-runs) the insert fails because
     * of the missing categories. By keeping the mapping here we can ensure the
     * necessary categories exist on the fly while still allowing newer seeders
     * to provide their own category metadata.
     */
    private const LEGACY_CATEGORY_NAMES = [
        1 => 'Past',
        2 => 'Present',
        3 => 'Present Continuous',
        4 => 'Future',
        5 => 'Present Perfect',
        6 => 'Conditionals',
    ];

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
            $existingQuestion = Question::where('uuid', $data['uuid'])->first();

            if ($existingQuestion) {
                if (
                    Schema::hasColumn('questions', 'seeder') &&
                    isset($data['seeder']) &&
                    empty($existingQuestion->seeder)
                ) {
                    $existingQuestion->forceFill(['seeder' => $data['seeder']])->save();
                }

                continue;
            }

            if (! empty($data['category_id'])) {
                $this->ensureCategoryExists((int) $data['category_id']);
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

            if (Schema::hasColumn('questions', 'seeder')) {
                $attributes['seeder'] = $data['seeder'] ?? null;
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

    private function ensureCategoryExists(int $categoryId): void
    {
        $name = self::LEGACY_CATEGORY_NAMES[$categoryId] ?? ('Legacy Category ' . $categoryId);

        Category::firstOrCreate(['id' => $categoryId], ['name' => $name]);
    }
}
