<?php

namespace Tests\Unit;

use App\Models\ContentOperationLock;
use App\Services\ContentDeployment\ContentOperationLockService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentOperationLockServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentOperationLocksTable();
        ContentOperationLock::query()->delete();
        config()->set('content-deployment.lock.enabled', true);
        config()->set('content-deployment.lock.ttl_seconds', 3600);
        config()->set('content-deployment.lock.stale_after_seconds', 60);
    }

    public function test_acquiring_fresh_lock_succeeds_and_second_execution_is_blocked(): void
    {
        $service = app(ContentOperationLockService::class);

        $first = $service->acquire([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
        ]);
        $second = $service->acquire([
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['page-v3'],
        ]);

        $this->assertTrue($first['acquired']);
        $this->assertFalse($second['acquired']);
        $this->assertSame('active_lock_present', $second['error']['reason']);
    }

    public function test_stale_lock_requires_explicit_takeover(): void
    {
        $service = app(ContentOperationLockService::class);
        $first = $service->acquire([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
        ]);

        ContentOperationLock::query()
            ->where('owner_token', $first['owner_token'])
            ->update([
                'heartbeat_at' => now()->subMinutes(10),
                'expires_at' => now()->subMinute(),
            ]);

        $blocked = $service->acquire([
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['page-v3'],
        ]);
        $takenOver = $service->acquire([
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => ['page-v3'],
        ], true);

        $this->assertFalse($blocked['acquired']);
        $this->assertSame('stale_lock_takeover_required', $blocked['error']['reason']);
        $this->assertTrue($takenOver['acquired']);
        $this->assertSame('taken_over', $takenOver['status']);
        $this->assertSame(['page-v3'], $takenOver['lock']['domains']);
    }

    public function test_heartbeat_refreshes_and_release_clears_lock(): void
    {
        $service = app(ContentOperationLockService::class);
        $lease = $service->acquire([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
        ]);

        ContentOperationLock::query()
            ->where('owner_token', $lease['owner_token'])
            ->update([
                'heartbeat_at' => now()->subMinutes(5),
                'expires_at' => now()->addMinute(),
            ]);

        $service->heartbeat($lease['owner_token']);
        $refreshed = $service->current();

        $this->assertNotNull($refreshed);
        $this->assertTrue($refreshed->heartbeat_at->greaterThan(now()->subMinute()));

        $service->release($lease['owner_token']);

        $this->assertNull($service->current());
    }

    public function test_attach_run_updates_lock_owner_run_id(): void
    {
        $service = app(ContentOperationLockService::class);
        $lease = $service->acquire([
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => 'deployment_ui',
            'domains' => ['v3', 'page-v3'],
        ]);

        $service->attachRun($lease['owner_token'], 321);

        $this->assertSame(321, $service->current()?->content_operation_run_id);

        $service->release($lease['owner_token']);
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
