<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Modules\GitDeployment\Services\GitHubApiClient;
use App\Services\ContentDeployment\ContentOpsCiStatusService;
use Mockery;
use Tests\TestCase;

class ContentOpsCiStatusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'git-deployment.github.owner' => 'Pvitaly91',
            'git-deployment.github.repo' => 'engapp-codex',
            'git-deployment.contentops_ci_status.cache_ttl_seconds' => 0,
            'git-deployment.contentops_ci_status.require_exact_sha' => true,
            'git-deployment.contentops_ci_status.accepted_conclusions' => ['success'],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_exact_sha_success_is_passed(): void
    {
        $service = $this->service([
            $this->workflowRun([
                'head_sha' => 'abcdef1234567890',
                'conclusion' => 'success',
            ]),
        ]);

        $result = $service->run([
            'sha' => 'abcdef1',
            'require_success' => true,
        ]);

        $this->assertSame('pass', $result['readiness']['status']);
        $this->assertSame('ci_passed', $result['readiness']['code']);
        $this->assertTrue($result['match']['exact_sha_verified']);
        $this->assertFalse($result['readiness']['exit_would_fail']);
    }

    public function test_sha_mismatch_is_fatal_when_exact_sha_is_required(): void
    {
        $service = $this->service([
            $this->workflowRun([
                'head_branch' => 'main',
                'head_sha' => 'ffffffffffffffff',
                'conclusion' => 'success',
            ]),
        ], expectedBranch: 'main');

        $result = $service->run([
            'branch' => 'main',
            'sha' => 'abcdef1234567890',
            'require_success' => true,
        ]);

        $this->assertSame('fail', $result['readiness']['status']);
        $this->assertSame('ci_sha_mismatch', $result['readiness']['code']);
        $this->assertTrue($result['match']['sha_mismatch']);
        $this->assertTrue($result['readiness']['exit_would_fail']);
    }

    public function test_missing_run_warns_unless_success_is_required(): void
    {
        $service = $this->service([]);

        $warning = $service->run([
            'sha' => 'abcdef1234567890',
        ]);
        $failure = $service->run([
            'sha' => 'abcdef1234567890',
            'require_success' => true,
        ]);

        $this->assertSame('warn', $warning['readiness']['status']);
        $this->assertFalse($warning['readiness']['exit_would_fail']);
        $this->assertSame('fail', $failure['readiness']['status']);
        $this->assertTrue($failure['readiness']['exit_would_fail']);
    }

    public function test_running_run_is_warning_when_in_progress_is_allowed(): void
    {
        $service = $this->service([
            $this->workflowRun([
                'status' => 'in_progress',
                'conclusion' => null,
                'head_sha' => 'abcdef1234567890',
            ]),
        ]);

        $result = $service->run([
            'sha' => 'abcdef1234567890',
            'allow_in_progress' => true,
        ]);

        $this->assertSame('warn', $result['readiness']['status']);
        $this->assertSame('ci_running', $result['readiness']['code']);
        $this->assertFalse($result['readiness']['exit_would_fail']);
    }

    /**
     * @param  list<array<string, mixed>>  $runs
     */
    private function service(array $runs, ?string $expectedBranch = null): ContentOpsCiStatusService
    {
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->byDefault()->andReturn(null);
        $probe->shouldReceive('currentHeadCommit')->byDefault()->andReturn('local-head-sha');

        $github = Mockery::mock(GitHubApiClient::class);
        $github->shouldReceive('listWorkflowRuns')
            ->with('contentops-release-gate.yml', Mockery::on(function (array $query) use ($expectedBranch): bool {
                if ($expectedBranch === null) {
                    return true;
                }

                return ($query['branch'] ?? null) === $expectedBranch;
            }))
            ->andReturn(['workflow_runs' => $runs]);

        $this->app->instance(GitHubApiClient::class, $github);

        return new ContentOpsCiStatusService($probe);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function workflowRun(array $overrides = []): array
    {
        return array_merge([
            'id' => 123,
            'status' => 'completed',
            'conclusion' => 'success',
            'head_branch' => 'main',
            'head_sha' => 'abcdef1234567890',
            'html_url' => 'https://github.com/Pvitaly91/engapp-codex/actions/runs/123',
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'run_started_at' => now()->toIso8601String(),
        ], $overrides);
    }
}
