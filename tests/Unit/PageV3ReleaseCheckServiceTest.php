<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3ReleaseCheckService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class PageV3ReleaseCheckServiceTest extends TestCase
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

    public function test_it_resolves_definition_package_loader_and_real_seeder_targets_for_page_packages(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Page Release Resolution',
            categoryMode: 'new',
            newCategoryTitle: 'Unit Page Release Category',
        );
        $service = app(PageV3ReleaseCheckService::class);
        $targets = [
            $generated['preview']['page_definition_relative_path'],
            $generated['preview']['page_package_relative_path'],
            $generated['preview']['page_seeder_relative_path'],
            $generated['preview']['page_real_seeder_relative_path'],
        ];

        foreach ($targets as $target) {
            $report = $service->run($target, 'scaffold');

            $this->assertSame(
                $generated['preview']['page_definition_relative_path'],
                $report['target']['definition_relative_path']
            );
            $this->assertSame(
                $generated['preview']['page_package_relative_path'],
                $report['target']['package_root_relative_path']
            );
            $this->assertSame(
                $this->normalizePath(base_path($generated['preview']['page_seeder_relative_path'])),
                $report['target']['loader_absolute_path']
            );
            $this->assertSame(
                $this->normalizePath(base_path($generated['preview']['page_real_seeder_relative_path'])),
                $report['target']['real_seeder_absolute_path']
            );
            $this->assertNull($report['target']['localizations']['uk']);
        }
    }

    public function test_scaffold_profile_returns_warnings_but_no_fails_for_a_fresh_page_scaffold(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Page Release Scaffold',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Unit Existing Category',
                'slug' => 'unit-existing-category',
                'namespace' => 'Tests\\CodexUnit\\PageReleaseScaffold',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexUnit/PageReleaseScaffold/UnitExistingCategorySeeder.php',
            ],
        );

        $report = app(PageV3ReleaseCheckService::class)->run(
            $generated['preview']['page_package_relative_path'],
            'scaffold'
        );

        $this->assertSame(0, $report['summary']['check_counts']['fail']);
        $this->assertGreaterThan(0, $report['summary']['check_counts']['warn']);
        $this->assertFalse($report['summary']['fully_valid']);
        $this->assertSame('warn', $this->statusForCode($report, 'page_v3.definition.block_readiness'));
        $this->assertSame('warn', $this->statusForCode($report, 'page_v3.localization.en.coverage'));
    }

    public function test_release_profile_fails_when_unresolved_placeholders_remain_in_the_package_definition(): void
    {
        $generated = $this->writeGenerated(
            topic: 'Unit Page Placeholder',
            categoryMode: 'new',
            newCategoryTitle: 'Unit Placeholder Category',
        );
        $definitionPath = base_path($generated['preview']['page_definition_relative_path']);
        $definition = json_decode(File::get($definitionPath), true, 512, JSON_THROW_ON_ERROR);
        $definition['page']['category']['slug'] = '<resolved-category-slug>';
        File::put($definitionPath, json_encode($definition, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $report = app(PageV3ReleaseCheckService::class)->run(
            $generated['preview']['page_definition_relative_path'],
            'release'
        );

        $this->assertGreaterThan(0, $report['summary']['check_counts']['fail']);
        $this->assertSame('fail', $this->statusForCode($report, 'page_v3.definition.content_contract'));
    }

    public function test_it_rejects_targets_outside_the_page_v3_root(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Target must stay inside database/seeders/Page_V3.');

        app(PageV3ReleaseCheckService::class)->run(base_path('composer.json'));
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

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
