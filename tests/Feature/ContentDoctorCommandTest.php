<?php

namespace Tests\Feature;

use App\Models\ContentOperationLock;
use App\Models\ContentOperationRun;
use App\Models\ContentSyncState;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentDoctorCommandTest extends TestCase
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

    public function test_json_output_returns_readiness_payload(): void
    {
        $exitCode = Artisan::call('content:doctor', [
            '--json' => true,
            '--domains' => 'v3',
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('warn', $payload['overall_status']);
        $this->assertSame(['v3'], $payload['options']['domains']);
        $this->assertIsArray($payload['checks']);
    }

    public function test_strict_mode_treats_warnings_as_failure_exit_code(): void
    {
        $exitCode = Artisan::call('content:doctor', [
            '--json' => true,
            '--strict' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertGreaterThan(0, $payload['summary']['warn']);
    }

    public function test_command_writes_report(): void
    {
        $exitCode = Artisan::call('content:doctor', [
            '--json' => true,
            '--write-report' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('content-doctor-reports', $payload['artifacts']['report_path']);
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
