<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class SentenceTransformationsTensesTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/sentence-transformations-tenses-theory-links.json');
    }
}
