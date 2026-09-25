<?php

namespace Tests\Feature\Theory;

use App\Models\PageCategory;
use Illuminate\Support\Js;
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
            $categoryPath = $case['category_slug'];
            if ($case['representative_category_slug'] !== null) {
                // The current content contract nests Future Simple under Future Forms.
                // M3 loads the child tree on demand; keep checking learner reachability
                // from the rendered loader through its actual response and lesson card.
                $parentId = PageCategory::query()->where('slug', $case['category_slug'])->value('id');
                // Follow the URL policy actually used by the rendered loader, as the
                // original matrix also does for its canonical (unprefixed) links.
                $navigationUrl = localized_route('theory.navigation')
                    .'?'.http_build_query(['variant' => 'desktop', 'category' => $parentId]);
                $this->assertSame('gramlyze.loc', parse_url($navigationUrl, PHP_URL_HOST));
                $this->assertStringContainsString('data-theory-desktop-navigation-loader', $html);
                $this->assertStringContainsString(Js::from($navigationUrl)->toHtml(), $html);
                $navigation = $this->get($navigationUrl)->assertOk()->getContent();
                $childPath = TheoryRouteMatrix::localizedPath('uk', $case['representative_category_slug']);
                $this->assertStringContainsString($childPath, $navigation);
                $categoryPath .= '/'.$case['representative_category_slug'];
                $this->assertStringContainsString(
                    TheoryRouteMatrix::localizedPath('uk', $categoryPath, $case['representative_page_slug']),
                    $navigation
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
