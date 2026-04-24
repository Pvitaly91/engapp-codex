<?php

namespace Tests\Feature;

use App\Services\ContentDeployment\ContentReleaseGateService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ContentReleaseGateCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_json_output_uses_release_gate_service_payload(): void
    {
        $service = Mockery::mock(ContentReleaseGateService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => $options['profile'] === 'ci'
                && $options['base'] === 'base-ref'
                && $options['head'] === 'head-ref'
                && $options['with_release_check'] === true
                && $options['domains'] === 'v3'))
            ->andReturn($this->gatePayload('pass', false));
        $this->app->instance(ContentReleaseGateService::class, $service);

        $exitCode = Artisan::call('content:release-gate', [
            '--json' => true,
            '--profile' => 'ci',
            '--domains' => 'v3',
            '--base' => 'base-ref',
            '--head' => 'head-ref',
            '--with-release-check' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('pass', $payload['gate']['overall_status']);
        $this->assertFalse($payload['gate']['exit_would_fail']);
    }

    public function test_command_exits_with_failure_when_gate_would_fail(): void
    {
        $service = Mockery::mock(ContentReleaseGateService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->gatePayload('fail', true));
        $this->app->instance(ContentReleaseGateService::class, $service);

        $exitCode = Artisan::call('content:release-gate', [
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('fail', $payload['gate']['overall_status']);
    }

    public function test_command_forwards_optional_target_scope_to_service(): void
    {
        $service = Mockery::mock(ContentReleaseGateService::class);
        $service->shouldReceive('run')
            ->once()
            ->with('database/seeders/V3/Polyglot', Mockery::type('array'))
            ->andReturn($this->gatePayload('pass', false));
        $this->app->instance(ContentReleaseGateService::class, $service);

        $exitCode = Artisan::call('content:release-gate', [
            'target' => 'database/seeders/V3/Polyglot',
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
    }

    public function test_command_writes_report_when_requested(): void
    {
        $service = Mockery::mock(ContentReleaseGateService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->gatePayload('warn', false));
        $service->shouldReceive('writeReport')
            ->once()
            ->andReturn('content-release-gates/test.md');
        $this->app->instance(ContentReleaseGateService::class, $service);

        $exitCode = Artisan::call('content:release-gate', [
            '--json' => true,
            '--write-report' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('content-release-gates/test.md', $payload['artifacts']['report_path']);
    }

    /**
     * @return array<string, mixed>
     */
    private function gatePayload(string $status, bool $exitWouldFail): array
    {
        return [
            'gate' => [
                'overall_status' => $status,
                'raw_status' => $status,
                'profile' => 'local',
                'strict' => false,
                'exit_would_fail' => $exitWouldFail,
                'fatal_warning_rules' => [],
            ],
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => null,
                'include_untracked' => false,
            ],
            'scope' => [
                'domains' => ['v3'],
                'target' => null,
                'resolved_roots' => [],
            ],
            'summary' => [
                'pass' => $status === 'pass' ? 1 : 0,
                'warn' => $status === 'warn' ? 1 : 0,
                'fail' => $status === 'fail' ? 1 : 0,
                'changed_packages' => 0,
                'blocked_packages' => 0,
                'warnings' => 0,
            ],
            'checks' => [
                [
                    'group' => 'release_gate',
                    'code' => 'test_check',
                    'label' => 'Test check',
                    'status' => $status,
                    'message' => 'Test release-gate check.',
                    'meta' => [],
                    'recommendation' => null,
                    'source' => 'release_gate',
                ],
            ],
            'changed_plan' => [],
            'sync_state' => [],
            'lock' => [],
            'doctor' => [],
            'recommendations' => [],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }
}
