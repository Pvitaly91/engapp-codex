<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Services\ContentDeployment\ChangedContentPlanService;
use App\Services\ContentDeployment\ContentSyncPlanService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Mockery;
use Tests\TestCase;

class ContentSyncPlanServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_builds_repair_plan_only_for_initialized_drifted_domains(): void
    {
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);

        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('code-sha');
        $syncState->shouldReceive('describe')
            ->once()
            ->with(['v3', 'page-v3'], ['v3' => 'code-sha', 'page-v3' => 'code-sha'], 'code-sha')
            ->andReturn([
                'v3' => [
                    'sync_state_ref' => 'old-v3',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'old-v3',
                    'status' => 'drifted',
                    'last_attempted_status' => 'success',
                    'last_attempted_at' => now()->subMinute()->toIso8601String(),
                ],
                'page-v3' => [
                    'sync_state_ref' => null,
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => null,
                    'status' => 'uninitialized',
                    'last_attempted_status' => null,
                    'last_attempted_at' => null,
                ],
            ]);
        $planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === ['v3']
                && ($options['base_refs_by_domain']['v3'] ?? null) === 'old-v3'
                && ($options['head'] ?? null) === 'code-sha'))
            ->andReturn($this->changedPlanResult(['v3']));

        $result = (new ContentSyncPlanService($syncState, $planner, $probe))->run([
            'with_release_check' => true,
            'check_profile' => 'release',
        ]);

        $this->assertNull($result['error']);
        $this->assertSame('code-sha', $result['deployment_refs']['current_deployed_ref']);
        $this->assertSame('drifted', $result['domains']['v3']['status']);
        $this->assertSame('old-v3', $result['domains']['v3']['effective_base_ref']);
        $this->assertTrue($result['domains']['v3']['drifted']);
        $this->assertSame('uninitialized', $result['domains']['page-v3']['status']);
        $this->assertTrue($result['domains']['page-v3']['bootstrap_required']);
        $this->assertSame(['page-v3'], $result['bootstrap']['required_domains']);
        $this->assertSame(1, $result['summary']['drifted_domains']);
        $this->assertSame(1, $result['summary']['uninitialized_domains']);
        $this->assertSame(1, $result['summary']['changed_packages']);
    }

    public function test_strict_mode_makes_bootstrap_required_state_fatal(): void
    {
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);

        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('code-sha');
        $syncState->shouldReceive('describe')
            ->once()
            ->andReturn([
                'v3' => [
                    'sync_state_ref' => null,
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => null,
                    'status' => 'uninitialized',
                    'last_attempted_status' => null,
                    'last_attempted_at' => null,
                ],
                'page-v3' => [
                    'sync_state_ref' => 'code-sha',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'code-sha',
                    'status' => 'synced',
                    'last_attempted_status' => 'success',
                    'last_attempted_at' => now()->toIso8601String(),
                ],
            ]);
        $planner->shouldNotReceive('run');

        $result = (new ContentSyncPlanService($syncState, $planner, $probe))->run([
            'strict' => true,
        ]);

        $this->assertSame('bootstrap_required', $result['error']['reason']);
        $this->assertSame(['v3'], $result['error']['domains']);
    }

    public function test_it_can_plan_from_explicit_recorded_base_refs_without_sync_state_probe(): void
    {
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $planner = Mockery::mock(ChangedContentPlanService::class);
        $probe = Mockery::mock(DeploymentGitRefProbe::class);

        $probe->shouldNotReceive('currentHeadCommit');
        $syncState->shouldNotReceive('describe');
        $planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === ['v3']
                && ($options['base_refs_by_domain']['v3'] ?? null) === 'recorded-base'
                && ($options['head'] ?? null) === 'recorded-head'))
            ->andReturn($this->changedPlanResult(['v3']));

        $result = (new ContentSyncPlanService($syncState, $planner, $probe))->run([
            'domains' => ['v3'],
            'base_refs_by_domain' => ['v3' => 'recorded-base'],
            'head_ref' => 'recorded-head',
        ]);

        $this->assertNull($result['error']);
        $this->assertSame('recorded-base', $result['domains']['v3']['effective_base_ref']);
        $this->assertSame('recorded-head', $result['domains']['v3']['current_deployed_ref']);
        $this->assertSame('drifted', $result['domains']['v3']['status']);
    }

    /**
     * @param  list<string>  $domains
     * @return array<string, mixed>
     */
    private function changedPlanResult(array $domains): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => null,
                'base_refs_by_domain' => ['v3' => 'old-v3'],
                'head' => 'code-sha',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => $domains,
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => true,
                'check_profile' => 'release',
                'strict' => false,
            ],
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
                'upsert_present' => [
                    [
                        'domain' => 'v3',
                        'package_key' => 'database/seeders/V3/Tests/AlphaSeeder',
                        'relative_path' => 'database/seeders/V3/Tests/AlphaSeeder',
                        'package_type' => 'v3_test',
                        'package_state' => 'not_seeded',
                        'recommended_action' => 'seed',
                        'release_check' => ['status' => 'pass'],
                    ],
                ],
            ],
            'packages' => [
                [
                    'domain' => 'v3',
                    'package_key' => 'database/seeders/V3/Tests/AlphaSeeder',
                    'relative_path' => 'database/seeders/V3/Tests/AlphaSeeder',
                    'package_type' => 'v3_test',
                    'package_state' => 'not_seeded',
                    'recommended_phase' => 'upsert_present',
                    'recommended_action' => 'seed',
                    'release_check' => ['status' => 'pass'],
                    'warnings' => [],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }
}
