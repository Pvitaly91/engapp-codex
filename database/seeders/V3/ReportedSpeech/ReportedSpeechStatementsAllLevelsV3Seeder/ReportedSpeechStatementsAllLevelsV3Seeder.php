<?php

namespace Database\Seeders\V3\ReportedSpeech;

use App\Support\Database\JsonTestSeeder;

class ReportedSpeechStatementsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
