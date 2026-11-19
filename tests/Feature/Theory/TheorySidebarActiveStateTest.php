<?php

namespace Tests\Feature\Theory;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class TheorySidebarActiveStateTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private PageCategory $presentPerfect;

    private string $menuPath;

    /** @var array<int, Page> */
    private array $lessons = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->menuPath = storage_path('framework/testing/theory-menu-http-'.bin2hex(random_bytes(8)));
        config([
            'app.url' => 'http://gramlyze.loc',
            'coming-soon.enabled' => false,
            'theory-menu.path' => $this->menuPath,
        ]);
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

    }

    protected function tearDown(): void
    {
        if (isset($this->menuPath) && str_starts_with($this->menuPath, storage_path('framework/testing/theory-menu-http-'))) {
            File::deleteDirectory($this->menuPath);
        }
        parent::tearDown();
    }

    public function test_desktop_tree_is_in_the_first_response_and_page_state_stays_outside_the_fragment(): void
    {
        $this->buildMenu();
        View::composer('theory.partials.menu-cache-fragment', static fn () => throw new \LogicException('Runtime re-rendered the tree.'));

        $category = $this->get('/theory/present-perfect')->assertOk()
            ->assertDontSee('data-theory-desktop-navigation-loader', false)
            ->getContent();
        $this->assertMatchesRegularExpression('/<div\b[^>]*\sdata-theory-menu-fragment="desktop"/', $category);
        $this->assertDoesNotMatchRegularExpression('/<div\b[^>]*\sdata-theory-menu-fragment="mobile"/', $category);
        $this->assertStringContainsString('data-theory-menu-selected-category="'.$this->presentPerfect->getKey().'"', $category);

        foreach ($this->lessons as $lesson) {
            $lessonUrl = '/theory/tenses/present-perfect/'.$lesson->slug;
            $lessonHtml = $this->get($lessonUrl)->assertOk()
                ->assertSee('data-theory-menu-current-page="'.$lesson->getKey().'"', false)
                ->assertSee($lessonUrl, false)
                ->getContent();
            $this->assertMatchesRegularExpression('/<div\b[^>]*\sdata-theory-menu-fragment="desktop"/', $lessonHtml);
        }
    }

    public function test_fragment_bytes_are_neutral_identical_and_not_rebuilt_at_runtime(): void
    {
        $this->buildMenu();
        View::composer('theory.partials.menu-cache-fragment', static fn () => throw new \LogicException('Runtime re-rendered the tree.'));
        $queries = [];
        DB::listen(static function ($query) use (&$queries) {
            if (preg_match('/\b(?:page_categories|pages|site_tree_variants|site_tree_items|text_blocks)\b/i', $query->sql)) {
                $queries[] = $query->sql;
            }
        });

        foreach (['mobile', 'desktop'] as $variant) {
            $response = $this->get('/theory/navigation?variant='.$variant)->assertOk()
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
                ->assertHeader('X-Theory-Menu-Cache', 'hit');
            $html = $response->getContent();
            $this->assertStringContainsString('data-theory-menu-fragment="'.$variant.'"', $html);
            $this->assertStringNotContainsString('aria-current=', $html);
            $this->assertStringNotContainsString('data-theory-nav-active="true"', $html);
            $this->assertStringNotContainsString('<script', $html);
            $revision = $response->headers->get('X-Theory-Menu-Revision');
            $etag = $response->headers->get('ETag');
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $revision);
            foreach ($this->lessons as $lesson) {
                $legacyUrl = '/theory/navigation?variant='.$variant.'&category='.$this->presentPerfect->getKey().'&page='.$lesson->getKey();
                $this->assertSame($html, $this->get($legacyUrl)->assertOk()->getContent());
                $this->assertStringContainsString('/theory/tenses/present-perfect/'.$lesson->slug, $html);
            }
            $this->get('/theory/navigation?variant='.$variant.'&revision='.$revision, ['If-None-Match' => $etag])
                ->assertStatus(304);
        }
        $this->assertSame([], $queries);
    }

    public function test_missing_or_untrusted_snapshot_requests_never_trigger_a_build(): void
    {
        View::composer('theory.partials.menu-cache-fragment', static fn () => throw new \LogicException('Runtime attempted to build.'));
        $this->get('/theory/navigation')->assertOk()
            ->assertHeader('X-Theory-Menu-Cache', 'miss')
            ->assertSee('data-theory-menu-fallback', false)
            ->assertSee('href="/theory"', false);
        $this->get('/theory/navigation?variant=../mobile')->assertNotFound();
        $this->get('/theory/navigation?variant[]=mobile')->assertNotFound();
        $this->get('/theory/navigation?revision=../../active.json')->assertNotFound();
        $this->get('/theory/navigation?revision='.str_repeat('a', 64))->assertNotFound();
        $this->assertDirectoryDoesNotExist($this->menuPath);
    }

    public function test_production_profile_isolated_from_development_and_private(): void
    {
        config(['site-mode.production_domains' => ['perf-production.test']]);
        $this->buildMenu();
        $this->get('http://perf-production.test/theory/navigation')->assertOk()
            ->assertHeader('X-Theory-Menu-Cache', 'miss');
        config(['app.url' => 'http://perf-production.test']);
        $this->buildMenu();
        $response = $this->get('http://perf-production.test/theory/navigation')->assertOk()
            ->assertHeader('X-Theory-Menu-Cache', 'hit');
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('gramlyze.loc', $response->getContent());
    }

    private function buildMenu(): void
    {
        $this->artisan('theory:menu-build', ['--locale' => 'uk'])->assertSuccessful();
    }
}
