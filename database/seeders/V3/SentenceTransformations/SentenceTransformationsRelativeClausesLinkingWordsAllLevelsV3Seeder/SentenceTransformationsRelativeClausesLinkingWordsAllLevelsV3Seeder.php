<?php

namespace Database\Seeders\V3\SentenceTransformations;

use App\Support\Database\JsonTestSeeder;

class SentenceTransformationsRelativeClausesLinkingWordsAllLevelsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
