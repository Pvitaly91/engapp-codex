<?php

namespace Database\Seeders\V3;

use App\Support\Database\JsonTestSeeder;

class WillVsBeGoingToFutureFormsV3Seeder extends JsonTestSeeder
{
    protected function definitionPath(): string
    {
        return __DIR__ . '/definition.json';
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
