<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Source;
use App\Models\VerbHint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuestionDeletionService
{ 
    public function deleteQuestion(Question $question): QuestionDeletionResult
    {
        $questionUuid = (string) $question->uuid;
        $categoryId = $question->category_id;
        $sourceId = $question->source_id;

        $questionText = trim((string) $question->question);
        $hasDuplicateText = $questionText !== '' && Question::query()
            ->where('id', '!=', $question->id)
            ->where('question', $questionText)
            ->exists();

        $optionIds = collect();

        if (Schema::hasTable('question_option_question')) {
            $optionIds = $optionIds->merge(
                DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->pluck('option_id')
            );

            DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->delete();
        }

        if (Schema::hasTable('question_answers')) {
            $optionIds = $optionIds->merge(
                QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->pluck('option_id')
            );

            QuestionAnswer::query()
                ->where('question_id', $question->id)
                ->delete();
        }

        if (Schema::hasTable('verb_hints')) {
            $optionIds = $optionIds->merge(
                VerbHint::query()
                    ->where('question_id', $question->id)
                    ->pluck('option_id')
            );

            VerbHint::query()
                ->where('question_id', $question->id)
                ->delete();
        }

        if (Schema::hasTable('question_hints')) {
            QuestionHint::query()
                ->where('question_id', $question->id)
                ->delete();
        }

        if (Schema::hasTable('question_variants')) {
            QuestionVariant::query()
                ->where('question_id', $question->id)
                ->delete();
        }

        $question->tags()->detach();
        $question->delete();

        if ($questionText !== '' && Schema::hasTable('chatgpt_explanations') && ! $hasDuplicateText) {
            ChatGPTExplanation::query()
                ->where('question', $questionText)
                ->delete();
        }

        $this->deleteUnusedOptions($optionIds);
        $this->pruneUnusedCategoryAndSource($categoryId, $sourceId);

        return new QuestionDeletionResult($questionUuid !== '' ? $questionUuid : null);
    }

    private function deleteUnusedOptions(Collection $optionIds): void
    {
        $optionIds->filter()
            ->unique()
            ->each(function ($optionId) {
                $stillUsed = (Schema::hasTable('question_answers')
                        && QuestionAnswer::query()->where('option_id', $optionId)->exists())
                    || (Schema::hasTable('verb_hints')
                        && VerbHint::query()->where('option_id', $optionId)->exists())
                    || (Schema::hasTable('question_option_question')
                        && DB::table('question_option_question')->where('option_id', $optionId)->exists());

                if (! $stillUsed) {
                    QuestionOption::query()->where('id', $optionId)->delete();
                }
            });
    }

    private function pruneUnusedCategoryAndSource(?int $categoryId, ?int $sourceId): void
    {
        if ($categoryId && Schema::hasTable('categories')) {
            $hasQuestions = Question::query()->where('category_id', $categoryId)->exists();

            if (! $hasQuestions) {
                Category::query()->whereKey($categoryId)->delete();
            }
        }

        if ($sourceId && Schema::hasTable('sources')) {
            $hasQuestions = Question::query()->where('source_id', $sourceId)->exists();

            if (! $hasQuestions) {
                Source::query()->whereKey($sourceId)->delete();
            }
        }
    }
}

class QuestionDeletionResult
{
    public function __construct(
        public readonly ?string $deletedQuestionUuid,
    ) {
    }
}
