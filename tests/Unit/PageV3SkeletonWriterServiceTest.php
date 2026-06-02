<?php

namespace Tests\Unit;

use App\Services\PageV3PromptGenerator\PageV3BlueprintService;
use App\Services\PageV3PromptGenerator\PageV3SkeletonWriterService;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class PageV3SkeletonWriterServiceTest extends TestCase
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

    public function test_planned_files_for_existing_mode_include_page_only(): void
    {
        $generated = $this->makeGenerated(
            topic: 'Unit Existing Page',
            categoryMode: 'existing',
            categoryContext: [
                'title' => 'Unit Existing Category',
                'slug' => 'unit-existing-category',
                'namespace' => 'Tests\\CodexUnit\\PageExisting',
                'seeder_relative_path' => 'database/seeders/Page_V3/Tests/CodexUnit/PageExisting/UnitExistingCategorySeeder.php',
            ],
        );

        $planned = app(PageV3SkeletonWriterService::class)->plannedFiles($generated);
        $pageDefinitionPath = base_path(str_replace('/', DIRECTORY_SEPARATOR, $generated['preview']['page_definition_relative_path']));
        $categoryDefinitionPath = base_path(str_replace('/', DIRECTORY_SEPARATOR, $generated['preview']['category_definition_relative_path']));

        $this->assertCount(5, $planned);
        $this->assertContains($pageDefinitionPath, $planned);
        $this->assertNotContains($categoryDefinitionPath, $planned);
    }

    public function test_write_new_mode_creates_page_and_category_scaffolds(): void
    {
        $generated = $this->makeGenerated(
            topic: 'Unit New Page',
            categoryMode: 'new',
            newCategoryTitle: 'Unit New Category',
        );

        $written = app(PageV3SkeletonWriterService::class)->write($generated, true);
        $pageDefinition = json_decode(File::get(base_path($generated['preview']['page_definition_relative_path'])), true, 512, JSON_THROW_ON_ERROR);
        $categoryDefinition = json_decode(File::get(base_path($generated['preview']['category_definition_relative_path'])), true, 512, JSON_THROW_ON_ERROR);
        $pageLocalization = json_decode(File::get(base_path($generated['preview']['page_localization_en_relative_path'])), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(10, $written['count']);
        $this->assertSame('page', $pageDefinition['content_type']);
        $this->assertSame('category', $categoryDefinition['content_type']);
        $this->assertSame($generated['preview']['category_slug'], $pageDefinition['page']['category']['slug']);
        $this->assertSame('../definition.json', $pageLocalization['target']['definition_path']);
        $this->assertSame(
            $generated['preview']['page_fully_qualified_class_name'],
            $pageLocalization['target']['seeder_class']
        );
    }

    public function test_ai_select_mode_is_rejected_for_scaffold_writing(): void
    {
        $generated = [
            'source' => [
                'source_type' => 'manual_topic',
                'source_label' => 'Manual topic',
                'topic' => 'Unit AI Select',
            ],
            'category' => [
                'mode' => 'ai_select',
                'mode_label' => 'Let AI choose the best category or create a new one',
            ],
            'preview' => app(PageV3BlueprintService::class)->buildPreview('Unit AI Select', 'ai_select', null, null),
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot write Page_V3 skeleton for ai_select mode.');

        app(PageV3SkeletonWriterService::class)->plannedFiles($generated);
    }

    /**
     * @param  array<string, mixed>|null  $categoryContext
     * @return array<string, mixed>
     */
    private function makeGenerated(
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

        return [
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
    }
}
