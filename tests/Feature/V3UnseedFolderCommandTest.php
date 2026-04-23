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

class V3UnseedFolderCommandTest extends TestCase
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

    public function test_it_resolves_supported_single_package_targets_for_folder_unseed(): void
    {
        $generated = $this->writeGenerated('Folder Unseed Targets Package', 'Tests\\CodexFeature\\V3UnseedFolderTargets');
        $this->makeDefinitionSeedable($generated, 'v3-folder-unseed-targets-question');
        $this->seedPackageLive($generated);

        $targets = [
            $generated['preview']['package_relative_path'],
            $generated['preview']['definition_absolute_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_absolute_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callUnseedFolder($target, [
                '--dry-run' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode);
            $this->assertTrue((bool) $payload['scope']['single_package']);
            $this->assertSame(1, $payload['plan']['summary']['total_packages']);
            $this->assertSame(1, $payload['plan']['summary']['unseed_candidates']);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
            $this->assertSame(1, $payload['preflight']['summary']['ok']);
            $this->assertSame(0, $payload['execution']['started']);
        }
    }

    public function test_dry_run_preflights_every_package_and_keeps_db_unchanged(): void
    {
        Storage::fake('local');

        $namespace = 'Tests\\CodexFeature\\V3UnseedFolderDryRun';
        $alpha = $this->writeGenerated('Alpha Folder Unseed Dry Run', $namespace);
        $beta = $this->writeGenerated('Beta Folder Unseed Dry Run', $namespace);
        $this->makeDefinitionSeedable($alpha, 'alpha-folder-unseed-dry-run-question');
        $this->makeDefinitionSeedable($beta, 'beta-folder-unseed-dry-run-question');
        $this->seedPackageLive($alpha);
        $this->seedPackageLive($beta);

        $questionCount = Question::query()->count();
        $savedTestCount = SavedGrammarTest::query()->count();
        $linkCount = DB::table('saved_grammar_test_questions')->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callUnseedFolder('database/seeders/V3/Tests/CodexFeature/V3UnseedFolderDryRun', [
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('folder-unseed-reports/v3');

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertTrue((bool) $payload['execution']['dry_run']);
        $this->assertSame(2, $payload['plan']['summary']['unseed_candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['candidates']);
        $this->assertSame(2, $payload['preflight']['summary']['ok']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['plan']['packages'][0]['relative_path']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['preflight']['packages'][0]['relative_path']);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertCount(1, $reportFiles);
        $this->assertSame($questionCount, Question::query()->count());
        $this->assertSame($savedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame($linkCount, DB::table('saved_grammar_test_questions')->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    public function test_live_folder_unseed_requires_force_after_clean_preflight(): void
    {
        $generated = $this->writeGenerated('Folder Unseed Force Package', 'Tests\\CodexFeature\\V3UnseedFolderForce');
        $this->makeDefinitionSeedable($generated, 'v3-folder-unseed-force-question');
        $this->seedPackageLive($generated);

        [$dryRunExitCode, $dryRunPayload] = $this->callUnseedFolder($generated['preview']['package_relative_path'], [
            '--dry-run' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $dryRunExitCode, json_encode($dryRunPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->assertNull($dryRunPayload['error']);

        [$exitCode, $payload] = $this->callUnseedFolder($generated['preview']['package_relative_path'], [
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('force_required', $payload['error']['reason']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertSame(1, Question::query()->count());
        $this->assertSame(1, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')->count());
    }

    public function test_preflight_failure_aborts_live_folder_unseed_before_any_writes(): void
    {
        $namespace = 'Tests\\CodexFeature\\V3UnseedFolderPreflight';
        $alpha = $this->writeGenerated('Alpha Folder Unseed Preflight', $namespace);
        $beta = $this->writeGenerated('Beta Folder Unseed Preflight', $namespace);
        $alphaQuestionUuid = '11111111-1111-4111-8111-111111111111';
        $betaQuestionUuid = '22222222-2222-4222-8222-222222222222';

        $this->makeDefinitionSeedable($alpha, $alphaQuestionUuid);
        $this->makeDefinitionSeedable($beta, $betaQuestionUuid);
        $this->seedPackageLive($alpha);
        $this->seedPackageLive($beta);

        $foreignSavedTest = SavedGrammarTest::query()->create([
            'uuid' => 'v3-folder-unseed-foreign-test',
            'slug' => 'v3-folder-unseed-foreign-test',
            'name' => 'V3 Folder Unseed Foreign Test',
            'filters' => [],
        ]);
        $foreignSavedTest->questionLinks()->create([
            'question_uuid' => $betaQuestionUuid,
            'position' => 1,
        ]);

        $questionCount = Question::query()->count();
        $savedTestCount = SavedGrammarTest::query()->count();
        $seedRunCount = DB::table('seed_runs')->count();

        [$exitCode, $payload] = $this->callUnseedFolder('database/seeders/V3/Tests/CodexFeature/V3UnseedFolderPreflight', [
            '--force' => true,
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('preflight_failed', $payload['error']['reason']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['error']['package']);
        $this->assertContains($payload['error']['service_error']['reason'], [
            'canonical_saved_test_expanded',
            'external_saved_tests_reference_package',
        ]);
        $this->assertSame(0, $payload['execution']['started']);
        $this->assertGreaterThanOrEqual(1, (int) $payload['preflight']['summary']['fail']);
        $this->assertGreaterThanOrEqual(0, (int) $payload['preflight']['summary']['ok']);
        $this->assertSame($questionCount, Question::query()->count());
        $this->assertSame($savedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame($seedRunCount, DB::table('seed_runs')->count());
    }

    /**
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callUnseedFolder(string $target, array $arguments): array
    {
        $exitCode = Artisan::call('v3:unseed-folder', array_merge([
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
                'question' => 'Folder unseed V3 package {a1}.',
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
}
