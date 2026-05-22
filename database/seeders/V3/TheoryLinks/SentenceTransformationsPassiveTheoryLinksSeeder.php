<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class SentenceTransformationsPassiveTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/sentence-transformations-passive-theory-links.json');
    }
}
