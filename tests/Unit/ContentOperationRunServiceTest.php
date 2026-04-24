<?php

namespace Tests\Unit;

use App\Models\ContentOperationRun;
use App\Services\ContentDeployment\ContentOperationRunService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentOperationRunServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    public function test_it_writes_payload_artifact_and_can_load_detail(): void
    {
        $service = app(ContentOperationRunService::class);
        $run = $service->start([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3', 'page-v3'],
            'dry_run' => true,
            'strict' => true,
        ]);

        $finished = $service->finishDryRun($run, [
            'diff' => [
                'base' => 'base-sha',
                'head' => 'head-sha',
            ],
            'scope' => [
                'domains' => ['v3', 'page-v3'],
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => 2,
                    'deleted_cleanup_candidates' => 1,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
            ],
            'execution' => [
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                ],
                'upsert_present' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                ],
            ],
            'artifacts' => [
                'report_path' => storage_path('app/content-changed-apply-reports/mock.md'),
            ],
        ]);

        $this->assertSame('dry_run', $finished->status);
        $this->assertNotNull($finished->payload_json_path);
        $this->assertNotNull($finished->report_path);

        $reloaded = $service->findWithArtifacts($finished->id);

        $this->assertNotNull($reloaded);
        $this->assertSame('apply_changed', $reloaded->operation_kind);
        $this->assertSame(2, data_get($reloaded->summary, 'changed_packages'));
        $this->assertSame('head-sha', data_get($reloaded->payload_json, 'diff.head'));
    }

    public function test_latest_filters_by_kind_status_and_domains(): void
    {
        $service = app(ContentOperationRunService::class);

        $service->finishSuccess($service->start([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
        ]), [
            'scope' => ['domains' => ['v3']],
            'diff' => ['head' => 'head-a'],
            'plan' => ['summary' => []],
            'execution' => [
                'cleanup_deleted' => [],
                'upsert_present' => [],
            ],
        ]);

        $service->finishBlocked($service->start([
            'operation_kind' => 'deployment_sync_repair',
            'trigger_source' => 'deployment_ui',
            'domains' => ['page-v3'],
        ]), [
            'domains_before' => ['page-v3' => ['status' => 'uninitialized']],
            'plan' => [
                'deployment_refs' => ['current_deployed_ref' => 'head-b'],
                'summary' => ['warnings' => 1],
            ],
            'status' => 'blocked',
            'error' => ['message' => 'bootstrap required'],
        ]);

        $filtered = $service->latest([
            'kind' => 'deployment_sync_repair',
            'status' => 'blocked',
            'domains' => ['page-v3'],
        ], 10);

        $this->assertCount(1, $filtered);
        $this->assertSame('deployment_sync_repair', $filtered->first()->operation_kind);
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
