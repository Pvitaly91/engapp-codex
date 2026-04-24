<?php

namespace Tests\Unit;

use App\Models\ContentOperationLock;
use App\Models\ContentOperationRun;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ChangedContentDeploymentApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    public function test_it_runs_deployment_dry_run_apply_from_preview_refs(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);
        $syncStateService = Mockery::mock(ContentSyncStateService::class);

        $previewService->shouldReceive('preview')
            ->once()
            ->andReturn($this->previewPayload());
        $syncStateService->shouldReceive('describe')
            ->once()
            ->with(['v3', 'page-v3'], ['v3' => 'head-sha', 'page-v3' => 'head-sha'], 'head-sha')
            ->andReturn($this->afterSyncPayload());
        $applyService->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(function (array $options): bool {
                return ($options['base'] ?? null) === 'base-sha'
                    && ($options['base_refs_by_domain']['v3'] ?? null) === 'v3-synced-sha'
                    && ($options['base_refs_by_domain']['page-v3'] ?? null) === 'base-sha'
                    && ($options['head'] ?? null) === 'head-sha'
                    && ($options['dry_run'] ?? null) === true
                    && ($options['force'] ?? null) === false
                    && ($options['with_release_check'] ?? null) === true
                    && ($options['skip_release_check'] ?? null) === false
                    && ($options['check_profile'] ?? null) === 'release'
                    && ($options['strict'] ?? null) === true;
            }))
            ->andReturn($this->applyPayload(true));

        $service = new ChangedContentDeploymentApplyService($previewService, $applyService, $syncStateService);
        $result = $service->run([
            'mode' => 'native',
            'source_kind' => 'deploy',
            'branch' => 'main',
        ], [
            'dry_run' => true,
            'requested' => true,
        ]);

        $this->assertSame('ready', $result['status']);
        $this->assertTrue($result['content_apply']['executed']);
        $this->assertFalse($result['deployment']['content_apply_executed']);
        $this->assertSame('base-sha', $result['deployment']['base_ref']);
        $this->assertSame('head-sha', $result['deployment']['head_ref']);
        $this->assertSame('synced', $result['content_sync']['after']['domains']['v3']['status']);
        $this->assertNotNull($result['artifacts']['report_path']);
        $this->assertFileExists($result['artifacts']['report_path']);
    }

    public function test_run_from_preview_marks_content_apply_failed_when_canonical_apply_fails(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);

        $previewService->shouldNotReceive('preview');
        $applyService->shouldReceive('run')
            ->once()
            ->andReturn($this->applyPayload(false, [
                'stage' => 'execution',
                'message' => 'upsert failed',
            ]));

        $service = new ChangedContentDeploymentApplyService($previewService, $applyService);
        $result = $service->runFromPreview($this->previewPayload(), [
            'requested' => true,
            'dry_run' => false,
        ]);

        $this->assertSame('content_apply_failed', $result['status']);
        $this->assertSame('upsert failed', $result['error']['message']);
        $this->assertTrue($result['deployment']['content_apply_executed']);
    }

    public function test_it_can_build_a_deploy_failed_payload_without_running_content_apply(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);

        $previewService->shouldNotReceive('preview');
        $applyService->shouldNotReceive('run');

        $service = new ChangedContentDeploymentApplyService($previewService, $applyService);
        $result = $service->deploymentFailedResult($this->previewPayload(), 'Code update failed.', [
            'requested' => true,
            'dry_run' => false,
        ]);

        $this->assertSame('deploy_failed', $result['status']);
        $this->assertFalse($result['content_apply']['executed']);
        $this->assertSame('Code update failed.', $result['error']['message']);
        $this->assertNotNull($result['artifacts']['report_path']);
    }

    public function test_it_records_deployment_apply_history_row(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);

        $previewService->shouldReceive('preview')->once()->andReturn($this->previewPayload());
        $applyService->shouldReceive('run')->once()->andReturn($this->applyPayload(true));

        $service = new ChangedContentDeploymentApplyService(
            $previewService,
            $applyService,
            null,
            app(ContentOperationRunService::class)
        );

        $result = $service->run([
            'mode' => 'standard',
            'source_kind' => 'deploy',
            'branch' => 'main',
        ], [
            'dry_run' => true,
            'requested' => true,
            'trigger_source' => 'deployment_ui',
        ]);

        $this->assertSame('ready', $result['status']);
        $this->assertArrayHasKey('operation_run', $result);
        $this->assertDatabaseCount('content_operation_runs', 1);
        $this->assertSame('deployment_apply_changed', ContentOperationRun::query()->latest('id')->value('operation_kind'));
    }

    public function test_run_from_preview_reuses_reserved_deployment_lock_without_releasing_it(): void
    {
        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();

        $lockService = app(ContentOperationLockService::class);
        $lease = $lockService->acquire([
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => 'deployment_ui',
            'domains' => ['v3', 'page-v3'],
            'meta' => [
                'deployment_lock_reservation' => true,
            ],
        ]);

        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);
        $applyService->shouldReceive('run')->once()->andReturn($this->applyPayload(false));

        $service = new ChangedContentDeploymentApplyService(
            $previewService,
            $applyService,
            null,
            null,
            $lockService
        );

        $result = $service->runFromPreview($this->previewPayload(), [
            'requested' => true,
            'dry_run' => false,
            'content_lock_lease' => $lease,
            'release_content_lock' => false,
        ]);

        $this->assertSame('completed', $result['status']);
        $this->assertSame($lease['owner_token'], $lockService->current()?->owner_token);

        $lockService->release($lease['owner_token']);
    }

    /**
     * @return array<string, mixed>
     */
    private function previewPayload(): array
    {
        return [
            'deployment' => [
                'mode' => 'native',
                'source_kind' => 'deploy',
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'branch' => 'main',
                'commit' => null,
                'with_release_check' => true,
                'check_profile' => 'release',
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                    'resolved_roots' => [],
                ],
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 1,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'content_sync' => [
                'domains' => [
                    'v3' => [
                        'domain' => 'v3',
                        'sync_state_ref' => 'v3-synced-sha',
                        'fallback_base_ref' => 'base-sha',
                        'effective_base_ref' => 'v3-synced-sha',
                        'fallback_used' => false,
                        'drift_from_code_ref' => true,
                        'sync_state_uninitialized' => false,
                        'status' => 'drifted',
                        'target_head_ref' => 'head-sha',
                    ],
                    'page-v3' => [
                        'domain' => 'page-v3',
                        'sync_state_ref' => null,
                        'fallback_base_ref' => 'base-sha',
                        'effective_base_ref' => 'base-sha',
                        'fallback_used' => true,
                        'drift_from_code_ref' => false,
                        'sync_state_uninitialized' => true,
                        'status' => 'uninitialized',
                        'target_head_ref' => 'head-sha',
                    ],
                ],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => false,
                'reasons' => [],
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $error
     * @return array<string, mixed>
     */
    private function applyPayload(bool $dryRun, ?array $error = null): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => 'base-sha',
                'base_refs_by_domain' => [
                    'v3' => 'v3-synced-sha',
                    'page-v3' => 'base-sha',
                ],
                'head' => 'head-sha',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => ['v3', 'page-v3'],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => true,
                'skip_release_check' => false,
                'check_profile' => 'release',
                'strict' => true,
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => 1,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                    'deleted_cleanup_candidates' => 0,
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
                    'candidates' => 1,
                    'ok' => 1,
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
                    'started' => $dryRun ? 0 : 1,
                    'completed' => $dryRun ? 0 : 1,
                    'succeeded' => $dryRun ? 0 : 1,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => $error,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function afterSyncPayload(): array
    {
        return [
            'v3' => [
                'domain' => 'v3',
                'sync_state_ref' => 'head-sha',
                'fallback_base_ref' => 'head-sha',
                'effective_base_ref' => 'head-sha',
                'fallback_used' => false,
                'drift_from_code_ref' => false,
                'sync_state_uninitialized' => false,
                'status' => 'synced',
                'target_head_ref' => 'head-sha',
            ],
            'page-v3' => [
                'domain' => 'page-v3',
                'sync_state_ref' => 'head-sha',
                'fallback_base_ref' => 'head-sha',
                'effective_base_ref' => 'head-sha',
                'fallback_used' => false,
                'drift_from_code_ref' => false,
                'sync_state_uninitialized' => false,
                'status' => 'synced',
                'target_head_ref' => 'head-sha',
            ],
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
