<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\V3PromptGenerator\V3FolderDestroyService;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3DestroyFolderCommandTest extends TestCase
{
    use RebuildsComposeTestSchema;

    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->rebuildComposeTestSchema();
    }

    protected function tearDown(): void
    {
        foreach (array_reverse(array_unique($this->cleanupPaths)) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);

                continue;
            }

            File::delete($path);
        }

        Mockery::close();

        parent::tearDown();
    }

    public function test_it_resolves_supported_v3_single_package_targets(): void
    {
        $generated = $this->writeGenerated('Destroy Target Package', 'Tests\\CodexFeature\\V3DestroyTargets');

        $targets = [
            $generated['preview']['package_relative_path'],
            $generated['preview']['definition_absolute_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_absolute_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callDestroy($target, [
                '--dry-run' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame(1, $payload['plan']['summary']['destroy_candidates']);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
        }
    }

    public function test_it_rejects_targets_outside_the_v3_root(): void
    {
        [$exitCode, $payload] = $this->callDestroy(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/V3', $payload['error']['message']);
    }

    public function test_dry_run_preflights_all_candidates_and_leaves_db_files_and_seed_runs_unchanged(): void
    {
        $alpha = $this->writeGenerated('Alpha Destroy Dry Run', 'Tests\\CodexFeature\\V3DestroyDryRun');
        $beta = $this->writeGenerated('Beta Destroy Dry Run', 'Tests\\CodexFeature\\V3DestroyDryRun');
        $this->makeDefinitionSeedable($alpha, 'alpha-destroy-dry-run-question');
        $this->seedPackageLive($alpha);

        $initialQuestionCount = Question::query()->count();
        $initialSavedTestCount = SavedGrammarTest::query()->count();
        $initialSeedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callDestroy('database/seeders/V3/Tests/CodexFeature/V3DestroyDryRun', [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(2, $payload['plan']['summary']['destroy_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['candidates']);
        $this->assertSame(1, $payload['preflight']['summary']['db_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['file_candidates']);
        $this->assertTrue(File::exists($alpha['preview']['seeder_absolute_path']));
        $this->assertTrue(File::isDirectory($alpha['preview']['package_absolute_path']));
        $this->assertTrue(File::exists($beta['preview']['seeder_absolute_path']));
        $this->assertTrue(File::isDirectory($beta['preview']['package_absolute_path']));
        $this->assertSame($initialQuestionCount, Question::query()->count());
        $this->assertSame($initialSavedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame($initialSeedRunCount, DB::table('seed_runs')->count());
    }

    public function test_live_destroy_requires_force(): void
    {
        $generated = $this->writeGenerated('Destroy Force Package', 'Tests\\CodexFeature\\V3DestroyForce');

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['package_relative_path'], [
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertTrue(File::exists($generated['preview']['seeder_absolute_path']));
        $this->assertTrue(File::isDirectory($generated['preview']['package_absolute_path']));
    }

    public function test_blocked_packages_prevent_any_live_destroy_writes(): void
    {
        $generated = $this->writeGenerated('Destroy Blocked Package', 'Tests\\CodexFeature\\V3DestroyBlocked');
        $this->makeDefinitionSeedable($generated, 'destroy-blocked-package-question');
        $this->seedPackageLive($generated);
        DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->delete();

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['package_relative_path'], [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['db_phase']['started']);
        $this->assertSame(0, $payload['execution']['file_phase']['started']);
        $this->assertTrue(File::exists($generated['preview']['seeder_absolute_path']));
        $this->assertGreaterThan(0, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
    }

    public function test_successful_live_run_removes_in_scope_db_seed_runs_and_files_only_inside_scope(): void
    {
        $scopeRoot = 'database/seeders/V3/Tests/CodexFeature/V3DestroyLive';
        $alpha = $this->writeGenerated('Alpha Destroy Live', 'Tests\\CodexFeature\\V3DestroyLive');
        $beta = $this->writeGenerated('Beta Destroy Live', 'Tests\\CodexFeature\\V3DestroyLive');
        $outside = $this->writeGenerated('Outside Destroy Live', 'Tests\\CodexFeature\\V3DestroyOutside');
        $this->makeDefinitionSeedable($alpha, 'alpha-destroy-live-question');
        $this->seedPackageLive($alpha);

        [$exitCode, $payload] = $this->callDestroy($scopeRoot, [
            '--force' => true,
            '--remove-empty-dirs' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(1, $payload['execution']['db_phase']['succeeded']);
        $this->assertSame(2, $payload['execution']['file_phase']['succeeded']);
        $this->assertSame(0, Question::query()->where('seeder', $alpha['preview']['fully_qualified_class_name'])->count());
        $this->assertSame(0, SavedGrammarTest::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->where('class_name', $alpha['preview']['fully_qualified_class_name'])->count());
        $this->assertFalse(File::exists($alpha['preview']['seeder_absolute_path']));
        $this->assertFalse(File::isDirectory($alpha['preview']['package_absolute_path']));
        $this->assertFalse(File::exists($beta['preview']['seeder_absolute_path']));
        $this->assertFalse(File::isDirectory($beta['preview']['package_absolute_path']));
        $this->assertFalse(File::isDirectory(base_path($scopeRoot)));
        $this->assertTrue(File::exists($outside['preview']['seeder_absolute_path']));
        $this->assertTrue(File::isDirectory($outside['preview']['package_absolute_path']));
    }

    public function test_strict_mode_fails_on_v3_destroy_warnings(): void
    {
        $generated = $this->writeGenerated('Destroy Strict Package', 'Tests\\CodexFeature\\V3DestroyStrict');
        $this->makeDefinitionSeedable($generated, 'destroy-strict-package-question');
        $this->seedPackageLive($generated);
        File::delete($generated['preview']['real_seeder_absolute_path']);

        [$exitCode, $payload] = $this->callDestroy($generated['preview']['package_relative_path'], [
            '--dry-run' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertNotEmpty($payload['plan']['packages'][0]['warnings']);
        $this->assertTrue(File::isDirectory($generated['preview']['package_absolute_path']));
        $this->assertGreaterThan(0, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
    }

    public function test_it_writes_a_report_and_supports_empty_scope(): void
    {
        Storage::fake('local');
        $emptyDirectory = base_path('database/seeders/V3/Tests/CodexFeature/V3DestroyEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callDestroy('database/seeders/V3/Tests/CodexFeature/V3DestroyEmpty', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-destroy-reports/v3');

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['total_packages']);
        $this->assertCount(1, $reportFiles);
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    public function test_command_can_render_db_phase_fail_fast_payloads(): void
    {
        $mock = Mockery::mock(V3FolderDestroyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult('db_phase_failed', 'scope-target'));

        $this->app->instance(V3FolderDestroyService::class, $mock);

        [$exitCode, $payload] = $this->callDestroy('scope-target', [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('db_phase_failed', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['file_phase']['started']);
    }

    public function test_command_can_render_file_phase_fail_fast_payloads(): void
    {
        $mock = Mockery::mock(V3FolderDestroyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->andReturn($this->mockedResult('file_phase_failed', 'scope-target'));

        $this->app->instance(V3FolderDestroyService::class, $mock);

        [$exitCode, $payload] = $this->callDestroy('scope-target', [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('file_phase_failed', $payload['error']['reason']);
        $this->assertSame('pending', $payload['execution']['file_phase']['packages'][2]['status']);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callDestroy(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('v3:destroy-folder', array_merge([
            'target' => $target,
        ], $arguments));

        /** @var array<string, mixed> $payload */
        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        return [$exitCode, $payload];
    }

    /**
     * @return array<string, mixed>
     */
    private function writeGenerated(string $topic, string $namespace): array
    {
        $preview = app(V3SeederBlueprintService::class)->buildPreview($namespace, $topic);
        $this->cleanupPaths[] = $preview['seeder_absolute_path'];
        $this->cleanupPaths[] = $preview['package_absolute_path'];

        $generated = [
            'source' => [
                'source_type' => 'manual_topic',
                'source_label' => 'Manual topic',
                'topic' => $topic,
            ],
            'preview' => $preview,
            'distribution' => ['A1' => 4],
            'total_questions' => 4,
        ];

        app(V3SkeletonWriterService::class)->write($generated, true);

        return [
            'preview' => $preview,
        ];
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function makeDefinitionSeedable(array $generated, string $questionUuid): void
    {
        $definitionPath = (string) $generated['preview']['definition_absolute_path'];
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);

        $definition['questions'] = [
            [
                'uuid' => $questionUuid,
                'question' => 'Destroy folder V3 package {a1}.',
                'level' => 'A1',
                'markers' => [
                    'a1' => [
                        'answer' => 'works',
                        'options' => ['works', 'fails'],
                    ],
                ],
            ],
        ];

        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    /**
     * @param  array<string, mixed>  $generated
     */
    private function seedPackageLive(array $generated): void
    {
        $exitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['package_relative_path'],
            '--skip-release-check' => true,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());
    }

    /**
     * @return array<string, mixed>
     */
    private function mockedResult(string $reason, string $target): array
    {
        return [
            'scope' => [
                'input' => $target,
                'resolved_root_absolute_path' => base_path('database/seeders/V3/Tests/CodexFeature/Mocked'),
                'resolved_root_relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked',
                'single_package' => false,
                'mode' => 'destroy',
                'strict' => false,
            ],
            'plan' => [
                'summary' => [
                    'total_packages' => 3,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'unseed_candidates' => 0,
                    'destroy_files_candidates' => 0,
                    'destroy_candidates' => 3,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'packages' => [
                    ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/AlphaSeeder', 'recommended_action' => 'destroy', 'needs_unseed' => true, 'needs_file_destroy' => true],
                    ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/BetaSeeder', 'recommended_action' => 'destroy', 'needs_unseed' => true, 'needs_file_destroy' => true],
                    ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/GammaSeeder', 'recommended_action' => 'destroy', 'needs_unseed' => false, 'needs_file_destroy' => true],
                ],
                'error' => null,
            ],
            'preflight' => [
                'executed' => true,
                'db_packages' => [],
                'file_packages' => [],
                'summary' => [
                    'candidates' => 3,
                    'db_candidates' => 2,
                    'file_candidates' => 3,
                    'ok' => 3,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
            ],
            'execution' => [
                'dry_run' => false,
                'force' => true,
                'remove_empty_dirs' => false,
                'fail_fast' => true,
                'folder_transactional' => false,
                'db_phase' => [
                    'started' => 2,
                    'completed' => 2,
                    'succeeded' => $reason === 'file_phase_failed' ? 2 : 1,
                    'failed' => $reason === 'db_phase_failed' ? 1 : 0,
                    'stopped_on' => $reason === 'db_phase_failed' ? 'database/seeders/V3/Tests/CodexFeature/Mocked/BetaSeeder' : null,
                    'packages' => [
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/AlphaSeeder', 'status' => 'ok', 'seed_run_removed' => true],
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/BetaSeeder', 'status' => $reason === 'db_phase_failed' ? 'failed' : 'ok', 'seed_run_removed' => $reason !== 'db_phase_failed'],
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/GammaSeeder', 'status' => 'skipped', 'seed_run_removed' => false],
                    ],
                ],
                'file_phase' => [
                    'started' => $reason === 'db_phase_failed' ? 0 : 2,
                    'completed' => $reason === 'file_phase_failed' ? 2 : ($reason === 'db_phase_failed' ? 0 : 2),
                    'succeeded' => $reason === 'file_phase_failed' ? 1 : ($reason === 'db_phase_failed' ? 0 : 2),
                    'failed' => $reason === 'file_phase_failed' ? 1 : 0,
                    'stopped_on' => $reason === 'file_phase_failed' ? 'database/seeders/V3/Tests/CodexFeature/Mocked/BetaSeeder' : null,
                    'packages' => [
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/AlphaSeeder', 'status' => $reason === 'db_phase_failed' ? 'pending' : 'ok'],
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/BetaSeeder', 'status' => $reason === 'file_phase_failed' ? 'failed' : ($reason === 'db_phase_failed' ? 'pending' : 'ok')],
                        ['relative_path' => 'database/seeders/V3/Tests/CodexFeature/Mocked/GammaSeeder', 'status' => 'pending'],
                    ],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'execution',
                'reason' => $reason,
                'message' => $reason === 'db_phase_failed'
                    ? 'Folder destroy stopped during DB phase; file phase did not start.'
                    : 'Folder destroy stopped during file phase after DB phase completed.',
            ],
        ];
    }
}
