<?php

namespace Tests\Unit;

use App\Models\ContentSyncState;
use App\Services\ContentDeployment\ContentSyncStateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentSyncStateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureContentSyncStatesTable();
        ContentSyncState::query()->delete();
    }

    public function test_describe_uses_last_successful_ref_when_present_and_falls_back_when_uninitialized(): void
    {
        ContentSyncState::query()->create([
            'domain' => 'v3',
            'last_successful_ref' => 'v3-synced-sha',
            'last_successful_applied_at' => now()->subHour(),
            'last_attempted_status' => 'success',
            'last_attempted_at' => now()->subHour(),
        ]);

        $service = new ContentSyncStateService();
        $descriptions = $service->describe(
            ['v3', 'page-v3'],
            ['v3' => 'code-sha', 'page-v3' => 'code-sha'],
            'target-sha'
        );

        $this->assertSame('v3-synced-sha', $descriptions['v3']['effective_base_ref']);
        $this->assertTrue($descriptions['v3']['drift_from_code_ref']);
        $this->assertSame('drifted', $descriptions['v3']['status']);
        $this->assertNull($descriptions['page-v3']['sync_state_ref']);
        $this->assertSame('code-sha', $descriptions['page-v3']['effective_base_ref']);
        $this->assertTrue($descriptions['page-v3']['fallback_used']);
        $this->assertTrue($descriptions['page-v3']['sync_state_uninitialized']);
        $this->assertSame('uninitialized', $descriptions['page-v3']['status']);
    }

    public function test_success_advances_cursor_and_failure_only_updates_attempt_metadata(): void
    {
        $service = new ContentSyncStateService();

        $service->recordSuccess(['v3'], ['v3' => 'base-sha'], 'head-sha', ['stage' => 'execution']);
        $state = ContentSyncState::query()->where('domain', 'v3')->firstOrFail();

        $this->assertSame('head-sha', $state->last_successful_ref);
        $this->assertSame('success', $state->last_attempted_status);
        $this->assertSame('base-sha', $state->last_attempted_base_ref);
        $this->assertSame('head-sha', $state->last_attempted_head_ref);

        $service->recordFailure(['v3'], ['v3' => 'head-sha'], 'next-head-sha', ['message' => 'failed']);
        $state->refresh();

        $this->assertSame('head-sha', $state->last_successful_ref);
        $this->assertSame('failed', $state->last_attempted_status);
        $this->assertSame('head-sha', $state->last_attempted_base_ref);
        $this->assertSame('next-head-sha', $state->last_attempted_head_ref);
        $this->assertSame('failed', $state->last_attempt_meta['message']);
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
