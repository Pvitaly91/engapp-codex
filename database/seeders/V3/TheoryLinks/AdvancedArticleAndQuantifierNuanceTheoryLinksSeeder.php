<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class AdvancedArticleAndQuantifierNuanceTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/articles-quantifiers-advanced-article-and-quantifier-nuance-theory-links.json');
    }
}
