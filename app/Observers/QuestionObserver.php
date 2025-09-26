<?php

namespace App\Observers;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Services\QuestionExportService;

class QuestionObserver
{
    private array $originalQuestions = [];

    public function __construct(private readonly QuestionExportService $exportService)
    {
    }

    public function saving(Question $question): void
    {
        if ($question->exists) {
            $this->originalQuestions[$question->getKey()] = $question->getOriginal('question');
        }
    }

    public function saved(Question $question): void
    {
        $this->syncChatGptExplanations($question);

        $this->exportService->export($question->fresh());

        unset($this->originalQuestions[$question->getKey()]);
    }

    private function syncChatGptExplanations(Question $question): void
    {
        if (! $question->wasChanged('question')) {
            return;
        }

        $previous = $this->originalQuestions[$question->getKey()] ?? null;

        if (! is_string($previous)) {
            return;
        }

        $current = $question->question;

        if (! is_string($current)) {
            return;
        }

        $trimmedPrevious = trim($previous);
        $trimmedCurrent = trim($current);

        if ($trimmedPrevious === '' || $trimmedCurrent === '' || $trimmedPrevious === $trimmedCurrent) {
            return;
        }

        $explanations = ChatGPTExplanation::query()
            ->where('question', $trimmedPrevious)
            ->get();

        if ($explanations->isEmpty()) {
            return;
        }

        $hasOtherOriginal = Question::query()
            ->where('id', '!=', $question->id)
            ->where('question', $trimmedPrevious)
            ->exists();

        if ($hasOtherOriginal) {
            return;
        }

        $hasOtherCurrent = Question::query()
            ->where('id', '!=', $question->id)
            ->where('question', $trimmedCurrent)
            ->exists();

        if ($hasOtherCurrent) {
            return;
        }

        foreach ($explanations as $explanation) {
            $conflictExists = ChatGPTExplanation::query()
                ->where('id', '!=', $explanation->id)
                ->where('question', $trimmedCurrent)
                ->where('wrong_answer', $explanation->wrong_answer)
                ->where('correct_answer', $explanation->correct_answer)
                ->where('language', $explanation->language)
                ->exists();

            if ($conflictExists) {
                continue;
            }

            $explanation->update(['question' => $trimmedCurrent]);
        }
    }
}

