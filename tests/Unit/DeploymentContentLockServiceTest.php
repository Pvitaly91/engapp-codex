<?php

namespace Tests\Unit;

use App\Models\ContentOperationLock;
use App\Modules\GitDeployment\Services\DeploymentContentLockService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeploymentContentLockServiceTest extends TestCase
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

    public function test_reserving_pre_code_deployment_lock_blocks_second_content_operation(): void
    {
        $service = app(DeploymentContentLockService::class);

        $reservation = $service->reserve($this->preview(), [
            'requested' => true,
            'trigger_source' => 'deployment_ui',
        ]);

        $blocked = app(\App\Services\ContentDeployment\ContentOperationLockService::class)->acquire([
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => ['v3'],
        ]);

        $this->assertTrue($reservation['acquired']);
        $this->assertFalse($blocked['acquired']);
        $this->assertSame('active_lock_present', $blocked['error']['reason']);

        $service->release($reservation);
    }

    public function test_stale_reservation_requires_explicit_takeover(): void
    {
        $lockService = app(\App\Services\ContentDeployment\ContentOperationLockService::class);
        $first = $lockService->acquire([
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

        $service = app(DeploymentContentLockService::class);
        $blocked = $service->reserve($this->preview(), [
            'requested' => true,
            'trigger_source' => 'deployment_ui',
        ]);
        $takenOver = $service->reserve($this->preview(), [
            'requested' => true,
            'trigger_source' => 'deployment_ui',
            'takeover_stale_lock' => true,
        ]);

        $this->assertTrue($blocked['blocked']);
        $this->assertSame('stale_lock_takeover_required', $blocked['error']['reason']);
        $this->assertTrue($takenOver['acquired']);
        $this->assertTrue($takenOver['takeover_performed']);

        $service->release($takenOver);
    }

    public function test_preview_status_reports_required_lock_gate_without_acquiring(): void
    {
        $service = app(DeploymentContentLockService::class);

        $status = $service->previewStatus(true);

        $this->assertSame('free', $status['current_status']);
        $this->assertFalse($status['blocked']);
        $this->assertNull(ContentOperationLock::query()->first());
    }

    /**
     * @return array<string, mixed>
     */
    private function preview(): array
    {
        return [
            'deployment' => [
                'mode' => 'standard',
                'source_kind' => 'deploy',
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'branch' => 'main',
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                ],
            ],
        ];
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
