<?php

namespace Tests\Unit\FileManager;

use App\Modules\FileManager\Services\FileSystemService;
use Tests\TestCase;

class FileSystemServiceTest extends TestCase
{
    protected FileSystemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FileSystemService;
    }

    public function test_format_file_size_returns_correct_format(): void
    {
        $this->assertEquals('0 B', $this->service->formatFileSize(0));
        $this->assertEquals('1 B', $this->service->formatFileSize(1));
        $this->assertEquals('1 KB', $this->service->formatFileSize(1024));
        $this->assertEquals('1 MB', $this->service->formatFileSize(1048576));
        $this->assertEquals('1 GB', $this->service->formatFileSize(1073741824));
    }

    public function test_format_file_size_handles_null(): void
    {
        $this->assertEquals('N/A', $this->service->formatFileSize(null));
    }

    public function test_get_base_path_returns_string(): void
    {
        $basePath = $this->service->getBasePath();
        $this->assertIsString($basePath);
        $this->assertNotEmpty($basePath);
    }

    public function test_get_file_tree_returns_array(): void
    {
        $tree = $this->service->getFileTree('');
        $this->assertIsArray($tree);
    }

    public function test_get_file_tree_filters_excluded_directories(): void
    {
        $tree = $this->service->getFileTree('');

        // Check that excluded directories are not in the tree
        $directoryNames = array_column(
            array_filter($tree, fn ($item) => $item['type'] === 'directory'),
            'name'
        );

        // These directories should be excluded by default
        $excludedDirs = ['vendor', 'node_modules', '.git'];

        foreach ($excludedDirs as $excludedDir) {
            $this->assertNotContains($excludedDir, $directoryNames,
                "Excluded directory '{$excludedDir}' should not be in the tree");
        }
    }

    public function test_get_file_info_returns_null_for_invalid_path(): void
    {
        $info = $this->service->getFileInfo('../../../etc/passwd');
        $this->assertNull($info);
    }

    public function test_get_file_info_returns_null_for_nonexistent_path(): void
    {
        $info = $this->service->getFileInfo('this/path/does/not/exist.txt');
        $this->assertNull($info);
    }

    public function test_get_file_tree_structure_is_valid(): void
    {
        $tree = $this->service->getFileTree('');

        foreach ($tree as $item) {
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('path', $item);
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('modified', $item);
            $this->assertArrayHasKey('readable', $item);
            $this->assertArrayHasKey('writable', $item);

            $this->assertContains($item['type'], ['file', 'directory']);

            if ($item['type'] === 'file') {
                $this->assertArrayHasKey('size', $item);
                $this->assertArrayHasKey('extension', $item);
                $this->assertArrayHasKey('mime_type', $item);
            }
        }
    }
}
