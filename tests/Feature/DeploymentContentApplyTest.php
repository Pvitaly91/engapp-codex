<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentApplyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDeploymentTables();
    }

    public function test_shell_content_apply_dry_run_endpoint_returns_json_payload(): void
    {
        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('run')
            ->once()
            ->andReturn($this->contentApplyPayload('ready', true));

        $this->app->instance(ChangedContentDeploymentApplyService::class, $applyService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-apply-preview?source_kind=deploy&branch=main');

        $response->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('content_apply.dry_run', true)
            ->assertJsonPath('deployment.base_ref', 'base-sha')
            ->assertJsonPath('content_sync.before.domains.v3.status', 'drifted');
    }

    public function test_native_deploy_runs_content_apply_after_successful_code_update(): void
    {
        $preview = $this->previewPayload();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('runFromPreview')
            ->once()
            ->with($preview, Mockery::on(fn (array $options): bool => ($options['requested'] ?? null) === true && ($options['dry_run'] ?? null) === false))
            ->andReturn($this->contentApplyPayload('completed', false));

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
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'success');
        $response->assertSessionHas('deployment_native_content_apply.status', 'completed');
    }

    public function test_native_deploy_marks_error_when_content_apply_fails(): void
    {
        $preview = $this->previewPayload();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('runFromPreview')
            ->once()
            ->andReturn($this->contentApplyPayload('content_apply_failed', false, [
                'stage' => 'execution',
                'message' => 'Changed content apply failed.',
            ]));

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
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'error');
        $response->assertSessionHas('deployment_native_content_apply.status', 'content_apply_failed');
    }

    public function test_native_deploy_surfaces_deploy_failed_content_payload_when_code_update_fails(): void
    {
        $preview = $this->previewPayload();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('deploymentFailedResult')
            ->once()
            ->with($preview, 'Native deploy exploded.', Mockery::on(fn (array $options): bool => ($options['requested'] ?? null) === true))
            ->andReturn($this->contentApplyPayload('deploy_failed', false, [
                'stage' => 'deploy',
                'message' => 'Native deploy exploded.',
            ], false));
        $applyService->shouldNotReceive('runFromPreview');

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('deploy')->once()->with('main')->andThrow(new \RuntimeException('Native deploy exploded.'));

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
        $response->assertSessionHas('deployment_native_content_apply.status', 'deploy_failed');
    }

    public function test_native_rollback_runs_content_apply_after_restore(): void
    {
        $preview = $this->previewPayload('backup_restore');
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $applyService = Mockery::mock(ChangedContentDeploymentApplyService::class);
        $applyService->shouldReceive('runFromPreview')
            ->once()
            ->andReturn($this->contentApplyPayload('completed', false));

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('rollback')->once()->with('deadbeef')->andReturn([
            'logs' => ['rollback ok'],
            'message' => 'Rollback ok.',
        ]);

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(ChangedContentDeploymentApplyService::class, $applyService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/rollback', [
                'commit' => 'deadbeef',
                'apply_changed_content' => '1',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'success');
        $response->assertSessionHas('deployment_native_content_apply.status', 'completed');
    }

    /**
     * @return array<string, mixed>
     */
    private function previewPayload(string $sourceKind = 'deploy'): array
    {
        return [
            'deployment' => [
                'mode' => 'native',
                'source_kind' => $sourceKind,
                'base_ref' => 'base-sha',
                'head_ref' => $sourceKind === 'deploy' ? 'head-sha' : 'deadbeef',
                'branch' => $sourceKind === 'deploy' ? 'main' : null,
                'commit' => $sourceKind === 'backup_restore' ? 'deadbeef' : null,
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
                        'target_head_ref' => $sourceKind === 'deploy' ? 'head-sha' : 'deadbeef',
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
                        'target_head_ref' => $sourceKind === 'deploy' ? 'head-sha' : 'deadbeef',
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
    private function contentApplyPayload(string $status, bool $dryRun, ?array $error = null, bool $liveExecuted = true): array
    {
        return [
            'deployment' => [
                'mode' => 'native',
                'source_kind' => 'deploy',
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'content_apply_requested' => true,
                'content_apply_executed' => $liveExecuted,
            ],
            'preview' => $this->previewPayload(),
            'content_apply' => [
                'executed' => true,
                'dry_run' => $dryRun,
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
            'content_sync' => [
                'before' => $this->previewPayload()['content_sync'],
                'after' => [
                    'domains' => [
                        'v3' => [
                            'domain' => 'v3',
                            'sync_state_ref' => $dryRun ? 'v3-synced-sha' : 'head-sha',
                            'fallback_base_ref' => 'head-sha',
                            'effective_base_ref' => $dryRun ? 'v3-synced-sha' : 'head-sha',
                            'fallback_used' => false,
                            'drift_from_code_ref' => $dryRun,
                            'sync_state_uninitialized' => false,
                            'status' => $dryRun ? 'drifted' : 'synced',
                            'target_head_ref' => 'head-sha',
                        ],
                        'page-v3' => [
                            'domain' => 'page-v3',
                            'sync_state_ref' => $dryRun ? null : 'head-sha',
                            'fallback_base_ref' => 'head-sha',
                            'effective_base_ref' => 'head-sha',
                            'fallback_used' => ! $dryRun,
                            'drift_from_code_ref' => false,
                            'sync_state_uninitialized' => $dryRun,
                            'status' => $dryRun ? 'uninitialized' : 'synced',
                            'target_head_ref' => 'head-sha',
                        ],
                    ],
                ],
                'advanced_domains' => $dryRun ? [] : ['v3', 'page-v3'],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => false,
                'reasons' => [],
            ],
            'artifacts' => [
                'report_path' => storage_path('app/deployment-content-apply-reports/mock.md'),
            ],
            'status' => $status,
            'error' => $error,
        ];
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
