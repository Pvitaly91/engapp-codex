<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentApplyService;
use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Services\ContentDeployment\ChangedContentApplyService;
use Mockery;
use Tests\TestCase;

class ChangedContentDeploymentApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_runs_deployment_dry_run_apply_from_preview_refs(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $applyService = Mockery::mock(ChangedContentApplyService::class);

        $previewService->shouldReceive('preview')
            ->once()
            ->andReturn($this->previewPayload());
        $applyService->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(function (array $options): bool {
                return ($options['base'] ?? null) === 'base-sha'
                    && ($options['head'] ?? null) === 'head-sha'
                    && ($options['dry_run'] ?? null) === true
                    && ($options['force'] ?? null) === false
                    && ($options['with_release_check'] ?? null) === true
                    && ($options['skip_release_check'] ?? null) === false
                    && ($options['check_profile'] ?? null) === 'release'
                    && ($options['strict'] ?? null) === true;
            }))
            ->andReturn($this->applyPayload(true));

        $service = new ChangedContentDeploymentApplyService($previewService, $applyService);
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
}
