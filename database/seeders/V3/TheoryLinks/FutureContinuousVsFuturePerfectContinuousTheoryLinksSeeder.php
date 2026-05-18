<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class FutureContinuousVsFuturePerfectContinuousTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/future-forms-future-continuous-vs-future-perfect-continuous-theory-links.json');
    }
}
