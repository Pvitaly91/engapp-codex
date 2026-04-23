<?php

namespace Tests\Feature;

use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\Support\RebuildsComposeTestSchema;
use Tests\TestCase;

class V3FolderPlanCommandTest extends TestCase
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

    public function test_sync_mode_plans_seed_and_refresh_candidates_for_one_v3_folder(): void
    {
        $namespace = 'Tests\\CodexFeature\\V3PlanFolder';
        $alpha = $this->writeGenerated('Alpha Plan Package', $namespace);
        $beta = $this->writeGenerated('Beta Plan Package', $namespace);
        $this->makeDefinitionSeedable($alpha, 'alpha-plan-package-question');
        $this->makeDefinitionSeedable($beta, 'beta-plan-package-question');
        $this->seedPackageLive($alpha);

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => 'database/seeders/V3/Tests/CodexFeature/V3PlanFolder',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('sync', $payload['scope']['mode']);
        $this->assertFalse((bool) $payload['scope']['single_package']);
        $this->assertSame(2, $payload['summary']['total_packages']);
        $this->assertSame(1, $payload['summary']['seed_candidates']);
        $this->assertSame(1, $payload['summary']['refresh_candidates']);
        $this->assertSame($alpha['preview']['package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('seeded', $payload['packages'][0]['package_state']);
        $this->assertSame('refresh', $payload['packages'][0]['recommended_action']);
        $this->assertStringContainsString('v3:refresh-package', $payload['packages'][0]['next_step_command']);
        $this->assertStringContainsString('--force', $payload['packages'][0]['next_step_command']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('not_seeded', $payload['packages'][1]['package_state']);
        $this->assertSame('seed', $payload['packages'][1]['recommended_action']);
        $this->assertStringContainsString('v3:seed-package', $payload['packages'][1]['next_step_command']);
    }

    public function test_unseed_mode_marks_seeded_packages_for_unseed_in_reverse_lexical_order(): void
    {
        $namespace = 'Tests\\CodexFeature\\V3PlanUnseedFolder';
        $alpha = $this->writeGenerated('Alpha Unseed Plan Package', $namespace);
        $beta = $this->writeGenerated('Beta Unseed Plan Package', $namespace);
        $gamma = $this->writeGenerated('Gamma Unseed Plan Package', $namespace);
        $this->makeDefinitionSeedable($alpha, 'alpha-unseed-plan-package-question');
        $this->makeDefinitionSeedable($beta, 'beta-unseed-plan-package-question');
        $this->makeDefinitionSeedable($gamma, 'gamma-unseed-plan-package-question');
        $this->seedPackageLive($alpha);
        $this->seedPackageLive($beta);

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => 'database/seeders/V3/Tests/CodexFeature/V3PlanUnseedFolder',
            '--mode' => 'unseed',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('unseed', $payload['scope']['mode']);
        $this->assertSame(3, $payload['summary']['total_packages']);
        $this->assertSame(2, $payload['summary']['unseed_candidates']);
        $this->assertSame(1, $payload['summary']['skipped']);
        $this->assertSame($gamma['preview']['package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('not_seeded', $payload['packages'][0]['package_state']);
        $this->assertSame('skip', $payload['packages'][0]['recommended_action']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('unseed', $payload['packages'][1]['recommended_action']);
        $this->assertStringContainsString('v3:unseed-package', $payload['packages'][1]['next_step_command']);
        $this->assertStringContainsString('--force', $payload['packages'][1]['next_step_command']);
        $this->assertSame($alpha['preview']['package_relative_path'], $payload['packages'][2]['relative_path']);
        $this->assertSame('unseed', $payload['packages'][2]['recommended_action']);
    }

    public function test_destroy_files_mode_marks_only_unseeded_on_disk_packages_for_file_destroy(): void
    {
        $namespace = 'Tests\\CodexFeature\\V3PlanDestroyFiles';
        $alpha = $this->writeGenerated('Alpha Destroy Files Plan Package', $namespace);
        $beta = $this->writeGenerated('Beta Destroy Files Plan Package', $namespace);
        $this->makeDefinitionSeedable($alpha, 'alpha-destroy-files-plan-package-question');
        $this->makeDefinitionSeedable($beta, 'beta-destroy-files-plan-package-question');
        $this->seedPackageLive($alpha);

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => 'database/seeders/V3/Tests/CodexFeature/V3PlanDestroyFiles',
            '--mode' => 'destroy-files',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode);
        $this->assertSame('destroy-files', $payload['scope']['mode']);
        $this->assertSame(2, $payload['summary']['total_packages']);
        $this->assertSame(1, $payload['summary']['destroy_files_candidates']);
        $this->assertSame(1, $payload['summary']['blocked']);
        $this->assertSame($beta['preview']['package_relative_path'], $payload['packages'][0]['relative_path']);
        $this->assertSame('destroy_files', $payload['packages'][0]['recommended_action']);
        $this->assertSame($alpha['preview']['package_relative_path'], $payload['packages'][1]['relative_path']);
        $this->assertSame('seeded', $payload['packages'][1]['package_state']);
        $this->assertSame('blocked', $payload['packages'][1]['recommended_action']);
        $this->assertNotEmpty($payload['packages'][1]['warnings']);
    }

    public function test_destroy_mode_classifies_combined_destroy_db_only_file_only_and_skip_packages(): void
    {
        $namespace = 'Tests\\CodexFeature\\V3PlanDestroy';
        $delta = $this->writeGenerated('Delta Destroy Plan Package', $namespace);
        $charlie = $this->writeGenerated('Charlie Destroy Plan Package', $namespace);
        $beta = $this->writeGenerated('Beta Destroy Plan Package', $namespace);
        $alpha = $this->writeGenerated('Alpha Destroy Plan Package', $namespace);
        $this->makeDefinitionSeedable($delta, 'delta-destroy-plan-package-question');
        $this->makeDefinitionSeedable($charlie, 'charlie-destroy-plan-package-question');
        $this->makeDefinitionSeedable($beta, 'beta-destroy-plan-package-question');
        $this->makeDefinitionSeedable($alpha, 'alpha-destroy-plan-package-question');
        $this->seedPackageLive($delta);
        $this->seedPackageLive($beta);
        File::delete($beta['preview']['real_seeder_absolute_path']);
        File::delete($alpha['preview']['real_seeder_absolute_path']);

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => 'database/seeders/V3/Tests/CodexFeature/V3PlanDestroy',
            '--mode' => 'destroy',
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame('destroy', $payload['scope']['mode']);
        $this->assertSame(4, $payload['summary']['total_packages']);
        $this->assertSame(3, $payload['summary']['destroy_candidates']);
        $this->assertSame(1, $payload['summary']['skipped']);
        $this->assertSame(
            [
                $delta['preview']['package_relative_path'],
                $charlie['preview']['package_relative_path'],
                $beta['preview']['package_relative_path'],
                $alpha['preview']['package_relative_path'],
            ],
            array_column($payload['packages'], 'relative_path')
        );
        $this->assertSame('destroy', $payload['packages'][0]['recommended_action']);
        $this->assertTrue((bool) $payload['packages'][0]['needs_unseed']);
        $this->assertTrue((bool) $payload['packages'][0]['needs_file_destroy']);
        $this->assertSame('destroy', $payload['packages'][1]['recommended_action']);
        $this->assertFalse((bool) $payload['packages'][1]['needs_unseed']);
        $this->assertTrue((bool) $payload['packages'][1]['needs_file_destroy']);
        $this->assertSame('destroy', $payload['packages'][2]['recommended_action']);
        $this->assertTrue((bool) $payload['packages'][2]['needs_unseed']);
        $this->assertFalse((bool) $payload['packages'][2]['needs_file_destroy']);
        $this->assertNotEmpty($payload['packages'][2]['warnings']);
        $this->assertSame('skip', $payload['packages'][3]['recommended_action']);
        $this->assertFalse((bool) $payload['packages'][3]['needs_unseed']);
        $this->assertFalse((bool) $payload['packages'][3]['needs_file_destroy']);
        $this->assertNotEmpty($payload['packages'][3]['warnings']);
    }

    public function test_strict_mode_fails_when_v3_package_has_db_content_without_seed_run(): void
    {
        $generated = $this->writeGenerated('Strict V3 Plan Package', 'Tests\\CodexFeature\\V3PlanStrict');
        $this->makeDefinitionSeedable($generated, 'strict-v3-plan-package-question');
        $this->seedPackageLive($generated);

        DB::table('seed_runs')
            ->where('class_name', $generated['preview']['fully_qualified_class_name'])
            ->delete();

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => $generated['preview']['package_relative_path'],
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('planning', $payload['error']['stage']);
        $this->assertSame('blocked_packages', $payload['error']['reason']);
        $this->assertSame('db_only_without_seed_run', $payload['packages'][0]['package_state']);
        $this->assertSame('blocked', $payload['packages'][0]['recommended_action']);
    }

    public function test_strict_mode_fails_on_v3_release_check_warnings_when_requested(): void
    {
        $generated = $this->writeGenerated('Scaffold V3 Plan Package', 'Tests\\CodexFeature\\V3PlanReleaseWarnings');

        $exitCode = Artisan::call('v3:plan-folder', [
            'target' => $generated['preview']['package_relative_path'],
            '--with-release-check' => true,
            '--check-profile' => 'scaffold',
            '--strict' => true,
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame('release_check', $payload['error']['stage']);
        $this->assertSame('warnings_are_fatal', $payload['error']['reason']);
        $this->assertSame('not_seeded', $payload['packages'][0]['package_state']);
        $this->assertSame('seed', $payload['packages'][0]['recommended_action']);
        $this->assertTrue((bool) $payload['packages'][0]['release_check']['executed']);
        $this->assertSame('warn', $payload['packages'][0]['release_check']['status']);
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
                'question' => 'Planner V3 package question {a1}.',
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
