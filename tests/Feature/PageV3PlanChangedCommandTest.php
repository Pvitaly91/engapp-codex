<?php

namespace Tests\Feature;

use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageV3PlanChangedCommandTest extends TestCase
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

    public function test_it_plans_untracked_page_v3_packages_with_category_before_page(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Plan Changed Page',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Plan Changed Category',
        );

        [$exitCode, $payload] = $this->callPlanChanged(dirname((string) $generated['preview']['category_seeder_relative_path']), [
            '--include-untracked' => true,
            '--json' => true,
        ]);

        $this->assertSame(0, $exitCode, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->assertSame('working_tree', $payload['diff']['mode']);
        $this->assertTrue((bool) $payload['diff']['include_untracked']);
        $this->assertSame(2, $payload['summary']['changed_packages']);
        $this->assertSame(2, $payload['summary']['seed_candidates']);
        $this->assertSame(
            [
                $generated['preview']['category_package_relative_path'],
                $generated['preview']['page_package_relative_path'],
            ],
            array_column($payload['phases']['upsert_present'], 'current_relative_path')
        );
        $this->assertSame('category', $payload['packages'][0]['package_type']);
        $this->assertSame('page', $payload['packages'][1]['package_type']);
    }

    public function test_it_rejects_targets_outside_the_page_v3_root(): void
    {
        [$exitCode, $payload] = $this->callPlanChanged(base_path('composer.json'), [
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('target_resolution', $payload['error']['stage']);
        $this->assertStringContainsString('database/seeders/Page_V3', $payload['error']['message']);
    }

    public function test_it_resolves_single_package_target_variants_for_page_v3_plan_changed(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Plan Changed Target Page',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Plan Changed Target Category',
        );
        $targets = [
            $generated['preview']['page_package_relative_path'],
            $generated['preview']['page_definition_relative_path'],
            $generated['preview']['page_seeder_relative_path'],
            $generated['preview']['page_real_seeder_relative_path'],
        ];

        foreach ($targets as $target) {
            [$exitCode, $payload] = $this->callPlanChanged($target, [
                '--include-untracked' => true,
                '--json' => true,
            ]);

            $this->assertSame(0, $exitCode, $target . PHP_EOL . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->assertTrue((bool) $payload['scope']['single_package'], $target);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['scope']['resolved_root_relative_path'], $target);
            $this->assertSame(1, $payload['summary']['changed_packages'], $target);
            $this->assertSame($generated['preview']['page_package_relative_path'], $payload['packages'][0]['current_relative_path'], $target);
        }
    }

    public function test_head_without_base_fails_for_page_v3_plan_changed(): void
    {
        [$exitCode, $payload] = $this->callPlanChanged(null, [
            '--head' => 'HEAD',
            '--json' => true,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('git_diff', $payload['error']['stage']);
        $this->assertStringContainsString('--base', $payload['error']['message']);
    }

    public function test_page_v3_plan_changed_runs_release_check_and_writes_report(): void
    {
        Storage::fake('local');
        $generated = $this->writeGenerated(
            topic: 'Plan Changed Release Page',
            categoryMode: 'new',
            categoryContext: null,
            newCategoryTitle: 'Plan Changed Release Category',
        );

        [$exitCode, $payload] = $this->callPlanChanged($generated['preview']['page_package_relative_path'], [
            '--include-untracked' => true,
            '--with-release-check' => true,
            '--check-profile' => 'scaffold',
            '--write-report' => true,
            '--json' => true,
        ]);

        $reportFiles = Storage::disk('local')->allFiles('changed-package-plans/page-v3');

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

        $exitCode = Artisan::call('page-v3:plan-changed', $payloadArguments);

        /** @var array<string, mixed> $payload */
        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        return [$exitCode, $payload];
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    private function writeGenerated(
        string $topic,
        string $categoryMode,
        ?array $categoryContext = null,
        ?string $newCategoryTitle = null,
    ): array {
        $preview = app(PageV3BlueprintService::class)->buildPreview(
            $topic,
            $categoryMode,
            $categoryContext,
            $newCategoryTitle
        );

        $this->cleanupPaths[] = base_path($preview['page_seeder_relative_path']);
        $this->cleanupPaths[] = base_path($preview['page_package_relative_path']);

        if ($categoryMode === 'new') {
            $this->cleanupPaths[] = base_path($preview['category_seeder_relative_path']);
            $this->cleanupPaths[] = base_path($preview['category_package_relative_path']);
        }

        $generated = [
            'source' => [
                'source_type' => 'manual_topic',
                'source_label' => 'Manual topic',
                'topic' => $topic,
            ],
            'category' => [
                'mode' => $categoryMode,
                'mode_label' => $categoryMode,
                'selected_category' => $categoryContext,
                'new_category_title' => $newCategoryTitle,
            ],
            'preview' => $preview,
        ];

        app(PageV3SkeletonWriterService::class)->write($generated, true);

        return [
            'preview' => $preview,
        ];
    }
}
