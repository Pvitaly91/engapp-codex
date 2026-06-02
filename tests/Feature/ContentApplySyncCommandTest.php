<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use App\Models\ContentOperationLock;
use App\Services\ContentDeployment\ContentSyncApplyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ContentApplySyncCommandTest extends TestCase
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

    public function test_command_passes_runtime_flags_to_service(): void
    {
        $mock = Mockery::mock(ContentSyncApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'page-v3'
                && ($options['dry_run'] ?? false) === true
                && ($options['bootstrap_uninitialized'] ?? false) === true
                && ($options['skip_release_check'] ?? false) === true))
            ->andReturn($this->mockedResult(true));

        $this->app->instance(ContentSyncApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-sync', [
            '--domains' => 'page-v3',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--bootstrap-uninitialized' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
    }

    public function test_command_writes_report_and_outputs_live_hint_after_dry_run(): void
    {
        $mock = Mockery::mock(ContentSyncApplyService::class);
        $mock->shouldReceive('run')->once()->andReturn($this->mockedResult(true, true));
        $mock->shouldReceive('writeReport')->once()->andReturn('content-sync-apply-reports/mock.md');

        $this->app->instance(ContentSyncApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-sync', [
            '--dry-run' => true,
            '--write-report' => true,
            '--bootstrap-uninitialized' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Live Repair Hint:', $output);
        $this->assertStringContainsString('php artisan content:apply-sync --force --bootstrap-uninitialized', $output);
        $this->assertStringContainsString('content-sync-apply-reports/mock.md', $output);
    }

    public function test_command_records_sync_history_row(): void
    {
        $mock = Mockery::mock(ContentSyncApplyService::class);
        $mock->shouldReceive('run')->once()->andReturn($this->mockedResult(true, true));

        $this->app->instance(ContentSyncApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-sync', [
            '--dry-run' => true,
            '--bootstrap-uninitialized' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('content_operation_runs', 1);
        $this->assertSame('apply_sync', ContentOperationRun::query()->latest('id')->value('operation_kind'));
    }

    public function test_command_blocked_by_lock_does_not_call_sync_apply_service(): void
    {
        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'owner-token',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $mock = Mockery::mock(ContentSyncApplyService::class);
        $mock->shouldNotReceive('run');
        $this->app->instance(ContentSyncApplyService::class, $mock);

        $exitCode = Artisan::call('content:apply-sync', [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('blocked', $payload['status']);
        $this->assertSame('active_lock_present', $payload['error']['reason']);
        $this->assertSame('blocked', ContentOperationRun::query()->latest('id')->value('status'));
    }

    /**
     * @return array<string, mixed>
     */
    private function mockedResult(bool $dryRun, bool $bootstrapRequested = false): array
    {
        return [
            'domains_before' => [
                'v3' => [
                    'status' => 'drifted',
                    'sync_state_ref' => 'old-v3',
                    'current_deployed_ref' => 'code-sha',
                    'bootstrap_required' => false,
                    'last_attempted_status' => 'success',
                ],
                'page-v3' => [
                    'status' => $bootstrapRequested ? 'uninitialized' : 'synced',
                    'sync_state_ref' => null,
                    'current_deployed_ref' => 'code-sha',
                    'bootstrap_required' => $bootstrapRequested,
                    'last_attempted_status' => null,
                ],
            ],
            'plan' => [
                'deployment_refs' => [
                    'current_deployed_ref' => 'code-sha',
                ],
                'summary' => [
                    'synced_domains' => 0,
                    'drifted_domains' => 1,
                    'uninitialized_domains' => $bootstrapRequested ? 1 : 0,
                    'blocked' => 0,
                    'warnings' => $bootstrapRequested ? 1 : 0,
                    'changed_packages' => 1,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                ],
                'bootstrap' => [
                    'required_domains' => $bootstrapRequested ? ['page-v3'] : [],
                ],
                'options' => [
                    'with_release_check' => false,
                    'check_profile' => 'release',
                    'strict' => false,
                ],
            ],
            'apply' => [
                'executed' => true,
                'dry_run' => $dryRun,
                'changed_content_result' => [
                    'plan' => [
                        'summary' => [
                            'changed_packages' => 1,
                            'deleted_cleanup_candidates' => 0,
                            'seed_candidates' => 1,
                            'refresh_candidates' => 0,
                        ],
                    ],
                    'preflight' => [
                        'summary' => [
                            'ok' => 1,
                            'warn' => 0,
                            'fail' => 0,
                        ],
                    ],
                ],
                'bootstrap' => [
                    'requested' => $bootstrapRequested,
                    'simulated' => $bootstrapRequested ? [['domain' => 'page-v3', 'head_ref' => 'code-sha']] : [],
                    'applied' => [],
                ],
            ],
            'domains_after' => [
                'v3' => [
                    'status' => $dryRun ? 'drifted' : 'synced',
                    'sync_state_ref' => $dryRun ? 'old-v3' : 'code-sha',
                    'current_deployed_ref' => 'code-sha',
                    'bootstrap_required' => false,
                    'last_attempted_status' => $dryRun ? 'dry_run' : 'success',
                ],
                'page-v3' => [
                    'status' => $bootstrapRequested ? 'uninitialized' : 'synced',
                    'sync_state_ref' => $bootstrapRequested ? null : 'code-sha',
                    'current_deployed_ref' => 'code-sha',
                    'bootstrap_required' => $bootstrapRequested,
                    'last_attempted_status' => null,
                ],
            ],
            'status' => $dryRun ? 'dry_run' : 'completed',
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
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
