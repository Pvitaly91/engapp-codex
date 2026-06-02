<?php

namespace Tests\Feature;

use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ContentCiDispatchCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_json_dry_run_uses_dispatch_service_payload(): void
    {
        $service = Mockery::mock(ContentOpsCiDispatchService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['branch'] === 'main'
                && $options['sha'] === 'abc123'
                && $options['domains'] === 'v3'
                && $options['dry_run'] === true
                && $options['force'] === false))
            ->andReturn($this->payload('simulated'));
        $this->app->instance(ContentOpsCiDispatchService::class, $service);

        $exitCode = Artisan::call('content:ci-dispatch', [
            '--json' => true,
            '--dry-run' => true,
            '--branch' => 'main',
            '--sha' => 'abc123',
            '--domains' => 'v3',
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('simulated', $payload['dispatch']['status']);
    }

    public function test_command_exits_with_failure_when_dispatch_fails(): void
    {
        $service = Mockery::mock(ContentOpsCiDispatchService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->payload('failed'));
        $this->app->instance(ContentOpsCiDispatchService::class, $service);

        $exitCode = Artisan::call('content:ci-dispatch', [
            '--json' => true,
            '--branch' => 'main',
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('failed', $payload['dispatch']['status']);
    }

    public function test_command_writes_dispatch_report_when_requested(): void
    {
        $service = Mockery::mock(ContentOpsCiDispatchService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->payload('simulated'));
        $service->shouldReceive('writeReport')
            ->once()
            ->andReturn('content-ci-dispatches/test.md');
        $this->app->instance(ContentOpsCiDispatchService::class, $service);

        $exitCode = Artisan::call('content:ci-dispatch', [
            '--json' => true,
            '--dry-run' => true,
            '--write-report' => true,
            '--branch' => 'main',
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('content-ci-dispatches/test.md', $payload['artifacts']['report_path']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $status): array
    {
        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => 'contentops-release-gate.yml',
                'name' => 'ContentOps Release Gate',
            ],
            'dispatch' => [
                'requested' => true,
                'dry_run' => $status === 'simulated',
                'ref' => 'main',
                'inputs' => [
                    'base_ref' => 'origin/main',
                    'head_ref' => 'abc123',
                    'domains' => 'v3',
                    'profile' => 'ci',
                    'with_release_check' => 'true',
                    'strict' => 'true',
                    'target_sha' => 'abc123',
                ],
                'status' => $status,
                'http_status' => $status === 'dispatched' ? 204 : null,
            ],
            'target' => [
                'ref' => 'main',
                'branch' => 'main',
                'sha' => 'abc123',
                'exact_sha_requested' => true,
            ],
            'run' => [
                'found' => false,
                'id' => null,
                'status' => null,
                'conclusion' => null,
                'html_url' => null,
                'head_sha' => null,
            ],
            'status_result' => null,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => $status === 'failed'
                ? ['stage' => 'contentops_ci_dispatch', 'message' => 'failed']
                : null,
        ];
    }
}
