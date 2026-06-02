<?php

namespace Tests\Feature;

use App\Models\ContentOperationLock;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentLockStatusCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();
    }

    public function test_it_reports_free_lock_as_json(): void
    {
        $exitCode = Artisan::call('content:lock-status', [
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('free', $payload['lock']['status']);
    }

    public function test_it_reports_active_lock_and_writes_report(): void
    {
        ContentOperationLock::query()->create([
            'lock_key' => 'global_content_operations',
            'owner_token' => 'owner-token',
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
            'content_operation_run_id' => 123,
            'acquired_at' => now(),
            'heartbeat_at' => now(),
            'expires_at' => now()->addHour(),
            'status' => 'active',
        ]);

        $exitCode = Artisan::call('content:lock-status', [
            '--json' => true,
            '--write-report' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('active', $payload['lock']['status']);
        $this->assertSame('apply_changed', $payload['lock']['lock']['operation_kind']);
        $this->assertStringContainsString('content-lock-status', $payload['artifacts']['report_path']);
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
