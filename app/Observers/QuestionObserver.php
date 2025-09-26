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

        if (! ChatGPTExplanation::query()->where('question', $previous)->exists()) {
            return;
        }

        $hasOtherOriginal = Question::query()
            ->where('id', '!=', $question->id)
            ->where('question', $previous)
            ->exists();

        if ($hasOtherOriginal) {
            return;
        }

        $hasOtherCurrent = Question::query()
            ->where('id', '!=', $question->id)
            ->where('question', $current)
            ->exists();

        if ($hasOtherCurrent) {
            return;
        }

        ChatGPTExplanation::query()
            ->where('question', $previous)
            ->update(['question' => $current]);
    }
}

