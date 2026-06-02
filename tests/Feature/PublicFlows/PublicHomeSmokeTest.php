<?php

namespace Tests\Feature\PublicFlows;

use Tests\Support\PublicRouteMatrix;

class PublicHomeSmokeTest extends SeededPublicFlowTestCase
{
    public function test_home_pages_render_for_default_and_supported_locales(): void
    {
        foreach (PublicRouteMatrix::homePaths() as $locale => $path) {
            $response = $this->get($path);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertHtmlLocale($html, $locale);
            $this->assertNoRawTranslationKeys($html);

            $response->assertSee('/catalog/tests-cards', false);
            $response->assertSee('/words/test', false);
            $response->assertSee('/verbs/test', false);
        }
    }
}
