<?php

namespace Tests\Feature;

use App\Services\V3PromptGenerator\V3SeederBlueprintService;
use App\Services\V3PromptGenerator\V3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class V3PlanChangedCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

    protected function tearDown(): void
    {
        foreach (array_reverse(array_unique($this->cleanupPaths)) as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);

                continue;
            }

            File::delete($path);
        }

        parent::tearDown();
    }

    public function test_it_plans_untracked_v3_packages_in_default_working_tree_mode(): void
    {
        $alpha = $this->writeGenerated('Plan Changed Alpha', 'Tests\\CodexFeature\\V3PlanChanged');
        $beta = $this->writeGenerated('Plan Changed Beta', 'Tests\\CodexFeature\\V3PlanChanged');

        [$exitCode, $payload] = $this->callPlanChanged('database/seeders/V3/Tests/CodexFeature/V3PlanChanged', [
            '--include-untracked' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame('working_tree', $payload['diff']['mode']);
        $this->assertTrue((bool) $payload['diff']['include_untracked']);
        $this->assertSame(2, $payload['summary']['changed_packages']);
        $this->assertSame(2, $payload['summary']['seed_candidates']);
        $this->assertSame(
            [$alpha['preview']['package_relative_path'], $beta['preview']['package_relative_path']],
            array_column($payload['phases']['upsert_present'], 'current_relative_path')
        );
        $this->assertSame('seed', $payload['packages'][0]['recommended_action']);
        $this->assertSame('seed', $payload['packages'][1]['recommended_action']);
    }

    public function test_it_rejects_targets_outside_the_v3_root(): void
    {
        [$exitCode, $payload] = $this->callPlanChanged(base_path('composer.json'), [
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/V3', $payload['error']['message']);
    }

    public function test_it_resolves_single_package_target_variants_for_v3_plan_changed(): void
    {
        $generated = $this->writeGenerated('Plan Changed Target', 'Tests\\CodexFeature\\V3PlanChangedTarget');
        $targets = [
            $generated['preview']['package_relative_path'],
            $generated['preview']['definition_relative_path'],
            $generated['preview']['seeder_relative_path'],
            $generated['preview']['real_seeder_relative_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callPlanChanged($target, [
                '--include-untracked' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, $target . PHP_EOL . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package'], $target);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['scope']['resolved_root_relative_path'], $target);
            $this->assertSame(1, $payload['summary']['changed_packages'], $target);
            $this->assertSame($generated['preview']['package_relative_path'], $payload['packages'][0]['current_relative_path'], $target);
        }
    }

    public function test_head_without_base_fails_for_v3_plan_changed(): void
    {
        [$exitCode, $payload] = $this->callPlanChanged(null, [
            '--head' => 'HEAD',
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('git_diff', $payload['error']['stage']);
        $this->assertStringContainsString('--base', $payload['error']['message']);
    }

    public function test_v3_plan_changed_runs_release_check_and_writes_report(): void
    {
        Storage::fake('local');
        $generated = $this->writeGenerated('Plan Changed Release', 'Tests\\CodexFeature\\V3PlanChangedRelease');

        [$exitCode, $payload] = $this->callPlanChanged($generated['preview']['package_relative_path'], [
            '--include-untracked' => true,
            '--with-release-check' => true,
            '--check-profile' => 'scaffold',
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('changed-package-plans/v3');

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertTrue((bool) $payload['packages'][0]['release_check']['executed']);
        $this->assertSame('scaffold', $payload['packages'][0]['release_check']['profile']);
        $this->assertContains($payload['packages'][0]['release_check']['status'], ['warn', 'pass', 'fail']);
        $this->assertCount(1, $reportFiles);
        $this->assertNotNull($payload['artifacts']['report_path']);
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array{0:int,1:array<string,mixed>}
     */
    private function callPlanChanged(?string $target, array $arguments): array
    {
        $payloadArguments = $arguments;

        if ($target !== null) {
            $payloadArguments['target'] = $target;
        }

        $exitCode = Artisan::call('v3:plan-changed', $payloadArguments);

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
}
