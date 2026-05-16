<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class PastContinuousFormsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/past-continuous-forms-theory-links.json';
    }
}
