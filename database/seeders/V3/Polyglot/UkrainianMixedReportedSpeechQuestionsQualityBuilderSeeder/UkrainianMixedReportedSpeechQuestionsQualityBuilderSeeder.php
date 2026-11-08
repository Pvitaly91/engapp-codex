<?php

namespace Database\Seeders\V3\Polyglot;

// Generated ReportedSpeech quality bank; edit canonical author JSON instead.
use App\Support\Database\JsonTestSeeder;

class UkrainianMixedReportedSpeechQuestionsQualityBuilderSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
