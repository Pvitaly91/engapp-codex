<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class AdvancedParticipleAndAbsoluteClausesTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/clauses-linking-words-advanced-participle-and-absolute-clauses-theory-links.json';
    }
}
