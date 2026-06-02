<?php

namespace Tests\Feature\Theory;

use Tests\Support\TheoryRouteMatrix;

class TheoryIndexSmokeTest extends SeededTheoryTestCase
{
    public function test_theory_index_pages_render_all_top_level_categories_in_stable_order(): void
    {
        $categories = TheoryRouteMatrix::topLevelCategories();

        foreach (['uk', 'en', 'pl'] as $locale) {
            app()->setLocale($locale);

            $response = $this->get(TheoryRouteMatrix::localizedPath($locale));

            $response->assertOk();

            $html = $response->getContent();

            $this->assertNoRawTranslationKeys($html);
            $this->assertStringContainsString(sprintf('<html lang="%s"', $locale), $html);

            $previousPosition = -1;

            foreach (array_keys($categories) as $categorySlug) {
                $categoryPath = TheoryRouteMatrix::localizedPath('uk', $categorySlug);
                $currentPosition = strpos($html, $categoryPath);

                $this->assertNotFalse($currentPosition, sprintf('Missing category link %s on %s.', $categoryPath, $locale));
                $this->assertGreaterThan(
                    $previousPosition,
                    $currentPosition,
                    sprintf('Category order regressed for %s on %s.', $categorySlug, $locale)
                );

                $previousPosition = $currentPosition;
            }
        }
    }
}
