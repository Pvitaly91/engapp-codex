<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Modules\GitDeployment\Services\GitHubApiClient;
use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use App\Services\ContentDeployment\ContentOpsCiStatusService;
use Mockery;
use Tests\TestCase;

class ContentOpsCiDispatchServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'git-deployment.contentops_ci_status.workflow_file' => 'contentops-release-gate.yml',
            'git-deployment.contentops_ci_status.default_base_ref' => 'origin/main',
            'git-deployment.contentops_ci_status.dispatch_with_release_check' => true,
            'git-deployment.contentops_ci_status.dispatch_strict' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_dry_run_builds_workflow_inputs_without_dispatching_github(): void
    {
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->once()->with('feature/content')->andReturn('abc123def456');
        $probe->shouldReceive('remoteBranchSha')->never();

        $status = Mockery::mock(ContentOpsCiStatusService::class);
        $status->shouldReceive('run')->never();

        $github = Mockery::mock(GitHubApiClient::class);
        $github->shouldReceive('dispatchWorkflow')->never();
        $this->app->instance(GitHubApiClient::class, $github);

        $result = (new ContentOpsCiDispatchService($probe, $status))->run([
            'ref' => 'feature/content',
            'domains' => 'v3',
            'dry_run' => true,
        ]);

        $this->assertSame('simulated', $result['dispatch']['status']);
        $this->assertSame('feature/content', $result['dispatch']['ref']);
        $this->assertSame('origin/main', $result['dispatch']['inputs']['base_ref']);
        $this->assertSame('abc123def456', $result['dispatch']['inputs']['head_ref']);
        $this->assertSame('abc123def456', $result['dispatch']['inputs']['target_sha']);
        $this->assertSame('v3', $result['dispatch']['inputs']['domains']);
        $this->assertSame('true', $result['dispatch']['inputs']['with_release_check']);
        $this->assertSame('true', $result['dispatch']['inputs']['strict']);
    }

    public function test_live_dispatch_requires_force(): void
    {
        $result = (new ContentOpsCiDispatchService(
            Mockery::mock(DeploymentGitRefProbe::class),
            Mockery::mock(ContentOpsCiStatusService::class)
        ))->run([
            'branch' => 'main',
            'dry_run' => false,
            'force' => false,
        ]);

        $this->assertSame('failed', $result['dispatch']['status']);
        $this->assertStringContainsString('--force', $result['error']['message']);
    }

    public function test_live_dispatch_calls_github_and_surfaces_new_run_when_visible(): void
    {
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('remoteBranchSha')->once()->with('main')->andReturn('abc123def456');

        $statusPayload = $this->statusPayload();
        $status = Mockery::mock(ContentOpsCiStatusService::class);
        $status->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['branch'] === 'main'
                && $options['sha'] === 'abc123def456'
                && $options['allow_in_progress'] === true
                && $options['require_success'] === false))
            ->andReturn($statusPayload);

        $github = Mockery::mock(GitHubApiClient::class);
        $github->shouldReceive('dispatchWorkflow')
            ->once()
            ->with('contentops-release-gate.yml', 'main', Mockery::on(fn (array $inputs): bool => $inputs['target_sha'] === 'abc123def456'
                && $inputs['base_ref'] === 'origin/main'
                && $inputs['domains'] === 'v3,page-v3'))
            ->andReturn(['http_status' => 204]);
        $this->app->instance(GitHubApiClient::class, $github);

        $result = (new ContentOpsCiDispatchService($probe, $status))->run([
            'branch' => 'main',
            'dry_run' => false,
            'force' => true,
        ]);

        $this->assertSame('dispatched', $result['dispatch']['status']);
        $this->assertSame(204, $result['dispatch']['http_status']);
        $this->assertTrue($result['run']['found']);
        $this->assertSame(321, $result['run']['id']);
    }

    public function test_only_configured_workflow_can_be_dispatched(): void
    {
        $result = (new ContentOpsCiDispatchService(
            Mockery::mock(DeploymentGitRefProbe::class),
            Mockery::mock(ContentOpsCiStatusService::class)
        ))->run([
            'workflow' => 'deploy.yml',
            'branch' => 'main',
            'dry_run' => true,
        ]);

        $this->assertSame('failed', $result['dispatch']['status']);
        $this->assertStringContainsString('Only the configured ContentOps Release Gate workflow', $result['error']['message']);
    }

    /**
     * @return array<string, mixed>
     */
    private function statusPayload(): array
    {
        return [
            'run' => [
                'id' => 321,
                'status' => 'queued',
                'conclusion' => null,
                'html_url' => 'https://github.com/Pvitaly91/engapp-codex/actions/runs/321',
                'head_sha' => 'abc123def456',
            ],
        ];
    }
}
