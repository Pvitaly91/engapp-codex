<?php

namespace Database\Seeders\V3\Polyglot;

use App\Support\Database\JsonTestSeeder;

class PolyglotPastPerfectQuestionsAllLevelsLessonSeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
