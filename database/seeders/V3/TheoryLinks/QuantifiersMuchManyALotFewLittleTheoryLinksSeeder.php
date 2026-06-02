<?php

namespace Database\Seeders\V3\TheoryLinks;

use App\Support\Database\JsonTheoryLinksSeederBase;

class QuantifiersMuchManyALotFewLittleTheoryLinksSeeder extends JsonTheoryLinksSeederBase
{
    protected function manifestPath(): string
    {
        return database_path('seeders/V3/TheoryLinks/data/nouns-articles-quantity-quantifiers-much-many-a-lot-few-little-theory-links.json');
    }
}
