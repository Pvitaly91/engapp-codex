<?php

namespace Database\Seeders\V3\AI\NewV3\Codex;

use App\Support\Database\JsonTestSeeder;

class PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
