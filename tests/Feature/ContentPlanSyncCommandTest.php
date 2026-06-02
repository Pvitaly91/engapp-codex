<?php

namespace Tests\Feature;

use App\Services\ContentDeployment\ContentSyncPlanService;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;

class ContentPlanSyncCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_command_passes_domains_and_runtime_options_to_service(): void
    {
        $mock = Mockery::mock(ContentSyncPlanService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => ($options['domains'] ?? null) === 'v3'
                && ($options['with_release_check'] ?? false) === true
                && ($options['strict'] ?? false) === true))
            ->andReturn($this->mockedResult(['v3']));

        $this->app->instance(ContentSyncPlanService::class, $mock);

        $exitCode = Artisan::call('content:plan-sync', [
            '--domains' => 'v3',
            '--with-release-check' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
    }

    public function test_command_writes_report_and_human_output_includes_sync_hints(): void
    {
        $mock = Mockery::mock(ContentSyncPlanService::class);
        $mock->shouldReceive('run')->once()->andReturn($this->mockedResult([
            'v3',
            'page-v3',
        ], ['page-v3']));
        $mock->shouldReceive('writeReport')->once()->andReturn('content-sync-plans/mock.md');

        $this->app->instance(ContentSyncPlanService::class, $mock);

        $exitCode = Artisan::call('content:plan-sync', [
            '--write-report' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('php artisan content:apply-sync --dry-run', $output);
        $this->assertStringContainsString('--bootstrap-uninitialized', $output);
        $this->assertStringContainsString('content-sync-plans/mock.md', $output);
    }

    /**
     * @param  list<string>  $domains
     * @param  list<string>  $bootstrapRequired
     * @return array<string, mixed>
     */
    private function mockedResult(array $domains, array $bootstrapRequired = []): array
    {
        $domainPayload = [];

        foreach ($domains as $domain) {
            $bootstrap = in_array($domain, $bootstrapRequired, true);
            $domainPayload[$domain] = [
                'domain' => $domain,
                'sync_state_ref' => $bootstrap ? null : 'old-sha',
                'current_deployed_ref' => 'code-sha',
                'effective_base_ref' => $bootstrap ? null : 'old-sha',
                'bootstrap_required' => $bootstrap,
                'drifted' => ! $bootstrap,
                'status' => $bootstrap ? 'uninitialized' : 'drifted',
                'last_attempted_status' => $bootstrap ? null : 'success',
                'last_attempted_at' => null,
            ];
        }

        return [
            'deployment_refs' => [
                'current_deployed_ref' => 'code-sha',
            ],
            'domains' => $domainPayload,
            'summary' => [
                'synced_domains' => 0,
                'drifted_domains' => count($domains) - count($bootstrapRequired),
                'uninitialized_domains' => count($bootstrapRequired),
                'blocked' => 0,
                'warnings' => count($bootstrapRequired),
                'changed_packages' => 0,
                'deleted_cleanup_candidates' => 0,
                'seed_candidates' => 0,
                'refresh_candidates' => 0,
            ],
            'content_plan' => [
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'summary' => [
                    'warnings' => 0,
                    'blocked' => 0,
                ],
            ],
            'bootstrap' => [
                'required_domains' => $bootstrapRequired,
            ],
            'options' => [
                'domains' => $domains,
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
}
