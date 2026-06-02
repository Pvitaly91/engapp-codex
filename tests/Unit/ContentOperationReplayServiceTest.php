<?php

namespace Tests\Unit;

use App\Models\ContentOperationRun;
use App\Models\ContentSyncState;
use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ContentOperationReplayService;
use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentSyncApplyService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ContentOperationReplayServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        $this->ensureContentSyncStatesTable();
        ContentOperationRun::query()->delete();
        ContentSyncState::query()->delete();
        Storage::disk('local')->deleteDirectory('content-operation-runs/test-replay');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_replays_failed_apply_changed_as_dry_run_by_default(): void
    {
        $originalRun = $this->storeRun([
            'operation_kind' => 'apply_changed',
            'status' => 'failed',
            'dry_run' => false,
            'domains' => ['v3'],
            'base_ref' => 'base-sha',
            'head_ref' => 'head-sha',
            'payload_json_path' => $this->storePayload('apply-changed.json', [
                'diff' => [
                    'mode' => 'refs',
                    'base' => 'base-sha',
                    'head' => 'head-sha',
                ],
                'scope' => [
                    'domains' => ['v3'],
                    'check_profile' => 'release',
                ],
            ]),
        ]);

        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $changedApply->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(function (array $options): bool {
                return ($options['domains'] ?? null) === ['v3']
                    && ($options['base'] ?? null) === 'base-sha'
                    && ($options['head'] ?? null) === 'head-sha'
                    && ($options['dry_run'] ?? null) === true
                    && ($options['force'] ?? null) === false
                    && ($options['check_profile'] ?? null) === 'release';
            }))
            ->andReturn([
                'diff' => [
                    'mode' => 'refs',
                    'base' => 'base-sha',
                    'head' => 'head-sha',
                    'include_untracked' => false,
                ],
                'scope' => [
                    'domains' => ['v3'],
                    'check_profile' => 'release',
                    'with_release_check' => false,
                    'skip_release_check' => false,
                    'strict' => false,
                ],
                'plan' => [
                    'summary' => [
                        'changed_packages' => 1,
                        'deleted_cleanup_candidates' => 0,
                        'seed_candidates' => 1,
                        'refresh_candidates' => 0,
                        'blocked' => 0,
                        'warnings' => 0,
                    ],
                ],
                'execution' => [
                    'dry_run' => true,
                    'force' => false,
                    'cleanup_deleted' => ['succeeded' => 0, 'packages' => []],
                    'upsert_present' => ['succeeded' => 0, 'packages' => []],
                ],
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => null,
            ]);

        $syncApply = Mockery::mock(ContentSyncApplyService::class);
        $syncApply->shouldNotReceive('run');

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->with('head-sha')->andReturn('head-sha');
        $probe->shouldReceive('resolveCommit')->with('base-sha')->andReturn('base-sha');
        $probe->shouldReceive('currentHeadCommit')->never();

        $service = new ContentOperationReplayService(
            app(ContentOperationRunService::class),
            $changedApply,
            $syncApply,
            app(ContentSyncStateService::class),
            $probe
        );

        $result = $service->run($originalRun->id, [
            'trigger_source' => 'cli',
        ]);

        $this->assertSame('dry_run', $result['replay']['status']);
        $this->assertNotSame($originalRun->id, $result['replay']['new_run']['id'] ?? null);
        $this->assertDatabaseCount('content_operation_runs', 2);
        $newRun = ContentOperationRun::query()->latest('id')->first();
        $this->assertSame($originalRun->id, $newRun?->replayed_from_run_id);
        $this->assertSame('dry_run', $newRun?->status);
        $this->assertSame('apply_changed', $newRun?->operation_kind);
    }

    public function test_it_replays_sync_run_with_recorded_base_refs(): void
    {
        $originalRun = $this->storeRun([
            'operation_kind' => 'apply_sync',
            'status' => 'failed',
            'dry_run' => false,
            'domains' => ['v3', 'page-v3'],
            'head_ref' => 'recorded-head',
            'base_refs_by_domain' => [
                'v3' => 'old-v3',
                'page-v3' => 'old-page',
            ],
            'payload_json_path' => $this->storePayload('apply-sync.json', [
                'domains_before' => [
                    'v3' => ['effective_base_ref' => 'old-v3'],
                    'page-v3' => ['effective_base_ref' => 'old-page'],
                ],
                'plan' => [
                    'deployment_refs' => [
                        'current_deployed_ref' => 'recorded-head',
                    ],
                    'options' => [
                        'check_profile' => 'release',
                    ],
                ],
            ]),
        ]);

        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $changedApply->shouldNotReceive('run');

        $syncApply = Mockery::mock(ContentSyncApplyService::class);
        $syncApply->shouldReceive('run')
            ->once()
            ->with(Mockery::on(function (array $options): bool {
                return ($options['domains'] ?? null) === ['v3', 'page-v3']
                    && ($options['base_refs_by_domain']['v3'] ?? null) === 'old-v3'
                    && ($options['base_refs_by_domain']['page-v3'] ?? null) === 'old-page'
                    && ($options['head_ref'] ?? null) === 'recorded-head'
                    && ($options['dry_run'] ?? null) === true
                    && ($options['force'] ?? null) === false;
            }))
            ->andReturn([
                'domains_before' => [],
                'plan' => [
                    'deployment_refs' => [
                        'current_deployed_ref' => 'recorded-head',
                    ],
                ],
                'apply' => [
                    'executed' => true,
                    'dry_run' => true,
                    'changed_content_result' => null,
                    'bootstrap' => [
                        'requested' => false,
                        'simulated' => [],
                        'applied' => [],
                    ],
                ],
                'domains_after' => [],
                'status' => 'dry_run',
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => null,
            ]);

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->with('recorded-head')->andReturn('recorded-head');
        $probe->shouldReceive('resolveCommit')->with('old-v3')->andReturn('old-v3');
        $probe->shouldReceive('resolveCommit')->with('old-page')->andReturn('old-page');
        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('recorded-head');

        $service = new ContentOperationReplayService(
            app(ContentOperationRunService::class),
            $changedApply,
            $syncApply,
            app(ContentSyncStateService::class),
            $probe
        );

        $result = $service->run($originalRun->id, [
            'trigger_source' => 'cli',
        ]);

        $this->assertSame('dry_run', $result['replay']['status']);
        $this->assertSame('old-v3', $result['replay']['resolved_context']['base_refs_by_domain']['v3'] ?? null);
        $this->assertSame('old-page', $result['replay']['resolved_context']['base_refs_by_domain']['page-v3'] ?? null);
        $this->assertDatabaseCount('content_operation_runs', 2);
    }

    public function test_it_blocks_success_without_allow_success(): void
    {
        $originalRun = $this->storeRun([
            'operation_kind' => 'apply_changed',
            'status' => 'success',
            'dry_run' => false,
            'domains' => ['v3'],
            'base_ref' => 'base-sha',
            'head_ref' => 'head-sha',
            'payload_json_path' => $this->storePayload('success.json', [
                'diff' => [
                    'mode' => 'refs',
                    'base' => 'base-sha',
                    'head' => 'head-sha',
                ],
                'scope' => [
                    'domains' => ['v3'],
                ],
            ]),
        ]);

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldNotReceive('resolveCommit');
        $probe->shouldNotReceive('currentHeadCommit');

        $service = new ContentOperationReplayService(
            app(ContentOperationRunService::class),
            Mockery::mock(ChangedContentApplyService::class),
            Mockery::mock(ContentSyncApplyService::class),
            app(ContentSyncStateService::class),
            $probe
        );

        $result = $service->run($originalRun->id, [
            'trigger_source' => 'cli',
        ]);

        $this->assertSame('blocked', $result['replay']['status']);
        $this->assertSame('successful_original_run_requires_allow_success', $result['replay']['validation']['reasons'][0]['code'] ?? null);
        $this->assertDatabaseCount('content_operation_runs', 2);
        $this->assertSame('blocked', ContentOperationRun::query()->latest('id')->value('status'));
    }

    public function test_it_warns_when_payload_artifact_is_missing_but_row_context_is_still_replayable(): void
    {
        $originalRun = $this->storeRun([
            'operation_kind' => 'apply_changed',
            'status' => 'failed',
            'dry_run' => false,
            'domains' => ['v3'],
            'base_ref' => 'base-sha',
            'head_ref' => 'head-sha',
            'payload_json_path' => 'content-operation-runs/test-replay/missing.json',
        ]);

        $changedApply = Mockery::mock(ChangedContentApplyService::class);
        $changedApply->shouldReceive('run')
            ->once()
            ->andReturn([
                'diff' => [
                    'mode' => 'refs',
                    'base' => 'base-sha',
                    'head' => 'head-sha',
                    'include_untracked' => false,
                ],
                'scope' => [
                    'domains' => ['v3'],
                    'check_profile' => 'release',
                    'with_release_check' => false,
                    'skip_release_check' => false,
                    'strict' => false,
                ],
                'plan' => [
                    'summary' => [
                        'changed_packages' => 0,
                        'deleted_cleanup_candidates' => 0,
                        'seed_candidates' => 0,
                        'refresh_candidates' => 0,
                    ],
                ],
                'execution' => [
                    'dry_run' => true,
                    'force' => false,
                    'cleanup_deleted' => ['succeeded' => 0, 'packages' => []],
                    'upsert_present' => ['succeeded' => 0, 'packages' => []],
                ],
                'artifacts' => [
                    'report_path' => null,
                ],
                'error' => null,
            ]);

        $syncApply = Mockery::mock(ContentSyncApplyService::class);
        $syncApply->shouldNotReceive('run');

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->with('head-sha')->andReturn('head-sha');
        $probe->shouldReceive('resolveCommit')->with('base-sha')->andReturn('base-sha');
        $probe->shouldReceive('currentHeadCommit')->never();

        $service = new ContentOperationReplayService(
            app(ContentOperationRunService::class),
            $changedApply,
            $syncApply,
            app(ContentSyncStateService::class),
            $probe
        );

        $result = $service->run($originalRun->id, [
            'trigger_source' => 'cli',
        ]);

        $this->assertSame('dry_run', $result['replay']['status']);
        $this->assertSame('artifact_missing', $result['replay']['validation']['warnings'][0]['code'] ?? null);
    }

    public function test_it_blocks_sync_replay_when_current_deployed_ref_changed(): void
    {
        $originalRun = $this->storeRun([
            'operation_kind' => 'deployment_sync_repair',
            'status' => 'failed',
            'dry_run' => false,
            'domains' => ['page-v3'],
            'head_ref' => 'recorded-head',
            'base_refs_by_domain' => ['page-v3' => 'recorded-base'],
            'bootstrap_uninitialized' => false,
            'payload_json_path' => $this->storePayload('sync-repair.json', [
                'domains_before' => [
                    'page-v3' => [
                        'sync_state_ref' => 'recorded-base',
                    ],
                ],
                'plan' => [
                    'deployment_refs' => [
                        'current_deployed_ref' => 'recorded-head',
                    ],
                    'options' => [
                        'check_profile' => 'release',
                    ],
                ],
                'apply' => [
                    'bootstrap' => [
                        'requested' => false,
                    ],
                ],
            ]),
        ]);

        ContentSyncState::query()->create([
            'domain' => 'page-v3',
            'last_successful_ref' => 'recorded-base',
            'last_successful_applied_at' => now(),
            'last_attempted_status' => 'success',
            'last_attempted_at' => now(),
        ]);

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('resolveCommit')->with('recorded-head')->andReturn('recorded-head');
        $probe->shouldReceive('resolveCommit')->with('recorded-base')->andReturn('recorded-base');
        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('current-head');

        $service = new ContentOperationReplayService(
            app(ContentOperationRunService::class),
            Mockery::mock(ChangedContentApplyService::class),
            Mockery::mock(ContentSyncApplyService::class),
            app(ContentSyncStateService::class),
            $probe
        );

        $result = $service->run($originalRun->id, [
            'force' => true,
            'trigger_source' => 'deployment_ui',
        ]);

        $this->assertSame('blocked', $result['replay']['status']);
        $this->assertSame('current_deployed_ref_changed_since_original_run', $result['replay']['validation']['reasons'][0]['code'] ?? null);
        $this->assertDatabaseCount('content_operation_runs', 2);
        $this->assertSame($originalRun->id, ContentOperationRun::query()->latest('id')->value('replayed_from_run_id'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function storeRun(array $attributes): ContentOperationRun
    {
        return ContentOperationRun::query()->create(array_merge([
            'replayed_from_run_id' => null,
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'base_ref' => null,
            'head_ref' => null,
            'base_refs_by_domain' => null,
            'dry_run' => false,
            'strict' => false,
            'with_release_check' => false,
            'skip_release_check' => false,
            'bootstrap_uninitialized' => false,
            'status' => 'failed',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'summary' => [],
            'payload_json_path' => null,
            'report_path' => null,
            'error_excerpt' => null,
            'meta' => ['check_profile' => 'release'],
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function storePayload(string $name, array $payload): string
    {
        $path = 'content-operation-runs/test-replay/' . $name;
        Storage::disk('local')->put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    private function ensureContentOperationRunsTable(): void
    {
        if (! Schema::hasTable('content_operation_runs')) {
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
        } elseif (! Schema::hasColumn('content_operation_runs', 'replayed_from_run_id')) {
            Schema::table('content_operation_runs', function (Blueprint $table): void {
                $table->unsignedBigInteger('replayed_from_run_id')->nullable()->after('id');
            });
        }
    }

    private function ensureContentSyncStatesTable(): void
    {
        if (Schema::hasTable('content_sync_states')) {
            return;
        }

        Schema::create('content_sync_states', function (Blueprint $table): void {
            $table->id();
            $table->string('domain')->unique();
            $table->string('last_successful_ref')->nullable();
            $table->timestamp('last_successful_applied_at')->nullable();
            $table->string('last_attempted_base_ref')->nullable();
            $table->string('last_attempted_head_ref')->nullable();
            $table->string('last_attempted_status', 32)->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->json('last_attempt_meta')->nullable();
            $table->timestamps();
        });
    }
}
