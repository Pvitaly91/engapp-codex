<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeploymentContentHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    public function test_history_list_endpoint_returns_runs_json(): void
    {
        ContentOperationRun::query()->create([
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => 'deployment_ui',
            'domains' => ['v3', 'page-v3'],
            'base_ref' => 'base-sha',
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => true,
            'bootstrap_uninitialized' => false,
            'status' => 'success',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'summary' => ['changed_packages' => 2],
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-runs');

        $response->assertOk()
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('runs.0.operation_kind', 'deployment_apply_changed');
    }

    public function test_history_detail_endpoint_returns_payload_json(): void
    {
        $relativePath = 'content-operation-runs/test-detail.json';
        \Storage::disk('local')->put($relativePath, json_encode([
            'status' => 'completed',
            'content_apply' => [
                'executed' => true,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $run = ContentOperationRun::query()->create([
            'operation_kind' => 'deployment_sync_repair',
            'trigger_source' => 'native_deployment_ui',
            'domains' => ['page-v3'],
            'head_ref' => 'head-sha',
            'dry_run' => true,
            'strict' => true,
            'bootstrap_uninitialized' => true,
            'status' => 'dry_run',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'summary' => ['bootstrap_domains' => ['page-v3']],
            'payload_json_path' => $relativePath,
        ]);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-runs/' . $run->id);

        $response->assertOk()
            ->assertJsonPath('run.id', $run->id)
            ->assertJsonPath('run.payload.status', 'completed');
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
