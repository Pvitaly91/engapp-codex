<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ContentOperationsDoctorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentDoctorTest extends TestCase
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

    public function test_shell_doctor_endpoint_returns_json_payload(): void
    {
        $doctorService = Mockery::mock(ContentOperationsDoctorService::class);
        $doctorService->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['with_deployment'] === true
                && $options['with_package_roots'] === true
                && $options['domains'] === 'v3'))
            ->andReturn($this->doctorPayload('warn'));
        $this->app->instance(ContentOperationsDoctorService::class, $doctorService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-doctor?domains=v3&json=1');

        $response->assertOk()
            ->assertJsonPath('overall_status', 'warn')
            ->assertJsonPath('summary.warn', 1)
            ->assertJsonPath('checks.0.code', 'content_sync_state_v3');
    }

    public function test_native_doctor_screen_renders_doctor_card(): void
    {
        $doctorService = Mockery::mock(ContentOperationsDoctorService::class);
        $doctorService->shouldReceive('run')
            ->once()
            ->andReturn($this->doctorPayload('pass'));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('currentBranch')->andReturn('main');
        $nativeDeployment->shouldReceive('headCommit')->andReturn('head-sha');

        $this->app->instance(ContentOperationsDoctorService::class, $doctorService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/deployment/native/content-doctor?with_deployment=1&with_package_roots=1');

        $response->assertOk()
            ->assertSee('ContentOps Doctor')
            ->assertSee('content_sync_state_v3')
            ->assertSee('Run and Write Report');
    }

    /**
     * @return array<string, mixed>
     */
    private function doctorPayload(string $status): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'overall_status' => $status,
            'summary' => [
                'total' => 1,
                'pass' => $status === 'pass' ? 1 : 0,
                'warn' => $status === 'warn' ? 1 : 0,
                'fail' => $status === 'fail' ? 1 : 0,
            ],
            'options' => [
                'domains' => ['v3'],
                'strict' => false,
            ],
            'checks' => [
                [
                    'code' => 'content_sync_state_v3',
                    'label' => 'Content sync state',
                    'status' => $status === 'pass' ? 'pass' : 'warn',
                    'message' => 'v3 content sync cursor is uninitialized.',
                    'meta' => ['domain' => 'v3'],
                    'recommendation' => 'Run content:plan-sync before apply.',
                ],
            ],
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
