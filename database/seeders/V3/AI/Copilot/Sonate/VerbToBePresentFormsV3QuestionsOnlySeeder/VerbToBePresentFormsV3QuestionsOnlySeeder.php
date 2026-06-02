<?php

namespace Database\Seeders\V3\AI\Copilot\Sonate;

use App\Support\Database\JsonTestSeeder;

class VerbToBePresentFormsV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
    }
}
