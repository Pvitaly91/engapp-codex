<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ContentReleaseGateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentReleaseGateTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDeploymentTables();
    }

    public function test_shell_release_gate_endpoint_returns_json_payload(): void
    {
        $gateService = Mockery::mock(ContentReleaseGateService::class);
        $gateService->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => $options['profile'] === 'ci'
                && $options['domains'] === 'v3'
                && $options['with_release_check'] === true))
            ->andReturn($this->gatePayload('warn'));
        $this->app->instance(ContentReleaseGateService::class, $gateService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-release-gate?domains=v3&profile=ci&with_release_check=1&json=1');

        $response->assertOk()
            ->assertJsonPath('gate.overall_status', 'warn')
            ->assertJsonPath('summary.warn', 1)
            ->assertJsonPath('checks.0.code', 'changed_plan_warnings');
    }

    public function test_native_release_gate_screen_renders_release_gate_card(): void
    {
        $gateService = Mockery::mock(ContentReleaseGateService::class);
        $gateService->shouldReceive('run')
            ->once()
            ->andReturn($this->gatePayload('pass'));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('currentBranch')->andReturn('main');
        $nativeDeployment->shouldReceive('headCommit')->andReturn('head-sha');

        $this->app->instance(ContentReleaseGateService::class, $gateService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/deployment/native/content-release-gate?profile=deployment&with_release_check=1');

        $response->assertOk()
            ->assertSee('ContentOps Release Gate')
            ->assertSee('changed_plan_warnings')
            ->assertSee('Run and Write Report');
    }

    /**
     * @return array<string, mixed>
     */
    private function gatePayload(string $status): array
    {
        return [
            'gate' => [
                'overall_status' => $status,
                'raw_status' => $status,
                'profile' => 'deployment',
                'strict' => false,
                'exit_would_fail' => false,
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
                'changed_packages' => 1,
                'blocked_packages' => 0,
                'warnings' => $status === 'warn' ? 1 : 0,
            ],
            'checks' => [
                [
                    'group' => 'changed_plan',
                    'code' => 'changed_plan_warnings',
                    'label' => 'Changed-content warnings',
                    'status' => $status === 'pass' ? 'pass' : 'warn',
                    'message' => 'Changed-content plan contains warnings.',
                    'meta' => ['warnings' => 1],
                    'recommendation' => 'Inspect package warnings before deploy/apply.',
                    'source' => 'changed_plan',
                ],
            ],
            'changed_plan' => [
                'summary' => [
                    'changed_packages' => 1,
                ],
            ],
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

    private function ensureDeploymentTables(): void
    {
        if (! Schema::hasTable('backup_branches')) {
            Schema::create('backup_branches', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('commit_hash');
                $table->timestamp('pushed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('branch_usage_history')) {
            Schema::create('branch_usage_history', function (Blueprint $table): void {
                $table->id();
                $table->string('branch_name');
                $table->string('action');
                $table->text('description')->nullable();
                $table->json('paths')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
