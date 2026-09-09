<?php

require dirname(__DIR__, 2).'/tests/bootstrap.php';

use App\Models\Question;
use App\Services\GrammarTestFilterService;
use Illuminate\Database\QueryException;
use Tests\Support\MySqlCompatibilityEnvironment;

/** The production builder and complete HTTP kernel, not a hand-written SQL facsimile. */
class M42CompatibilityProbe extends \Tests\Feature\MainTheoryTestSitemapReadinessTest
{
    use \Tests\Support\UsesDisposableMySql;

    public function probe(): array
    {
        $this->setUp();
        try {
            $lesson = new ReflectionMethod(\Tests\Feature\MainTheoryTestSitemapReadinessTest::class, 'lesson');
            $lesson->invoke($this, 'future-perfect', 'forms');
            $this->withoutExceptionHandling();
            $result = ['metadata' => MySqlCompatibilityEnvironment::metadata(), 'checks' => []];
            $executed = [];
            \Illuminate\Support\Facades\DB::listen(static function ($event) use (&$executed): void {
                if (str_contains($event->sql, 'TRIM(')) {
                    $executed[] = ['sql_shape' => $event->sql, 'binding_types' => array_map('get_debug_type', $event->bindings)];
                }
            });
            foreach (['usable_builder', 'sitemap_kernel'] as $id) {
                $executed = [];
                try {
                    if ($id === 'usable_builder') {
                        $actual = app(GrammarTestFilterService::class)->constrainUsableQuestions(Question::query())->exists();
                        $this->assertTrue($actual);
                        $result['checks'][$id] = ['pass' => true, 'actual' => $actual];
                    } else {
                        $response = $this->get('https://seo.production.test/sitemap.xml');
                        $response->assertOk()->assertSee('https://gramlyze.com/test/future-perfect/forms</loc>', false);
                        $result['checks'][$id] = ['pass' => true, 'status' => $response->status(), 'xml_sha256' => hash('sha256', $response->getContent())];
                    }
                    $result['checks'][$id]['executed'] = $executed;
                } catch (QueryException $e) {
                    $result['checks'][$id] = ['pass' => false, 'sqlstate' => $e->errorInfo[0] ?? null,
                        'error_code' => $e->errorInfo[1] ?? null, 'sql_shape' => $e->getSql(),
                        'binding_types' => array_map('get_debug_type', $e->getBindings())];
                }
            }

            return $result;
        } finally {
            $this->tearDown();
        }
    }
}

try {
    echo json_encode((new M42CompatibilityProbe('probe'))->probe(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    fwrite(STDERR, json_encode(['probe_error_class' => get_class($e), 'code' => $e->getCode(),
        'location' => basename($e->getFile()).':'.$e->getLine(),
        'sql_shape' => $e instanceof QueryException ? $e->getSql() : null,
        'sqlstate' => $e instanceof QueryException ? ($e->errorInfo[0] ?? null) : null,
        'error_code' => $e instanceof QueryException ? ($e->errorInfo[1] ?? null) : null,
        'trace_locations' => array_map(static fn ($frame) => basename($frame['file'] ?? '').':'.($frame['line'] ?? 0), array_slice($e->getTrace(), 0, 8))]));
    exit(2);
}
