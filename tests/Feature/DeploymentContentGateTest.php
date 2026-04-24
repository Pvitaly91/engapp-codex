<?php

namespace Tests\Feature;

use App\Models\ContentOperationLock;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDeploymentTables();
        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();
        config()->set('content-deployment.lock.enabled', true);
        config()->set('content-deployment.lock.ttl_seconds', 3600);
        config()->set('content-deployment.lock.stale_after_seconds', 60);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_shell_deploy_is_blocked_before_git_update_starts(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($this->blockedPreview());
        $previewService->shouldReceive('gateBlocks')->once()->andReturn(true);
        $previewService->shouldReceive('gateReasons')->once()->andReturn(['Blocked by content gate.']);

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/deploy', [
                'branch' => 'main',
            ]);

        $response->assertRedirect(route('deployment.index'));
        $response->assertSessionHas('deployment.status', 'error');
        $response->assertSessionHas('deployment_content_preview');
    }

    public function test_native_deploy_is_blocked_before_api_update_starts(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($this->blockedPreview());
        $previewService->shouldReceive('gateBlocks')->once()->andReturn(true);
        $previewService->shouldReceive('gateReasons')->once()->andReturn(['Blocked by content gate.']);

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldNotReceive('deploy');

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/deploy', [
                'branch' => 'main',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'error');
        $response->assertSessionHas('deployment_native_content_preview');
    }

    public function test_native_deploy_with_content_apply_is_blocked_by_active_lock_before_code_update(): void
    {
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'active-owner',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $preview = $this->readyPreview();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('lockBlockedResult')
            ->once()
            ->andReturn($this->blockedApplyPayload());
        $applyService->shouldNotReceive('runFromPreview');

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldNotReceive('deploy');

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(ChangedContentDeploymentApplyService::class, $applyService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/deploy', [
                'branch' => 'main',
                'apply_changed_content' => '1',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'error');
        $response->assertSessionHas('deployment_native_content_apply.status', 'content_apply_blocked');
    }

    public function test_native_deploy_can_take_over_stale_lock_before_code_update(): void
    {
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'stale-owner',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now()->subHour(),
            'heartbeat_at' => now()->subMinutes(10),
            'expires_at' => now()->subMinute(),
            'status' => 'active',
        ]);

        $preview = $this->readyPreview();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('runFromPreview')
            ->once()
            ->with($preview, Mockery::on(fn (array $options): bool => is_array($options['content_lock_lease'] ?? null)
                && ($options['release_content_lock'] ?? null) === false
                && ($options['requested'] ?? null) === true))
            ->andReturn($this->completedApplyPayload());

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('deploy')->once()->with('main')->andReturn([
            'logs' => ['deploy ok'],
            'message' => 'Native deploy ok.',
        ]);

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(ChangedContentDeploymentApplyService::class, $applyService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/deploy', [
                'branch' => 'main',
                'apply_changed_content' => '1',
                'content_apply_takeover_stale_lock' => '1',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'success');
        $this->assertNull(ContentOperationLock::query()->first());
    }

    public function test_native_deploy_without_content_apply_does_not_reserve_lock(): void
    {
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'active-owner',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $preview = $this->readyPreview();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldNotReceive('lockBlockedResult');
        $applyService->shouldNotReceive('runFromPreview');

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('deploy')->once()->with('main')->andReturn([
            'logs' => ['deploy ok'],
            'message' => 'Native deploy ok.',
        ]);

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(ChangedContentDeploymentApplyService::class, $applyService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/deploy', [
                'branch' => 'main',
                'apply_changed_content' => '0',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'success');
        $this->assertSame('active-owner', ContentOperationLock::query()->value('owner_token'));
    }

    public function test_native_rollback_is_blocked_before_restore_starts(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($this->blockedPreview('backup_restore'));
        $previewService->shouldReceive('gateBlocks')->once()->andReturn(true);
        $previewService->shouldReceive('gateReasons')->once()->andReturn(['Blocked by content gate.']);

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldNotReceive('rollback');

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/rollback', [
                'commit' => 'deadbeef',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'error');
        $response->assertSessionHas('deployment_native_content_preview');
    }

    /**
     * @return array<string, mixed>
     */
    private function blockedPreview(string $sourceKind = 'deploy'): array
    {
        return [
            'deployment' => [
                'mode' => $sourceKind === 'deploy' ? 'standard' : 'native',
                'source_kind' => $sourceKind,
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
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
                    'warnings' => 1,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => [
                    'stage' => 'planning',
                    'message' => 'Warnings are fatal.',
                ],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => true,
                'reasons' => ['Warnings are fatal.'],
            ],
            'error' => [
                'stage' => 'planning',
                'message' => 'Warnings are fatal.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readyPreview(string $sourceKind = 'deploy'): array
    {
        return [
            'deployment' => [
                'mode' => 'native',
                'source_kind' => $sourceKind,
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'branch' => $sourceKind === 'deploy' ? 'main' : null,
                'commit' => $sourceKind === 'backup_restore' ? 'deadbeef' : null,
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
                'domains' => [],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => false,
                'reasons' => [],
            ],
            'lock' => [
                'required_for_content_apply' => true,
                'current_status' => 'free',
                'blocked' => false,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blockedApplyPayload(): array
    {
        return [
            'deployment' => [
                'content_apply_requested' => true,
                'content_apply_executed' => false,
            ],
            'content_apply' => [
                'executed' => false,
                'dry_run' => false,
                'result' => null,
            ],
            'status' => 'content_apply_blocked',
            'lock' => [
                'acquired' => false,
                'status' => 'active',
            ],
            'error' => [
                'stage' => 'content_operation_lock',
                'reason' => 'active_lock_present',
                'message' => 'Blocked by active lock.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function completedApplyPayload(): array
    {
        return [
            'deployment' => [
                'content_apply_requested' => true,
                'content_apply_executed' => true,
            ],
            'content_apply' => [
                'executed' => true,
                'dry_run' => false,
                'result' => [
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
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'status' => 'completed',
            'error' => null,
        ];
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

    private function ensureDeploymentTables(): void
    {
        if (! Schema::hasTable('backup_branches')) {
            Schema::create('backup_branches', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('commit_hash');
                $table->timestamp('pushed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('branch_usage_history')) {
            Schema::create('branch_usage_history', function (Blueprint $table): void {
                $table->id();
                $table->string('branch_name');
                $table->string('action');
                $table->text('description')->nullable();
                $table->json('paths')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
