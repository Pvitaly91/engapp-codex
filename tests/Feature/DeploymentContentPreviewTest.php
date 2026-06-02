<?php

namespace Tests\Feature;

use App\Modules\GitDeployment\Services\ChangedContentDeploymentPreviewService;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DeploymentContentPreviewTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDeploymentTables();
    }

    public function test_shell_preview_endpoint_returns_json_payload(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')
            ->once()
            ->andReturn($this->previewPayload('standard', 'deploy', false));

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);

        $response = $this->withSession(['admin_authenticated' => true])
            ->getJson('/admin/deployment/content-preview?source_kind=deploy&branch=main');

        $response->assertOk()
            ->assertJsonPath('deployment.mode', 'standard')
            ->assertJsonPath('deployment.source_kind', 'deploy')
            ->assertJsonPath('gate.blocked', false)
            ->assertJsonPath('content_plan.summary.changed_packages', 1);
    }

    public function test_native_preview_screen_renders_the_preview_card(): void
    {
        $previewService = Mockery::mock(ChangedContentDeploymentPreviewService::class);
        $previewService->shouldReceive('preview')
            ->once()
            ->andReturn($this->previewPayload('native', 'backup_restore', true));
        $nativeDeployment = Mockery::mock(NativeGitDeploymentService::class);
        $nativeDeployment->shouldReceive('currentBranch')->andReturn('main');
        $nativeDeployment->shouldReceive('headCommit')->andReturn('base-sha');

        $this->app->instance(ChangedContentDeploymentPreviewService::class, $previewService);
        $this->app->instance(NativeGitDeploymentService::class, $nativeDeployment);

        $response = $this->withSession(['admin_authenticated' => true])
            ->get('/admin/deployment/native/content-preview?source_kind=backup_restore&commit=deadbeef');

        $response->assertOk()
            ->assertSee('Content Sync State')
            ->assertSee('Content-Aware Deployment Preview')
            ->assertSee('Deployment blocked')
            ->assertSee('Strict deployment gate treats 1 content warning(s) as blockers.');
    }

    /**
     * @return array<string, mixed>
     */
    private function previewPayload(string $mode, string $sourceKind, bool $blocked): array
    {
        return [
            'deployment' => [
                'mode' => $mode,
                'source_kind' => $sourceKind,
                'base_ref' => 'base-sha',
                'head_ref' => 'head-sha',
                'branch' => $sourceKind === 'deploy' ? 'main' : null,
                'commit' => $sourceKind === 'backup_restore' ? 'deadbeef' : null,
                'with_release_check' => true,
                'check_profile' => 'release',
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                    'resolved_roots' => [],
                ],
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 1,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 1,
                    'refresh_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => $blocked ? 1 : 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [
                        [
                            'domain' => 'page-v3',
                            'relative_path' => 'database/seeders/Page_V3/Theory/CategorySeeder',
                            'package_type' => 'category',
                            'change_type' => 'modified',
                            'package_state' => 'seeded',
                            'recommended_action' => 'refresh',
                            'release_check' => [
                                'status' => 'warn',
                            ],
                        ],
                    ],
                ],
                'packages' => [],
                'error' => $blocked ? [
                    'stage' => 'planning',
                    'message' => 'Warnings are fatal.',
                ] : null,
            ],
            'content_sync' => [
                'domains' => [
                    'v3' => [
                        'domain' => 'v3',
                        'sync_state_ref' => 'v3-synced-sha',
                        'fallback_base_ref' => 'base-sha',
                        'effective_base_ref' => 'v3-synced-sha',
                        'fallback_used' => false,
                        'drift_from_code_ref' => true,
                        'sync_state_uninitialized' => false,
                        'status' => 'drifted',
                        'target_head_ref' => 'head-sha',
                    ],
                    'page-v3' => [
                        'domain' => 'page-v3',
                        'sync_state_ref' => null,
                        'fallback_base_ref' => 'base-sha',
                        'effective_base_ref' => 'base-sha',
                        'fallback_used' => true,
                        'drift_from_code_ref' => false,
                        'sync_state_uninitialized' => true,
                        'status' => 'uninitialized',
                        'target_head_ref' => 'head-sha',
                    ],
                ],
            ],
            'gate' => [
                'strict' => true,
                'blocked' => $blocked,
                'reasons' => $blocked ? ['Strict deployment gate treats 1 content warning(s) as blockers.'] : [],
            ],
            'error' => $blocked ? [
                'stage' => 'planning',
                'message' => 'Warnings are fatal.',
            ] : null,
        ];
    }

    private function ensureDeploymentTables(): void
    {
        if (! Schema::hasTable('backup_branches')) {
            Schema::create('backup_branches', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->unique();
                $table->string('commit_hash');
                $table->timestamp('pushed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('branch_usage_history')) {
            Schema::create('branch_usage_history', function (Blueprint $table): void {
                $table->id();
                $table->string('branch_name');
                $table->string('action');
                $table->text('description')->nullable();
                $table->json('paths')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
