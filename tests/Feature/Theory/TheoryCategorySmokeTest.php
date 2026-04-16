<?php

namespace Tests\Feature\Theory;

use Tests\Support\TheoryRouteMatrix;

class TheoryCategorySmokeTest extends SeededTheoryTestCase
{
    public function test_every_top_level_theory_category_page_renders_in_all_locales(): void
    {
        foreach (TheoryRouteMatrix::categorySmokeCases() as $case) {
            app()->setLocale($case['locale']);

            $response = $this->get(TheoryRouteMatrix::localizedPath($case['locale'], $case['category_slug']));

            $response->assertOk();

            $html = $response->getContent();
            $heading = $this->extractFirstHeading($html);

            $this->assertNoRawTranslationKeys($html);
            $this->assertNotSame('', $heading);
            $this->assertStringContainsString(
                TheoryRouteMatrix::localizedPath('uk', $case['category_slug'], $case['representative_page_slug']),
                $html
            );
        }
    }
}
