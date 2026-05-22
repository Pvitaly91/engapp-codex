<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class SentenceTransformationsConditionalsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/sentence-transformations-conditionals-theory-links.json');
    }
}
