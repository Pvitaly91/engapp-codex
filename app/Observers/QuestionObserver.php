<?php

namespace App\Observers;

use App\Models\Question;
use App\Services\QuestionExportService;

class QuestionObserver
{
    public function __construct(private readonly QuestionExportService $exportService)
    {
    }

    public function saved(Question $question): void
    {
        $this->exportService->export($question->fresh());
    }
}

