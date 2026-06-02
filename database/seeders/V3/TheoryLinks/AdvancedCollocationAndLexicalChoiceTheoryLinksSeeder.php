<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class AdvancedCollocationAndLexicalChoiceTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/vocabulary-collocations-advanced-collocation-and-lexical-choice-theory-links.json';
    }
}
