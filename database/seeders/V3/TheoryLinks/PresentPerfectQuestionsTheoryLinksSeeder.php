<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class PresentPerfectQuestionsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/present-perfect-questions-theory-links.json';
    }
}
