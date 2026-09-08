<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Services\QuestionExportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\TestCase;

class SmokeIsolationTest extends TestCase
{
    public function test_paths_are_isolated_before_singleton_compiler_is_created(): void
    {
        $root = IsolatedTestEnvironment::prepare();
        $this->assertSame($root, storage_path());
        $this->assertNotSame(base_path(), $this->app->environmentPath());
        foreach ([config('view.compiled'), config('session.files'), config('filesystems.disks.local.root'),
            config('filesystems.disks.public.root'), config('filesystems.disks.s3.root'),
            config('questions.export_path'), dirname($this->app->getCachedConfigPath()),
            dirname($this->app->getCachedPackagesPath()), dirname($this->app->getCachedServicesPath()),
            dirname($this->app->getCachedRoutesPath()), dirname($this->app->getCachedEventsPath())] as $path) {
            IsolatedTestEnvironment::assertOwnedPath($path);
        }
        $compiler = app('blade.compiler');
        $source = resource_path('views/home.blade.php');
        $path = $compiler->getCompiledPath($source);
        $this->assertSame(realpath(config('view.compiled')), realpath(dirname($path)));
        $compiler->compile($source);
        $this->refreshApplication();
        $this->assertFileExists($path, 'A later application must not delete a live compiler path.');
        $this->assertSame($path, app('blade.compiler')->getCompiledPath($source));
    }

    public function test_guard_accepts_actual_sqlite_memory_connection(): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    public function test_guard_rejects_mismatched_actual_pdo_database_before_schema_changes(): void
    {
        $path = storage_path('framework/testing/mismatched.sqlite');
        $connection = new SQLiteConnection(new PDO('sqlite:'.$path), ':memory:', '', ['driver' => 'sqlite']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Actual SQLite file differs');
        IsolatedTestEnvironment::assertSafeDatabase($connection);
    }

    public function test_guard_rejects_unowned_configured_database_before_opening_lazy_pdo(): void
    {
        $opened = false;
        $connection = new SQLiteConnection(function () use (&$opened): never {
            $opened = true;
            // Even against a broken guard this regression never opens a real file.
            throw new RuntimeException('The unsafe PDO resolver must not run.');
        }, base_path('storage/framework/testing/unowned-guard.sqlite'), '', ['driver' => 'sqlite']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Refusing a shared filesystem path');
        try {
            IsolatedTestEnvironment::assertSafeDatabase($connection);
        } finally {
            $this->assertFalse($opened, 'Reject ownership before resolving a lazy connection.');
        }
    }

    public function test_guard_refuses_working_storage_path_without_touching_it(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Refusing a shared filesystem path');
        IsolatedTestEnvironment::assertOwnedPath(base_path('storage/framework/views'));
    }

    public function test_question_events_are_only_disabled_in_fixture_scope_and_real_export_stays_isolated(): void
    {
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        Schema::create('questions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid');
            $table->text('question');
            $table->timestamps();
        });
        // Guard the write boundary too: reverting the service path fails BEFORE it can
        // touch working snapshots, so this regression is safe even against old code.
        File::partialMock()->shouldReceive('ensureDirectoryExists')->once()->andReturnUsing(function (string $path): void {
            IsolatedTestEnvironment::assertOwnedPath($path);
            (new Filesystem)->ensureDirectoryExists($path);
        });
        File::shouldReceive('put')->once()->andReturnUsing(function (string $path, string $contents): int {
            IsolatedTestEnvironment::assertOwnedPath(dirname($path));
            return (new Filesystem)->put($path, $contents);
        });
        $question = Question::withoutEvents(fn () => Question::create([
            'uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', 'question' => 'Fixture text',
        ]));
        $snapshot = config('questions.export_path').DIRECTORY_SEPARATOR.$question->uuid.'.json';
        $this->assertFileDoesNotExist($snapshot);
        $question->update(['question' => 'Observer is active']);
        IsolatedTestEnvironment::assertOwnedPath($snapshot);
        $this->assertSame('Observer is active', json_decode(file_get_contents($snapshot), true)['question']['question']);
    }

    public function test_question_export_default_destination_is_unchanged_without_writing_it(): void
    {
        config(['questions' => []]);
        $directory = database_path('seeders/questions');
        $uuid = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
        File::partialMock()->shouldReceive('ensureDirectoryExists')->once()->with($directory);
        File::shouldReceive('put')->once()->withArgs(function (string $path, string $contents) use ($directory, $uuid): bool {
            $this->assertSame($directory.DIRECTORY_SEPARATOR.$uuid.'.json', $path);
            $this->assertSame('Default destination contract', json_decode($contents, true)['question']['question']);
            return true;
        })->andReturn(1);
        app(QuestionExportService::class)->export(new Question([
            'uuid' => $uuid, 'question' => 'Default destination contract',
        ]));
    }
}
