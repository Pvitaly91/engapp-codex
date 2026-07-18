<?php

namespace Tests\Feature\Theory;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheorySidebarActiveStateTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private PageCategory $presentPerfect;

    /** @var array<int, Page> */
    private array $lessons = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['coming-soon.enabled' => false]);
        $this->rebuildComposeTestSchema();

        $tenses = PageCategory::query()->create([
            'title' => 'Tenses',
            'slug' => 'tenses',
            'language' => 'uk',
            'type' => 'theory',
        ]);

        $this->presentPerfect = PageCategory::query()->create([
            'title' => 'Present Perfect',
            'slug' => 'present-perfect',
            'language' => 'uk',
            'type' => 'theory',
            'parent_id' => $tenses->getKey(),
        ]);

        foreach ([
            'present-perfect-forms' => 'Present Perfect: Forms and Use',
            'present-perfect-negatives' => 'Present Perfect: Negatives',
            'present-perfect-questions' => 'Present Perfect: Questions',
            'present-perfect-time-expressions' => 'Present Perfect: Time Expressions',
        ] as $slug => $title) {
            $this->lessons[] = Page::query()->create([
                'slug' => $slug,
                'title' => $title,
                'type' => 'theory',
                'page_category_id' => $this->presentPerfect->getKey(),
            ]);
        }

        Cache::flush();
    }

    public function test_desktop_loader_keeps_the_lazy_tree_scrollable(): void
    {
        $html = view('theory.partials.desktop-navigation-loader', [
            'selectedCategory' => $this->presentPerfect,
            'currentPage' => null,
            'routePrefix' => 'theory',
        ])->render();

        $xpath = $this->xpath($html);
        $loader = $xpath->query('//*[@data-theory-desktop-navigation-loader]')->item(0);

        $this->assertNotNull($loader);
        $this->assertClassTokens($loader, ['flex', 'min-h-0', 'flex-1', 'flex-col', 'overflow-hidden']);

        $content = $xpath->query('.//*[@x-ref="content"]', $loader)->item(0);

        $this->assertNotNull($content);
        $this->assertClassTokens($content, ['flex', 'min-h-0', 'flex-1', 'flex-col', 'overflow-hidden']);
        $this->assertStringContainsString(
            'container.querySelector(\'[data-theory-nav-current-page="true"]\')',
            $html
        );
    }

    public function test_category_overview_is_the_only_active_sidebar_destination(): void
    {
        foreach (['mobile', 'desktop'] as $variant) {
            $response = $this->navigationResponse($variant, null);

            $this->assertNavigationState($response, null);
        }
    }

    public function test_every_present_perfect_lesson_is_active_in_both_sidebar_variants(): void
    {
        $this->assertCount(4, $this->lessons);

        foreach ($this->lessons as $lesson) {
            foreach (['mobile', 'desktop'] as $variant) {
                $response = $this->navigationResponse($variant, $lesson);

                $this->assertNavigationState($response, $lesson);
            }
        }
    }

    public function test_mobile_lazy_navigation_runs_active_item_autoscroll_after_loading(): void
    {
        $html = view('theory.partials.mobile-navigation', [
            'categories' => collect(),
            'selectedCategory' => $this->presentPerfect,
            'categoryPages' => collect($this->lessons),
            'currentPage' => $this->lessons[0],
            'routePrefix' => 'theory',
        ])->render();

        $this->assertStringContainsString('window.initTheorySidebarAutoscroll?.();', $html);
    }

    private function navigationResponse(string $variant, ?Page $page): TestResponse
    {
        $query = http_build_query(array_filter([
            'variant' => $variant === 'desktop' ? 'desktop' : null,
            'category' => $this->presentPerfect->getKey(),
            'page' => $page?->getKey(),
        ]));

        return $this->get('/theory/navigation?'.$query)
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }

    private function assertNavigationState(TestResponse $response, ?Page $page): void
    {
        $xpath = $this->xpath($response->getContent());
        $activeCategories = $xpath->query('//a[@data-theory-nav-active="true"]');
        $currentPages = $xpath->query('//a[@data-theory-nav-current-page="true" and @aria-current="page"]');

        $this->assertCount(1, $activeCategories);

        if (! $page) {
            $this->assertCount(0, $currentPages);

            return;
        }

        $this->assertCount(1, $currentPages);
        $this->assertStringEndsWith('/'.$page->slug, $currentPages->item(0)->getAttribute('href'));
    }

    private function xpath(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($document);
    }

    /** @param array<int, string> $expected */
    private function assertClassTokens(\DOMNode $node, array $expected): void
    {
        $classes = preg_split('/\s+/', trim((string) $node->attributes?->getNamedItem('class')?->nodeValue)) ?: [];

        foreach ($expected as $class) {
            $this->assertContains($class, $classes);
        }
    }
}
