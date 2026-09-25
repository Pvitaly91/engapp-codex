<?php

require dirname(__DIR__, 2).'/tests/bootstrap.php';

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SavedGrammarTest;
use App\Models\TextBlock;
use App\Services\CourseSitemapMetadataService;
use App\Services\GrammarTestFilterService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\MySqlCompatibilityEnvironment;
use Tests\Support\RebuildsComposeTestSchema;

try {
    // PHPUnit 12 keeps runner configuration in a registry, so a standalone
    // diagnostic must not call TestCase::setUp() outside that runner. Boot the
    // same isolated Laravel application directly and dispatch through its real
    // HTTP kernel instead.
    MySqlCompatibilityEnvironment::application();
    $schema = new class
    {
        use RebuildsComposeTestSchema;

        public function rebuild(): void
        {
            $this->rebuildComposeTestSchema();
        }

        protected function assertComposeTestDatabase(): void
        {
            MySqlCompatibilityEnvironment::assertSafeDatabase(DB::connection());
        }
    };
    $schema->rebuild();
    config([
        'app.debug' => false,
        'app.locale' => 'uk',
        'coming-soon.enabled' => true,
        'coming-soon.prefixes' => ['/test/'],
        'site-mode.production_domains' => ['seo.production.test'],
        'site-mode.production_origin' => 'https://gramlyze.com',
        'site-mode.production_locales' => ['uk'],
        'site-mode.response_cache.enabled' => false,
    ]);
    app()->setLocale('uk');
    $courses = Mockery::mock(CourseSitemapMetadataService::class);
    $courses->shouldReceive('eligiblePaths')->andReturn([]);
    app()->instance(CourseSitemapMetadataService::class, $courses);

    $category = PageCategory::create([
        'slug' => 'future-perfect', 'title' => 'future-perfect', 'language' => 'uk', 'type' => 'theory',
    ]);
    $page = Page::create([
        'title' => 'future-perfect forms', 'slug' => 'future-perfect-forms', 'type' => 'theory', 'page_category_id' => $category->id,
    ]);
    TextBlock::create([
        'uuid' => (string) Str::uuid(), 'page_id' => $page->id, 'page_category_id' => $category->id,
        'locale' => 'uk', 'type' => 'box', 'body' => '<p>Навчальна теорія для цього тесту.</p>',
    ]);
    foreach (['Tenses\\M4', 'Polyglot'] as $kind) {
        $seeder = 'Database\\Seeders\\V3\\'.$kind.'\\Fixture'.$page->id.'Seeder';
        $question = Question::withoutEvents(fn () => Question::create([
            'uuid' => (string) Str::uuid(), 'question' => 'She {a1} her work by noon.', 'difficulty' => 1,
            'level' => 'A1', 'type' => '0', 'flag' => 0, 'seeder' => $seeder,
            'options_by_marker' => ['a1' => ['will have finished', 'will finish']],
        ]));
        $option = QuestionOption::firstOrCreate(['option' => 'will have finished']);
        $question->answers()->create(['marker' => 'a1', 'option_id' => $option->id]);
        $saved = SavedGrammarTest::create([
            'uuid' => (string) Str::uuid(), 'name' => $page->title.' '.$kind,
            'slug' => 'm42-fixture-'.$question->id,
            'filters' => ['seeder_classes' => [$seeder], 'levels' => ['A1'], 'num_questions' => 2,
                'prompt_generator' => ['source_type' => 'theory_page', 'theory_page_id' => $page->id]],
        ]);
        $saved->questionLinks()->create(['question_uuid' => $question->uuid, 'position' => 1]);
    }
    $result = ['metadata' => MySqlCompatibilityEnvironment::metadata(), 'checks' => []];
    $executed = [];
    DB::listen(static function ($event) use (&$executed): void {
        if (str_contains($event->sql, 'TRIM(')) {
            $executed[] = ['sql_shape' => $event->sql, 'binding_types' => array_map('get_debug_type', $event->bindings)];
        }
    });
    foreach (['usable_builder', 'sitemap_kernel'] as $id) {
        $executed = [];
        try {
            if ($id === 'usable_builder') {
                $actual = app(GrammarTestFilterService::class)->constrainUsableQuestions(Question::query())->exists();
                $result['checks'][$id] = ['pass' => $actual, 'actual' => $actual];
            } else {
                $request = Request::create('https://seo.production.test/sitemap.xml', 'GET');
                $response = app(Kernel::class)->handle($request);
                $content = $response->getContent();
                $result['checks'][$id] = [
                    'pass' => $response->getStatusCode() === 200 && str_contains($content, 'https://gramlyze.com/test/future-perfect/forms</loc>'),
                    'status' => $response->getStatusCode(),
                    'xml_sha256' => hash('sha256', $content),
                ];
            }
            $result['checks'][$id]['executed'] = $executed;
        } catch (QueryException $e) {
            $result['checks'][$id] = ['pass' => false, 'sqlstate' => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null, 'sql_shape' => $e->getSql(),
                'binding_types' => array_map('get_debug_type', $e->getBindings())];
        }
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    fwrite(STDERR, json_encode(['probe_error_class' => get_class($e), 'code' => $e->getCode(),
        'location' => basename($e->getFile()).':'.$e->getLine(),
        'sql_shape' => $e instanceof QueryException ? $e->getSql() : null,
        'sqlstate' => $e instanceof QueryException ? ($e->errorInfo[0] ?? null) : null,
        'error_code' => $e instanceof QueryException ? ($e->errorInfo[1] ?? null) : null,
        'trace_locations' => array_map(static fn ($frame) => basename($frame['file'] ?? '').':'.($frame['line'] ?? 0), array_slice($e->getTrace(), 0, 8))]));
    exit(2);
}
