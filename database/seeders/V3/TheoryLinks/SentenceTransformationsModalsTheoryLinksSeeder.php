<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class SentenceTransformationsModalsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/sentence-transformations-modals-theory-links.json');
    }
}
