<?php

namespace Database\Seeders\V3\Tenses\PresentPerfectContinuous;

use App\Support\Database\JsonTestSeeder;

class UkrainianMixedPresentPerfectContinuousQuestionsStandardSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
