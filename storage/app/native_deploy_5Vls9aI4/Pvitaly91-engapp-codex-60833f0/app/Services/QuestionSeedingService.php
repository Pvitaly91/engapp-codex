<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\VerbHint;
use App\Models\QuestionVariant;
use Illuminate\Database\Seeder as LaravelSeeder;
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
        $defaultSeederClass = $this->detectSeederClassName();

        foreach ($items as $data) {
            DB::transaction(function () use ($data, $defaultSeederClass) {
                $seederClass = $data['seeder'] ?? $defaultSeederClass;

                $question = $this->locateExistingQuestion($data);

                if (! empty($data['category_id'])) {
                    $this->ensureCategoryExists((int) $data['category_id']);
                }

                $attributes = $this->buildQuestionAttributes($data, $seederClass);

                if ($question) {
                    $question->forceFill($attributes)->save();
                    $this->recreateQuestionRelations($question, $data);

                    return;
                }

                $question = Question::create($attributes);
                $this->recreateQuestionRelations($question, $data);
            });
        }
    }

    private function locateExistingQuestion(array $data): ?Question
    {
        $question = Question::where('uuid', $data['uuid'])->first();

        if ($question) {
            return $question;
        }

        return Question::where('question', $data['question'])->first();
    }

    private function buildQuestionAttributes(array $data, ?string $seederClass): array
    {
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
            $attributes['seeder'] = $seederClass;
        }

        return $attributes;
    }

    private function recreateQuestionRelations(Question $question, array $data): void
    {
        QuestionAnswer::where('question_id', $question->id)->delete();
        VerbHint::where('question_id', $question->id)->delete();
        QuestionVariant::where('question_id', $question->id)->delete();
        DB::table('question_option_question')->where('question_id', $question->id)->delete();

        foreach ($data['answers'] as $ans) {
            $option = $this->attachOption($question, $ans['answer']);

            QuestionAnswer::create([
                'question_id' => $question->id,
                'marker'      => $ans['marker'],
                'option_id'   => $option->id,
            ]);

            if (! empty($ans['verb_hint'])) {
                $hintOption = $this->attachOption($question, $ans['verb_hint'], 1);

                VerbHint::create([
                    'question_id' => $question->id,
                    'marker'      => $ans['marker'],
                    'option_id'   => $hintOption->id,
                ]);
            }
        }

        foreach ($data['options'] ?? [] as $opt) {
            $this->attachOption($question, $opt);
        }

        foreach ($data['variants'] ?? [] as $variantText) {
            if (! $variantText) {
                continue;
            }

            QuestionVariant::create([
                'question_id' => $question->id,
                'text' => $variantText,
            ]);
        }

        $question->tags()->sync($data['tag_ids'] ?? []);
    }

    private function ensureCategoryExists(int $categoryId): void
    {
        $name = self::LEGACY_CATEGORY_NAMES[$categoryId] ?? ('Legacy Category ' . $categoryId);

        Category::firstOrCreate(['id' => $categoryId], ['name' => $name]);
    }

    private function detectSeederClassName(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 25);

        foreach ($trace as $frame) {
            $object = $frame['object'] ?? null;

            if ($object instanceof LaravelSeeder) {
                return get_class($object);
            }
        }

        return null;
    }
}
