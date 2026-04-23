<?php

namespace Tests\Unit;

use App\Services\ContentDeployment\ChangedContentPlanService;
use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesPlanService;
use App\Services\V3PromptGenerator\V3ChangedPackagesPlanService;
use Mockery;
use Tests\TestCase;

class ChangedContentPlanServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_merges_domain_plans_with_safe_cross_domain_phase_ordering(): void
    {
        $v3Planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $pagePlanner = Mockery::mock(PageV3ChangedPackagesPlanService::class);

        $v3Planner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['strict'] ?? null) === false))
            ->andReturn($this->domainPlanResult(
                'database/seeders/V3',
                [
                    $this->package('v3-deleted', 'database/seeders/V3/Tests/GhostSeeder', 'v3_test', 'deleted', 'deleted_from_disk', 'unseed_deleted', 'cleanup_deleted'),
                    $this->package('v3-seed', 'database/seeders/V3/Tests/AlphaSeeder', 'v3_test', 'added', 'not_seeded', 'seed', 'upsert_present'),
                ]
            ));

        $pagePlanner->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['strict'] ?? null) === false))
            ->andReturn($this->domainPlanResult(
                'database/seeders/Page_V3',
                [
                    $this->package('page-deleted-page', 'database/seeders/Page_V3/Tests/PageSeeder', 'page', 'deleted', 'deleted_from_disk', 'unseed_deleted', 'cleanup_deleted'),
                    $this->package('page-deleted-category', 'database/seeders/Page_V3/Tests/CategorySeeder', 'category', 'deleted', 'deleted_from_disk', 'unseed_deleted', 'cleanup_deleted'),
                    $this->package('page-seed-category', 'database/seeders/Page_V3/Tests/CategoryCurrentSeeder', 'category', 'added', 'not_seeded', 'seed', 'upsert_present'),
                    $this->package('page-refresh-page', 'database/seeders/Page_V3/Tests/PageCurrentSeeder', 'page', 'modified', 'seeded', 'refresh', 'upsert_present'),
                ]
            ));

        $result = (new ChangedContentPlanService($v3Planner, $pagePlanner))->run();

        $this->assertNull($result['error']);
        $this->assertSame(['v3', 'page-v3'], $result['scope']['domains']);
        $this->assertSame([
            'v3',
            'page-v3',
            'page-v3',
        ], array_column($result['phases']['cleanup_deleted'], 'domain'));
        $this->assertSame([
            'database/seeders/V3/Tests/GhostSeeder',
            'database/seeders/Page_V3/Tests/PageSeeder',
            'database/seeders/Page_V3/Tests/CategorySeeder',
        ], array_column($result['phases']['cleanup_deleted'], 'relative_path'));
        $this->assertSame([
            'page-v3',
            'page-v3',
            'v3',
        ], array_column($result['phases']['upsert_present'], 'domain'));
        $this->assertSame([
            'database/seeders/Page_V3/Tests/CategoryCurrentSeeder',
            'database/seeders/Page_V3/Tests/PageCurrentSeeder',
            'database/seeders/V3/Tests/AlphaSeeder',
        ], array_column($result['phases']['upsert_present'], 'relative_path'));
        $this->assertSame(6, $result['summary']['changed_packages']);
        $this->assertSame(3, $result['summary']['deleted_cleanup_candidates']);
        $this->assertSame(2, $result['summary']['seed_candidates']);
        $this->assertSame(1, $result['summary']['refresh_candidates']);
    }

    public function test_target_inside_one_domain_scopes_to_that_domain_only(): void
    {
        $v3Planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $pagePlanner = Mockery::mock(PageV3ChangedPackagesPlanService::class);

        $v3Planner->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Tests/ScopedSeeder', Mockery::type('array'))
            ->andReturn($this->domainPlanResult('database/seeders/V3/Tests/ScopedSeeder', []));
        $pagePlanner->shouldNotReceive('run');

        $result = (new ChangedContentPlanService($v3Planner, $pagePlanner))
            ->run('database/seeders/V3/Tests/ScopedSeeder');

        $this->assertNull($result['error']);
        $this->assertSame(['v3'], $result['scope']['domains']);
        $this->assertCount(1, $result['scope']['resolved_roots']);
        $this->assertSame('v3', $result['scope']['resolved_roots'][0]['domain']);
    }

    public function test_it_fails_when_target_and_domains_filter_contradict_each_other(): void
    {
        $v3Planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $pagePlanner = Mockery::mock(PageV3ChangedPackagesPlanService::class);

        $v3Planner->shouldNotReceive('run');
        $pagePlanner->shouldNotReceive('run');

        $result = (new ChangedContentPlanService($v3Planner, $pagePlanner))->run(
            'database/seeders/V3/Tests/ScopedSeeder',
            ['domains' => 'page-v3']
        );

        $this->assertSame('target_resolution', $result['error']['stage']);
        $this->assertStringContainsString('Target resolves inside V3', $result['error']['message']);
    }

    public function test_strict_mode_makes_merged_warnings_fatal(): void
    {
        $v3Planner = Mockery::mock(V3ChangedPackagesPlanService::class);
        $pagePlanner = Mockery::mock(PageV3ChangedPackagesPlanService::class);

        $v3Planner->shouldReceive('run')
            ->once()
            ->andReturn($this->domainPlanResult('database/seeders/V3', [
                $this->package('v3-seed', 'database/seeders/V3/Tests/AlphaSeeder', 'v3_test', 'added', 'not_seeded', 'seed', 'upsert_present', ['Planner warning']),
            ]));
        $pagePlanner->shouldReceive('run')
            ->once()
            ->andReturn($this->domainPlanResult('database/seeders/Page_V3', []));

        $result = (new ChangedContentPlanService($v3Planner, $pagePlanner))->run(null, [
            'strict' => true,
        ]);

        $this->assertSame('warnings_are_fatal', $result['error']['reason']);
        $this->assertSame(1, $result['summary']['warnings']);
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function domainPlanResult(string $scopeRoot, array $packages): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => true,
            ],
            'scope' => [
                'input' => null,
                'resolved_root_absolute_path' => base_path($scopeRoot),
                'resolved_root_relative_path' => $scopeRoot,
                'single_package' => count($packages) === 1,
            ],
            'summary' => [
                'changed_packages' => count($packages),
                'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed')),
                'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh')),
                'deleted_cleanup_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted')),
                'skipped' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip')),
                'blocked' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked')),
                'warnings' => count(array_filter($packages, fn (array $package): bool => ($package['warnings'] ?? []) !== [])),
            ],
            'phases' => [
                'cleanup_deleted' => array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'cleanup_deleted')),
                'upsert_present' => array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'upsert_present')),
            ],
            'packages' => $packages,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    private function package(
        string $key,
        string $path,
        string $type,
        string $changeType,
        string $state,
        string $action,
        string $phase,
        array $warnings = [],
    ): array {
        return [
            'package_key' => $key,
            'package_type' => $type,
            'change_type' => $changeType,
            'current_relative_path' => $phase === 'cleanup_deleted' ? null : $path,
            'historical_relative_path' => $phase === 'cleanup_deleted' ? $path : null,
            'current_on_disk' => $phase !== 'cleanup_deleted',
            'historical_metadata_available' => $phase === 'cleanup_deleted',
            'resolved_seeder_class' => 'Seeder\\' . md5($key),
            'seed_run_present' => $phase === 'cleanup_deleted',
            'package_present_in_db' => $phase === 'cleanup_deleted',
            'package_state' => $state,
            'recommended_phase' => $phase,
            'recommended_action' => $action,
            'release_check' => [
                'executed' => false,
                'status' => 'skipped',
                'summary' => ['pass' => 0, 'warn' => 0, 'fail' => 0],
            ],
            'historical_definition_summary' => null,
            'warnings' => $warnings,
            'next_step_hint' => null,
        ];
    }
}
