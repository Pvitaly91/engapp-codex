<?php

namespace Tests\Feature;

use App\Services\ContentDeployment\ContentOpsCiStatusService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ContentCiStatusCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_json_output_uses_ci_status_service_payload(): void
    {
        $service = Mockery::mock(ContentOpsCiStatusService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['ref'] === 'origin/main'
                && $options['branch'] === 'main'
                && $options['sha'] === 'abc123'
                && $options['require_success'] === true
                && $options['allow_in_progress'] === true
                && $options['max_age_minutes'] === 30))
            ->andReturn($this->payload('pass', false));
        $this->app->instance(ContentOpsCiStatusService::class, $service);

        $exitCode = Artisan::call('content:ci-status', [
            '--json' => true,
            '--ref' => 'origin/main',
            '--branch' => 'main',
            '--sha' => 'abc123',
            '--require-success' => true,
            '--allow-in-progress' => true,
            '--max-age-minutes' => 30,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('pass', $payload['readiness']['status']);
    }

    public function test_command_exits_with_failure_when_ci_status_would_fail(): void
    {
        $service = Mockery::mock(ContentOpsCiStatusService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->payload('fail', true));
        $this->app->instance(ContentOpsCiStatusService::class, $service);

        $exitCode = Artisan::call('content:ci-status', [
            '--json' => true,
            '--require-success' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('fail', $payload['readiness']['status']);
    }

    public function test_command_writes_report_when_requested(): void
    {
        $service = Mockery::mock(ContentOpsCiStatusService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->payload('warn', false));
        $service->shouldReceive('writeReport')
            ->once()
            ->andReturn('content-ci-status/test.md');
        $this->app->instance(ContentOpsCiStatusService::class, $service);

        $exitCode = Artisan::call('content:ci-status', [
            '--json' => true,
            '--write-report' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('content-ci-status/test.md', $payload['artifacts']['report_path']);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $status, bool $exitWouldFail): array
    {
        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => 'contentops-release-gate.yml',
                'name' => 'ContentOps Release Gate',
            ],
            'target' => [
                'ref' => 'origin/main',
                'branch' => 'main',
                'sha' => 'abc123',
            ],
            'match' => [
                'run_found' => true,
                'exact_sha_verified' => true,
                'sha_mismatch' => false,
                'matched_by' => 'exact_sha',
            ],
            'run' => [
                'id' => 123,
                'status' => 'completed',
                'conclusion' => $status === 'fail' ? 'failure' : 'success',
                'head_branch' => 'main',
                'head_sha' => 'abc123',
                'html_url' => 'https://github.com/example/repo/actions/runs/123',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'run_started_at' => now()->toIso8601String(),
            ],
            'readiness' => [
                'status' => $status,
                'raw_status' => $status,
                'high_level_status' => $status === 'pass' ? 'passed' : 'failed',
                'code' => $status === 'pass' ? 'ci_passed' : 'ci_failed',
                'message' => 'Test CI status.',
                'warnings' => $status === 'warn' ? ['warning'] : [],
                'failures' => $status === 'fail' ? ['failure'] : [],
                'strict' => false,
                'require_success' => false,
                'allow_in_progress' => false,
                'exit_would_fail' => $exitWouldFail,
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }
}
