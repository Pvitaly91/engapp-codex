<?php

namespace Tests\Feature\Theory;

use Tests\Support\TheoryRouteMatrix;

class TheoryLocaleLeakageTest extends SeededTheoryTestCase
{
    public function test_selected_english_theory_pages_do_not_show_obvious_locale_leakage(): void
    {
        foreach (TheoryRouteMatrix::englishLeakageCases() as $case) {
            app()->setLocale('en');

            $response = $this->get(TheoryRouteMatrix::localizedPath('en', $case['category_slug'], $case['page_slug']));

            $response->assertOk();

            $html = $response->getContent();

            $this->assertNoRawTranslationKeys($html);
            $this->assertSame($case['expected_h1'], $this->extractFirstHeading($html));
            $this->assertNoCyrillicInH1($html);

            foreach ($case['unexpected_fragments'] as $unexpectedFragment) {
                $this->assertStringNotContainsString($unexpectedFragment, $html);
            }
        }
    }

    public function test_selected_polish_theory_routes_do_not_show_known_stale_strings(): void
    {
        foreach (TheoryRouteMatrix::polishLeakageCases() as $case) {
            app()->setLocale('pl');

            $response = $this->get($case['path']);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertNoRawTranslationKeys($html);
            $this->assertSame($case['expected_h1'], $this->extractFirstHeading($html));

            foreach ($case['unexpected_fragments'] as $unexpectedFragment) {
                $this->assertStringNotContainsString($unexpectedFragment, $html);
            }
        }
    }
}
