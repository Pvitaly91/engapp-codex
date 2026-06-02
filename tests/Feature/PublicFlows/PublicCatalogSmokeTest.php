<?php

namespace Tests\Feature\PublicFlows;

use Tests\Support\PublicRouteMatrix;

class PublicCatalogSmokeTest extends SeededPublicFlowTestCase
{
    public function test_catalog_cards_render_for_supported_locales(): void
    {
        foreach (PublicRouteMatrix::catalogPaths() as $locale => $path) {
            $response = $this->get($path);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertHtmlLocale($html, $locale);
            $this->assertNoRawTranslationKeys($html);

            $response->assertSee('Saved Present Simple Test');
            $response->assertSee(PublicRouteMatrix::CATALOG_TAG);
            $response->assertSee('/test/saved-present-simple-test', false);
        }
    }
}
