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

class V3UnseedPackageCommandTest extends TestCase
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

    public function test_dry_run_reports_delete_impact_without_persisting_changes(): void
    {
        Storage::fake('local');

        $generated = $this->writeGenerated(
            topic: 'Feature V3 Unseed Dry Run',
            namespace: 'Tests\\CodexFeature\\V3UnseedDryRun',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-unseed-dry-run-question');
        $this->seedPackageLive($generated);

        $expectedQuestionCount = Question::query()->count();
        $expectedSavedTestCount = SavedGrammarTest::query()->count();
        $expectedSavedTestQuestionCount = DB::table('saved_grammar_test_questions')->count();

        $exitCode = Artisan::call('v3:unseed-package', [
            'target' => $generated['preview']['definition_absolute_path'],
            '--dry-run' => true,
            '--write-report' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $reportFiles = Storage::disk('local')->allFiles('package-unseed-reports/v3');

        $this->assertSame(0, $exitCode);
        $this->assertTrue((bool) $payload['mode']['dry_run']);
        $this->assertTrue((bool) $payload['ownership']['seed_run_present']);
        $this->assertTrue((bool) $payload['ownership']['package_present_in_db']);
        $this->assertSame($expectedQuestionCount, (int) ($payload['impact']['counts']['Question'] ?? 0));
        $this->assertSame($expectedSavedTestCount, (int) ($payload['impact']['counts']['SavedGrammarTest'] ?? 0));
        $this->assertSame(
            $expectedSavedTestQuestionCount,
            (int) ($payload['impact']['counts']['SavedGrammarTestQuestion'] ?? 0)
        );
        $this->assertTrue((bool) $payload['result']['deleted']);
        $this->assertTrue((bool) $payload['result']['rolled_back']);
        $this->assertFalse((bool) $payload['result']['seed_run_removed']);
        $this->assertCount(1, $reportFiles);
        $this->assertSame(1, Question::query()->count());
        $this->assertSame(1, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('saved_grammar_test_questions')->count());
        $this->assertSame(1, DB::table('seed_runs')->count());
    }

    public function test_live_unseed_requires_force_and_then_removes_package_owned_data(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Unseed Live',
            namespace: 'Tests\\CodexFeature\\V3UnseedLive',
        );
        $this->makeDefinitionSeedable($generated, 'feature-v3-unseed-live-question');
        $this->seedPackageLive($generated);

        $blockedExitCode = Artisan::call('v3:unseed-package', [
            'target' => $generated['preview']['real_seeder_relative_path'],
            '--json' => true,
        ]);
        $blockedPayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $blockedExitCode);
        $this->assertSame('force_required', $blockedPayload['error']['reason']);
        $this->assertSame(1, Question::query()->count());
        $this->assertSame(1, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')->count());

        $liveExitCode = Artisan::call('v3:unseed-package', [
            'target' => $generated['preview']['seeder_relative_path'],
            '--force' => true,
            '--json' => true,
        ]);
        $livePayload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $liveExitCode);
        $this->assertFalse((bool) $livePayload['mode']['dry_run']);
        $this->assertTrue((bool) $livePayload['result']['deleted']);
        $this->assertFalse((bool) $livePayload['result']['rolled_back']);
        $this->assertTrue((bool) $livePayload['result']['seed_run_removed']);
        $this->assertSame(0, Question::query()->count());
        $this->assertSame(0, SavedGrammarTest::query()->count());
        $this->assertSame(0, DB::table('saved_grammar_test_questions')->count());
        $this->assertSame(0, DB::table('seed_runs')->count());
    }

    public function test_live_unseed_fails_when_other_saved_tests_reference_package_questions(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature V3 Unseed Guard',
            namespace: 'Tests\\CodexFeature\\V3UnseedGuard',
        );
        $questionUuid = 'feature-v3-unseed-guard-question';
        $this->makeDefinitionSeedable($generated, $questionUuid);
        $this->seedPackageLive($generated);

        $foreignSavedTest = SavedGrammarTest::query()->create([
            'uuid' => 'feature-v3-foreign-test',
            'slug' => 'feature-v3-foreign-test',
            'name' => 'Feature V3 Foreign Test',
            'filters' => [],
        ]);
        $foreignSavedTest->questionLinks()->create([
            'question_uuid' => $questionUuid,
            'position' => 1,
        ]);

        $exitCode = Artisan::call('v3:unseed-package', [
            'target' => $generated['preview']['package_relative_path'],
            '--force' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('dependency_guard', $payload['error']['stage']);
        $this->assertSame('external_saved_tests_reference_package', $payload['error']['reason']);
        $this->assertSame(1, Question::query()->count());
        $this->assertSame(2, SavedGrammarTest::query()->count());
        $this->assertSame(1, DB::table('seed_runs')->count());
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
                'question' => 'Feature V3 unseed package {a1}.',
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
