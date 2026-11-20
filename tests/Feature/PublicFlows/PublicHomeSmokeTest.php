<?php

namespace Tests\Feature\PublicFlows;

use Illuminate\Support\Facades\DB;
use Tests\Support\PublicRouteMatrix;

class PublicHomeSmokeTest extends SeededPublicFlowTestCase
{
    public function test_home_pages_render_for_default_and_supported_locales(): void
    {
        foreach (PublicRouteMatrix::homePaths() as $locale => $path) {
            $connection = DB::connection();
            $connection->flushQueryLog();
            $connection->enableQueryLog();

            try {
                $response = $this->get($path);
                $queries = array_column($connection->getQueryLog(), 'query');
            } finally {
                $connection->disableQueryLog();
                $connection->flushQueryLog();
            }

            $response->assertOk();

            $html = $response->getContent();

            $this->assertHtmlLocale($html, $locale);
            $this->assertNoRawTranslationKeys($html);

            $response->assertSee('/catalog/tests-cards', false);
            $response->assertSee('/words/test', false);
            $response->assertSee('/verbs/test', false);

            $catalogQueries = array_values(array_filter($queries, static fn (string $query): bool => preg_match(
                '/\b(?:from|join)\s+["`]?(?:saved_grammar_tests|page_categories|pages|questions|tags)\b/i',
                $query,
            ) === 1));
            $this->assertSame([], $catalogQueries, "Home [$locale] should not load unused catalog data.");
        }
    }
}
