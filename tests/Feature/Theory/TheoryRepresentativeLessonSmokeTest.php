<?php

namespace Tests\Feature\Theory;

use Tests\Support\TheoryRouteMatrix;

class TheoryRepresentativeLessonSmokeTest extends SeededTheoryTestCase
{
    public function test_representative_lessons_render_in_all_locales(): void
    {
        $categories = TheoryRouteMatrix::topLevelCategories();

        foreach (TheoryRouteMatrix::representativeLessonCases() as $case) {
            app()->setLocale($case['locale']);

            $response = $this->get(TheoryRouteMatrix::localizedPath(
                $case['locale'],
                $case['category_slug'],
                $case['page_slug']
            ));

            $response->assertOk();

            $html = $response->getContent();
            $heading = $this->extractFirstHeading($html);

            $this->assertNoRawTranslationKeys($html);
            $this->assertNotSame('', $heading);
            $this->assertNotSame($categories[$case['category_slug']]['titles'][$case['locale']], $heading);
            $this->assertStringContainsString(
                TheoryRouteMatrix::localizedPath('uk'),
                $html
            );
            $this->assertStringContainsString(
                TheoryRouteMatrix::localizedPath('uk', $case['category_slug']),
                $html
            );
        }
    }

    public function test_selected_category_pages_expose_expected_lesson_links(): void
    {
        foreach (TheoryRouteMatrix::selectedCategoryNavigationCases() as $case) {
            app()->setLocale('uk');

            $response = $this->get($case['path']);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertNoRawTranslationKeys($html);

            foreach ($case['expected_links'] as $expectedLink) {
                $this->assertStringContainsString($expectedLink, $html);
            }

            foreach ($case['expected_labels'] as $expectedLabel) {
                $this->assertStringContainsString($expectedLabel, $html);
            }
        }
    }

    public function test_selected_lesson_pages_expose_sibling_navigation_labels(): void
    {
        foreach (TheoryRouteMatrix::selectedLessonNavigationCases() as $case) {
            app()->setLocale('uk');

            $response = $this->get($case['path']);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertNoRawTranslationKeys($html);

            foreach ($case['expected_labels'] as $expectedLabel) {
                $this->assertStringContainsString($expectedLabel, $html);
            }
        }
    }
}
