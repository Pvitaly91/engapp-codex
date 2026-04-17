<?php

namespace Tests\Feature\PublicFlows;

use Tests\Support\PublicRouteMatrix;

class PublicSearchSmokeTest extends SeededPublicFlowTestCase
{
    public function test_search_results_page_renders_page_and_test_hits(): void
    {
        $response = $this->get('/search?q=' . PublicRouteMatrix::SEARCH_TERM);

        $response->assertOk();

        $html = $response->getContent();

        $this->assertHtmlLocale($html, PublicRouteMatrix::DEFAULT_LOCALE);
        $this->assertNoRawTranslationKeys($html);

        $response->assertSee(PublicRouteMatrix::PAGE_TITLE);
        $response->assertSee(PublicRouteMatrix::LEGACY_TEST_NAME);
        $response->assertSee(PublicRouteMatrix::theorySearchPath(), false);
        $response->assertSee('/test/' . PublicRouteMatrix::LEGACY_TEST_SLUG, false);
    }

    public function test_words_search_route_returns_expected_json_shape(): void
    {
        $response = $this->getJson('/words?q=' . PublicRouteMatrix::WORD_QUERY);

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => [
                'word',
                'translation',
                'translation_lang',
                'forms' => ['base', 'past', 'participle'],
            ],
        ]);

        $go = $this->assertJsonItemExists($response->json(), 'word', 'go');

        $this->assertSame('йти', $go['translation']);
        $this->assertSame('uk', $go['translation_lang']);
        $this->assertSame(['went'], $go['forms']['past']);
        $this->assertSame(['gone'], $go['forms']['participle']);
    }

    public function test_api_search_route_returns_expected_json_shape(): void
    {
        $response = $this->getJson('/api/search?lang=uk&q=' . PublicRouteMatrix::WORD_QUERY);

        $response->assertOk();
        $response->assertJsonStructure([
            '*' => ['en', 'translation'],
        ]);

        $go = $this->assertJsonItemExists($response->json(), 'en', 'go');

        $this->assertSame('йти', $go['translation']);
    }
}
