<?php

namespace Database\Seeders\V3\AI\ChatGpt\Codex;

use App\Support\Database\JsonTestSeeder;

class PluralNounsMnozinaImennikivPravilaViniatkiPrikladiV3QuestionsOnlySeeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/AI/ChatGpt/Codex/plural_nouns_mnozina_imennikiv_pravila_viniatki_prikladi_v3_questions_only.json');
    }
}
