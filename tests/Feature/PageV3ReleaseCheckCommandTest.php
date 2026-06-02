<?php

namespace Tests\Feature;

use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageV3ReleaseCheckCommandTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $cleanupPaths = [];

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

    public function test_scaffold_profile_human_output_for_existing_page_package_can_write_a_report(): void
    {
        Storage::fake('local');
        $generated = $this->writeGenerated(
            topic: 'Feature Page Release Scaffold',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Feature Existing Category',
                'slug' => 'feature-existing-category',
                'namespace' => 'Tests\\CodexFeature\\PageReleaseScaffold',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexFeature/PageReleaseScaffold/FeatureExistingCategorySeeder.php',
            ],
        );

        $exitCode = Artisan::call('page-v3:release-check', [
            'target' => $generated['preview']['page_package_relative_path'],
            '--profile' => 'scaffold',
            '--write-report' => true,
        ]);

        $output = str_replace("\r\n", "\n", Artisan::output());
        $reportFiles = Storage::disk('local')->allFiles('release-checks/page-v3');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Profile: scaffold', $output);
        $this->assertStringContainsString('[WARN] The Page_V3 definition has content blocks ready for seeding', $output);
        $this->assertStringContainsString('Summary:', $output);
        $this->assertCount(1, $reportFiles);
    }

    public function test_release_profile_json_output_fails_for_an_empty_page_scaffold(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Release Empty',
            categoryMode: 'new',
            newCategoryTitle: 'Feature Page Release Category',
        );

        $exitCode = Artisan::call('page-v3:release-check', [
            'target' => $generated['preview']['page_definition_relative_path'],
            '--json' => true,
        ]);

        $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(1, $exitCode);
        $this->assertSame($generated['preview']['page_definition_relative_path'], $payload['target']['definition_relative_path']);
        $this->assertGreaterThan(0, $payload['summary']['check_counts']['fail']);
        $this->assertSame('fail', $this->statusForCode($payload, 'page_v3.definition.block_readiness'));
    }

    public function test_strict_mode_makes_scaffold_warnings_fatal_for_page_packages(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Feature Page Release Strict',
            categoryMode: 'new',
            newCategoryTitle: 'Feature Page Release Strict Category',
        );

        $exitCode = Artisan::call('page-v3:release-check', [
            'target' => $generated['preview']['page_seeder_relative_path'],
            '--profile' => 'scaffold',
            '--strict' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Summary:', $output);
        $this->assertStringContainsString('WARN', $output);
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
        $preview = app(PageV3BlueprintService::class)->buildPreview($topic, $categoryMode, $categoryContext, $newCategoryTitle);

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
                'selected_category' => $categoryMode === 'existing' ? $categoryContext : null,
                'new_category_title' => $newCategoryTitle,
                'new_category_slug' => $categoryMode === 'new' ? $preview['category_slug'] : null,
            ],
            'preview' => $preview,
        ];

        app(PageV3SkeletonWriterService::class)->write($generated, true);

        return $generated;
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function statusForCode(array $report, string $code): ?string
    {
        foreach ((array) ($report['checks'] ?? []) as $check) {
            if (($check['code'] ?? null) === $code) {
                return (string) ($check['status'] ?? '');
            }
        }

        return null;
    }
}
