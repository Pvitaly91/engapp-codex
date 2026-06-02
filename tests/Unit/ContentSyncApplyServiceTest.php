<?php

namespace Tests\Unit;

use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ContentSyncApplyService;
use App\Services\ContentDeployment\ContentSyncPlanService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Mockery;
use Tests\TestCase;

class ContentSyncApplyServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_runs_changed_apply_and_bootstraps_uninitialized_domains_in_live_mode(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $plan = $this->planResult([
            'v3' => $this->domainState('v3', 'drifted', 'old-v3', 'code-sha', false, true),
            'page-v3' => $this->domainState('page-v3', 'uninitialized', null, 'code-sha', true, false),
        ], ['page-v3']);

        $planService->shouldReceive('run')
            ->once()
            ->andReturn($plan);
        $changedApply->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === ['v3']
                && ($options['base_refs_by_domain']['v3'] ?? null) === 'old-v3'
                && ($options['head'] ?? null) === 'code-sha'
                && ($options['force'] ?? null) === true
                && ($options['track_sync_state'] ?? null) === false))
            ->andReturn($this->changedApplyResult(false));
        $syncState->shouldReceive('recordSuccess')
            ->once()
            ->with(['v3'], ['v3' => 'old-v3'], 'code-sha', Mockery::type('array'));
        $syncState->shouldReceive('recordSuccess')
            ->once()
            ->with(['page-v3'], ['page-v3' => null], 'code-sha', Mockery::on(
                fn (array $meta): bool => (bool) data_get($meta, 'domains.page-v3.bootstrap', false)
            ));
        $syncState->shouldReceive('describe')
            ->once()
            ->with(['v3', 'page-v3'], ['v3' => 'code-sha', 'page-v3' => 'code-sha'], 'code-sha')
            ->andReturn([
                'v3' => [
                    'sync_state_ref' => 'code-sha',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'code-sha',
                    'status' => 'synced',
                    'last_attempted_status' => 'success',
                    'last_attempted_at' => now()->toIso8601String(),
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

        $result = (new ContentSyncApplyService($planService, $changedApply, $syncState))->run([
            'force' => true,
            'bootstrap_uninitialized' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame('completed', $result['status']);
        $this->assertTrue($result['apply']['executed']);
        $this->assertSame('code-sha', $result['domains_after']['v3']['sync_state_ref']);
        $this->assertSame('code-sha', $result['domains_after']['page-v3']['sync_state_ref']);
        $this->assertSame('page-v3', $result['apply']['bootstrap']['applied'][0]['domain']);
    }

    public function test_it_blocks_uninitialized_domains_without_bootstrap_override(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $plan = $this->planResult([
            'v3' => $this->domainState('v3', 'uninitialized', null, 'code-sha', true, false),
        ], ['v3']);

        $planService->shouldReceive('run')->once()->andReturn($plan);
        $changedApply->shouldNotReceive('run');
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
            ]);

        $result = (new ContentSyncApplyService($planService, $changedApply, $syncState))->run([]);

        $this->assertSame('blocked', $result['status']);
        $this->assertSame('bootstrap_required', $result['error']['reason']);
    }

    public function test_dry_run_simulates_bootstrap_without_advancing_cursor(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $plan = $this->planResult([
            'v3' => $this->domainState('v3', 'drifted', 'old-v3', 'code-sha', false, true),
            'page-v3' => $this->domainState('page-v3', 'uninitialized', null, 'code-sha', true, false),
        ], ['page-v3']);

        $planService->shouldReceive('run')->once()->andReturn($plan);
        $changedApply->shouldReceive('run')->once()->andReturn($this->changedApplyResult(true));
        $syncState->shouldReceive('recordDryRun')
            ->once()
            ->with(['v3'], ['v3' => 'old-v3'], 'code-sha', Mockery::type('array'));
        $syncState->shouldReceive('describe')
            ->once()
            ->andReturn([
                'v3' => [
                    'sync_state_ref' => 'old-v3',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'old-v3',
                    'status' => 'drifted',
                    'last_attempted_status' => 'dry_run',
                    'last_attempted_at' => now()->toIso8601String(),
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
        $syncState->shouldNotReceive('recordSuccess');
        $syncState->shouldNotReceive('recordFailure');

        $result = (new ContentSyncApplyService($planService, $changedApply, $syncState))->run([
            'dry_run' => true,
            'bootstrap_uninitialized' => true,
        ]);

        $this->assertNull($result['error']);
        $this->assertSame('dry_run', $result['status']);
        $this->assertSame('page-v3', $result['apply']['bootstrap']['simulated'][0]['domain']);
        $this->assertNull($result['domains_after']['page-v3']['sync_state_ref']);
    }

    public function test_partial_domain_progress_is_recorded_conservatively_on_failure(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $syncState = Mockery::mock(ContentSyncStateService::class);
        $plan = $this->planResult([
            'v3' => $this->domainState('v3', 'drifted', 'old-v3', 'code-sha', false, true),
            'page-v3' => $this->domainState('page-v3', 'drifted', 'old-page', 'code-sha', false, true),
        ], []);
        $failedChangedApply = $this->changedApplyResult(false, [
            'stage' => 'execution',
            'phase' => 'upsert_present',
            'reason' => 'upsert_present_failed',
            'message' => 'Page upsert failed.',
            'package' => 'database/seeders/Page_V3/Tests/PageSeeder',
            'domain' => 'page-v3',
        ]);
        $failedChangedApply['plan']['packages'] = [
            [
                'domain' => 'page-v3',
                'package_key' => 'database/seeders/Page_V3/Tests/PageSeeder',
                'relative_path' => 'database/seeders/Page_V3/Tests/PageSeeder',
                'recommended_action' => 'refresh',
            ],
        ];
        $failedChangedApply['execution']['upsert_present']['packages'] = [
            [
                'domain' => 'page-v3',
                'package_key' => 'database/seeders/Page_V3/Tests/PageSeeder',
                'relative_path' => 'database/seeders/Page_V3/Tests/PageSeeder',
                'status' => 'failed',
            ],
        ];

        $planService->shouldReceive('run')->once()->andReturn($plan);
        $changedApply->shouldReceive('run')->once()->andReturn($failedChangedApply);
        $syncState->shouldReceive('recordSuccess')
            ->once()
            ->with(['v3'], ['v3' => 'old-v3'], 'code-sha', Mockery::type('array'));
        $syncState->shouldReceive('recordFailure')
            ->once()
            ->with(['page-v3'], ['page-v3' => 'old-page'], 'code-sha', Mockery::type('array'));
        $syncState->shouldReceive('describe')
            ->once()
            ->andReturn([
                'v3' => [
                    'sync_state_ref' => 'code-sha',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'code-sha',
                    'status' => 'synced',
                    'last_attempted_status' => 'success',
                    'last_attempted_at' => now()->toIso8601String(),
                ],
                'page-v3' => [
                    'sync_state_ref' => 'old-page',
                    'fallback_base_ref' => 'code-sha',
                    'last_successful_ref' => 'old-page',
                    'status' => 'failed_last_apply',
                    'last_attempted_status' => 'failed',
                    'last_attempted_at' => now()->toIso8601String(),
                ],
            ]);

        $result = (new ContentSyncApplyService($planService, $changedApply, $syncState))->run([
            'force' => true,
        ]);

        $this->assertSame('partial', $result['status']);
        $this->assertSame('code-sha', $result['domains_after']['v3']['sync_state_ref']);
        $this->assertSame('old-page', $result['domains_after']['page-v3']['sync_state_ref']);
    }

    /**
     * @param  array<string, array<string, mixed>>  $domains
     * @param  list<string>  $bootstrapRequired
     * @return array<string, mixed>
     */
    private function planResult(array $domains, array $bootstrapRequired): array
    {
        return [
            'deployment_refs' => [
                'current_deployed_ref' => 'code-sha',
            ],
            'domains' => $domains,
            'summary' => [
                'synced_domains' => 0,
                'drifted_domains' => count(array_filter($domains, fn (array $domain): bool => (bool) ($domain['drifted'] ?? false))),
                'uninitialized_domains' => count($bootstrapRequired),
                'blocked' => 0,
                'warnings' => 0,
                'changed_packages' => 1,
                'deleted_cleanup_candidates' => 0,
                'seed_candidates' => 1,
                'refresh_candidates' => 0,
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 1,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
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
            'bootstrap' => [
                'required_domains' => $bootstrapRequired,
            ],
            'options' => [
                'domains' => array_keys($domains),
                'with_release_check' => false,
                'check_profile' => 'release',
                'strict' => false,
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function domainState(string $domain, string $status, ?string $syncRef, string $headRef, bool $bootstrapRequired, bool $drifted): array
    {
        return [
            'domain' => $domain,
            'sync_state_ref' => $syncRef,
            'current_deployed_ref' => $headRef,
            'effective_base_ref' => $syncRef,
            'bootstrap_required' => $bootstrapRequired,
            'drifted' => $drifted,
            'status' => $status,
            'last_attempted_status' => $status === 'failed_last_apply' ? 'failed' : 'success',
            'last_attempted_at' => now()->subMinute()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $error
     * @return array<string, mixed>
     */
    private function changedApplyResult(bool $dryRun, ?array $error = null): array
    {
        return [
            'plan' => [
                'summary' => [
                    'changed_packages' => 1,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                ],
                'packages' => [],
            ],
            'preflight' => [
                'summary' => [
                    'ok' => 1,
                    'warn' => 0,
                    'fail' => 0,
                ],
            ],
            'execution' => [
                'cleanup_deleted' => [
                    'packages' => [],
                ],
                'upsert_present' => [
                    'packages' => [],
                ],
            ],
            'error' => $error,
            'diff' => [
                'mode' => 'refs',
            ],
            'scope' => [
                'domains' => ['v3'],
            ],
        ];
    }
}
