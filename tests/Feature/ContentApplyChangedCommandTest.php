<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use App\Models\ContentOperationLock;
use App\Services\ContentDeployment\ChangedContentApplyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ContentApplyChangedCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();

        if (Schema::hasTable('content_operation_locks')) {
            ContentOperationLock::query()->delete();
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_command_passes_domains_diff_modes_and_runtime_flags_to_service(): void
    {
        $mock = Mockery::mock(ChangedContentApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'v3,page-v3' && ($options['staged'] ?? false) === true && ($options['dry_run'] ?? false) === true))
            ->andReturn($this->mockedResult(true));
        $mock->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/AlphaSeeder', Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'v3' && ($options['base'] ?? null) === 'HEAD~2' && ($options['head'] ?? null) === 'HEAD~1' && ($options['force'] ?? false) === true && ($options['skip_release_check'] ?? false) === true))
            ->andReturn($this->mockedResult(false, 'database/seeders/V3/Tests/AlphaSeeder', ['v3']));

        $this->app->instance(ChangedContentApplyService::class, $mock);

        $firstExitCode = Artisan::call('content:apply-changed', [
            '--domains' => 'v3,page-v3',
            '--staged' => true,
            '--dry-run' => true,
            '--json' => true,
        ]);
        $secondExitCode = Artisan::call('content:apply-changed', [
            'target' => 'database/seeders/V3/Tests/AlphaSeeder',
            '--domains' => 'v3',
            '--base' => 'HEAD~2',
            '--head' => 'HEAD~1',
            '--force' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
    }

    public function test_command_writes_report_and_supports_empty_plan_behavior(): void
    {
        $mock = Mockery::mock(ChangedContentApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult(true));
        $mock->shouldReceive('writeReport')
            ->once()
            ->andReturn('content-changed-apply-reports/mock-report.md');

        $this->app->instance(ChangedContentApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-changed', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['changed_packages']);
        $this->assertStringContainsString('content-changed-apply-reports/mock-report.md', $payload['artifacts']['report_path']);
    }

    public function test_human_output_includes_live_force_hint_after_dry_run(): void
    {
        $mock = Mockery::mock(ChangedContentApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult(true, null, ['v3', 'page-v3'], [
                $this->executionPackage('page-v3', 'database/seeders/Page_V3/Tests/PageSeeder', 'seed'),
            ], true, true));

        $this->app->instance(ChangedContentApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-changed', [
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Live Apply Hint:', $output);
        $this->assertStringContainsString('php artisan content:apply-changed --force --with-release-check --skip-release-check', $output);
    }

    public function test_command_records_dry_run_history_row(): void
    {
        $mock = Mockery::mock(ChangedContentApplyService::class);
        $mock->shouldReceive('run')->once()->andReturn($this->mockedResult(true));

        $this->app->instance(ChangedContentApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-changed', [
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('content_operation_runs', 1);
        $this->assertSame('dry_run', ContentOperationRun::query()->latest('id')->value('status'));
    }

    public function test_command_blocked_by_active_lock_records_blocked_history_row(): void
    {
        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'owner-token',
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $mock = Mockery::mock(ChangedContentApplyService::class);
        $mock->shouldNotReceive('run');
        $this->app->instance(ChangedContentApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-changed', [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('content_operation_lock', $payload['error']['stage']);
        $this->assertSame('active_lock_present', $payload['error']['reason']);
        $this->assertSame('blocked', ContentOperationRun::query()->latest('id')->value('status'));
    }

    /**
     * @param  list<string>  $domains
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function mockedResult(bool $dryRun, ?string $input = null, array $domains = ['v3', 'page-v3'], array $packages = [], bool $withReleaseCheck = false, bool $skipReleaseCheck = false): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => $input,
                'domains' => $domains,
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => $withReleaseCheck,
                'skip_release_check' => $skipReleaseCheck,
                'check_profile' => 'release',
                'strict' => false,
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => count($packages),
                    'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['action'] ?? null) === 'seed')),
                    'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['action'] ?? null) === 'refresh')),
                    'deleted_cleanup_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['action'] ?? null) === 'unseed_deleted')),
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'domains' => [
                    'v3' => null,
                    'page-v3' => null,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => true,
                'summary' => [
                    'candidates' => count($packages),
                    'ok' => count($packages),
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => $dryRun,
                'force' => ! $dryRun,
                'fail_fast' => true,
                'scope_transactional' => false,
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
                'upsert_present' => [
                    'started' => $dryRun ? 0 : count($packages),
                    'completed' => $dryRun ? 0 : count($packages),
                    'succeeded' => $dryRun ? 0 : count($packages),
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => $packages,
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function executionPackage(string $domain, string $path, string $action): array
    {
        return [
            'domain' => $domain,
            'package_key' => $path,
            'relative_path' => $path,
            'action' => $action,
            'executed' => false,
            'status' => 'pending',
            'seed_run_removed' => false,
            'seed_run_logged' => false,
            'service_result' => null,
        ];
    }

    private function ensureContentOperationRunsTable(): void
    {
        if (Schema::hasTable('content_operation_runs')) {
            if (! Schema::hasColumn('content_operation_runs', 'replayed_from_run_id')) {
                Schema::table('content_operation_runs', function (Blueprint $table): void {
                    $table->unsignedBigInteger('replayed_from_run_id')->nullable()->after('id');
                });
            }

            return;
        }

        Schema::create('content_operation_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('replayed_from_run_id')->nullable();
            $table->string('operation_kind', 64);
            $table->string('trigger_source', 64);
            $table->json('domains');
            $table->string('base_ref')->nullable();
            $table->string('head_ref')->nullable();
            $table->json('base_refs_by_domain')->nullable();
            $table->boolean('dry_run')->default(false);
            $table->boolean('strict')->default(false);
            $table->boolean('with_release_check')->nullable();
            $table->boolean('skip_release_check')->nullable();
            $table->boolean('bootstrap_uninitialized')->default(false);
            $table->string('status', 32)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->json('summary')->nullable();
            $table->string('payload_json_path')->nullable();
            $table->string('report_path')->nullable();
            $table->text('error_excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    private function ensureContentOperationLocksTable(): void
    {
        if (Schema::hasTable('content_operation_locks')) {
            return;
        }

        Schema::create('content_operation_locks', function (Blueprint $table): void {
            $table->id();
            $table->string('lock_key')->unique();
            $table->string('owner_token')->unique();
            $table->string('operation_kind');
            $table->string('trigger_source');
            $table->json('domains')->nullable();
            $table->unsignedBigInteger('content_operation_run_id')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->timestamp('acquired_at')->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->json('meta')->nullable();
        });
    }
}
