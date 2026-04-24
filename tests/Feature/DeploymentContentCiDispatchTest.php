<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ContentOpsCiDispatchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentCiDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDeploymentTables();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_native_ci_dispatch_endpoint_returns_json_payload(): void
    {
        $service = Mockery::mock(ContentOpsCiDispatchService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['branch'] === 'main'
                && $options['sha'] === 'abc123'
                && $options['dry_run'] === true
                && $options['force'] === false))
            ->andReturn($this->dispatchPayload('simulated'));
        $this->app->instance(ContentOpsCiDispatchService::class, $service);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson('/admin/deployment/native/content-ci-dispatch?json=1', [
                'branch' => 'main',
                'sha' => 'abc123',
                'run_mode' => 'dry_run',
            ]);

        $response->assertOk()
            ->assertJsonPath('dispatch.status', 'simulated')
            ->assertJsonPath('target.sha', 'abc123');
    }

    public function test_native_screen_renders_ci_dispatch_result(): void
    {
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('currentBranch')->andReturn('main');
        $nativeDeployment->shouldReceive('headCommit')->andReturn('abc123');
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession([
            'admin_authenticated' => true,
            'deployment_native_content_ci_dispatch' => $this->dispatchPayload('dispatched'),
        ])->get('/admin/deployment/native');

        $response->assertOk()
            ->assertSee('Run ContentOps CI Gate')
            ->assertSee('Latest CI dispatch request')
            ->assertSee('Dispatch accepted');
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchPayload(string $status): array
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
                    'domains' => 'v3,page-v3',
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
