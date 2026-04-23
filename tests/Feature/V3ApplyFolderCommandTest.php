<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3ApplyFolderCommandTest extends TestCase
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
        foreach (array_reverse($this->cleanupPaths) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);

                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_it_resolves_supported_v3_single_package_targets(): void
    {
        $generated = $this->writeGenerated('Apply Target Package', 'Tests\\CodexFeature\\V3ApplyTargets');
        $this->makeDefinitionSeedable($generated, 'v3-apply-target-package-question');

        $targets = [
            $generated['preview']['package_relative_path'],
            $generated['preview']['definition_absolute_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_absolute_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callApply($target, [
                '--dry-run' => true,
                '--skip-release-check' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode);
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
            $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        }
    }

    public function test_it_resolves_a_v3_folder_root_to_one_subtree(): void
    {
        $alpha = $this->writeGenerated('Alpha Folder Apply', 'Tests\\CodexFeature\\V3ApplyFolderRoot');
        $beta = $this->writeGenerated('Beta Folder Apply', 'Tests\\CodexFeature\\V3ApplyFolderRoot');
        $this->makeDefinitionSeedable($alpha, 'v3-folder-root-alpha-question');
        $this->makeDefinitionSeedable($beta, 'v3-folder-root-beta-question');

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplyFolderRoot', [
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertFalse((bool) $payload['scope']['single_package']);
        $this->assertSame(2, $payload['plan']['summary']['total_packages']);
        $this->assertSame(2, $payload['execution']['started']);
    }

    public function test_it_rejects_targets_outside_the_v3_root(): void
    {
        [$exitCode, $payload] = $this->callApply(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/V3', $payload['error']['message']);
    }

    public function test_live_apply_requires_force(): void
    {
        $generated = $this->writeGenerated('Live Apply Force', 'Tests\\CodexFeature\\V3ApplyForce');
        $this->makeDefinitionSeedable($generated, 'v3-live-apply-force-question');

        [$exitCode, $payload] = $this->callApply($generated['preview']['package_relative_path'], [
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_missing_mode_executes_only_seed_candidates(): void
    {
        $alpha = $this->writeGenerated('Alpha Missing Apply', 'Tests\\CodexFeature\\V3ApplyMissing');
        $beta = $this->writeGenerated('Beta Missing Apply', 'Tests\\CodexFeature\\V3ApplyMissing');
        $this->makeDefinitionSeedable($alpha, 'v3-missing-alpha-question');
        $this->makeDefinitionSeedable($beta, 'v3-missing-beta-question');
        $this->seedPackageLive($alpha);

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplyMissing', [
            '--mode' => 'missing',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(1, $payload['execution']['started']);
        $this->assertSame('skip', $payload['execution']['packages'][0]['action']);
        $this->assertSame('skipped', $payload['execution']['packages'][0]['status']);
        $this->assertSame('seed', $payload['execution']['packages'][1]['action']);
        $this->assertSame('ok', $payload['execution']['packages'][1]['status']);
    }

    public function test_refresh_mode_executes_only_refresh_candidates(): void
    {
        $alpha = $this->writeGenerated('Alpha Refresh Apply', 'Tests\\CodexFeature\\V3ApplyRefresh');
        $beta = $this->writeGenerated('Beta Refresh Apply', 'Tests\\CodexFeature\\V3ApplyRefresh');
        $this->makeDefinitionSeedable($alpha, 'v3-refresh-alpha-question');
        $this->makeDefinitionSeedable($beta, 'v3-refresh-beta-question');
        $this->seedPackageLive($alpha);

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplyRefresh', [
            '--mode' => 'refresh',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, $payload['execution']['started']);
        $this->assertSame('refresh', $payload['execution']['packages'][0]['action']);
        $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        $this->assertSame('skip', $payload['execution']['packages'][1]['action']);
        $this->assertSame('skipped', $payload['execution']['packages'][1]['status']);
    }

    public function test_sync_mode_executes_seed_and_refresh_and_dry_run_leaves_db_unchanged(): void
    {
        $alpha = $this->writeGenerated('Alpha Sync Apply', 'Tests\\CodexFeature\\V3ApplySync');
        $beta = $this->writeGenerated('Beta Sync Apply', 'Tests\\CodexFeature\\V3ApplySync');
        $this->makeDefinitionSeedable($alpha, 'v3-sync-alpha-question');
        $this->makeDefinitionSeedable($beta, 'v3-sync-beta-question');
        $this->seedPackageLive($alpha);

        $questionCount = Question::query()->count();
        $savedTestCount = SavedGrammarTest::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplySync', [
            '--mode' => 'sync',
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(2, $payload['execution']['started']);
        $this->assertSame('refresh', $payload['execution']['packages'][0]['action']);
        $this->assertSame('seed', $payload['execution']['packages'][1]['action']);
        $this->assertSame($questionCount, Question::query()->count());
        $this->assertSame($savedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_blocked_planner_state_aborts_before_any_writes(): void
    {
        $generated = $this->writeGenerated('Blocked Apply', 'Tests\\CodexFeature\\V3ApplyBlocked');
        $this->makeDefinitionSeedable($generated, 'v3-apply-blocked-question');
        $this->seedPackageLive($generated);
        DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->delete();

        [$exitCode, $payload] = $this->callApply($generated['preview']['package_relative_path'], [
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertSame(1, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
    }

    public function test_fail_fast_stops_after_first_package_failure_and_reports_pending_packages(): void
    {
        $alpha = $this->writeGenerated('Alpha Fail Fast Apply', 'Tests\\CodexFeature\\V3ApplyFailFast');
        $beta = $this->writeGenerated('Beta Fail Fast Apply', 'Tests\\CodexFeature\\V3ApplyFailFast');
        $gamma = $this->writeGenerated('Gamma Fail Fast Apply', 'Tests\\CodexFeature\\V3ApplyFailFast');
        $this->makeDefinitionSeedable($alpha, 'v3-fail-fast-alpha-question');
        $this->makeDefinitionSeedable($beta, 'v3-fail-fast-beta-question');
        $this->makeDefinitionSeedable($gamma, 'v3-fail-fast-gamma-question');
        $this->seedPackageLive($beta);
        $this->breakDefinition($beta);

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplyFailFast', [
            '--mode' => 'sync',
            '--force' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('package_failed', $payload['error']['reason']);
        $this->assertSame(2, $payload['execution']['completed']);
        $this->assertSame('ok', $payload['execution']['packages'][0]['status']);
        $this->assertSame('failed', $payload['execution']['packages'][1]['status']);
        $this->assertSame('pending', $payload['execution']['packages'][2]['status']);
        $this->assertTrue((bool) ($payload['execution']['packages'][1]['service_result']['phases']['rolled_back'] ?? false));
        $classByPackage = [
            $alpha['preview']['package_relative_path'] => $alpha['preview']['fully_qualified_class_name'],
            $beta['preview']['package_relative_path'] => $beta['preview']['fully_qualified_class_name'],
            $gamma['preview']['package_relative_path'] => $gamma['preview']['fully_qualified_class_name'],
        ];

        foreach ((array) $payload['execution']['packages'] as $package) {
            $className = $classByPackage[$package['relative_path']] ?? null;
            $this->assertNotNull($className);

            $expectedCount = match ($package['status']) {
                'ok' => ($package['action'] ?? null) === 'seed' ? 1 : 1,
                'pending' => 0,
                default => null,
            };

            if ($expectedCount === null) {
                continue;
            }

            $this->assertSame(
                $expectedCount,
                Question::query()->where('seeder', $className)->count(),
                sprintf(
                    'Package %s with status %s and action %s had an unexpected persisted question count.',
                    $package['relative_path'],
                    $package['status'],
                    $package['action']
                )
            );
        }
    }

    public function test_with_release_check_and_skip_release_check_are_reported_in_json_output(): void
    {
        $generated = $this->writeGenerated('Release Check Apply', 'Tests\\CodexFeature\\V3ApplyReleaseCheck');
        $this->makeDefinitionSeedable($generated, 'v3-release-check-apply-question');

        [$exitCode, $payload] = $this->callApply($generated['preview']['package_relative_path'], [
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['scope']['with_release_check']);
        $this->assertTrue((bool) $payload['plan']['packages'][0]['release_check']['executed']);
        $this->assertTrue((bool) $payload['execution']['packages'][0]['service_result']['mode']['release_check_skipped']);
    }

    public function test_strict_mode_fails_before_execution_when_planner_returns_release_check_warnings(): void
    {
        $generated = $this->writeGenerated('Strict Apply', 'Tests\\CodexFeature\\V3ApplyStrict');
        $this->makeDefinitionSeedable($generated, 'v3-strict-apply-question');

        [$exitCode, $payload] = $this->callApply($generated['preview']['package_relative_path'], [
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('release_check', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertSame(0, $payload['execution']['started']);
    }

    public function test_it_writes_a_report_and_supports_empty_scope(): void
    {
        Storage::fake('local');
        $emptyDirectory = base_path('database/seeders/V3/Tests/CodexFeature/V3ApplyEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callApply('database/seeders/V3/Tests/CodexFeature/V3ApplyEmpty', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-apply-reports/v3');

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['total_packages']);
        $this->assertSame([], $payload['execution']['packages']);
        $this->assertCount(1, $reportFiles);
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callApply(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('v3:apply-folder', array_merge([
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
                'question' => 'Folder apply V3 package {a1}.',
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
    private function breakDefinition(array $generated): void
    {
        $definitionPath = (string) $generated['preview']['definition_absolute_path'];
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['questions'] = [];

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
}
