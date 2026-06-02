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

class V3RefreshPackageCommandTest extends TestCase
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

    public function test_dry_run_refresh_rolls_back_seeded_package_and_writes_report(): void
    {
        Storage::fake('local');

        $generated = $this->writeGenerated(
            topic: 'Feature V3 Refresh Dry Run',
            namespace: 'Tests\\CodexFeature\\V3RefreshDryRun',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-refresh-dry-run-question');
        $this->seedPackageLive($generated);

        $expectedQuestionCount = Question::query()->count();
        $expectedSavedTestCount = SavedGrammarTest::query()->count();

        $exitCode = Artisan::call('v3:refresh-package', [
            'target' => $generated['preview']['definition_absolute_path'],
            '--dry-run' => true,
            '--check-profile' => 'scaffold',
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $reportFiles = Storage::disk('local')->allFiles('package-refresh-reports/v3');

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['mode']['dry_run']);
        $this->assertTrue((bool) $payload['preflight']['executed']);
        $this->assertSame('admin_refresh', $payload['phases']['refresh_strategy']);
        $this->assertTrue((bool) $payload['result']['refreshed']);
        $this->assertTrue((bool) $payload['result']['seeded_after']);
        $this->assertTrue((bool) $payload['phases']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['seed_run_logged']);
        $this->assertCount(1, $reportFiles);
        $this->assertSame($expectedQuestionCount, Question::query()->count());
        $this->assertSame($expectedSavedTestCount, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')->count());
    }

    public function test_live_refresh_requires_force_and_then_falls_back_to_seed_only_when_package_is_absent(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Refresh Seed Fallback',
            namespace: 'Tests\\CodexFeature\\V3RefreshSeedFallback',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-refresh-seed-fallback-question');

        $blockedExitCode = Artisan::call('v3:refresh-package', [
            'target' => $generated['preview']['package_relative_path'],
            '--skip-release-check' => true,
            '--json' => true,
        ]);
        $blockedPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $blockedExitCode);
        $this->assertSame('force_required', $blockedPayload['error']['reason']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());

        $liveExitCode = Artisan::call('v3:refresh-package', [
            'target' => $generated['preview']['seeder_relative_path'],
            '--skip-release-check' => true,
            '--force' => true,
            '--json' => true,
        ]);
        $livePayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $liveExitCode);
        $this->assertSame('seed_only_fallback', $livePayload['phases']['refresh_strategy']);
        $this->assertFalse((bool) $livePayload['ownership']['seed_run_present']);
        $this->assertFalse((bool) $livePayload['ownership']['package_present_in_db']);
        $this->assertNotEmpty($livePayload['warnings']);
        $this->assertTrue((bool) $livePayload['result']['refreshed']);
        $this->assertTrue((bool) $livePayload['result']['seeded_after']);
        $this->assertFalse((bool) $livePayload['phases']['rolled_back']);
        $this->assertTrue((bool) $livePayload['result']['seed_run_logged']);
        $this->assertSame(1, Question::query()->where('seeder', $generated['preview']['fully_qualified_class_name'])->count());
        $this->assertSame(1, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->count());
    }

    public function test_strict_mode_makes_seed_only_fallback_warning_fatal(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Refresh Strict',
            namespace: 'Tests\\CodexFeature\\V3RefreshStrict',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-refresh-strict-question');

        $exitCode = Artisan::call('v3:refresh-package', [
            'target' => $generated['preview']['real_seeder_relative_path'],
            '--dry-run' => true,
            '--skip-release-check' => true,
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('impact', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertFalse((bool) $payload['result']['refreshed']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_live_refresh_rolls_back_when_reseed_fails_after_delete_phase(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Refresh Rollback',
            namespace: 'Tests\\CodexFeature\\V3RefreshRollback',
        );
        $questionUuid = 'feature-v3-refresh-rollback-question';
        $this->makeDefinitionSeedable($generated, $questionUuid);
        $this->seedPackageLive($generated);

        $definitionPath = (string) $generated['preview']['definition_absolute_path'];
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['questions'] = [];
        File::put(
            $definitionPath,
            json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );

        $exitCode = Artisan::call('v3:refresh-package', [
            'target' => $generated['preview']['real_seeder_relative_path'],
            '--skip-release-check' => true,
            '--force' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('refresh', $payload['error']['stage']);
        $this->assertSame('refresh_failed', $payload['error']['reason']);
        $this->assertTrue((bool) $payload['phases']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['refreshed']);
        $this->assertSame(1, Question::query()->where('uuid', $questionUuid)->count());
        $this->assertSame(1, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->count());
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
            'distribution' => ['A1' => 4, 'B1' => 4],
            'total_questions' => 8,
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
                'question' => 'Feature V3 refresh package {a1}.',
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
