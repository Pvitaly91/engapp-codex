<?php

namespace Database\Seeders\Page_V3\Concerns;

abstract class JsonGrammarPageSeeder extends GrammarPageSeeder
{
    use InteractsWithPageV3Json;

    protected function slug(): string
    {
        return $this->resolvedSlugFromSource();
    }

    protected function type(): ?string
    {
        return $this->resolvedPageTypeFromSource();
    }

    protected function category(): ?array
    {
        return $this->resolvedCategoryFromSource();
    }

    protected function page(): array
    {
        return $this->resolvedPageConfigFromSource();
    }
}
