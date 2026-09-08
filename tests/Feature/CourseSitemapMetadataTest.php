<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\CourseCatalogService;
use App\Services\CourseSitemapMetadataService;
use App\Services\PolyglotCourseBlueprintService;
use App\Services\PolyglotCourseManifestService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class CourseSitemapMetadataTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const HOST = 'https://seo.production.test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildComposeTestSchema();
        config([
            'app.locale' => 'uk', 'app.debug' => false,
            'site-mode.production_domains' => ['seo.production.test'],
            'site-mode.production_origin' => 'https://gramlyze.com',
            'site-mode.production_locales' => ['uk'],
            'site-mode.response_cache.enabled' => false,
            'coming-soon.enabled' => true,
            'coming-soon.routes' => [], 'coming-soon.prefixes' => ['/test/'],
            'coming-soon.allowed_test_slug_prefixes' => ['polyglot-', 'course-td-'],
        ]);
        app()->setLocale('uk');
    }

    public function test_catalog_candidates_are_actual_destinations_with_only_canonical_branding(): void
    {
        app()->setLocale('pl');
        $slugs = app(CourseCatalogService::class)->canonicalCourseSlugs();
        $this->assertSame([
            'english-grammar-theory', 'sentence-builder-english-a1', 'sentence-builder-english-a2',
            'sentence-builder-english-b1', 'sentence-builder-english-b2', 'sentence-builder-english-c1',
            'sentence-builder-english-c2', 'theory-driven',
        ], $slugs);
        $this->assertSame([], $this->paths());
    }

    public function test_persisted_ready_theory_driven_course_is_included_without_registering_anything(): void
    {
        $this->savedLesson('course-td-there-is-there-are', 'theory-driven');
        $this->app['session']->put('unrelated', 'preserved');
        $session = $this->app['session']->all();
        Cache::spy();

        $this->assertSame(['/courses/theory-driven'], $this->paths());
        $this->assertSame(['/courses/theory-driven'], $this->paths());
        $this->assertSame($session, $this->app['session']->all());
        foreach (['put', 'forever', 'remember', 'rememberForever', 'forget'] as $method) {
            Cache::shouldNotHaveReceived($method);
        }
    }

    public function test_canonical_course_path_is_used_when_existing_guest_policy_allows_its_entry(): void
    {
        // A synthetic disabled gate proves URL branding independently of the gate regression below.
        config(['coming-soon.enabled' => false]);
        $this->savedLesson('polyglot-to-be-a1', 'polyglot-english-a1');
        $this->assertSame(['/courses/sentence-builder-english-a1'], $this->paths());
        $this->get(self::HOST.'/courses/sentence-builder-english-a1', ['Accept' => 'text/html'])
            ->assertOk()->assertHeaderMissing('X-Robots-Tag')
            ->assertSee('<link rel="canonical" href="https://gramlyze.com/courses/sentence-builder-english-a1">', false);
    }

    public function test_ready_catalog_and_theory_driven_home_are_indexable_in_the_full_production_kernel(): void
    {
        $this->savedLesson('course-td-there-is-there-are', 'theory-driven');
        foreach (['/courses', '/courses/theory-driven'] as $path) {
            $this->app['session']->flush();
            $response = $this->get(self::HOST.$path, ['Accept' => 'text/html'])
                ->assertOk()->assertHeader('X-Site-Mode', 'production')->assertHeaderMissing('X-Robots-Tag')
                ->assertSee('<link rel="canonical" href="https://gramlyze.com'.$path.'">', false);
            $this->assertDoesNotMatchRegularExpression('/<meta[^>]+name=["\']robots["\'][^>]+noindex/i', $response->getContent());
            if ($path === '/courses/theory-driven') {
                $response->assertSee('data-polyglot-implemented-lessons="1"', false)
                    ->assertSee('/test/course-td-there-is-there-are/step/compose', false);
            }
        }
        $this->app['session']->flush();
        $this->get(self::HOST.'/test/course-td-there-is-there-are/step/compose', ['Accept' => 'text/html'])->assertOk();
        $this->assertSame(['/courses/theory-driven'], $this->paths());
    }

    public function test_default_cold_guest_branding_gate_is_not_bypassed_by_sitemap_metadata(): void
    {
        $this->savedLesson('polyglot-to-be-a1', 'polyglot-english-a1');
        $this->app['session']->flush();
        $this->assertSame([], $this->paths());
        $this->get(self::HOST.'/test/sentence-builder-to-be-a1/step/compose', ['Accept' => 'text/html'])
            ->assertNotFound();
        $this->app['session']->flush();
        $this->app['session']->put('admin_authenticated', true);
        $this->app['session']->put('coming_soon.allowed_theory_test_slugs', ['sentence-builder-to-be-a1' => true]);
        $this->withHeader('Referer', self::HOST.'/theory/basic-grammar/sentence-types');
        $this->assertSame([], $this->paths());
    }

    public function test_empty_first_entry_is_not_hidden_by_a_later_usable_lesson(): void
    {
        config(['coming-soon.enabled' => false]);
        $this->savedLesson('polyglot-to-be-a1', 'polyglot-english-a1', false, 1);
        $this->savedLesson('polyglot-there-is-there-are-a1', 'polyglot-english-a1', true, 2);
        $this->assertSame([], $this->paths());
    }

    public function test_dangling_question_links_and_missing_answers_do_not_prove_readiness(): void
    {
        $test = $this->savedLesson('course-td-there-is-there-are', 'theory-driven', false);
        $test->questionLinks()->create(['question_uuid' => (string) Str::uuid(), 'position' => 0]);
        $this->assertSame([], $this->paths());
        $this->attachQuestion($test, false);
        $this->assertSame([], $this->paths());
        $this->attachQuestion($test, true);
        $this->assertSame(['/courses/theory-driven'], $this->paths());
        DB::table('question_answers')->delete();
        $this->assertSame([], $this->paths());
    }

    public function test_legacy_test_shadow_and_filter_mode_are_conservatively_excluded(): void
    {
        $test = $this->savedLesson('course-td-there-is-there-are', 'theory-driven');
        DB::table('tests')->insert(['name' => 'Shadow fixture', 'slug' => $test->slug, 'questions' => '[]']);
        $this->assertSame([], $this->paths());
        DB::table('tests')->delete();
        $test->update(['filters' => [...$test->filters, '__meta' => ['mode' => 'filters']]]);
        $this->assertSame([], $this->paths());
    }

    public function test_raw_slug_whitespace_and_route_delimiters_cannot_create_ready_entry_metadata(): void
    {
        foreach (['course-td-entry ', 'course-td-entry\\escape', 'course-td-entry?mode=x', 'course-td-entry#fragment', 'course-td-entry/step'] as $index => $slug) {
            $course = 'slug-fixture-'.$index;
            $this->savedLesson($slug, $course);
            $entry = app(PolyglotCourseManifestService::class)->sitemapEntryMetadata([$course])[$course];
            $this->assertFalse($entry['usable'], $slug);
        }
    }

    public function test_missing_or_invalid_blueprint_is_not_mistaken_for_a_ready_course(): void
    {
        $this->savedLesson('course-td-there-is-there-are', 'theory-driven');
        $blueprints = \Mockery::mock(PolyglotCourseBlueprintService::class);
        $blueprints->shouldReceive('getCourseBlueprint')->with('theory-driven')->once()->andThrow(new RuntimeException('Invalid fixture'));
        $this->app->instance(PolyglotCourseBlueprintService::class, $blueprints);
        $this->assertSame([], $this->paths());
    }

    public function test_theory_entry_uses_reachable_uk_hierarchy_and_does_not_depend_on_request_locale(): void
    {
        $root = $this->category('root', 'uk');
        $child = $this->category('nested', 'uk', $root->id);
        $page = $this->page($child);
        $this->block($page, 'uk', 'box', '<p>Справжній навчальний матеріал.</p>');
        app()->setLocale('en');
        $this->app['session']->put('locale', 'pl');
        $this->assertSame(['/courses/english-grammar-theory'], $this->paths());
        $root->update(['language' => 'en']);
        $this->assertSame([], $this->paths());
        $root->update(['language' => 'uk', 'parent_id' => 999999]);
        $this->assertSame([], $this->paths());
    }

    public function test_theory_page_text_headings_empty_markup_and_noninstructional_json_are_not_content(): void
    {
        $page = $this->page($this->category('basic-grammar'));
        $page->update(['text' => 'Legacy text is not rendered in the course.']);
        $this->block($page, 'uk', 'box', '<p>&nbsp;</p>');
        $this->block($page, 'uk', 'hero', json_encode(['title' => 'Heading', 'level' => 'A1']));
        $this->block($page, 'uk', 'usage-panels', json_encode(['sections' => [['color' => 'rose', 'label' => 'Heading']]]));
        $this->block($page, 'en', 'box', '<p>English-only content.</p>');
        $this->assertSame([], $this->paths());
        $instruction = $this->block($page, 'uk', 'forms-grid', json_encode(['items' => [['rules' => [['text' => 'Навчальне правило.']]]]]));
        $this->assertSame(['/courses/english-grammar-theory'], $this->paths());
        $instruction->delete();
        $this->assertSame([], $this->paths());
    }

    public function test_theory_entry_rejects_raw_page_slugs_that_the_lesson_resolver_cannot_match(): void
    {
        $page = $this->page($this->category('basic-grammar'));
        $this->block($page, 'uk', 'box', '<p>Instructional theory.</p>');
        $this->assertSame(['/courses/english-grammar-theory'], $this->paths());

        foreach (['Fixture-Lesson', " fixture-lesson \t"] as $slug) {
            $page->update(['slug' => $slug]);
            $this->assertSame([], $this->paths(), $slug);
        }

        $page->update(['slug' => 'fixture-lesson']);
        $this->assertSame(['/courses/english-grammar-theory'], $this->paths());
    }

    public function test_course_copies_and_closed_entry_paths_are_never_returned(): void
    {
        $page = $this->page($this->category('basic-grammar'));
        $this->block($page, 'uk', 'box', '<p>Instructional theory.</p>');
        $this->assertSame(['/courses/english-grammar-theory'], $this->paths());
        config(['coming-soon.prefixes' => ['/courses/english-grammar-theory/lesson/basic-grammar/']]);
        $this->assertSame([], $this->paths());
    }

    public function test_closed_catalog_is_distinct_from_an_available_course_home(): void
    {
        $this->savedLesson('course-td-there-is-there-are', 'theory-driven');
        config(['coming-soon.routes' => ['courses.index']]);
        $service = app(CourseSitemapMetadataService::class);
        $this->assertSame(['/courses/theory-driven'], $service->eligiblePaths());
        $this->assertFalse($service->catalogueEligible());
    }

    public function test_course_existence_queries_are_batched_and_do_not_select_question_bodies(): void
    {
        $this->savedLesson('polyglot-to-be-a1', 'polyglot-english-a1');
        $manifest = app(PolyglotCourseManifestService::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $manifest->sitemapEntryMetadata(['polyglot-english-a1']);
        $initial = DB::getQueryLog();
        for ($i = 2; $i <= 42; $i++) {
            $this->savedLesson('polyglot-fixture-'.$i, 'polyglot-english-a1', false, $i);
        }
        DB::flushQueryLog();
        $manifest->sitemapEntryMetadata(['polyglot-english-a1']);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertCount(3, $initial);
        $this->assertCount(3, $queries);
        foreach ($queries as $query) {
            $this->assertMatchesRegularExpression('/^select /i', $query['query']);
            // Nested EXISTS may use SELECT * without hydrating payload; outer rows must be metadata.
            $this->assertDoesNotMatchRegularExpression('/^select\s+(?:\*|["`]?questions["`]?\.\*)\s+/i', $query['query']);
        }
    }

    private function paths(): array
    {
        return app(CourseSitemapMetadataService::class)->eligiblePaths();
    }

    private function savedLesson(string $slug, string $course, bool $question = true, int $order = 1): SavedGrammarTest
    {
        $test = SavedGrammarTest::create(['uuid' => (string) Str::uuid(), 'name' => 'Course fixture', 'slug' => $slug,
            'filters' => ['course_slug' => $course, 'lesson_order' => $order, 'mode' => 'compose_tokens', 'level' => 'A1']]);
        if ($question) {
            $this->attachQuestion($test);
        }

        return $test;
    }

    private function attachQuestion(SavedGrammarTest $test, bool $answer = true): void
    {
        $uuid = (string) Str::uuid();
        // Raw fixture inserts avoid invoking question export observers.
        $id = DB::table('questions')->insertGetId(['uuid' => $uuid, 'question' => 'Я готовий.', 'level' => 'A1', 'type' => Question::TYPE_COMPOSE_TOKENS]);
        $optionId = DB::table('question_options')->insertGetId(['option' => 'Fixture token '.Str::random(10)]);
        DB::table('question_option_question')->insert(['question_id' => $id, 'option_id' => $optionId, 'flag' => 0]);
        if ($answer) {
            DB::table('question_answers')->insert(['question_id' => $id, 'option_id' => $optionId, 'marker' => 'a1']);
        }
        $test->questionLinks()->create(['question_uuid' => $uuid, 'position' => 1]);
    }

    private function category(string $slug, string $language = 'uk', ?int $parent = null): PageCategory
    {
        return PageCategory::create(['slug' => $slug, 'title' => $slug, 'language' => $language, 'type' => 'theory', 'parent_id' => $parent]);
    }

    private function page(PageCategory $category): Page
    {
        return Page::create(['slug' => 'fixture-lesson', 'title' => 'Fixture lesson', 'type' => 'theory', 'page_category_id' => $category->id]);
    }

    private function block(Page $page, string $locale, string $type, string $body): TextBlock
    {
        return TextBlock::create(['uuid' => (string) Str::uuid(), 'page_id' => $page->id, 'locale' => $locale,
            'type' => $type, 'heading' => 'Heading alone is insufficient', 'body' => $body, 'sort_order' => 1]);
    }
}
