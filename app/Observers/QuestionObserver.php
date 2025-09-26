<?php

namespace App\Observers;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Services\QuestionExportService;

class QuestionObserver
{
    public function __construct(private readonly QuestionExportService $exportService)
    {
    }

    public function saved(Question $question): void
    {
        $this->syncChatGptExplanations($question);

        $this->exportService->export($question->fresh());
    }

    private function syncChatGptExplanations(Question $question): void
    {
        if (! $question->wasChanged('question')) {
            return;
        }

        $previous = $question->getOriginal('question');

        if (! is_string($previous)) {
            return;
        }

        $current = $question->question;

        if (! is_string($current)) {
            return;
        }

        $trimmedPrevious = trim($previous);
        $trimmedCurrent = trim($current);

        if ($trimmedPrevious === '' || $trimmedCurrent === '') {
            return;
        }

        if ($previous === $current) {
            return;
        }

        $explanations = ChatGPTExplanation::query()
            ->where('question', $previous)
            ->get();

        if ($explanations->isEmpty() && $previous !== $trimmedPrevious) {
            $explanations = ChatGPTExplanation::query()
                ->whereRaw('TRIM(question) = ?', [$trimmedPrevious])
                ->get();
        }

        if ($explanations->isEmpty()) {
            return;
        }

        $hasOtherOriginal = Question::query()
            ->where('id', '!=', $question->id)
            ->where(function ($query) use ($previous, $trimmedPrevious) {
                $query->where('question', $previous);

                if ($previous !== $trimmedPrevious) {
                    $query->orWhereRaw('TRIM(question) = ?', [$trimmedPrevious]);
                }
            })
            ->exists();

        if ($hasOtherOriginal) {
            return;
        }

        $hasOtherCurrent = Question::query()
            ->where('id', '!=', $question->id)
            ->where(function ($query) use ($current, $trimmedCurrent) {
                $query->where('question', $current);

                if ($current !== $trimmedCurrent) {
                    $query->orWhereRaw('TRIM(question) = ?', [$trimmedCurrent]);
                }
            })
            ->exists();

        if ($hasOtherCurrent) {
            return;
        }

        foreach ($explanations as $explanation) {
            $conflictExists = ChatGPTExplanation::query()
                ->where('id', '!=', $explanation->id)
                ->where(function ($query) use ($current, $trimmedCurrent) {
                    $query->where('question', $current);

                    if ($current !== $trimmedCurrent) {
                        $query->orWhereRaw('TRIM(question) = ?', [$trimmedCurrent]);
                    }
                })
                ->where('wrong_answer', $explanation->wrong_answer)
                ->where('correct_answer', $explanation->correct_answer)
                ->where('language', $explanation->language)
                ->exists();

            if ($conflictExists) {
                continue;
            }

            $explanation->update(['question' => $current]);
        }
    }
}

