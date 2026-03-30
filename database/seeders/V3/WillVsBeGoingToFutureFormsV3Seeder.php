<?php

namespace Database\Seeders\V3;

use App\Support\Database\JsonTestSeeder;

class WillVsBeGoingToFutureFormsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return database_path('seeders/V3/definitions/will_vs_be_going_to_future_forms_v2.json');
    }

    protected function normalizeQuestionPresentation(string $questionText, array $variants, array $markers): array
    {
        return [
            'question' => $this->stripDuplicatedVerbHintTail($questionText, $markers),
            'variants' => array_map(
                fn (string $variant): string => $this->stripDuplicatedVerbHintTail($variant, $markers),
                $variants
            ),
        ];
    }
}
