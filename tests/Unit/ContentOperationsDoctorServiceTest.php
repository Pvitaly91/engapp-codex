<?php

namespace Tests\Unit;

use App\Models\ContentOperationLock;
use App\Models\ContentOperationRun;
use App\Models\ContentSyncState;
use App\Services\ContentDeployment\ContentOperationsDoctorService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentOperationsDoctorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentSyncStatesTable();
        $this->ensureContentOperationRunsTable();
        $this->ensureContentOperationLocksTable();

        ContentSyncState::query()->delete();
        ContentOperationRun::query()->delete();
        ContentOperationLock::query()->delete();
    }

    public function test_core_doctor_reports_schema_passes_and_sync_state_warnings(): void
    {
        $result = app(ContentOperationsDoctorService::class)->run([
            'domains' => 'v3,page-v3',
        ]);

        $this->assertSame('warn', $result['overall_status']);
        $this->assertSame('pass', $this->checkStatus($result, 'content_schema_content_sync_states'));
        $this->assertSame('pass', $this->checkStatus($result, 'content_operation_lock_status'));
        $this->assertSame('warn', $this->checkStatus($result, 'content_sync_state_v3'));
        $this->assertSame('warn', $this->checkStatus($result, 'content_sync_state_page_v3'));
    }

    public function test_active_lock_is_reported_as_warning(): void
    {
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'owner-token',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $result = app(ContentOperationsDoctorService::class)->run([
            'domains' => 'v3',
        ]);

        $this->assertSame('warn', $this->checkStatus($result, 'content_operation_lock_status'));
        $this->assertSame('active', data_get($this->check($result, 'content_operation_lock_status'), 'meta.status'));
    }

    public function test_artifact_check_fails_on_corrupt_payload_json(): void
    {
        Storage::disk('local')->put('content-operation-runs/corrupt-payload.json', 'not-json');

        ContentOperationRun::query()->create([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'dry_run' => true,
            'strict' => false,
            'bootstrap_uninitialized' => false,
            'status' => 'dry_run',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'payload_json_path' => 'content-operation-runs/corrupt-payload.json',
        ]);

        $result = app(ContentOperationsDoctorService::class)->run([
            'domains' => 'v3',
            'with_artifacts' => true,
        ]);

        $this->assertSame('fail', $this->checkStatus($result, 'content_history_artifacts'));
        $this->assertSame('fail', $result['overall_status']);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function checkStatus(array $result, string $code): ?string
    {
        return $this->check($result, $code)['status'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function check(array $result, string $code): array
    {
        return collect((array) ($result['checks'] ?? []))
            ->firstWhere('code', $code) ?? [];
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
            $table->string('last_attempted_status')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->json('last_attempt_meta')->nullable();
            $table->timestamps();
        });
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

    private function ensureContentOperationLocksTable(): void
    {
        if (Schema::hasTable('content_operation_locks')) {
            return;
        }

        Schema::create('content_operation_locks', function (Blueprint $table): void {
            $table->id();
            $table->string('lock_key')->unique();
            $table->string('owner_token')->unique();
            $table->string('operation_kind');
            $table->string('trigger_source');
            $table->json('domains')->nullable();
            $table->unsignedBigInteger('content_operation_run_id')->nullable();
            $table->unsignedBigInteger('operator_user_id')->nullable();
            $table->timestamp('acquired_at')->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->json('meta')->nullable();
        });
    }
}
