<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class DemonstrativesThisThatTheseThoseTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/pronouns-demonstratives-demonstratives-this-that-these-those-theory-links.json');
    }
}
