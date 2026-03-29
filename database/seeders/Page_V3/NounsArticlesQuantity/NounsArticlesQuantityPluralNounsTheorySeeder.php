<?php

namespace Database\Seeders\Page_V3\NounsArticlesQuantity;

class NounsArticlesQuantityPluralNounsTheorySeeder extends NounsArticlesQuantityPageSeeder
{
    protected function sourceSlug(): ?string
    {
        return 'plural-nouns-s-es-ies';
    }

    protected function localizationDefinitionPath(): ?string
    {
        return database_path('seeders/Page_V3/definitions/nouns_articles_quantity_plural_nouns_theory.json');
    }
}
