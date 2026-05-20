<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class DefiningRelativeClausesTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/relative-clauses-defining-relative-clauses-theory-links.json');
    }
}
