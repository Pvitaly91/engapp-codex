<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class DiscourseMarkersAndCohesionTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/clauses-linking-words-discourse-markers-and-cohesion-theory-links.json';
    }
}
