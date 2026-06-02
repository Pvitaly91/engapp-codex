<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ContentOpsCiStatusService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentCiStatusTest extends TestCase
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

    public function test_shell_ci_status_endpoint_returns_json_payload(): void
    {
        $service = Mockery::mock(ContentOpsCiStatusService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['branch'] === 'main'
                && $options['sha'] === 'abc123'
                && $options['require_success'] === true))
            ->andReturn($this->ciPayload('pass', false));
        $this->app->instance(ContentOpsCiStatusService::class, $service);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-ci-status?branch=main&sha=abc123&require_success=1&json=1');

        $response->assertOk()
            ->assertJsonPath('readiness.status', 'pass')
            ->assertJsonPath('match.exact_sha_verified', true)
            ->assertJsonPath('run.id', 123);
    }

    public function test_native_ci_status_screen_renders_ci_card(): void
    {
        $service = Mockery::mock(ContentOpsCiStatusService::class);
        $service->shouldReceive('run')
            ->once()
            ->andReturn($this->ciPayload('warn', false));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('currentBranch')->andReturn('main');
        $nativeDeployment->shouldReceive('headCommit')->andReturn('head-sha');

        $this->app->instance(ContentOpsCiStatusService::class, $service);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/deployment/native/content-ci-status?branch=main');

        $response->assertOk()
            ->assertSee('ContentOps CI Status')
            ->assertSee('ci_passed_sha_unverified')
            ->assertSee('Open workflow run');
    }

    public function test_required_ci_status_blocks_native_deploy_before_code_update(): void
    {
        config()->set('git-deployment.contentops_ci_status.required_for_deploy', true);

        $preview = $this->readyPreview();
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')->once()->andReturn($preview);
        $previewService->shouldReceive('gateBlocks')->once()->with($preview)->andReturn(false);

        $ciStatusService = Mockery::mock(ContentOpsCiStatusService::class);
        $ciStatusService->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => $options['ref'] === 'head-sha'
                && $options['branch'] === 'main'
                && $options['require_success'] === true))
            ->andReturn($this->ciPayload('fail', true));

        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldNotReceive('deploy');

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(ContentOpsCiStatusService::class, $ciStatusService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/deploy', [
                'branch' => 'main',
                'apply_changed_content' => '0',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'error');
        $response->assertSessionHas('deployment_native_content_ci_status.readiness.status', 'fail');
    }

    /**
     * @return array<string, mixed>
     */
    private function ciPayload(string $status, bool $exitWouldFail): array
    {
        return [
            'provider' => 'github_actions',
            'workflow' => [
                'file' => 'contentops-release-gate.yml',
                'name' => 'ContentOps Release Gate',
            ],
            'target' => [
                'ref' => 'head-sha',
                'branch' => 'main',
                'sha' => 'abc123',
            ],
            'match' => [
                'run_found' => true,
                'exact_sha_verified' => $status !== 'warn',
                'sha_mismatch' => false,
                'matched_by' => 'exact_sha',
            ],
            'run' => [
                'id' => 123,
                'status' => 'completed',
                'conclusion' => $status === 'fail' ? 'failure' : 'success',
                'head_branch' => 'main',
                'head_sha' => 'abc123',
                'html_url' => 'https://github.com/Pvitaly91/engapp-codex/actions/runs/123',
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
                'run_started_at' => now()->toIso8601String(),
            ],
            'readiness' => [
                'status' => $status,
                'raw_status' => $status,
                'high_level_status' => $status === 'pass' ? 'passed' : 'failed',
                'code' => $status === 'warn' ? 'ci_passed_sha_unverified' : ($status === 'pass' ? 'ci_passed' : 'ci_failed'),
                'message' => 'Test CI status.',
                'warnings' => $status === 'warn' ? ['SHA not verified.'] : [],
                'failures' => $status === 'fail' ? ['CI failed.'] : [],
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

    /**
     * @return array<string, mixed>
     */
    private function readyPreview(): array
    {
        return [
            'deployment' => [
                'mode' => 'native',
                'source_kind' => 'deploy',
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'branch' => 'main',
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                    'resolved_roots' => [],
                ],
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 0,
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
            'content_sync' => [
                'domains' => [],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => false,
                'reasons' => [],
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
