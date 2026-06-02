<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use App\Services\ContentDeployment\ContentOperationReplayService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ContentRetryRunCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_command_passes_replay_options_and_returns_json(): void
    {
        $run = ContentOperationRun::query()->create([
            'replayed_from_run_id' => null,
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'base_ref' => 'old-base',
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => false,
            'bootstrap_uninitialized' => false,
            'status' => 'failed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        $service = Mockery::mock(ContentOperationReplayService::class);
        $service->shouldReceive('run')
            ->once()
            ->with($run->id, Mockery::on(fn (array $options): bool => ($options['force'] ?? false) === true
                && ($options['dry_run'] ?? true) === false
                && ($options['allow_success'] ?? false) === true
                && ($options['reuse_original_mode'] ?? false) === true
                && ($options['strict'] ?? false) === true
                && ($options['trigger_source'] ?? null) === 'cli'))
            ->andReturn([
                'original_run' => [
                    'id' => $run->id,
                    'operation_kind' => 'apply_changed',
                    'status' => 'failed',
                    'domains' => ['v3'],
                ],
                'replay' => [
                    'mode' => [
                        'dry_run' => false,
                        'force' => true,
                        'allow_success' => true,
                        'reuse_original_mode' => true,
                    ],
                    'validation' => [
                        'blocked' => false,
                        'warnings' => [],
                        'reasons' => [],
                    ],
                    'resolved_context' => [
                        'operation_kind' => 'apply_changed',
                        'domains' => ['v3'],
                        'base_ref' => 'old-base',
                        'head_ref' => 'head-sha',
                        'base_refs_by_domain' => [],
                        'bootstrap_uninitialized' => false,
                    ],
                    'result' => [
                        'plan' => [
                            'summary' => [
                                'changed_packages' => 1,
                                'deleted_cleanup_candidates' => 0,
                                'seed_candidates' => 1,
                                'refresh_candidates' => 0,
                            ],
                        ],
                        'error' => null,
                    ],
                    'new_run' => [
                        'id' => 9001,
                        'status' => 'success',
                        'payload_json_path' => storage_path('app/content-operation-runs/2026/04/24/9001.json'),
                        'report_path' => storage_path('app/content-operation-replays/2026/04/24/replay.md'),
                    ],
                    'status' => 'success',
                ],
                'artifacts' => [
                    'report_path' => storage_path('app/content-operation-replays/2026/04/24/replay.md'),
                ],
                'error' => null,
                'status' => 'success',
            ]);

        $this->app->instance(ContentOperationReplayService::class, $service);

        $exitCode = Artisan::call('content:retry-run', [
            'run' => $run->id,
            '--force' => true,
            '--allow-success' => true,
            '--reuse-original-mode' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('success', $payload['replay']['status']);
        $this->assertSame(9001, $payload['replay']['new_run']['id']);
    }

    public function test_human_output_renders_blockers_and_new_run_metadata(): void
    {
        $run = ContentOperationRun::query()->create([
            'replayed_from_run_id' => null,
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['page-v3'],
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => false,
            'bootstrap_uninitialized' => false,
            'status' => 'failed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        $service = Mockery::mock(ContentOperationReplayService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn([
                'original_run' => [
                    'id' => $run->id,
                    'operation_kind' => 'apply_sync',
                    'status' => 'failed',
                    'domains' => ['page-v3'],
                ],
                'replay' => [
                    'mode' => [
                        'dry_run' => true,
                        'force' => false,
                        'allow_success' => false,
                        'reuse_original_mode' => false,
                    ],
                    'validation' => [
                        'blocked' => true,
                        'warnings' => [
                            ['code' => 'content_sync_state_advanced_since_original_run', 'message' => 'Sync cursor changed.'],
                        ],
                        'reasons' => [
                            ['code' => 'current_deployed_ref_changed_since_original_run', 'message' => 'Deployed ref changed.'],
                        ],
                    ],
                    'resolved_context' => [
                        'operation_kind' => 'apply_sync',
                        'domains' => ['page-v3'],
                        'base_refs_by_domain' => ['page-v3' => 'old-page'],
                        'head_ref' => 'head-sha',
                        'bootstrap_uninitialized' => false,
                    ],
                    'result' => null,
                    'new_run' => [
                        'id' => 9002,
                        'status' => 'blocked',
                        'payload_json_path' => storage_path('app/content-operation-runs/2026/04/24/9002.json'),
                        'report_path' => storage_path('app/content-operation-replays/2026/04/24/replay-blocked.md'),
                    ],
                    'status' => 'blocked',
                ],
                'artifacts' => [
                    'report_path' => storage_path('app/content-operation-replays/2026/04/24/replay-blocked.md'),
                ],
                'error' => [
                    'stage' => 'replay_validation',
                    'message' => 'Replay was blocked before canonical execution started.',
                ],
                'status' => 'blocked',
            ]);

        $this->app->instance(ContentOperationReplayService::class, $service);

        $exitCode = Artisan::call('content:retry-run', [
            'run' => $run->id,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Command: content:retry-run', $output);
        $this->assertStringContainsString('[BLOCK] current_deployed_ref_changed_since_original_run', $output);
        $this->assertStringContainsString('New Run ID: 9002', $output);
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
}
