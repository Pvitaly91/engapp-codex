<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3SeedPackageCommandTest extends TestCase
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

    public function test_dry_run_rolls_back_seed_changes_for_absolute_definition_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Seed Dry Run',
            namespace: 'Tests\\CodexFeature\\V3SeedDryRun',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-seed-dry-run-question');

        $exitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['definition_absolute_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame(
            $generated['preview']['definition_relative_path'],
            $payload['target']['definition_relative_path']
        );
        $this->assertSame(
            $generated['preview']['fully_qualified_class_name'],
            $payload['target']['resolved_seeder_class']
        );
        $this->assertTrue((bool) $payload['mode']['dry_run']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertTrue((bool) $payload['result']['seeded']);
        $this->assertTrue((bool) $payload['result']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['seed_run_logged']);
        $this->assertSame(1, (int) $payload['definition_summary']['question_count']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, SavedGrammarTest::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_release_profile_blocks_live_seed_for_non_release_ready_package_directory_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Seed Release Gate',
            namespace: 'Tests\\CodexFeature\\V3SeedReleaseGate',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-seed-release-gate-question');

        $exitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['package_relative_path'],
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('checks_failed', $payload['error']['reason']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertFalse((bool) $payload['result']['seeded']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_skip_release_check_allows_live_seed_and_safe_rerun_for_loader_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Seed Live',
            namespace: 'Tests\\CodexFeature\\V3SeedLive',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-seed-live-question');

        $firstExitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['seeder_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $firstPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $secondExitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['seeder_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $secondPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $firstExitCode);
        $this->assertSame(0, $secondExitCode);
        $this->assertFalse((bool) $firstPayload['preflight']['executed']);
        $this->assertTrue((bool) $firstPayload['result']['seeded']);
        $this->assertFalse((bool) $firstPayload['result']['rolled_back']);
        $this->assertTrue((bool) $firstPayload['result']['seed_run_logged']);
        $this->assertTrue((bool) $secondPayload['result']['seeded']);
        $this->assertSame(1, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
        $this->assertSame(1, SavedGrammarTest::query()->where('slug', $generated['saved_test_slug'])->count());
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->count());
    }

    public function test_strict_mode_makes_scaffold_warnings_fatal_for_real_seeder_target(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Seed Strict',
            namespace: 'Tests\\CodexFeature\\V3SeedStrict',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-seed-strict-question');

        $exitCode = Artisan::call('v3:seed-package', [
            'target' => $generated['preview']['real_seeder_relative_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('preflight', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertFalse((bool) $payload['result']['seeded']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function writeGenerated(
        string $topic,
        string $namespace,
    ): array {
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
            'distribution' => ['A1' => 4, 'B1' => 4],
            'total_questions' => 8,
        ];

        app(V3SkeletonWriterService::class)->write($generated, true);

        $definition = json_decode(
            File::get($preview['definition_absolute_path']),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return [
            'preview' => $preview,
            'saved_test_slug' => (string) ($definition['saved_test']['slug'] ?? ''),
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
                'question' => 'Feature V3 seed package {a1}.',
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
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
        );
    }
}
