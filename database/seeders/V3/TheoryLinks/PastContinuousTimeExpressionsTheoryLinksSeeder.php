<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class PastContinuousTimeExpressionsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/past-continuous-time-expressions-theory-links.json';
    }
}
