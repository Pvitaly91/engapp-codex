<?php

namespace Tests\Feature\PublicFlows;

use Tests\Support\PublicRouteMatrix;

class PublicTestRoutesSmokeTest extends SeededPublicFlowTestCase
{
    public function test_representative_public_test_routes_render_deterministically(): void
    {
        foreach (PublicRouteMatrix::publicTestPaths() as $path) {
            $response = $this->get($path);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertHtmlLocale($html, PublicRouteMatrix::DEFAULT_LOCALE);
            $this->assertNoRawTranslationKeys($html);

            $response->assertSee(PublicRouteMatrix::LEGACY_TEST_NAME);
            $response->assertSee('new-design-test-shell', false);
            $response->assertSee('window.__INITIAL_JS_TEST_QUESTIONS__', false);
            $response->assertSee('I {a1} breakfast every day.', false);
            $response->assertSee('"eat"', false);
        }
    }
}
