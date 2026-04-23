<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use Mockery;
use Tests\TestCase;

class DeploymentContentGateTest extends TestCase
{
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
}
