<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class CommonPhrasalVerbsByTopicTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/prepositions-phrasal-verbs-common-phrasal-verbs-by-topic-theory-links.json');
    }
}
