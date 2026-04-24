<?php

namespace Tests\Unit;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Services\ContentDeployment\ChangedContentPlanService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationsDoctorService;
use App\Services\ContentDeployment\ContentReleaseGateService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Mockery;
use Tests\TestCase;

class ContentReleaseGateServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_passes_when_doctor_plan_sync_state_and_lock_are_clean(): void
    {
        $service = $this->makeService(
            doctor: $this->doctorPayload(),
            plan: $this->planPayload(),
            syncStates: [
                'v3' => ['domain' => 'v3', 'status' => 'synced'],
                'page-v3' => ['domain' => 'page-v3', 'status' => 'synced'],
            ],
            lock: ['status' => 'free', 'lock' => null]
        );

        $result = $service->run(null, [
            'domains' => 'v3,page-v3',
        ]);

        $this->assertSame('pass', $result['gate']['overall_status']);
        $this->assertFalse($result['gate']['exit_would_fail']);
        $this->assertSame(0, $result['summary']['changed_packages']);
        $this->assertSame('pass', $this->check($result, 'changed_plan_blocked_packages')['status']);
        $this->assertSame('pass', $this->check($result, 'content_operation_lock')['status']);
    }

    public function test_changed_plan_blockers_fail_the_gate(): void
    {
        $service = $this->makeService(
            doctor: $this->doctorPayload(),
            plan: $this->planPayload(summary: [
                'changed_packages' => 1,
                'blocked' => 1,
                'warnings' => 0,
            ]),
            syncStates: ['v3' => ['domain' => 'v3', 'status' => 'synced']],
            lock: ['status' => 'free', 'lock' => null]
        );

        $result = $service->run(null, [
            'domains' => 'v3',
        ]);

        $this->assertSame('fail', $result['gate']['overall_status']);
        $this->assertTrue($result['gate']['exit_would_fail']);
        $this->assertSame('fail', $this->check($result, 'changed_plan_blocked_packages')['status']);
    }

    public function test_ci_profile_treats_changed_plan_warnings_as_fatal(): void
    {
        $service = $this->makeService(
            doctor: $this->doctorPayload(),
            plan: $this->planPayload(summary: [
                'changed_packages' => 1,
                'blocked' => 0,
                'warnings' => 1,
            ]),
            syncStates: ['v3' => ['domain' => 'v3', 'status' => 'synced']],
            lock: ['status' => 'free', 'lock' => null],
            expectGitRefProbe: true
        );

        $result = $service->run(null, [
            'profile' => 'ci',
            'domains' => 'v3',
        ]);

        $this->assertSame('fail', $result['gate']['overall_status']);
        $this->assertSame('fail', $this->check($result, 'changed_plan_warnings')['status']);
        $this->assertTrue($result['gate']['exit_would_fail']);
    }

    public function test_active_lock_can_fail_the_gate_when_lock_fail_rule_is_enabled(): void
    {
        $service = $this->makeService(
            doctor: $this->doctorPayload(),
            plan: $this->planPayload(),
            syncStates: ['v3' => ['domain' => 'v3', 'status' => 'synced']],
            lock: [
                'status' => 'active',
                'lock' => [
                    'operation_kind' => 'apply_changed',
                    'content_operation_run_id' => 123,
                ],
            ]
        );

        $result = $service->run(null, [
            'domains' => 'v3',
            'fail_on_lock' => true,
        ]);

        $this->assertSame('fail', $this->check($result, 'content_operation_lock')['status']);
        $this->assertTrue($result['gate']['exit_would_fail']);
    }

    /**
     * @param  array<string, mixed>  $doctor
     * @param  array<string, mixed>  $plan
     * @param  array<string, array<string, mixed>>  $syncStates
     * @param  array<string, mixed>  $lock
     */
    private function makeService(
        array $doctor,
        array $plan,
        array $syncStates,
        array $lock,
        bool $expectGitRefProbe = false
    ): ContentReleaseGateService {
        $doctorService = Mockery::mock(ContentOperationsDoctorService::class);
        $doctorService->shouldReceive('run')
            ->once()
            ->andReturn($doctor);

        $planService = Mockery::mock(ChangedContentPlanService::class);
        $planService->shouldReceive('run')
            ->once()
            ->andReturn($plan);

        $syncStateService = Mockery::mock(ContentSyncStateService::class);
        $syncStateService->shouldReceive('describe')
            ->once()
            ->andReturn($syncStates);

        $lockService = Mockery::mock(ContentOperationLockService::class);
        $lockService->shouldReceive('status')
            ->once()
            ->andReturn($lock);

        $gitRefProbe = Mockery::mock(DeploymentGitRefProbe::class);
        if ($expectGitRefProbe) {
            $gitRefProbe->shouldReceive('currentHeadCommit')
                ->once()
                ->andReturn('current-code-ref');
        } else {
            $gitRefProbe->shouldNotReceive('currentHeadCommit');
        }

        return new ContentReleaseGateService(
            $doctorService,
            $planService,
            $syncStateService,
            $lockService,
            $gitRefProbe
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorPayload(): array
    {
        return [
            'overall_status' => 'pass',
            'summary' => [
                'pass' => 1,
                'warn' => 0,
                'fail' => 0,
            ],
            'checks' => [
                [
                    'code' => 'content_schema_content_sync_states',
                    'label' => 'Content schema',
                    'status' => 'pass',
                    'message' => 'content_sync_states table exists.',
                    'meta' => [],
                    'recommendation' => null,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, int>  $summary
     * @return array<string, mixed>
     */
    private function planPayload(array $summary = []): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => null,
                'include_untracked' => false,
            ],
            'scope' => [
                'domains' => ['v3'],
                'resolved_roots' => [],
            ],
            'summary' => array_merge([
                'changed_packages' => 0,
                'deleted_cleanup_candidates' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ], $summary),
            'phases' => [
                'cleanup_deleted' => [],
                'upsert_present' => [],
            ],
            'packages' => [],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function check(array $result, string $code): array
    {
        return collect((array) ($result['checks'] ?? []))->firstWhere('code', $code) ?? [];
    }
}
