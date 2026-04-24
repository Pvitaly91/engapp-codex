<?php

namespace Tests\Feature;

use App\Models\ContentOperationRun;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentHistoryCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationRunsTable();
        ContentOperationRun::query()->delete();
    }

    public function test_it_lists_recent_runs_as_json(): void
    {
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
            'summary' => ['changed_packages' => 1],
        ]);

        $exitCode = Artisan::call('content:history', [
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['summary']['total']);
        $this->assertSame('apply_changed', $payload['runs'][0]['operation_kind']);
    }

    public function test_it_shows_detail_and_writes_report(): void
    {
        $run = ContentOperationRun::query()->create([
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['v3', 'page-v3'],
            'base_ref' => 'old-sha',
            'head_ref' => 'head-sha',
            'dry_run' => false,
            'strict' => true,
            'bootstrap_uninitialized' => true,
            'status' => 'partial',
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'summary' => ['stopped_phase' => 'upsert_present'],
            'payload_json_path' => 'content-operation-runs/test.json',
            'report_path' => 'content-sync-apply-reports/mock.md',
            'error_excerpt' => 'page-v3 failed',
        ]);

        $exitCode = Artisan::call('content:history', [
            'runId' => $run->id,
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame($run->id, $payload['run']['id']);
        $this->assertStringContainsString('content-operation-history', $payload['artifacts']['report_path']);
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
