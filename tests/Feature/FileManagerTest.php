<?php

namespace Tests\Feature;

use Tests\TestCase;

class FileManagerTest extends TestCase
{
    public function test_file_manager_service_can_get_directory_tree(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        $result = $service->getDirectoryTree();
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Verify structure of items
        foreach ($result as $item) {
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('path', $item);
            $this->assertArrayHasKey('type', $item);
            $this->assertContains($item['type'], ['directory', 'file']);
        }
    }

    public function test_file_manager_service_can_get_statistics(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        $stats = $service->getStatistics();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('directories', $stats);
        $this->assertArrayHasKey('files', $stats);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertIsInt($stats['directories']);
        $this->assertIsInt($stats['files']);
        $this->assertIsInt($stats['total_size']);
    }

    public function test_file_manager_service_can_get_breadcrumbs(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        // Test root breadcrumb
        $breadcrumbs = $service->getBreadcrumbs(null);
        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals('Root', $breadcrumbs[0]['name']);
        
        // Test nested path breadcrumbs
        $breadcrumbs = $service->getBreadcrumbs('app/Modules');
        $this->assertGreaterThan(1, count($breadcrumbs));
        $this->assertEquals('Root', $breadcrumbs[0]['name']);
    }

    public function test_file_manager_service_prevents_directory_traversal(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        // Attempt directory traversal
        $this->expectException(\RuntimeException::class);
        $service->getDirectoryTree('../../../etc');
    }

    public function test_file_manager_service_can_read_file_content(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        // Test reading composer.json which should exist
        $fileData = $service->getFileContent('composer.json');
        
        $this->assertIsArray($fileData);
        $this->assertArrayHasKey('name', $fileData);
        $this->assertArrayHasKey('content', $fileData);
        $this->assertArrayHasKey('can_preview', $fileData);
        $this->assertEquals('composer.json', $fileData['name']);
    }

    public function test_file_manager_hides_configured_paths(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        $items = $service->getDirectoryTree();
        
        // Check that vendor and node_modules are hidden
        $names = array_column($items, 'name');
        $this->assertNotContains('vendor', $names);
        $this->assertNotContains('node_modules', $names);
        $this->assertNotContains('.git', $names);
    }

    public function test_file_manager_root_path_is_set(): void
    {
        $service = new \App\Modules\FileManager\Services\FileManagerService();
        
        $rootPath = $service->getRootPath();
        
        $this->assertNotEmpty($rootPath);
        $this->assertEquals(base_path(), $rootPath);
    }
}
