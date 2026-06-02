<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Services\ContentDeployment\ContentSyncApplyService;
use App\Services\ContentDeployment\ContentSyncPlanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentSyncRepairTest extends TestCase
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
        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    public function test_shell_sync_preview_endpoint_returns_json_payload(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $planService->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => ! array_key_exists('head_ref', $options)))
            ->andReturn($this->planPayload());

        $this->app->instance(ContentSyncPlanService::class, $planService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-sync-preview');

        $response->assertOk()
            ->assertJsonPath('deployment_refs.current_deployed_ref', 'code-sha')
            ->assertJsonPath('domains.v3.status', 'drifted');
    }

    public function test_native_sync_preview_uses_current_deployed_ref_override(): void
    {
        $planService = Mockery::mock(ContentSyncPlanService::class);
        $planService->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => ($options['head_ref'] ?? null) === 'native-head'))
            ->andReturn($this->planPayload('native-head'));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('headCommit')->once()->andReturn('native-head');

        $this->app->instance(ContentSyncPlanService::class, $planService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/native/content-sync-preview');

        $response->assertOk()
            ->assertJsonPath('deployment_refs.current_deployed_ref', 'native-head');
    }

    public function test_native_live_sync_repair_uses_same_resolved_ref_contract(): void
    {
        $applyService = Mockery::mock(ContentSyncApplyService::class);
        $applyService->shouldReceive('run')
            ->once()
            ->with(Mockery::on(fn (array $options): bool => ($options['head_ref'] ?? null) === 'native-head'
                && ($options['dry_run'] ?? null) === false
                && ($options['force'] ?? null) === true
                && ($options['bootstrap_uninitialized'] ?? null) === true))
            ->andReturn($this->applyPayload('native-head'));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('headCommit')->once()->andReturn('native-head');

        $this->app->instance(ContentSyncApplyService::class, $applyService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/native/content-sync', [
                'run_mode' => 'live',
                'content_sync_bootstrap_uninitialized' => '1',
            ]);

        $response->assertRedirect(route('deployment.native.index'));
        $response->assertSessionHas('deployment_native.status', 'success');
        $response->assertSessionHas('deployment_native_content_sync_apply.status', 'completed');
        $this->assertDatabaseCount('content_operation_runs', 1);
        $this->assertSame('deployment_sync_repair', ContentOperationRun::query()->latest('id')->value('operation_kind'));
    }

    /**
     * @return array<string, mixed>
     */
    private function planPayload(string $headRef = 'code-sha'): array
    {
        return [
            'deployment_refs' => [
                'current_deployed_ref' => $headRef,
            ],
            'domains' => [
                'v3' => [
                    'status' => 'drifted',
                    'sync_state_ref' => 'old-v3',
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => false,
                ],
                'page-v3' => [
                    'status' => 'uninitialized',
                    'sync_state_ref' => null,
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => true,
                ],
            ],
            'summary' => [
                'synced_domains' => 0,
                'drifted_domains' => 1,
                'uninitialized_domains' => 1,
                'blocked' => 0,
                'warnings' => 1,
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
                'required_domains' => ['page-v3'],
            ],
            'options' => [
                'domains' => ['v3', 'page-v3'],
                'with_release_check' => true,
                'check_profile' => 'release',
                'strict' => true,
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
    private function applyPayload(string $headRef): array
    {
        return [
            'domains_before' => [
                'v3' => [
                    'status' => 'drifted',
                    'sync_state_ref' => 'old-v3',
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => false,
                ],
                'page-v3' => [
                    'status' => 'uninitialized',
                    'sync_state_ref' => null,
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => true,
                ],
            ],
            'plan' => [
                'deployment_refs' => [
                    'current_deployed_ref' => $headRef,
                ],
                'summary' => [
                    'synced_domains' => 0,
                    'drifted_domains' => 1,
                    'uninitialized_domains' => 1,
                    'blocked' => 0,
                    'warnings' => 1,
                    'changed_packages' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                ],
                'bootstrap' => [
                    'required_domains' => ['page-v3'],
                ],
            ],
            'apply' => [
                'executed' => true,
                'dry_run' => false,
                'changed_content_result' => null,
                'bootstrap' => [
                    'requested' => true,
                    'simulated' => [],
                    'applied' => [
                        [
                            'domain' => 'page-v3',
                            'head_ref' => $headRef,
                        ],
                    ],
                ],
            ],
            'domains_after' => [
                'v3' => [
                    'status' => 'drifted',
                    'sync_state_ref' => 'old-v3',
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => false,
                ],
                'page-v3' => [
                    'status' => 'synced',
                    'sync_state_ref' => $headRef,
                    'current_deployed_ref' => $headRef,
                    'bootstrap_required' => false,
                ],
            ],
            'status' => 'completed',
            'artifacts' => [
                'report_path' => storage_path('app/content-sync-apply-reports/mock.md'),
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

    private function ensureContentOperationRunsTable(): void
    {
        if (Schema::hasTable('content_operation_runs')) {
            if (! Schema::hasColumn('content_operation_runs', 'replayed_from_run_id')) {
                Schema::table('content_operation_runs', function (Blueprint $table): void {
                    $table->unsignedBigInteger('replayed_from_run_id')->nullable()->after('id');
                });
            }

            return;
        }

        Schema::create('content_operation_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('replayed_from_run_id')->nullable();
            $table->string('operation_kind', 64);
            $table->string('trigger_source', 64);
            $table->json('domains');
            $table->string('base_ref')->nullable();
            $table->string('head_ref')->nullable();
            $table->json('base_refs_by_domain')->nullable();
            $table->boolean('dry_run')->default(false);
            $table->boolean('strict')->default(false);
            $table->boolean('with_release_check')->nullable();
            $table->boolean('skip_release_check')->nullable();
            $table->boolean('bootstrap_uninitialized')->default(false);
            $table->string('status', 32)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->json('summary')->nullable();
            $table->string('payload_json_path')->nullable();
            $table->string('report_path')->nullable();
            $table->text('error_excerpt')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }
}
