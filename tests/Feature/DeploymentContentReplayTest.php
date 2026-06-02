<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use App\Services\ContentDeployment\ContentOperationReplayService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentReplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_retry_endpoint_returns_json_and_preserves_native_trigger_source(): void
    {
        $run = ContentOperationRun::query()->create([
            'replayed_from_run_id' => null,
            'operation_kind' => 'deployment_sync_repair',
            'trigger_source' => 'native_deployment_ui',
            'domains' => ['page-v3'],
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => true,
            'bootstrap_uninitialized' => false,
            'status' => 'failed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        $service = Mockery::mock(ContentOperationReplayService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::type(ContentOperationRun::class), Mockery::on(fn (array $options): bool => ($options['force'] ?? false) === true
                && ($options['dry_run'] ?? true) === false
                && ($options['write_report'] ?? false) === true
                && ($options['strict'] ?? false) === true
                && ($options['trigger_source'] ?? null) === 'native_deployment_ui'))
            ->andReturn([
                'original_run' => [
                    'id' => $run->id,
                    'operation_kind' => 'deployment_sync_repair',
                ],
                'replay' => [
                    'status' => 'success',
                    'mode' => [
                        'dry_run' => false,
                        'force' => true,
                    ],
                    'validation' => [
                        'blocked' => false,
                        'warnings' => [],
                        'reasons' => [],
                    ],
                    'resolved_context' => [
                        'domains' => ['page-v3'],
                        'head_ref' => 'head-sha',
                    ],
                    'result' => [
                        'status' => 'completed',
                    ],
                    'new_run' => [
                        'id' => 77,
                        'status' => 'success',
                    ],
                ],
                'artifacts' => [
                    'report_path' => storage_path('app/content-operation-replays/mock.md'),
                ],
                'error' => null,
                'status' => 'success',
            ]);

        $this->app->instance(ContentOperationReplayService::class, $service);

        $response = $this->withSession(['admin_authenticated' => true])
            ->postJson('/admin/deployment/content-runs/' . $run->id . '/retry', [
                'run_mode' => 'live',
                'strict' => '1',
            ]);

        $response->assertOk()
            ->assertJsonPath('replay.status', 'success')
            ->assertJsonPath('replay.new_run.id', 77);
    }

    public function test_retry_endpoint_redirects_and_flashes_replay_result_for_html_requests(): void
    {
        $run = ContentOperationRun::query()->create([
            'replayed_from_run_id' => null,
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'base_ref' => 'old-base',
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => false,
            'bootstrap_uninitialized' => false,
            'status' => 'failed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);

        $service = Mockery::mock(ContentOperationReplayService::class);
        $service->shouldReceive('run')
            ->once()
            ->with(Mockery::type(ContentOperationRun::class), Mockery::on(fn (array $options): bool => ($options['force'] ?? true) === false
                && ($options['dry_run'] ?? false) === true
                && ($options['trigger_source'] ?? null) === 'deployment_ui'))
            ->andReturn([
                'original_run' => [
                    'id' => $run->id,
                    'operation_kind' => 'deployment_apply_changed',
                ],
                'replay' => [
                    'status' => 'blocked',
                    'mode' => [
                        'dry_run' => true,
                        'force' => false,
                    ],
                    'validation' => [
                        'blocked' => true,
                        'warnings' => [],
                        'reasons' => [
                            ['code' => 'recorded_head_ref_missing', 'message' => 'Missing head ref.'],
                        ],
                    ],
                    'resolved_context' => [
                        'domains' => ['v3'],
                        'base_ref' => 'old-base',
                        'head_ref' => 'head-sha',
                    ],
                    'result' => null,
                    'new_run' => [
                        'id' => 78,
                        'status' => 'blocked',
                    ],
                ],
                'artifacts' => [
                    'report_path' => storage_path('app/content-operation-replays/mock-blocked.md'),
                ],
                'error' => [
                    'stage' => 'replay_validation',
                    'message' => 'Replay was blocked before canonical execution started.',
                ],
                'status' => 'blocked',
            ]);

        $this->app->instance(ContentOperationReplayService::class, $service);

        $response = $this->withSession(['admin_authenticated' => true])
            ->post('/admin/deployment/content-runs/' . $run->id . '/retry', [
                'run_mode' => 'dry_run',
            ]);

        $response->assertRedirect(route('deployment.content-runs.show', $run->id));
        $response->assertSessionHas('content_operation_replay.replay.status', 'blocked');
        $response->assertSessionHas('content_operation_replay.replay.new_run.id', 78);
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
