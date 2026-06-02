<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class SeparableInseparablePhrasalVerbsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/prepositions-phrasal-verbs-separable-inseparable-phrasal-verbs-theory-links.json');
    }
}
