<?php

namespace Tests\Feature\Theory;

use Illuminate\Support\Facades\File;
use Tests\Support\TheoryRouteMatrix;

class TheoryCategorySmokeTest extends SeededTheoryTestCase
{
    public function test_every_top_level_theory_category_page_renders_in_all_locales(): void
    {
        $menuPath = storage_path('framework/testing/theory-menu-smoke-'.bin2hex(random_bytes(8)));
        config(['app.url' => 'http://gramlyze.loc', 'theory-menu.path' => $menuPath]);
        $this->artisan('theory:menu-build')->assertSuccessful();
        try {
            $this->checkCategoryMatrix();
        } finally {
            File::deleteDirectory($menuPath);
        }
    }

    private function checkCategoryMatrix(): void
    {
        foreach (TheoryRouteMatrix::categorySmokeCases() as $case) {
            app()->setLocale($case['locale']);

            $response = $this->get(TheoryRouteMatrix::localizedPath($case['locale'], $case['category_slug']));

            $response->assertOk();

            $html = $response->getContent();
            $heading = $this->extractFirstHeading($html);

            $this->assertNoRawTranslationKeys($html);
            $this->assertNotSame('', $heading);
            $categoryPath = $case['category_slug'];
            if ($case['representative_category_slug'] !== null) {
                // The current content contract nests Future Simple under Future Forms.
                // Both canonical links must now be reachable in the initial SSR tree.
                $this->assertMatchesRegularExpression('/<div\b[^>]*\sdata-theory-menu-fragment="desktop"/', $html);
                $this->assertStringNotContainsString('data-theory-desktop-navigation-loader', $html);
                $childPath = TheoryRouteMatrix::localizedPath('uk', $case['representative_category_slug']);
                $this->assertStringContainsString($childPath, $html);
                $categoryPath .= '/'.$case['representative_category_slug'];
                $this->assertStringContainsString(
                    TheoryRouteMatrix::localizedPath('uk', $categoryPath, $case['representative_page_slug']),
                    $html
                );
                $html = $this->get(TheoryRouteMatrix::localizedPath($case['locale'], $case['representative_category_slug']))
                    ->assertOk()->getContent();
            }
            $this->assertStringContainsString(
                TheoryRouteMatrix::localizedPath('uk', $categoryPath, $case['representative_page_slug']),
                $html
            );
        }
    }
}
