<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class ConditionalsWithUnlessProvidedAsLongAsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/conditionals-with-unless-provided-as-long-as-theory-links.json';
    }
}
