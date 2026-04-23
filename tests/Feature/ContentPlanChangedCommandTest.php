<?php

namespace Tests\Feature;

use App\Services\ContentDeployment\ChangedContentPlanService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ContentPlanChangedCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_command_passes_domains_and_diff_modes_to_service(): void
    {
        $mock = Mockery::mock(ChangedContentPlanService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'v3' && ($options['staged'] ?? false) === true))
            ->andReturn($this->mockedResult(['v3']));
        $mock->shouldReceive('run')
            ->once()
            ->with('database/seeders/Page_V3/Tests/PageSeeder', Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'page-v3' && ($options['base'] ?? null) === 'HEAD~1' && ($options['head'] ?? null) === 'HEAD'))
            ->andReturn($this->mockedResult(['page-v3'], 'database/seeders/Page_V3/Tests/PageSeeder'));

        $this->app->instance(ChangedContentPlanService::class, $mock);

        $firstExitCode = Artisan::call('content:plan-changed', [
            '--domains' => 'v3',
            '--staged' => true,
            '--json' => true,
        ]);
        $secondExitCode = Artisan::call('content:plan-changed', [
            'target' => 'database/seeders/Page_V3/Tests/PageSeeder',
            '--domains' => 'page-v3',
            '--base' => 'HEAD~1',
            '--head' => 'HEAD',
            '--json' => true,
        ]);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
    }

    public function test_command_writes_report_and_supports_empty_result_behavior(): void
    {
        $mock = Mockery::mock(ChangedContentPlanService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult(['v3', 'page-v3']));
        $mock->shouldReceive('writeReport')
            ->once()
            ->andReturn('content-changed-plans/mock-report.md');

        $this->app->instance(ChangedContentPlanService::class, $mock);

        $exitCode = Artisan::call('content:plan-changed', [
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['summary']['changed_packages']);
        $this->assertStringContainsString('content-changed-plans/mock-report.md', $payload['artifacts']['report_path']);
    }

    public function test_human_output_includes_unified_apply_hint(): void
    {
        $mock = Mockery::mock(ChangedContentPlanService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult(['v3', 'page-v3'], null, [
                $this->package('v3', 'database/seeders/V3/Tests/AlphaSeeder', 'seed', 'upsert_present'),
            ], true));

        $this->app->instance(ChangedContentPlanService::class, $mock);

        $exitCode = Artisan::call('content:plan-changed', [
            '--with-release-check' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Apply Hint:', $output);
        $this->assertStringContainsString('php artisan content:apply-changed --dry-run --with-release-check', $output);
    }

    /**
     * @param  list<string>  $domains
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>
     */
    private function mockedResult(array $domains, ?string $input = null, array $packages = [], bool $withReleaseCheck = false): array
    {
        $cleanup = array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'cleanup_deleted'));
        $upsert = array_values(array_filter($packages, fn (array $package): bool => ($package['recommended_phase'] ?? null) === 'upsert_present'));

        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => $input,
                'domains' => $domains,
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => $withReleaseCheck,
                'check_profile' => 'release',
                'strict' => false,
            ],
            'summary' => [
                'changed_packages' => count($packages),
                'seed_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed')),
                'refresh_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh')),
                'deleted_cleanup_candidates' => count(array_filter($packages, fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted')),
                'skipped' => 0,
                'blocked' => 0,
                'warnings' => 0,
            ],
            'domains' => [
                'v3' => null,
                'page-v3' => null,
            ],
            'phases' => [
                'cleanup_deleted' => $cleanup,
                'upsert_present' => $upsert,
            ],
            'packages' => $packages,
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function package(string $domain, string $path, string $action, string $phase): array
    {
        return [
            'domain' => $domain,
            'package_key' => $path,
            'relative_path' => $path,
            'package_type' => $domain === 'v3' ? 'v3_test' : 'page',
            'change_type' => 'added',
            'current_relative_path' => $path,
            'historical_relative_path' => null,
            'package_state' => 'not_seeded',
            'recommended_action' => $action,
            'recommended_phase' => $phase,
            'release_check' => [
                'executed' => false,
                'status' => 'skipped',
            ],
            'warnings' => [],
        ];
    }
}
