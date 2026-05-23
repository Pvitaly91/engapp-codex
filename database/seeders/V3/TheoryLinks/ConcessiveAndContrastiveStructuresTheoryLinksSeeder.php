<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class ConcessiveAndContrastiveStructuresTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/clauses-linking-words-concessive-contrastive-structures-theory-links.json';
    }
}
