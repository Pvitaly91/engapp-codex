<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Modules\GitDeployment\Services\GitHubApiClient;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ChangedContentPlanService;
use Mockery;
use Tests\TestCase;

class ChangedContentDeploymentPreviewServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_standard_deploy_preview_reuses_changed_content_plan_service(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $githubApi = Mockery::mock(GitHubApiClient::class);

        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('base-sha');
        $probe->shouldReceive('resolveCommit')->once()->with('origin/main')->andReturn('head-sha');
        $probe->shouldReceive('remoteBranchSha')->once()->with('main')->andReturn('head-sha');
        $planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(function (array $options): bool {
                return ($options['base'] ?? null) === 'base-sha'
                    && ($options['head'] ?? null) === 'origin/main'
                    && ($options['with_release_check'] ?? null) === true
                    && ($options['strict'] ?? null) === true;
            }))
            ->andReturn($this->planResult());
        $nativeDeployment->shouldNotReceive('headCommit');
        $githubApi->shouldNotReceive('getBranch');

        $service = new ChangedContentDeploymentPreviewService($planner, $probe, $nativeDeployment, $githubApi);
        $result = $service->preview([
            'mode' => 'standard',
            'source_kind' => 'deploy',
            'branch' => 'main',
        ]);

        $this->assertNull($result['error']);
        $this->assertSame('base-sha', $result['deployment']['base_ref']);
        $this->assertSame('origin/main', $result['deployment']['head_ref']);
        $this->assertFalse($result['gate']['blocked']);
        $this->assertSame(1, $result['content_plan']['summary']['seed_candidates']);
    }

    public function test_standard_deploy_preview_blocks_when_local_tracking_ref_is_stale(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $githubApi = Mockery::mock(GitHubApiClient::class);

        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('base-sha');
        $probe->shouldReceive('resolveCommit')->once()->with('origin/release')->andReturn('old-sha');
        $probe->shouldReceive('remoteBranchSha')->once()->with('release')->andReturn('new-sha');
        $planner->shouldNotReceive('run');
        $nativeDeployment->shouldNotReceive('headCommit');
        $githubApi->shouldNotReceive('getBranch');

        $service = new ChangedContentDeploymentPreviewService($planner, $probe, $nativeDeployment, $githubApi);
        $result = $service->preview([
            'mode' => 'standard',
            'source_kind' => 'deploy',
            'branch' => 'release',
        ]);

        $this->assertTrue($result['gate']['blocked']);
        $this->assertSame('deployment_preview', $result['error']['stage']);
        $this->assertStringContainsString('stale compared to remote', $result['error']['message']);
    }

    public function test_native_deploy_preview_fails_when_remote_commit_is_not_available_locally(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $githubApi = Mockery::mock(GitHubApiClient::class);

        $nativeDeployment->shouldReceive('headCommit')->once()->andReturn('native-base');
        $githubApi->shouldReceive('getBranch')->once()->with('main')->andReturn([
            'object' => ['sha' => 'remote-head'],
        ]);
        $probe->shouldReceive('commitExists')->once()->with('remote-head')->andReturn(false);
        $planner->shouldNotReceive('run');
        $probe->shouldNotReceive('currentHeadCommit');
        $probe->shouldNotReceive('resolveCommit');
        $probe->shouldNotReceive('remoteBranchSha');

        $service = new ChangedContentDeploymentPreviewService($planner, $probe, $nativeDeployment, $githubApi);
        $result = $service->preview([
            'mode' => 'native',
            'source_kind' => 'deploy',
            'branch' => 'main',
        ]);

        $this->assertTrue($result['gate']['blocked']);
        $this->assertStringContainsString('not available in local git objects', $result['error']['message']);
    }

    public function test_strict_plan_warnings_block_the_deployment_gate(): void
    {
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $githubApi = Mockery::mock(GitHubApiClient::class);

        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('base-sha');
        $probe->shouldReceive('resolveCommit')->once()->with('origin/main')->andReturn('head-sha');
        $probe->shouldReceive('remoteBranchSha')->once()->with('main')->andReturn('head-sha');
        $planner->shouldReceive('run')->once()->andReturn($this->planResult([
            'warnings' => 1,
        ], [
            'stage' => 'planning',
            'reason' => 'warnings_are_fatal',
            'message' => 'Warnings are fatal.',
        ]));
        $nativeDeployment->shouldNotReceive('headCommit');
        $githubApi->shouldNotReceive('getBranch');

        $service = new ChangedContentDeploymentPreviewService($planner, $probe, $nativeDeployment, $githubApi);
        $result = $service->preview([
            'mode' => 'standard',
            'source_kind' => 'deploy',
            'branch' => 'main',
        ]);

        $this->assertTrue($result['gate']['blocked']);
        $this->assertContains('Warnings are fatal.', $result['gate']['reasons']);
        $this->assertContains('Strict deployment gate treats 1 content warning(s) as blockers.', $result['gate']['reasons']);
    }

    /**
     * @param  array<string, int>  $summaryOverrides
     * @param  array<string, mixed>|null  $error
     * @return array<string, mixed>
     */
    private function planResult(array $summaryOverrides = [], ?array $error = null): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => 'base-sha',
                'head' => 'origin/main',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => ['v3', 'page-v3'],
                'resolved_roots' => [
                    [
                        'domain' => 'v3',
                        'resolved_root_absolute_path' => base_path('database/seeders/V3'),
                        'resolved_root_relative_path' => 'database/seeders/V3',
                        'single_package' => false,
                    ],
                    [
                        'domain' => 'page-v3',
                        'resolved_root_absolute_path' => base_path('database/seeders/Page_V3'),
                        'resolved_root_relative_path' => 'database/seeders/Page_V3',
                        'single_package' => false,
                    ],
                ],
                'single_package' => false,
            ],
            'summary' => array_merge([
                'changed_packages' => 1,
                'deleted_cleanup_candidates' => 0,
                'seed_candidates' => 1,
                'refresh_candidates' => 0,
                'skipped' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ], $summaryOverrides),
            'phases' => [
                'cleanup_deleted' => [],
                'upsert_present' => [
                    [
                        'domain' => 'v3',
                        'relative_path' => 'database/seeders/V3/Tests/AlphaSeeder',
                        'package_type' => 'v3_test',
                        'change_type' => 'added',
                        'package_state' => 'not_seeded',
                        'recommended_action' => 'seed',
                        'release_check' => [
                            'status' => 'pass',
                        ],
                    ],
                ],
            ],
            'packages' => [],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => $error,
        ];
    }
}
