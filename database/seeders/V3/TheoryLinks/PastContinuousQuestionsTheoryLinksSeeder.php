<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class PastContinuousQuestionsTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return __DIR__ . '/data/past-continuous-questions-theory-links.json';
    }
}
