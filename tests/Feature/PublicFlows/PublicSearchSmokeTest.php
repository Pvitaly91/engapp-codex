<?php

namespace Tests\Feature\PublicFlows;

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
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

    public function test_stateless_word_search_returns_rich_cached_results_without_query_fanout(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson(
            'http://gramlyze.ub/api/word-search/uk?q=' . PublicRouteMatrix::WORD_QUERY
        );

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
        $this->assertSame(['went'], $go['forms']['past']);
        $this->assertSame(['gone'], $go['forms']['participle']);

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=300', $cacheControl);
        $response->assertHeader('X-RateLimit-Limit', '300');

        $wordQueries = collect(DB::getQueryLog())->filter(function (array $query): bool {
            $sql = strtolower((string) ($query['query'] ?? ''));

            return str_contains($sql, '`words`') || str_contains($sql, '`translates`');
        });
        $this->assertLessThanOrEqual(3, $wordQueries->count());

        $route = app('router')->getRoutes()->getByName('api.words.search');
        $this->assertNotNull($route);
        $this->assertNotContains(StartSession::class, app('router')->gatherRouteMiddleware($route));
    }
}
