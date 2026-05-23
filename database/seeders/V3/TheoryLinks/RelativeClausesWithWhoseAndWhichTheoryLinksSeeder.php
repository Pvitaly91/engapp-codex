<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class RelativeClausesWithWhoseAndWhichTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/clauses-linking-words-relative-clauses-with-whose-and-which-theory-links.json');
    }
}
