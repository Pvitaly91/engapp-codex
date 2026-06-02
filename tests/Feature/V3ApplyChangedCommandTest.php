<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\V3PromptGenerator\V3ChangedPackagesApplyService;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3ApplyChangedCommandTest extends TestCase
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

    public function test_it_preflights_untracked_v3_packages_in_default_working_tree_mode(): void
    {
        $generated = $this->writeGenerated('Apply Changed Alpha', 'Tests\\CodexFeature\\V3ApplyChanged');
        $this->makeDefinitionSeedable($generated, 'v3-apply-changed-alpha-question');
        $questionCount = Question::query()->count();
        $savedTestCount = SavedGrammarTest::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callApplyChanged('database/seeders/V3/Tests/CodexFeature/V3ApplyChanged', [
            '--include-untracked' => true,
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame('working_tree', $payload['diff']['mode']);
        $this->assertTrue((bool) $payload['diff']['include_untracked']);
        $this->assertSame(1, $payload['plan']['summary']['changed_packages']);
        $this->assertSame(1, $payload['preflight']['summary']['candidates']);
        $this->assertSame(1, $payload['preflight']['summary']['ok']);
        $this->assertSame(0, $payload['execution']['cleanup_deleted']['started']);
        $this->assertSame(0, $payload['execution']['upsert_present']['started']);
        $this->assertSame($questionCount, Question::query()->count());
        $this->assertSame($savedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_it_resolves_supported_v3_single_package_targets(): void
    {
        $generated = $this->writeGenerated('Apply Changed Target', 'Tests\\CodexFeature\\V3ApplyChangedTarget');
        $this->makeDefinitionSeedable($generated, 'v3-apply-changed-target-question');
        $targets = [
            $generated['preview']['package_relative_path'],
            $generated['preview']['definition_absolute_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_absolute_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callApplyChanged($target, [
                '--include-untracked' => true,
                '--dry-run' => true,
                '--skip-release-check' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, $target . PHP_EOL . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package'], $target);
            $this->assertSame(1, $payload['plan']['summary']['changed_packages'], $target);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['plan']['packages'][0]['current_relative_path'], $target);
        }
    }

    public function test_it_rejects_targets_outside_the_v3_root(): void
    {
        [$exitCode, $payload] = $this->callApplyChanged(base_path('composer.json'), [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('database/seeders/V3', $payload['error']['message']);
    }

    public function test_live_apply_requires_force(): void
    {
        $generated = $this->writeGenerated('Apply Changed Force', 'Tests\\CodexFeature\\V3ApplyChangedForce');
        $this->makeDefinitionSeedable($generated, 'v3-apply-changed-force-question');

        [$exitCode, $payload] = $this->callApplyChanged($generated['preview']['package_relative_path'], [
            '--include-untracked' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_live_apply_seeds_untracked_v3_package(): void
    {
        $generated = $this->writeGenerated('Apply Changed Live', 'Tests\\CodexFeature\\V3ApplyChangedLive');
        $this->makeDefinitionSeedable($generated, 'v3-apply-changed-live-question');

        [$exitCode, $payload] = $this->callApplyChanged($generated['preview']['package_relative_path'], [
            '--include-untracked' => true,
            '--force' => true,
            '--skip-release-check' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame(1, $payload['execution']['upsert_present']['started']);
        $this->assertSame('ok', $payload['execution']['upsert_present']['packages'][0]['status']);
        $this->assertSame(1, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
        $this->assertSame(1, DB::table('seed_runs')->where('class_name', $generated['preview']['fully_qualified_class_name'])->count());
    }

    public function test_with_release_check_and_skip_release_check_are_reported(): void
    {
        $generated = $this->writeGenerated('Apply Changed Release', 'Tests\\CodexFeature\\V3ApplyChangedRelease');
        $this->makeDefinitionSeedable($generated, 'v3-apply-changed-release-question');

        [$exitCode, $payload] = $this->callApplyChanged($generated['preview']['package_relative_path'], [
            '--include-untracked' => true,
            '--dry-run' => true,
            '--with-release-check' => true,
            '--skip-release-check' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['scope']['with_release_check']);
        $this->assertTrue((bool) $payload['scope']['skip_release_check']);
        $this->assertTrue((bool) $payload['plan']['packages'][0]['release_check']['executed']);
        $this->assertTrue((bool) $payload['preflight']['packages'][0]['service_result']['mode']['release_check_skipped']);
    }

    public function test_it_writes_a_report_and_supports_empty_plan_behavior(): void
    {
        Storage::fake('local');
        $emptyDirectory = base_path('database/seeders/V3/Tests/CodexFeature/V3ApplyChangedEmpty');
        File::ensureDirectoryExists($emptyDirectory);
        $this->cleanupPaths[] = $emptyDirectory;

        [$exitCode, $payload] = $this->callApplyChanged('database/seeders/V3/Tests/CodexFeature/V3ApplyChangedEmpty', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertNull($payload['error']);
        $this->assertSame(0, $payload['plan']['summary']['changed_packages']);
        $this->assertCount(1, Storage::disk('local')->allFiles('changed-package-apply-reports/v3'));
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    public function test_command_passes_staged_mode_to_service(): void
    {
        $mock = Mockery::mock(V3ChangedPackagesApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['staged'] ?? false) === true && ($options['working_tree'] ?? false) === false))
            ->andReturn($this->mockedResult());

        $this->app->instance(V3ChangedPackagesApplyService::class, $mock);

        [$exitCode, $payload] = $this->callApplyChanged(null, [
            '--staged' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame('working_tree', $payload['diff']['mode']);
    }

    public function test_command_passes_base_with_default_head_and_explicit_refs_to_service(): void
    {
        $mock = Mockery::mock(V3ChangedPackagesApplyService::class);
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['base'] ?? null) === 'HEAD~1' && ($options['head'] ?? null) === ''))
            ->andReturn($this->mockedResult());
        $mock->shouldReceive('run')
            ->once()
            ->with(null, Mockery::on(fn (array $options): bool => ($options['base'] ?? null) === 'HEAD~2' && ($options['head'] ?? null) === 'HEAD~1'))
            ->andReturn($this->mockedResult());

        $this->app->instance(V3ChangedPackagesApplyService::class, $mock);

        [$firstExitCode] = $this->callApplyChanged(null, [
            '--base' => 'HEAD~1',
            '--json' => true,
        ]);
        [$secondExitCode] = $this->callApplyChanged(null, [
            '--base' => 'HEAD~2',
            '--head' => 'HEAD~1',
            '--json' => true,
        ]);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callApplyChanged(?string $target, array $arguments): array
    {
        $payloadArguments = $arguments;

        if ($target !== null) {
            $payloadArguments['target'] = $target;
        }

        $exitCode = Artisan::call('v3:apply-changed', $payloadArguments);

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
                'question' => 'Apply changed V3 package {a1}.',
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
     * @return array<string, mixed>
     */
    private function mockedResult(): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'head' => 'HEAD',
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => 'database/seeders/V3',
                'resolved_root_absolute_path' => base_path('database/seeders/V3'),
                'resolved_root_relative_path' => 'database/seeders/V3',
                'single_package' => false,
                'with_release_check' => false,
                'skip_release_check' => false,
                'check_profile' => 'release',
                'strict' => false,
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => true,
                'summary' => [
                    'candidates' => 0,
                    'ok' => 0,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => true,
                'force' => false,
                'fail_fast' => true,
                'folder_transactional' => false,
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
                'upsert_present' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }
}
