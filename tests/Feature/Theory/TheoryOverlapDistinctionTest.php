<?php

namespace Tests\Feature\Theory;

use Tests\Support\TheoryRouteMatrix;

class TheoryOverlapDistinctionTest extends SeededTheoryTestCase
{
    public function test_overlap_sensitive_pages_keep_distinct_learner_facing_identity(): void
    {
        foreach (TheoryRouteMatrix::overlapPairs() as $pairKey => [$firstPage, $secondPage]) {
            foreach (['uk', 'en', 'pl'] as $locale) {
                app()->setLocale($locale);

                $firstResponse = $this->get(TheoryRouteMatrix::localizedPath(
                    $locale,
                    $firstPage['category_slug'],
                    $firstPage['page_slug']
                ));
                $secondResponse = $this->get(TheoryRouteMatrix::localizedPath(
                    $locale,
                    $secondPage['category_slug'],
                    $secondPage['page_slug']
                ));

                $firstResponse->assertOk();
                $secondResponse->assertOk();

                $firstHtml = $firstResponse->getContent();
                $secondHtml = $secondResponse->getContent();
                $firstHeading = $this->extractFirstHeading($firstHtml);
                $secondHeading = $this->extractFirstHeading($secondHtml);

                $this->assertNoRawTranslationKeys($firstHtml);
                $this->assertNoRawTranslationKeys($secondHtml);
                $this->assertNotSame('', $firstHeading);
                $this->assertNotSame('', $secondHeading);
                $this->assertNotSame(
                    $this->normalizeText($firstHeading),
                    $this->normalizeText($secondHeading),
                    sprintf('Overlap pair %s collapsed for locale %s.', $pairKey, $locale)
                );
            }
        }
    }
}
