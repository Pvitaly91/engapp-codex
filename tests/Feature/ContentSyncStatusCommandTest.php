<?php

namespace Tests\Feature;

use App\Models\ContentSyncState;
use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ContentSyncStatusCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentSyncStatesTable();
        ContentSyncState::query()->delete();
    }

    public function test_json_output_reports_status_and_current_code_ref(): void
    {
        ContentSyncState::query()->create([
            'domain' => 'v3',
            'last_successful_ref' => 'synced-sha',
            'last_successful_applied_at' => now()->subMinute(),
            'last_attempted_status' => 'success',
            'last_attempted_at' => now()->subMinute(),
        ]);

        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('code-sha');
        $this->app->instance(DeploymentGitRefProbe::class, $probe);

        $exitCode = Artisan::call('content:sync-status', [
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('code-sha', $payload['current_code_ref']);
        $this->assertSame('drifted', $payload['domains']['v3']['status']);
        $this->assertSame('uninitialized', $payload['domains']['page-v3']['status']);
    }

    public function test_command_writes_report(): void
    {
        $probe = Mockery::mock(DeploymentGitRefProbe::class);
        $probe->shouldReceive('currentHeadCommit')->once()->andReturn('code-sha');
        $this->app->instance(DeploymentGitRefProbe::class, $probe);

        $exitCode = Artisan::call('content:sync-status', [
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('content-sync-status', $payload['artifacts']['report_path']);
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
}
