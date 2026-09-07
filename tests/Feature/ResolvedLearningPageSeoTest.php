<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\PolyglotCourseManifestService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

/** Actual routes, resolvers, controllers, views and complete HTTP middleware; no external HTTP. */
class ResolvedLearningPageSeoTest extends TestCase
{
    use RebuildsComposeTestSchema;

    private const HOST = 'https://seo.production.test';
    private const ORIGIN = 'https://gramlyze.com';

    private array $temporaryEnvironment = [];

    public function createApplication(): Application
    {
        if (Env::get('APP_ENV') !== 'testing'
            || Env::get('DB_CONNECTION') !== 'sqlite'
            || Env::get('DB_DATABASE') !== ':memory:'
            || Env::get('DATABASE_URL')
            || Env::get('CACHE_DRIVER') !== 'array'
            || Env::get('SESSION_DRIVER') !== 'array') {
            throw new RuntimeException('SEO integration tests require testing env, SQLite :memory: without DATABASE_URL, and array cache/session.');
        }

        foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE'] as $key) {
            if (! Env::get($key)) {
                $this->temporaryEnvironment[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
                $value = sys_get_temp_dir().'/gramlyze-seo-'.bin2hex(random_bytes(12)).'.php';
                putenv($key.'='.$value);
                $_ENV[$key] = $_SERVER[$key] = $value;
            }
            if (is_file((string) Env::get($key))) {
                throw new RuntimeException('SEO integration tests cannot load an existing bootstrap cache.');
            }
        }

        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->temporaryEnvironment as $key => [$process, $env, $server]) {
            putenv($process === false ? $key : $key.'='.$process);
            unset($_ENV[$key], $_SERVER[$key]);
            if ($env !== null) {
                $_ENV[$key] = $env;
            }
            if ($server !== null) {
                $_SERVER[$key] = $server;
            }
        }
        $this->temporaryEnvironment = [];
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite'
            || DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException('Refusing to rebuild a non-memory test database.');
        }

        config([
            'app.debug' => false,
            'app.locale' => 'uk',
            'coming-soon.enabled' => true,
            'site-mode.production_domains' => ['seo.production.test'],
            'site-mode.production_origin' => self::ORIGIN,
            'site-mode.production_locales' => ['uk'],
            'site-mode.response_cache.enabled' => true,
            'site-mode.response_cache.excluded_paths' => [],
        ]);
        $this->rebuildComposeTestSchema();
        $this->createLesson('future-perfect', 'questions', 'Future Perfect Questions', true);
        $this->createLesson('future-perfect', 'forms', 'Future Perfect Forms', true);
        $this->createLesson('past-simple', 'questions', 'Past Simple Questions');
    }

    public function test_both_questions_topics_render_indexable_complete_html_for_html_and_wildcard_accept(): void
    {
        foreach (['future-perfect', 'past-simple'] as $topic) {
            foreach (['text/html', '*/*'] as $accept) {
                $this->app['session']->flush();
                $response = $this->get(self::HOST.'/test/'.$topic.'/questions', ['Accept' => $accept]);
                $response->assertOk()->assertViewIs('test-show')
                    ->assertHeader('X-Site-Mode', 'production')->assertHeaderMissing('X-Robots-Tag');
                $this->assertCanonical($response, '/test/'.$topic.'/questions');
                $this->assertNoRobotsMeta($response);
                $this->assertSame('test.js.questions', $this->app['request']->route()->getName());
                $this->assertSame($topic, $this->app['request']->route('slug'));
                $this->assertCount(2, $response->viewData('questionData'));
                $this->assertContains('Accept', $response->getVary());
                $this->assertContains('Host', $response->getVary());
            }
        }
    }

    public function test_accept_switching_and_real_json_endpoint_preserve_representation_and_bank(): void
    {
        $html = $this->get(self::HOST.'/test/future-perfect/questions', ['Accept' => 'text/html'])->assertOk();
        $bank = collect($html->viewData('questionData'))->keyBy('uuid');

        $this->app['session']->flush();
        $jsonParent = $this->get(self::HOST.'/test/future-perfect/questions', ['Accept' => 'application/json']);
        $jsonParent->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertContains('Accept', $jsonParent->getVary());
        $this->assertStringNotContainsString('rel="canonical"', $jsonParent->getContent());

        $json = $this->get(self::HOST.'/test/future-perfect/questions/questions?mode=saved-test-js-v2', ['Accept' => 'application/json']);
        $json->assertOk()->assertJsonCount(2, 'questions')
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertContains('Accept', $json->getVary());
        foreach ($json->json('questions') as $question) {
            $this->assertSame($bank[$question['uuid']]['question'], $question['question']);
            $this->assertSame($bank[$question['uuid']]['answer_map'], $question['answer_map']);
        }

        $again = $this->get(self::HOST.'/test/future-perfect/questions', ['Accept' => '*/*']);
        $again->assertOk()->assertHeaderMissing('X-Robots-Tag');
        $this->assertCanonical($again, '/test/future-perfect/questions');
        $this->assertSame($bank->keys()->sort()->values()->all(), collect($again->viewData('questionData'))->pluck('uuid')->sort()->values()->all());
    }

    public function test_normal_test_alternate_mode_and_unknown_slug_keep_their_existing_seo_policies(): void
    {
        $normal = $this->get(self::HOST.'/test/future-perfect/forms', ['Accept' => 'text/html']);
        $normal->assertOk()->assertHeaderMissing('X-Robots-Tag');
        $this->assertCanonical($normal, '/test/future-perfect/forms');
        $this->assertNoRobotsMeta($normal);

        $alternate = $this->get(self::HOST.'/test/future-perfect/questions/step', ['Accept' => 'text/html']);
        $alternate->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->assertCanonical($alternate, '/test/future-perfect/questions');

        $unknown = $this->get(self::HOST.'/test/nonexistent-seo-topic/questions', ['Accept' => 'text/html']);
        $unknown->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringNotContainsString('rel="canonical"', $unknown->getContent());
    }

    public function test_query_input_cannot_spoof_resolved_html_identity_or_remove_api_noindex(): void
    {
        $query = http_build_query([
            'canonical' => 'https://attacker.invalid/spoof',
            'resolved_html_test' => 'future-perfect/questions',
            'App\\Support\\ResolvedHtmlTest' => 'future-perfect/questions',
        ]);
        $json = $this->get(self::HOST.'/test/future-perfect/questions/questions?'.$query, ['Accept' => 'application/json']);
        $json->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringNotContainsString('attacker.invalid', $json->getContent());

        $unknown = $this->get(self::HOST.'/test/nonexistent-seo-topic/questions?source=theory&'.$query, ['Accept' => 'text/html']);
        $unknown->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringNotContainsString('rel="canonical"', $unknown->getContent());
    }

    public function test_source_query_cleanup_is_not_given_html_identity_until_a_successful_html_response(): void
    {
        foreach (['text/html', 'application/json'] as $accept) {
            $this->app['session']->flush();
            $response = $this->get(self::HOST.'/test/future-perfect/questions?source=theory', ['Accept' => $accept]);
            $response->assertRedirect(self::HOST.'/test/future-perfect/questions')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $this->assertStringNotContainsString('rel="canonical"', $response->getContent());
        }
    }

    public function test_development_noindex_remains_on_resolved_html_learning_pages(): void
    {
        $response = $this->get('http://gramlyze.loc/test/future-perfect/questions', ['Accept' => 'text/html']);
        $response->assertOk()->assertHeader('X-Site-Mode', 'development')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertCanonical($response, '/test/future-perfect/questions');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_course_copies_map_to_the_resolved_theory_pages_including_nested_categories(): void
    {
        foreach (['maibutni-formy/future-perfect/future-perfect-questions', 'past-simple/past-simple-questions'] as $path) {
            $theory = $this->get(self::HOST.'/theory/'.$path)->assertOk();
            $course = $this->get(self::HOST.'/courses/english-grammar-theory/lesson/'.$path)->assertOk();
            $this->assertCanonical($theory, '/theory/'.$path);
            $this->assertCanonical($course, '/theory/'.$path);
            $course->assertHeaderMissing('X-Robots-Tag')->assertViewIs('courses.theory-lesson');
            $this->assertNoRobotsMeta($course);
            $this->assertSame($theory->viewData('page')->id, $course->viewData('page')->id);
            $this->assertSame($course->viewData('page')->id, $course->viewData('lesson')['page_id']);
        }
    }

    public function test_course_home_and_lesson_test_keep_their_own_canonicals_and_unknown_lesson_has_none(): void
    {
        foreach (['/courses', '/courses/english-grammar-theory', '/courses/english-grammar-theory/lesson/past-simple/past-simple-questions/test'] as $path) {
            $response = $this->get(self::HOST.$path)->assertOk();
            $this->assertCanonical($response, $path);
        }

        $unknown = $this->get(self::HOST.'/courses/english-grammar-theory/lesson/past-simple/unknown?theory_url=/theory/past-simple/past-simple-questions');
        $unknown->assertNotFound();
        $this->assertStringNotContainsString('rel="canonical"', $unknown->getContent());
    }

    public function test_other_course_progress_remains_a_technical_json_endpoint(): void
    {
        $manifest = \Mockery::mock(PolyglotCourseManifestService::class);
        $manifest->shouldReceive('build')->once()->with('seo-progress-fixture')
            ->andReturn(['total_lessons' => 1, 'lessons' => []]);
        $this->app->instance(PolyglotCourseManifestService::class, $manifest);

        $response = $this->get(self::HOST.'/courses/seo-progress-fixture/progress', ['Accept' => 'application/json']);
        $response->assertOk()->assertExactJson(['authenticated' => false])
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertSame('courses.progress.show', $this->app['request']->route()->getName());
        $this->assertStringNotContainsString('canonical', $response->getContent());
    }

    private function assertCanonical(TestResponse $response, string $path): void
    {
        $document = new \DOMDocument();
        @$document->loadHTML($response->getContent());
        $links = (new \DOMXPath($document))->query('//head/link[@rel="canonical"]');
        $this->assertCount(1, $links);
        $this->assertSame(self::ORIGIN.$path, $links->item(0)->getAttribute('href'));
    }

    private function assertNoRobotsMeta(TestResponse $response): void
    {
        $this->assertDoesNotMatchRegularExpression('/<meta\b[^>]*name=["\']robots["\']/i', $response->getContent());
    }

    private function createLesson(string $topic, string $pageSegment, string $title, bool $nested = false): void
    {
        $parent = $nested ? PageCategory::firstOrCreate(['slug' => 'maibutni-formy'], [
            'title' => 'Майбутні форми', 'language' => 'uk', 'type' => 'theory',
        ]) : null;
        $category = PageCategory::firstOrCreate(['slug' => $topic], [
            'title' => $topic, 'language' => 'uk', 'type' => 'theory', 'parent_id' => $parent?->id,
        ]);
        $page = Page::create([
            'slug' => $topic.'-'.$pageSegment, 'title' => $title, 'type' => 'theory',
            'page_category_id' => $category->id,
        ]);
        TextBlock::create([
            'uuid' => (string) Str::uuid(), 'page_id' => $page->id, 'page_category_id' => $category->id,
            'locale' => 'uk', 'type' => 'box', 'column' => 'left',
            'body' => '<p>Навчальний матеріал: '.$title.'.</p>',
        ]);

        foreach (['Tenses\\Seo', 'Polyglot'] as $kind) {
            $seeder = 'Database\\Seeders\\V3\\'.$kind.'\\SeoFixture'.$page->id.'Seeder';
            $question = Question::withoutEvents(fn () => Question::create([
                'uuid' => (string) Str::uuid(), 'question' => 'She {a1} her work by noon.',
                'difficulty' => 1, 'level' => 'A1', 'type' => '0', 'flag' => 0, 'seeder' => $seeder,
                'options_by_marker' => ['a1' => ['will have finished', 'will finish']],
            ]));
            $option = QuestionOption::firstOrCreate(['option' => 'will have finished']);
            $question->answers()->create(['marker' => 'a1', 'option_id' => $option->id]);
            $saved = SavedGrammarTest::create([
                'uuid' => (string) Str::uuid(), 'name' => $title.' '.$kind,
                'slug' => 'seo-fixture-'.$question->id,
                'filters' => [
                    'levels' => ['A1'], 'num_questions' => 2, 'seeder_classes' => [$seeder],
                    'prompt_generator' => ['theory_page_id' => $page->id, 'source_type' => 'theory_page'],
                ],
            ]);
            $saved->questionLinks()->create(['question_uuid' => $question->uuid, 'position' => 1]);
        }
    }
}
